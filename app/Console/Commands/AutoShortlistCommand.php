<?php

namespace App\Console\Commands;

use App\Models\JobRequisition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoShortlistCommand extends Command
{
    protected $signature = 'jobs:auto-shortlist {--threshold=70} {--requisition-id=}';
    protected $description = 'Run auto-shortlisting for job requisitions';

    public function handle()
    {
        $threshold = $this->option('threshold');
        $requisitionId = $this->option('requisition-id');
        
        $query = JobRequisition::query();
        
        if ($requisitionId) {
            // Manual processing - allow override
            $query->where('id', $requisitionId);
        } else {
            // Automatic processing - only unprocessed requisitions with passed deadlines
            $query->where('application_deadline', '<', now())
                  ->where('auto_shortlisting_completed', false);
        }
        
        $requisitions = $query->get();
        
        if ($requisitions->isEmpty()) {
            $this->info('No job requisitions found that need auto-shortlisting.');
            return;
        }
        
        $this->info("Starting auto-shortlisting for {$requisitions->count()} job requisition(s) with passed deadlines...");
        
        foreach ($requisitions as $requisition) {
            try {
                $applicationsCount = $requisition->applications()->count();
                
                if ($applicationsCount === 0) {
                    $this->warn("⚠️  Job Requisition #{$requisition->id} ({$requisition->title}): No applications to process");
                    
                    // Still mark as completed to avoid future processing
                    $requisition->update([
                        'auto_shortlisting_completed' => true,
                        'auto_shortlisting_completed_at' => now()
                    ]);
                    continue;
                }
                
                $shortlisted = $requisition->autoShortlistApplicants($threshold);
                
                $shortlisted->each(function ($application) {
                    $application->is_shortlisted = true;
                    $application->saveQuietly();
                });
                
                // Mark as completed
                $requisition->update([
                    'auto_shortlisting_completed' => true,
                    'auto_shortlisting_completed_at' => now()
                ]);
                
                $this->info("✅ Job Requisition #{$requisition->id} ({$requisition->title}): {$shortlisted->count()}/{$applicationsCount} applications shortlisted (Deadline: {$requisition->application_deadline})");
                
            } catch (\Exception $e) {
                $this->error("❌ Job Requisition #{$requisition->id}: Failed - {$e->getMessage()}");
                Log::error("Auto-shortlisting failed for Job Requisition #{$requisition->id}: " . $e->getMessage());
                
                // Don't mark as completed if it failed - allow retry
            }
        }
        
        $this->info('Auto-shortlisting completed!');
    }
}

// Add to app/Console/Kernel.php in the schedule method:
// $schedule->command('jobs:auto-shortlist')->dailyAt('09:00'); // Run daily at 9 AM
// $schedule->command('jobs:auto-shortlist')->twiceDaily(9, 17); // Run at 9 AM and 5 PM

// Usage examples:
// php artisan jobs:auto-shortlist                    # Process all requisitions with passed deadlines
// php artisan jobs:auto-shortlist --threshold=80     # Process with 80% threshold  
// php artisan jobs:auto-shortlist --requisition-id=5 # Process specific requisition (ignores deadline check)