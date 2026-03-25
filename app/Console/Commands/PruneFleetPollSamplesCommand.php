<?php

namespace App\Console\Commands;

use App\Models\FleetPollSample;
use Illuminate\Console\Command;

class PruneFleetPollSamplesCommand extends Command
{
    protected $signature = 'fleet:prune-poll-samples {--days=45 : Delete samples older than this many days}';

    protected $description = 'Delete fleet poll history older than the retention window (keep ≥7 days for 7d sparklines)';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = FleetPollSample::query()->where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} sample(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
