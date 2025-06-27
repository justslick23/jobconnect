<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantReferences extends Model
{
    // Explicitly defining the table name since it's a reserved keyword in many DBs
    protected $table = 'references';

    // Allow mass assignment for the following fields
    protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'email',
        'phone',
        'notes',
    ];

    // Define inverse relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
