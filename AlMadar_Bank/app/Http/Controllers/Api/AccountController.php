<?php

namespace App\Http\Controllers\Api;

use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Exception;

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
        return response()->json($this->accountService->getAllAccounts());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'        => 'required|string|in:COURANT,MINEUR,EPARGNE',
            'balance'     => 'sometimes|numeric|min:0', // <--- Add this line
            'guardian_id' => 'required_if:type,MINEUR|integer|exists:users,id',
        ]);

        try {
            $account = $this->accountService->storeAccount($data);
            return response()->json(['message' => 'Account created', 'account' => $account], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function addMember(Request $request, int $accountId): JsonResponse
    {
        $data = $request->validate(['user_id' => 'required|integer|exists:users,id']);

        try {
            $this->accountService->addMember($accountId, $data['user_id']);
            return response()->json(['message' => 'Co-owner added successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function removeMember(int $accountId, int $userId): JsonResponse
    {
        try {
            $this->accountService->removeMember($accountId, $userId);
            return response()->json(['message' => 'Member removed successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function acceptClosure(int $accountId): JsonResponse
    {
        try {
            $this->accountService->acceptClosure($accountId);
            return response()->json(['message' => 'Closure agreement recorded.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function requestClosure(int $accountId): JsonResponse
    {
        try {
            $this->accountService->requestClosure($accountId);
            return response()->json(['message' => 'Closure request initiated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
