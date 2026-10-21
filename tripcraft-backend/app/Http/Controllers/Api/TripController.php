<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripMember;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TripController extends Controller
{
    /**
     * Determine if the user can view the trip.
     */
    private function canView(Trip $trip, $user): bool
    {
        return $trip->owner_id === $user->id
            || $trip->members()->where('user_id', $user->id)->where('invite_status', 'accepted')->exists();
    }

    /**
     * Determine if the user can edit the trip.
     */
    private function canEdit(Trip $trip, $user): bool
    {
        return $trip->owner_id === $user->id
            || $trip->members()->where('user_id', $user->id)
                ->where('invite_status', 'accepted')
                ->where('member_role', 'editor')
                ->exists();
    }

    /**
     * Display a paginated list of trips.
     * Admins see all trips; travelers see only their own approved trips.
     */
    public function index(Request $request)
    {
        $u = $request->user();

        $q = Trip::with(['destination', 'owner:id,name,email', 'approver:id,name'])
                 ->withCount(['members as members_count' => function ($m) {
                     $m->where('invite_status', 'accepted');
                 }]);

        if ($u->role !== 'admin') {
            $q->where(function ($s) use ($u) {
                $s->where('owner_id', $u->id)
                  ->orWhereHas('members', function ($m) use ($u) {
                      $m->where('user_id', $u->id)->where('invite_status', 'accepted');
                  });
            })
            ->where('status', '!=', 'pending_approval')
            ->latest();

            return $q->paginate(12);
        }

        return $q->latest()->paginate(12);
    }

    /**
     * Store a new trip.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'destination_id' => 'required|exists:destinations,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'budget' => 'required|numeric|min:0|max:10000000',
            'preferences' => 'nullable|array',
            'preferences.*' => 'string|max:60',
            'status' => ['sometimes', Rule::in(['draft', 'planning', 'confirmed', 'completed'])],
        ]);

        $d = Destination::where('is_active', true)->findOrFail($data['destination_id']);
        $data['owner_id'] = $request->user()->id;

        if ($request->user()->role === 'admin') {
            $data['status'] = $data['status'] ?? 'draft';
        } else {
            $data['status'] = 'pending_approval';
        }

        $trip = Trip::create($data);
        TripMember::create([
            'trip_id' => $trip->id,
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'member_role' => 'owner',
            'invite_status' => 'accepted',
        ]);

        return response()->json($trip->load('destination'), 201);
    }

    /**
     * Display a single trip.
     * Admins can view any trip; others must be the owner or a member.
     * Non-admin users cannot view trips that are pending approval or rejected.
     */
    public function show(Request $request, Trip $trip)
    {
        if ($request->user()->role !== 'admin') {
            abort_unless(
                $this->canView($trip, $request->user()) && $trip->status !== 'pending_approval',
                403,
                'This trip is awaiting admin approval or has been rejected.'
            );
        }

        return $trip->load('destination', 'owner:id,name,email')
                    ->loadCount(['members as members_count' => function ($m) {
                        $m->where('invite_status', 'accepted');
                    }]);
    }

    /**
     * Update an existing trip.
     * Admins can edit any trip; others must be the owner or an editor.
     */
    public function update(Request $request, Trip $trip)
    {
        abort_unless(
            $this->canEdit($trip, $request->user()) || $request->user()->role === 'admin',
            403,
            'You cannot edit this trip.'
        );

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:120',
            'destination_id' => 'sometimes|required|exists:destinations,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'budget' => 'sometimes|required|numeric|min:0|max:10000000',
            'preferences' => 'sometimes|nullable|array',
            'preferences.*' => 'string|max:60',
            'status' => ['sometimes', Rule::in(['draft', 'planning', 'confirmed', 'completed'])],
        ]);

        $trip->update($data);

        return $trip->fresh()->load('destination');
    }

    /**
     * Delete a trip.
     * Only the owner or an admin can delete.
     */
    public function destroy(Request $request, Trip $trip)
    {
        abort_unless(
            $trip->owner_id === $request->user()->id || $request->user()->role === 'admin',
            403,
            'Only the trip owner can delete this trip.'
        );

        $trip->delete();
        return response()->noContent();
    }

    /**
     * Generate an itinerary for a trip.
     * Admins can generate for any trip; others must be able to edit.
     */
    public function generate(Request $request, Trip $trip)
    {
        abort_unless(
            $this->canEdit($trip, $request->user()) || $request->user()->role === 'admin',
            403,
            'You cannot edit this trip.'
        );

        $trip->items()->delete();
        $days = max(1, min(60, $trip->start_date->diffInDays($trip->end_date) + 1));
        $prefs = array_map('strtolower', $trip->preferences ?? []);
        $dests = Destination::where('is_active', true)->get()->sortByDesc(function ($d) use ($prefs) {
            return count(array_intersect($prefs, array_map('strtolower', array_merge([$d->category], $d->tags ?? []))));
        })->values();

        $seed = [$trip->destination];
        foreach ($dests as $d) {
            if ($d->id !== $trip->destination_id) {
                $seed[] = $d;
            }
        }

        $sort = 0;
        for ($day = 1; $day <= $days; $day++) {
            for ($slot = 0; $slot < min(3, count($seed)); $slot++) {
                $d = $seed[(($day - 1) * 2 + $slot) % count($seed)];
                $trip->items()->create([
                    'day_number' => $day,
                    'title' => $slot === 0 ? 'Explore ' . $d->name : ($slot === 1 ? 'Visit ' . $d->name : 'Enjoy ' . $d->name),
                    'location' => $d->location,
                    'start_time' => ['09:00:00', '11:30:00', '14:30:00'][$slot],
                    'end_time' => ['10:30:00', '13:00:00', '16:00:00'][$slot],
                    'cost' => round((float) $d->avg_daily_cost / 2, 2),
                    'notes' => 'Suggested stop — adjust timing and cost to fit your group. Verify local access and opening details before traveling.',
                    'sort_order' => $sort++,
                ]);
            }
        }

        $trip->update(['status' => 'planning']);
        return response()->json([
            'message' => 'Itinerary generated. You can edit or remove any activity.',
            'items' => $trip->items()->get(),
        ]);
    }
}
