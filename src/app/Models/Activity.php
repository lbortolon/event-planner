<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'location',
        'starts_at',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }
}