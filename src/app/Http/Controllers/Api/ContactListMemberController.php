<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactList;
use App\Models\User;
use Illuminate\Http\Request;

class ContactListMemberController extends Controller
{
    // POST /api/contact-lists/{contactList}/members — add a member to a list
    public function store(Request $request, ContactList $contactList)
    {
        // Ownership check: only the list owner can add members.
        if ($contactList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorizzato.'], 403);
        }

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

        return response()->json($member, 201);
    }

    // DELETE /api/contact-lists/{contactList}/members/{user} — remove a member
    public function destroy(Request $request, ContactList $contactList, User $user)
    {
        // Ownership check: only the list owner can remove members.
        if ($contactList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorizzato.'], 403);
        }

        // Delete the pivot record linking this user to this list.
        $contactList->members()->where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Membro rimosso.']);
    }
}