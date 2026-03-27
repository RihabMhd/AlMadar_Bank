<?php

namespace App\Services;

use App\Models\Account;
use App\Repositories\AccountRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;

class AccountService
{
    public function __construct(
        protected AccountRepositoryInterface $accountRepository,
        protected UserRepositoryInterface    $userRepository
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

            $guardian = $this->userRepository->findById($guardianId);

            if (!$guardian || \Carbon\Carbon::parse($guardian->date_naissance)->age < 18) {
                throw new Exception("A valid adult guardian is required.");
            }

            $account = $this->accountRepository->create($data);
            $this->accountRepository->addGuardian($account, $guardianId);

            return $account;
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

    public function addCoHolder(int $accountId, int $userId): void
    {
        $account = $this->getAccount($accountId);
        $this->authorizeHolder($account);

        if ($account->status === 'CLOSED') {
            throw new Exception("This account is closed.");
        }

        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new Exception("User not found.");
        }

        $this->accountRepository->addCoHolder($account, $userId);
    }

    public function assignGuardian(int $accountId, int $userId): void
    {
        $account = $this->getAccount($accountId);
        $this->authorizeHolder($account);

        if ($account->type !== 'MINEUR') {
            throw new Exception("Guardians can only be assigned to minor accounts.");
        }

        if ($account->status === 'CLOSED') {
            throw new Exception("This account is closed.");
        }

        $guardian = $this->userRepository->findById($userId);
        if (!$guardian || \Carbon\Carbon::parse($guardian->date_naissance)->age < 18) {
            throw new Exception("A valid adult guardian is required.");
        }

        $this->accountRepository->addGuardian($account, $userId);
    }

    public function removeCoHolder(int $accountId, int $userId): void
    {
        $account = $this->getAccount($accountId);
        $this->authorizeHolder($account);

        if ($account->status === 'CLOSED') {
            throw new Exception("This account is closed.");
        }

        $this->accountRepository->removeCoHolder($account, $userId);
    }

    public function convertToCurrentAccount(int $accountId): Account
    {
        $account = $this->getAccount($accountId);
        $this->authorizeHolder($account);

        if ($account->type !== 'MINEUR') {
            throw new Exception("Only minor accounts can be converted.");
        }

        return $this->accountRepository->updateType($account);
    }

    public function requestClosure(int $accountId): void
    {
        $account = $this->getAccount($accountId);
        $this->authorizeHolder($account);

        if ($account->status === 'CLOSED') {
            throw new Exception("Account is already closed.");
        }

        if ($account->balance != 0) {
            throw new Exception("Account balance must be zero to request closure.");
        }

        $this->accountRepository->acceptClosure($account, auth()->id());
        $this->accountRepository->closeAccount($account->fresh());
    }

    private function authorizeHolder(Account $account): void
    {
        if (!$account->users()->where('user_id', auth()->id())->exists()) {
            throw new Exception("Unauthorized.");
        }
    }
}