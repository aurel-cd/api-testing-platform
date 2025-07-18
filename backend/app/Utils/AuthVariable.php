<?php

namespace App\Utils;

abstract class AuthVariable
{
    public static function getAccessTokenExpiration(): int
    {
        return config('sanctum.access_token_expiration');
    }

    public static function getRememberMeAccessTokenExpiration(): int
    {
        return config('sanctum.remember_access_token_expiration');
    }

    public static function getRefreshTokenExpiration(): int
    {
        return config('sanctum.refresh_token_expiration');
    }

    public static function getRememberMeRefreshTokenExpiration(): int
    {
        return config('sanctum.remember_refresh_token_expiration');
    }
}
