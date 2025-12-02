<?php

namespace App\Http\Controllers;

use App\Exports\RestPointsExport;
use App\Http\Requests\RestPointRequest;
use App\Models\RestPoint;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class RestPointController extends Controller
{
    /**
     * Display a listing of rest points with map
     */
    public function index(Request $request): View|RedirectResponse
    {
        try {
            // Check if table exists, if not return empty results
            if (!Schema::hasTable('rest_points')) {
                return view('rest-points.index', [
                    'restPoints' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                    'allRestPoints' => collect([]),
                    'types' => [
                        'area' => __('messages.area') ?? 'Area',
                        'station' => __('messages.station') ?? 'Station',
                        'parking' => __('messages.parking') ?? 'Parking',
                        'other' => __('messages.other') ?? 'Other',
                    ],
                    'filters' => $request->only(['type', 'search']),
                ]);
            }

            $restPointsQuery = RestPoint::query()
                ->orderByDesc('created_at');

            // Filter by type
            if ($request->filled('type')) {
                $restPointsQuery->where('type', $request->input('type'));
            }

            // Search filter
            if ($request->filled('search')) {
                $search = $request->input('search');
                $restPointsQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $restPoints = $restPointsQuery->paginate(15)->withQueryString();

            // Get filtered rest points for map (without pagination, but with same filters)
            $allRestPoints = (clone $restPointsQuery)->get();

            return view('rest-points.index', [
                'restPoints' => $restPoints,
                'allRestPoints' => $allRestPoints,
                'types' => [
                    'area' => __('messages.area') ?? 'Area',
                    'station' => __('messages.station') ?? 'Station',
                    'parking' => __('messages.parking') ?? 'Parking',
                    'other' => __('messages.other') ?? 'Other',
                ],
                'filters' => $request->only(['type', 'search']),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load rest points index', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            // Instead of redirecting, show the page with empty data
            return view('rest-points.index', [
                'restPoints' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'allRestPoints' => collect([]),
                'types' => [
                    'area' => __('messages.area') ?? 'Area',
                    'station' => __('messages.station') ?? 'Station',
                    'parking' => __('messages.parking') ?? 'Parking',
                    'other' => __('messages.other') ?? 'Other',
                ],
                'filters' => $request->only(['type', 'search']),
            ])->with('error', __('messages.error_loading_rest_points') ?? 'Error loading rest points. Please run the migration: php artisan migrate');
        }
    }


    /**
     * Store a newly created rest point
     */
    public function store(RestPointRequest $request): RedirectResponse
    {
        try {
            // Check if table exists
            if (!Schema::hasTable('rest_points')) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.error_creating_rest_point') ?? 'Error creating rest point. Please run the migration: php artisan migrate');
            }

            // Validate coordinates are set
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            
            if (empty($latitude) || empty($longitude)) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.location_required') ?? 'Please select a location on the map before submitting.');
            }

            $restPoint = RestPoint::create([
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
                'description' => $request->input('description'),
                'created_by' => Auth::id(),
            ]);

            return redirect()
                ->route('rest-points.index')
                ->with('success', __('messages.rest_point_created') ?? 'Rest point created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions so they're handled properly
            throw $e;
        } catch (Throwable $exception) {
            Log::error('Failed to create rest point', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'data' => $request->except(['_token']),
            ]);
            
            $errorMessage = __('messages.error_creating_rest_point') ?? 'Error creating rest point.';
            
            // Add more specific error message for debugging
            if (config('app.debug')) {
                $errorMessage .= ' ' . $exception->getMessage();
            }
            
            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }


    /**
     * Update the specified rest point
     */
    public function update(RestPointRequest $request, RestPoint $restPoint): RedirectResponse
    {
        try {
            $restPoint->update([
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'description' => $request->input('description'),
            ]);

            return redirect()
                ->route('rest-points.index')
                ->with('success', __('messages.rest_point_updated') ?? 'Rest point updated successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to update rest point', [
                'error' => $exception->getMessage(),
                'rest_point_id' => $restPoint->id,
                'data' => $request->except(['_token', '_method']),
            ]);
            return back()
                ->withInput()
                ->with('error', __('messages.error_updating_rest_point') ?? 'Error updating rest point.');
        }
    }

    /**
     * Export rest points map to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $restPointsQuery = RestPoint::query()
                ->orderByDesc('created_at');

            // Apply same filters as index
            if ($request->filled('type')) {
                $restPointsQuery->where('type', $request->input('type'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $restPointsQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $restPoints = $restPointsQuery->get();

            if ($restPoints->isEmpty()) {
                return back()->with('error', __('messages.no_rest_points_found') ?? 'No rest points found to export.');
            }

            // Get map image from request (captured by html2canvas)
            $mapImageBase64 = $request->input('map_image');
            
            // If no map image provided, try to generate static map URL as fallback
            $staticMapUrl = null;
            if (empty($mapImageBase64)) {
                $staticMapUrl = $this->generateStaticMapUrl($restPoints);
                
                if ($staticMapUrl) {
                    try {
                        $response = Http::timeout(10)->get($staticMapUrl);
                        if ($response->successful()) {
                            $imageContent = $response->body();
                            if (!empty($imageContent)) {
                                $mapImageBase64 = 'data:image/png;base64,' . base64_encode($imageContent);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to download map image for PDF', [
                            'url' => $staticMapUrl,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $pdf = Pdf::loadView('rest-points.pdf.map', [
                'restPoints' => $restPoints,
                'staticMapUrl' => $staticMapUrl,
                'mapImageBase64' => $mapImageBase64,
            ])
                ->setPaper('a4', 'landscape')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            $fileName = 'rest-points-map-' . now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (Throwable $exception) {
            Log::error('Failed to export rest points PDF', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            
            return back()->with('error', __('messages.error_exporting_pdf') ?? 'Error exporting PDF.');
        }
    }

    /**
     * Generate static map URL with markers for all rest points
     */
    private function generateStaticMapUrl($restPoints): ?string
    {
        if ($restPoints->isEmpty()) {
            return null;
        }

        $lats = $restPoints->pluck('latitude')->filter();
        $lngs = $restPoints->pluck('longitude')->filter();

        if ($lats->isEmpty() || $lngs->isEmpty()) {
            return null;
        }

        $centerLat = $lats->avg();
        $centerLng = $lngs->avg();

        // Calculate zoom level based on spread of points
        $latSpread = $lats->max() - $lats->min();
        $lngSpread = $lngs->max() - $lngs->min();
        $maxSpread = max($latSpread, $lngSpread);

        if ($maxSpread > 5) {
            $zoom = 6;
        } elseif ($maxSpread > 1) {
            $zoom = 7;
        } elseif ($maxSpread > 0.5) {
            $zoom = 8;
        } elseif ($maxSpread > 0.1) {
            $zoom = 9;
        } else {
            $zoom = 10;
        }

        // Try Google Maps Static API first (if API key is available)
        $googleApiKey = env('GOOGLE_MAPS_API_KEY', '');
        if (!empty($googleApiKey)) {
            $markerParams = [];
            
            // Group markers by type for different colors
            $markersByType = [
                'area' => [],
                'station' => [],
                'parking' => [],
                'other' => []
            ];

            foreach ($restPoints as $point) {
                if ($point->latitude && $point->longitude) {
                    $type = $point->type ?? 'other';
                    $markersByType[$type][] = $point->latitude . ',' . $point->longitude;
                }
            }

            // Add markers with different colors for each type
            if (!empty($markersByType['area'])) {
                $markerParams[] = 'markers=color:green|' . implode('|', array_slice($markersByType['area'], 0, 50));
            }
            if (!empty($markersByType['station'])) {
                $markerParams[] = 'markers=color:blue|' . implode('|', array_slice($markersByType['station'], 0, 50));
            }
            if (!empty($markersByType['parking'])) {
                $markerParams[] = 'markers=color:yellow|' . implode('|', array_slice($markersByType['parking'], 0, 50));
            }
            if (!empty($markersByType['other'])) {
                $markerParams[] = 'markers=color:gray|' . implode('|', array_slice($markersByType['other'], 0, 50));
            }

            return 'https://maps.googleapis.com/maps/api/staticmap?' .
                'center=' . urlencode($centerLat . ',' . $centerLng) .
                '&zoom=' . $zoom .
                '&size=800x600' .
                '&maptype=roadmap' .
                (!empty($markerParams) ? '&' . implode('&', $markerParams) : '') .
                '&key=' . $googleApiKey;
        }

        // Fallback: Use a free service that supports markers
        // Using a service that can render markers on static maps
        // For now, we'll use OpenStreetMap static map
        // Note: Most free OSM static map services don't support markers easily
        // The map will show the center area, and markers are listed in the table below
        
        $allMarkers = [];
        foreach ($restPoints as $point) {
            if ($point->latitude && $point->longitude) {
                $allMarkers[] = $point->latitude . ',' . $point->longitude;
            }
        }

        // Use OpenStreetMap static map (basic, without markers in the image)
        // Markers will be shown in the detailed table below
        return 'https://staticmap.openstreetmap.de/staticmap.php?' .
            'center=' . urlencode($centerLat . ',' . $centerLng) .
            '&zoom=' . $zoom .
            '&size=800x600' .
            '&maptype=mapnik';
    }

    /**
     * Export rest points to Excel
     */
    public function export(Request $request)
    {
        try {
            $restPointsQuery = RestPoint::query()
                ->orderByDesc('created_at');

            // Apply same filters as index
            if ($request->filled('type')) {
                $restPointsQuery->where('type', $request->input('type'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $restPointsQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $restPoints = $restPointsQuery->get();

            $fileName = 'rest-points-' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new RestPointsExport($restPoints), $fileName);
        } catch (Throwable $exception) {
            Log::error('Failed to export rest points', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            
            return back()->with('error', __('messages.error_exporting') ?? 'Error exporting rest points.');
        }
    }

    /**
     * Remove the specified rest point
     */
    public function destroy(RestPoint $restPoint): RedirectResponse
    {
        try {
            $restPoint->delete();

            return redirect()
                ->route('rest-points.index')
                ->with('success', __('messages.rest_point_deleted') ?? 'Rest point deleted successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to delete rest point', [
                'error' => $exception->getMessage(),
                'rest_point_id' => $restPoint->id,
            ]);
            return back()->with('error', __('messages.error_deleting_rest_point') ?? 'Error deleting rest point.');
        }
    }
}

