<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Events\CheckInCompleted;
use App\Events\EmergencyTriggered;
use App\Events\EmergencyResolved;

use App\Listeners\CreateActivityLog;
use App\Listeners\UpdateSafetyScore;
use App\Listeners\RefreshDashboardCache;
use App\Listeners\QueueSosAlert;
use App\Listeners\EvaluateAutomationRules;
use App\Listeners\EscalationListener;
use App\Listeners\TrustedContactNotifier;
use App\Listeners\ResolutionAuditListener;

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

        EmergencyTriggered::class => [
            CreateActivityLog::class,
            QueueSosAlert::class,
            EvaluateAutomationRules::class,
            UpdateSafetyScore::class,
            EscalationListener::class,
            TrustedContactNotifier::class,
        ],

        EmergencyResolved::class => [
            ResolutionAuditListener::class,
        ],


    ];

    public function boot(): void
    {
        //
    }
}
