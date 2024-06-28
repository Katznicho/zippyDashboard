<?php

namespace App\Traits;

trait AppUserTrait
{

    public function getCurrentLoggedAppUserBySanctum()
    {
        return auth('app_user')->user();
    }
}