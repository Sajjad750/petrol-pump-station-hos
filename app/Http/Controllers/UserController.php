<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = User::query()->with(['roles', 'permissions']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $roles = Role::all();
        $permissions = Permission::all();

        return view('users.create', compact('roles', 'permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        // Assign roles
        if (! empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        // Assign permissions
        if (! empty($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $user->load(['roles.permissions', 'permissions']);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $user->load(['roles', 'permissions']);
        $roles = Role::all();
        $permissions = Permission::all();

        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update password if provided
        if (! empty($validated['password'])) {
            $user->update([
                'password' => bcrypt($validated['password']),
            ]);
        }

        // Sync roles
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        } else {
            $user->syncRoles([]);
        }

        // Sync permissions
        if (isset($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        } else {
            $user->syncPermissions([]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting the currently authenticated user
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
