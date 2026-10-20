<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\BelongsToTenant;
use App\Support\TenantContext;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'tenant_id',
    'branch_id',
    'name',
    'username',
    'email',
    'mobile',
    'password',
    'locale',
    'is_super_admin',
    'last_seen_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToBranch, BelongsToTenant, HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'mobile', 'username'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function canAccessAllBranches(): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        if (app(TenantContext::class)->id() === null) {
            return false;
        }

        return $this->hasAnyRole(['owner', 'general-manager', 'accountant']);
    }
}
