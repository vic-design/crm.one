<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RolesController extends Controller
{
    /**
     * Дисплей списка ролей (для Inertia/Vue пагинации).
     */
    public function index(Request $request)
    {
        $query = Role::query()
            ->orderBy('name', 'asc');

        // Фильтрация
        if ($request->input('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $roles = $query->paginate(10);

        // Формируем данные для Inertia
        return inertia()->render('Roles/Index', [
            'roles' => $roles->items(),
            'pagination' => $roles->appends(['search' => $request->search]),
            // 'links' => $roles->links('pagination::pagination'), // Или кастомный вид
        ]);
    }

    /**
     * Дисплей форму создания роли.
     */
    public function create(Request $request)
    {
        // Загружаем все права для чекбоксов
        $permissions = Permission::all();

        return inertia()->render('Roles/Create', [
            'permissions' => $permissions->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'guard_name' => $p->guard_name,
            ]),
            'guard_name' => $request->input('guard_name') ?? 'web',
        ]);
    }

    /**
     * Внутренняя Inertia: Сохранение новой роли.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'guard_name' => 'required|string',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create([
            'name' => $validated->name,
            'guard_name' => $validated->guard_name,
        ]);

        // Назначаем права
        if (!empty($validated->permissions)) {
            $role->syncPermissions($validated->permissions);
        }

        return inertia()->location(route('roles.index'))->only([
            'role' => $role->load('permissions')
        ]);
    }

    /**
     * Внутренняя Inertia: Редактирование роли.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();

        return inertia()->render('Roles/Edit', [
            'role' => $role->load('permissions'),
            'permissions' => $permissions->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'guard_name' => $p->guard_name,
            ]),
        ]);
    }

    /**
     * Внутренняя Inertia: Обновление роли.
     */
    public function update(Role $role, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'guard_name' => 'required|string',
            'permissions' => 'nullable|array',
        ]);

        $role->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name,
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return inertia()->location(route('roles.index'))->only([
            'message' => 'Role updated successfully',
            'role' => $role->load('permissions')
        ]);
    }

    /**
     * Внутренняя Inertia: Удаление роли.
     */
    public function destroy(Role $role)
    {
        // Проверка: нельзя удалять роль, если у нее есть пользователи
        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'Cannot delete role with assigned users'], 422);
        }

        $role->delete();

        return inertia()->location(route('roles.index'))->only([
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * API: Назначение роли пользователю.
     */
    public function attach(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $role = Role::findOrFail($request->input('role_id'));
        $user = User::findOrFail($request->input('user_id'));

        $role->users()->attach($user->id);

        return response()->json(['message' => 'Role attached to user'], 200);
    }

    /**
     * API: Отмена назначения роли.
     */
    public function detach(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $role = Role::findOrFail($request->input('role_id'));
        $user = User::findOrFail($request->input('user_id'));

        $role->users()->detach($user->id);

        return response()->json(['message' => 'Role detached from user'], 200);
    }
}
