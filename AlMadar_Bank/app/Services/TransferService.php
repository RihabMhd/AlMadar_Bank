<?php

namespace App\Services;

use App\Models\Transfer;
use App\Repositories\AccountRepository;
use App\Repositories\TransferRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferService
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository,
        protected TransactionRepositoryInterface $transactionRepository,
        protected AccountService $accountService,
        protected AccountRepository $accountRepo
    ) {}

    public function getTransfer(int $id): Transfer
    {
        $transfer = $this->transferRepository->findById($id);

        if (!$transfer) {
            throw new Exception("Transfer not found.");
        }

        return $transfer;
    }

    public function createTransfer(array $data): Transfer
    {
        return DB::transaction(function () use ($data) {
            if ($data['amount'] <= 0) throw new Exception("Amount must be positive.");

            $sender = $this->accountRepo->findById($data['sender_id']);
            if ($sender->balance < $data['amount']) throw new Exception("Insufficient balance.");

            $transfer = $this->transferRepository->initiateTransfer($data);

            return $transfer;
        });
    }
}