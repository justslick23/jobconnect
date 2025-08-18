<?php

// database/migrations/xxxx_xx_xx_create_shortlisting_settings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShortlistingSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('shortlisting_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('skills_weight', 5, 2)->default(40);
            $table->decimal('experience_weight', 5, 2)->default(30);
            $table->decimal('education_weight', 5, 2)->default(20);
            $table->decimal('qualification_bonus', 5, 2)->default(10);
            $table->timestamps();
        });
        

        // Seed default record
        \DB::table('shortlisting_settings')->insert([
            'skills_weight' => 40,
            'experience_weight' => 30,
            'education_weight' => 20,
            'qualification_bonus' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('shortlisting_settings');
    }
}
