<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactListMember extends Model
{
    protected $fillable = [
        'contact_list_id',
        'user_id',
    ];

    public function contactList()
    {
        return $this->belongsTo(ContactList::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
