<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ScannerScan;
use App\Models\ScannerPairing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ScannerController extends Controller
{
    /**
     * View the mobile scanner page.
     */
    public function showScanner(Request $request): View
    {
        $code = $request->query('pair');
        $pairing = null;

        if ($code) {
            $pairing = ScannerPairing::where('pairing_code', $code)
                ->where('is_active', true)
                ->first();
            
            if ($pairing) {
                // Save pairing to session or cookie so the phone remains paired
                $request->session()->put('paired_scanner_code', $code);
            }
        } else {
            $sessionCode = $request->session()->get('paired_scanner_code');
            if ($sessionCode) {
                $pairing = ScannerPairing::where('pairing_code', $sessionCode)
                    ->where('is_active', true)
                    ->first();
            }
        }

        return view('pages.scanner', [
            'pairing' => $pairing,
            'pairingCode' => $code ?: $request->session()->get('paired_scanner_code'),
        ]);
    }

    /**
     * Submit a pairing code manually from mobile.
     */
    public function pair(Request $request): RedirectResponse
    {
        $request->validate([
            'pairing_code' => ['required', 'string']
        ]);

        $code = strtoupper($request->input('pairing_code'));

        $pairing = ScannerPairing::where('pairing_code', $code)
            ->where('is_active', true)
            ->first();

        if (!$pairing) {
            return back()->with('error', 'Kode pairing tidak valid atau sudah kedaluwarsa.');
        }

        $request->session()->put('paired_scanner_code', $code);

        return redirect()->route('scanner.index')->with('success', 'Scanner berhasil terhubung!');
    }

    /**
     * Unpair the mobile phone.
     */
    public function unpair(Request $request): RedirectResponse
    {
        $request->session()->forget('paired_scanner_code');
        return redirect()->route('scanner.index')->with('success', 'Scanner berhasil diputuskan.');
    }

    /**
     * Get active orders for the paired branch.
     */
    public function getActiveOrders(Request $request): JsonResponse
    {
        $sessionCode = $request->session()->get('paired_scanner_code');
        if (!$sessionCode) {
            return response()->json(['success' => false, 'message' => 'Scanner belum terhubung.'], 401);
        }

        $pairing = ScannerPairing::where('pairing_code', $sessionCode)
            ->where('is_active', true)
            ->first();

        if (!$pairing) {
            return response()->json(['success' => false, 'message' => 'Pairing tidak valid.'], 401);
        }

        $orders = Order::with('table')
            ->where('branch_id', $pairing->branch_id)
            ->where('status', 'WAITING_PAYMENT')
            ->latest()
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'invoice' => $o->invoice_number,
                'table' => $o->table ? $o->table->code : 'Tanpa Meja',
                'total' => 'Rp ' . number_format($o->total, 0, ',', '.'),
            ]);

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    /**
     * Process barcode scanned from the mobile phone.
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['required'],
            'barcode' => ['required', 'string']
        ]);

        $sessionCode = $request->session()->get('paired_scanner_code');
        if (!$sessionCode) {
            return response()->json(['success' => false, 'message' => 'Scanner belum terhubung.'], 401);
        }

        $pairing = ScannerPairing::where('pairing_code', $sessionCode)
            ->where('is_active', true)
            ->first();

        if (!$pairing) {
            return response()->json(['success' => false, 'message' => 'Pairing tidak aktif.'], 401);
        }

        $orderId = $request->input('order_id');
        $barcode = $request->input('barcode');
        $product = Product::where('barcode', $barcode)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk dengan barcode "' . $barcode . '" tidak ditemukan.'], 404);
        }

        if (!$product->is_available) {
            return response()->json(['success' => false, 'message' => 'Produk "' . $product->name . '" sedang tidak tersedia.'], 422);
        }

        // Create new order or use existing
        if ($orderId === 'new_order') {
            $invoiceNumber = 'INV-' . now()->format('ymd') . '-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $serviceFee = intval(\App\Models\Setting::where('key', 'service_fee')->first()?->value ?? 0);
            
            $order = Order::create([
                'branch_id' => $pairing->branch_id,
                'table_id' => null, // No table required!
                'customer_session_id' => null,
                'invoice_number' => $invoiceNumber,
                'status' => 'WAITING_PAYMENT',
                'subtotal' => 0,
                'service_fee' => $serviceFee,
                'total' => $serviceFee,
            ]);
        } else {
            $order = Order::find($orderId);
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
            }
            if ($order->branch_id !== $pairing->branch_id) {
                return response()->json(['success' => false, 'message' => 'Order tidak sesuai cabang scanner.'], 403);
            }
        }

        // Add or increment
        $orderItem = OrderItem::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->first();

        if ($orderItem) {
            $orderItem->increment('quantity');
        } else {
            OrderItem::create([
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
        $subtotal = OrderItem::where('order_id', $order->id)
            ->get()
            ->reduce(fn($sum, $item) => $sum + ($item->unit_price + $item->size_price) * $item->quantity, 0);

        $total = $subtotal + $order->service_fee;

        $order->update([
            'subtotal' => $subtotal,
            'total' => $total
        ]);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'message' => 'Produk "' . $product->name . '" berhasil ditambahkan ke ' . $order->invoice_number
        ]);
    }

    /**
     * Buffer a barcode scan for the POS cart (no order created yet).
     * The mobile phone calls this instead of scan() to just buffer the product.
     */
    public function scanBuffer(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => ['required', 'string']
        ]);

        $sessionCode = $request->session()->get('paired_scanner_code');
        if (!$sessionCode) {
            return response()->json(['success' => false, 'message' => 'Scanner belum terhubung.'], 401);
        }

        $pairing = ScannerPairing::where('pairing_code', $sessionCode)
            ->where('is_active', true)
            ->first();

        if (!$pairing) {
            return response()->json(['success' => false, 'message' => 'Pairing tidak aktif.'], 401);
        }

        $barcode = $request->input('barcode');
        $product = Product::where('barcode', $barcode)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk dengan barcode "' . $barcode . '" tidak ditemukan.'], 404);
        }

        if (!$product->is_available) {
            return response()->json(['success' => false, 'message' => 'Produk "' . $product->name . '" sedang tidak tersedia.'], 422);
        }

        // Buffer the scan - PC cashier will pick it up via polling
        ScannerScan::create([
            'pairing_code' => $sessionCode,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'is_processed' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk "' . $product->name . '" berhasil dikirim ke kasir!',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'barcode' => $product->barcode,
            ]
        ]);
    }

    /**
     * Generate pairing code for the cashier view.
     */
    public function generateCode(Request $request): JsonResponse
    {
        $user = $request->session()->get('auth_user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $branchId = $user['branch_id'] ?? Branch::first()?->id;
        if (!$branchId) {
            return response()->json(['success' => false, 'message' => 'Cabang tidak diidentifikasi.'], 400);
        }

        // Disable existing pairings for this user
        ScannerPairing::where('user_id', $user['id'])->update(['is_active' => false]);

        // Generate clean random code
        $code = rand(100000, 999999);

        $pairing = ScannerPairing::create([
            'pairing_code' => $code,
            'branch_id' => $branchId,
            'user_id' => $user['id'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'pairing_code' => $code,
            'url' => route('scanner.index', ['pair' => $code])
        ]);
    }

    /**
     * Check if a phone has paired with this cashier's code.
     * Also returns any unprocessed scanned items for the POS cart.
     */
    public function checkStatus(Request $request): JsonResponse
    {
        $code = $request->query('code');
        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Code missing'], 400);
        }

        $pairing = ScannerPairing::where('pairing_code', $code)
            ->where('is_active', true)
            ->first();

        // Fetch unprocessed scans for this pairing code
        $pendingScans = [];
        if ($pairing) {
            $scans = ScannerScan::where('pairing_code', $code)
                ->where('is_processed', false)
                ->orderBy('created_at', 'asc')
                ->get();

            $pendingScans = $scans->map(fn($s) => [
                'id' => $s->id,
                'product_id' => $s->product_id,
                'product_name' => $s->product_name,
                'product_price' => (float) $s->product_price,
            ])->toArray();

            // Mark them as processed immediately
            if ($scans->isNotEmpty()) {
                ScannerScan::whereIn('id', $scans->pluck('id'))->update(['is_processed' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'paired' => $pairing !== null,
            'pending_scans' => $pendingScans,
            'last_order_update' => null,
        ]);
    }
}
