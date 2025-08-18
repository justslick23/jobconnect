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
        Schema::table('applicant_education', function (Blueprint $table) {
            $table->string('status')->default('Completed')->after('institution'); // Completed / In Progress / Paused/Deferred
            $table->date('expected_graduation')->nullable()->after('end_date'); // For ongoing studies
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicant_education', function (Blueprint $table) {
            $table->dropColumn(['status', 'expected_graduation']);

        });
    }
};
