<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Events\CheckInCompleted;
use App\Events\SOSTriggered;

use App\Listeners\CreateActivityLog;
use App\Listeners\UpdateSafetyScore;
use App\Listeners\RefreshDashboardCache;
use App\Listeners\QueueSosAlert;
use App\Listeners\EvaluateAutomationRules;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        CheckInCompleted::class => [
            CreateActivityLog::class,
            UpdateSafetyScore::class,
            RefreshDashboardCache::class,
            EvaluateAutomationRules::class,
        ],

        SOSTriggered::class => [
            CreateActivityLog::class,
            QueueSosAlert::class,
            EvaluateAutomationRules::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
