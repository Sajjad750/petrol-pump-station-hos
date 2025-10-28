<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * Get the role for this user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role|string|int $role): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        $this->role_id = is_object($role) ? $role->id : $role;
        $this->save();

        return $this;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(Role|string|int $role): bool
    {
        if (! $this->role) {
            return false;
        }

        if (is_string($role)) {
            return $this->role->name === $role;
        }

        $roleId = is_object($role) ? $role->id : $role;

        return $this->role_id === $roleId;
    }

    /**
     * Check if user has a permission (through their role).
     */
    public function hasPermission(string $permission): bool
    {
        if (! $this->role) {
            return false;
        }

        return $this->role->hasPermission($permission);
    }

    /**
     * Get all permissions for the user (through their role).
     */
    public function getPermissions(): array
    {
        if (! $this->role) {
            return [];
        }

        return $this->role->permissions ?? [];
    }
}
