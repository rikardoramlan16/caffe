<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\EmployeeLeave;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffPortalController extends Controller
{
    /**
     * Retrieve the associated employee for the logged in session user.
     */
    private function getEmployee(Request $request)
    {
        $authUser = $request->session()->get('auth_user');
        if (!$authUser) {
            return null;
        }
        return Employee::with('branch')->where('user_id', $authUser['id'])->first();
    }

    /**
     * View own profile & salary components.
     */
    public function profile(Request $request)
    {
        $employee = $this->getEmployee($request);
        if (!$employee) {
            return redirect()->route('landing')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $salaryComponents = DB::table('salary_components')
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->get();

        // Get attendance summary for current month
        $currentMonthAttendances = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();

        $attendanceSummary = [
            'hadir' => $currentMonthAttendances->whereIn('status', ['Hadir', 'Terlambat'])->count(),
            'terlambat' => $currentMonthAttendances->where('status', 'Terlambat')->count(),
            'cuti' => $currentMonthAttendances->whereIn('status', ['Cuti', 'Sakit', 'Izin'])->count(),
        ];

        $totalPayrolls = Payroll::where('employee_id', $employee->id)->where('status', 'APPROVED')->count();

        $myLeaves = EmployeeLeave::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.dashboard.staff.profile', compact('employee', 'salaryComponents', 'attendanceSummary', 'totalPayrolls', 'myLeaves'));
    }

    /**
     * View personal attendance log & Clock In/Out form.
     */
    public function attendance(Request $request)
    {
        $employee = $this->getEmployee($request);
        if (!$employee) {
            return redirect()->route('landing')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $myAttendances = Attendance::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        // Check today's clocking status
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        // Check pending leaves
        $leaves = EmployeeLeave::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.dashboard.staff.attendance', compact('employee', 'myAttendances', 'todayAttendance', 'leaves'));
    }

    /**
     * Handle Clock-In action.
     */
    public function clockIn(Request $request)
    {
        $employee = $this->getEmployee($request);
        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        // Check if already clocked in today
        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        if ($existing) {
            return back()->with('error', 'Anda sudah melakukan Absen Masuk hari ini.');
        }

        // Clock-in rules: Standard time is 08:00
        $currentTime = now();
        $status = $currentTime->format('H:i:s') > '08:00:00' ? 'Terlambat' : 'Hadir';

        DB::transaction(function () use ($employee, $currentTime, $status) {
            Attendance::create([
                'employee_id' => $employee->id,
                'date' => today()->format('Y-m-d'),
                'clock_in' => $currentTime->format('H:i:s'),
                'clock_out' => null,
                'status' => $status,
            ]);

            SystemLog::create([
                'level' => 'info',
                'source' => 'StaffPortalController@clockIn',
                'message' => "Karyawan '{$employee->name}' Clock-In ({$status}) pada " . $currentTime->format('H:i:s'),
                'context' => ['employee_id' => $employee->id, 'status' => $status],
            ]);
        });

        $msg = $status === 'Terlambat' 
            ? 'Absen Masuk berhasil dicatat. Status: Terlambat (lewat jam 08:00).' 
            : 'Absen Masuk berhasil dicatat tepat waktu. Selamat bekerja!';

        return back()->with('success', $msg);
    }

    /**
     * Handle Clock-Out action.
     */
    public function clockOut(Request $request)
    {
        $employee = $this->getEmployee($request);
        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        // Check if clocked in today
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Anda harus melakukan Absen Masuk terlebih dahulu.');
        }

        if ($attendance->clock_out) {
            return back()->with('error', 'Anda sudah melakukan Absen Keluar hari ini.');
        }

        $currentTime = now();

        DB::transaction(function () use ($attendance, $employee, $currentTime) {
            $attendance->update([
                'clock_out' => $currentTime->format('H:i:s'),
            ]);

            SystemLog::create([
                'level' => 'info',
                'source' => 'StaffPortalController@clockOut',
                'message' => "Karyawan '{$employee->name}' Clock-Out pada " . $currentTime->format('H:i:s'),
                'context' => ['employee_id' => $employee->id],
            ]);
        });

        return back()->with('success', 'Absen Keluar berhasil dicatat. Terima kasih atas kerja keras Anda hari ini!');
    }

    /**
     * View personal approved payroll list.
     */
    public function payroll(Request $request)
    {
        $employee = $this->getEmployee($request);
        if (!$employee) {
            return redirect()->route('landing')->with('error', 'Data karyawan tidak ditemukan.');
        }

        // Only show APPROVED payroll slips to the employee
        $payrolls = Payroll::with(['detail'])
            ->where('employee_id', $employee->id)
            ->where('status', 'APPROVED')
            ->orderBy('month', 'desc')
            ->get();

        return view('pages.dashboard.staff.payroll', compact('employee', 'payrolls'));
    }

    /**
     * Print specific personal slip.
     */
    public function slip(Request $request, Payroll $payroll)
    {
        $employee = $this->getEmployee($request);
        if (!$employee || $payroll->employee_id !== $employee->id) {
            abort(403, 'Anda tidak memiliki akses ke slip gaji ini.');
        }

        if ($payroll->status !== 'APPROVED') {
            abort(403, 'Slip gaji ini belum disetujui.');
        }

        $payroll->load(['employee.branch', 'user']);
        return view('pages.dashboard.owner.slip', compact('payroll'));
    }

    /**
     * Propose a leave request.
     */
    public function storeLeave(Request $request)
    {
        $employee = $this->getEmployee($request);
        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:Cuti Tahunan,Cuti Sakit,Cuti Hamil,Cuti Penting',
            'reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request, $employee) {
            EmployeeLeave::create([
                'employee_id' => $employee->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'type' => $request->type,
                'reason' => $request->reason,
                'status' => 'PENDING',
            ]);

            // Notify Owner
            DB::table('system_activities')->insert([
                'branch_id' => $employee->branch_id,
                'actor_role' => 'system',
                'message' => "Ada pengajuan cuti baru dari " . ucfirst($employee->name) . " ({$request->type}) mulai {$request->start_date}.",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            SystemLog::create([
                'level' => 'info',
                'source' => 'StaffPortalController@storeLeave',
                'message' => "Karyawan '{$employee->name}' mengajukan cuti '{$request->type}'",
                'context' => ['employee_id' => $employee->id, 'type' => $request->type],
            ]);
        });

        return back()->with('success', 'Pengajuan cuti berhasil diajukan dan sedang menunggu persetujuan.');
    }
}
