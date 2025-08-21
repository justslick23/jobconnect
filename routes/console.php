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
    }

    $this->info("Closed {$jobsToClose->count()} job requisitions.");

    // 2️⃣ Run auto-shortlisting ONLY if deadline was yesterday AND current time >= 08:00
    $jobsForShortlisting = JobRequisition::where('job_status', 'closed')
        ->whereNotNull('application_deadline')
        ->whereDate('application_deadline', '=', $now->copy()->subDay()->toDateString())
        ->whereTime('application_deadline', '<', '23:59:59') // make sure job deadline is yesterday
        ->where('auto_shortlisting_completed', false)
        ->get();

    if ($now->hour >= 8) {
        foreach ($jobsForShortlisting as $job) {
            $this->call('jobs:auto-shortlist', [
                '--requisition-id' => $job->id
            ]);
            $this->info("Auto-shortlisted job requisition #{$job->id}.");
        }
    } else {
        $this->info('It is before 08:00, auto-shortlisting will run later.');
    }

})->describe('Close expired job requisitions and auto-shortlist next day at 08:00');
