<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\UsersCreateRequest;
use App\Http\Requests\Users\UsersUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
    public function index(): Response
    {
        $users = User::orderBy('name')->paginate(15);

        return Inertia::render('Users/Index', [
            'userList' => $users,
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
        User::create($validated);

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
