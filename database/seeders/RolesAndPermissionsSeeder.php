<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'create designs']);
        Permission::create(['name' => 'edit designs']);
        Permission::create(['name' => 'delete designs']);
        Permission::create(['name' => 'create commissions']);
        Permission::create(['name' => 'edit commissions']);
        Permission::create(['name' => 'edit stores']);
        Permission::create(['name' => 'assign roles']);
        Permission::create(['name' => 'assign permissions']);

        // Create roles and assign existing permissions
        $designerRole = Role::create(['name' => 'designer']);
        $designerRole->givePermissionTo('create designs');
        $designerRole->givePermissionTo('edit designs');
        $designerRole->givePermissionTo('delete designs');
        $designerRole->givePermissionTo('create commissions');

        $tailorRole = Role::create(['name' => 'tailors']);
        $tailorRole->givePermissionTo('edit commissions');
        $tailorRole->givePermissionTo('edit stores');

        $adminRole = Role::create(['name' => 'admin']);
    }
}
