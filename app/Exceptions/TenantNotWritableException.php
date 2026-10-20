<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\TenantStatus;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantNotWritableException extends Exception
{
    public function __construct(public TenantStatus $status)
    {
        parent::__construct("Tenant is {$status->value}.");
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        $code = $this->status === TenantStatus::Suspended ? 'TENANT_SUSPENDED' : 'TENANT_READ_ONLY';
        $message = __("tenants.{$this->status->value}");

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'details' => ['status' => $this->status->value],
                ],
            ], 403);
        }

        return back()->withErrors(['tenant' => $message]);
    }
}
