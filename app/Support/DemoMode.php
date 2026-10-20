<?php

declare(strict_types=1);

namespace App\Support;

class DemoMode
{
    public function enabled(): bool
    {
        return ! app()->isProduction() && config('propix.demo.enabled') === true;
    }
}
