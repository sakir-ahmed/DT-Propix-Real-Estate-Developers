<?php

declare(strict_types=1);

namespace App\Enums;

enum ScheduleRowKind: string
{
    case BookingMoney = 'booking_money';
    case DownPayment = 'down_payment';
    case Installment = 'installment';
    case Handover = 'handover';
    case Other = 'other';
}
