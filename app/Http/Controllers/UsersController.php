<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\UsersCreateRequest;
use App\Http\Requests\Users\UsersUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(): Response
    {
        // Получаем список пользователей с пагинацией и отсортируем по имени
        $users = User::orderBy('name')
            ->paginate(15);

        return inertia('Users/Index', [
            'userList' => $users,
        ]);
    }

    public function create(): Response
    {
        return inertia('Users/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UsersCreateRequest $request
     * @return void
     */
    public function store(UsersCreateRequest $request)
    {

        $randomPassword = Str::random(12);

         $validatedData = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($randomPassword),
        ];

        $user = User::create($validatedData);

        return redirect('/users/' . $user->id)
            ->withSuccess('Пользователь успешно создан! Сгенерированный пароль: ' . $randomPassword);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return \Inertia\Response
     */
    public function show(User $user): Response
    {
        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Edit the specified resource.
     *
     * @param User $user
     * @return \Inertia\Response
     */
    public function edit(User $user): Response
    {
        return inertia('Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return \Inertia\Response
     */
    public function update(UsersUpdateRequest $request, User $user): Response
    {
        $user->update($request->validated());
        return $this->edit($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        // Проверка: нельзя удалять себя или админов
        if ($user->id === Auth::id()) {
            abort(403, 'Вы не можете удалить свой собственный аккаунт');
        }

        if ($user->hasRole('admin')) {
            abort(403, 'Нельзя удалять административные аккаунты');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->withSuccess('Пользователь успешно удален!');
        // return $this->index();
    }
}

