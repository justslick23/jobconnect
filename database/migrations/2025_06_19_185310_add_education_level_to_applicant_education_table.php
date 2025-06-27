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
        Schema::table('applicant_education', function (Blueprint $table) {
            $table->string('education_level')->nullable()->after('field_of_study');
        });

        // Optionally seed existing records with default values (e.g., using DB::table)
        DB::table('applicant_education')->update([
            'education_level' => 'Unknown'
        ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('applicant_education', function (Blueprint $table) {
            $table->dropColumn('education_level');
        });
    }
};
