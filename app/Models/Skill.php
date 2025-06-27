<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    //
    protected $fillable = ['name'];


    public function jobRequisitions()
{
    return $this->belongsToMany(JobRequisition::class, 'job_requisition_skill');
}

}
