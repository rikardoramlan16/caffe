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

    public function addItemByBarcode(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'barcode' => ['required', 'string']
        ]);

        // Find the product item by barcode
        $product = \App\Models\Product::where('barcode', $data['barcode'])->first();
        if (!$product) {
            return back()->with('error', 'Produk dengan barcode "' . $data['barcode'] . '" tidak ditemukan.');
        }

        if (!$product->is_available) {
            return back()->with('error', 'Produk "' . $product->name . '" sedang tidak tersedia.');
        }

        // Add or increment the item in the order
        $orderItem = \App\Models\OrderItem::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->first();

        if ($orderItem) {
            $orderItem->increment('quantity');
        } else {
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => $product->price,
                'size' => 'Regular',
                'size_price' => 0,
                'sugar_level' => '100%',
                'ice_level' => 'Normal',
            ]);
        }

        // Recalculate totals
        $subtotal = \App\Models\OrderItem::where('order_id', $order->id)
            ->get()
            ->reduce(fn($sum, $item) => $sum + ($item->unit_price + $item->size_price) * $item->quantity, 0);
            
        $total = $subtotal + $order->service_fee;

        $order->update([
            'subtotal' => $subtotal,
            'total' => $total
        ]);

        return back()->with('success', 'Produk "' . $product->name . '" berhasil ditambahkan ke pesanan.');
    }

    public function createDirectOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'table_id' => ['nullable', 'exists:tables,id'],
            'customer_note' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.menu_id' => ['nullable', 'exists:menus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->session()->get('auth_user');
        $branchId = $user['branch_id'] ?? \App\Models\Branch::first()?->id;

        $invoiceNumber = 'INV-' . now()->format('ymd') . '-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $serviceFee = intval(\App\Models\Setting::where('key', 'service_fee')->first()?->value ?? 0);

        \DB::beginTransaction();
        try {
            $order = Order::create([
                'branch_id' => $branchId,
                'table_id' => $data['table_id'] ?? null,
                'customer_session_id' => null,
                'invoice_number' => $invoiceNumber,
                'status' => 'WAITING_PAYMENT',
                'subtotal' => 0,
                'service_fee' => $serviceFee,
                'total' => $serviceFee,
                'customer_note' => $data['customer_note'] ?? null,
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $price = 0;
                $productId = $item['product_id'] ?? null;
                $menuId = $item['menu_id'] ?? null;

                if ($productId) {
                    $product = \App\Models\Product::find($productId);
                    $price = $product->price;
                } elseif ($menuId) {
                    $menu = \App\Models\Menu::find($menuId);
                    $price = $menu->price;
                }

                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'menu_id' => $menuId,
                    'quantity' => $item['quantity'],
                    'unit_price' => $price,
                    'size' => 'Regular',
                    'size_price' => 0,
                    'sugar_level' => '100%',
                    'ice_level' => 'Normal',
                ]);

                $subtotal += $price * $item['quantity'];
            }

            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + $serviceFee
            ]);

            \DB::commit();
            return back()->with('success', 'Order ' . $invoiceNumber . ' berhasil dibuat!');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Gagal membuat order: ' . $e->getMessage());
        }
    }
}
