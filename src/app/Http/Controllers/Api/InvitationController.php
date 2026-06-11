<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Invitation;
use App\Models\ContactList;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    // POST /api/activities/{activity}/invitations
    // Invite one or more users to an activity, by user_ids and/or contact_list_ids.
    public function store(Request $request, Activity $activity)
    {
        // Only the organizer can invite people.
        if ($activity->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorizzato.'], 403);
        }

        $request->validate([
            'user_ids'           => 'nullable|array',
            'user_ids.*'         => 'integer|exists:users,id',
            'contact_list_ids'   => 'nullable|array',
            'contact_list_ids.*' => 'integer|exists:contact_lists,id',
        ]);

        // At least one of the two fields must be present and non-empty.
        if (empty($request->user_ids) && empty($request->contact_list_ids)) {
            return response()->json([
                'message' => 'Specifica almeno un utente o una lista.'
            ], 422);
        }

        $userIds = collect($request->user_ids ?? []);

        // Expand contact lists into their member user_ids.
        if (!empty($request->contact_list_ids)) {
            $listUserIds = ContactList::whereIn('id', $request->contact_list_ids)
                ->where('user_id', $request->user()->id) // only own lists
                ->with('members')
                ->get()
                ->flatMap(fn($list) => $list->members->pluck('user_id'));

            $userIds = $userIds->merge($listUserIds);
        }

        // Remove duplicates, exclude the organizer.
        $userIds = $userIds
            ->unique()
            ->reject(fn($id) => $id === $request->user()->id)
            ->values();

        // Get already invited user ids to avoid duplicates.
        $alreadyInvited = $activity->invitations()
            ->whereIn('user_id', $userIds)
            ->pluck('user_id');

        $toInvite = $userIds->diff($alreadyInvited);

        // Create invitations for all new users.
        $invitations = $toInvite->map(fn($userId) => $activity->invitations()->create([
            'user_id' => $userId,
            'status'  => 'pending',
        ]));

        return response()->json([
            'invited' => $invitations->count(),
            'skipped' => $alreadyInvited->count(),
        ], 201);
    }

    // PATCH /api/activities/{activity}/invitations/{invitation}
    // Respond to an invitation (accept or decline). Invited user only.
    public function update(Request $request, Activity $activity, Invitation $invitation)
    {
        // Only the invited user can respond to their own invitation.
        if ($invitation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorizzato.'], 403);
        }

        // Prevent responding to an already answered invitation.
        if ($invitation->status !== 'pending') {
            return response()->json([
                'message' => 'Hai già risposto a questo invito.'
            ], 422);
        }

        // Prevent responding to invitations for past activities.
        // starts_at is cast to Carbon in the Activity model, so isPast() is available.
        if ($activity->starts_at->isPast()) {
            return response()->json([
                'message' => 'Non puoi rispondere a un invito per un\'attività già passata.'
            ], 422);
        }

        $validated = $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        $invitation->update([
            'status'       => $validated['status'],
            'responded_at' => now(),
        ]);

        return response()->json($invitation);
    }

    // DELETE /api/activities/{activity}/invitations/{invitation}
    // Revoke an invitation. Organizer only.
    public function destroy(Request $request, Activity $activity, Invitation $invitation)
    {
        // Only the organizer can revoke invitations.
        if ($activity->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorizzato.'], 403);
        }

        $invitation->delete();

        return response()->json(['message' => 'Invito revocato.']);
    }
}