<?php

namespace App\Repositories;

use App\Models\Account;
use Illuminate\Support\Collection;

interface AdminRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): ?Account;
    public function updateStatus(int $id, string $status): bool;
}