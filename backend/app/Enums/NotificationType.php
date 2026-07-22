<?php

namespace App\Enums;

enum NotificationType: string
{
    case INFO = 'INFO';
    case ACTION_REQUIRED = 'ACTION_REQUIRED';
    case REMINDER = 'REMINDER';
    case WARNING = 'WARNING';
    case CRITICAL = 'CRITICAL';
    case SYSTEM = 'SYSTEM';
}
