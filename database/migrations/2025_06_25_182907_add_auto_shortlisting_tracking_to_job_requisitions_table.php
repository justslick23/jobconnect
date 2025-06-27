<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('job_requisitions', function (Blueprint $table) {
            $table->boolean('auto_shortlisting_completed')->default(false);
            $table->timestamp('auto_shortlisting_completed_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('job_requisitions', function (Blueprint $table) {
            $table->dropColumn(['auto_shortlisting_completed', 'auto_shortlisting_completed_at']);
        });
    }
};