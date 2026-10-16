<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\UsersCreateRequest;
use App\Http\Requests\Users\UsersUpdateRequest;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

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
        $roles = $request->input('roles', []);
        $tags = $request->input('tags', []);
        $direction = $request->input('direction', 'asc');

        $allowedSorts = ['name', 'email'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? strtolower($direction) : 'asc';

        $users = User::query()
            ->with([
                'roles:id,name',
                'tags:id,name,color',
                'profile.contacts'
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(!empty($roles), function ($query) use ($roles) {
                $query->whereHas('roles', function ($q) use ($roles) {
                    $q->whereIn('name', $roles);
                });
            })
            ->when(!empty($tags), function ($query) use ($tags) {
                $query->whereHas('tags', function ($q) use ($tags) {
                    $q->whereIn('tags.id', $tags);
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
            'availableRoles' => Role::select('id', 'name')->get(),
            'availableTags' => Tag::select('id', 'name', 'color')->get(),
            'filters'  => [
                'search' => $search,
                'roles' => $roles,
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

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-!=+';
        $randomPassword = '';
        $maxIndex = strlen($chars) - 1;

        for ($i = 0; $i < 8; $i++) {
            $randomPassword .= $chars[random_int(0, $maxIndex)];
        }
        $userData['password'] = Hash::make($randomPassword);

        $user = User::create($userData);

        if (!$user->profile || !$user->profile->exists) {
            $user->profile()->create();
            $user->load('profile');
        }

        $user->profile()->update(['bio' => $validated['bio'] ?? null]);
        if (!empty($validated['contacts'])) {
            $user->profile->syncContacts($validated['contacts']);
        }

        if($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatars');
        }

        if($request->has('roles')) {
            $user->syncRoles($validated['roles']);
        }

        if($request->has('tags')) {
            $user->syncTags($validated['tags']);
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

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        if (!$user->profile->exists) {
            $user->profile()->create();
            $user->load('profile');
        }

        $user->profile()->update(['bio' => $validated['bio'] ?? null]);
        if (array_key_exists('contacts', $validated)) {
            $user->profile->syncContacts($validated['contacts'] ?? []);
        }


        if($request->hasFile('avatar')) {
            $user->clearMediaCollection('avatars');
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatars');
        }

        if (array_key_exists('roles', $validated)) {
            $oldRoles = $user->roles->pluck('name')->sort()->values()->toArray();
            $newRoles = collect($validated['roles'])->sort()->values()->toArray();

            if ($oldRoles !== $newRoles) {
                $currentUser = \Illuminate\Support\Facades\Auth::user();

                \Spatie\Activitylog\Models\Activity::create([
                    'log_name' => 'roles',
                    'description' => "Роли пользователя '{$user->name}' изменены",
                    'subject_type' => \App\Models\User::class,
                    'subject_id' => $user->id,
                    'causer_type' => $currentUser ? get_class($currentUser) : null,
                    'causer_id' => $currentUser?->id,
                    'properties' => [
                        'old' => $oldRoles,
                        'new' => $newRoles,
                    ],
                ]);
            }

            $user->syncRoles($validated['roles']);
        }

        if($request->has('tags')) {
            $user->syncTags($validated['tags']);
        }

        return redirect()->back()
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

    /**
     * Summary of show
     * @param User $user
     * @return \Inertia\Response
     */
    public function show(User $user): Response
    {
        $user->load([
            'roles:id,name',
            'tags:id,name,color',
            'media',
            'profile.contacts',
        ]);

        $user->avatar_url = $user->avatar_url;

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }
}
