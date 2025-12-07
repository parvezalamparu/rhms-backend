<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserManagement\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'create_user',
            'update_user',
            'delete_user',
            'send_password',
            'change_password',
            'create_role',
            'assign_role',
            'view_user_role',
            'create_permission',
            'assign_permission',
            'revoke_permission',
            'view_role_permission',


            'view_items',
            'show_items',
            'add_items',
            'delete_items',
            'update_items',
            'toggle_items',

            'view_categories',
            'active_categories',
            'add_categories',
            'show_categories',
            'update_categories',
            'delete_categories',
            'toggle_categories',

            'view_unit',
            'add_unit',
            'show_unit',
            'update_unit',
            'delete_unit',
            'active_unit',
            'toggle_unit',

            'view_item_type',
            'active_item_types',
            'add_item_type',
            'show_item_type',
            'update_item_type',
            'delete_item_type',
            'toggle_item_type',

            'view_companies',
            'active_companies',
            'add_companies',
            'show_companies',
            'update_companies',
            'delete_companies',
            'toggle_companies',

            'view_item_store',
            'active_item_store',
            'add_item_store',
            'show_item_store',
            'update_item_store',
            'delete_item_store',
            'toggle_item_store',

            'view_department',
            'add_department',
            'show_department',
            'update_department',
            'delete_department',
            'toggle_department',

            'view_requisition',
            'add_requisition',
            'show_requisition',
            'update_requisition',
            'delete_requisition',

            'view_purchase_order',
            'create_purchase_order',
            'show_purchase_order',
            'update_purchase_order',
            'delete_purchase_order',

            'view_purchase',
            'add_purchase',
            'show_purchase',
            'delete_purchase',

            'add_vendor',
            'view_vendor',
            'update_vendor',
            'delete_vendor',

            'view_issue',
            'add_issue',
            'show_issue',
            'update_issue',
            'delete_issue',

            'view_return_item',
            'add_return_item',
            'show_return_item',
            'update_return_item',
            'delete_return_item',

            'approve_return',
            'view_approve_return',
            'show_approve_return',
            'delete_approve_return',

            'add_repair',
            'view_repair',
            'show_repair',
            'update_repair',
            'delete_repair',

            'view_discard',
            'add_discard',
            'show_discard',
            'delete_discard',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['permission_name' => $permission],
                ['slug' => $permission]
            );
        }

        echo "Permissions seeded successfully.\n";
    }
}
