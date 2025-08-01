<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User\User;
use App\Models\User\UserPersonalAccessToken;
use App\Services\Auth\AuthService;
use App\Services\User\UserCreateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    )
    {
    }

    /**
     * Authentication
     * @param LoginRequest $loginRequest
     * @return JsonResponse
     */
    public function login(LoginRequest $loginRequest): JsonResponse
    {
        $input = $loginRequest->validated();
        $user = User::query()->where('email', $input["email"])->first();
        if ($user && Hash::check($input["password"], $user->password)) {
            $tokens = $this->authService->createTokens($user, isRememberMe: (bool)$loginRequest->get('remember_me'));

            return response()->json([
                'user' => new UserResource($user),
                ...$tokens
            ]);
        }
        return response()->json([
            "message" => __("Email or password is incorrect."),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Register into the platform
     * @param RegisterRequest $registerRequest
     * @return JsonResponse
     */
    public function register(RegisterRequest $registerRequest): JsonResponse
    {
        $registerUserData = $registerRequest->validated();
        $userCreateService = new UserCreateService();
        $registerUserData = [...$registerUserData, ['password' => Hash::make($registerUserData['password'])]];
        $newUser = $userCreateService->create($registerUserData);
        $tokens = $this->authService->createTokens($newUser);

        return response()->json([
            'user' => new UserResource($newUser),
            ...$tokens,
            'message' => __('The registration was successful!')
        ], Response::HTTP_OK);
    }

    /**
     * Get authenticated user
     * @return JsonResponse
     */
    public function user(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => __('Unauthorized')], Response::HTTP_UNAUTHORIZED);
        }
        return response()->json(['user' => new UserResource($user)], Response::HTTP_OK);
    }

    /**
     * Refresh tokens without logging out the user when he logged in using remember me option
     * @return JsonResponse
     */
    public function refreshTokens(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = auth()->user();
        $currentRefreshToken = $user->currentAccessToken();
        $tokens = $this->authService->createTokens($user, isRememberMe: $currentRefreshToken->is_remember_me);
        UserPersonalAccessToken::query()
            ->whereIn('id', [$currentRefreshToken->id, $currentRefreshToken->related_token_id])
            ->delete();

        return response()->json($tokens, Response::HTTP_OK);
    }

    /**
     * Delete tokens for the user
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->deleteTokens($request->user());
        return response()->json(["message" => __('Logged out!')], Response::HTTP_OK);
    }
}
