<?php

namespace App\Repositories;

use App\Models\Transfer;

interface TransferRepositoryInterface
{
    public function findById(int $id): ?Transfer;
    public function initiateTransfer(array $data): Transfer;
    public function updateStatus(int $id, string $status): void;
}