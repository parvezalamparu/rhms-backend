<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserManagement\Permission;
use App\Models\UserManagement\HasPermission;

class AssignPermissionsToRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roleId = 2;

        // Fetch all permission IDs
        $permissions = Permission::pluck('id')->toArray();

        $data = [];

        foreach ($permissions as $permissionId) {
            $data[] = [
                'role_id'       => $roleId,
                'permission_id' => $permissionId
            ];
        }

        // Insert only if not exists
        HasPermission::insertOrIgnore($data);
    }
}
