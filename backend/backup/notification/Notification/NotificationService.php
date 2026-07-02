<?php

namespace App\Services\Notification;

use App\Services\Notification\Drivers\LogDriver;
use App\Services\Notification\Drivers\EmailDriver;

class NotificationService
{
    protected $defaultDriver = 'log';
    
    protected $drivers = [
        'log' => LogDriver::class,
        'email' => EmailDriver::class,
    ];
    
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        $driver = $options['driver'] ?? $this->defaultDriver;
        
        if (!isset($this->drivers[$driver])) {
            $driver = 'log';
        }
        
        $driverClass = $this->drivers[$driver];
        $driverInstance = new $driverClass();
        
        return $driverInstance->send($to, $subject, $body, $options);
    }
    
    public function sendEmail(string $to, string $subject, string $body): bool
    {
        return $this->send($to, $subject, $body, ['driver' => 'email']);
    }
}
