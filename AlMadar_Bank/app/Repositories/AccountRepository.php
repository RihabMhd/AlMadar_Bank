<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\User;
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

    public function create(array $data): Account
    {
        $data['rib'] = 'MA' . strtoupper(uniqid()) . rand(100, 999);

        $data['balance'] = $data['balance'] ?? 0;

        $account = Account::create($data);

        $role = ($data['type'] === 'MINEUR') ? 'guardian' : 'owner';
        $account->users()->attach(auth()->id(), [
            'relation_type'    => $role,
            'accepted_closure' => false,
        ]);

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

    public function closeAccount(Account $account): void
    {

        if ($account->balance != 0) {
            throw new \Exception("Account balance must be zero to close.");
        }

        $holders = $account->users()->get();
        $allAgreed = $holders->every(fn($user) => $user->pivot->accepted_closure);

        if ($allAgreed) {
            $account->update(['status' => 'CLOSED']);
        }
    }
}
