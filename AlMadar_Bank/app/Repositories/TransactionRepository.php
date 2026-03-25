<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function findById(int $id): ?Transaction
    {
        return Transaction::find($id);
    }
    public function getByAccountId(int $accountId): Collection
    {
        return Transaction::where('account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}