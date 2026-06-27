#!/bin/bash
cd ~/storage/kin_platform/backend
php artisan queue:work --stop-when-empty
