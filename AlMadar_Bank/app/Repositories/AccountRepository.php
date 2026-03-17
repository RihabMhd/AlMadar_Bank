<?php

namespace App\Repositories;

use App\Models\Account;
use App\Repositories\AccountRepositoryInterface;
use Illuminate\Support\Collection;

class AccountRepository implements AccountRepositoryInterface
{

    public function all(): Collection
    {
        return Account::all();
    }

    public function findById(int $id): ?Account
    {
        return Account::find($id);
    }

    public function create(array $data): Account
    {
        $data['rib'] = 'MA' . strtoupper(uniqid()) . rand(100, 999);
        $account = Account::create($data);
        $role = ($data['type'] === 'Mineur') ? 'guardian' : 'owner';
        $account->users()->attach(auth()->id(), [
            'relation_type' => $role,
            'accepted_closure' => false
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
            'relation_type' => 'owner',
            'accepted_closure' => false
        ]);
    }

    public function removeCoHolder(Account $account, int $userId): void
    {
        $account->users()->detach($userId);
    }

    public function closeAccount(Account $account): void
    {
        $holders = $account->users()->get();

        $allAgreed = $holders->every(function ($user) {
            return $user->pivot->accepted_closure == true;
        });

        if ($allAgreed) {
            $account->update(['status' => 'CLOSED']);
        } else {
            throw new \Exception("All holders must accept the closure.");
        }
    }
}
