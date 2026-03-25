<?php
/*
POST: /api/transfers => Initier un virement

GET: /api/transfers/{id} => Détail virement

*/

namespace App\Repositories;

use Illuminate\Support\Collection;
use App\Models\Transfer;

interface TransferRepositoryInterface
{
    public function findById(int $id): ?Transfer;
    public function initiateTransfer(array $data): Transfer;
}
