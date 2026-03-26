<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePassword;
use App\Http\Requests\UpdateProfile;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Exception;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService) 
    {
        $this->middleware('auth:api');
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'data' => $this->profileService->getProfile()
        ]);
    }

    public function updateProfile(UpdateProfile $request): JsonResponse
    {
        try {
            $user = $this->profileService->updateProfile($request->validated());
            return response()->json(['message' => 'Profile updated', 'data' => $user]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function updatePassword(UpdatePassword $request): JsonResponse
    {
        try {
            $this->profileService->updatePassword($request->validated());
            return response()->json(['message' => 'Password updated successfully']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(): JsonResponse
    {
        try {
            $this->profileService->deleteAccount();
            return response()->json(['message' => 'Account deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}