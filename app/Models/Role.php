<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    /**
     * Get the users that have this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Give this role a permission.
     */
    public function givePermissionTo(string $permission): self
    {
        $permissions = $this->permissions ?? [];

        if (! in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }

        return $this;
    }

    /**
     * Remove a permission from this role.
     */
    public function revokePermissionTo(string $permission): self
    {
        $permissions = $this->permissions ?? [];

        if (($key = array_search($permission, $permissions)) !== false) {
            unset($permissions[$key]);
            $this->permissions = array_values($permissions);
            $this->save();
        }

        return $this;
    }

    /**
     * Sync permissions with this role.
     */
    public function syncPermissions(array $permissions): self
    {
        $this->permissions = $permissions;
        $this->save();

        return $this;
    }

    /**
     * Check if role has a permission.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array($permission, $permissions);
    }
}
