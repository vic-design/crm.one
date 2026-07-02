<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesController extends Controller
{
    /**
     * Дисплей списка ролей с пагинацией Inertia v2.
     */
    public function index(Request $request): InertiaResponse
    {
        $search = $request->input('search');

        $roles = Role::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->with('permissions:id,name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Roles/Index', [
            'roleList'    => $roles,
            'filters'     => [
                'search' => $search
            ],
            'permissions' => Permission::select(['id', 'name', 'guard_name'])->get(),
            'guard_name'  => 'web',
        ]);
    }


    /**
     * Дисплей формы создания роли.
     */
    public function create(Request $request): InertiaResponse
    {
        return Inertia::render('Roles/Create', [
            'permissions' => Permission::select(['id', 'name', 'guard_name'])->get(),
            'guard_name' => $request->input('guard_name', 'web'),
        ]);
    }

    /**
     * Сохранение новой роли.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id', // Валидируем каждый ID
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'],
        ]);

        if (!empty($validated['permissions'])) {
            // Передаем массив ID напрямую — Spatie v7 это поддерживает
            $role->syncPermissions($validated['permissions']);
        }

        // Правильный Inertia-редирект с флеш-сообщением
        return redirect()->route('roles.index')
            ->with('success', 'Роль успешно создана');
    }

    /**
     * Дисплей формы редактирования роли.
     */
    public function edit(Role $role): InertiaResponse
    {
        return Inertia::render('Roles/Edit', [
            // Передаем роль и массив только ID выбранных разрешений для чекбоксов
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $role->permissions->pluck('id'),
            ],
            'permissions' => Permission::select(['id', 'name', 'guard_name'])->get(),
        ]);
    }

    /**
     * Обновление роли.
     */
    public function update(Role $role, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Валидация уникальности с исключением текущей роли
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'guard_name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Роль успешно обновлена');
    }

    /**
     * Удаление роли.
     */
    public function destroy(Role $role): RedirectResponse
    {
        // В Spatie связь с пользователями называется users
        if ($role->users()->exists()) {
            return redirect()->back()
                ->with('error', 'Нельзя удалить роль, к которой привязаны пользователи');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Роль успешно удалена');
    }

    /**
     * API / Синхронный метод: Назначение роли пользователю.
     */
    public function attach(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role_name' => 'required|string|exists:roles,name',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->assignRole($validated['role_name']); // Используем метод Spatie вместо attach()

        return redirect()->back()->with('success', 'Роль успешно назначена');
    }

    /**
     * API / Синхронный метод: Отмена назначения роли.
     */
    public function detach(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role_name' => 'required|string|exists:roles,name',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->removeRole($validated['role_name']); // Используем метод Spatie вместо detach()

        return redirect()->back()->with('success', 'Роль успешно удалена у пользователя');
    }
}
