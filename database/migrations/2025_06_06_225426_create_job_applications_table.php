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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_requisition_id')->constrained()->onDelete('cascade');

            $table->enum('status', ['submitted',  'shortlisted', 'offer sent', 'rejected', 'hired'])->default('submitted');
            $table->timestamp('submitted_at')->nullable(); // optional if you want a separate field


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
