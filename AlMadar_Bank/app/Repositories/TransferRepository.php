<?php

namespace App\Repositories;

use App\Models\Transfer;

class TransferRepository implements TransferRepositoryInterface
{
    public function initiateTransfer(array $data): Transfer
    {
        return Transfer::create([
            'sender_id'    => $data['sender_id'],
            'receiver_id'  => $data['receiver_id'],
            'initiated_by' => auth()->id(),
            'amount'       => $data['amount'],
            'reason'       => $data['reason'] ?? null,
            'status'       => 'PENDING',
        ]);
    }

    public function findById(int $id): ?Transfer
    {
        return Transfer::find($id);
    }

    public function updateStatus(int $id, string $status): void
    {
        Transfer::where('id', $id)->update(['status' => $status]);
    }
}