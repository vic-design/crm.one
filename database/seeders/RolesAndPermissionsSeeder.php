<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Сброс кэша ролей и разрешений
        resolve(PermissionRegistrar::class)->forgetCachedPermissions();

        // CRUD для Ролей и Разрешений (Новое!)
        $viewRoles = Permission::create(['name' => 'view roles']);
        $createRoles = Permission::create(['name' => 'create roles']);
        $editRoles = Permission::create(['name' => 'edit roles']);
        $deleteRoles = Permission::create(['name' => 'delete roles']);

        // CRUD для Пользователей (Users)
        $viewUsers = Permission::create(['name' => 'view users']);
        $createUsers = Permission::create(['name' => 'create users']);
        $editUsers = Permission::create(['name' => 'edit users']);
        $deleteUsers = Permission::create(['name' => 'delete users']);

        // 4. Полный CRUD для Контента (Content)
        $viewContent = Permission::create(['name' => 'view content']);
        $createContent = Permission::create(['name' => 'create content']);
        $editContent = Permission::create(['name' => 'edit content']);
        $deleteContent = Permission::create(['name' => 'delete content']);

        // ADMIN: Полный доступ ко всему без исключений
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // MANAGER: Жестко ограниченный доступ
        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            $viewRoles,

            $viewUsers,
            $createUsers,

            $viewContent,
            $createContent,
            $editContent,
            $deleteContent,
        ]);
    }
}
