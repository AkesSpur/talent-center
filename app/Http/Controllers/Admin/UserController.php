<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActionLogService;
use App\Traits\HandlesImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use HandlesImages;

    public function index(Request $request): View
    {
        $query = User::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $totalCount = User::count();
        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users', 'totalCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'patronymic' => ['nullable', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'role'       => ['required', 'in:admin,participant,support'],
            'password'   => ['required', 'string', 'min:6'],
        ], [
            'email.unique'         => 'Пользователь с таким email уже зарегистрирован.',
            'email.required'       => 'Укажите email.',
            'first_name.required'  => 'Укажите имя.',
            'last_name.required'   => 'Укажите фамилию.',
            'password.required'    => 'Укажите пароль.',
            'password.min'         => 'Пароль должен содержать минимум 6 символов.',
        ]);

        $user = User::create([
            'first_name'        => $validated['first_name'],
            'last_name'         => $validated['last_name'],
            'patronymic'        => $validated['patronymic'] ?? null,
            'phone'             => $validated['phone'] ?? null,
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'role'              => $validated['role'],
            'email_verified_at' => now(),
        ]);

        ActionLogService::log('user.created', $user);

        return redirect()->route('admin.users.index')
            ->with('status', 'user-created');
    }

    public function show(User $user): View
    {
        $user->load(['organizations', 'children', 'createdOrganizations']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'patronymic' => ['nullable', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'education' => ['nullable', 'string', 'max:255'],
            'role' => ['sometimes', 'in:admin,participant,support'],
            'is_blocked' => ['sometimes', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp,bmp', 'max:4096'],
        ]);

        $changes = [];

        // Track role change
        if (isset($validated['role']) && $user->role->value !== $validated['role']) {
            $changes['role'] = ['from' => $user->role->value, 'to' => $validated['role']];
            $user->role = UserRole::from($validated['role']);
        }

        // Track block change
        if (isset($validated['is_blocked'])) {
            $blocked = (bool) $validated['is_blocked'];
            if ($user->is_blocked !== $blocked) {
                $changes['is_blocked'] = ['from' => $user->is_blocked, 'to' => $blocked];
                $user->is_blocked = $blocked;
            }
        }

        // Handle profile fields
        $profileFields = ['first_name', 'last_name', 'patronymic', 'email', 'phone', 'bio', 'city', 'country', 'education'];
        foreach ($profileFields as $field) {
            if (array_key_exists($field, $validated) && $user->{$field} !== $validated[$field]) {
                $changes[$field] = ['from' => $user->{$field}, 'to' => $validated[$field]];
                $user->{$field} = $validated[$field];
            }
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $this->deleteStoredImage($user->avatar_path);
            $path = $this->storeImageAsWebp($request->file('avatar'), 'avatars', 512);
            $changes['avatar'] = 'updated';
            $user->avatar_path = $path;
        }

        if (! empty($changes)) {
            $user->save();
            ActionLogService::log('user.updated', $user, $changes);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('status', 'user-updated');
    }
}
