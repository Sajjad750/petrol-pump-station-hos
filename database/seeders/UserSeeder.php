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
                'password' => Hash::make('Crover$20004026'),
                'role_id' => $role->id ?? null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@autostation.com.sa'],
            [
                'name' => 'Admin HOS',
                'password' => Hash::make('Crover$20004026'),
                'role_id' => $role->id ?? null,
            ]
        );
    }
}
