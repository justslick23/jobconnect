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
        Schema::table('job_requisitions', function (Blueprint $table) {
            $table->integer('min_experience')->default(0)->after('application_deadline');
            $table->string('education_level')->after('min_experience');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_requisitions', function (Blueprint $table) {
            //
        });
    }
};
