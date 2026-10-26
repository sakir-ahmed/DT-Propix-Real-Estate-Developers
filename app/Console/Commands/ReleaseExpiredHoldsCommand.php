<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Sales\ReleaseExpiredHoldsAction;
use Illuminate\Console\Command;

class ReleaseExpiredHoldsCommand extends Command
{
    protected $signature = 'propix:release-expired-holds';

    protected $description = 'Release expired unit holds and return the units to available (idempotent)';

    public function handle(ReleaseExpiredHoldsAction $action): int
    {
        $released = $action->handle();

        $this->info("Released {$released} expired hold(s).");

        return self::SUCCESS;
    }
}
