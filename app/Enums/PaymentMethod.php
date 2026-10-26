<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Cheque = 'cheque';
    case Bank = 'bank';
    case Mobile = 'mobile';
    case Card = 'card';
}
