<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\AccountRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;

class TransactionService
{
    public function __construct(
        protected TransactionRepositoryInterface $transactionRepository,
        protected AccountRepositoryInterface     $accountRepository
    ) {}

    public function getTransaction(int $id): Transaction
    {
        $transaction = $this->transactionRepository->findById($id);

        if (!$transaction) {
            throw new Exception("Transaction not found.");
        }

        $account = $this->accountRepository->findById($transaction->account_id);
        if (!$account || !$account->users()->where('user_id', auth()->id())->exists()) {
            throw new Exception("Unauthorized.");
        }

        return $transaction;
    }

    public function getAccountHistory(int $accountId, array $filters = []): Collection
    {
        $account = $this->accountRepository->findById($accountId);

        if (!$account) {
            throw new Exception("Account not found.");
        }

        if (!$account->users()->where('user_id', auth()->id())->exists()) {
            throw new Exception("Unauthorized.");
        }

        return $this->transactionRepository->getByAccountId($accountId, $filters);
    }
}