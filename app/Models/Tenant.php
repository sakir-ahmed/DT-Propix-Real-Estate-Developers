<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TenantStatus;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'name',
    'slug',
    'custom_domain',
    'domain_verified_at',
    'plan_id',
    'status',
    'trial_ends_at',
    'grace_ends_at',
    'suspended_at',
    'retention_until',
    'message_credits',
    'limit_overrides',
    'module_overrides',
    'locale',
    'timezone',
    'settings',
])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory, LogsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'domain_verified_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'suspended_at' => 'datetime',
            'retention_until' => 'datetime',
            'limit_overrides' => 'array',
            'module_overrides' => 'array',
            'settings' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'status', 'plan_id', 'message_credits', 'custom_domain'])
            ->logOnlyDirty();
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return HasMany<Branch, $this>
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @param  Builder<Tenant>  $query
     * @return Builder<Tenant>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [TenantStatus::Trial, TenantStatus::Active, TenantStatus::ReadOnly]);
    }

    public function host(): string
    {
        if ($this->custom_domain !== null && $this->domain_verified_at !== null) {
            return $this->custom_domain;
        }

        return $this->slug.'.'.config('propix.domain');
    }

    /**
     * @return list<string>
     */
    public function enabledModules(): array
    {
        $modules = $this->module_overrides ?? $this->plan->modules ?? [];

        return array_values($modules);
    }

    public function limitFor(string $resource): ?int
    {
        $overrides = $this->limit_overrides ?? [];

        if (array_key_exists($resource, $overrides)) {
            $value = $overrides[$resource];

            return $value === null ? null : (int) $value;
        }

        $value = $this->plan->limits[$resource] ?? null;

        return $value === null ? null : (int) $value;
    }

    public function cachePrefix(): string
    {
        return 'tenant:'.$this->id.':';
    }

    public function storagePath(string $path = ''): string
    {
        return trim('tenants/'.$this->id.'/'.$path, '/');
    }
}
