<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepositoryInterface;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(
        private AuthRepositoryInterface $authRepo
    ) {}

    public function register(array $data): array
    {
        $user  = $this->authRepo->create($data);
        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token, $user);
    }

    public function login(array $credentials): array
    {
        if (!$token = auth()->attempt($credentials)) {
            throw new \Exception('Invalid email or password.', 401);
        }

        return $this->respondWithToken($token, auth()->user());
    }

    public function logout(): void
    {
        auth()->logout();
    }

    public function refresh(): array
    {
        $token = auth()->refresh();
        return $this->respondWithToken($token, auth()->user());
    }

    public function me(): User
    {
        return auth()->userOrFail();
    }

    private function respondWithToken(string $token, User $user): array
    {
        return [
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
        ];
    }
}