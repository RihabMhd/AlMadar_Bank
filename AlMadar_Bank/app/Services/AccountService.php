<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use App\Repositories\AccountRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;

class AccountService
{
    protected $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

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

            if (!$guardian) {
                throw new Exception("Guardian not found.");
            }

            $age = \Carbon\Carbon::parse($guardian->date_naissance)->age;
            if ($age < 18) {
                throw new Exception("Guardian must be an adult.");
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

    private function authorizeHolder(Account $account): void
    {
        $isMember = $account->users()->where('user_id', auth()->id())->exists();

        if (!$isMember) {
            throw new Exception("Unauthorized.");
        }
    }

    public function convertAccount(int $id): Account
    {
        $account = $this->getAccount($id);
        $this->authorizeHolder($account);

        if ($account->type !== 'MINEUR') {
            throw new Exception("Only minor accounts can be converted.");
        }

        $holder = $account->users()->where('user_id', auth()->id())->first();

        if (!$holder || $holder->pivot->relation_type !== 'guardian') {
            throw new Exception("Only the guardian can convert a minor account.");
        }

        $minor = $account->users()->wherePivot('relation_type', 'owner')->first();

        if ($minor) {
            $age = \Carbon\Carbon::parse($minor->date_naissance)->age;
            if ($age < 18) {
                throw new Exception("The account holder has not reached majority yet.");
            }
        }

        return $this->accountRepository->updateType($account);
    }

    public function addMember(int $accountId, int $userId): void
    {
        $account = $this->getAccount($accountId);
        $this->authorizeHolder($account);

        if ($account->status === 'CLOSED') {
            throw new Exception("This account is closed and cannot accept new members.");
        }

        if (!User::find($userId)) {
            throw new Exception("User not found.");
        }

        if ($account->users()->where('user_id', $userId)->exists()) {
            throw new Exception("User is already a member of this account.");
        }

        $this->accountRepository->addCoHolder($account, $userId);
    }

    public function removeMember(int $accountId, int $userId): void
    {
        $account = $this->getAccount($accountId);
        $this->authorizeHolder($account);

        if ($account->status === 'CLOSED') {
            throw new Exception("This account is closed.");
        }

        if (!$account->users()->where('user_id', $userId)->exists()) {
            throw new Exception("User is not a member of this account.");
        }

        $this->accountRepository->removeCoHolder($account, $userId);
    }

    public function acceptClosure(int $accountId): void
    {
        $account = $this->getAccount($accountId);
        $this->authorizeHolder($account);

        if ($account->status === 'CLOSED') {
            throw new Exception("Account is already closed.");
        }

        $this->accountRepository->acceptClosure($account, auth()->id());
    }

    public function requestClosure(int $accountId): void
    {
        $account = $this->getAccount($accountId);
        $this->authorizeHolder($account);

        if ($account->status === 'CLOSED') {
            throw new Exception("Account is already closed.");
        }

        $this->accountRepository->closeAccount($account);
    }
}