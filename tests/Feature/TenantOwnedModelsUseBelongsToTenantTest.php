<?php

declare(strict_types=1);

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

it('fails when a tenant-owned model omits BelongsToTenant', function () {
    $models = collect(File::allFiles(app_path('Models')))
        ->map(fn ($file): string => 'App\\Models\\'.$file->getBasename('.php'))
        ->filter(fn (string $class): bool => class_exists($class) && is_subclass_of($class, Model::class));

    expect($models)->not->toBeEmpty();

    foreach ($models as $class) {
        /** @var Model $model */
        $model = new $class;

        if (! Schema::hasColumn($model->getTable(), 'tenant_id')) {
            continue;
        }

        expect(in_array(BelongsToTenant::class, class_uses_recursive($class), true))
            ->toBeTrue("{$class} has tenant_id but does not use BelongsToTenant");
    }
});
