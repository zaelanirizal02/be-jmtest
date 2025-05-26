<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $roles = ['pendaftaran', 'dokter', 'perawat', 'apoteker'];

        foreach ($roles as $role) {
            User::create([
                'name' => ucfirst($role),
                'email' => "$role@admin.com",
                'password' => Hash::make('password'),
                'role' => $role,
            ]);
        }
    }
}
