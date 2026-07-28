<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Events\CheckInCompleted;
use App\Events\EmergencyTriggered;
use App\Events\EmergencyResolved;
use App\Events\TrustedContact\TrustedContactRequestCreated;
use App\Events\TrustedContact\TrustedContactRequestAccepted;
use App\Events\TrustedContact\TrustedContactRequestDeclined;
use App\Events\TrustedContact\TrustedContactInvitationAccepted;
use App\Events\TrustedContact\TrustedContactRemoved;

use App\Listeners\CreateActivityLog;
use App\Listeners\UpdateSafetyScore;
use App\Listeners\RefreshDashboardCache;
use App\Listeners\QueueSosAlert;
use App\Listeners\EvaluateAutomationRules;
use App\Listeners\EscalationListener;
use App\Listeners\TrustedContactNotifier;
use App\Listeners\ResolutionAuditListener;
use App\Listeners\TrustedContactNotificationListener;

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

        // Trusted Contact Events
        TrustedContactRequestCreated::class => [
            TrustedContactNotificationListener::class,
        ],

        TrustedContactRequestAccepted::class => [
            TrustedContactNotificationListener::class,
        ],

        TrustedContactRequestDeclined::class => [
            TrustedContactNotificationListener::class,
        ],

        TrustedContactInvitationAccepted::class => [
            TrustedContactNotificationListener::class,
        ],

        TrustedContactRemoved::class => [
            TrustedContactNotificationListener::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
