<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissionId = DB::table('permissions')->where('name', 'manage_branches')->value('id');

        if (! $permissionId) {
            return;
        }

        DB::table('role_permissions')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();
    }

    public function down(): void
    {
        if (DB::table('permissions')->where('name', 'manage_branches')->exists()) {
            return;
        }

        $permissionId = DB::table('permissions')->insertGetId([
            'name' => 'manage_branches',
            'label' => 'Manage Branches',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $superAdminRoleId = DB::table('roles')->where('name', 'super_admin')->value('id');

        if ($superAdminRoleId) {
            DB::table('role_permissions')->insert([
                'role_id' => $superAdminRoleId,
                'permission_id' => $permissionId,
            ]);
        }
    }
};
