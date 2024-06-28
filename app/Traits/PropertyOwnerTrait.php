<?php

namespace App\Traits;

trait PropertyOwnerTrait
{

    public function getCurrentLoggedPropertyOwnerBySanctum()
    {
        return auth('property_owner')->user();
    }
}