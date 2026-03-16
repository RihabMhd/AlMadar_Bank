<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePassword;
use App\Http\Requests\UpdateProfile;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService) {}

    public function me(): JsonResponse
    {
        return response()->json([
            'message' => 'Profile fetched successfully',
            'data'    => $this->profileService->getProfile()
        ]);
    }

    public function updateProfile(UpdateProfile $request): JsonResponse
    {
        $user = $this->profileService->updateProfile($request->validated());

        return response()->json([
            'message' => 'Profile updated successfully',
            'data'    => $user
        ]);
    }

    public function updatePassword(UpdatePassword $request): JsonResponse
    {
        try {
            $this->profileService->updatePassword($request->validated());
            return response()->json(['message' => 'Password updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(): JsonResponse
    {
        $this->profileService->deleteAccount();
        return response()->json(['message' => 'Account deleted successfully']);
    }
}