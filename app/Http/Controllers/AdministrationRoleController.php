<?php

namespace App\Http\Controllers;

use App\Exports\AdministrationRolesExport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash;
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
     * Show the form for creating a new administrative user.
     */
    public function create()
    {
        return view('administration_roles.create', [
            'statusOptions' => [
                'active' => __('messages.status_active'),
                'inactive' => __('messages.status_inactive'),
                // 'on_leave' => __('messages.status_on_leave'),
            ],
        ]);
    }

    /**
     * Store a newly created administrative user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:manager,other'],
            'status' => ['required', 'in:active,inactive,on_leave'],
            'date_of_birth' => ['nullable', 'date'],
            'date_integration' => ['nullable', 'date'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'role' => $validated['role'],
            'status' => $validated['status'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'date_integration' => $validated['date_integration'] ?? null,
            'is_integrated' => false,
            'email_verified_at' => now(),
        ];

        if ($request->hasFile('profile_photo')) {
            $userData['profile_photo_path'] = $request->file('profile_photo')->store('profiles/users', 'uploads');
        }

        $user = User::create($userData);

        return redirect()
            ->route('administration-roles.show', $user)
            ->with('success', __('messages.user_created_successfully') ?? 'User created successfully.');
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
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        if ($user->role === 'admin') {
            abort(404);
        }

        return view('administration_roles.edit', [
            'user' => $user,
            'statusOptions' => [
                'active' => __('messages.status_active'),
                'inactive' => __('messages.status_inactive'),
                'on_leave' => __('messages.status_on_leave'),
                'terminated' => __('messages.terminated'),
            ],
        ]);
    }

    /**
     * Update the specified administrative user.
     */
    public function update(Request $request, User $user)
    {
        if ($user->role === 'admin') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:manager,other'],
            'status' => ['required', 'in:active,inactive,on_leave,terminated'],
            'date_of_birth' => ['nullable', 'date'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        $updates = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'role' => $validated['role'],
            'status' => $validated['status'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
        ];

        $removePhoto = (bool) ($validated['remove_photo'] ?? false);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('uploads')->delete($user->profile_photo_path);
            }
            $updates['profile_photo_path'] = $request->file('profile_photo')->store('profiles/users', 'uploads');
        } elseif ($removePhoto && $user->profile_photo_path) {
            Storage::disk('uploads')->delete($user->profile_photo_path);
            $updates['profile_photo_path'] = null;
        }

        $user->update($updates);

        return redirect()
            ->route('administration-roles.show', $user)
            ->with('success', __('messages.user_updated_successfully'));
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
