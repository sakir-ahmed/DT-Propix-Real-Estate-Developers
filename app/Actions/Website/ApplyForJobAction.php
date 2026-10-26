<?php

declare(strict_types=1);

namespace App\Actions\Website;

use App\Models\JobApplication;
use App\Models\JobPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Stores a career application. The CV goes to the private disk; only its path
 * is kept on the record.
 */
class ApplyForJobAction
{
    /**
     * @param  array{name: string, mobile: string, email?: string|null, cover_letter?: string|null}  $data
     */
    public function handle(JobPost $job, array $data, UploadedFile $cv): JobApplication
    {
        $path = $cv->store('applications/'.$job->id, 'local');

        $application = JobApplication::query()->create([
            'job_post_id' => $job->id,
            'name' => $data['name'],
            'mobile' => normalize_bd_mobile($data['mobile']),
            'email' => $data['email'] ?? null,
            'cover_letter' => $data['cover_letter'] ?? null,
            'cv_path' => is_string($path) ? $path : '',
            'status' => 'received',
        ]);

        Log::info('Job application received.', ['job_post_id' => $job->id, 'application_id' => $application->id]);

        return $application;
    }
}
