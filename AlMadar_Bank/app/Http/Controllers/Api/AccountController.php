<?php

namespace App\Http\Controllers\Api;

use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Exception;

class AccountController extends Controller
{
    public function __construct(protected AccountService $accountService)
    {
        $this->middleware('auth:api');
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->accountService->getAllAccounts()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'                => 'required|string|in:COURANT,MINEUR,EPARGNE',
            'balance'             => 'sometimes|numeric|min:0',
            'guardian_id'         => 'required_if:type,MINEUR|integer|exists:users,id',
            'overdraft_limit'     => 'sometimes|numeric|min:0',
            'interest_rate'       => 'sometimes|numeric|min:0',
            'monthly_fee'         => 'sometimes|numeric|min:0',
            'daily_transfer_limit'=> 'sometimes|numeric|min:0',
        ]);

        try {
            $account = $this->accountService->storeAccount($data);
            return response()->json(['message' => 'Account created.', 'data' => $account], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $account = $this->accountService->getAccount($id);
            return response()->json(['data' => $account]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function listCoHolders(int $accountId): JsonResponse
    {
        try {
            $account = $this->accountService->getAccount($accountId);
            $this->accountService->authorizeHolder($account);

            $holders = $account->users()->withPivot('relation_type', 'accepted_closure')->get()
                ->map(fn($user) => [
                    'id'               => $user->id,
                    'nom'              => $user->nom,
                    'prenom'           => $user->prenom,
                    'email'            => $user->email,
                    'relation_type'    => $user->pivot->relation_type,
                    'accepted_closure' => (bool) $user->pivot->accepted_closure,
                ]);

            return response()->json(['data' => $holders]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function addCoHolder(Request $request, int $accountId): JsonResponse
    {
        $data = $request->validate(['user_id' => 'required|integer|exists:users,id']);

        try {
            $this->accountService->addCoHolder($accountId, $data['user_id']);
            return response()->json(['message' => 'Co-owner added successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function removeCoHolder(int $accountId, int $userId): JsonResponse
    {
        try {
            $this->accountService->removeCoHolder($accountId, $userId);
            return response()->json(['message' => 'Co-owner removed successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function assignGuardian(Request $request, int $accountId): JsonResponse
    {
        $data = $request->validate(['user_id' => 'required|integer|exists:users,id']);

        try {
            $this->accountService->assignGuardian($accountId, $data['user_id']);
            return response()->json(['message' => 'Guardian assigned successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function convert(int $accountId): JsonResponse
    {
        try {
            $account = $this->accountService->convertToCurrentAccount($accountId);
            return response()->json(['message' => 'Account converted to COURANT.', 'data' => $account]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function requestClosure(int $accountId): JsonResponse
    {
        try {
            $this->accountService->requestClosure($accountId);
            return response()->json(['message' => 'Closure request recorded. Account will close once all holders agree.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}