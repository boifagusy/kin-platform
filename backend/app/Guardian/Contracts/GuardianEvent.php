<?php

namespace App\Guardian\Contracts;

interface GuardianEvent
{
    public function getEventId(): string;
    public function getEventType(): string;
    public function getTimestamp(): string;
    public function getCorrelationId(): string;
    public function getUserId(): ?int;
    public function toArray(): array;
}
