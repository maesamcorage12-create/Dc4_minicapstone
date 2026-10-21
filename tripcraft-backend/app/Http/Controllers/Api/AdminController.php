<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trip;
use App\Models\Destination;
use App\Models\TripComment;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Ensure the current user is an admin.
     */
    private function check(Request $r): void
    {
        abort_unless(
            $r->user() && $r->user()->role === 'admin',
            403,
            'Administrator access required.'
        );
    }

    /**
     * Get admin dashboard stats.
     */
    public function dashboard(Request $r)
    {
        $this->check($r);

        return response()->json([
            'users' => User::count(),
            'trips' => Trip::count(),
            'destinations' => Destination::where('is_active', true)->count(),
            'comments' => TripComment::count(),
            'pending_trips' => Trip::where('status', 'pending_approval')->count(),
            'recent_trips' => Trip::with(['owner:id,name', 'destination:id,name'])
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }

    /**
     * List all registered users.
     */
    public function users(Request $r)
    {
        $this->check($r);

        return User::select('id', 'name', 'email', 'role', 'created_at')
            ->latest()
            ->paginate(50);
    }

    /**
         * Update a user's role.
         */
        public function updateUser(Request $r, User $user)
        {
            $this->check($r);

            $data = $r->validate([
                'role' => 'required|in:admin,traveler',
            ]);

            if ($user->id === $r->user()->id && $data['role'] !== 'admin') {
                return response()->json(['message' => 'You cannot remove your own admin role.'], 422);
            }

            $user->update($data);

            return $user->only('id', 'name', 'email', 'role');
        }

        /**
         * List trips pending approval.
         */
        public function pendingTrips(Request $r)
        {
            $this->check($r);

            $q = Trip::with(['destination', 'owner:id,name,email'])
                     ->where('status', 'pending_approval')
                     ->latest();

            if ($r->filled('search')) {
                $q->where(function ($s) use ($r) {
                    $s->where('title', 'like', '%' . $r->input('search') . '%')
                      ->orWhereHas('owner', function ($o) use ($r) {
                          $o->where('name', 'like', '%' . $r->input('search') . '%')
                            ->orWhere('email', 'like', '%' . $r->input('search') . '%');
                      });
                });
            }

            return $q->paginate(12);
        }

        /**
         * Approve a pending trip.
         */
        public function approveTrip(Request $r, Trip $trip)
        {
            $this->check($r);

            if ($trip->status !== 'pending_approval') {
                return response()->json(['message' => 'This trip is not pending approval.'], 422);
            }

            $trip->update([
                'status' => 'draft',
                'approved_by' => $r->user()->id,
                'approved_at' => now(),
            ]);

            return response()->json([
                'message' => 'Trip approved successfully.',
                'trip' => $trip->load('destination', 'owner:id,name'),
            ]);
        }

        /**
         * Reject a pending trip.
         */
        public function rejectTrip(Request $r, Trip $trip)
        {
            $this->check($r);

            if ($trip->status !== 'pending_approval') {
                return response()->json(['message' => 'This trip is not pending approval.'], 422);
            }

            $data = $r->validate([
                'note' => 'nullable|string|max:500',
            ]);

            $trip->update([
                'status' => 'rejected',
                'approved_by' => $r->user()->id,
                'approved_at' => now(),
                'approval_note' => $data['note'] ?? null,
            ]);

            return response()->json([
                'message' => 'Trip rejected.',
                'trip' => $trip->load('destination', 'owner:id,name'),
            ]);
        }
}
