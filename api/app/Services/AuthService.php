<?php

namespace App\Services;

use App\Actions\Auth\CreateTokenAction;
use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Contracts\Services\AuthServiceInterface;
use App\Models\User;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private RegisterUserAction $registerUserAction,
        private LoginUserAction $loginUserAction,
        private CreateTokenAction $createTokenAction
    ) {}

    public function register(array $data): array
    {
        $user = $this->registerUserAction->execute($data);
        $token = $this->createTokenAction->execute($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials): string
    {
        $user = $this->loginUserAction->execute($credentials);
        return $this->createTokenAction->execute($user);
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        } else {
            $user->tokens()->delete();
        }
    }
}
