<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\User\UserCreateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    )
    {
    }

    public function login(LoginRequest $loginRequest): JsonResponse
    {
        $input = $loginRequest->validated();
        $user = User::query()->where('email', $input["email"])->first();
        if ($user && Hash::check($input["password"], $user->password)) {
            $tokens = $this->authService->createTokens($user);

            return response()->json([
                'user' => new UserResource($user),
                ...$tokens
            ]);
        }
        return response()->json([
            "message" => __("Email or password is incorrect."),
        ], 422);
    }

    public function register(RegisterRequest $registerRequest): JsonResponse
    {
        $registerUserData = $registerRequest->validated();
        $userCreateService = new UserCreateService();
        $newUser = $userCreateService->create($registerUserData);

        return response()->json([
            'user' => new UserResource($newUser),
            'message' => __('The registration was successful!')
        ]);
    }

    public function verifyEmail(Request $request)
    {

    }
}
