<?php

namespace App\Services\Recovery;

use App\Services\Recovery\Contracts\RecoveryResult;

class RecoveryResultImpl implements RecoveryResult
{
    protected string $status;
    protected string $message;
    protected array $data;
    
    public function __construct(string $status, string $message, array $data = [])
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }
    
    public function getStatus(): string
    {
        return $this->status;
    }
    
    public function getMessage(): string
    {
        return $this->message;
    }
    
    public function getData(): array
    {
        return $this->data;
    }
    
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }
}
