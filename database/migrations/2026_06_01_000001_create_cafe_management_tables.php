<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->unsignedTinyInteger('capacity')->default(2);
            $table->string('status')->default('available');
            $table->string('qr_token')->unique();
            $table->timestamps();
        });

        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('price');
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('cafe_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('customer_name')->nullable();
            $table->string('status')->default('pending_payment');
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('service_fee')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->timestamps();
        });

        Schema::create('cafe_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cafe_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('unit_price');
            $table->string('production_status')->default('queued');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cafe_order_id')->constrained()->cascadeOnDelete();
            $table->string('method');
            $table->string('status')->default('waiting');
            $table->string('reference')->nullable();
            $table->unsignedInteger('amount');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('system_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_role');
            $table->string('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_activities');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('cafe_order_items');
        Schema::dropIfExists('cafe_orders');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menu_categories');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('branches');
    }
};
