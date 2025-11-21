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
}
