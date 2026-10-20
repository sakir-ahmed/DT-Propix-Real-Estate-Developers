<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    public function send(SendOtpRequest $request, OtpService $otp): JsonResponse
    {
        $sent = $otp->send($request->string('mobile')->toString(), (string) $request->ip());

        if (! $sent) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TOO_MANY_REQUESTS',
                    'message' => __('auth.otp_throttled'),
                    'details' => [],
                ],
            ], 429);
        }

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => __('auth.otp_sent'),
        ]);
    }

    public function verify(VerifyOtpRequest $request, OtpService $otp): JsonResponse
    {
        $valid = $otp->verify(
            $request->string('mobile')->toString(),
            $request->string('code')->toString(),
        );

        if (! $valid) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_FAILED',
                    'message' => __('auth.otp_invalid'),
                    'details' => ['code' => [__('auth.otp_invalid')]],
                ],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => ['verified' => true],
            'message' => __('auth.otp_verified'),
        ]);
    }
}
