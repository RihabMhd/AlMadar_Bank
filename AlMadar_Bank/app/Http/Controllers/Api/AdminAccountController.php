<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Exception;

class AdminAccountController extends Controller
{
    public function __construct(protected AdminService $adminService)
    {
        $this->middleware('auth:api');
    }

    public function index(): JsonResponse
    {
        $accounts = $this->adminService->listAllAccounts();
        return response()->json(['data' => $accounts]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $account = $this->adminService->getAccount($id);
            return response()->json(['data' => $account]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function block(int $id): JsonResponse
    {
        try {
            $this->adminService->updateAccountStatus($id, 'BLOCKED');
            return response()->json(['message' => 'Account blocked successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function unblock(int $id): JsonResponse
    {
        try {
            $this->adminService->updateAccountStatus($id, 'ACTIVE');
            return response()->json(['message' => 'Account unblocked successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function close(int $id): JsonResponse
    {
        try {
            $this->adminService->updateAccountStatus($id, 'CLOSED');
            return response()->json(['message' => 'Account closed by administrator.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}