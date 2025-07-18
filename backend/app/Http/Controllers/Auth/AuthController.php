<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthService;
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

    public function login(Request $request): JsonResponse
    {
        $email = $request->email;
        $password = $request->password;
        $user = User::query()->where('email', $email)->first();
        if ($user &&  Hash::check($password, $user->password)) {
            $tokens = $this->authService->createTokens($user);

            return response()->json([
                'user' => $user->toArray(),
                ...$tokens
            ]);
        }
        return response()->json([
            "message" => __("Email or password is incorrect."),
        ], 422);
    }

    public function register(Request $request)
    {
        $data = $request->all();
        User::query()->create($data);
        $user = User::query()->where('email', $data["email"])->first();
        dd($user);
    }

    public function verifyEmail(Request $request)
    {

    }
}
