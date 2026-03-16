<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProfileRepository implements ProfileRepositoryInterface
{
    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function updatePassword(User $user, string $newPassword): User
    {
        $user->update(['password' => Hash::make($newPassword)]);
        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}