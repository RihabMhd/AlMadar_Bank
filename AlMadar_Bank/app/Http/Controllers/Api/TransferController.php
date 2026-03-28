<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class TransferController extends Controller
{
    public function __construct(protected TransferService $transferService)
    {
        $this->middleware('auth:api');
    }

    public function index(): JsonResponse
    {
        $transfers = $this->transferService->getTransfersForUser(auth()->id());
        return response()->json(['data' => $transfers]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sender_id'   => 'required|exists:accounts,id',
            'receiver_id' => 'required|exists:accounts,id|different:sender_id',
            'amount'      => 'required|numeric|min:0.01',
            'reason'      => 'nullable|string|max:255',
        ]);

        try {
            $transfer = $this->transferService->createTransfer($validated);
            return response()->json(['message' => 'Transfer successful.', 'data' => $transfer], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->getTransfer($id);

            if ($transfer->initiated_by !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            return response()->json(['data' => $transfer]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}