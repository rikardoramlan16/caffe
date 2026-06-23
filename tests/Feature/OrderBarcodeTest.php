<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\CafeTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ScannerPairing;
use Illuminate\Support\Facades\DB;

class OrderBarcodeTest extends TestCase
{
    use RefreshDatabase;

    protected $branch;
    protected $category;
    protected $table;
    protected $cashierUser;
    protected $order;
    protected $sessionData;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create a Branch
        $this->branch = Branch::create([
            'name' => 'Kopi Senja Test',
            'city' => 'Jakarta Selatan',
            'status' => 'active',
        ]);

        // 2. Create a Category
        $this->category = Category::create([
            'branch_id' => $this->branch->id,
            'name' => 'Snack',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // 3. Create a CafeTable
        $this->table = CafeTable::create([
            'branch_id' => $this->branch->id,
            'code' => 'TBL001',
            'capacity' => 4,
            'status' => 'available',
            'qr_token' => 'test-token',
            'number' => 1,
        ]);

        // 4. Create Role and Cashier User
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'kasir',
            'label' => 'Kasir',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->cashierUser = User::create([
            'name' => 'Kasir Test',
            'email' => 'kasir@test.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
            'role_id' => $roleId,
            'branch_id' => $this->branch->id,
        ]);

        // 5. Create a clean Order under waiting payment
        $this->order = Order::create([
            'branch_id' => $this->branch->id,
            'table_id' => $this->table->id,
            'invoice_number' => 'INV-TEST-0001',
            'status' => 'WAITING_PAYMENT',
            'subtotal' => 0,
            'service_fee' => 3000,
            'total' => 3000,
        ]);

        // 6. Define Session Data for EnsureRole middleware
        $this->sessionData = [
            'auth_user' => [
                'id' => $this->cashierUser->id,
                'name' => $this->cashierUser->name,
                'role' => $this->cashierUser->role,
                'branch_id' => $this->cashierUser->branch_id,
            ]
        ];
    }

    public function test_cashier_can_add_item_by_barcode(): void
    {
        // Create a product with a barcode
        $product = Product::create([
            'branch_id' => $this->branch->id,
            'name' => 'Coca Cola',
            'description' => 'Dingin',
            'price' => 10000,
            'is_available' => true,
            'is_featured' => false,
            'barcode' => '8992689100010',
        ]);

        $response = $this->withSession($this->sessionData)
            ->post(route('orders.add-barcode', $this->order->id), [
                'barcode' => '8992689100010'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Produk "Coca Cola" berhasil ditambahkan ke pesanan.');

        // Verify it was added to database
        $this->assertDatabaseHas('order_items', [
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 10000,
        ]);

        // Verify order totals updated (10000 subtotal + 3000 service fee = 13000 total)
        $this->order->refresh();
        $this->assertEquals(10000, $this->order->subtotal);
        $this->assertEquals(13000, $this->order->total);
    }

    public function test_cashier_cannot_add_item_with_invalid_barcode(): void
    {
        $response = $this->withSession($this->sessionData)
            ->post(route('orders.add-barcode', $this->order->id), [
                'barcode' => '9999999999999'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Produk dengan barcode "9999999999999" tidak ditemukan.');

        // Verify order totals remained unchanged
        $this->order->refresh();
        $this->assertEquals(0, $this->order->subtotal);
        $this->assertEquals(3000, $this->order->total);
    }

    public function test_cashier_cannot_add_unavailable_item_by_barcode(): void
    {
        // Create an unavailable product with a barcode
        $product = Product::create([
            'branch_id' => $this->branch->id,
            'name' => 'Fanta Strawberry',
            'description' => 'Habis',
            'price' => 10000,
            'is_available' => false,
            'is_featured' => false,
            'barcode' => '8992689100027',
        ]);

        $response = $this->withSession($this->sessionData)
            ->post(route('orders.add-barcode', $this->order->id), [
                'barcode' => '8992689100027'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Produk "Fanta Strawberry" sedang tidak tersedia.');

        // Verify it was NOT added to order_items
        $this->assertDatabaseMissing('order_items', [
            'order_id' => $this->order->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_cashier_adding_existing_item_by_barcode_increments_quantity(): void
    {
        // Create product with a barcode
        $product = Product::create([
            'branch_id' => $this->branch->id,
            'name' => 'Coca Cola',
            'description' => 'Dingin',
            'price' => 10000,
            'is_available' => true,
            'is_featured' => false,
            'barcode' => '8992689100010',
        ]);

        // Pre-insert OrderItem
        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 10000,
            'size' => 'Regular',
            'size_price' => 0,
            'sugar_level' => '100%',
            'ice_level' => 'Normal',
        ]);

        $response = $this->withSession($this->sessionData)
            ->post(route('orders.add-barcode', $this->order->id), [
                'barcode' => '8992689100010'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Produk "Coca Cola" berhasil ditambahkan ke pesanan.');

        // Verify quantity is incremented to 2
        $this->assertDatabaseHas('order_items', [
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Verify order totals updated (20000 subtotal + 3000 service fee = 23000 total)
        $this->order->refresh();
        $this->assertEquals(20000, $this->order->subtotal);
        $this->assertEquals(23000, $this->order->total);
    }

    public function test_cashier_can_access_kelola_barang_page(): void
    {
        $response = $this->withSession($this->sessionData)
            ->get(route('dashboard.cashier.section', 'kelola-barang'));

        $response->assertStatus(200);
        $response->assertViewHas('section', 'kelola-barang');
        $response->assertSee('Kelola Barang (Produk Jadi)');
    }

    public function test_cashier_can_create_new_barang(): void
    {
        $response = $this->withSession($this->sessionData)
            ->post(route('admin.products.store'), [
                'name' => 'Fanta Strawberry New',
                'barcode' => '8992689100088',
                'price' => 11000,
                'description' => 'Ready ready',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('products', [
            'name' => 'Fanta Strawberry New',
            'barcode' => '8992689100088',
            'price' => 11000,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_cashier_can_update_existing_barang(): void
    {
        $product = Product::create([
            'branch_id' => $this->branch->id,
            'name' => 'Aqua Botol',
            'description' => 'Dingin',
            'price' => 5000,
            'is_available' => true,
            'is_featured' => false,
            'barcode' => '8992689100034',
        ]);

        $response = $this->withSession($this->sessionData)
            ->post(route('admin.products.update', $product->id), [
                'name' => 'Aqua Botol Updated',
                'barcode' => '8992689100035',
                'price' => 6000,
                'is_available' => 0,
                'is_featured' => 1,
                'description' => 'New desc',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Aqua Botol Updated',
            'barcode' => '8992689100035',
            'price' => 6000,
            'is_available' => false,
            'is_featured' => true,
        ]);
    }

    public function test_cashier_can_delete_existing_barang(): void
    {
        $product = Product::create([
            'branch_id' => $this->branch->id,
            'name' => 'Aqua Botol',
            'description' => 'Dingin',
            'price' => 5000,
            'is_available' => true,
            'is_featured' => false,
            'barcode' => '8992689100034',
        ]);

        $response = $this->withSession($this->sessionData)
            ->delete(route('admin.products.destroy', $product->id));

        $response->assertRedirect();

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_admin_can_access_kelola_barang_page(): void
    {
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'admin',
            'label' => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'role_id' => $roleId,
            'branch_id' => $this->branch->id,
        ]);

        $adminSession = [
            'auth_user' => [
                'id' => $adminUser->id,
                'name' => $adminUser->name,
                'role' => $adminUser->role,
                'branch_id' => $adminUser->branch_id,
            ]
        ];

        $response = $this->withSession($adminSession)
            ->get(route('admin.menu.index') . '?section=barang');

        $response->assertStatus(200);
        $response->assertSee('Daftar Barang (Produk Jadi)');
    }

    public function test_cashier_can_generate_pairing_code(): void
    {
        $response = $this->withSession($this->sessionData)
            ->post(route('cashier.scanner.generate'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pairing_code',
            'url'
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        
        $this->assertDatabaseHas('scanner_pairings', [
            'pairing_code' => $data['pairing_code'],
            'user_id' => $this->cashierUser->id,
            'branch_id' => $this->branch->id,
            'is_active' => true
        ]);
    }

    public function test_can_check_scanner_pairing_status(): void
    {
        // 1. Check with invalid code
        $response = $this->withSession($this->sessionData)
            ->get(route('cashier.scanner.status') . '?code=999999');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'paired' => false,
            'last_order_update' => null
        ]);

        // 2. Create active pairing
        $pairing = ScannerPairing::create([
            'pairing_code' => '123456',
            'branch_id' => $this->branch->id,
            'user_id' => $this->cashierUser->id,
            'is_active' => true
        ]);

        $response = $this->withSession($this->sessionData)
            ->get(route('cashier.scanner.status') . '?code=123456');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'paired' => true
        ]);
    }

    public function test_mobile_scanner_can_view_page_and_pair(): void
    {
        // Create active pairing
        $pairing = ScannerPairing::create([
            'pairing_code' => '123456',
            'branch_id' => $this->branch->id,
            'user_id' => $this->cashierUser->id,
            'is_active' => true
        ]);

        // Visit with pair parameter
        $response = $this->get(route('scanner.index') . '?pair=123456');
        $response->assertStatus(200);
        $response->assertSessionHas('paired_scanner_code', '123456');
        $response->assertSee('Scanner Terhubung');
    }

    public function test_paired_mobile_scanner_can_fetch_orders(): void
    {
        $pairing = ScannerPairing::create([
            'pairing_code' => '123456',
            'branch_id' => $this->branch->id,
            'user_id' => $this->cashierUser->id,
            'is_active' => true
        ]);

        // Unpaired call fails with 401
        $response = $this->get(route('scanner.orders'));
        $response->assertStatus(401);

        // Paired call works
        $response = $this->withSession(['paired_scanner_code' => '123456'])
            ->get(route('scanner.orders'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'orders'
        ]);
    }

    public function test_paired_mobile_scanner_can_scan_barcode(): void
    {
        $pairing = ScannerPairing::create([
            'pairing_code' => '123456',
            'branch_id' => $this->branch->id,
            'user_id' => $this->cashierUser->id,
            'is_active' => true
        ]);

        $product = Product::create([
            'branch_id' => $this->branch->id,
            'name' => 'Fanta Strawberry',
            'description' => 'Ready',
            'price' => 10000,
            'is_available' => true,
            'is_featured' => false,
            'barcode' => '8992689100027',
        ]);

        $response = $this->withSession(['paired_scanner_code' => '123456'])
            ->postJson(route('scanner.scan'), [
                'order_id' => $this->order->id,
                'barcode' => '8992689100027'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    }

    public function test_paired_mobile_scanner_can_scan_barcode_for_new_order(): void
    {
        $pairing = ScannerPairing::create([
            'pairing_code' => '123456',
            'branch_id' => $this->branch->id,
            'user_id' => $this->cashierUser->id,
            'is_active' => true
        ]);

        $product = Product::create([
            'branch_id' => $this->branch->id,
            'name' => 'Fanta Strawberry',
            'description' => 'Ready',
            'price' => 10000,
            'is_available' => true,
            'is_featured' => false,
            'barcode' => '8992689100027',
        ]);

        $response = $this->withSession(['paired_scanner_code' => '123456'])
            ->postJson(route('scanner.scan'), [
                'order_id' => 'new_order',
                'barcode' => '8992689100027'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $data = $response->json();
        $this->assertNotNull($data['order_id']);

        $this->assertDatabaseHas('orders', [
            'id' => $data['order_id'],
            'table_id' => null,
            'status' => 'WAITING_PAYMENT'
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $data['order_id'],
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    }
}
