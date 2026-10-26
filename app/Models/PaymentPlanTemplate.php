<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentPlanTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Lines shape: list<array{label: string, kind: string, percent: float, due_days: int}>
 * Percentages are of the net price; due_days is counted from the booking date.
 */
#[Fillable(['name', 'lines'])]
class PaymentPlanTemplate extends Model
{
    /** @use HasFactory<PaymentPlanTemplateFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lines' => 'array',
        ];
    }
}
