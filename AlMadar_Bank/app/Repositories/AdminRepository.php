<?php

namespace App\Repositories;

use App\Models\Account;
use Illuminate\Support\Collection;

class AdminRepository implements AdminRepositoryInterface
{
    public function getAll(): Collection
    {
        return Account::with(['users', 'transactions'])->get();
    }

    public function findById(int $id): ?Account
    {
        return Account::find($id);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $account = $this->findById($id);
        if ($account) {
            return $account->update(['status' => $status]);
        }
        return false;
    }
}
