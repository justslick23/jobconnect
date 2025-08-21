<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobRequisition;
use Carbon\Carbon;

class CloseExpiredJobRequisitions extends Command
{
    protected $signature = 'jobs:close-expired';
    protected $description = 'Automatically close job requisitions after deadline (date + time)';

    public function handle()
    {
        // Get current date & time
        $now = Carbon::now(); // includes hours, minutes, seconds

        // Close all active job requisitions where deadline has passed
        $expiredJobs = JobRequisition::where('job_status', 'active')
            ->whereNotNull('application_deadline')
            ->where('application_deadline', '<=', $now) // <= ensures exact time comparison
            ->update([
                'job_status' => 'closed',
            ]);

        $this->info("✅ Closed $expiredJobs expired job requisition(s) (including time).");
    }
}
