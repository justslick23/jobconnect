<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });

        // Fill existing records with generated UUIDs
        $jobApplications = DB::table('job_applications')->get();

        foreach ($jobApplications as $application) {
            DB::table('job_applications')
                ->where('id', $application->id)
                ->update(['uuid' => (string) Str::uuid()]);
        }

        // Make uuid column NOT NULL after populating
        Schema::table('job_applications', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Reverse changes (remove uuid primary key, re-add id)
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropPrimary('job_applications_pkey'); // adjust constraint name as needed
            $table->dropColumn('uuid');
            $table->bigIncrements('id');
        });
    }
};
