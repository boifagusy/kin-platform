<?php

namespace App\Services\Recovery\Actions;

use App\Services\Recovery\Contracts\RecoveryAction;
use App\Services\Recovery\Contracts\RecoveryResult;
use Carbon\Carbon;

abstract class BaseAction implements RecoveryAction
{
    protected string $name;
    protected string $description;
    protected bool $safe = true;
    protected bool $rollbackable = false;
    
    public function getName(): string
    {
        return $this->name ?? class_basename($this);
    }
    
    public function getDescription(): string
    {
        return $this->description ?? 'Execute recovery action';
    }
    
    public function isSafe(): bool
    {
        return $this->safe;
    }
    
    public function isRollbackable(): bool
    {
        return $this->rollbackable;
    }
    
    protected function success(string $message, array $data = []): RecoveryResult
    {
        return new class($message, $data) implements RecoveryResult {
            protected $message;
            protected $data;
            public function __construct($message, $data) { $this->message = $message; $this->data = $data; }
            public function getStatus(): string { return 'success'; }
            public function getMessage(): string { return $this->message; }
            public function getData(): array { return $this->data; }
            public function isSuccess(): bool { return true; }
        };
    }
    
    protected function failed(string $message, array $data = []): RecoveryResult
    {
        return new class($message, $data) implements RecoveryResult {
            protected $message;
            protected $data;
            public function __construct($message, $data) { $this->message = $message; $this->data = $data; }
            public function getStatus(): string { return 'failed'; }
            public function getMessage(): string { return $this->message; }
            public function getData(): array { return $this->data; }
            public function isSuccess(): bool { return false; }
        };
    }
}
