<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripMember;
use App\Models\User;
use Illuminate\Http\Request;

class TripMemberController extends Controller
{
    /**
     * Ensure the user is the trip owner or an admin.
     */
    private function owner(Request $r, Trip $trip): void
    {
        abort_unless(
            $trip->owner_id === $r->user()->id || $r->user()->role === 'admin',
            403,
            'Only the trip owner can manage invitations.'
        );
    }

    /**
     * List all members of a trip.
     */
    public function index(Request $r, Trip $trip)
    {
        $u = $r->user();

        abort_unless(
            $trip->owner_id === $u->id
            || $u->role === 'admin'
            || $trip->members()->where('user_id', $u->id)->where('invite_status', 'accepted')->exists(),
            403
        );

        return $trip->members()->with('user:id,name,email')->get();
    }

    /**
     * Invite a new member to a trip.
     */
    public function store(Request $r, Trip $trip)
    {
        $this->owner($r, $trip);

        $data = $r->validate([
            'email' => 'required|email|max:190',
            'member_role' => 'sometimes|in:editor,viewer',
        ]);

        $email = strtolower($data['email']);

        if ($email === $r->user()->email) {
            return response()->json(['message' => 'You are already the trip owner.'], 422);
        }

        $user = User::where('email', $email)->first();

        $m = TripMember::updateOrCreate(
            ['trip_id' => $trip->id, 'email' => $email],
            [
                'user_id' => $user?->id,
                'member_role' => $data['member_role'] ?? 'editor',
                'invite_status' => $user ? 'accepted' : 'pending',
            ]
        );

        return response()->json($m->load('user:id,name,email'), 201);
    }

    /**
     * Update a trip member's role or membership status.
     */
    public function update(Request $r, Trip $trip, TripMember $member)
    {
        abort_unless($member->trip_id === $trip->id, 404);

        if ($r->user()->email === $member->email && $member->member_role !== 'owner') {
            $member->update(['invite_status' => 'accepted', 'user_id' => $r->user()->id]);
            return $member;
        }

        $this->owner($r, $trip);

        $data = $r->validate([
            'member_role' => 'sometimes|in:editor,viewer',
            'invite_status' => 'sometimes|in:accepted,declined',
        ]);

        $member->update($data);

        return $member->fresh()->load('user:id,name,email');
    }

    /**
     * Remove a member from a trip.
     */
    public function destroy(Request $r, Trip $trip, TripMember $member)
    {
        $this->owner($r, $trip);

        abort_unless($member->trip_id === $trip->id, 404);

        if ($member->member_role === 'owner') {
            abort(422, 'The owner cannot be removed.');
        }

        $member->delete();
        return response()->noContent();
    }
}
