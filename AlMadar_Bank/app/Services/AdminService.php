<?php

namespace App\Services;

use App\Repositories\AdminRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;

class AdminService
{
    public function __construct(
        protected AdminRepositoryInterface $adminRepository
    ) {}

    public function listAllAccounts(): Collection
    {
        return $this->adminRepository->getAll();
    }

    public function updateAccountStatus(int $id, string $status): bool
    {
        $account = $this->adminRepository->findById($id);

        if (!$account) {
            throw new Exception("Account not found.");
        }

        if ($account->status === 'closed') {
            throw new Exception("This account is already closed and cannot be modified.");
        }

        return $this->adminRepository->updateStatus($id, $status);
    }
}