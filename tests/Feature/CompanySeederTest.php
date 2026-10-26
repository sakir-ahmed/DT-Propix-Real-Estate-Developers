<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Illuminate\Support\Facades\Hash;

it('creates the owner and the hidden super-admin with the starter password', function () {
    $this->seed(CompanySeeder::class);

    $owner = User::query()->where('username', 'owner')->firstOrFail();
    $superAdmin = User::query()->where('is_super_admin', true)->firstOrFail();

    expect(Hash::check(CompanySeeder::DEFAULT_PASSWORD, $owner->password))->toBeTrue()
        ->and($owner->hasRole('owner'))->toBeTrue()
        ->and($owner->branch_id)->not->toBeNull()
        ->and(Hash::check(CompanySeeder::DEFAULT_PASSWORD, $superAdmin->password))->toBeTrue()
        ->and($superAdmin->username)->toBe('superadmin')
        ->and($superAdmin->branch_id)->toBeNull();
});

it('keeps a changed password and adds no duplicate rows when seeded again', function () {
    $this->seed(CompanySeeder::class);
    $owner = User::query()->where('username', 'owner')->firstOrFail();
    $owner->update(['password' => 'changed-by-owner']);

    $this->seed(CompanySeeder::class);

    expect(Hash::check('changed-by-owner', $owner->refresh()->password))->toBeTrue()
        ->and(User::query()->where('username', 'owner')->count())->toBe(1)
        ->and(User::query()->where('is_super_admin', true)->count())->toBe(1)
        ->and(Branch::query()->count())->toBe(2);
});
