<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\ProfileRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    public function __construct(
        private ProfileRepositoryInterface $profileRepo
    ) {}

    public function getProfile(): User
    {
        return auth('api')->user();
    }

    public function updateProfile(array $data): User
    {
        $user = auth('api')->user();
        return $this->profileRepo->update($user, $data);
    }

    public function updatePassword(array $data): void
    {
        $user = auth('api')->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            throw new \Exception('Current password is incorrect.', 422);
        }

        $this->profileRepo->updatePassword($user, $data['new_password']);
    }

    public function deleteAccount(): void
    {
        $user = auth('api')->user();
        auth('api')->logout();
        $this->profileRepo->delete($user);
    }
}