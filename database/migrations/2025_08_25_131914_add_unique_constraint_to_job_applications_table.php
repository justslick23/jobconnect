<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // Add unique constraint on user_id and job_requisition_id combination
            $table->unique(['user_id', 'job_requisition_id'], 'unique_user_job_application');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique('unique_user_job_application');
        });
    }
};