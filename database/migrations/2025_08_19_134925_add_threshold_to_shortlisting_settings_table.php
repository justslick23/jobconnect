<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shortlisting_settings', function (Blueprint $table) {
            $table->decimal('threshold', 5, 2)
                  ->default(70.00)
                  ->after('qualification_bonus')
                  ->comment('Minimum total score (%) required to shortlist an applicant');
        });
    }

    public function down(): void
    {
        Schema::table('shortlisting_settings', function (Blueprint $table) {
            $table->dropColumn('threshold');
        });
    }
};
