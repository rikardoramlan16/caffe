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
        Schema::create('scanner_scans', function (Blueprint $table) {
            $table->id();
            $table->string('pairing_code');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('product_price', 12, 2)->default(0);
            $table->boolean('is_processed')->default(false);
            $table->timestamps();

            $table->index(['pairing_code', 'is_processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scanner_scans');
    }
};
