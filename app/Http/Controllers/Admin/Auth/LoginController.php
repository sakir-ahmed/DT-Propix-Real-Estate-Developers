<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\DemoMode;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(DemoMode $demoMode): Response
    {
        return Inertia::render('Admin/Auth/Login', [
            'demoUsers' => $demoMode->enabled() ? $this->demoUsers() : [],
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        return $this->login($request, $request->authenticate());
    }

    public function demo(Request $request, DemoMode $demoMode): RedirectResponse
    {
        abort_unless($demoMode->enabled(), 404);

        $user = User::query()->withoutGlobalScopes()->find($request->integer('user_id'));

        if (! $user instanceof User) {
            abort(404);
        }

        $tenantId = app(TenantContext::class)->id();

        if ($tenantId !== null && ! $user->is_super_admin && $user->tenant_id !== $tenantId) {
            abort(404);
        }

        return $this->login($request, $user);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function login(Request $request, User $user): RedirectResponse
    {
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $request->session()->put('last_activity_at', now()->getTimestamp());

        if ($user->is_super_admin) {
            return redirect()->route('platform.tenants.index');
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * @return list<array{id: int, name: string, username: string, email: string, company: string|null}>
     */
    private function demoUsers(): array
    {
        $tenantId = app(TenantContext::class)->id();

        $query = User::query()
            ->withoutGlobalScopes()
            ->with('tenant:id,name')
            ->orderBy('name');

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get()->map(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'company' => $user->is_super_admin ? null : $user->tenant?->name,
        ])->all();
    }
}
