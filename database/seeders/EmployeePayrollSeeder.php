<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\EmployeeLeave;
use App\Models\EmployeeBonus;
use App\Models\EmployeeDeduction;
use App\Models\SalaryComponent;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeePayrollSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get staff users
        $roles = ['admin', 'kasir', 'barista', 'pelayan'];
        $branch = Branch::first();
        $branchId = $branch ? $branch->id : null;

        $employees = [];

        foreach ($roles as $role) {
            $user = User::where('role', $role)->first();
            if (!$user) {
                continue;
            }

            // Create Employee record linked to User
            $employee = Employee::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '0812345678' . $user->id,
                'address' => 'Jl. Merdeka No. ' . $user->id . ', Batam Center',
                'photo_path' => null,
                'joined_at' => '2025-01-15',
                'role' => $role,
                'branch_id' => $user->branch_id ?? $branchId,
                'basic_salary' => $role === 'admin' ? 4500000 : ($role === 'pelayan' ? 3000000 : 3500000),
                'status' => 'Aktif',
            ]);

            $employees[] = $employee;

            // 2. Create Salary Components (recurring allowances)
            SalaryComponent::create([
                'employee_id' => $employee->id,
                'name' => 'Tunjangan Transportasi',
                'type' => 'allowance',
                'amount' => $role === 'admin' ? 300000 : 150000,
                'is_active' => true,
            ]);

            SalaryComponent::create([
                'employee_id' => $employee->id,
                'name' => 'Tunjangan Makan',
                'type' => 'allowance',
                'amount' => 200000,
                'is_active' => true,
            ]);

            // 3. Create Attendances for June 1st to June 7th, 2026 (local time is June 8)
            $attendanceData = [
                ['date' => '2026-06-01', 'clock_in' => '07:54:00', 'clock_out' => '17:02:00', 'status' => 'Hadir'],
                ['date' => '2026-06-02', 'clock_in' => '07:58:00', 'clock_out' => '17:00:00', 'status' => 'Hadir'],
                ['date' => '2026-06-03', 'clock_in' => '08:14:00', 'clock_out' => '17:05:00', 'status' => 'Terlambat'],
                ['date' => '2026-06-04', 'clock_in' => '07:55:00', 'clock_out' => '17:00:00', 'status' => 'Hadir'],
                ['date' => '2026-06-05', 'clock_in' => null, 'clock_out' => null, 'status' => 'Alpha'], // Test alpha
                ['date' => '2026-06-06', 'clock_in' => null, 'clock_out' => null, 'status' => 'Sakit'], // Sick day
                ['date' => '2026-06-07', 'clock_in' => '07:59:00', 'clock_out' => '17:01:00', 'status' => 'Hadir'],
            ];

            foreach ($attendanceData as $att) {
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $att['date'],
                    'clock_in' => $att['clock_in'],
                    'clock_out' => $att['clock_out'],
                    'status' => $att['status'],
                ]);
            }

            // 4. Create Leaves
            if ($role === 'barista') {
                EmployeeLeave::create([
                    'employee_id' => $employee->id,
                    'start_date' => '2026-06-15',
                    'end_date' => '2026-06-17',
                    'type' => 'Cuti Tahunan',
                    'reason' => 'Keperluan keluarga di luar kota',
                    'status' => 'PENDING',
                ]);
            }

            // 5. Create Bonuses & Deductions
            if ($role === 'kasir') {
                // Proposed bonus by admin
                EmployeeBonus::create([
                    'employee_id' => $employee->id,
                    'amount' => 200000,
                    'bonus_type' => 'Bonus Kinerja',
                    'reason' => 'Pelayanan ramah dan feedback positif dari pelanggan',
                    'status' => 'PENDING',
                ]);

                // Proposed deduction
                EmployeeDeduction::create([
                    'employee_id' => $employee->id,
                    'amount' => 50000,
                    'deduction_type' => 'Denda',
                    'reason' => 'Selisih kas kecil pada shift malam 3 Juni',
                    'status' => 'PENDING',
                ]);
            }

            // 6. Create Payroll and Details
            // May 2026 (APPROVED)
            $basic = $employee->basic_salary;
            $allowance = $role === 'admin' ? 500000 : 350000;
            $bonus = 150000;
            $deduction = 0;
            $total = $basic + $allowance + $bonus - $deduction;

            $payrollMay = Payroll::create([
                'user_id' => $user->id,
                'employee_id' => $employee->id,
                'month' => 'Mei 2026',
                'basic_salary' => $basic,
                'allowance' => $allowance,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'total_salary' => $total,
                'status' => 'APPROVED',
                'paid_at' => '2026-06-01 10:00:00',
            ]);

            PayrollDetail::create([
                'payroll_id' => $payrollMay->id,
                'basic_salary' => $basic,
                'allowance' => $allowance,
                'bonus' => $bonus,
                'overtime' => 0,
                'deduction' => $deduction,
                'net_salary' => $total,
            ]);

            // June 2026 (PENDING)
            // Let's add some late deductions (Terlambat = 1, Alpha = 1)
            // Terlambat deduction = 1 * Rp10.000 = Rp10.000
            // Alpha deduction = 1 * Rp50.000 = Rp50.000
            // Total deduction = Rp60.000
            $dedJune = 60000;
            $totalJune = $basic + $allowance + 0 - $dedJune;

            $payrollJune = Payroll::create([
                'user_id' => $user->id,
                'employee_id' => $employee->id,
                'month' => 'Juni 2026',
                'basic_salary' => $basic,
                'allowance' => $allowance,
                'bonus' => 0,
                'deduction' => $dedJune,
                'total_salary' => $totalJune,
                'status' => 'PENDING',
                'paid_at' => null,
            ]);

            PayrollDetail::create([
                'payroll_id' => $payrollJune->id,
                'basic_salary' => $basic,
                'allowance' => $allowance,
                'bonus' => 0,
                'overtime' => 0,
                'deduction' => $dedJune,
                'net_salary' => $totalJune,
            ]);
        }

        // Clean up any old payroll records that don't have employee_id
        DB::table('payrolls')->whereNull('employee_id')->delete();
    }
}
