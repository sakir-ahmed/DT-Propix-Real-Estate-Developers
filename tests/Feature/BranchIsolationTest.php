<?php

declare(strict_types=1);

use App\Actions\Access\LogAccessAction;
use App\Models\AccessLog;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\CompanySeeder;

beforeEach(function () {
    $this->seed(CompanySeeder::class);
});

it('hides another branch from queries and returns 404 for their branch url', function () {
    $headOffice = Branch::query()->where('code', 'HO')->firstOrFail();
    $satellite = Branch::factory()->satellite()->create(['code' => 'CTG2']);

    $manager = User::factory()->create([
        'branch_id' => $headOffice->id,
        'username' => 'ho-manager',
        'email' => 'manager@company.test',
        'mobile' => '8801811000001',
    ]);
    $manager->assignRole('sales-manager');

    $satelliteUser = User::factory()->create([
        'branch_id' => $satellite->id,
        'username' => 'ctg-manager',
        'email' => 'ctg@company.test',
        'mobile' => '8801811000002',
    ]);
    $satelliteUser->assignRole('sales-manager');

    $this->actingAs($manager)
        ->get('/admin/branches/'.$satellite->id)
        ->assertNotFound();

    $this->actingAs($manager)
        ->get('/admin/branches/'.$headOffice->id)
        ->assertOk();

    $this->actingAs($manager);
    expect(Branch::query()->pluck('id')->all())->toBe([$headOffice->id])
        ->and(User::query()->pluck('id')->all())->toContain($manager->id)
        ->and(User::query()->pluck('id')->all())->not->toContain($satelliteUser->id);

    auth()->forgetGuards();

    $this->actingAs($satelliteUser);
    expect(Branch::query()->pluck('id')->all())->toBe([$satellite->id]);
});

it('lets owners and the super admin see every branch', function () {
    $satellite = Branch::factory()->satellite()->create(['code' => 'CTG2']);

    $owner = User::query()->where('username', 'owner')->firstOrFail();
    $superAdmin = User::query()->where('is_super_admin', true)->firstOrFail();

    $this->actingAs($owner)
        ->get('/admin/branches/'.$satellite->id)
        ->assertOk();

    $this->actingAs($superAdmin)
        ->get('/admin/branches/'.$satellite->id)
        ->assertOk();

    $this->actingAs($owner);
    expect(Branch::query()->count())->toBe(3);
});

it('logs access without exposing other branches', function () {
    $manager = User::factory()->create([
        'username' => 'ho-manager',
        'email' => 'manager@company.test',
        'mobile' => '8801811000001',
    ]);
    $manager->assignRole('sales-manager');

    $this->actingAs($manager);
    app(LogAccessAction::class)->handle('document.view', null, $manager);

    expect(AccessLog::query()->where('action', 'document.view')->count())->toBe(1);
});
