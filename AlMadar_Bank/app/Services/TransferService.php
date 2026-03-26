<?php

namespace App\Services;

use App\Models\Transfer;
use App\Repositories\TransferRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferService
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository,
        protected TransactionRepositoryInterface $transactionRepository,
        protected AccountService $accountService
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
            if ($data['amount'] <= 0) {
                throw new Exception("Amount must be positive.");
            }

            $senderAccount = $this->accountService->getAccount($data['sender_id']);
            $receiverAccount = $this->accountService->getAccount($data['receiver_id']);

            if ($senderAccount->balance < $data['amount']) {
                throw new Exception("Insufficient balance.");
            }

            $transfer = $this->transferRepository->initiateTransfer($data);

            $this->transactionRepository->create([
                'account_id'  => $senderAccount->id,
                'transfer_id' => $transfer->id,
                'type'        => 'DEBIT',
                'amount'      => $data['amount'],
                'label'       => "Transfer to Account #" . $receiverAccount->id,
            ]);

            $this->transactionRepository->create([
                'account_id'  => $receiverAccount->id,
                'transfer_id' => $transfer->id,
                'type'        => 'CREDIT',
                'amount'      => $data['amount'],
                'label'       => "Transfer from Account #" . $senderAccount->id,
            ]);

            $senderAccount->decrement('balance', $data['amount']);
            $receiverAccount->increment('balance', $data['amount']);

            return $transfer;
        });
    }
}