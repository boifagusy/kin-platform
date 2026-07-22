<?php

namespace App\Enums;

enum NotificationLifecycle: string
{
    case CREATED = 'CREATED';
    case DELIVERED = 'DELIVERED';
    case DISPLAYED = 'DISPLAYED';
    case READ = 'READ';
    case ACTIONED = 'ACTIONED';
    case COMPLETED = 'COMPLETED';
    case RESPONDING = 'RESPONDING';
    case RESOLVED = 'RESOLVED';
    case ARCHIVED = 'ARCHIVED';
    case EXPIRED = 'EXPIRED';
}
