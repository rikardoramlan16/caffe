<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\InventoryTransaction;
use App\Models\MenuRecipe;
use App\Models\Order;
use App\Models\Payment;
use App\Models\TableTransfer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderActionController extends Controller
{
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:WAITING_PAYMENT,PAID,MAKING,READY,DONE,CANCEL']]);
        
        $oldStatus = $order->status;
        $order->update(['status' => $data['status'], 'paid_at' => $data['status'] === 'PAID' ? now() : $order->paid_at]);

        // Deduct stock when status transitions to DONE
        if ($data['status'] === 'DONE' && $oldStatus !== 'DONE') {
            $order->loadMissing(['items.menu', 'items.toppings.topping.inventory']);

            foreach ($order->items as $item) {
                $recipes = MenuRecipe::where('menu_id', $item->menu_id)->get();
                foreach ($recipes as $recipe) {
                    $inventory = $recipe->inventory;
                    $deductQty = $recipe->quantity * $item->quantity;
                    
                    $inventory->decrement('current_stock', $deductQty);

                    InventoryTransaction::create([
                        'inventory_id' => $inventory->id,
                        'type' => 'OUT',
                        'quantity' => -$deductQty,
                        'reference' => $order->invoice_number,
                        'note' => 'Pengurangan stok otomatis produksi menu: ' . $item->menu->name . ' (' . $item->quantity . ' porsi)',
                    ]);
                }

                foreach ($item->toppings as $orderTopping) {
                    $topping = $orderTopping->topping;
                    if (! $topping || ! $topping->inventory_id || ! $topping->inventory) {
                        continue;
                    }

                    $deductQty = $topping->inventory_quantity * $item->quantity;
                    $topping->inventory->decrement('current_stock', $deductQty);

                    InventoryTransaction::create([
                        'inventory_id' => $topping->inventory_id,
                        'type' => 'OUT',
                        'quantity' => -$deductQty,
                        'reference' => $order->invoice_number,
                        'note' => 'Pengurangan stok otomatis topping: ' . $topping->name . ' (' . $item->quantity . ' porsi)',
                    ]);
                }
            }
        }

        ActivityLog::create([
            'branch_id' => $order->branch_id,
            'user_id' => $request->session()->get('auth_user.id'),
            'action' => 'order.status',
            'description' => "Order {$order->invoice_number} diubah ke {$data['status']}.",
        ]);

        return back()->with('success', 'Status order diperbarui.');
    }

    public function confirmPayment(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['method' => ['required', 'in:QRIS,Cash,Debit,Transfer']]);

        $order->update(['status' => 'PAID', 'paid_at' => now()]);
        Payment::updateOrCreate(
            ['order_id' => $order->id],
            ['cafe_order_id' => null, 'method' => $data['method'], 'status' => 'paid', 'amount' => $order->total, 'paid_at' => now(), 'reference' => 'PAY-'.$order->invoice_number]
        );

        return back()->with('success', 'Pembayaran dikonfirmasi.');
    }

    public function moveTable(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['table_id' => ['required', 'exists:tables,id']]);
        $fromTableId = $order->table_id;

        $order->update(['table_id' => $data['table_id']]);
        TableTransfer::create([
            'order_id' => $order->id,
            'from_table_id' => $fromTableId,
            'to_table_id' => $data['table_id'],
            'moved_by_user_id' => $request->session()->get('auth_user.id'),
            'reason' => 'Pindah meja dari dashboard staff',
        ]);

        return back()->with('success', 'Meja pelanggan berhasil dipindahkan.');
    }
}
