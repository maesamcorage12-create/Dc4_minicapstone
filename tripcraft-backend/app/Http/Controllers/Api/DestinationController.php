<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DestinationController extends Controller
{
    /**
     * Display a paginated list of destinations.
     * Non-admins only see active destinations.
     */
    public function index(Request $request)
    {
        $q = Destination::query();
        $user = $this->getUser($request);

        if (!$user || $user->role !== 'admin') {
            $q->where('is_active', true);
        } elseif (!$request->boolean('include_inactive')) {
            $q->where('is_active', true);
        }

        if ($request->filled('search')) {
            $q->where(function ($s) use ($request) {
                $s->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $q->where('category', $request->category);
        }

        return $q->orderBy('name')->paginate(min(max((int) $request->input('per_page', 8), 1), 100));
    }

    /**
     * Display a single destination.
     * Non-admins only see active destinations.
     */
    public function show(Destination $destination)
    {
        $user = $this->getUser(request());

        if (!$destination->is_active && (!$user || $user->role !== 'admin')) {
            abort(404);
        }

        return $destination;
    }

    /**
     * Store a new destination (admin only).
     */
    public function store(Request $request)
    {
        $this->admin($request);
        $data = $this->validated($request);

        return response()->json(Destination::create($data), 201);
    }

    /**
     * Update an existing destination (admin only).
     */
    public function update(Request $request, Destination $destination)
    {
        $this->admin($request);
        $data = $this->validated($request, $destination->id);
        $destination->update($data);

        return $destination->fresh();
    }

    /**
     * Delete or deactivate a destination (admin only).
     */
    public function destroy(Request $request, Destination $destination)
    {
        $this->admin($request);

        if ($destination->trips()->exists()) {
            $destination->update(['is_active' => false]);
            return response()->json(['message' => 'Destination has trips and was deactivated instead.']);
        }

        $destination->delete();
        return response()->noContent();
    }

    /**
     * Ensure the current user is an admin.
     */
    private function admin(Request $r): void
    {
        $user = $this->getUser($r);
        abort_unless($user && $user->role === 'admin', 403, 'Administrator access required.');
    }

    /**
     * Get the authenticated user from either the injected request or Sanctum guard.
     */
    private function getUser(Request $request): ?User
    {
        return $request->user() ?? Auth::guard('sanctum')->user();
    }

    /**
     * Validate the destination data.
     */
    private function validated(Request $r, $ignore = null): array
    {
        return $r->validate([
            'name' => 'required|string|max:150',
            'location' => 'required|string|max:180',
            'category' => 'required|string|max:60',
            'description' => 'required|string|max:3000',
            'avg_daily_cost' => 'required|numeric|min:0|max:10000000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:60',
            'image_url' => 'nullable|url|max:2000',
            'is_active' => 'sometimes|boolean',
        ]);
    }
}
