<?php

namespace App\Repositories;

use App\Models\Transfer;
use Illuminate\Support\Collection;

class TransferRepository implements TransferRepositoryInterface
{
    public function initiateTransfer(array $data): Transfer
    {
        return Transfer::create([
            'sender_id'     => $data['sender_id'],
            'receiver_id'   => $data['receiver_id'],
            'initiated_by'  => $data['initiated_by'],
            'amount'        => $data['amount'],
            'reason'        => $data['reason'] ?? null,
            'status'        => 'pending',
        ]);
    }

    public function findById(int $id): ?Transfer
    {
        return Transfer::find($id);
    }
}