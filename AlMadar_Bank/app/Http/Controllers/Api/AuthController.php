<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterUser;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends Controller
{
    public function register(RegisterUser $request): JsonResponse
    {
        $user = User::create(
            array_merge(
                $request->validated(),
                ['password' => Hash::make($request->password)]
            )
        );

        $token = JWTAuth::fromUser($user);

        return response()->json(['message' => 'Account created successfully'], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ], 200);
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logout successful'], 200);
    }

    public function refresh(): JsonResponse
    {
        $token = Auth::refresh();
        return response()->json(['token' => $token]);
    }
}
