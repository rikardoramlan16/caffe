<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Owner Features
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month'); // e.g. "Juni 2026"
            $table->unsignedInteger('basic_salary')->default(0);
            $table->unsignedInteger('allowance')->default(0);
            $table->unsignedInteger('bonus')->default(0);
            $table->unsignedInteger('deduction')->default(0);
            $table->unsignedInteger('total_salary')->default(0);
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('amount')->default(0);
            $table->string('category')->default('Operational'); // Operational, Inventory, Marketing, etc.
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->timestamps();
        });

        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount')->default(0);
            $table->string('reason');
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->timestamps();
        });

        Schema::create('deletion_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->unsignedBigInteger('record_id');
            $table->text('data_summary')->nullable();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->timestamps();
        });

        // Inventory Categories
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Inventories
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_category_id')->constrained('inventory_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('unit'); // Gram, Ml, Pcs, Liter
            $table->decimal('current_stock', 12, 2)->default(0);
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->timestamps();
        });

        // Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact')->nullable();
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->text('supplied_products')->nullable();
            $table->timestamps();
        });

        // Purchase Orders (PO)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('po_number')->unique();
            $table->string('status')->default('DRAFT'); // DRAFT, SENT, RECEIVED, COMPLETED
            $table->unsignedInteger('total_amount')->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->unsignedInteger('price');
            $table->timestamps();
        });

        // Inventory Transactions (IN, OUT, ADJUSTMENT)
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->string('type'); // IN, OUT, ADJUSTMENT
            $table->decimal('quantity', 12, 2);
            $table->string('reference'); // e.g. PO-xxx, ORDER-xxx, SO-xxx
            $table->string('note')->nullable();
            $table->timestamps();
        });

        // Stock Opname
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_number')->unique();
            $table->string('status')->default('DRAFT'); // DRAFT, ADJUSTED
            $table->timestamps();
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->decimal('system_stock', 12, 2);
            $table->decimal('physical_stock', 12, 2);
            $table->decimal('difference', 12, 2);
            $table->timestamps();
        });

        // Menu Recipes
        Schema::create('menu_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2); // Kuantitas terpakai per porsi (misal 20g kopi, 150ml susu)
            $table->timestamps();
        });

        // Modify payments table to make cafe_order_id nullable
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('cafe_order_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_recipes');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('inventory_categories');
        Schema::dropIfExists('deletion_approvals');
        Schema::dropIfExists('bonuses');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payrolls');
    }
};
