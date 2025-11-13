<?php

namespace App\Http\Controllers;

use App\Http\Requests\DriverConcernRequest;
use App\Models\Driver;
use App\Models\DriverConcern;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class DriverConcernController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $concernsQuery = DriverConcern::query()
                ->with(['driver'])
                ->latest('reported_at');

            if ($request->filled('status')) {
                $concernsQuery->where('status', $request->input('status'));
            }

            if ($request->filled('concern_type')) {
                $concernsQuery->where('concern_type', $request->input('concern_type'));
            }

            if ($request->filled('driver_id')) {
                $concernsQuery->where('driver_id', $request->input('driver_id'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $concernsQuery->where(function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('immediate_action', 'like', "%{$search}%")
                        ->orWhere('responsible_party', 'like', "%{$search}%")
                        ->orWhere('resolution_comments', 'like', "%{$search}%")
                        ->orWhere('vehicle_licence_plate', 'like', "%{$search}%");
                });
            }

            $concerns = $concernsQuery->paginate(12)->withQueryString();

            $totalConcerns = DriverConcern::count();
            $totalDrivers = Driver::count();
            $concernsPerDriverPercentage = $totalDrivers > 0 ? round(($totalConcerns / $totalDrivers) * 100, 1) : 0;
            
            $statusCounts = DriverConcern::select('status', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            $driverStats = DriverConcern::select('driver_id', DB::raw('COUNT(*) as total'))
                ->with('driver:id,full_name')
                ->groupBy('driver_id')
                ->orderByDesc('total')
                ->get()
                ->filter(fn ($stat) => $stat->driver !== null)
                ->map(function ($stat) use ($totalConcerns) {
                    $percentage = $totalConcerns > 0 ? round(($stat->total / $totalConcerns) * 100, 1) : 0;
                    return [
                        'driver' => $stat->driver,
                        'total' => $stat->total,
                        'percentage' => $percentage,
                    ];
                });

            return view('concerns.driver_concerns.index', [
                'concerns' => $concerns,
                'drivers' => Driver::orderBy('full_name')->pluck('full_name', 'id'),
                'concernTypes' => DriverConcern::TYPES,
                'statuses' => DriverConcern::STATUSES,
                'filters' => $request->all([
                    'status',
                    'concern_type',
                    'driver_id',
                    'search',
                ]),
                'stats' => [
                    'total' => $totalConcerns,
                    'in_progress' => $statusCounts['in_progress'] ?? 0,
                    'closed' => $statusCounts['closed'] ?? 0,
                    'concerns_per_driver_percentage' => $concernsPerDriverPercentage,
                ],
                'driverStats' => $driverStats,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('dashboard')
                ->with('error', __('messages.driver_concern_index_error'));
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            return view('concerns.driver_concerns.create', [
                'drivers' => Driver::with('assignedVehicle')->orderBy('full_name')->get(),
                'concernTypes' => DriverConcern::TYPES,
                'statuses' => DriverConcern::STATUSES,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('concerns.driver-concerns.index')
                ->with('error', __('messages.driver_concern_create_error'));
        }
    }

    public function store(DriverConcernRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['status'] = 'in_progress';
            $data['completion_date'] = null;

            DriverConcern::create($data);

            return redirect()
                ->route('concerns.driver-concerns.index')
                ->with('success', __('messages.driver_concern_created'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('concerns.driver-concerns.index')
                ->with('error', __('messages.driver_concern_store_error'));
        }
    }

    public function show(DriverConcern $driverConcern): View|RedirectResponse
    {
        try {
            $driverConcern->load(['driver']);

            return view('concerns.driver_concerns.show', [
                'concern' => $driverConcern,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('concerns.driver-concerns.index')
                ->with('error', __('messages.driver_concern_show_error'));
        }
    }

    public function edit(DriverConcern $driverConcern): View|RedirectResponse
    {
        try {
            return view('concerns.driver_concerns.edit', [
                'concern' => $driverConcern->load(['driver']),
                'drivers' => Driver::with('assignedVehicle')->orderBy('full_name')->get(),
                'concernTypes' => DriverConcern::TYPES,
                'statuses' => DriverConcern::STATUSES,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('concerns.driver-concerns.index')
                ->with('error', __('messages.driver_concern_edit_error'));
        }
    }

    public function update(DriverConcernRequest $request, DriverConcern $driverConcern): RedirectResponse
    {
        try {
            $data = $request->validated();

            // Check if completion_date is provided and validate it's not older than reported_at
            if (!empty($data['completion_date'])) {
                $completionDate = \Carbon\Carbon::parse($data['completion_date']);
                $reportedAt = $driverConcern->reported_at;

                // Explicit check: completion_date must not be older than reported_at
                if ($completionDate->lt($reportedAt)) {
                    return redirect()
                        ->route('concerns.driver-concerns.edit', $driverConcern)
                        ->withInput()
                        ->with('error', __('messages.completion_date_before_reported_date_error'));
                }

                $data['status'] = 'closed';
            }

            $driverConcern->update($data);

            return redirect()
                ->route('concerns.driver-concerns.index')
                ->with('success', __('messages.driver_concern_updated'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('concerns.driver-concerns.index')
                ->with('error', __('messages.driver_concern_update_error'));
        }
    }

    public function destroy(DriverConcern $driverConcern): RedirectResponse
    {
        try {
            $driverConcern->delete();

            return redirect()
                ->route('concerns.driver-concerns.index')
                ->with('success', __('messages.driver_concern_deleted'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('concerns.driver-concerns.index')
                ->with('error', __('messages.driver_concern_delete_error'));
        }
    }

    public function complete(Request $request, DriverConcern $driverConcern): RedirectResponse
    {
        $validated = $request->validate([
            'completion_date' => ['required', 'date', 'after_or_equal:' . $driverConcern->reported_at->format('Y-m-d')],
        ]);

        try {
            $completionDate = \Carbon\Carbon::parse($validated['completion_date']);
            $reportedAt = $driverConcern->reported_at;

            // Explicit check: completion_date must not be older than reported_at
            if ($completionDate->lt($reportedAt)) {
                return redirect()
                    ->route('concerns.driver-concerns.index')
                    ->with('error', __('messages.completion_date_before_reported_date_error'));
            }

            $driverConcern->update([
                'completion_date' => $validated['completion_date'],
                'status' => 'closed',
            ]);

            return redirect()
                ->route('concerns.driver-concerns.index')
                ->with('success', __('messages.driver_concern_completed'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('concerns.driver-concerns.index')
                ->with('error', __('messages.driver_concern_complete_error'));
        }
    }
}
