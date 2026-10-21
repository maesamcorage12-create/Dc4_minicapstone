<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\ItineraryItem;
use Illuminate\Http\Request;

class ItineraryItemController extends Controller
{
    /**
     * Authorize access to a trip's itinerary items.
     * Admins can always view; editors can edit.
     */
    private function authorizeTrip(Request $r, Trip $trip, $edit = true): void
    {
        $u = $r->user();

        $ok = $trip->owner_id === $u->id
            || $trip->members()->where('user_id', $u->id)->where('invite_status', 'accepted')
                ->when($edit, fn($q) => $q->where('member_role', 'editor'))
                ->exists();

        abort_unless($ok || $u->role === 'admin', 403, 'You do not have permission to access this trip.');
    }

    /**
     * List all itinerary items for a trip.
     */
    public function index(Request $r, Trip $trip)
    {
        $this->authorizeTrip($r, $trip, false);

        return $trip->items()->get();
    }

    /**
     * Create a new itinerary item.
     */
    public function store(Request $r, Trip $trip)
    {
        $this->authorizeTrip($r, $trip);

        $data = $this->validateData($r);
        $data['sort_order'] = $trip->items()->where('day_number', $data['day_number'])->count();

        return response()->json($trip->items()->create($data), 201);
    }

    /**
     * Update an existing itinerary item.
     */
    public function update(Request $r, Trip $trip, ItineraryItem $item)
    {
        $this->authorizeTrip($r, $trip);

        abort_unless($item->trip_id === $trip->id, 404);

        $item->update($this->validateData($r));

        return $item->fresh();
    }

    /**
     * Delete an itinerary item.
     */
    public function destroy(Request $r, Trip $trip, ItineraryItem $item)
    {
        $this->authorizeTrip($r, $trip);

        abort_unless($item->trip_id === $trip->id, 404);

        $item->delete();
        return response()->noContent();
    }

    /**
     * Validate itinerary item data.
     */
    private function validateData(Request $r): array
    {
        return $r->validate([
            'day_number' => 'required|integer|min:1|max:60',
            'title' => 'required|string|max:150',
            'location' => 'nullable|string|max:180',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'cost' => 'nullable|numeric|min:0|max:10000000',
            'notes' => 'nullable|string|max:3000',
        ]);
    }
}
