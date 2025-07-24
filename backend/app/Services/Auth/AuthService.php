<?php

namespace App\Services\Auth;

use App\Models\UserPersonalAccessToken;
use App\Models\User;
use App\Utils\AuthVariable;
use Carbon\Carbon;

class AuthService
{
    public function createTokens(User $user, bool $twoFactorVerified = false, bool $isRememberMe = false): array
    {
        $minutesToExpire = $isRememberMe ? AuthVariable::getRememberMeAccessTokenExpiration() : AuthVariable::getAccessTokenExpiration();
        $expiresAt = Carbon::now()->addMinutes($minutesToExpire);
        $accessToken = $user->createToken('api_token', [], $expiresAt)->plainTextToken;
        $accessTokenId = explode('|', $accessToken)[0];

        $refreshTokenExpireAfter =  $isRememberMe ? AuthVariable::getRememberMeRefreshTokenExpiration() : AuthVariable::getRefreshTokenExpiration();
        $refreshTokenExpiresAt = Carbon::now()->addMinutes($refreshTokenExpireAfter);
        $refreshToken = $user->createToken('refresh_token', [], $refreshTokenExpiresAt)->plainTextToken;
        $refreshTokenId = explode('|', $refreshToken)[0];
        $personalAccessToken = UserPersonalAccessToken::query()->find($refreshTokenId);
        $personalAccessToken->related_token_id = $accessTokenId;
        $personalAccessToken->save();

        $updateTokens = [];
        if($isRememberMe){
            $updateTokens['is_remember_me'] = true;
        }

        if(!empty($updateTokens)){
            UserPersonalAccessToken::query()
                ->whereIn('id', [$accessTokenId, $refreshTokenId])
                ->update($updateTokens);
        }

        return [
            "access_token" => $accessToken,
            "access_token_expires_at" => $expiresAt->timestamp,
            "refresh_token" => $refreshToken,
            "refresh_token_expires_at" => $refreshTokenExpiresAt->timestamp,
        ];
    }
}
