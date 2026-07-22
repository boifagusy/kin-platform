<?php

namespace App\Enums;

enum NotificationSyncStatus: string
{
    case PENDING_SYNC = 'PENDING_SYNC';
    case SYNCED = 'SYNCED';
    case FAILED = 'FAILED';
    case LOCAL_ONLY = 'LOCAL_ONLY';
    case CONFLICT = 'CONFLICT';
}
