<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('interviews', function (Blueprint $table) {
            // Drop the columns
            $table->dropColumn(['interviewer', 'notes', 'status']);

            // Add applicant_id as a foreign key
            $table->foreignId('applicant_id')->after('job_application_id')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('interviews', function (Blueprint $table) {
            // Add back the dropped columns
            $table->string('interviewer')->after('job_application_id');
            $table->text('notes')->nullable()->after('interviewer');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled')->after('notes');

            // Drop the applicant_id column and its foreign key constraint
            $table->dropForeign(['applicant_id']);
            $table->dropColumn('applicant_id');
        });
    }
};
