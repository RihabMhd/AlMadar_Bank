<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Exception;

class AdminAccountController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index(): JsonResponse
    {
        $accounts = $this->adminService->listAllAccounts();
        return response()->json(['data' => $accounts]);
    }

    public function block(int $id): JsonResponse
    {
        try {
            $this->adminService->updateAccountStatus($id, 'blocked');
            return response()->json(['message' => 'Account blocked successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function unblock(int $id): JsonResponse
    {
        try {
            $this->adminService->updateAccountStatus($id, 'active');
            return response()->json(['message' => 'Account unblocked successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function close(int $id): JsonResponse
    {
        try {
            $this->adminService->updateAccountStatus($id, 'closed');
            return response()->json(['message' => 'Account closed by administrator.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
