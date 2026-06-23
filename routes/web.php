<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderActionController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\StaffPortalController;
use App\Http\Controllers\Admin\TableQrCodeController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Support\CafeDemoData;
use Illuminate\Support\Facades\Route;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CafeTable;
use App\Models\Payroll;
use App\Models\Expense;
use App\Models\DeletionApproval;
use App\Models\EmployeeBonus;
use App\Models\EmployeeDeduction;
use App\Models\EmployeeLeave;
use Illuminate\Http\Request;

// 1. Public & Landing Routes
Route::get('/', function () {
    return view('pages.landing', [
        'metrics' => CafeDemoData::metrics(),
        'activities' => CafeDemoData::activities(),
        'orders' => CafeDemoData::orders(),
        'dashboards' => CafeDemoData::dashboards(),
        'flow' => CafeDemoData::userFlow(),
    ]);
})->name('landing');

Route::get('/menu', [CustomerOrderController::class, 'publicPreview'])->name('menu.preview');

Route::get('/tentang', function () {
    return view('pages.about');
})->name('about');

Route::get('/kontak', function () {
    return view('pages.contact');
})->name('contact');

// 2. Authentication Routes for Staff
Route::get('/staff/login', [AuthController::class, 'login'])->name('login');
Route::post('/staff/login', [AuthController::class, 'authenticate']);
Route::any('/staff/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Customer QR Flow Routes
Route::get('/customer/{state?}', [CustomerOrderController::class, 'customer'])->name('customer');
Route::get('/qr/{code}', [CustomerOrderController::class, 'qrLogin'])->name('qr.login');
Route::post('/qr/transfer', [CustomerOrderController::class, 'transferTable'])->name('qr.transfer');
Route::get('/cart', [CustomerOrderController::class, 'viewCart'])->name('cart.view');
Route::get('/checkout', [CustomerOrderController::class, 'viewCheckout'])->name('checkout.view');
Route::post('/customer/checkout', [CustomerOrderController::class, 'processCheckout'])->name('customer.checkout');

// Polling order status for live tracking
Route::get('/customer/order-status/{invoice}', function (string $invoice) {
    $order = Order::where('invoice_number', $invoice)->first();
    return response()->json([
        'status' => $order ? $order->status : 'NOT_FOUND'
    ]);
})->name('customer.order-status');

Route::get('/order/status', [CustomerOrderController::class, 'orderStatus'])->name('order.status');

// 4. Owner Dashboard & Approvals
Route::middleware('role:owner')->group(function () {
    Route::get('/dashboard/owner', [DashboardController::class, 'owner'])->name('dashboard.owner');
    Route::get('/dashboard/owner/{section}', [DashboardController::class, 'owner'])->name('dashboard.owner.section')->where('section', 'penjualan|keuangan|analitik|approval|penggajian');
    
    // Payroll Approval Actions
    Route::post('/dashboard/owner/payroll/{payroll}/status', [PayrollController::class, 'updateStatus'])->name('owner.payroll.status');

    // Print Salary Slip
    Route::get('/dashboard/owner/payroll/{payroll}/slip', [PayrollController::class, 'slip'])->name('owner.payroll.slip');

    // Owner specific approvals
    Route::post('/dashboard/owner/bonus-req/{bonus}/approve', [PayrollController::class, 'approveBonus'])->name('owner.bonus.approve');
    Route::post('/dashboard/owner/deduction-req/{deduction}/approve', [PayrollController::class, 'approveDeduction'])->name('owner.deduction.approve');
    Route::post('/dashboard/owner/leave-req/{leave}/approve', [PayrollController::class, 'approveLeave'])->name('owner.leave.approve');

    // Expense Approval Actions
    Route::post('/dashboard/owner/expense/{expense}/status', function (Request $request, Expense $expense) {
        $data = $request->validate(['status' => ['required', 'in:APPROVED,REJECTED']]);
        $expense->update(['status' => $data['status']]);
        return back()->with('success', 'Status pengeluaran operasional berhasil diperbarui.');
    })->name('owner.expense.status');

    // Deletion Approval Actions
    Route::post('/dashboard/owner/deletion/{deletion}/status', function (Request $request, DeletionApproval $deletion) {
        $data = $request->validate(['status' => ['required', 'in:APPROVED,REJECTED']]);
        
        DB::transaction(function () use ($deletion, $data) {
            $deletion->update(['status' => $data['status']]);
            
            // If approved, execute the deletion of the target record!
            if ($data['status'] === 'APPROVED') {
                DB::table($deletion->table_name)->where('id', $deletion->record_id)->delete();
            }
        });

        return back()->with('success', 'Status pengajuan penghapusan data berhasil diperbarui.');
    })->name('owner.deletion.status');
});

// 5. Shared HR & Payroll Management (Owner, Super Admin, Admin)
Route::middleware('role:owner,super_admin,admin')->group(function () {
    // Employees Management
    Route::get('/dashboard/owner/employees', [EmployeeController::class, 'index'])->name('owner.employees');
    Route::post('/dashboard/owner/employees', [EmployeeController::class, 'store'])->name('owner.employees.store');
    Route::post('/dashboard/owner/employees/{employee}/update', [EmployeeController::class, 'update'])->name('owner.employees.update');
    Route::delete('/dashboard/owner/employees/{employee}', [EmployeeController::class, 'destroy'])->name('owner.employees.destroy');

    // Attendance Management
    Route::get('/dashboard/owner/attendance', [AttendanceController::class, 'index'])->name('owner.attendance');
    Route::post('/dashboard/owner/attendance', [AttendanceController::class, 'store'])->name('owner.attendance.store');
    Route::post('/dashboard/owner/attendance/{attendance}/update', [AttendanceController::class, 'update'])->name('owner.attendance.update');

    // Payroll Management & Calculations
    Route::get('/dashboard/owner/payroll', [PayrollController::class, 'index'])->name('owner.payroll');
    Route::post('/dashboard/owner/payroll/generate', [PayrollController::class, 'generate'])->name('owner.payroll.generate');
    Route::post('/dashboard/owner/payroll/bonus', [PayrollController::class, 'storeBonus'])->name('owner.payroll.bonus');
    Route::post('/dashboard/owner/payroll/deduction', [PayrollController::class, 'storeDeduction'])->name('owner.payroll.deduction');
});

// 6. Staff Portal (Self-Service for all staff)
Route::middleware('role:owner,super_admin,admin,kasir,barista,pelayan')->group(function () {
    Route::get('/dashboard/staff/profile', [StaffPortalController::class, 'profile'])->name('staff.profile');
    Route::get('/dashboard/staff/attendance', [StaffPortalController::class, 'attendance'])->name('staff.attendance');
    Route::post('/dashboard/staff/clock-in', [StaffPortalController::class, 'clockIn'])->name('staff.clock-in');
    Route::post('/dashboard/staff/clock-out', [StaffPortalController::class, 'clockOut'])->name('staff.clock-out');
    Route::post('/dashboard/staff/leave', [StaffPortalController::class, 'storeLeave'])->name('staff.leave.store');
    Route::get('/dashboard/staff/payroll', [StaffPortalController::class, 'payroll'])->name('staff.payroll');
    Route::get('/dashboard/staff/payroll/{payroll}/slip', [StaffPortalController::class, 'slip'])->name('staff.slip');
});

// 7. Super Admin Dashboard
Route::middleware('role:super_admin')->group(function () {
    Route::get('/dashboard/super-admin', [DashboardController::class, 'superAdmin'])->name('dashboard.super-admin');
    Route::get('/dashboard/super-admin/{section}', [DashboardController::class, 'superAdmin'])->name('dashboard.super-admin.section')->where('section', 'user|roles|monitoring|pengaturan|logs');
    Route::post('/dashboard/super-admin/users', [DashboardController::class, 'storeSuperAdminUser'])->name('super-admin.users.store');
    Route::post('/dashboard/super-admin/users/{user}', [DashboardController::class, 'updateSuperAdminUser'])->name('super-admin.users.update');
    Route::delete('/dashboard/super-admin/users/{user}', [DashboardController::class, 'destroySuperAdminUser'])->name('super-admin.users.destroy');
    Route::post('/dashboard/super-admin/roles', [DashboardController::class, 'storeSuperAdminRole'])->name('super-admin.roles.store');
    Route::post('/dashboard/super-admin/roles/{role}/permissions', [DashboardController::class, 'updateSuperAdminRolePermissions'])->name('super-admin.roles.permissions');
    Route::delete('/dashboard/super-admin/roles/{role}', [DashboardController::class, 'destroySuperAdminRole'])->name('super-admin.roles.destroy');
    Route::post('/dashboard/super-admin/permissions', [DashboardController::class, 'storeSuperAdminPermission'])->name('super-admin.permissions.store');
    Route::delete('/dashboard/super-admin/permissions/{permission}', [DashboardController::class, 'destroySuperAdminPermission'])->name('super-admin.permissions.destroy');
    Route::post('/dashboard/super-admin/settings', [DashboardController::class, 'updateSuperAdminSettings'])->name('super-admin.settings.update');
});

// 6. Admin Menu & Inventory Management
Route::middleware('role:admin')->group(function () {
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
    Route::get('/dashboard/admin/{section}', [DashboardController::class, 'admin'])->name('dashboard.admin.section')->where('section', 'meja|qr-meja|staff|laporan');
    Route::get('/dashboard/admin/table-qrcodes/{table}.svg', [TableQrCodeController::class, 'show'])->name('admin.tables.qr-code');
    
    // Admin menu management
    Route::get('/dashboard/admin/menu', [MenuController::class, 'index'])->name('admin.menu.index');
    Route::post('/admin/menu', [MenuController::class, 'store'])->name('admin.menu.store');
    Route::post('/admin/menu/{menu}/update', [MenuController::class, 'update'])->name('admin.menu.update');
    Route::post('/admin/menu/{menu}/toggle', [MenuController::class, 'toggleStatus'])->name('admin.menu.toggle');
    Route::delete('/admin/menu/{menu}', [MenuController::class, 'destroy'])->name('admin.menu.destroy');
    Route::post('/admin/menu/{menu}/recipe', [MenuController::class, 'saveRecipe'])->name('admin.menu.recipe');
    
    Route::post('/admin/category', [MenuController::class, 'category'])->name('admin.category.store');
    
    Route::post('/admin/topping', [MenuController::class, 'storeTopping'])->name('admin.topping.store');
    Route::post('/admin/topping/{topping}/update', [MenuController::class, 'updateTopping'])->name('admin.topping.update');
    Route::delete('/admin/topping/{topping}', [MenuController::class, 'destroyTopping'])->name('admin.topping.destroy');
});

// Admin & Super Admin Inventory Modifiers
Route::middleware('role:admin,super_admin')->group(function () {
    Route::post('/dashboard/inventory/store', [InventoryController::class, 'store'])->name('admin.inventory.store');
    Route::post('/dashboard/inventory/manual-out', [InventoryController::class, 'manualOut'])->name('admin.inventory.manual-out');
    
    Route::post('/dashboard/inventory/supplier', [InventoryController::class, 'storeSupplier'])->name('admin.supplier.store');
    Route::delete('/dashboard/inventory/supplier/{supplier}', [InventoryController::class, 'deleteSupplier'])->name('admin.supplier.destroy');
    
    Route::post('/dashboard/inventory/po', [InventoryController::class, 'storePO'])->name('admin.po.store');
    Route::post('/dashboard/inventory/po/{po}/receive', [InventoryController::class, 'receivePO'])->name('admin.po.receive');
    
    Route::post('/dashboard/inventory/opname', [InventoryController::class, 'storeOpname'])->name('admin.opname.store');
});

// Multi-role Inventory View
Route::middleware('role:admin,super_admin,owner,barista')->group(function () {
    Route::get('/dashboard/inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');
});

// 7. Cashier Dashboard
Route::middleware('role:kasir')->group(function () {
    Route::get('/dashboard/cashier', [DashboardController::class, 'cashier'])->name('dashboard.cashier');
    Route::get('/dashboard/cashier/{section}', [DashboardController::class, 'cashier'])->name('dashboard.cashier.section')->where('section', 'order-masuk|pembayaran|riwayat');
});

// 8. Barista Dashboard
Route::middleware('role:barista')->group(function () {
    Route::get('/dashboard/barista', [DashboardController::class, 'barista'])->name('dashboard.barista');
    Route::get('/dashboard/barista/{section}', [DashboardController::class, 'barista'])->name('dashboard.barista.section')->where('section', 'queue-paid|queue-making|riwayat');
});

// 9. Shared Order Actions for Staf
Route::middleware('role:super_admin,admin,kasir,barista')->group(function () {
    Route::post('/orders/{order}/status', [OrderActionController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/payment', [OrderActionController::class, 'confirmPayment'])->name('orders.payment');
    Route::post('/orders/{order}/move', [OrderActionController::class, 'moveTable'])->name('orders.move');
});

