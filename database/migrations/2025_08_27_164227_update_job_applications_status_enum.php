<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update enum to include 'potential' and 'interview_scheduled'
        DB::statement("ALTER TABLE job_applications MODIFY COLUMN status ENUM('submitted','shortlisted','review','interview scheduled','offer sent','rejected','hired') NOT NULL DEFAULT 'submitted'");
    }

    public function down(): void
    {
        // Revert back to old enum
        DB::statement("ALTER TABLE job_applications MODIFY COLUMN status ENUM('submitted','shortlisted','offer sent','rejected','hired') NOT NULL DEFAULT 'submitted'");
    }
};
