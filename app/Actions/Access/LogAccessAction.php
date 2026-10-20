<?php

declare(strict_types=1);

namespace App\Actions\Access;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class LogAccessAction
{
    public function handle(string $action, ?Model $subject = null, ?User $user = null, ?Request $request = null): AccessLog
    {
        $actor = $user ?? $request?->user();

        return AccessLog::query()->create([
            'user_id' => $actor instanceof User ? $actor->id : null,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
