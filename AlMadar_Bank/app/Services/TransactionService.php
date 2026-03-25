<?php

namespace App\Services;

use App\Repositories\TransactionRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;

class TransactionService
{
    protected $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getTransaction(int $id)
    {
        $transaction = $this->transactionRepository->findById($id);

        if (!$transaction) {
            throw new Exception("Transaction not found.");
        }

        return $transaction;
    }

    public function getAccountHistory(int $accountId): Collection
    {
        return $this->transactionRepository->getByAccountId($accountId);
    }
}