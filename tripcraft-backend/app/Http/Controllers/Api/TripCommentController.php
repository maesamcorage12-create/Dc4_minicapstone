<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripComment;
use Illuminate\Http\Request;

class TripCommentController extends Controller
{
    /**
     * Check if the user can view the trip.
     */
    private function canView(Trip $t, $u): bool
    {
        return $t->owner_id === $u->id
            || $t->members()->where('user_id', $u->id)->where('invite_status', 'accepted')->exists();
    }

    /**
     * List all comments on a trip.
     */
    public function index(Request $r, Trip $trip)
    {
        abort_unless(
            $this->canView($trip, $r->user()) || $r->user()->role === 'admin',
            403
        );

        return $trip->comments()->with('user:id,name')->get();
    }

    /**
     * Create a new comment on a trip.
     */
    public function store(Request $r, Trip $trip)
    {
        abort_unless(
            $this->canView($trip, $r->user()) || $r->user()->role === 'admin',
            403
        );

        $data = $r->validate([
            'comment' => 'required|string|max:3000',
            'item_id' => 'nullable|exists:itinerary_items,id',
        ]);

        if (isset($data['item_id'])) {
            abort_unless(
                $trip->items()->whereKey($data['item_id'])->exists(),
                422,
                'Activity does not belong to this trip.'
            );
        }

        $c = $trip->comments()->create([
            'user_id' => $r->user()->id,
            'comment' => $data['comment'],
            'item_id' => $data['item_id'] ?? null,
        ]);

        return response()->json($c->load('user:id,name'), 201);
    }
}
