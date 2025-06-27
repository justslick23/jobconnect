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
    Schema::create('interview_reviews', function (Blueprint $table) {
        $table->id();

        $table->foreignId('interview_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // reviewer
        $table->text('comments')->nullable();
        $table->tinyInteger('rating')->nullable(); // Optional: out of 5 or 10
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_reviews');
    }
};
