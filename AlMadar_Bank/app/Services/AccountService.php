<?php

namespace App\Services;

use App\Models\Account;
use App\Repositories\AccountRepositoryInterface;
use Exception;

class AccountService
{
    protected $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function storeAccount(array $data): Account
    {
        return $this->accountRepository->create($data);
    }
    
    public function getAccount(int $id): Account
    {
        $account = $this->accountRepository->findById($id);
        if (!$account) throw new \Exception("Account not found");
        return $account;
    }

    public function convertAccount(int $id): Account
    {
        $account = $this->getAccount($id);
        return $this->accountRepository->updateType($account);
    }

    public function addMember(int $accountId, int $userId): void
    {
        $account = $this->accountRepository->findById($accountId);

        if (!$account) {
            throw new Exception("Account not found.");
        }

        if ($account->status === 'CLOSED') {
            throw new Exception("This account is closed and cannot accept new members.");
        }

        $this->accountRepository->addCoHolder($account, $userId);
    }

    public function removeMember(int $accountId, int $userId): void
    {
        $account = $this->accountRepository->findById($accountId);

        if (!$account) {
            throw new Exception("Account not found.");
        }

        if ($account->status === 'CLOSED') {
            throw new Exception("This account is closed and cannot remove members.");
        }

        $this->accountRepository->removeCoHolder($account, $userId);
    }


    public function requestClosure(int $accountId): void
    {
        $account = $this->accountRepository->findById($accountId);

        if (!$account) {
            throw new Exception("Account not found.");
        }

        $this->accountRepository->closeAccount($account);
    }
}
