<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the primary role for this user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the roles for this user (many-to-many).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the direct permissions for this user.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role|string|array $roles): self
    {
        $roles = is_array($roles) ? $roles : [$roles];

        foreach ($roles as $role) {
            if (is_string($role)) {
                $role = Role::where('name', $role)->firstOrFail();
            }

            $this->roles()->syncWithoutDetaching($role);
        }

        return $this;
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(Role|string $role): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        $this->roles()->detach($role);

        return $this;
    }

    /**
     * Sync roles with the user.
     */
    public function syncRoles(array $roles): self
    {
        $roleIds = collect($roles)->map(function ($role) {
            if (is_string($role)) {
                return Role::where('name', $role)->firstOrFail()->id;
            }

            return is_object($role) ? $role->id : $role;
        });

        $this->roles()->sync($roleIds);

        return $this;
    }

    /**
     * Give a permission to the user.
     */
    public function givePermissionTo(Permission|string|array $permissions): self
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];

        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $permission = Permission::where('name', $permission)->firstOrFail();
            }

            $this->permissions()->syncWithoutDetaching($permission);
        }

        return $this;
    }

    /**
     * Revoke a permission from the user.
     */
    public function revokePermissionTo(Permission|string $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission);

        return $this;
    }

    /**
     * Sync permissions with the user.
     */
    public function syncPermissions(array $permissions): self
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('name', $permission)->firstOrFail()->id;
            }

            return is_object($permission) ? $permission->id : $permission;
        });

        $this->permissions()->sync($permissionIds);

        return $this;
    }

    /**
     * Check if user has a role.
     */
    public function hasRole(Role|string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        foreach ($roles as $role) {
            if (is_string($role)) {
                if ($this->roles()->where('name', $role)->exists()) {
                    return true;
                }
            } elseif ($this->roles()->where('id', $role->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a permission (either directly or through a role).
     */
    public function hasPermission(Permission|string $permission): bool
    {
        // Check direct permissions
        if (is_string($permission)) {
            if ($this->permissions()->where('name', $permission)->exists()) {
                return true;
            }
        } elseif ($this->permissions()->where('id', $permission->id)->exists()) {
            return true;
        }

        // Check permissions through roles
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all permissions for the user (direct and through roles).
     */
    public function getAllPermissions()
    {
        $directPermissions = $this->permissions;

        $rolePermissions = $this->roles->map(function ($role) {
            return $role->permissions;
        })->flatten();

        return $directPermissions->merge($rolePermissions)->unique('id');
    }
}
