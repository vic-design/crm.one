<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\RolesAttachRequest;
use App\Http\Requests\Roles\RolesCreateRequest;
use App\Http\Requests\Roles\RolesDetachRequest;
use App\Http\Requests\Roles\RolesUpdateRequest;
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
     * Summary of index
     * @param Request $request
     * @return InertiaResponse
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
            'filters'     => ['search' => $search],
            'permissions' => Permission::select(['id', 'name', 'guard_name'])->get(),
            'guard_name'  => 'web',
        ]);
    }

    /**
     * Summary of store
     * @param RolesCreateRequest $request
     * @return RedirectResponse
     */
    public function store(RolesCreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'],
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Роль успешно создана');
    }

    /**
     * Summary of update
     * @param Role $role
     * @param RolesUpdateRequest $request
     * @return RedirectResponse
     */
    public function update(Role $role, RolesUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role->update([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Роль успешно обновлена');
    }

    /**
     * Summary of destroy
     * @param Role $role
     * @return RedirectResponse
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return redirect()->back()
                ->with('error', 'Нельзя удалить роль, к которой привязаны пользователи');
        }

        if ($role->name === 'admin') {
            return redirect()->back()
                ->with('error', 'Удаление корневой роли администратора запрещено системой');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Роль успешно удалена');
    }

    /**
     * Summary of attach
     * @param RolesAttachRequest $request
     * @return RedirectResponse
     */
    public function attach(RolesAttachRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::findOrFail($validated['user_id']);
        $user->assignRole($validated['role_name']);

        return redirect()->back()->with('success', 'Роль успешно назначена');
    }

    /**
     * Summary of detach
     * @param RolesDetachRequest $request
     * @return RedirectResponse
     */
    public function detach(RolesDetachRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::findOrFail($validated['user_id']);
        $user->removeRole($validated['role_name']);

        return redirect()->back()->with('success', 'Роль успешно удалена у пользователя');
    }
}
