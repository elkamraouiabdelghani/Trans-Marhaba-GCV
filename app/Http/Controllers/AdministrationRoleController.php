<?php

namespace App\Http\Controllers;

use App\Exports\AdministrationRolesExport;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            'terminated' => (clone $baseQuery)->where('status', 'terminated')->count(),
        ];

        $query = clone $baseQuery;

        $status = $request->input('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        } else {
            $query->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '!=', 'terminated');
            });
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

    public function export(Request $request): BinaryFileResponse
    {
        $status = $request->input('status', 'all');
        $search = trim((string) $request->input('search', ''));
        $fileName = sprintf('administration_roles_%s.xlsx', now()->format('Ymd_His'));

        return Excel::download(new AdministrationRolesExport($status, $search), $fileName);
    }

    /**
     * Display the list of terminated administrative staff.
     */
    public function terminated(Request $request)
    {
        $query = User::query()
            ->where('role', '!=', 'admin')
            ->where('status', 'terminated');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->orderByDesc('terminated_date')
            ->get();

        return view('administration_roles.terminated', [
            'users' => $users,
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
            'terminated_cause' => 'required|string|max:500',
        ]);

        // Update user status and terminated date
        $user->update([
            'status' => 'terminated',
            'terminated_date' => $validated['terminated_date'],
            'terminated_cause' => trim($validated['terminated_cause']),
            'is_integrated' => false,
            'role' => 'other',
            'department' => 'other',
        ]);

        return redirect()->route('administration-roles.show', $user)
            ->with('success', __('messages.user_terminated_successfully'));
    }

    public function updateStatus(User $user, Request $request)
    {
        if ($user->role === 'admin') {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'required|in:active,on_leave,inactive',
        ]);

        $user->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('administration-roles.show', $user)
            ->with('success', __('messages.user_status_updated'));
    }
}
