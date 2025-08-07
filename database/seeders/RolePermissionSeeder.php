<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset de cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar permissões
        $permissions = [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Criar roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Atribuir todas as permissões ao admin
        $adminRole->givePermissionTo($permissions);

        // Atribuir apenas view_users ao user
        // $userRole->givePermissionTo(['view_users']);

        // Criar usuário admin padrão
        $admin = User::firstOrCreate([
            'email' => 'admin@admin.com'
        ], [
            'name' => 'Administrator',
            'password' => bcrypt('123456789'),
            'is_active' => true, // Importante adicionar este campo
        ]);

        if(!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Criar um usuário comum para teste
        $normalUser = User::firstOrCreate([
            'email' => 'admin@admin.com'
        ], [
            'name' => 'Usuário Comum',
            'password' => bcrypt('123456789'),
            'is_active' => true,
        ]);

        if(!$normalUser->hasRole('user')) {
            $normalUser->assignRole('user');
        }
    }
}