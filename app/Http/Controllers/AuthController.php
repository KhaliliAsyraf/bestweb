<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Login for authentication
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json(
                    [
                        'error' => 'Invalid credentials.'
                    ],
                    401
                );
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(
                [
                    'message' => 'Login success.',
                    'token' => $token
                ]
            );
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json(
                [
                    'error' => 'Login encounter error.'
                ],
                500
            );
        }
    }
}
