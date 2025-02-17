<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Регистрация кастомных консольных команд.
     */
    protected $commands = [
        \App\Console\Commands\ExportToGoogleSheets::class,
    ];

    /**
     * Определение расписания команд.
     */
    protected function schedule(Schedule $schedule): void
    {
        
        $schedule->command('export:google-sheets')->everyMinute()->withoutOverlapping();

    }

    /**
     * Регистрация команд Artisan.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
