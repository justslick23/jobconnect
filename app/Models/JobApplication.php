<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\ApplicationScore;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_requisition_id',
        'status',
        'submitted_at',
        'notes',
        'uuid',
        'application_source',

    ];

    protected $casts = [
        'submitted_at' => 'datetime',

    ];

    // Keep default primaryKey = 'id', no override

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function score()
    {
        return $this->hasOne(ApplicationScore::class, 'application_id');
    }

    public function jobRequisition()
    {
        return $this->belongsTo(JobRequisition::class);
    }

    public function reviews()
    {
        return $this->hasMany(ApplicationReview::class, 'job_application_id');
    }

    public function interviews()
{
    return $this->hasOne(Interview::class);
}

}
