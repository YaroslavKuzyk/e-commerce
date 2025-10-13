<?php

namespace App\Actions\Auth;

use App\Models\User;

class CreateTokenAction
{
    public function execute(User $user, string $tokenName = 'auth-token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }
}
