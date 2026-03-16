<?php

namespace App\Repositories;

use App\Models\User;

interface ProfileRepositoryInterface
{
    public function update(User $user, array $data): User;
    public function updatePassword(User $user, string $newPassword): User;
    public function delete(User $user): void;
}