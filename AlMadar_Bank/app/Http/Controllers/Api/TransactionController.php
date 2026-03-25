<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Exception;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function indexByAccount(int $accountId): JsonResponse
    {
        $transactions = $this->transactionService->getAccountHistory($accountId);
        return response()->json(['data' => $transactions]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $transaction = $this->transactionService->getTransaction($id);
            return response()->json(['data' => $transaction]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}