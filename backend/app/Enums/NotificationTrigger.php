<?php

namespace App\Enums;

enum NotificationTrigger: string
{
    case SERVER_EVENT = 'SERVER_EVENT';
    case LOCAL_SCHEDULE = 'LOCAL_SCHEDULE';
    case MANUAL = 'MANUAL';
    case AUTOMATION = 'AUTOMATION';
    case SYSTEM = 'SYSTEM';
    case API = 'API';
}
