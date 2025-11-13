<?php

namespace App\Http\Controllers;

use App\Http\Requests\DriverConcernRequest;
use App\Models\ConcernType;
use App\Models\Driver;
use App\Models\DriverConcern;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DriverConcernController extends Controller
{
    public function index(Request $request): View
    {
        $concernsQuery = DriverConcern::query()
            ->with(['driver', 'concernType'])
            ->latest('reported_at');

        if ($request->filled('status')) {
            $concernsQuery->where('status', $request->input('status'));
        }

        if ($request->filled('concern_type_id')) {
            $concernsQuery->where('concern_type_id', $request->input('concern_type_id'));
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

        return view('driver_concerns.index', [
            'concerns' => $concerns,
            'drivers' => Driver::orderBy('full_name')->pluck('full_name', 'id'),
            'concernTypes' => ConcernType::orderBy('name')->pluck('name', 'id'),
            'statuses' => DriverConcern::STATUSES,
            'filters' => $request->all([
                'status',
                'concern_type_id',
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
    }

    public function create(): View
    {
        return view('driver_concerns.create', [
            'drivers' => Driver::with('assignedVehicle')->orderBy('full_name')->get(),
            'concernTypes' => ConcernType::orderBy('name')->pluck('name', 'id'),
            'statuses' => DriverConcern::STATUSES,
        ]);
    }

    public function store(DriverConcernRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = 'in_progress';
        $data['completion_date'] = null;

        DriverConcern::create($data);

        return redirect()
            ->route('driver-concerns.index')
            ->with('success', __('messages.driver_concern_created'));
    }

    public function show(DriverConcern $driverConcern): View
    {
        $driverConcern->load(['driver', 'concernType']);

        return view('driver_concerns.show', [
            'concern' => $driverConcern,
        ]);
    }

    public function edit(DriverConcern $driverConcern): View
    {
        return view('driver_concerns.edit', [
            'concern' => $driverConcern->load(['driver', 'concernType']),
            'drivers' => Driver::with('assignedVehicle')->orderBy('full_name')->get(),
            'concernTypes' => ConcernType::orderBy('name')->pluck('name', 'id'),
            'statuses' => DriverConcern::STATUSES,
        ]);
    }

    public function update(DriverConcernRequest $request, DriverConcern $driverConcern): RedirectResponse
    {
        $data = $request->validated();

        if (!empty($data['completion_date'])) {
            $data['status'] = 'closed';
        }

        $driverConcern->update($data);

        return redirect()
            ->route('driver-concerns.index')
            ->with('success', __('messages.driver_concern_updated'));
    }

    public function destroy(DriverConcern $driverConcern): RedirectResponse
    {
        $driverConcern->delete();

        return redirect()
            ->route('driver-concerns.index')
            ->with('success', __('messages.driver_concern_deleted'));
    }

    public function complete(Request $request, DriverConcern $driverConcern): RedirectResponse
    {
        $validated = $request->validate([
            'completion_date' => ['required', 'date', 'after_or_equal:' . $driverConcern->reported_at->format('Y-m-d')],
        ]);

        $driverConcern->update([
            'completion_date' => $validated['completion_date'],
            'status' => 'closed',
        ]);

        return redirect()
            ->route('driver-concerns.index')
            ->with('success', __('messages.driver_concern_completed'));
    }
}
