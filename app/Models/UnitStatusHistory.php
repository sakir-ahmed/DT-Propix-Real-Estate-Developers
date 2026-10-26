<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UnitStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['unit_id', 'from_status', 'to_status', 'changed_by', 'note'])]
class UnitStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'unit_status_history';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => UnitStatus::class,
            'to_status' => UnitStatus::class,
            'changed_by' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
