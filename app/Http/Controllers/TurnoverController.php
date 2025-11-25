<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInterviewAnswersRequest;
use App\Http\Requests\StoreTurnoverRequest;
use App\Models\Turnover;
use App\Models\Driver;
use App\Models\User;
use App\Models\Flotte;
use App\Services\TurnoverPdfService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TurnoverController extends Controller
{
    protected TurnoverPdfService $turnoverPdfService;

    public function __construct(TurnoverPdfService $turnoverPdfService)
    {
        $this->turnoverPdfService = $turnoverPdfService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Turnover::with(['driver', 'user', 'confirmedBy']);

        // Apply filters
        if ($request->filled('flotte')) {
            $query->where('flotte', 'like', '%' . $request->flotte . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('departure_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('departure_date', '<=', $request->date_to);
        }

        $turnovers = $query->orderBy('departure_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculate stats
        $totalTurnovers = Turnover::count();
        $pendingTurnovers = Turnover::where('status', 'pending')->count();
        $confirmedTurnovers = Turnover::where('status', 'confirmed')->count();

        // Get date range for graph filter
        $graphDateFrom = $request->input('graph_date_from', date('Y-01-01', strtotime('-4 years'))); // Default: 5 years ago
        $graphDateTo = $request->input('graph_date_to', date('Y-12-31')); // Default: current year end

        // Check if both dates are in the same year
        $startYear = (int)date('Y', strtotime($graphDateFrom));
        $endYear = (int)date('Y', strtotime($graphDateTo));
        $isSameYear = $startYear === $endYear;

        // Calculate turnover percentage by year or month
        $turnoverPercentageData = $this->calculateTurnoverPercentage($graphDateFrom, $graphDateTo);

        $flottes = Flotte::orderBy('name')->get();

        return view('turnovers.index', compact(
            'turnovers', 'flottes', 'totalTurnovers', 'pendingTurnovers', 'confirmedTurnovers',
            'turnoverPercentageData', 'graphDateFrom', 'graphDateTo', 'isSameYear'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $drivers = Driver::orderBy('full_name')
            ->get()
            ->filter(function (Driver $driver) {
                $status = Str::lower((string) ($driver->status ?? ''));
                return $status !== 'terminated';
            })
            ->values();

        $users = User::orderBy('name')
            ->get()
            ->filter(function (User $user) {
                $status = Str::lower((string) ($user->status ?? ''));
                $role = Str::lower((string) ($user->role ?? ''));
                return $status !== 'terminated' && $role !== 'admin';
            })
            ->values();
        $flottes = Flotte::orderBy('name')->get();

        return view('turnovers.create', compact('drivers', 'users', 'flottes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTurnoverRequest $request)
    {
        $data = $request->validated();

        // Auto-fill position based on driver or user
        if ($request->filled('driver_id')) {
            $driver = Driver::find($request->driver_id);
            $data['position'] = 'Chauffeur';
            // Auto-fill flotte from driver if not provided
            if (empty($data['flotte']) && $driver && $driver->flotte) {
                $data['flotte'] = $driver->flotte->name;
            }
        } elseif ($request->filled('user_id')) {
            $user = User::find($request->user_id);
            $data['position'] = $user->role ?? 'Administration';
            // Auto-fill flotte to "Administration" for administration staff
            if (empty($data['flotte'])) {
                $data['flotte'] = 'Administration';
            }
        }

        $turnover = Turnover::create($data);

        return redirect()->route('turnovers.index')
            ->with('success', __('messages.turnover_created_successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Turnover $turnover)
    {
        $drivers = Driver::orderBy('full_name')->get();
        $users = User::orderBy('name')->get();
        $flottes = Flotte::orderBy('name')->get();

        return view('turnovers.edit', compact('turnover', 'drivers', 'users', 'flottes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTurnoverRequest $request, Turnover $turnover)
    {
        $data = $request->validated();

        // Auto-fill position based on driver or user
        if ($request->filled('driver_id')) {
            $data['position'] = 'Chauffeur';
            // Auto-fill flotte from driver if not provided
            if (empty($data['flotte'])) {
                $driver = Driver::find($request->driver_id);
                if ($driver && $driver->flotte) {
                    $data['flotte'] = $driver->flotte->name;
                }
            }
        } elseif ($request->filled('user_id')) {
            $user = User::find($request->user_id);
            $data['position'] = $user->role ?? 'Administration';
            // Auto-fill flotte to "Administration" for administration staff
            if (empty($data['flotte'])) {
                $data['flotte'] = 'Administration';
            }
        }

        $turnover->update($data);

        return redirect()->route('turnovers.edit', $turnover)
            ->with('success', __('messages.turnover_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Turnover $turnover)
    {
        // Prevent deleting if confirmed
        if ($turnover->isConfirmed()) {
            return redirect()->route('turnovers.index')
                ->with('error', __('messages.cannot_delete_confirmed_turnover'));
        }

        $turnover->delete();

        return redirect()->route('turnovers.index')
            ->with('success', __('messages.turnover_deleted_successfully'));
    }

    /**
     * Confirm the turnover and update driver status.
     */
    public function confirm(Turnover $turnover)
    {
        if ($turnover->isConfirmed()) {
            return redirect()->route('turnovers.index')
                ->with('error', __('messages.turnover_already_confirmed'));
        }

        try {
            $turnover->confirm();

            return redirect()->route('turnovers.index')
                ->with('success', __('messages.turnover_confirmed'));
        } catch (\Exception $e) {
            return redirect()->route('turnovers.index')
                ->with('error', __('messages.error_confirming_turnover'));
        }
    }

    /**
     * Display the exit interview form for the specified turnover.
     */
    public function showInterviewForm(Turnover $turnover): RedirectResponse|View
    {
        if (empty($turnover->interview_notes) || empty($turnover->interviewed_by)) {
            return redirect()
                ->route('turnovers.edit', $turnover)
                ->with('error', __('messages.exit_interview_prerequisites'));
        }

        $turnover->loadMissing(['driver', 'user']);

        $questions = config('turnover_interview.questions', []);
        $ratingScale = config('turnover_interview.rating_scale', [1, 2, 3, 4, 5]);

        $interviewData = $turnover->interview_answers ?? [];
        $answers = $interviewData['answers'] ?? [];
        $meta = [
            'employee_name' => $interviewData['employee_name'] ?? $turnover->person_name,
            'interview_date' => $interviewData['interview_date'] ?? now()->toDateString(),
            'employee_signature' => $interviewData['employee_signature'] ?? '',
        ];

        return view('turnovers.interview', compact(
            'turnover',
            'questions',
            'ratingScale',
            'answers',
            'meta'
        ));
    }

    /**
     * Store the exit interview answers for the specified turnover.
     */
    public function storeInterviewAnswers(StoreInterviewAnswersRequest $request, Turnover $turnover): RedirectResponse
    {
        if (empty($turnover->interview_notes) || empty($turnover->interviewed_by)) {
            return redirect()
                ->route('turnovers.edit', $turnover)
                ->with('error', __('messages.exit_interview_prerequisites'));
        }

        $validated = $request->validated();
        $questions = config('turnover_interview.questions', []);

        $answers = [];
        foreach ($questions as $question) {
            $key = $question['key'];
            $answers[$key] = $validated[$key] ?? null;
        }

        $interviewPayload = [
            'answers' => $answers,
            'employee_name' => $validated['employee_name'],
            'interview_date' => $validated['interview_date'],
            'employee_signature' => $validated['employee_signature'],
        ];

        $turnover->update([
            'interview_answers' => $interviewPayload,
        ]);

        try {
            if ($turnover->turnover_pdf_path && Storage::disk('uploads')->exists($turnover->turnover_pdf_path)) {
                Storage::disk('uploads')->delete($turnover->turnover_pdf_path);
            }

            $turnover->refresh();
            $pdfPath = $this->turnoverPdfService->generateInterviewPdf($turnover);

            $turnover->update([
                'turnover_pdf_path' => $pdfPath,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to generate exit interview PDF', [
                'turnover_id' => $turnover->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('turnovers.edit', $turnover)
                ->with('error', __('messages.exit_interview_pdf_error'));
        }

        return redirect()
            ->route('turnovers.edit', $turnover)
            ->with('success', __('messages.exit_interview_saved'));
    }

    /**
     * Download the stored exit interview PDF.
     */
    public function downloadInterviewPdf(Turnover $turnover)
    {
        if (!$turnover->turnover_pdf_path || !Storage::disk('uploads')->exists($turnover->turnover_pdf_path)) {
            return redirect()
                ->back()
                ->with('error', __('messages.exit_interview_pdf_not_found'));
        }

        $fileName = sprintf('exit-interview-%d.pdf', $turnover->id);

        return response()->download(Storage::disk('uploads')->path($turnover->turnover_pdf_path), $fileName);
    }

    /**
     * Calculate turnover percentage by year or month
     */
    private function calculateTurnoverPercentage($dateFrom, $dateTo)
    {
        $data = [];
        
        // Get years from dates
        $startYear = (int)date('Y', strtotime($dateFrom));
        $endYear = (int)date('Y', strtotime($dateTo));
        
        // Check if both dates are in the same year
        $isSameYear = $startYear === $endYear;
        
        // Get total integrated drivers and users (denominator)
        $totalIntegratedDrivers = Driver::where('is_integrated', 1)->count();
        $totalUsers = User::count();
        $totalEmployees = $totalIntegratedDrivers + $totalUsers;
        
        // If no employees, return empty data
        if ($totalEmployees == 0) {
            if ($isSameYear) {
                // Return months for the year
                $startMonth = (int)date('m', strtotime($dateFrom));
                $endMonth = (int)date('m', strtotime($dateTo));
                for ($month = $startMonth; $month <= $endMonth; $month++) {
                    $data[] = [
                        'label' => date('M', mktime(0, 0, 0, $month, 1, $startYear)),
                        'percentage' => 0
                    ];
                }
            } else {
                // Return years
                for ($year = $startYear; $year <= $endYear; $year++) {
                    $data[] = [
                        'label' => (string)$year,
                        'percentage' => 0
                    ];
                }
            }
            return $data;
        }
        
        if ($isSameYear) {
            // Calculate percentage for each month in the same year
            $startMonth = (int)date('m', strtotime($dateFrom));
            $endMonth = (int)date('m', strtotime($dateTo));
            
            for ($month = $startMonth; $month <= $endMonth; $month++) {
                $monthStart = sprintf('%04d-%02d-01', $startYear, $month);
                $daysInMonth = date('t', strtotime($monthStart));
                $monthEnd = sprintf('%04d-%02d-%02d', $startYear, $month, $daysInMonth);
                
                // Adjust for actual date range
                if ($month == $startMonth) {
                    $monthStart = $dateFrom;
                }
                if ($month == $endMonth) {
                    $monthEnd = $dateTo;
                }
                
                // Count confirmed turnovers in this month
                $confirmedTurnovers = Turnover::where('status', 'confirmed')
                    ->whereBetween('confirmed_at', [$monthStart, $monthEnd])
                    ->count();
                
                // Calculate percentage: (confirmed turnovers / total employees) × 100
                $percentage = $totalEmployees > 0 ? ($confirmedTurnovers / $totalEmployees) * 100 : 0;
                
                $data[] = [
                    'label' => date('M', mktime(0, 0, 0, $month, 1, $startYear)),
                    'percentage' => round($percentage, 2)
                ];
            }
        } else {
            // Calculate percentage for each year
            for ($year = $startYear; $year <= $endYear; $year++) {
                $yearStart = $year . '-01-01';
                $yearEnd = $year . '-12-31';
                
                // Adjust for actual date range
                if ($year == $startYear) {
                    $yearStart = $dateFrom;
                }
                if ($year == $endYear) {
                    $yearEnd = $dateTo;
                }
                
                // Count confirmed turnovers in this year
                $confirmedTurnovers = Turnover::where('status', 'confirmed')
                    ->whereBetween('confirmed_at', [$yearStart, $yearEnd])
                    ->count();
                
                // Calculate percentage: (confirmed turnovers / total employees) × 100
                $percentage = $totalEmployees > 0 ? ($confirmedTurnovers / $totalEmployees) * 100 : 0;
                
                $data[] = [
                    'label' => (string)$year,
                    'percentage' => round($percentage, 2)
                ];
            }
        }
        
        return $data;
    }
}
