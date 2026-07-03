<?php

namespace App\Services\Recovery\Contracts;

interface RecoveryAction
{
    /**
     * Execute the recovery action
     * @return RecoveryResult
     */
    public function execute(): RecoveryResult;
    
    /**
     * Get the action name
     */
    public function getName(): string;
    
    /**
     * Get a description of what this action does
     */
    public function getDescription(): string;
    
    /**
     * Check if this action is safe to run automatically
     */
    public function isSafe(): bool;
    
    /**
     * Check if this action can be rolled back
     */
    public function isRollbackable(): bool;
}
