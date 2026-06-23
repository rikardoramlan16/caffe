<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Attendance;
use App\Models\EmployeeBonus;
use App\Models\EmployeeDeduction;
use App\Models\EmployeeLeave;
use App\Models\Bonus;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Display a listing of payroll statements and proposals.
     */
    public function index(Request $request)
    {
        $authUser = $request->session()->get('auth_user');

        $branchQuery = Branch::query();
        if ($authUser['role'] === 'admin') {
            $branchQuery->where('id', $authUser['branch_id']);
        }
        $branches = $branchQuery->get();

        $query = Payroll::with(['employee.branch', 'detail']);

        // Filter by branch
        if ($authUser['role'] === 'admin') {
            $query->whereHas('employee', function ($q) use ($authUser) {
                $q->where('branch_id', $authUser['branch_id']);
            });
        } elseif ($request->filled('branch_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        // Apply filters
        if ($request->filled('role')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('role', $request->role);
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        $payrolls = $query->orderBy('month', 'desc')->latest()->get();

        // Load pending proposals for bonuses, deductions, and leaves
        $bonusQuery = EmployeeBonus::with('employee.branch')->latest();
        $deductionQuery = EmployeeDeduction::with('employee.branch')->latest();
        $leaveQuery = EmployeeLeave::with('employee.branch')->latest();

        if ($authUser['role'] === 'admin') {
            $bonusQuery->whereHas('employee', function ($q) use ($authUser) { $q->where('branch_id', $authUser['branch_id']); });
            $deductionQuery->whereHas('employee', function ($q) use ($authUser) { $q->where('branch_id', $authUser['branch_id']); });
            $leaveQuery->whereHas('employee', function ($q) use ($authUser) { $q->where('branch_id', $authUser['branch_id']); });
        }

        $bonuses = $bonusQuery->get();
        $deductions = $deductionQuery->get();
        $leaves = $leaveQuery->get();

        // Get list of employees for bonuses/deductions modals
        $employeeQuery = Employee::query();
        if ($authUser['role'] === 'admin') {
            $employeeQuery->where('branch_id', $authUser['branch_id']);
        }
        $employees = $employeeQuery->where('status', 'Aktif')->get();

        // Months list for filter
        $payrollMonths = Payroll::select('month')->distinct()->pluck('month')->all();
        if (empty($payrollMonths)) {
            $payrollMonths = [now()->translatedFormat('F Y')];
        }

        $roles = ['admin' => 'Admin', 'kasir' => 'Kasir', 'barista' => 'Barista', 'pelayan' => 'Pelayan'];

        return view('pages.dashboard.owner.payroll', compact(
            'payrolls', 'branches', 'employees', 'roles', 'payrollMonths', 
            'bonuses', 'deductions', 'leaves', 'authUser'
        ));
    }

    /**
     * Generate payroll for a specific month.
     */
    public function generate(Request $request)
    {
        $authUser = $request->session()->get('auth_user');

        if (!in_array($authUser['role'], ['owner', 'super_admin'])) {
            return back()->with('error', 'Hanya Owner atau Super Admin yang dapat memproses payroll.');
        }

        $request->validate([
            'month' => 'required|string', // e.g. "Juni 2026"
        ]);

        $monthStr = $request->month;

        // Parse start and end date for target month
        try {
            // Setup Indonesian locale or parse standard month
            // Translated month: e.g. "Juni 2026" -> map to english "June 2026"
            $indMonth = explode(' ', $monthStr);
            $monthsMap = [
                'Januari' => 'January', 'Februari' => 'February', 'Maret' => 'March', 
                'April' => 'April', 'Mei' => 'May', 'Juni' => 'June', 
                'Juli' => 'July', 'Agustus' => 'August', 'September' => 'September', 
                'Oktober' => 'October', 'November' => 'November', 'Desember' => 'December'
            ];
            $engMonthName = $monthsMap[$indMonth[0]] ?? $indMonth[0];
            $engDateStr = $engMonthName . ' ' . ($indMonth[1] ?? now()->year);
            $startDate = Carbon::parse($engDateStr)->startOfMonth();
            $endDate = Carbon::parse($engDateStr)->endOfMonth();
        } catch (\Exception $e) {
            return back()->with('error', 'Format bulan tidak valid. Gunakan format seperti "Juni 2026".');
        }

        // Get all active employees
        $employees = Employee::where('status', 'Aktif')->get();

        if ($employees->isEmpty()) {
            return back()->with('error', 'Tidak ada karyawan aktif untuk diproses.');
        }

        $generatedCount = 0;

        DB::transaction(function () use ($employees, $monthStr, $startDate, $endDate, $authUser, &$generatedCount) {
            foreach ($employees as $employee) {
                // Check if payroll already exists and is APPROVED
                $existing = Payroll::where('employee_id', $employee->id)
                    ->where('month', $monthStr)
                    ->first();

                if ($existing && $existing->status === 'APPROVED') {
                    continue; // Skip approved payroll
                }

                // 1. Basic Salary
                $basicSalary = $employee->basic_salary;

                // 2. Allowances (from active salary components)
                $allowanceSum = DB::table('salary_components')
                    ->where('employee_id', $employee->id)
                    ->where('type', 'allowance')
                    ->where('is_active', true)
                    ->sum('amount');

                // 3. Approved bonuses (requested in this month)
                $approvedBonuses = EmployeeBonus::where('employee_id', $employee->id)
                    ->where('status', 'APPROVED')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('amount');

                // 4. Deductions (from active components + automatic attendance penalties)
                $fixedDeductions = DB::table('salary_components')
                    ->where('employee_id', $employee->id)
                    ->where('type', 'deduction')
                    ->where('is_active', true)
                    ->sum('amount');

                // Attendance penalties: Alpha (Rp50,000), Terlambat (Rp10,000)
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->get();

                $alphaCount = $attendances->where('status', 'Alpha')->count();
                $lateCount = $attendances->where('status', 'Terlambat')->count();

                $attendancePenalties = ($alphaCount * 50000) + ($lateCount * 10000);

                // Approved manual deductions
                $approvedDeductions = EmployeeDeduction::where('employee_id', $employee->id)
                    ->where('status', 'APPROVED')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('amount');

                $totalDeductions = $fixedDeductions + $attendancePenalties + $approvedDeductions;

                // 5. Total Salary calculation
                $totalSalary = $basicSalary + $allowanceSum + $approvedBonuses - $totalDeductions;
                if ($totalSalary < 0) $totalSalary = 0;

                // 6. Save Payroll
                $payroll = Payroll::updateOrCreate(
                    ['employee_id' => $employee->id, 'month' => $monthStr],
                    [
                        'user_id' => $employee->user_id,
                        'basic_salary' => $basicSalary,
                        'allowance' => $allowanceSum,
                        'bonus' => $approvedBonuses,
                        'deduction' => $totalDeductions,
                        'total_salary' => $totalSalary,
                        'status' => 'PENDING',
                        'paid_at' => null,
                    ]
                );

                // 7. Save Details
                PayrollDetail::updateOrCreate(
                    ['payroll_id' => $payroll->id],
                    [
                        'basic_salary' => $basicSalary,
                        'allowance' => $allowanceSum,
                        'bonus' => $approvedBonuses,
                        'overtime' => 0, // optional overtime addition can go here
                        'deduction' => $totalDeductions,
                        'net_salary' => $totalSalary,
                    ]
                );

                $generatedCount++;
            }

            // Fire notification: "Payroll belum disetujui" if there are pending payrolls
            DB::table('system_activities')->insert([
                'branch_id' => null,
                'actor_role' => 'system',
                'message' => "Payroll periode {$monthStr} telah digenerate dan sedang menunggu persetujuan Owner.",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            SystemLog::create([
                'level' => 'info',
                'source' => 'PayrollController@generate',
                'message' => "Payroll periode '{$monthStr}' berhasil digenerate untuk {$generatedCount} karyawan oleh " . $authUser['name'],
                'context' => ['month' => $monthStr, 'generated_count' => $generatedCount],
            ]);
        });

        return back()->with('success', "Payroll periode {$monthStr} berhasil diproses untuk {$generatedCount} karyawan.");
    }

    /**
     * Update status (Approval/Rejection) of a payroll.
     */
    public function updateStatus(Request $request, Payroll $payroll)
    {
        $authUser = $request->session()->get('auth_user');

        if ($authUser['role'] !== 'owner') {
            return back()->with('error', 'Hanya Owner yang berhak menyetujui atau menolak payroll.');
        }

        $request->validate([
            'status' => 'required|in:APPROVED,REJECTED',
        ]);

        DB::transaction(function () use ($request, $payroll, $authUser) {
            $payroll->update([
                'status' => $request->status,
                'paid_at' => $request->status === 'APPROVED' ? now() : null,
            ]);

            SystemLog::create([
                'level' => 'info',
                'source' => 'PayrollController@updateStatus',
                'message' => "Payroll ID #{$payroll->id} set status '{$request->status}' oleh Owner",
                'context' => ['payroll_id' => $payroll->id, 'status' => $request->status],
            ]);
        });

        return back()->with('success', 'Status Payroll berhasil diperbarui.');
    }

    /**
     * View and print a salary slip.
     */
    public function slip(Payroll $payroll)
    {
        $payroll->load(['employee.branch', 'user']);
        return view('pages.dashboard.owner.slip', compact('payroll'));
    }

    /**
     * Propose a bonus for an employee.
     */
    public function storeBonus(Request $request)
    {
        $authUser = $request->session()->get('auth_user');

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'bonus_type' => 'required|in:Bonus Penjualan,Bonus Kehadiran,Bonus Kinerja,Bonus Khusus',
            'reason' => 'required|string|max:255',
        ]);

        $employee = Employee::find($request->employee_id);

        if ($authUser['role'] === 'admin' && $employee->branch_id !== $authUser['branch_id']) {
            return back()->with('error', 'Anda hanya dapat mengajukan bonus untuk karyawan cabang Anda.');
        }

        DB::transaction(function () use ($request, $employee, $authUser) {
            // Owner automatically gets APPROVED status, Admin starts as PENDING
            $status = $authUser['role'] === 'owner' ? 'APPROVED' : 'PENDING';

            EmployeeBonus::create([
                'employee_id' => $employee->id,
                'amount' => $request->amount,
                'bonus_type' => $request->bonus_type,
                'reason' => $request->reason,
                'status' => $status,
            ]);

            // Notify if proposed by admin
            if ($status === 'PENDING') {
                DB::table('system_activities')->insert([
                    'branch_id' => $employee->branch_id,
                    'actor_role' => 'system',
                    'message' => "Ada pengajuan bonus baru: " . ucfirst($employee->name) . " sejumlah Rp" . number_format($request->amount, 0, ',', '.') . " (" . $request->bonus_type . ") oleh Admin.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            SystemLog::create([
                'level' => 'info',
                'source' => 'PayrollController@storeBonus',
                'message' => "Bonus karyawan '{$employee->name}' diajukan dengan status '{$status}' oleh " . $authUser['name'],
                'context' => ['employee_id' => $employee->id, 'amount' => $request->amount, 'status' => $status],
            ]);
        });

        return back()->with('success', 'Bonus karyawan berhasil ' . ($authUser['role'] === 'owner' ? 'ditambahkan' : 'diajukan untuk persetujuan Owner.'));
    }

    /**
     * Propose a deduction for an employee.
     */
    public function storeDeduction(Request $request)
    {
        $authUser = $request->session()->get('auth_user');

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'deduction_type' => 'required|in:Alpha,Terlambat,Denda,Kasbon',
            'reason' => 'required|string|max:255',
        ]);

        $employee = Employee::find($request->employee_id);

        if ($authUser['role'] === 'admin' && $employee->branch_id !== $authUser['branch_id']) {
            return back()->with('error', 'Anda hanya dapat mengajukan potongan untuk karyawan cabang Anda.');
        }

        DB::transaction(function () use ($request, $employee, $authUser) {
            $status = $authUser['role'] === 'owner' ? 'APPROVED' : 'PENDING';

            EmployeeDeduction::create([
                'employee_id' => $employee->id,
                'amount' => $request->amount,
                'deduction_type' => $request->deduction_type,
                'reason' => $request->reason,
                'status' => $status,
            ]);

            // Notify if proposed by admin
            if ($status === 'PENDING') {
                DB::table('system_activities')->insert([
                    'branch_id' => $employee->branch_id,
                    'actor_role' => 'system',
                    'message' => "Ada pengajuan potongan baru: " . ucfirst($employee->name) . " sejumlah Rp" . number_format($request->amount, 0, ',', '.') . " (" . $request->deduction_type . ") oleh Admin.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            SystemLog::create([
                'level' => 'info',
                'source' => 'PayrollController@storeDeduction',
                'message' => "Potongan karyawan '{$employee->name}' diajukan dengan status '{$status}' oleh " . $authUser['name'],
                'context' => ['employee_id' => $employee->id, 'amount' => $request->amount, 'status' => $status],
            ]);
        });

        return back()->with('success', 'Potongan karyawan berhasil ' . ($authUser['role'] === 'owner' ? 'ditambahkan' : 'diajukan untuk persetujuan Owner.'));
    }

    /**
     * Approve or reject a bonus request.
     */
    public function approveBonus(Request $request, Bonus $bonus)
    {
        $authUser = $request->session()->get('auth_user');

        if ($authUser['role'] !== 'owner') {
            return back()->with('error', 'Hanya Owner yang berhak menyetujui bonus.');
        }

        $request->validate(['status' => 'required|in:APPROVED,REJECTED']);

        DB::transaction(function () use ($request, $bonus) {
            $bonus->update(['status' => $request->status]);
        });

        return back()->with('success', 'Status pengajuan bonus berhasil diperbarui.');
    }

    /**
     * Approve or reject a deduction request.
     */
    public function approveDeduction(Request $request, EmployeeDeduction $deduction)
    {
        $authUser = $request->session()->get('auth_user');

        if ($authUser['role'] !== 'owner') {
            return back()->with('error', 'Hanya Owner yang berhak menyetujui potongan.');
        }

        $request->validate(['status' => 'required|in:APPROVED,REJECTED']);

        DB::transaction(function () use ($request, $deduction) {
            $deduction->update(['status' => $request->status]);
        });

        return back()->with('success', 'Status pengajuan potongan berhasil diperbarui.');
    }

    /**
     * Approve or reject a leave request.
     */
    public function approveLeave(Request $request, EmployeeLeave $leave)
    {
        $authUser = $request->session()->get('auth_user');

        if (!in_array($authUser['role'], ['owner', 'super_admin'])) {
            return back()->with('error', 'Anda tidak memiliki izin untuk memproses pengajuan cuti.');
        }

        $request->validate(['status' => 'required|in:APPROVED,REJECTED']);

        DB::transaction(function () use ($request, $leave) {
            $leave->update(['status' => $request->status]);
            
            // If approved, update employee status to Cuti during leave period?
            // Optionally, update employee status to Cuti or keep it as Aktif with leaves
        });

        return back()->with('success', 'Status pengajuan cuti berhasil diperbarui.');
    }
}

