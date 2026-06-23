<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('photo_path')->nullable();
            $table->date('joined_at');
            $table->string('role'); // admin, kasir, barista, pelayan
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('basic_salary')->default(0);
            $table->string('status')->default('Aktif'); // Aktif, Nonaktif, Cuti, Resign
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->string('status')->default('Hadir'); // Hadir, Terlambat, Izin, Sakit, Alpha, Cuti
            $table->timestamps();
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnDelete();
            $table->unsignedInteger('basic_salary')->default(0);
            $table->unsignedInteger('allowance')->default(0);
            $table->unsignedInteger('bonus')->default(0);
            $table->unsignedInteger('overtime')->default(0);
            $table->unsignedInteger('deduction')->default(0);
            $table->unsignedInteger('net_salary')->default(0);
            $table->timestamps();
        });

        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // allowance, deduction
            $table->unsignedInteger('amount')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('type'); // Cuti Tahunan, Cuti Sakit, Cuti Hamil, Cuti Penting
            $table->text('reason')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->timestamps();
        });

        Schema::create('employee_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount')->default(0);
            $table->string('bonus_type'); // Bonus Penjualan, Bonus Kehadiran, Bonus Kinerja, Bonus Khusus
            $table->string('reason');
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->timestamps();
        });

        Schema::create('employee_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount')->default(0);
            $table->string('deduction_type'); // Alpha, Terlambat, Denda, Kasbon
            $table->string('reason');
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_deductions');
        Schema::dropIfExists('employee_bonuses');
        Schema::dropIfExists('employee_leaves');
        Schema::dropIfExists('salary_components');
        Schema::dropIfExists('payroll_details');
        
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });

        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employees');
    }
};
