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
        Schema::create('interview_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->constrained()->onDelete('cascade');
            $table->foreignId('interviewer_id')->constrained('users')->onDelete('cascade'); // who scored
            $table->integer('technical_skills')->nullable();    // 1-5
            $table->integer('communication')->nullable();       // 1-5
            $table->integer('cultural_fit')->nullable();        // 1-5
            $table->integer('problem_solving')->nullable();     // 1-5
            $table->text('comments')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_scores');
    }
};
