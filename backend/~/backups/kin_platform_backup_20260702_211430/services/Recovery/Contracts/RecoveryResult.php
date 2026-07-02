<?php

namespace App\Services\Recovery\Contracts;

interface RecoveryResult
{
    /**
     * Get result status (success, failed, partial)
     */
    public function getStatus(): string;
    
    /**
     * Get a message describing the outcome
     */
    public function getMessage(): string;
    
    /**
     * Get any data returned from the action
     */
    public function getData(): array;
    
    /**
     * Check if the action was successful
     */
    public function isSuccess(): bool;
}
