<?php

namespace App\Services\Sentinel\Rules\Contracts;

interface DetectionRule
{
    public function getRuleId(): string;
    public function getName(): string;
    public function getDescription(): string;
    public function getCategory(): string;
    public function getSeverity(): string; // critical, high, medium, low
    public function getRiskPoints(): int;
    public function getThreshold(): int;
    public function getTimeWindow(): int; // seconds
    public function isEnabled(): bool;
    public function detect(array $event): bool;
    public function getAutomatedActions(): array;
    public function getCooldown(): int; // seconds
    public function getVersion(): string;
}
