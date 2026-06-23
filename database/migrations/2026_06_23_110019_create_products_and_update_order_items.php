<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('barcode')->nullable()->unique();
            $table->unsignedInteger('price');
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        // 2. Modify order_items table to make menu_id nullable and add product_id
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id')->nullable()->change();
            $table->foreign('menu_id')->references('id')->on('menus')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->after('menu_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            
            $table->dropForeign(['menu_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id')->nullable(false)->change();
            $table->foreign('menu_id')->references('id')->on('menus')->cascadeOnDelete();
        });

        Schema::dropIfExists('products');
    }
};
