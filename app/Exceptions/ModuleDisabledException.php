<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModuleDisabledException extends Exception
{
    public function __construct(public string $module)
    {
        parent::__construct("Module {$module} is disabled.");
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        $message = __('plans.module_disabled', ['module' => $this->module]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'MODULE_DISABLED',
                    'message' => $message,
                    'details' => ['module' => $this->module],
                ],
            ], 403);
        }

        return back()->withErrors(['plan' => $message]);
    }
}
