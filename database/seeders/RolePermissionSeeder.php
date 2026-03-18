<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Product permissions
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            
            // Sales permissions
            'view-sales',
            'create-sales',
            'edit-sales',
            'delete-sales',
            
            // Report permissions
            'view-reports',
            'create-reports',
            
            // Prediction permissions (admin only)
            'view-predictions',
            
            // Overall data permissions (admin only)
            'view-overall-data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Admin role with all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Create User role with limited permissions
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->syncPermissions([
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            'view-sales',
            'create-sales',
            'edit-sales',
            'delete-sales',
            'view-reports',
            'create-reports',
        ]);

        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles(['admin']);

        // Create default user (karyawan)
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Karyawan',
                'password' => Hash::make('password'),
            ]
        );
        $user->syncRoles(['user']);
    }
}
