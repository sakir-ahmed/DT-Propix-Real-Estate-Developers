<?php

declare(strict_types=1);

use App\Exceptions\ModuleDisabledException;
use App\Exceptions\PlanLimitReachedException;
use App\Exceptions\TenantNotWritableException;
use App\Models\Tenant;
use App\Support\PlanGate;

it('rejects writes when the tenant is read only or suspended', function () {
    $gate = app(PlanGate::class);

    expect(fn () => $gate->assertWritable(Tenant::factory()->readOnly()->create()))
        ->toThrow(TenantNotWritableException::class);

    expect(fn () => $gate->assertWritable(Tenant::factory()->suspended()->create()))
        ->toThrow(TenantNotWritableException::class);
});

it('rejects a disabled module', function () {
    $tenant = Tenant::factory()->create();

    expect(fn () => app(PlanGate::class)->assertModuleEnabled($tenant, 'hr'))
        ->toThrow(ModuleDisabledException::class);
});

it('rejects a resource past the plan limit', function () {
    $tenant = Tenant::factory()->create();

    expect(fn () => app(PlanGate::class)->assertWithinLimit($tenant, 'users', 5))
        ->toThrow(PlanLimitReachedException::class);
});
