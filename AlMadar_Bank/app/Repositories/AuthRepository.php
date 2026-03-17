<?php

namespace App\Repositories;

use App\Repositories\AuthRepositoryInterface;
use App\Models\User;

class AuthRepository implements AuthRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create([
            'nom'            => $data['name'],
            'prenom'         => $data['prenom'] ?? '',
            'email'    => $data['email'],
            'date_naissance' => $data['date_naissance'] ?? null,
            'password' => bcrypt($data['password']),
        ]);
    }
}
