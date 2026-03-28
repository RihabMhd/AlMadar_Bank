<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccountingService;
use Illuminate\Http\JsonResponse;
use Exception;

class AdminAccountingController extends Controller
{
    public function __construct(protected AccountingService $accountingService)
    {
        $this->middleware('auth:api');
    }

    public function runMonthlyRoutine(): JsonResponse
    {
        try {
            $this->accountingService->processMonthlyRoutine();
            return response()->json(['message' => 'Monthly routine completed successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}