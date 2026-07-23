<?php

namespace App\Events;

use App\Models\SafetyIncident;
use Illuminate\Foundation\Events\Dispatchable;

class EmergencyTriggered
{
    use Dispatchable;

    public SafetyIncident $incident;

    public function __construct(SafetyIncident $incident)
    {
        $this->incident = $incident;
    }
}
