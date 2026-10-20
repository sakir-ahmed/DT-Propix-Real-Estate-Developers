<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array{email: string, password: string}|array{mobile: string, password: string}|array{username: string, password: string}
     */
    public function getCredentials(): array
    {
        $login = trim($this->string('login')->toString());

        $field = match (true) {
            filter_var($login, FILTER_VALIDATE_EMAIL) !== false => 'email',
            preg_match('/^(\+?88)?01[3-9]\d{8}$/', $login) === 1 => 'mobile',
            default => 'username',
        };

        if ($field === 'mobile') {
            $login = normalize_bd_mobile($login);
        }

        return [$field => $login, 'password' => $this->string('password')->toString()];
    }

    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->getCredentials();
        $field = (string) array_key_first($credentials);
        $tenantId = app(TenantContext::class)->id();

        $query = User::query()
            ->withoutGlobalScope('tenant')
            ->where($field, $credentials[$field]);

        if ($tenantId === null) {
            $query->where('is_super_admin', true);
        } else {
            $query->where('tenant_id', $tenantId);
        }

        $user = $query->first();
        $password = $credentials['password'];

        if (! $user instanceof User || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')->toString()).'|'.$this->ip());
    }

    private function ensureIsNotRateLimited(): void
    {
        $maxAttempts = (int) config('propix.login.max_attempts_per_minute');

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        throw ValidationException::withMessages([
            'login' => __('auth.throttle'),
        ])->status(429);
    }
}
