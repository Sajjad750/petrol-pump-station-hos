<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', 'admin')->first();
        User::updateOrCreate(
            ['email' => 'user@hos.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('user'),
                'role_id' => $role->id ?? null,
            ]
        );
    }
}
