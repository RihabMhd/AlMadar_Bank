<?php

namespace App\Http\Controllers\Api;

use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->middleware('auth:api');
        $this->accountService = $accountService;
    }

    public function index(): JsonResponse
    {
        try {
            $accounts = $this->accountService->getAllAccounts();
            return response()->json($accounts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'        => 'required|string|in:COURANT,MINEUR',
            'balance'     => 'sometimes|numeric|min:0',
            'guardian_id' => 'required_if:type,MINEUR|integer',
        ]);

        try {
            $account = $this->accountService->storeAccount($data);
            return response()->json([
                'message' => 'Account created successfully',
                'account' => $account,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $account = $this->accountService->getAccount($id);
            return response()->json($account);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function convertAccount(int $id): JsonResponse
    {
        try {
            $account = $this->accountService->convertAccount($id);
            return response()->json([
                'message' => 'Account converted successfully',
                'account' => $account,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function addMember(Request $request, int $accountId): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
        ]);

        try {
            $this->accountService->addMember($accountId, $data['user_id']);
            return response()->json(['message' => 'Member added successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function removeMember(int $accountId, int $userId): JsonResponse
    {
        try {
            $this->accountService->removeMember($accountId, $userId);
            return response()->json(['message' => 'Member removed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function acceptClosure(int $accountId): JsonResponse
    {
        try {
            $this->accountService->acceptClosure($accountId);
            return response()->json(['message' => 'Closure accepted']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function requestClosure(int $accountId): JsonResponse
    {
        try {
            $this->accountService->requestClosure($accountId);
            return response()->json(['message' => 'Account closed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}