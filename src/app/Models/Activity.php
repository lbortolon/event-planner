<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory,SoftDeletes;

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

    public function isUserOrganizer(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function isUserInvited(User $user): bool
    {
        return $this->invitations()->where('user_id', $user->id)->exists();
    }
}