<?php

namespace App\Repositories;

use App\Models\Account;
use Illuminate\Support\Collection;

class AccountRepository implements AccountRepositoryInterface
{
    public function allForUser(int $userId): Collection
    {
        return Account::whereHas('users', fn($q) => $q->where('user_id', $userId))->get();
    }

    public function findById(int $id): ?Account
    {
        return Account::find($id);
    }

    public function findByRib(string $rib): ?Account
    {
        return Account::where('rib', $rib)->first();
    }

    public function create(array $data): Account
    {
        $data['rib']     = 'MA' . strtoupper(uniqid()) . rand(100, 999);
        $data['balance'] = $data['balance'] ?? 0;

        $account = Account::create($data);

        if ($data['type'] !== 'MINEUR') {
            $account->users()->attach(auth()->id(), [
                'relation_type'    => 'owner',
                'accepted_closure' => false,
            ]);
        }

        return $account;
    }

    public function updateType(Account $account): Account
    {
        $account->update(['type' => 'COURANT']);
        return $account->fresh();
    }

    public function addCoHolder(Account $account, int $userId): void
    {
        $account->users()->attach($userId, [
            'relation_type'    => 'owner',
            'accepted_closure' => false,
        ]);
    }

    public function addGuardian(Account $account, int $userId): void
    {
        $account->users()->attach($userId, [
            'relation_type'    => 'guardian',
            'accepted_closure' => false,
        ]);
    }

    public function removeCoHolder(Account $account, int $userId): void
    {
        $account->users()->detach($userId);
    }

    public function acceptClosure(Account $account, int $userId): void
    {
        $account->users()->updateExistingPivot($userId, ['accepted_closure' => true]);
    }

    public function incrementBalance(int $id, float $amount): void
    {
        Account::where('id', $id)->increment('balance', $amount);
    }

    public function decrementBalance(int $id, float $amount): void
    {
        Account::where('id', $id)->decrement('balance', $amount);
    }

    public function incrementWithdrawalCount(int $id): void
    {
        Account::where('id', $id)->increment('withdrawal_count');
        Account::where('id', $id)->update(['withdrawal_reset_date' => now()->startOfMonth()]);
    }

    public function resetWithdrawalCount(int $id): void
    {
        Account::where('id', $id)->update([
            'withdrawal_count'      => 0,
            'withdrawal_reset_date' => now()->startOfMonth(),
        ]);
    }

    public function incrementDailyTransferTotal(int $id, float $amount): void
    {
        Account::where('id', $id)->increment('daily_transfer_total', $amount);
        Account::where('id', $id)->update(['last_transfer_date' => now()->toDateString()]);
    }

    public function resetDailyTransferTotal(int $id): void
    {
        Account::where('id', $id)->update([
            'daily_transfer_total' => 0,
            'last_transfer_date'   => now()->toDateString(),
        ]);
    }

    public function blockAccount(int $id, string $reason): void
    {
        Account::where('id', $id)->update([
            'status'       => 'BLOCKED',
            'block_reason' => $reason,
        ]);
    }

    public function closeAccount(Account $account): void
    {
        if ($account->balance != 0) {
            throw new \Exception("Account balance must be zero to close.");
        }

        $holders   = $account->users()->get();
        $allAgreed = $holders->every(fn($user) => $user->pivot->accepted_closure);

        if ($allAgreed) {
            $account->update(['status' => 'CLOSED']);
        }
    }
}