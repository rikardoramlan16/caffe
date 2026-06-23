<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Topping;
use App\Models\CafeTable;
use App\Models\Payroll;
use App\Models\Expense;
use App\Models\Bonus;
use App\Models\DeletionApproval;
use App\Models\InventoryCategory;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\InventoryTransaction;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\MenuRecipe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $roleIds = collect([
            'owner' => 'Owner',
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'kasir' => 'Kasir',
            'barista' => 'Barista',
            'pelayan' => 'Pelayan',
            'pelanggan' => 'Pelanggan',
        ])->mapWithKeys(fn (string $label, string $name) => [
            $name => DB::table('roles')->insertGetId([
                'name' => $name,
                'label' => $label,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
        ]);

        // Seed Permissions
        $permissionIds = collect([
            'manage_users', 'manage_roles', 'manage_settings', 'backup_database',
            'manage_menu', 'manage_tables', 'view_reports', 'manage_payments', 'manage_orders',
            'barista_queue', 'waiter_delivery',
        ])->mapWithKeys(fn (string $name) => [
            $name => DB::table('permissions')->insertGetId([
                'name' => $name,
                'label' => Str::of($name)->replace('_', ' ')->title(),
                'created_at' => now(),
                'updated_at' => now(),
            ]),
        ]);

        // Link Role Permissions
        foreach ($roleIds as $role => $roleId) {
            $allowed = $role === 'super_admin'
                ? $permissionIds->keys()
                : match ($role) {
                    'owner' => ['view_reports'],
                    'admin' => ['manage_menu', 'manage_tables', 'view_reports', 'manage_users', 'manage_orders'],
                    'kasir' => ['manage_payments', 'manage_orders'],
                    'barista' => ['barista_queue'],
                    default => ['waiter_delivery'],
                };

            foreach ($allowed as $permission) {
                DB::table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionIds[$permission],
                ]);
            }
        }

        // 2. Seed Branches
        $branches = collect([
            ['name' => 'Kopi Senja Kemang', 'city' => 'Jakarta Selatan', 'status' => 'active'],
            ['name' => 'Kopi Senja BSD', 'city' => 'Tangerang Selatan', 'status' => 'active'],
            ['name' => 'Kopi Senja Bandung', 'city' => 'Bandung', 'status' => 'active'],
        ])->map(fn (array $branch) => DB::table('branches')->insertGetId($branch + [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        // 3. Seed Tables with code TBL001 - TBL018
        $tableCounter = 1;
        foreach ($branches as $branchId) {
            foreach (range(1, 6) as $num) {
                $code = 'TBL00' . $tableCounter;
                if ($tableCounter >= 10) {
                    $code = 'TBL0' . $tableCounter;
                }
                DB::table('tables')->insert([
                    'branch_id' => $branchId,
                    'code' => $code,
                    'capacity' => $num % 2 == 1 ? 2 : 4,
                    'status' => 'available',
                    'qr_token' => $code,
                    'number' => $tableCounter,
                    'qr_code_path' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $tableCounter++;
            }
        }

        // 4. Seed Menu Categories
        $categories = collect(['Coffee', 'Non Coffee', 'Tea', 'Snack'])->map(fn (string $name, int $index) => DB::table('categories')->insertGetId([
            'branch_id' => $branches[0],
            'name' => $name,
            'sort_order' => $index + 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        // 5. Seed Menus
        $menuIds = collect([
            [$categories[0], 'Aren Signature Latte', 'Espresso, fresh milk, gula aren premium.', 32000, true],
            [$categories[0], 'Cappuccino Reserve', 'Espresso blend house dan foam halus.', 34000, true],
            [$categories[0], 'Cold Brew Citrus', 'Cold brew 18 jam dengan citrus sparkling.', 38000, false],
            [$categories[1], 'Matcha Cream Latte', 'Matcha ceremonial dan cream milk.', 36000, false],
            [$categories[3], 'Butter Croissant', 'Croissant butter hangat.', 28000, true],
        ])->map(fn (array $menu) => DB::table('menus')->insertGetId([
            'branch_id' => $branches[0],
            'category_id' => $menu[0],
            'name' => $menu[1],
            'description' => $menu[2],
            'price' => $menu[3],
            'image_path' => 'images/cafe/customer-page.svg',
            'is_available' => true,
            'is_featured' => $menu[4],
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        // 6. Seed Toppings
        $toppingIds = collect([
            ['Extra Shot Espresso', 5000],
            ['Oat Milk', 7000],
            ['Cheese Foam', 6000],
            ['Boba', 4000],
            ['Jelly', 3000],
            ['Whipped Cream', 5000],
        ])->map(fn (array $topping) => DB::table('toppings')->insertGetId([
            'branch_id' => $branches[0],
            'name' => $topping[0],
            'price' => $topping[1],
            'inventory_quantity' => 1,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        // 7. Seed Staff Users
        $staffRoles = ['owner', 'super_admin', 'admin', 'kasir', 'barista', 'pelayan'];
        $createdUsers = [];
        foreach ($staffRoles as $role) {
            $createdUsers[$role] = User::factory()->create([
                'name' => str($role)->replace('_', ' ')->title(),
                'email' => $role.'@cafeflow.test',
                'role' => $role,
                'role_id' => $roleIds[$role],
                'branch_id' => in_array($role, ['super_admin', 'owner']) ? null : $branches[0],
                'password' => Hash::make('password'),
            ]);
        }

        // 8. Seed Customer Order History
        $tableId = DB::table('tables')->where('code', 'TBL001')->value('id');
        $sessionId = DB::table('customer_sessions')->insertGetId([
            'branch_id' => $branches[0],
            'table_id' => $tableId,
            'customer_token' => 'demo-token-'.Str::random(12),
            'last_seen_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (range(1, 7) as $index) {
            $subtotal = 32000 + ($index * 9000);
            $status = ['WAITING_PAYMENT', 'PAID', 'MAKING', 'READY', 'DONE', 'CANCEL', 'READY'][$index - 1];
            $orderId = DB::table('orders')->insertGetId([
                'branch_id' => $branches[0],
                'table_id' => $tableId,
                'customer_session_id' => $sessionId,
                'invoice_number' => 'INV-'.now()->format('ymd').'-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                'status' => $status,
                'subtotal' => $subtotal,
                'service_fee' => 8000,
                'total' => $subtotal + 8000,
                'customer_note' => 'Kurangi gula.',
                'paid_at' => in_array($status, ['PAID', 'MAKING', 'READY', 'DONE'], true) ? now()->subMinutes(20) : null,
                'created_at' => now()->subMinutes($index * 12),
                'updated_at' => now()->subMinutes($index * 6),
            ]);

            $orderItemId = DB::table('order_items')->insertGetId([
                'order_id' => $orderId,
                'menu_id' => $menuIds[$index % $menuIds->count()],
                'quantity' => 1 + ($index % 2),
                'unit_price' => 32000,
                'note' => $index % 2 ? 'Extra hot' : 'Normal ice',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('order_item_toppings')->insert([
                'order_item_id' => $orderItemId,
                'topping_id' => $toppingIds[$index % $toppingIds->count()],
                'price' => 6000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('payments')->insert([
                'order_id' => $orderId,
                'cafe_order_id' => null,
                'method' => $index % 2 ? 'QRIS' : 'Cash',
                'status' => $status === 'WAITING_PAYMENT' ? 'waiting' : 'paid',
                'reference' => 'PAY-INV-'.$index,
                'amount' => $subtotal + 8000,
                'paid_at' => $status === 'WAITING_PAYMENT' ? null : now()->subMinutes(18),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 9. Seed Inventory System
        // Inventory Categories
        $invCategories = collect(['Bahan Basah', 'Bubuk & Sirup', 'Kemasan'])->map(fn (string $name) => DB::table('inventory_categories')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        // Inventories
        $coffeeBeanId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[0],
            'name' => 'Biji Kopi Arabika',
            'unit' => 'Gram',
            'current_stock' => 12000,
            'min_stock' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $milkId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[0],
            'name' => 'Susu Full Cream',
            'unit' => 'Ml',
            'current_stock' => 25000,
            'min_stock' => 5000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $matchaId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[1],
            'name' => 'Matcha Powder Premium',
            'unit' => 'Gram',
            'current_stock' => 1500,
            'min_stock' => 300,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sirupId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[1],
            'name' => 'Sirup Gula Aren',
            'unit' => 'Ml',
            'current_stock' => 5000,
            'min_stock' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cupId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[2],
            'name' => 'Cup Kopi',
            'unit' => 'Pcs',
            'current_stock' => 500,
            'min_stock' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sedotanId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[2],
            'name' => 'Sedotan Kertas',
            'unit' => 'Pcs',
            'current_stock' => 35, // Triggering low stock!
            'min_stock' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $espressoId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[1],
            'name' => 'Espresso',
            'unit' => 'Ml',
            'current_stock' => 3000,
            'min_stock' => 300,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $oatMilkId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[0],
            'name' => 'Oat Milk',
            'unit' => 'Ml',
            'current_stock' => 8000,
            'min_stock' => 800,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cheeseFoamId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[0],
            'name' => 'Cheese Foam',
            'unit' => 'Gram',
            'current_stock' => 2000,
            'min_stock' => 200,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $bobaId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[1],
            'name' => 'Boba',
            'unit' => 'Gram',
            'current_stock' => 3000,
            'min_stock' => 300,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $jellyId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[1],
            'name' => 'Jelly',
            'unit' => 'Gram',
            'current_stock' => 2500,
            'min_stock' => 250,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whippedCreamId = DB::table('inventories')->insertGetId([
            'inventory_category_id' => $invCategories[0],
            'name' => 'Whipped Cream',
            'unit' => 'Gram',
            'current_stock' => 1500,
            'min_stock' => 150,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $toppingInventoryMap = [
            'Extra Shot Espresso' => [$espressoId, 30],
            'Oat Milk' => [$oatMilkId, 120],
            'Cheese Foam' => [$cheeseFoamId, 35],
            'Boba' => [$bobaId, 40],
            'Jelly' => [$jellyId, 35],
            'Whipped Cream' => [$whippedCreamId, 25],
        ];

        foreach ($toppingInventoryMap as $name => [$inventoryId, $usage]) {
            DB::table('toppings')
                ->where('branch_id', $branches[0])
                ->where('name', $name)
                ->update([
                    'inventory_id' => $inventoryId,
                    'inventory_quantity' => $usage,
                    'updated_at' => now(),
                ]);
        }

        foreach ($menuIds as $menuId) {
            foreach ($toppingIds as $toppingId) {
                DB::table('menu_topping')->insert([
                    'menu_id' => $menuId,
                    'topping_id' => $toppingId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 10. Seed Menu Recipes
        // Recipe: Aren Signature Latte (ID = 1) -> 20g Kopi, 150ml Susu, 30ml Sirup Aren, 1 Cup, 1 Sedotan
        DB::table('menu_recipes')->insert([
            ['menu_id' => $menuIds[0], 'inventory_id' => $coffeeBeanId, 'quantity' => 20],
            ['menu_id' => $menuIds[0], 'inventory_id' => $milkId, 'quantity' => 150],
            ['menu_id' => $menuIds[0], 'inventory_id' => $sirupId, 'quantity' => 30],
            ['menu_id' => $menuIds[0], 'inventory_id' => $cupId, 'quantity' => 1],
            ['menu_id' => $menuIds[0], 'inventory_id' => $sedotanId, 'quantity' => 1],
        ]);

        // Recipe: Matcha Cream Latte (ID = 4) -> 15g Matcha, 200ml Susu, 1 Cup, 1 Sedotan
        DB::table('menu_recipes')->insert([
            ['menu_id' => $menuIds[3], 'inventory_id' => $matchaId, 'quantity' => 15],
            ['menu_id' => $menuIds[3], 'inventory_id' => $milkId, 'quantity' => 200],
            ['menu_id' => $menuIds[3], 'inventory_id' => $cupId, 'quantity' => 1],
            ['menu_id' => $menuIds[3], 'inventory_id' => $sedotanId, 'quantity' => 1],
        ]);

        // 11. Seed Suppliers
        $sup1 = DB::table('suppliers')->insertGetId([
            'name' => 'PT Kopi Nusantara',
            'contact' => '08123456789',
            'address' => 'Jl. Kopi Gayo No. 22, Bandung',
            'email' => 'sales@kopinusantara.com',
            'supplied_products' => 'Biji Kopi Arabika, Sirup Gula Aren',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sup2 = DB::table('suppliers')->insertGetId([
            'name' => 'CV Susu Segar Sejahtera',
            'contact' => '08987654321',
            'address' => 'Jl. Peternak No. 15, Bogor',
            'email' => 'order@sususegar.com',
            'supplied_products' => 'Susu Full Cream',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 12. Seed Purchase Orders
        $po1 = DB::table('purchase_orders')->insertGetId([
            'supplier_id' => $sup1,
            'po_number' => 'PO-260601-0001',
            'status' => 'COMPLETED',
            'total_amount' => 450000,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(1),
        ]);
        DB::table('purchase_order_items')->insert([
            ['purchase_order_id' => $po1, 'inventory_id' => $coffeeBeanId, 'quantity' => 10000, 'price' => 350000],
            ['purchase_order_id' => $po1, 'inventory_id' => $sirupId, 'quantity' => 2000, 'price' => 100000],
        ]);

        $po2 = DB::table('purchase_orders')->insertGetId([
            'supplier_id' => $sup2,
            'po_number' => 'PO-260602-0002',
            'status' => 'SENT',
            'total_amount' => 250000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('purchase_order_items')->insert([
            ['purchase_order_id' => $po2, 'inventory_id' => $milkId, 'quantity' => 20000, 'price' => 250000],
        ]);

        // 13. Seed Stock Opnames
        $so = DB::table('stock_opnames')->insertGetId([
            'opname_number' => 'SO-260601-0001',
            'status' => 'ADJUSTED',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);
        DB::table('stock_opname_items')->insert([
            ['stock_opname_id' => $so, 'inventory_id' => $coffeeBeanId, 'system_stock' => 12060, 'physical_stock' => 12000, 'difference' => -60],
        ]);
        DB::table('inventory_transactions')->insert([
            ['inventory_id' => $coffeeBeanId, 'type' => 'ADJUSTMENT', 'quantity' => -60, 'reference' => 'SO-260601-0001', 'note' => 'Penyesuaian stock opname selisih susut', 'created_at' => now()->subDays(1), 'updated_at' => now()->subDays(1)]
        ]);

        // 14. Seed Owner Approval Features
        // Payrolls
        DB::table('payrolls')->insert([
            [
                'user_id' => $createdUsers['admin']->id,
                'month' => 'Mei 2026',
                'basic_salary' => 4500000,
                'allowance' => 500000,
                'bonus' => 250000,
                'deduction' => 0,
                'total_salary' => 5250000,
                'status' => 'APPROVED',
                'paid_at' => now()->subDays(2),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => $createdUsers['barista']->id,
                'month' => 'Juni 2026',
                'basic_salary' => 3500000,
                'allowance' => 300000,
                'bonus' => 0,
                'deduction' => 100000,
                'total_salary' => 3700000,
                'status' => 'PENDING',
                'paid_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Expenses
        DB::table('expenses')->insert([
            [
                'branch_id' => $branches[0],
                'title' => 'Pembelian Biji Kopi Arabika Darurat',
                'description' => 'Beli 2kg biji kopi Arabika di supplier lokal karena keterlambatan pengiriman PO.',
                'amount' => 160000,
                'category' => 'Inventory',
                'status' => 'APPROVED',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'branch_id' => $branches[0],
                'title' => 'Sewa AC Portable Event',
                'description' => 'Sewa AC portable 2 unit untuk event gathering komunitas tanggal 5 Juni.',
                'amount' => 500000,
                'category' => 'Operational',
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Bonuses
        DB::table('bonuses')->insert([
            [
                'user_id' => $createdUsers['kasir']->id,
                'amount' => 200000,
                'reason' => 'Bonus pencapaian target penjualan outlet bulan Mei',
                'status' => 'APPROVED',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'user_id' => $createdUsers['barista']->id,
                'amount' => 250000,
                'reason' => 'Bonus atas kerja lembur event weekend dan pelayanan prima',
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Deletion Approvals
        DB::table('deletion_approvals')->insert([
            [
                'table_name' => 'menus',
                'record_id' => $menuIds[2], // Cold Brew Citrus
                'data_summary' => json_encode(['name' => 'Cold Brew Citrus', 'price' => 38000, 'category' => 'Coffee']),
                'requested_by' => $createdUsers['admin']->id,
                'reason' => 'Menu tidak laku terjual dalam 3 bulan terakhir dan bahan citrus sirup sudah habis.',
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 15. General Settings and Activity Logs
        foreach (['app_name' => 'CafeFlow', 'theme' => 'coffee-premium', 'payment_methods' => 'QRIS,Cash,Debit'] as $key => $value) {
            DB::table('settings')->insert([
                'key' => $key,
                'value' => $value,
                'group' => 'app',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ([
            'Kasir menerima pembayaran QRIS #INV-0004',
            'Barista menandai Aren Latte siap antar',
            'Admin memperbarui stok Susu Full Cream',
            'Sistem mendeteksi stok Sedotan Kertas menipis',
        ] as $message) {
            DB::table('system_activities')->insert([
                'branch_id' => $branches[0],
                'actor_role' => 'system',
                'message' => $message,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('activity_logs')->insert([
            'branch_id' => $branches[0],
            'user_id' => $createdUsers['kasir']->id,
            'action' => 'payment.confirmed',
            'description' => 'Kasir mengonfirmasi pembayaran order demo.',
            'properties' => json_encode(['method' => 'QRIS']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('system_logs')->insert([
            'level' => 'info',
            'source' => 'seed',
            'message' => 'Dummy data CafeFlow berhasil dibuat.',
            'context' => json_encode(['version' => 'demo']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
