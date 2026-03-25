<?php

namespace App\Services;

use App\Models\Transfer;
use App\Models\User;
use App\Repositories\TransferRepositoryInterface;
use Exception;

class TransferService
{
    protected $transferRepository;

    public function __construct(TransferRepositoryInterface $transferRepository)
    {
        $this->transferRepository = $transferRepository;
    }

    public function getTransfer(int $id){
        $transfer = $this->transferRepository->findById($id);

        if (!$transfer) {
            throw new Exception("Account not found.");
        }

        return $transfer;
    }

    public function createTransfer(array $data):Transfer{
        if($data['amount']<0){
            throw new Exception("cannot send a negative amount");
        }

        return $this->transferRepository->initiateTransfer($data);
    }
}
