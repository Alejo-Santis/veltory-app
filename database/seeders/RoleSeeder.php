<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos
        $permissions = [
            'products.view',   'products.create',   'products.edit',   'products.delete',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'suppliers.view',  'suppliers.create',  'suppliers.edit',  'suppliers.delete',
            'stock.view',      'stock.adjust',
            'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
            'transfers.view',  'transfers.create',  'transfers.manage',
            'reports.view',    'reports.export',
            'users.view',      'users.manage',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $viewer = Role::firstOrCreate(['name' => 'viewer']);

        // admin — todos los permisos
        $admin->syncPermissions($permissions);

        // manager — puede gestionar todo excepto usuarios
        $manager->syncPermissions(array_filter($permissions, fn ($p) => !str_starts_with($p, 'users.')));

        // viewer — solo lectura
        $viewer->syncPermissions([
            'products.view', 'categories.view', 'suppliers.view',
            'stock.view', 'warehouses.view', 'transfers.view',
            'reports.view',
        ]);

        // Asignar admin al primer usuario registrado
        $firstUser = User::first();
        if ($firstUser && !$firstUser->hasRole('admin')) {
            $firstUser->assignRole('admin');
        }
    }
}
