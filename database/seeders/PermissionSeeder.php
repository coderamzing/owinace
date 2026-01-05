<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get permissions from config
        $permissions = config('permission.permissions', []);

        // Create permissions using firstOrCreate
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }

        // Sync role permissions
        $this->syncRolePermissions();

        $this->command->info('Permissions seeded and role permissions synced successfully!');
    }

    /**
     * Sync permissions for roles based on config
     */
    private function syncRolePermissions(): void
    {
        $rolesConfig = config('permission.roles', []);

        foreach ($rolesConfig as $roleName => $roleData) {
            // Create or get the role
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['name' => $roleName, 'guard_name' => 'web']
            );

            // Get the permissions for this role
            $rolePermissions = $roleData['permissions'] ?? [];

            if ($rolePermissions === ['all']) {
                // Give all permissions to this role
                $allPermissions = Permission::all();
                $role->syncPermissions($allPermissions);
                $this->command->info("Synced all permissions to role: {$roleName}");
            } else {
                // Sync specific permissions
                $permissions = Permission::whereIn('name', $rolePermissions)->get();
                $role->syncPermissions($permissions);
                $this->command->info("Synced " . $permissions->count() . " permissions to role: {$roleName}");
            }
        }
    }
}
