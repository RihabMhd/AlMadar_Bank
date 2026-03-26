<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    public function create(array $data): Transaction;
    public function findById(int $id): ?Transaction;
    public function getByAccountId(int $accountId): Collection;
}