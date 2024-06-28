<?php

namespace App\Traits;

trait AgentTrait
{

    public function getCurrentLoggedAgentBySanctum()
    {
        return auth('agent')->user();
    }
}
