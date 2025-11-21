<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdministrationRoleController extends Controller
{
    /**
     * Display a listing of non-admin (integrated) users.
     */
    public function index(Request $request)
    {
        $baseQuery = User::query()->where('role', '!=', 'admin');

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'on_leave' => (clone $baseQuery)->where('status', 'on_leave')->count(),
            'integrated' => (clone $baseQuery)->where('is_integrated', true)->count(),
        ];

        $query = clone $baseQuery;

        $status = $request->input('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('administration_roles.index', [
            'users' => $users,
            'stats' => $stats,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Display the specified administrative user.
     */
    public function show(User $user)
    {
        // Ensure user is not an admin
        if ($user->role === 'admin') {
            abort(404);
        }

        // Load related data with their relationships
        $user->load([
            'turnovers' => function($query) {
                $query->orderBy('departure_date', 'desc');
            },
            'changements' => function($query) {
                $query->with('changementType')
                      ->orderBy('date_changement', 'desc');
            }
        ]);

        return view('administration_roles.show', [
            'user' => $user,
        ]);
    }

    /**
     * Mark the administrative user as terminated.
     */
    public function terminate(User $user, Request $request)
    {
        // Ensure user is not an admin
        if ($user->role === 'admin') {
            abort(404);
        }

        // Validate the request
        $validated = $request->validate([
            'terminated_date' => 'required|date',
        ]);

        // Update user status and terminated date
        $user->update([
            'status' => 'terminated',
            'terminated_date' => $validated['terminated_date'],
            'is_integrated' => false,
            'role' => 'other',
            'department' => 'other',
        ]);

        return redirect()->route('administration-roles.show', $user)
            ->with('success', __('messages.user_terminated_successfully'));
    }
}
