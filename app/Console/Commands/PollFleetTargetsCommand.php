<?php

namespace App\Console\Commands;

use App\Services\FleetTargetPoller;
use Illuminate\Console\Command;

class PollFleetTargetsCommand extends Command
{
    protected $signature = 'fleet:poll-targets';

    protected $description = 'Poll all configured fleet targets and store samples (scheduled when FLEET_BACKGROUND_POLL_ENABLED is true).';

    public function handle(FleetTargetPoller $poller): int
    {
        if (! config('fleet_console.background_poll_enabled')) {
            $this->comment('Skipped: FLEET_BACKGROUND_POLL_ENABLED is false.');

            return self::SUCCESS;
        }

        $results = $poller->pollAll();
        $ok = collect($results)->where('ok', true)->count();
        $this->info('Polled '.count($results)." targets ({$ok} OK).");

        return self::SUCCESS;
    }
}
