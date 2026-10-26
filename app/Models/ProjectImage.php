<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\PurgesWebsiteCache;
use Database\Factories\ProjectImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A published gallery image, floor plan or cover for a project. Alt text is
 * required so every published image is accessible.
 */
#[Fillable(['project_id', 'kind', 'path', 'alt', 'caption', 'sort_order'])]
class ProjectImage extends Model
{
    /** @use HasFactory<ProjectImageFactory> */
    use HasFactory, HasTranslations, PurgesWebsiteCache;

    /** @var list<string> */
    public const array KINDS = ['gallery', 'floor_plan', 'cover'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'alt' => 'array',
            'caption' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
