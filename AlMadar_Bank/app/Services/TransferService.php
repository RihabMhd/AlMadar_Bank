<?php

namespace App\Services;

use App\Models\Transfer;
use App\Repositories\AccountRepositoryInterface;
use App\Repositories\TransferRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class TransferService
{
    public function __construct(
        protected TransferRepositoryInterface    $transferRepository,
        protected TransactionRepositoryInterface $transactionRepository,
        protected AccountRepositoryInterface     $accountRepository,
        protected AccountService                 $accountService
    ) {}

    public function getTransfer(int $id): Transfer
    {
        $transfer = $this->transferRepository->findById($id);
        if (!$transfer) {
            throw new Exception("Transfer not found.");
        }
        return $transfer;
    }

    public function getTransfersForUser(int $userId): Collection
    {
        return Transfer::where('initiated_by', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createTransfer(array $data): Transfer
    {
        return DB::transaction(function () use ($data) {

            $amount   = (float) $data['amount'];
            $userId   = auth()->id();

            if ($amount <= 0) {
                throw new Exception("Amount must be positive.");
            }

            $sender   = $this->accountRepository->findById((int) $data['sender_id']);
            $receiver = $this->accountRepository->findById((int) $data['receiver_id']);

            if (!$sender || !$receiver) {
                throw new Exception("One or both accounts were not found.");
            }

            if ($sender->status !== 'ACTIVE') {
                throw new Exception("Sender account is not active.");
            }

            if ($receiver->status !== 'ACTIVE') {
                throw new Exception("Receiver account is not active.");
            }

            if ($sender->type === 'MINEUR') {
                if (!$this->accountService->isGuardianOf($sender, $userId)) {
                    throw new Exception("Only the guardian can initiate transfers from a minor account.");
                }
            } else {
                $this->accountService->authorizeHolder($sender);
            }

            $this->checkWithdrawalLimit($sender);

            $this->checkDailyLimit($sender, $amount);

            if ($sender->type === 'COURANT') {
                $available = $sender->balance + $sender->overdraft_limit;
                if ($available < $amount) {
                    throw new Exception("Insufficient balance (including authorised overdraft).");
                }
            } else {
                if ($sender->balance < $amount) {
                    throw new Exception("Insufficient balance.");
                }
            }

            $transfer = $this->transferRepository->initiateTransfer($data);

            try {
                $this->accountRepository->decrementBalance($sender->id, $amount);
                $this->accountRepository->incrementBalance($receiver->id, $amount);

                $this->accountRepository->incrementWithdrawalCount($sender->id);
                $this->updateDailyTotal($sender, $amount);

                $this->transactionRepository->create([
                    'account_id'  => $sender->id,
                    'transfer_id' => $transfer->id,
                    'type'        => 'TRANSFER_OUT',
                    'amount'      => $amount,
                    'label'       => 'Transfer to ' . $receiver->rib,
                ]);

                $this->transactionRepository->create([
                    'account_id'  => $receiver->id,
                    'transfer_id' => $transfer->id,
                    'type'        => 'TRANSFER_IN',
                    'amount'      => $amount,
                    'label'       => 'Transfer from ' . $sender->rib,
                ]);

                $this->transferRepository->updateStatus($transfer->id, 'COMPLETED');

            } catch (Exception $e) {
                $this->transferRepository->updateStatus($transfer->id, 'FAILED');
                throw $e;
            }

            return $transfer->fresh();
        });
    }


    private function checkWithdrawalLimit($sender): void
    {
        $limit = match($sender->type) {
            'EPARGNE' => 3,
            'MINEUR'  => 2,
            default   => null,
        };

        if ($limit === null) return;

        if ($sender->withdrawal_reset_date &&
            Carbon::parse($sender->withdrawal_reset_date)->lt(now()->startOfMonth())) {
            $this->accountRepository->resetWithdrawalCount($sender->id);
            $sender->refresh();
        }

        if ($sender->withdrawal_count >= $limit) {
            throw new Exception("Monthly withdrawal limit of {$limit} reached for this account type.");
        }
    }

    private function checkDailyLimit($sender, float $amount): void
    {
        if ($sender->last_transfer_date &&
            Carbon::parse($sender->last_transfer_date)->lt(now()->startOfDay())) {
            $this->accountRepository->resetDailyTransferTotal($sender->id);
            $sender->refresh();
        }

        $projectedTotal = $sender->daily_transfer_total + $amount;

        if ($projectedTotal > $sender->daily_transfer_limit) {
            throw new Exception(
                "Daily transfer limit of {$sender->daily_transfer_limit} MAD exceeded."
            );
        }
    }

    private function updateDailyTotal($sender, float $amount): void
    {
        if ($sender->last_transfer_date &&
            Carbon::parse($sender->last_transfer_date)->lt(now()->startOfDay())) {
            $this->accountRepository->resetDailyTransferTotal($sender->id);
        }

        $this->accountRepository->incrementDailyTransferTotal($sender->id, $amount);
    }
}