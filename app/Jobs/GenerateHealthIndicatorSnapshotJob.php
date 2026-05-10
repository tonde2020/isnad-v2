<?php

namespace App\Jobs;

use App\Services\HealthIndicatorSnapshotGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateHealthIndicatorSnapshotJob implements ShouldQueue
{
    use Queueable;

    public function handle(HealthIndicatorSnapshotGenerator $generator): void
    {
        $generator->generateNationalDaily();
    }
}
