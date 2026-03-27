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
        Account::whereIn('type', ['EPARGNE', 'MINEUR'])->each(function (Account $account) {
            $this->accountRepository->resetWithdrawalCount($account->id);
        });

        Account::where('status', 'ACTIVE')->each(function (Account $account) {
            DB::transaction(function () use ($account) {
                if ($account->type === 'COURANT') {
                    $this->applyMonthlyFee($account);
                }

                if (in_array($account->type, ['EPARGNE', 'MINEUR'])) {
                    $this->applyMonthlyInterest($account);
                }
            });
        });
    }


    private function applyMonthlyFee(Account $account): void
    {
        $fee = (float) $account->monthly_fee;

        if ($fee <= 0) return;

        $canCover = ($account->balance + $account->overdraft_limit) >= $fee;

        if ($canCover) {
            $this->accountRepository->decrementBalance($account->id, $fee);
            $this->transactionRepository->create([
                'account_id' => $account->id,
                'type'       => 'FEE',
                'amount'     => $fee,
                'label'      => 'Monthly management fee',
            ]);
        } else {
            $this->transactionRepository->create([
                'account_id' => $account->id,
                'type'       => 'FEE_FAILED',
                'amount'     => $fee,
                'label'      => 'Monthly fee failed — insufficient balance',
            ]);
            $this->accountRepository->blockAccount($account->id, 'Insufficient balance for monthly fee');
        }
    }


    private function applyMonthlyInterest(Account $account): void
    {
        $annualRate = (float) $account->interest_rate;

        if ($annualRate <= 0 || $account->balance <= 0) return;

        $monthlyInterest = round($account->balance * ($annualRate / 100 / 12), 2);

        if ($monthlyInterest <= 0) return;

        $this->accountRepository->incrementBalance($account->id, $monthlyInterest);
        $this->transactionRepository->create([
            'account_id' => $account->id,
            'type'       => 'INTEREST',
            'amount'     => $monthlyInterest,
            'label'      => "Monthly interest ({$annualRate}% p.a.)",
        ]);
    }
}