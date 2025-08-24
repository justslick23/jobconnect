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
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->boolean('is_draft')->default(true)->after('district');
            $table->timestamp('completed_at')->nullable()->after('is_draft');
            $table->index(['user_id', 'is_draft']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_draft']);
            $table->dropColumn(['is_draft', 'completed_at']);
        });
    }
};