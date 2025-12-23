<?php

namespace App\Services;

use App\Actions\Auth\CreateTokenAction;
use App\Contracts\Services\Customer\AuthServiceInterface;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthService implements AuthServiceInterface
{
    public function __construct(
        private CreateTokenAction $createTokenAction
    ) {}

    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'type' => UserType::CUSTOMER,
            'status' => 'active',
        ]);

        $token = $this->createTokenAction->execute($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials): string
    {
        $user = User::where('email', $credentials['email'])
            ->where('type', UserType::CUSTOMER)
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }

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
