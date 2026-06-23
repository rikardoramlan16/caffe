<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Category;
use App\Models\CafeTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemTopping;
use App\Models\Payment;
use App\Models\TableTransfer;
use App\Models\CustomerSession;
use App\Models\InventoryTransaction;
use App\Models\MenuRecipe;
use App\Models\Topping;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function publicPreview(Request $request)
    {
        $tableCode = $request->session()->get('customer_table_code');
        if ($tableCode) {
            return redirect()->route('qr.login', ['code' => $tableCode]);
        }

        $categories = Category::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $menuItems = Menu::with(['category', 'toppings' => fn ($query) => $query->where('is_available', true)])
            ->where('is_available', true)
            ->get();

        return view('pages.menu-preview', [
            'categories' => $categories,
            'menu' => $menuItems,
        ]);
    }

    /**
     * QR Ordering Pelanggan
     */
    public function qrLogin(Request $request, string $code)
    {
        $table = CafeTable::where('code', $code)->first();
        if (! $table) {
            return redirect()->route('menu.preview')->with('error', 'Meja tidak ditemukan.');
        }

        // Save table id and code in session
        $request->session()->put('customer_table_id', $table->id);
        $request->session()->put('customer_table_code', $table->code);

        // Generate / Retrieve customer_token
        $token = $request->cookie('customer_token') 
            ?? $request->session()->get('customer_token') 
            ?? $request->input('customer_token');

        if (! $token) {
            $token = 'CF-' . strtoupper(Str::random(6)) . '-' . strtoupper(Str::random(5));
            $request->session()->put('customer_token', $token);
            // Save in cookie for 30 days
            cookie()->queue('customer_token', $token, 60 * 24 * 30);
        } else {
            $request->session()->put('customer_token', $token);
        }

        // Ensure session record exists
        $sessionRecord = CustomerSession::firstOrCreate(
            ['customer_token' => $token],
            ['branch_id' => $table->branch_id, 'table_id' => $table->id, 'last_seen_at' => now()]
        );
        $sessionRecord->update(['table_id' => $table->id, 'last_seen_at' => now()]);

        // Find active order for this token
        $activeOrder = Order::with('table')
            ->where('customer_session_id', $sessionRecord->id)
            ->whereNotIn('status', ['DONE', 'CANCEL'])
            ->latest()
            ->first();

        // Check if there is an active order and table is different -> triggers Pindah Meja popup
        $showTransferPopup = false;
        $oldTableCode = '';
        if ($activeOrder && $activeOrder->table_id !== $table->id) {
            $showTransferPopup = true;
            $oldTableCode = $activeOrder->table->code;
        }

        $categories = Category::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $menuItems = Menu::with(['category', 'toppings' => fn ($query) => $query->where('is_available', true)])
            ->where('is_available', true)
            ->get();

        return view('pages.menu-order', [
            'table' => $table,
            'token' => $token,
            'activeOrder' => $activeOrder,
            'showTransferPopup' => $showTransferPopup,
            'oldTableCode' => $oldTableCode,
            'categories' => $categories,
            'menu' => $menuItems,
        ]);
    }

    /**
     * Pindah Meja
     */
    public function transferTable(Request $request): JsonResponse
    {
        $token = $request->session()->get('customer_token');
        $newTableId = $request->session()->get('customer_table_id');

        if (! $token || ! $newTableId) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid.'], 400);
        }

        $sessionRecord = CustomerSession::where('customer_token', $token)->first();
        if (! $sessionRecord) {
            return response()->json(['success' => false, 'message' => 'Sesi pelanggan tidak ditemukan.'], 400);
        }

        $activeOrder = Order::where('customer_session_id', $sessionRecord->id)
            ->whereNotIn('status', ['DONE', 'CANCEL'])
            ->latest()
            ->first();

        if (! $activeOrder) {
            return response()->json(['success' => false, 'message' => 'Tidak ada pesanan aktif.'], 400);
        }

        $fromTableId = $activeOrder->table_id;
        if ($fromTableId === $newTableId) {
            return response()->json(['success' => true]);
        }

        // Update table in order
        $activeOrder->update(['table_id' => $newTableId]);

        // Record transfer
        TableTransfer::create([
            'order_id' => $activeOrder->id,
            'from_table_id' => $fromTableId,
            'to_table_id' => $newTableId,
            'moved_by_user_id' => null, // moved by customer QR scan
            'reason' => 'Pelanggan pindah meja via scan QR baru',
        ]);

        // Update table in session record
        $sessionRecord->update(['table_id' => $newTableId]);

        return response()->json(['success' => true]);
    }

    /**
     * Halaman Cart
     */
    public function viewCart(Request $request): View
    {
        return view('pages.cart');
    }

    /**
     * Halaman Checkout
     */
    public function viewCheckout(Request $request)
    {
        $tableId = $request->session()->get('customer_table_id');
        $table = CafeTable::find($tableId);

        if (! $table) {
            return redirect()->route('menu.preview')->with('error', 'Silakan pilih meja terlebih dahulu.');
        }

        // Generate queue number
        $todayCount = Order::whereDate('created_at', today())->count();
        $queueNumber = 'CF-' . str_pad((string)($todayCount + 1), 3, '0', STR_PAD_LEFT);

        return view('pages.checkout', [
            'table' => $table,
            'queueNumber' => $queueNumber,
        ]);
    }

    /**
     * Process Checkout
     */
    public function processCheckout(Request $request): JsonResponse
    {
        $cart = $request->input('cart', []);
        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Keranjang belanja kosong.'], 400);
        }

        $tableId = $request->session()->get('customer_table_id');
        $table = CafeTable::find($tableId);

        if (! $table) {
            return response()->json(['success' => false, 'message' => 'Nomor meja belum teridentifikasi.'], 400);
        }

        $token = $request->session()->get('customer_token');
        $sessionRecord = CustomerSession::where('customer_token', $token)->first();

        if (! $sessionRecord) {
            $sessionRecord = CustomerSession::create([
                'branch_id' => $table->branch_id,
                'table_id' => $table->id,
                'customer_token' => $token ?? 'CF-' . strtoupper(Str::random(6)) . '-' . strtoupper(Str::random(5)),
                'last_seen_at' => now(),
            ]);
        }

        $normalizedCart = [];
        $subtotal = 0;
        $sizePrices = ['Regular' => 0, 'Large' => 3000];
        $sugarLevels = ['0%', '25%', '50%', '75%', '100%'];
        $iceLevels = ['Tanpa Es', 'Sedikit Es', 'Normal'];

        foreach ($cart as $item) {
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $menu = Menu::with(['toppings' => fn ($query) => $query->where('is_available', true)])
                ->where('is_available', true)
                ->find($item['id'] ?? null);

            if (! $menu) {
                return response()->json(['success' => false, 'message' => 'Menu tidak tersedia.'], 422);
            }

            $size = in_array(($item['size'] ?? 'Regular'), array_keys($sizePrices), true) ? $item['size'] : 'Regular';
            $sugarLevel = in_array(($item['sugar_level'] ?? '100%'), $sugarLevels, true) ? $item['sugar_level'] : '100%';
            $iceLevel = in_array(($item['ice_level'] ?? 'Normal'), $iceLevels, true) ? $item['ice_level'] : 'Normal';

            $requestedToppingIds = collect($item['toppings'] ?? [])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $allowedToppings = $menu->toppings->whereIn('id', $requestedToppingIds)->values();
            if ($requestedToppingIds->count() !== $allowedToppings->count()) {
                return response()->json(['success' => false, 'message' => 'Ada topping yang tidak valid untuk produk ini.'], 422);
            }

            $unitPrice = (int) $menu->price + $sizePrices[$size] + (int) $allowedToppings->sum('price');
            $subtotal += $unitPrice * $quantity;

            $normalizedCart[] = [
                'menu' => $menu,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'size' => $size,
                'size_price' => $sizePrices[$size],
                'sugar_level' => $sugarLevel,
                'ice_level' => $iceLevel,
                'note' => $item['note'] ?? null,
                'toppings' => $allowedToppings,
            ];
        }

        $serviceFee = intval(\App\Models\Setting::where('key', 'service_fee')->first()?->value ?? 0);
        $total = $subtotal + $serviceFee;

        $invoiceNumber = 'INV-' . now()->format('ymd') . '-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Begin Transaction
        DB::beginTransaction();
        try {
            // Create Order
            $order = Order::create([
                'branch_id' => $table->branch_id,
                'table_id' => $table->id,
                'customer_session_id' => $sessionRecord->id,
                'invoice_number' => $invoiceNumber,
                'status' => 'WAITING_PAYMENT',
                'subtotal' => $subtotal,
                'service_fee' => $serviceFee,
                'total' => $total,
                'customer_note' => $request->input('note'),
            ]);

            // Create Order Items and Toppings
            foreach ($normalizedCart as $cartItem) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $cartItem['menu']->id,
                    'quantity' => $cartItem['quantity'],
                    'unit_price' => $cartItem['unit_price'],
                    'size' => $cartItem['size'],
                    'size_price' => $cartItem['size_price'],
                    'sugar_level' => $cartItem['sugar_level'],
                    'ice_level' => $cartItem['ice_level'],
                    'note' => $cartItem['note'],
                ]);

                foreach ($cartItem['toppings'] as $topping) {
                    OrderItemTopping::create([
                        'order_item_id' => $orderItem->id,
                        'topping_id' => $topping->id,
                        'price' => $topping->price,
                    ]);
                }
            }

            // Create Payment Request
            Payment::create([
                'order_id' => $order->id,
                'method' => $request->input('payment_method', 'QRIS'),
                'status' => 'waiting',
                'amount' => $total,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'invoice' => $invoiceNumber,
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Halaman Utama Pelanggan (stateful)
     */
    public function customer(Request $request, string $state = 'menu'): View
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $menuItems = Menu::with(['category', 'toppings' => fn ($query) => $query->where('is_available', true)])
            ->where('is_available', true)
            ->get();

        return view('pages.customer', [
            'state' => $state,
            'menu' => $menuItems,
            'categories' => $categories,
        ]);
    }

    /**
     * Halaman Status Pesanan
     */
    public function orderStatus(Request $request): View
    {
        return view('pages.order-status');
    }
}
