<?php

declare(strict_types=1);

namespace App\Enums;

enum TenantStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case ReadOnly = 'read_only';
    case Suspended = 'suspended';

    public function isWritable(): bool
    {
        return in_array($this, [self::Trial, self::Active], true);
    }
}
