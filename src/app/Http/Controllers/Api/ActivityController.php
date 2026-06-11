<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    // GET /api/activities — all my activities (created + invited to) with role field
    public function index(Request $request)
    {
        $user = $request->user();

        // Fetch activities where the user is the organizer OR has an invitation.
        // map() adds a computed 'role' field to each activity.
        $activities = Activity::where('user_id', $user->id)
            ->orWhereHas('invitations', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get()
            ->map(function ($activity) use ($user) {
                $activity->role = $activity->user_id === $user->id
                    ? 'organizer'
                    : 'invited';
                return $activity;
            });

        return response()->json($activities);
    }

    // POST /api/activities — create a new activity
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'starts_at' => 'required|date|after:now',
            'notes'    => 'nullable|string',
        ]);

        $activity = $request->user()->activities()->create($validated);

        return response()->json($activity, 201);
    }

    // GET /api/activities/{id} — activity detail with invitations
    public function show(Request $request, Activity $activity)
    {
        // Both the organizer and invited users can view the activity.
        $user = $request->user();
        $isOrganizer = $activity->user_id === $user->id;
        $isInvited = $activity->invitations()->where('user_id', $user->id)->exists();

        if (!$isOrganizer && !$isInvited) {
            return response()->json(['message' => 'Non autorizzato.'], 403);
        }

        $activity->load('invitations.user');
        $activity->role = $isOrganizer ? 'organizer' : 'invited';

        return response()->json($activity);
    }

    // PUT /api/activities/{id} — update an activity (organizer only)
    public function update(Request $request, Activity $activity)
    {
        if ($activity->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorizzato.'], 403);
        }

        $validated = $request->validate([
            'title'     => 'sometimes|string|max:255',
            'location'  => 'nullable|string|max:255',
            'starts_at' => 'sometimes|date|after:now',
            'notes'     => 'nullable|string',
        ]);

        $activity->update($validated);

        return response()->json($activity);
    }

    // DELETE /api/activities/{id} — soft delete (organizer only)
    public function destroy(Request $request, Activity $activity)
    {
        if ($activity->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorizzato.'], 403);
        }

        // Soft delete: sets deleted_at, activity is excluded from future queries.
        $activity->delete();

        return response()->json(['message' => 'Attività eliminata.']);
    }
}