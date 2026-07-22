<?php

namespace App\Enums;

enum NotificationStoragePolicy: string
{
    case SERVER_ONLY = 'SERVER_ONLY';
    case LOCAL_ONLY = 'LOCAL_ONLY';
    case LOCAL_THEN_SYNC = 'LOCAL_THEN_SYNC';
    case SERVER_WITH_LOCAL_CACHE = 'SERVER_WITH_LOCAL_CACHE';
}
