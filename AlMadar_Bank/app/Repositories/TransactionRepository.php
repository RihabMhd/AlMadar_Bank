<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function findById(int $id): ?Transaction
    {
        return Transaction::find($id);
    }

    public function getByAccountId(int $accountId, array $filters = []): Collection
    {
        $query = Transaction::where('account_id', $accountId);

        if (isset($filters['type'])) {
            $query->where('type', strtoupper($filters['type']));
        }

        if (isset($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}