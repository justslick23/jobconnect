<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationScoresTable extends Migration
{
    public function up()
    {
        Schema::create('application_scores', function (Blueprint $table) {
            $table->id();

            // Use foreignId if applications.id is unsignedBigInteger (default)
            $table->foreignId('application_id')->constrained('job_applications')->onDelete('cascade');

            $table->float('skills_score')->default(0);
            $table->float('experience_score')->default(0);
            $table->float('education_score')->default(0);
            $table->float('qualification_bonus')->default(0);
            $table->float('total_score')->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('application_scores');
    }
}
