<?php

namespace Database\Seeders;

use App\Enums\User\UserStatus;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userData = [
            'name' => "Aurel Spahiu",
            'email' => "aurelspahiu62@gmail.com",
            'birthdate' => "2002-06-06",
            'password' => Hash::make('1234'),
            'status' => UserStatus::ACTIVE
        ];

        User::query()->updateOrCreate(['email' => $userData["email"]], $userData);
    }
}
