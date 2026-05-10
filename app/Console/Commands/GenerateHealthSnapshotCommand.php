<?php

namespace App\Console\Commands;

use App\Jobs\GenerateHealthIndicatorSnapshotJob;
use Illuminate\Console\Command;

class GenerateHealthSnapshotCommand extends Command
{
    protected $signature = 'isnad:generate-health-snapshot {--sync : تنفيذ فوري بدون طابور}';

    protected $description = 'توليد لقطة مؤشرات صحية مجمّعة (يدوياً)';

    public function handle(): int
    {
        if ($this->option('sync')) {
            GenerateHealthIndicatorSnapshotJob::dispatchSync();
            $this->info('تم تنفيذ لقطة المؤشرات الصحية فوراً.');
        } else {
            GenerateHealthIndicatorSnapshotJob::dispatch();
            $this->info('تمت جدولة مهمة لقطة المؤشرات الصحية.');
        }

        return self::SUCCESS;
    }
}
