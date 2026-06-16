<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Http\Resources\ActivityResource;
use Illuminate\Support\Facades\Gate;

class ActivityController extends Controller
{
    // GET /api/activities — all my activities (created + invited to) with role field
    public function index(Request $request)
    {
        $user = $request->user();

        return ActivityResource::collection(
            Activity::where('user_id', $user->id)
            ->orWhereHas('invitations', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('invitations.user')
            ->get()
            ->map(function (Activity $activity) use ($user) {
                $activity->role = $activity->isUserOrganizer($user)
                    ? 'organizer'
                    : 'invited';
                return $activity;
            })
        );
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

        return new ActivityResource($activity);
    }

    // GET /api/activities/{id} — activity detail with invitations
    public function show(Request $request, Activity $activity)
    {
        Gate::authorize('view', $activity);

        $activity->role = $activity->isUserOrganizer($request->user()) ? 'organizer' : 'invited';

        $activity->load('invitations.user');

        return new ActivityResource($activity);
    }

    // PUT /api/activities/{id} — update an activity (organizer only)
    public function update(Request $request, Activity $activity)
    {
        Gate::authorize('update', $activity);

        $validated = $request->validate([
            'title'     => 'sometimes|string|max:255',
            'location'  => 'nullable|string|max:255',
            'starts_at' => 'sometimes|date|after:now',
            'notes'     => 'nullable|string',
        ]);

        $activity->update($validated);

        return new ActivityResource($activity);
    }

    // DELETE /api/activities/{id} — soft delete (organizer only)
    public function destroy(Request $request, Activity $activity)
    {
        Gate::authorize('delete', $activity);

        // Soft delete: sets deleted_at, activity is excluded from future queries.
        $activity->delete();

        return response()->json(['message' => 'Attività eliminata.']);
    }
}