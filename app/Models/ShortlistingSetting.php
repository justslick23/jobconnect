<?php

// app/Models/ShortlistingSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortlistingSetting extends Model
{
    protected $fillable = [
        'skills_weight',
        'experience_weight',
        'education_weight',
        'qualification_bonus',
    ];
}
