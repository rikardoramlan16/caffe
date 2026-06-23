<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('toppings', function (Blueprint $table) {
            if (! Schema::hasColumn('toppings', 'inventory_quantity')) {
                $table->decimal('inventory_quantity', 12, 2)->default(1)->after('inventory_id');
            }
        });

        Schema::create('menu_topping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('topping_id')->constrained('toppings')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['menu_id', 'topping_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'size')) {
                $table->string('size')->default('Regular')->after('unit_price');
            }
            if (! Schema::hasColumn('order_items', 'size_price')) {
                $table->unsignedInteger('size_price')->default(0)->after('size');
            }
            if (! Schema::hasColumn('order_items', 'sugar_level')) {
                $table->string('sugar_level')->default('100%')->after('size_price');
            }
            if (! Schema::hasColumn('order_items', 'ice_level')) {
                $table->string('ice_level')->default('Normal')->after('sugar_level');
            }
        });

        $branchId = DB::table('branches')->value('id');
        if (! $branchId) {
            return;
        }

        $categoryId = DB::table('inventory_categories')->where('name', 'Topping & Add-On')->value('id')
            ?? DB::table('inventory_categories')->insertGetId([
                'name' => 'Topping & Add-On',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $examples = [
            ['Extra Shot Espresso', 5000, 'Espresso', 'Ml', 3000, 30],
            ['Oat Milk', 7000, 'Oat Milk', 'Ml', 8000, 120],
            ['Cheese Foam', 6000, 'Cheese Foam', 'Gram', 2000, 35],
            ['Boba', 4000, 'Boba', 'Gram', 3000, 40],
            ['Jelly', 3000, 'Jelly', 'Gram', 2500, 35],
            ['Whipped Cream', 5000, 'Whipped Cream', 'Gram', 1500, 25],
        ];

        foreach ($examples as [$name, $price, $inventoryName, $unit, $stock, $usage]) {
            $inventoryId = DB::table('inventories')->where('name', $inventoryName)->value('id')
                ?? DB::table('inventories')->insertGetId([
                    'inventory_category_id' => $categoryId,
                    'name' => $inventoryName,
                    'unit' => $unit,
                    'current_stock' => $stock,
                    'min_stock' => max(50, $stock / 10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('toppings')->updateOrInsert(
                ['branch_id' => $branchId, 'name' => $name],
                [
                    'inventory_id' => $inventoryId,
                    'inventory_quantity' => $usage,
                    'price' => $price,
                    'is_available' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $menuIds = DB::table('menus')->where('branch_id', $branchId)->pluck('id');
        $toppingIds = DB::table('toppings')->where('branch_id', $branchId)->where('is_available', true)->pluck('id');
        foreach ($menuIds as $menuId) {
            foreach ($toppingIds as $toppingId) {
                DB::table('menu_topping')->updateOrInsert(
                    ['menu_id' => $menuId, 'topping_id' => $toppingId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_topping');

        Schema::table('order_items', function (Blueprint $table) {
            foreach (['ice_level', 'sugar_level', 'size_price', 'size'] as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('toppings', function (Blueprint $table) {
            if (Schema::hasColumn('toppings', 'inventory_quantity')) {
                $table->dropColumn('inventory_quantity');
            }
        });
    }
};
