<?php

namespace App\Services;

use App\Repositories\TransactionRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;

class TransactionService
{
    public function __construct(
        protected TransactionRepositoryInterface $transactionRepository
    ) {}

    public function getTransaction(int $id)
    {
        $transaction = $this->transactionRepository->findById($id);

        if (!$transaction) {
            throw new Exception("Transaction not found.");
        }

        return $transaction;
    }

    public function getAccountHistory(int $accountId, array $filters = []): Collection
    {
        return $this->transactionRepository->getByAccountId($accountId, $filters);
    }
}
