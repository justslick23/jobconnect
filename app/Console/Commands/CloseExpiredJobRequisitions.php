<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobRequisition;
use Carbon\Carbon;

class CloseExpiredJobRequisitions extends Command
{
    protected $signature = 'jobs:close-expired';
    protected $description = 'Automatically close job requisitions after deadline';

    public function handle()
    {
        $now = Carbon::now();

        $expiredJobs = JobRequisition::where('job_status', 'active')
            ->whereNotNull('application_deadline')
            ->where('application_deadline', '<', $now)
            ->update([
                'job_status' => 'closed',
            ]);

        $this->info("✅ Closed $expiredJobs expired job requisition(s).");
    }
}
