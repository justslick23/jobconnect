<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name' ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'department_manager', 'department_id', 'user_id')->withTimestamps();
    }

    public function jobRequisitions()
    {
        return $this->hasMany(JobRequisition::class);
    }

    
    
}
