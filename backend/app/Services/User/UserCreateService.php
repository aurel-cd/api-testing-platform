<?php

namespace App\Services\User;

use App\Models\User\User;

class UserCreateService
{
    public function create(array $newUserData): User
    {
        return User::query()->create($newUserData);
    }
}
