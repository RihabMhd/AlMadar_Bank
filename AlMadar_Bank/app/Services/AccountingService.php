<?php

namespace App\Services;

use App\Models\Account;
use App\Repositories\AccountRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function __construct(
        protected TransactionRepositoryInterface $transactionRepository,
        protected AccountRepositoryInterface     $accountRepository
    ) {}

    public function processMonthlyRoutine(): void
    {
        $accounts = Account::where('status', 'ACTIVE')->get();

        foreach ($accounts as $account) {
            DB::transaction(function () use ($account) {
                if ($account->type === 'COURANT') {
                    $this->applyTransaction($account, 'FEE', 5.00, 'Monthly management fee');
                }

                if ($account->type === 'EPARGNE' || $account->type === 'MINEUR') {
                    $interest = $account->balance * 0.01;
                    if ($interest > 0) {
                        $this->applyTransaction($account, 'INTEREST', $interest, 'Monthly interest credit');
                    }
                }
            });
        }
    }

    private function applyTransaction(Account $account, string $type, float $amount, string $label): void
    {
        $this->transactionRepository->create([
            'account_id' => $account->id,
            'type'       => $type,
            'amount'     => $amount,
            'label'      => $label,
        ]);

        if ($type === 'FEE') {
            $this->accountRepository->decrementBalance($account->id, $amount);
        } else {
            $this->accountRepository->incrementBalance($account->id, $amount);
        }
    }
}