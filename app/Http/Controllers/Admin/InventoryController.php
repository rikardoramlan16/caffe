<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Dashboard Inventory
     */
    public function index(Request $request): View
    {
        $user = $request->session()->get('auth_user');

        $inventories = Inventory::with('category')->get();
        $categories = InventoryCategory::all();
        $suppliers = Supplier::all();
        
        $purchaseOrders = collect();
        $stockOpnames = collect();
        $transactions = collect();

        // Barista only needs inventories and categories
        if ($user['role'] !== 'barista') {
            $transactions = InventoryTransaction::with('inventory')->latest()->take(30)->get();
            
            // Only admin and super_admin need POs and Stock Opnames
            if (in_array($user['role'], ['admin', 'super_admin'])) {
                $purchaseOrders = PurchaseOrder::with(['supplier', 'items.inventory'])->latest()->get();
                $stockOpnames = StockOpname::with('items.inventory')->latest()->get();
            }
        }

        return view('pages.dashboard.admin-inventory', [
            'user' => $user,
            'inventories' => $inventories,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'purchaseOrders' => $purchaseOrders,
            'stockOpnames' => $stockOpnames,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Input Barang Masuk Manual (IN)
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'inventory_id' => ['required', 'exists:inventories,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'price' => ['nullable', 'integer', 'min:0'],
            'supplier_name' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $inventory = Inventory::findOrFail($data['inventory_id']);
            $oldStock = $inventory->current_stock;
            $newStock = $oldStock + $data['quantity'];

            // Update Stock
            $inventory->update(['current_stock' => $newStock]);

            // Record Transaction
            InventoryTransaction::create([
                'inventory_id' => $inventory->id,
                'type' => 'IN',
                'quantity' => $data['quantity'],
                'reference' => 'MANUAL-IN-' . now()->format('ymdHis'),
                'note' => ($data['supplier_name'] ? 'Dari: ' . $data['supplier_name'] . '. ' : '') . ($data['note'] ?? 'Barang masuk manual'),
            ]);
        });

        return back()->with('success', 'Barang masuk berhasil dicatat dan stok bertambah.');
    }

    /**
     * Input Barang Keluar Manual (OUT)
     */
    public function manualOut(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'inventory_id' => ['required', 'exists:inventories,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'note' => ['required', 'string'],
        ]);

        $inventory = Inventory::findOrFail($data['inventory_id']);
        if ($inventory->current_stock < $data['quantity']) {
            return back()->with('error', 'Stok tidak mencukupi untuk pengeluaran barang ini. Stok saat ini: ' . $inventory->current_stock . ' ' . $inventory->unit);
        }

        DB::transaction(function () use ($inventory, $data) {
            $inventory->decrement('current_stock', $data['quantity']);

            InventoryTransaction::create([
                'inventory_id' => $inventory->id,
                'type' => 'OUT',
                'quantity' => -$data['quantity'],
                'reference' => 'MANUAL-OUT-' . now()->format('ymdHis'),
                'note' => $data['note'],
            ]);
        });

        return back()->with('success', 'Barang keluar manual berhasil dicatat.');
    }

    /**
     * Kelola Supplier
     */
    public function storeSupplier(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'contact' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'supplied_products' => ['nullable', 'string'],
        ]);

        Supplier::create($data);

        return back()->with('success', 'Supplier baru berhasil ditambahkan.');
    }

    public function deleteSupplier(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();
        return back()->with('success', 'Supplier berhasil dihapus.');
    }

    /**
     * Membuat Purchase Order (PO)
     */
    public function storePO(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array'],
            'items.*.inventory_id' => ['required', 'exists:inventories,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.price' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $poNumber = 'PO-' . now()->format('ymd') . '-' . str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT);
            
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            $po = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'po_number' => $poNumber,
                'status' => 'SENT', // Automate status to SENT
                'total_amount' => $totalAmount,
            ]);

            foreach ($data['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        });

        return back()->with('success', 'Purchase Order (PO) berhasil dibuat dan berstatus terkirim.');
    }

    /**
     * Konfirmasi Barang Datang (Receive PO)
     */
    public function receivePO(Request $request, PurchaseOrder $po): RedirectResponse
    {
        if ($po->status === 'COMPLETED' || $po->status === 'RECEIVED') {
            return back()->with('error', 'Purchase Order ini sudah diselesaikan sebelumnya.');
        }

        DB::transaction(function () use ($po) {
            $po->update(['status' => 'COMPLETED']);

            foreach ($po->items as $item) {
                $inventory = $item->inventory;
                $inventory->increment('current_stock', $item->quantity);

                // Log Transaction
                InventoryTransaction::create([
                    'inventory_id' => $inventory->id,
                    'type' => 'IN',
                    'quantity' => $item->quantity,
                    'reference' => $po->po_number,
                    'note' => 'Penerimaan barang PO Supplier: ' . $po->supplier->name,
                ]);
            }
        });

        return back()->with('success', 'Konfirmasi barang datang sukses. Stok inventory otomatis ditambahkan.');
    }

    /**
     * Kelola Stock Opname
     */
    public function storeOpname(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.inventory_id' => ['required', 'exists:inventories,id'],
            'items.*.physical_stock' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $soNumber = 'SO-' . now()->format('ymd') . '-' . str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT);
            
            $so = StockOpname::create([
                'opname_number' => $soNumber,
                'status' => 'ADJUSTED',
            ]);

            foreach ($data['items'] as $item) {
                $inventory = Inventory::findOrFail($item['inventory_id']);
                $systemStock = $inventory->current_stock;
                $physicalStock = $item['physical_stock'];
                $diff = $physicalStock - $systemStock;

                StockOpnameItem::create([
                    'stock_opname_id' => $so->id,
                    'inventory_id' => $inventory->id,
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $diff,
                ]);

                // Adjust Stock in system
                $inventory->update(['current_stock' => $physicalStock]);

                // Record Transaction if difference is non-zero
                if ($diff != 0) {
                    InventoryTransaction::create([
                        'inventory_id' => $inventory->id,
                        'type' => 'ADJUSTMENT',
                        'quantity' => $diff,
                        'reference' => $soNumber,
                        'note' => 'Penyesuaian hasil Stock Opname. Selisih: ' . ($diff > 0 ? '+' : '') . $diff . ' ' . $inventory->unit,
                    ]);
                }
            }
        });

        return back()->with('success', 'Stock Opname berhasil disimpan dan penyesuaian stok sistem telah dieksekusi.');
    }
}
