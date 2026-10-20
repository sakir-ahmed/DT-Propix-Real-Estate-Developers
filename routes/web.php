<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\UiKitController;
use App\Http\Controllers\Platform\TenantController;
use App\Models\User;
use App\Support\HostTenantResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['tenant', 'locale', 'no-ssr', 'inertia'])->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/admin/login', [LoginController::class, 'create'])->name('admin.login');
        Route::post('/admin/login', [LoginController::class, 'store'])->name('admin.login.store');
        Route::post('/admin/login/demo', [LoginController::class, 'demo'])->name('admin.login.demo');
    });

    Route::get('/locale/{locale}', LocaleController::class)->name('locale.update');

    Route::middleware(['auth', 'idle', 'tenant-user'])->group(function (): void {
        Route::post('/admin/logout', [LoginController::class, 'destroy'])->name('admin.logout');

        Route::get('/admin', DashboardController::class)->name('admin.dashboard');
        Route::get('/admin/ui-kit', UiKitController::class)->name('admin.ui-kit');
        Route::get('/admin/branches/{branch}', [BranchController::class, 'show'])->name('admin.branches.show');

        Route::middleware('super-admin')->prefix('platform')->name('platform.')->group(function (): void {
            Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
            Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
            Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        });
    });
});

Route::middleware(['tenant', 'locale', 'no-ssr', 'inertia'])->get('/', function (Request $request, HostTenantResolver $hosts) {
    if (! $hosts->isPlatformHost($request->getHost())) {
        abort(404);
    }

    $user = $request->user();

    if ($user instanceof User && $user->is_super_admin) {
        return redirect()->route('platform.tenants.index');
    }

    if ($user instanceof User) {
        return redirect()->route('admin.dashboard');
    }

    return app()->call([app(LoginController::class), 'create']);
});
