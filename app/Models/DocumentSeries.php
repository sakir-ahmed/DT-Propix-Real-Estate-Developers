<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'next_number'])]
class DocumentSeries extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'name';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'next_number' => 'integer',
        ];
    }
}
