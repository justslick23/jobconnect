<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationAttachment extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'file_path',
        'original_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
