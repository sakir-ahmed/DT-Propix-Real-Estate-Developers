<?php

declare(strict_types=1);

namespace App\Enums;

enum UnitStatus: string
{
    case Available = 'available';
    case OnHold = 'on_hold';
    case Booked = 'booked';
    case Sold = 'sold';
    case HandedOver = 'handed_over';
    case LandownerShare = 'landowner_share';
    case Blocked = 'blocked';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Available => [self::OnHold, self::Booked, self::LandownerShare, self::Blocked],
            self::OnHold => [self::Available, self::Booked],
            self::Booked => [self::Available, self::Sold],
            self::Sold => [self::Available, self::HandedOver],
            self::HandedOver => [],
            self::LandownerShare => [self::Available, self::Sold],
            self::Blocked => [self::Available],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }
}
