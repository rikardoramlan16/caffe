<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Display a listing of employee attendance records.
     */
    public function index(Request $request)
    {
        $authUser = $request->session()->get('auth_user');

        $branchQuery = Branch::query();
        if ($authUser['role'] === 'admin') {
            $branchQuery->where('id', $authUser['branch_id']);
        }
        $branches = $branchQuery->get();

        $query = Attendance::with(['employee.branch']);

        // Filter based on branch (Admin can only see their own branch)
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
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        } else {
            // Default to showing latest records, but let's not restrict to date unless filtered
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $attendances = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get();

        // Get list of employees for manual attendance logging dropdown
        $employeeQuery = Employee::query();
        if ($authUser['role'] === 'admin') {
            $employeeQuery->where('branch_id', $authUser['branch_id']);
        }
        $employees = $employeeQuery->where('status', 'Aktif')->get();

        $roles = ['admin' => 'Admin', 'kasir' => 'Kasir', 'barista' => 'Barista', 'pelayan' => 'Pelayan'];
        $statuses = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Cuti'];

        return view('pages.dashboard.owner.attendance', compact('attendances', 'branches', 'employees', 'roles', 'statuses', 'authUser'));
    }

    /**
     * Store a manual attendance record.
     */
    public function store(Request $request)
    {
        $authUser = $request->session()->get('auth_user');

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'status' => 'required|in:Hadir,Terlambat,Izin,Sakit,Alpha,Cuti',
        ]);

        $employee = Employee::find($request->employee_id);

        // Access check
        if ($authUser['role'] === 'admin' && $employee->branch_id !== $authUser['branch_id']) {
            return back()->with('error', 'Anda hanya dapat mengelola absensi karyawan cabang Anda.');
        }

        // Check if attendance for this employee on this date already exists
        $exists = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $request->date)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Data absensi karyawan pada tanggal tersebut sudah ada. Silakan edit data yang sudah ada.');
        }

        DB::transaction(function () use ($request, $employee, $authUser) {
            Attendance::create([
                'employee_id' => $employee->id,
                'date' => $request->date,
                'clock_in' => $request->clock_in ?: null,
                'clock_out' => $request->clock_out ?: null,
                'status' => $request->status,
            ]);

            // Notify if Alpha
            if ($request->status === 'Alpha') {
                DB::table('system_activities')->insert([
                    'branch_id' => $employee->branch_id,
                    'actor_role' => 'system',
                    'message' => "Ada absensi Alpha untuk karyawan: {$employee->name} pada {$request->date}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            SystemLog::create([
                'level' => 'info',
                'source' => 'AttendanceController@store',
                'message' => "Absensi manual karyawan '{$employee->name}' ditambahkan oleh " . $authUser['name'],
                'context' => ['employee_id' => $employee->id, 'created_by' => $authUser['id'], 'status' => $request->status],
            ]);
        });

        return back()->with('success', 'Absensi karyawan berhasil ditambahkan.');
    }

    /**
     * Update an attendance record.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $authUser = $request->session()->get('auth_user');

        $request->validate([
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'status' => 'required|in:Hadir,Terlambat,Izin,Sakit,Alpha,Cuti',
        ]);

        $employee = $attendance->employee;

        // Access check
        if ($authUser['role'] === 'admin' && $employee->branch_id !== $authUser['branch_id']) {
            return back()->with('error', 'Anda hanya dapat mengelola absensi karyawan cabang Anda.');
        }

        DB::transaction(function () use ($request, $attendance, $employee, $authUser) {
            $oldStatus = $attendance->status;
            
            $attendance->update([
                'clock_in' => $request->clock_in ?: null,
                'clock_out' => $request->clock_out ?: null,
                'status' => $request->status,
            ]);

            // Notify if changed to Alpha
            if ($oldStatus !== $request->status && $request->status === 'Alpha') {
                DB::table('system_activities')->insert([
                    'branch_id' => $employee->branch_id,
                    'actor_role' => 'system',
                    'message' => "Ada absensi Alpha untuk karyawan: {$employee->name} pada {$attendance->date}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            SystemLog::create([
                'level' => 'info',
                'source' => 'AttendanceController@update',
                'message' => "Absensi karyawan '{$employee->name}' tanggal {$attendance->date} diperbarui oleh " . $authUser['name'],
                'context' => ['attendance_id' => $attendance->id, 'updated_by' => $authUser['id'], 'old_status' => $oldStatus, 'new_status' => $request->status],
            ]);
        });

        return back()->with('success', 'Absensi karyawan berhasil diperbarui.');
    }
}

