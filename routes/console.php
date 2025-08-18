<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\JobRequisition;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('jobs:close-expired-and-shortlist', function () {
    // Fetch all jobs to close
    $jobsToClose = JobRequisition::where('job_status', 'active')
        ->whereNotNull('application_deadline')
        ->where('application_deadline', '<', now())
        ->get();

    $closedCount = $jobsToClose->count();

    if ($closedCount === 0) {
        $this->info('No job requisitions to close.');
        return;
    }

    // Close jobs
    foreach ($jobsToClose as $job) {
        $job->job_status = 'closed';
        $job->save();
    }

    $this->info("Closed {$closedCount} job requisitions.");

    // Now run auto-shortlisting on those just closed jobs by passing each ID
    foreach ($jobsToClose as $job) {
        $this->call('jobs:auto-shortlist', [
            '--requisition-id' => $job->id
        ]);
        $this->info("Auto-shortlisted job requisition #{$job->id}.");
    }
})->describe('Close expired job requisitions and auto-shortlist immediately');
