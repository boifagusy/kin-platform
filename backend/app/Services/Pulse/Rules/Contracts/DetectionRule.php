<?php

namespace App\Services\Pulse\Rules\Contracts;

use App\Models\User;

interface DetectionRule
{
    public function detect(User $user): bool;
    public function getEventType(): string;
    public function getImpact(): int;
    public function getDescription(): string;
    public function getSeverity(): string;
}
