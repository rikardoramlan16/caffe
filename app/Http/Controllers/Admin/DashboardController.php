<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\CafeTable;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SystemLog;
use App\Models\Topping;
use App\Models\User;
use App\Models\Payroll;
use App\Models\Expense;
use App\Models\Bonus;
use App\Models\DeletionApproval;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function resolveBranchId(Request $request): ?int
    {
        $user = $request->session()->get('auth_user');
        if (! $user) {
            return null;
        }

        $branchId = $user['branch_id'] ?? null;
        if ($branchId) {
            return (int) $branchId;
        }

        if (! empty($user['id'])) {
            $dbBranchId = (int) (User::find($user['id'])?->branch_id ?? 0);
            if ($dbBranchId) {
                return $dbBranchId;
            }
        }

        return (int) (\App\Models\Branch::first()?->id ?? 0) ?: null;
    }

    /**
     * Dashboard Super Admin
     */
    public function superAdmin(Request $request, string $section = 'dashboard'): View
    {
        $user = $request->session()->get('auth_user');

        $totalUsers = User::count();
        $totalTransactions = Order::count();
        $totalRevenue = Order::whereIn('status', ['PAID', 'MAKING', 'READY', 'DONE'])->sum('total');
        
        $activities = ActivityLog::with('user')->latest()->take(8)->get();
        $systemLogs = SystemLog::latest()->take(8)->get();
        $roles = Role::with(['permissions'])->withCount('users')->get();
        $permissions = Permission::orderBy('label')->get();
        $users = User::with('roleModel')->latest()->get();
        $settings = Setting::orderBy('group')->orderBy('key')->get()->keyBy('key');
        $inventoryStats = [
            'items' => Inventory::count(),
            'low_stock' => Inventory::get()->filter(fn ($item) => $item->current_stock <= $item->min_stock && $item->current_stock > 0)->count(),
            'out_of_stock' => Inventory::where('current_stock', '<=', 0)->count(),
        ];
        $orderStatusCounts = Order::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Penjualan Bulanan (Bulanan)
        $monthlySales = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as revenue')
        )
        ->whereIn('status', ['PAID', 'MAKING', 'READY', 'DONE'])
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get()
        ->pluck('revenue', 'month')
        ->all();

        $monthlyChartData = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyChartData[$monthNames[$i - 1]] = $monthlySales[$i] ?? 0;
        }

        // Aktivitas User
        $activeUsers = User::withCount('activityLogs')->get();

        return view('pages.dashboard.super-admin', [
            'user' => $user,
            'metrics' => [
                'users' => $totalUsers,
                'transactions' => $totalTransactions,
                'revenue' => $totalRevenue,
            ],
            'activities' => $activities,
            'systemLogs' => $systemLogs,
            'roles' => $roles,
            'permissions' => $permissions,
            'users' => $users,
            'settings' => $settings,
            'inventoryStats' => $inventoryStats,
            'orderStatusCounts' => $orderStatusCounts,
            'monthlyChartData' => $monthlyChartData,
            'activeUsers' => $activeUsers,
            'section' => $section,
        ]);
    }

    public function storeSuperAdminUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $role = Role::findOrFail($data['role_id']);

        User::create($data + ['role' => $role->name]);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function updateSuperAdminUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email,' . $user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $role = Role::findOrFail($data['role_id']);
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data + ['role' => $role->name]);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroySuperAdminUser(Request $request, User $user): RedirectResponse
    {
        if (($request->session()->get('auth_user.id')) === $user->id) {
            return back()->with('error', 'User aktif tidak dapat dihapus.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }

    public function storeSuperAdminRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:roles,name'],
            'label' => ['required', 'string', 'max:120'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $permissionIds = $data['permission_ids'] ?? [];
        unset($data['permission_ids']);

        $role = Role::create($data);
        $role->permissions()->sync($permissionIds);

        return back()->with('success', 'Role berhasil ditambahkan.');
    }

    public function updateSuperAdminRolePermissions(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->sync($data['permission_ids'] ?? []);

        return back()->with('success', 'Permission role berhasil diperbarui.');
    }

    public function destroySuperAdminRole(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return back()->with('error', 'Role masih digunakan user.');
        }

        $role->delete();

        return back()->with('success', 'Role berhasil dihapus.');
    }

    public function storeSuperAdminPermission(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:permissions,name'],
            'label' => ['required', 'string', 'max:120'],
        ]);

        Permission::create($data);

        return back()->with('success', 'Permission berhasil ditambahkan.');
    }

    public function destroySuperAdminPermission(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return back()->with('success', 'Permission berhasil dihapus.');
    }

    public function updateSuperAdminSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:255'],
            'app_logo' => ['nullable', 'image', 'max:2048'], // Maksimum 2MB
        ]);

        foreach ($data['settings'] as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => str_starts_with($key, 'company_') ? 'company' : 'app']
            );
        }

        if ($request->hasFile('app_logo')) {
            $file = $request->file('app_logo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('logos'), $filename);
            $path = 'logos/' . $filename;
            
            // Delete old logo file if it exists in public directory
            $oldSetting = Setting::where('key', 'app_logo')->first();
            if ($oldSetting && $oldSetting->value) {
                $oldPath = public_path($oldSetting->value);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            Setting::updateOrCreate(
                ['key' => 'app_logo'],
                ['value' => $path, 'group' => 'app']
            );
        }

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }

    /**
     * Dashboard Admin
     */
    public function admin(Request $request, string $section = 'dashboard'): View
    {
        $user = $request->session()->get('auth_user');
        $branchId = $this->resolveBranchId($request);

        $totalOrdersToday = Order::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->count();

        $revenueToday = Order::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->whereIn('status', ['PAID', 'MAKING', 'READY', 'DONE'])
            ->sum('total');

        // Menu terlaris
        $bestSellingProduct = DB::table('order_items')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('menus.name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->where('orders.branch_id', $branchId)
            ->whereIn('orders.status', ['PAID', 'MAKING', 'READY', 'DONE'])
            ->groupBy('menus.id', 'menus.name')
            ->orderByDesc('total_qty')
            ->first();

        $bestSellingName = $bestSellingProduct ? $bestSellingProduct->name : 'Tidak ada data';
        $bestSellingQty = $bestSellingProduct ? $bestSellingProduct->total_qty : 0;

        $activeOrdersCount = Order::where('branch_id', $branchId)
            ->whereIn('status', ['PAID', 'MAKING', 'READY'])
            ->count();

        // Penjualan Harian (7 hari terakhir)
        $dailySales = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as revenue')
        )
        ->where('branch_id', $branchId)
        ->whereIn('status', ['PAID', 'MAKING', 'READY', 'DONE'])
        ->where('created_at', '>=', now()->subDays(6)->startOfDay())
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get()
        ->pluck('revenue', 'date')
        ->all();

        $dailyChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->translatedFormat('l'); // translated name of the day
            $dailyChartData[$label] = $dailySales[$date] ?? 0;
        }

        // Produk Terlaris untuk grafik/list
        $topProducts = DB::table('order_items')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('menus.name', 'menus.price', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_sales'))
            ->where('orders.branch_id', $branchId)
            ->whereIn('orders.status', ['PAID', 'MAKING', 'READY', 'DONE'])
            ->groupBy('menus.id', 'menus.name', 'menus.price')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $menus = Menu::with('category')->where('branch_id', $branchId)->latest()->get();
        $categories = Category::where('branch_id', $branchId)->orderBy('sort_order', 'asc')->get();
        $toppings = Topping::where('branch_id', $branchId)->get();
        $tables = CafeTable::where('branch_id', $branchId)->orderBy('code', 'asc')->get();
        $staff = User::where('branch_id', $branchId)->where('role', '!=', 'admin')->get();

        return view('pages.dashboard.admin', [
            'user' => $user,
            'metrics' => [
                'orders_today' => $totalOrdersToday,
                'revenue_today' => $revenueToday,
                'best_selling' => $bestSellingName . ' (' . $bestSellingQty . ' x)',
                'active_orders' => $activeOrdersCount,
            ],
            'dailyChartData' => $dailyChartData,
            'topProducts' => $topProducts,
            'menus' => $menus,
            'categories' => $categories,
            'toppings' => $toppings,
            'tables' => $tables,
            'staff' => $staff,
            'section' => $section,
        ]);
    }

    /**
     * Dashboard Kasir
     */
    public function cashier(Request $request, string $section = 'dashboard'): View
    {
        $user = $request->session()->get('auth_user');
        $branchId = $this->resolveBranchId($request);

        $waitingPayment = Order::where('branch_id', $branchId)
            ->where('status', 'WAITING_PAYMENT')
            ->count();

        $orderToday = Order::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->count();

        $revenueToday = Order::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->whereIn('status', ['PAID', 'MAKING', 'READY', 'DONE'])
            ->sum('total');

        $totalTransactions = Order::where('branch_id', $branchId)
            ->whereIn('status', ['PAID', 'MAKING', 'READY', 'DONE'])->count();

        $tables = CafeTable::where('branch_id', $branchId)->orderBy('code', 'asc')->get();

        $newOrders = Order::with(['table', 'items.menu', 'items.product', 'items.toppings.topping'])
            ->where('branch_id', $branchId)
            ->where('status', 'WAITING_PAYMENT')
            ->latest()
            ->get();

        $payments = Payment::with('order.table')
            ->whereHas('order', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->where('status', 'paid')
            ->latest()
            ->take(15)
            ->get();

        $products = [];
        $menus = [];
        if ($section === 'kelola-barang') {
            $products = Product::where('branch_id', $branchId)->latest()->get();
        } elseif ($section === 'pembayaran' || $section === 'order-masuk') {
            $products = Product::where('branch_id', $branchId)->where('is_available', true)->latest()->get();
            $menus = Menu::where('is_available', true)->latest()->get();
        }

        return view('pages.dashboard.cashier', [
            'user' => $user,
            'metrics' => [
                'waiting_payment' => $waitingPayment,
                'orders_today' => $orderToday,
                'revenue_today' => $revenueToday,
                'total_transactions' => $totalTransactions,
            ],
            'tables' => $tables,
            'newOrders' => $newOrders,
            'payments' => $payments,
            'section' => $section,
            'products' => $products,
            'menus' => $menus,
        ]);
    }

    /**
     * Live waiting-payment orders for cashier tab refresh
     */
    public function waitingOrders(Request $request): JsonResponse
    {
        $user = $request->session()->get('auth_user');
        $branchId = $this->resolveBranchId($request);

        $orders = Order::with(['table', 'items.menu', 'items.product'])
            ->where('branch_id', $branchId)
            ->where('status', 'WAITING_PAYMENT')
            ->latest()
            ->get()
            ->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'invoice_number' => $order->invoice_number,
                    'table_code' => $order->table ? $order->table->code : null,
                    'table_label' => $order->table ? 'Meja ' . $order->table->code : 'Tanpa Meja (Direct)',
                    'total' => (int) $order->total,
                    'customer_note' => $order->customer_note,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'name' => $item->menu ? $item->menu->name : ($item->product ? $item->product->name : 'Item'),
                            'quantity' => (int) $item->quantity,
                            'unit_price' => (int) $item->unit_price,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'count' => $orders->count(),
            'orders' => $orders,
        ]);
    }

    /**
     * Dashboard Barista
     */
    public function barista(Request $request, string $section = 'dashboard'): View
    {
        $user = $request->session()->get('auth_user');
        $branchId = $this->resolveBranchId($request);

        $queueActive = Order::where('branch_id', $branchId)
            ->where('status', 'PAID')
            ->count();

        $making = Order::where('branch_id', $branchId)
            ->where('status', 'MAKING')
            ->count();

        $ready = Order::where('branch_id', $branchId)
            ->where('status', 'READY')
            ->count();

        // Total Minuman Hari Ini (Kecuali item Pastry)
        $totalDrinksToday = DB::table('order_items')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')
            ->join('categories', 'menus.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->whereDate('orders.created_at', today())
            ->whereIn('orders.status', ['PAID', 'MAKING', 'READY', 'DONE'])
            ->where('categories.name', '!=', 'Pastry')
            ->sum('order_items.quantity');

        $paidOrders = Order::with(['table', 'items.menu', 'items.toppings.topping'])
            ->where('branch_id', $branchId)
            ->where('status', 'PAID')
            ->orderBy('created_at', 'asc')
            ->get();

        $makingOrders = Order::with(['table', 'items.menu', 'items.toppings.topping'])
            ->where('branch_id', $branchId)
            ->where('status', 'MAKING')
            ->orderBy('updated_at', 'asc')
            ->get();

        $doneOrders = Order::with(['table', 'items.menu'])
            ->where('branch_id', $branchId)
            ->whereDate('updated_at', today())
            ->where('status', 'DONE')
            ->latest()
            ->take(15)
            ->get();

        return view('pages.dashboard.barista', [
            'user' => $user,
            'metrics' => [
                'queue_active' => $queueActive,
                'making' => $making,
                'ready' => $ready,
                'drinks_today' => $totalDrinksToday,
            ],
            'paidOrders' => $paidOrders,
            'makingOrders' => $makingOrders,
            'doneOrders' => $doneOrders,
            'section' => $section,
        ]);
    }

    /**
     * Dashboard Owner
     */
    public function owner(Request $request, string $section = 'dashboard'): View
    {
        $user = $request->session()->get('auth_user');

        // Widgets
        $totalRevenueToday = Order::whereDate('created_at', today())
            ->whereIn('status', ['PAID', 'MAKING', 'READY', 'DONE'])
            ->sum('total');

        $totalRevenueThisMonth = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['PAID', 'MAKING', 'READY', 'DONE'])
            ->sum('total');

        $approvedExpensesThisMonth = Expense::where('status', 'APPROVED')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $approvedPayrollThisMonth = Payroll::where('status', 'APPROVED')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_salary');

        // Laba bersih = total omset bulan ini - pengeluaran bulan ini - payroll bulan ini
        $netProfit = $totalRevenueThisMonth - $approvedExpensesThisMonth - $approvedPayrollThisMonth;

        $totalOrders = Order::count();
        $totalEmployees = User::where('role', '!=', 'owner')->count();
        // Produk Terlaris
        $bestSellingProduct = DB::table('order_items')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('menus.name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->whereIn('orders.status', ['PAID', 'MAKING', 'READY', 'DONE'])
            ->groupBy('menus.id', 'menus.name')
            ->orderByDesc('total_qty')
            ->first();

        $bestSellingName = $bestSellingProduct ? $bestSellingProduct->name : 'Tidak ada data';
        $bestSellingQty = $bestSellingProduct ? $bestSellingProduct->total_qty : 0;

        // Inventory Widgets
        $totalInventoryItems = Inventory::count();
        $lowStockCount = Inventory::get()->filter(fn($item) => $item->current_stock <= $item->min_stock && $item->current_stock > 0)->count();
        $outOfStockCount = Inventory::where('current_stock', '<=', 0)->count();
        $totalPurchaseThisMonth = PurchaseOrder::where('status', 'COMPLETED')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        $activeSupplierCount = Supplier::count();

        // 1. Grafik Penjualan Bulanan (2026)
        $monthlySales = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as revenue')
        )
        ->whereIn('status', ['PAID', 'MAKING', 'READY', 'DONE'])
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get()
        ->pluck('revenue', 'month')
        ->all();

        $monthlyChartData = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyChartData[$monthNames[$i - 1]] = $monthlySales[$i] ?? 0;
        }

        // 2. Grafik Pendapatan Harian (7 hari terakhir)
        $dailySales = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as revenue')
        )
        ->whereIn('status', ['PAID', 'MAKING', 'READY', 'DONE'])
        ->where('created_at', '>=', now()->subDays(6)->startOfDay())
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get()
        ->pluck('revenue', 'date')
        ->all();

        $dailyChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->translatedFormat('l');
            $dailyChartData[$label] = $dailySales[$date] ?? 0;
        }

        // 3. Grafik Produk Terlaris (Top 5)
        $topProducts = DB::table('order_items')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('menus.name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->whereIn('orders.status', ['PAID', 'MAKING', 'READY', 'DONE'])
            ->groupBy('menus.id', 'menus.name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // 4. Grafik Pemakaian Stok Bulanan (OUT)
        $stockUsage = DB::table('inventory_transactions')
            ->join('inventories', 'inventory_transactions.inventory_id', '=', 'inventories.id')
            ->select('inventories.name', DB::raw('SUM(ABS(inventory_transactions.quantity)) as total_usage'))
            ->where('inventory_transactions.type', 'OUT')
            ->groupBy('inventories.id', 'inventories.name')
            ->orderByDesc('total_usage')
            ->take(5)
            ->get();

        // 5. Grafik Pembelian Bulanan
        $monthlyPurchases = PurchaseOrder::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_amount) as amount')
        )
        ->where('status', 'COMPLETED')
        ->groupBy('month')
        ->get()
        ->pluck('amount', 'month')
        ->all();

        $purchaseChartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $purchaseChartData[$monthNames[$i - 1]] = $monthlyPurchases[$i] ?? 0;
        }

        // Approval Lists
        $payrolls = Payroll::with('user')->latest()->get();
        $expenses = Expense::with('branch')->latest()->get();
        $bonuses = Bonus::with('user')->latest()->get();
        $deletions = DeletionApproval::with('requester')->latest()->get();

        // Operational read-only lists
        $orders = Order::with('table')->latest()->take(20)->get();
        $menus = Menu::with('category')->get();
        $inventories = Inventory::with('category')->get();
        $suppliers = Supplier::all();
        $transactions = InventoryTransaction::with('inventory')->latest()->take(20)->get();

        // Check for Low Stock Warning Banner
        $lowStockWarnings = Inventory::where('current_stock', '<=', DB::raw('min_stock'))->get();

        return view('pages.dashboard.owner', [
            'user' => $user,
            'metrics' => [
                'revenue_today' => $totalRevenueToday,
                'revenue_month' => $totalRevenueThisMonth,
                'net_profit' => $netProfit,
                'total_orders' => $totalOrders,
                'total_employees' => $totalEmployees,
                'best_selling' => $bestSellingName . ' (' . $bestSellingQty . ' x)',
                'total_items' => $totalInventoryItems,
                'low_stock' => $lowStockCount,
                'out_of_stock' => $outOfStockCount,
                'purchase_month' => $totalPurchaseThisMonth,
                'active_suppliers' => $activeSupplierCount,
            ],
            'monthlyChartData' => $monthlyChartData,
            'dailyChartData' => $dailyChartData,
            'topProducts' => $topProducts,
            'stockUsage' => $stockUsage,
            'purchaseChartData' => $purchaseChartData,
            'payrolls' => $payrolls,
            'expenses' => $expenses,
            'bonuses' => $bonuses,
            'deletions' => $deletions,
            'orders' => $orders,
            'menus' => $menus,
            'inventories' => $inventories,
            'suppliers' => $suppliers,
            'transactions' => $transactions,
            'lowStockWarnings' => $lowStockWarnings,
            'section' => $section,
        ]);
    }
}
