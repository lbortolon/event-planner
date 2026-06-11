<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactList;
use Illuminate\Http\Request;

class ContactListController extends Controller
{
    // GET /api/contact-lists — returns the authenticated user's lists
    public function index(Request $request)
    {
        // $request->user() returns the authenticated User model.
        // How it works: the auth:sanctum middleware extracts the Bearer token
        // from the Authorization header, hashes it, looks it up in the
        // personal_access_tokens table and attaches the related User to the request.
        // If the token is invalid, the middleware rejects the request (401)
        // before ever reaching this controller.
        $lists = $request->user()->contactLists()->get();

        // response()->json() defaults to HTTP 200 when no status code is given.
        return response()->json($lists);
    }

    // POST /api/contact-lists — creates a new list
    public function store(Request $request)
    {
        // Validates the JSON body of the request.
        // If validation fails, Laravel automatically stops execution and
        // returns a 422 Unprocessable Entity with error details as JSON.
        // No manual error handling needed — code below only runs on success.
        // Note: the client must send the Accept: application/json header,
        // otherwise Laravel would attempt a redirect (web form behavior).
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Creating through the relationship automatically sets user_id.
        $list = $request->user()->contactLists()->create($validated);

        // 201 Created is the proper REST status code for resource creation.
        return response()->json($list, 201);
    }

    // GET /api/contact-lists/{id} — list detail including its members
    public function show(Request $request, ContactList $contactList)
    {
        // Ownership check: the list must belong to the requesting user.
        // TODO: refactor to a Policy (Laravel's dedicated authorization layer).
        if ($contactList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorizzato.'], 403);
        }

        // Nested eager loading: 'members' alone would only load the pivot
        // records (ids), because the relation chain is:
        // ContactList -> hasMany -> ContactListMember -> belongsTo -> User.
        // 'members.user' also loads the related User model for each member,
        // so the response includes names/emails instead of bare ids.
        $contactList->load('members.user');

        return response()->json($contactList);
    }

    // PUT /api/contact-lists/{id} — renames a list
    public function update(Request $request, ContactList $contactList)
    {
        // Same ownership check as show().
        if ($contactList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorizzato.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $contactList->update($validated);

        return response()->json($contactList);
    }

    // DELETE /api/contact-lists/{id}
    public function destroy(Request $request, ContactList $contactList)
    {
        // Same ownership check as show().
        if ($contactList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorizzato.'], 403);
        }

        // Soft delete: since the model uses the SoftDeletes trait, this only
        // sets the deleted_at column. The record stays in the database and
        // future queries exclude it automatically.
        $contactList->delete();

        return response()->json(['message' => 'Lista eliminata.']);
    }
}