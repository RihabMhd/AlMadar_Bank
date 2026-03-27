<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService)
    {
        $this->middleware('auth:api');
    }

    public function indexByAccount(Request $request, int $accountId): JsonResponse
    {
        try {
            $filters      = $request->only(['type', 'date']);
            $transactions = $this->transactionService->getAccountHistory($accountId, $filters);
            return response()->json(['data' => $transactions]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
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