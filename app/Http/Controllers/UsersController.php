<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\UsersCreateRequest;
use App\Http\Requests\Users\UsersUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    /**
     * Summary of index
     * @return Response
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');

        $allowedSorts = ['name', 'email'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? strtolower($direction) : 'asc';

        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(function ($user) {
                $user->avatar_url = $user->avatar_url;
                return $user;
            });

        return Inertia::render('Users/Index', [
            'userList' => $users,
            'filters'  => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    /**
     * Summary of store
     * @param UsersCreateRequest $request
     * @return RedirectResponse
     */
    public function store(UsersCreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-!=+';
        $randomPassword = '';
        $maxIndex = strlen($chars) - 1;

        for ($i = 0; $i < 8; $i++) {
            $randomPassword .= $chars[random_int(0, $maxIndex)];
        }

        $validated['password'] = Hash::make($randomPassword);
        $user = User::create($validated);

        if($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatars');
        }

        return redirect()->route('users.index')
            ->with('success', 'Пользователь создан! Пароль: ' . $randomPassword);
    }

    /**
     * Summary of update
     * @param UsersUpdateRequest $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(UsersUpdateRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if($request->hasFile('avatar')) {
            $user->clearMediaCollection('avatars');
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatars');
        }

        return redirect()->route('users.index')
            ->with('success', 'Пользователь обновлен!');
    }

    /**
     * Summary of destroy
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            abort(403, 'Нельзя удалить свой аккаунт');
        }

        if ($user->hasRole('admin')) {
            abort(403, 'Нельзя удалять админов');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Пользователь удален!');
    }
}
