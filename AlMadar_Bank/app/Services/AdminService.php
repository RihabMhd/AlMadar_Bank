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

        if ($account->status === 'CLOSED') {
            throw new Exception("This account is already closed and cannot be modified.");
        }

        $allowed = ['ACTIVE', 'BLOCKED', 'CLOSED'];
        if (!in_array(strtoupper($status), $allowed)) {
            throw new Exception("Invalid status value.");
        }

        return $this->adminRepository->updateStatus($id, strtoupper($status));
    }
}