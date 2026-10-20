<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanLimitReachedException extends Exception
{
    public function __construct(public string $resource, public int $limit)
    {
        parent::__construct("Plan limit reached for {$resource}.");
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        $message = __('plans.limit_reached', ['resource' => $this->resource]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAN_LIMIT_REACHED',
                    'message' => $message,
                    'details' => [
                        'resource' => $this->resource,
                        'limit' => $this->limit,
                    ],
                ],
            ], 403);
        }

        return back()->withErrors(['plan' => $message]);
    }
}
