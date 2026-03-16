<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePassword;
use App\Http\Requests\UpdateProfile;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class ProfileController extends Controller
{
    public function me(): JsonResponse
    {
        return response()->json([
            'message' => 'Profile fetched successfully',
            'data' => auth('api')->user()
        ], 200);
    }

    public function updateProfile(UpdateProfile $request): JsonResponse
    {
        $user = auth('api')->user();
        $user->update($request->validated());
        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $user
        ], 200);
    }

    public function updatePassword(UpdatePassword $request): JsonResponse
    {
        $user = auth('api')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password updated successfully'], 200);
    }

    public function destroy(): JsonResponse
    {
        $user = auth('api')->user();
        auth('api')->logout();
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully'], 200); //
    }
}
