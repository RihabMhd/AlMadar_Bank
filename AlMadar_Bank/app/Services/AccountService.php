<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use App\Repositories\AccountRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;

class AccountService
{
    public function __construct(
        protected AccountRepositoryInterface $accountRepository
    ) {}

    public function getAllAccounts(): Collection
    {
        return $this->accountRepository->allForUser(auth()->id());
    }

    public function storeAccount(array $data): Account
    {
        $data['type'] = strtoupper($data['type']);

        if ($data['type'] === 'MINEUR') {
            $guardianId = $data['guardian_id'] ?? null;
            if (!$guardianId) {
                throw new Exception("A guardian is required for a minor account.");
            }

            $guardian = User::find($guardianId);
            if (!$guardian || \Carbon\Carbon::parse($guardian->date_naissance)->age < 18) {
                throw new Exception("A valid adult guardian is required.");
            }
        }

        return $this->accountRepository->create($data);
    }

    public function getAccount(int $id): Account
    {
        $account = $this->accountRepository->findById($id);
        if (!$account) {
            throw new Exception("Account not found.");
        }
        return $account;
    }

    public function addMember(int $accountId, int $userId): void
    {
        $account = $this->getAccount($accountId);
        $this->authorizeHolder($account);

        if ($account->status === 'CLOSED') {
            throw new Exception("This account is closed.");
        }

        $this->accountRepository->addCoHolder($account, $userId);
    }

    private function authorizeHolder(Account $account): void
    {
        if (!$account->users()->where('user_id', auth()->id())->exists()) {
            throw new Exception("Unauthorized.");
        }
    }

    public function requestClosure(int $accountId): void
    {
        $account = $this->getAccount($accountId);
        $this->authorizeHolder($account);

        if ($account->balance != 0) {
            throw new Exception("Account balance must be zero to request closure.");
        }

        $this->accountRepository->closeAccount($account);
    }
}