<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactListMemberResource;
use App\Models\ContactList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContactListMemberController extends Controller
{
    // POST /api/contact-lists/{contactList}/members — add a member to a list
    public function store(Request $request, ContactList $contactList)
    {
        Gate::authorize('update', $contactList);

        $validated = $request->validate([
            // The user to add must exist in the users table.
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Prevent adding yourself to your own list.
        if ($validated['user_id'] === $request->user()->id) {
            return response()->json(['message' => 'Non puoi aggiungere te stesso a una lista.'], 422);
        }

        // The unique constraint on (contact_list_id, user_id) prevents
        // duplicates at DB level. firstOrCreate avoids a duplicate error
        // by checking first, creating only if not already present.
        $member = $contactList->members()->firstOrCreate([
            'user_id' => $validated['user_id'],
        ]);

        // return response()->json($member, 201);
        return new ContactListMemberResource($member);
    }

    // DELETE /api/contact-lists/{contactList}/members/{user} — remove a member
    public function destroy(Request $request, ContactList $contactList, User $user)
    {
        Gate::authorize('delete', $contactList);

        // Delete the pivot record linking this user to this list.
        $contactList->members()->where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Membro rimosso.']);
    }
}