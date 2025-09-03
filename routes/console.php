<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\JobRequisition;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('jobs:close-expired-and-shortlist', function () {

    $now = Carbon::now();

    // 1️⃣ Close jobs whose deadline has passed
    $jobsToClose = JobRequisition::where('job_status', 'active')
        ->whereNotNull('application_deadline')
        ->where('application_deadline', '<=', $now)
        ->get();

    foreach ($jobsToClose as $job) {
        $job->job_status = 'closed';
        $job->save();

        $this->info("Closed job requisition #{$job->id} ({$job->job_title}).");

        // 2️⃣ Immediately run auto-shortlisting if not yet completed
        if (! $job->auto_shortlisting_completed) {
            try {
                $this->call('jobs:auto-shortlist', [
                    '--requisition-id' => $job->id
                ]);
                $this->info("Auto-shortlisted job requisition #{$job->id}.");
            } catch (\Exception $e) {
                $this->error("Failed to auto-shortlist job #{$job->id}: " . $e->getMessage());
            }
        }
    }

    $this->info("Processed {$jobsToClose->count()} job requisitions.");

})->describe('Close expired job requisitions and immediately run auto-shortlisting');
