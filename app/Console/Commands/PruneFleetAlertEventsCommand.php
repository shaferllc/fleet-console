<?php

namespace App\Console\Commands;

use App\Models\FleetAlertEvent;
use Illuminate\Console\Command;

class PruneFleetAlertEventsCommand extends Command
{
    protected $signature = 'fleet:prune-alert-events {--days=90 : Delete events older than this many days}';

    protected $description = 'Delete fleet_alert_events rows older than the retention window';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = FleetAlertEvent::query()->where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} alert event(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
