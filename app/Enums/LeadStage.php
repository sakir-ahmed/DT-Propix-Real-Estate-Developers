<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadStage: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case VisitPlanned = 'visit_planned';
    case Visited = 'visited';
    case Negotiation = 'negotiation';
    case Booked = 'booked';
    case Lost = 'lost';

    /**
     * @return list<self>
     */
    public static function pipeline(): array
    {
        return [
            self::New,
            self::Contacted,
            self::VisitPlanned,
            self::Visited,
            self::Negotiation,
            self::Booked,
            self::Lost,
        ];
    }
}
