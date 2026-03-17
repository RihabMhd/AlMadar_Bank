<?php


namespace App\Http\Controllers\Api;

use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->middleware('auth:api');
        $this->accountService = $accountService;
    }


    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:Courant,Mineur',
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $account = $this->accountService->storeAccount($request->all());

            return response()->json([
                'message' => 'Account created successfully',
                'account' => $account
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $account = $this->accountService->getAccount($id);
            return response()->json($account);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function convertAccount($id): JsonResponse
    {
        try {
            $account = $this->accountService->convertAccount($id);
            return response()->json(['message' => 'Account converted successfully', 'account' => $account]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function removeMember(Request $request, $accountId, $userId): JsonResponse
    {
        try {
            $this->accountService->removeMember($accountId, $userId);
            return response()->json(['message' => 'Member removed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function addMember(Request $request, $accountId): JsonResponse
    {
        try {
            $userId = $request->input('user_id');

            $this->accountService->addMember($accountId, $userId);

            return response()->json(['message' => 'Member added successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function requestClosure($accountId): JsonResponse
    {
        try {
            $this->accountService->requestClosure($accountId);

            return response()->json(['message' => 'Account Closed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
