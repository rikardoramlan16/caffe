<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        $authUser = $request->session()->get('auth_user');
        
        // Filter by branch
        $branchQuery = Branch::query();
        if ($authUser['role'] === 'admin') {
            $branchQuery->where('id', $authUser['branch_id']);
        }
        $branches = $branchQuery->get();

        $query = Employee::with('branch');

        // Apply role boundaries
        if ($authUser['role'] === 'admin') {
            // Admin can only see employees in their branch
            $query->where('branch_id', $authUser['branch_id']);
        }

        // Apply filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $employees = $query->latest()->get();

        // Roles list for form dropdown
        $roles = ['admin' => 'Admin', 'kasir' => 'Kasir', 'barista' => 'Barista', 'pelayan' => 'Pelayan'];

        return view('pages.dashboard.owner.employees', compact('employees', 'branches', 'roles', 'authUser'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $authUser = $request->session()->get('auth_user');

        // Check if admin is trying to modify salary (unauthorized)
        if ($authUser['role'] === 'admin' && $request->filled('basic_salary')) {
            return back()->with('error', 'Admin tidak memiliki wewenang untuk mengatur gaji pokok.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'joined_at' => 'required|date',
            'role' => 'required|in:admin,kasir,barista,pelayan',
            'branch_id' => 'required|exists:branches,id',
            'basic_salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:Aktif,Nonaktif,Cuti,Resign',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Enforce branch for Admin
        $branchId = $request->branch_id;
        if ($authUser['role'] === 'admin') {
            $branchId = $authUser['branch_id'];
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('employees', 'public');
            $photoPath = '/storage/' . $path;
        }

        DB::transaction(function () use ($request, $branchId, $photoPath, $authUser) {
            // 1. Create User account so they can log in
            $roleName = strtolower($request->role);
            $roleModel = Role::where('name', $roleName)->first();
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $roleName,
                'role_id' => $roleModel ? $roleModel->id : null,
                'branch_id' => $branchId,
                'password' => Hash::make('password'), // default password
            ]);

            // 2. Create Employee record
            Employee::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'photo_path' => $photoPath,
                'joined_at' => $request->joined_at,
                'role' => $roleName,
                'branch_id' => $branchId,
                'basic_salary' => $authUser['role'] === 'admin' ? 0 : ($request->basic_salary ?? 0),
                'status' => $request->status,
            ]);

            // Create notification log
            DB::table('system_activities')->insert([
                'branch_id' => $branchId,
                'actor_role' => 'system',
                'message' => "Karyawan baru ditambahkan: {$request->name} sebagai " . ucfirst($request->role),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            SystemLog::create([
                'level' => 'info',
                'source' => 'EmployeeController@store',
                'message' => "Karyawan baru '{$request->name}' ditambahkan oleh " . $authUser['name'],
                'context' => ['user_id' => $user->id, 'created_by' => $authUser['id']],
            ]);
        });

        return back()->with('success', 'Karyawan baru berhasil ditambahkan.');
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $authUser = $request->session()->get('auth_user');

        // Boundary checks
        if ($authUser['role'] === 'admin') {
            // Admin cannot modify basic salary
            if ($request->basic_salary != $employee->basic_salary) {
                return back()->with('error', 'Admin tidak dapat mengubah gaji pokok.');
            }
            // Admin cannot edit employees of other branches
            if ($employee->branch_id !== $authUser['branch_id'] || $request->branch_id != $authUser['branch_id']) {
                return back()->with('error', 'Anda hanya dapat mengelola karyawan cabang Anda.');
            }
        }

        // Super Admin cannot modify Owner account
        if ($authUser['role'] === 'super_admin' && $employee->user && $employee->user->role === 'owner') {
            return back()->with('error', 'Super Admin tidak memiliki izin untuk mengubah data Owner.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id . '|unique:users,email,' . ($employee->user_id ?? 0),
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'joined_at' => 'required|date',
            'role' => 'required|in:admin,kasir,barista,pelayan',
            'branch_id' => 'required|exists:branches,id',
            'basic_salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:Aktif,Nonaktif,Cuti,Resign',
            'photo' => 'nullable|image|max:2048',
        ]);

        $photoPath = $employee->photo_path;
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($photoPath) {
                $oldPath = str_replace('/storage/', '', $photoPath);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('photo')->store('employees', 'public');
            $photoPath = '/storage/' . $path;
        }

        DB::transaction(function () use ($request, $employee, $photoPath, $authUser) {
            // Update associated User account
            if ($employee->user) {
                $roleName = strtolower($request->role);
                $roleModel = Role::where('name', $roleName)->first();

                $employee->user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'role' => $roleName,
                    'role_id' => $roleModel ? $roleModel->id : null,
                    'branch_id' => $request->branch_id,
                ]);
            }

            // Update Employee record
            $oldStatus = $employee->status;
            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'photo_path' => $photoPath,
                'joined_at' => $request->joined_at,
                'role' => strtolower($request->role),
                'branch_id' => $request->branch_id,
                'basic_salary' => $authUser['role'] === 'admin' ? $employee->basic_salary : ($request->basic_salary ?? 0),
                'status' => $request->status,
            ]);

            // Notify if status changed to Cuti
            if ($oldStatus !== $request->status && $request->status === 'Cuti') {
                DB::table('system_activities')->insert([
                    'branch_id' => $employee->branch_id,
                    'actor_role' => 'system',
                    'message' => "Ada pengajuan cuti karyawan: {$employee->name}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            SystemLog::create([
                'level' => 'info',
                'source' => 'EmployeeController@update',
                'message' => "Data karyawan '{$employee->name}' diperbarui oleh " . $authUser['name'],
                'context' => ['employee_id' => $employee->id, 'updated_by' => $authUser['id']],
            ]);
        });

        return back()->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Request $request, Employee $employee)
    {
        $authUser = $request->session()->get('auth_user');

        // Boundary checks
        if ($authUser['role'] === 'admin') {
            return back()->with('error', 'Admin tidak dapat menghapus karyawan.');
        }

        // Super Admin cannot delete Owner
        if ($authUser['role'] === 'super_admin' && $employee->user && $employee->user->role === 'owner') {
            return back()->with('error', 'Super Admin tidak dapat menghapus data Owner.');
        }

        DB::transaction(function () use ($employee, $authUser) {
            // Delete associated user
            if ($employee->user) {
                $employee->user->delete();
            }

            // Delete photo
            if ($employee->photo_path) {
                $oldPath = str_replace('/storage/', '', $employee->photo_path);
                Storage::disk('public')->delete($oldPath);
            }

            // Delete employee record
            $employee->delete();

            SystemLog::create([
                'level' => 'warning',
                'source' => 'EmployeeController@destroy',
                'message' => "Karyawan '{$employee->name}' dihapus oleh " . $authUser['name'],
                'context' => ['employee_name' => $employee->name, 'deleted_by' => $authUser['id']],
            ]);
        });

        return back()->with('success', 'Karyawan berhasil dihapus dari sistem.');
    }
}

