<?php

namespace App\Http\Controllers;

use App\Http\Requests\DriverViolationRequest;
use App\Models\Driver;
use App\Models\DriverViolation;
use App\Models\ViolationType;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class DriverViolationController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $violationsQuery = DriverViolation::query()
                ->with(['driver', 'violationType', 'vehicle'])
                ->orderByDesc('violation_date')
                ->orderByDesc('id'); // ensure stable ordering when dates are identical

            // Filters
            if ($request->filled('status')) {
                $violationsQuery->where('status', $request->input('status'));
            }

            if ($request->filled('violation_type_id')) {
                $violationsQuery->where('violation_type_id', $request->input('violation_type_id'));
            }

            if ($request->filled('driver_id')) {
                $violationsQuery->where('driver_id', $request->input('driver_id'));
            }

            if ($request->filled('date_from')) {
                $violationsQuery->where('violation_date', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $violationsQuery->where('violation_date', '<=', $request->input('date_to'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $violationsQuery->where(function ($query) use ($search) {
                    $query->where('location', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('driver', function ($q) use ($search) {
                            $q->where('full_name', 'like', "%{$search}%");
                        });
                });
            }

            $violations = $violationsQuery->paginate(15)->withQueryString();

            // Statistics
            $totalViolations = DriverViolation::count();
            $statusCounts = DriverViolation::select('status', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            return view('violations.index', [
                'violations' => $violations,
                'drivers' => Driver::orderBy('full_name')->pluck('full_name', 'id'),
                'violationTypes' => ViolationType::active()->orderBy('name')->pluck('name', 'id'),
                'statuses' => [
                    'pending' => __('messages.pending'),
                    'confirmed' => __('messages.confirmed'),
                    'rejected' => __('messages.rejected'),
                ],
                'filters' => $request->all([
                    'status',
                    'violation_type_id',
                    'driver_id',
                    'date_from',
                    'date_to',
                    'search',
                ]),
                'stats' => [
                    'total' => $totalViolations,
                    'pending' => $statusCounts['pending'] ?? 0,
                    'confirmed' => $statusCounts['confirmed'] ?? 0,
                    'rejected' => $statusCounts['rejected'] ?? 0,
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('dashboard')
                ->with('error', __('messages.violations_index_error'));
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            // Get drivers with their assigned vehicles - handle errors gracefully
            $drivers = collect();
            try {
                $drivers = Driver::active()
                    ->with('assignedVehicle')
                    ->orderBy('full_name')
                    ->get();
            } catch (\Exception $e) {
                Log::warning('Could not load drivers for violation form', ['error' => $e->getMessage()]);
                // Try without the active scope
                try {
                    $drivers = Driver::with('assignedVehicle')
                        ->orderBy('full_name')
                        ->get();
                } catch (\Exception $e2) {
                    Log::error('Could not load drivers even without active scope', ['error' => $e2->getMessage()]);
                }
            }

            // Get active violation types
            $violationTypes = collect();
            try {
                $violationTypes = ViolationType::active()
                    ->orderBy('name')
                    ->get();
            } catch (\Exception $e) {
                Log::warning('Could not load violation types for violation form', ['error' => $e->getMessage()]);
                // Try without the active scope
                try {
                    $violationTypes = ViolationType::orderBy('name')->get();
                } catch (\Exception $e2) {
                    Log::error('Could not load violation types even without active scope', ['error' => $e2->getMessage()]);
                }
            }

            // Get vehicles - handle potential database issues
            $vehicles = collect();
            try {
                // Check if vehicles table exists
                if (Schema::hasTable('vehicles')) {
                    $vehiclesQuery = Vehicle::query();
                    // Check if license_plate column exists before ordering
                    if (Schema::hasColumn('vehicles', 'license_plate')) {
                        $vehiclesQuery = $vehiclesQuery->orderBy('license_plate');
                    }
                    $vehicles = $vehiclesQuery->get();
                }
            } catch (\Exception $e) {
                // If vehicles table doesn't exist or has issues, use empty collection
                Log::warning('Could not load vehicles for violation form', ['error' => $e->getMessage()]);
            }

            return view('violations.create', [
                'drivers' => $drivers,
                'violationTypes' => $violationTypes,
                'vehicles' => $vehicles,
            ]);
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            Log::error('Violation create form database error', [
                'message' => $exception->getMessage(),
                'sql' => $exception->getSql(),
                'bindings' => $exception->getBindings(),
            ]);

            return redirect()
                ->route('violations.index')
                ->with('error', __('messages.violation_create_form_error') . ': ' . $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);
            Log::error('Violation create form error', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);

            return redirect()
                ->route('violations.index')
                ->with('error', __('messages.violation_create_form_error') . ': ' . $exception->getMessage());
        }
    }

    public function store(DriverViolationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            // Handle document upload (multi-file input, store first file for now)
            if ($request->hasFile('document')) {
                $files = $request->file('document');
                $file = is_array($files) ? reset($files) : $files;
                if ($file) {
                    $documentPath = $file->store('violations/documents', 'uploads');
                    $validated['document_path'] = $documentPath;
                }
            }

            // Set default status if not provided
            $validated['status'] = $validated['status'] ?? 'pending';
            $validated['created_by'] = Auth::id();

            // Handle evidence upload for inline action-plan fields
            if ($request->hasFile('evidence')) {
                $file = $request->file('evidence');
                $path = $file->store('violations/evidence', 'uploads');
                $validated['evidence_path'] = $path;
                $validated['evidence_original_name'] = $file->getClientOriginalName();
            }

            unset($validated['document'], $validated['evidence']);

            $driverViolation = DriverViolation::create($validated);

            return redirect()
                ->route('violations.index')
                ->with('success', __('messages.violation_created'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('violations.index')
                ->with('error', __('messages.violation_store_error'));
        }
    }

    public function show(DriverViolation $driverViolation): View|RedirectResponse
    {
        try {
            $driverViolation->load(['driver', 'violationType', 'vehicle', 'createdBy']);

            return view('violations.show', [
                'violation' => $driverViolation,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('violations.index')
                ->with('error', __('messages.violation_show_error'));
        }
    }

    /**
     * Download a PDF report for a single violation.
     */
    public function downloadReport(DriverViolation $driverViolation)
    {
        try {
            $driverViolation->load(['driver', 'violationType', 'vehicle', 'createdBy']);

            $driverName = $driverViolation->driver?->full_name ?? 'driver';
            $fileName = sprintf(
                'violation-%d-%s-%s.pdf',
                $driverViolation->id,
                Str::slug($driverName),
                now()->format('Ymd_His')
            );

            $pdf = Pdf::loadView('violations.report', [
                'violation' => $driverViolation,
            ])->setPaper('a4');

            return $pdf->download($fileName);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', __('messages.violation_report_error'));
        }
    }

    public function edit(DriverViolation $driverViolation): View|RedirectResponse
    {
        try {
            $drivers = collect();
            try {
                $drivers = Driver::active()->with('assignedVehicle')->orderBy('full_name')->get();
            } catch (\Exception $e) {
                Log::warning('Could not load active drivers for violation edit', ['error' => $e->getMessage()]);
                try {
                    $drivers = Driver::with('assignedVehicle')->orderBy('full_name')->get();
                } catch (\Exception $fallbackException) {
                    Log::error('Could not load drivers for violation edit', ['error' => $fallbackException->getMessage()]);
                }
            }

            $violationTypes = collect();
            try {
                $violationTypes = ViolationType::active()->orderBy('name')->get();
            } catch (\Exception $e) {
                Log::warning('Could not load active violation types for violation edit', ['error' => $e->getMessage()]);
                try {
                    $violationTypes = ViolationType::orderBy('name')->get();
                } catch (\Exception $fallbackException) {
                    Log::error('Could not load violation types for violation edit', ['error' => $fallbackException->getMessage()]);
                }
            }

            $vehicles = collect();
            try {
                if (Schema::hasTable('vehicles')) {
                    $vehiclesQuery = Vehicle::query();
                    if (Schema::hasColumn('vehicles', 'license_plate')) {
                        $vehiclesQuery->orderBy('license_plate');
                    }
                    $vehicles = $vehiclesQuery->get();
                }
            } catch (\Exception $e) {
                Log::warning('Could not load vehicles for violation edit', ['error' => $e->getMessage()]);
            }

            return view('violations.edit', [
                'violation' => $driverViolation->load(['driver', 'violationType', 'vehicle']),
                'drivers' => $drivers,
                'violationTypes' => $violationTypes,
                'vehicles' => $vehicles,
            ]);
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            Log::error('Violation edit form database error', [
                'message' => $exception->getMessage(),
                'sql' => $exception->getSql(),
                'bindings' => $exception->getBindings(),
            ]);

            return redirect()
                ->route('violations.index')
                ->with('error', __('messages.violation_edit_form_error') . ': ' . $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('violations.index')
                ->with('error', __('messages.violation_edit_form_error'));
        }
    }

    public function update(DriverViolationRequest $request, DriverViolation $driverViolation): RedirectResponse
    {
        $validated = $request->validated();

        try {
            // Handle document upload (multi-file input, store first file)
            if ($request->hasFile('document')) {
                $files = $request->file('document');
                $file = is_array($files) ? reset($files) : $files;
                if ($file) {
                    if ($driverViolation->document_path && Storage::disk('uploads')->exists($driverViolation->document_path)) {
                        Storage::disk('uploads')->delete($driverViolation->document_path);
                    }
                    $documentPath = $file->store('violations/documents', 'uploads');
                    $validated['document_path'] = $documentPath;
                }
            }

            // Handle evidence upload/update
            if ($request->hasFile('evidence')) {
                if ($driverViolation->evidence_path && Storage::disk('uploads')->exists($driverViolation->evidence_path)) {
                    Storage::disk('uploads')->delete($driverViolation->evidence_path);
                }

                $file = $request->file('evidence');
                $path = $file->store('violations/evidence', 'uploads');
                $validated['evidence_path'] = $path;
                $validated['evidence_original_name'] = $file->getClientOriginalName();
            }

            unset($validated['document'], $validated['evidence']);

            $driverViolation->update($validated);

            return redirect()
                ->route('violations.index')
                ->with('success', __('messages.violation_updated'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('violations.index')
                ->with('error', __('messages.violation_update_error'));
        }
    }

    public function destroy(DriverViolation $driverViolation): RedirectResponse
    {
        try {
            // Delete document if exists
            if ($driverViolation->document_path && Storage::disk('uploads')->exists($driverViolation->document_path)) {
                Storage::disk('uploads')->delete($driverViolation->document_path);
            }

            if ($driverViolation->evidence_path && Storage::disk('uploads')->exists($driverViolation->evidence_path)) {
                Storage::disk('uploads')->delete($driverViolation->evidence_path);
            }

            $driverViolation->delete();

            return redirect()
                ->route('violations.index')
                ->with('success', __('messages.violation_deleted'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('violations.index')
                ->with('error', __('messages.violation_delete_error'));
        }
    }

    public function markAsConfirmed(Request $request, DriverViolation $driverViolation): RedirectResponse
    {
        try {
            $driverViolation->markAsConfirmed();

            return redirect()
                ->route('violations.show', $driverViolation)
                ->with('success', __('messages.violation_marked_as_confirmed'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('violations.show', $driverViolation)
                ->with('error', __('messages.violation_mark_confirmed_error'));
        }
    }

    public function markAsRejected(Request $request, DriverViolation $driverViolation): RedirectResponse
    {
        try {
            $driverViolation->markAsRejected();

            return redirect()
                ->route('violations.show', $driverViolation)
                ->with('success', __('messages.violation_marked_as_rejected'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('violations.show', $driverViolation)
                ->with('error', __('messages.violation_mark_rejected_error'));
        }
    }

    public function updateStatus(Request $request, DriverViolation $driverViolation): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,rejected,confirmed'],
        ]);

        try {
            $driverViolation->update(['status' => $validated['status']]);

            $statusMessages = [
                'pending' => __('messages.violation_status_changed_to_pending'),
                'confirmed' => __('messages.violation_status_changed_to_confirmed'),
                'rejected' => __('messages.violation_status_changed_to_rejected'),
            ];

            return redirect()
                ->route('violations.index')
                ->with('success', $statusMessages[$validated['status']] ?? __('messages.violation_status_updated'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('violations.index')
                ->with('error', __('messages.violation_status_update_error'));
        }
    }

    public function downloadDocument(DriverViolation $driverViolation)
    {
        try {
            if (!$driverViolation->document_path || !Storage::disk('uploads')->exists($driverViolation->document_path)) {
                return redirect()
                    ->route('violations.show', $driverViolation)
                    ->with('error', __('messages.violation_document_not_found'));
            }

            $fileName = sprintf('violation-%d-%s.%s', 
                $driverViolation->id, 
                now()->format('YmdHis'),
                pathinfo($driverViolation->document_path, PATHINFO_EXTENSION)
            );

            $filePath = Storage::disk('uploads')->path($driverViolation->document_path);
            return response()->download($filePath, $fileName);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('violations.show', $driverViolation)
                ->with('error', __('messages.violation_document_download_error'));
        }
    }

    public function downloadActionEvidence(DriverViolation $driverViolation)
    {
        try {
            if (!$driverViolation->evidence_path || !Storage::disk('uploads')->exists($driverViolation->evidence_path)) {
                return redirect()
                    ->route('violations.show', $driverViolation)
                    ->with('error', __('messages.violation_evidence_not_found'));
            }

            $downloadName = $driverViolation->evidence_original_name
                ? $driverViolation->evidence_original_name
                : sprintf('violation-action-%d-%s.%s',
                    $driverViolation->id,
                    now()->format('YmdHis'),
                    pathinfo($driverViolation->evidence_path, PATHINFO_EXTENSION)
                );

            return response()->download(Storage::disk('uploads')->path($driverViolation->evidence_path), $downloadName);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('violations.show', $driverViolation)
                ->with('error', __('messages.violation_evidence_download_error'));
        }
    }

}
