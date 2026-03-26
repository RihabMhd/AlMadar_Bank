<?php

namespace App\Repositories;

use App\Models\Account;
use Illuminate\Support\Collection;

interface AccountRepositoryInterface
{
    public function allForUser(int $userId): Collection;
    public function findById(int $id): ?Account;
    public function create(array $data): Account;
    public function updateType(Account $account): Account;
    public function addCoHolder(Account $account, int $userId): void;
    public function removeCoHolder(Account $account, int $userId): void;
    public function acceptClosure(Account $account, int $userId): void;
    public function closeAccount(Account $account): void;
    public function incrementBalance(int $id, float $amount): void; 
    public function decrementBalance(int $id, float $amount): void;
}