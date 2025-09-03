<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\ModuleStatusCommand::class,
        Commands\ModuleInstallCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Schedule module health checks
        $schedule->command('module:status health')
                ->hourly()
                ->appendOutputTo(storage_path('logs/module-health.log'));
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}