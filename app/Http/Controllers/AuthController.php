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
     *
     * @OA\Post(
     *     path="/api/login",
     *     operationId="loginUser",
     *     tags={"Auth"},
     *     summary="User login",
     *     description="Authenticate a user and return a bearer token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login success.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login success."),
     *             @OA\Property(property="token", type="string", example="<token>")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error (invalid email or password).",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The selected email is invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="The selected email is invalid.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Something when wrong")
     *         )
     *     )
     * )
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
