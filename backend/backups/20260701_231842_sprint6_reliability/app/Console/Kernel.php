<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
    AppConsoleCommandsWatchtowerDiagnoseCommand::class,
        Commands\CheckMissedCheckins::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('kin:check-missed-checkins')->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

    protected $commands = [
    AppConsoleCommandsWatchtowerDiagnoseCommand::class,
        \App\Console\Commands\Forge\DoctorCommand::class,
        \App\Console\Commands\Forge\CleanupCommand::class,
        \App\Console\Commands\Forge\WorkspaceCommand::class,
        \App\Console\Commands\Forge\BuildCommand::class,
    ];
