<?php

use App\Http\Middleware\EnsureUserIsPatient;
use App\Http\Middleware\ForceRootUrlFromRequest;
use App\Jobs\GenerateHealthIndicatorSnapshotJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            ForceRootUrlFromRequest::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('patient.login'));
        $middleware->redirectUsersTo(fn (): string => filament()->getPanel('app')->getUrl() ?? url('/app'));
        $middleware->alias([
            'patient.portal' => EnsureUserIsPatient::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        if (config('isnad.health_snapshots.enabled', true)) {
            $time = (string) config('isnad.health_snapshots.daily_time', '02:00');

            $schedule->job(GenerateHealthIndicatorSnapshotJob::class)
                ->dailyAt($time)
                ->name('isnad-health-indicator-snapshot')
                ->withoutOverlapping();
        }

        if (config('isnad.queue.process_via_scheduler', false)) {
            $connection = (string) config('queue.default', 'database');

            if ($connection !== 'sync' && $connection !== 'null') {
                $schedule->command(
                    "queue:work {$connection} --stop-when-empty --max-time=55 --sleep=3 --tries=3"
                )
                    ->everyMinute()
                    ->withoutOverlapping(120)
                    ->name('isnad-queue-drain');
            }
        }
    })
    ->create();
