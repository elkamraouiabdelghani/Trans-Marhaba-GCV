<?php

namespace App\Http\Controllers;

use App\Exports\DriverHandoversExport;
use App\Http\Requests\StoreDriverHandoverRequest;
use App\Models\Driver;
use App\Models\DriverHandover;
use App\Models\Vehicle;
use App\Services\DriverHandoverPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DriverHandoverController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $handoversQuery = DriverHandover::with(['driverFrom', 'driverTo', 'vehicle'])
            ->latest();

        if ($statusFilter) {
            $handoversQuery->where('status', $statusFilter);
        }

        if ($dateFrom) {
            $handoversQuery->whereDate('handover_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $handoversQuery->whereDate('handover_date', '<=', $dateTo);
        }

        $handovers = $handoversQuery->paginate(20)->withQueryString();

        // Calculate stats (respect date filters but not status filter)
        $statsQuery = DriverHandover::query();
        
        if ($dateFrom) {
            $statsQuery->whereDate('handover_date', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $statsQuery->whereDate('handover_date', '<=', $dateTo);
        }
        
        $totalHandovers = $statsQuery->count();
        $confirmedHandovers = (clone $statsQuery)->where('status', DriverHandover::STATUS_CONFIRMED)->count();
        $pendingHandovers = (clone $statsQuery)->where('status', DriverHandover::STATUS_PENDING)->count();

        return view('driver-handovers.index', [
            'handovers' => $handovers,
            'statusFilter' => $statusFilter,
            'statusOptions' => $this->statusOptions(),
            'totalHandovers' => $totalHandovers,
            'confirmedHandovers' => $confirmedHandovers,
            'pendingHandovers' => $pendingHandovers,
        ]);
    }

    public function export(Request $request)
    {
        $statusFilter = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $handoversQuery = DriverHandover::with(['driverFrom', 'driverTo', 'vehicle'])
            ->latest();

        if ($statusFilter) {
            $handoversQuery->where('status', $statusFilter);
        }

        if ($dateFrom) {
            $handoversQuery->whereDate('handover_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $handoversQuery->whereDate('handover_date', '<=', $dateTo);
        }

        $handovers = $handoversQuery->get();

        $fileName = sprintf(
            'driver-handovers-%s-%s.xlsx',
            $statusFilter ?: 'all',
            now()->format('Ymd_His')
        );

        return Excel::download(new DriverHandoversExport($handovers), $fileName);
    }

    public function create()
    {
        return view('driver-handovers.create', [
            'drivers' => $this->driverOptions(),
            'driverVehicleMap' => $this->driverVehicleMap(),
            'vehicles' => $this->vehicleOptions(),
            'vehicleMileageMap' => $this->vehicleMileageMap(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreDriverHandoverRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $data = $this->prepareData($request);
            
            // Log prepared data for debugging (without sensitive file data)
            Log::info('Creating handover', [
                'data_keys' => array_keys($data),
                'has_documents' => isset($data['documents']),
                'has_document_files' => isset($data['document_files']),
                'has_equipment' => isset($data['equipment']),
            ]);

            $handover = DriverHandover::create($data);
            
            DB::commit();

            // Generate PDF automatically after creation
            try {
                $pdfService = new DriverHandoverPdfService();
                $pdfPath = $pdfService->generateHandoverPdf($handover);
                $handover->update(['handover_file_path' => $pdfPath]);
            } catch (\Exception $e) {
                // Log error but don't fail the creation
                Log::error('Failed to generate handover PDF', [
                    'handover_id' => $handover->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()
                ->route('driver-handovers.show', $handover)
                ->with('success', __('messages.handover_created'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // Laravel automatically redirects back with validation errors
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create handover', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['documents_files', 'documents_images', 'equipment_images']),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('messages.handover_creation_failed') ?? 'Failed to create handover: ' . $e->getMessage());
        }
    }

    public function show(DriverHandover $driver_handover)
    {
        $driver_handover->load(['driverFrom', 'driverTo', 'vehicle']);

        return view('driver-handovers.show', ['handover' => $driver_handover]);
    }

    public function edit(DriverHandover $driver_handover)
    {
        // Check if handover is confirmed - prevent editing
        if ($driver_handover->status === DriverHandover::STATUS_CONFIRMED) {
            return redirect()
                ->route('driver-handovers.show', $driver_handover)
                ->with('error', __('messages.cannot_edit_confirmed_handover') ?? 'Cannot edit a confirmed handover.');
        }

        $driver_handover->load(['driverFrom', 'driverTo', 'vehicle']);

        return view('driver-handovers.edit', [
            'handover' => $driver_handover,
            'drivers' => $this->driverOptions(),
            'driverVehicleMap' => $this->driverVehicleMap(),
            'vehicles' => $this->vehicleOptions(),
            'vehicleMileageMap' => $this->vehicleMileageMap(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(StoreDriverHandoverRequest $request, DriverHandover $driver_handover)
    {
        // Check if handover is confirmed - prevent updates
        if ($driver_handover->status === DriverHandover::STATUS_CONFIRMED) {
            return redirect()
                ->route('driver-handovers.show', $driver_handover)
                ->with('error', __('messages.cannot_edit_confirmed_handover') ?? 'Cannot edit a confirmed handover.');
        }

        $data = $this->prepareData($request, $driver_handover);

        // Store old PDF path before update
        $oldPdfPath = $driver_handover->handover_file_path;

        $driver_handover->update($data);

        // Delete old PDF file if it exists
        if ($oldPdfPath && Storage::disk('public')->exists($oldPdfPath)) {
            try {
                Storage::disk('public')->delete($oldPdfPath);
            } catch (\Exception $e) {
                Log::warning('Failed to delete old handover PDF', [
                    'handover_id' => $driver_handover->id,
                    'old_path' => $oldPdfPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Generate new PDF automatically after update
        try {
            $pdfService = new DriverHandoverPdfService();
            $pdfPath = $pdfService->generateHandoverPdf($driver_handover);
            $driver_handover->update(['handover_file_path' => $pdfPath]);
        } catch (\Exception $e) {
            // Log error but don't fail the update
            Log::error('Failed to generate handover PDF after update', [
                'handover_id' => $driver_handover->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('driver-handovers.show', $driver_handover)
            ->with('success', __('messages.handover_updated'));
    }

    public function confirm(DriverHandover $driver_handover)
    {
        DB::beginTransaction();
        try {
            // Load relationships
            $driver_handover->load(['driverFrom', 'driverTo', 'vehicle']);

            // Check if already confirmed
            if ($driver_handover->status === DriverHandover::STATUS_CONFIRMED) {
                return redirect()
                    ->route('driver-handovers.index')
                    ->with('error', __('messages.handover_already_confirmed'));
            }

            // Unassign vehicle from driver_from
            if ($driver_handover->driver_from_id && $driver_handover->vehicle_id) {
                $driverFrom = Driver::find($driver_handover->driver_from_id);
                if ($driverFrom && $driverFrom->assigned_vehicle_id == $driver_handover->vehicle_id) {
                    $driverFrom->assigned_vehicle_id = null;
                    $driverFrom->status = 'inactive';
                    $driverFrom->save();
                }
            }

            // Assign vehicle to driver_to
            if ($driver_handover->driver_to_id && $driver_handover->vehicle_id) {
                $driverTo = Driver::find($driver_handover->driver_to_id);
                if ($driverTo) {
                    // Check if vehicle is already assigned to another driver
                    $existingDriver = Driver::where('assigned_vehicle_id', $driver_handover->vehicle_id)
                        ->where('id', '!=', $driver_handover->driver_to_id)
                        ->first();
                    
                    if ($existingDriver) {
                        $existingDriver->assigned_vehicle_id = null;
                        $existingDriver->save();
                    }

                    $driverTo->assigned_vehicle_id = $driver_handover->vehicle_id;
                    $driverTo->status = 'active';
                    $driverTo->save();

                    // Update vehicle status if needed
                    $vehicle = Vehicle::find($driver_handover->vehicle_id);
                    if ($vehicle) {
                        $vehicle->status = 'active';
                        if ($driverTo->flotte_id) {
                            $vehicle->flotte_id = $driverTo->flotte_id;
                        }
                        $vehicle->save();
                    }
                }
            }

            // Update handover status
            $driver_handover->update([
                'status' => DriverHandover::STATUS_CONFIRMED,
            ]);

            DB::commit();

            Log::info('Driver handover confirmed', [
                'handover_id' => $driver_handover->id,
                'driver_from_id' => $driver_handover->driver_from_id,
                'driver_to_id' => $driver_handover->driver_to_id,
                'vehicle_id' => $driver_handover->vehicle_id,
            ]);

            return redirect()
                ->route('driver-handovers.index')
                ->with('success', __('messages.handover_confirmed'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to confirm driver handover', [
                'handover_id' => $driver_handover->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('driver-handovers.index')
                ->with('error', __('messages.handover_confirm_error'));
        }
    }

    public function destroy(DriverHandover $driver_handover)
    {
        if ($driver_handover->handover_file_path) {
            Storage::disk('public')->delete($driver_handover->handover_file_path);
        }

        $driver_handover->delete();

        return redirect()
            ->route('driver-handovers.index')
            ->with('success', __('messages.handover_deleted'));
    }

    // private functions
    private function driverOptions()
    {
        return Driver::orderByRaw("CASE WHEN (full_name IS NULL OR full_name = '') THEN 1 ELSE 0 END")
            ->orderBy('full_name')
            ->where('status', '!=', 'terminated')
            ->pluck('full_name', 'id');
    }

    private function driverVehicleMap(): array
    {
        return Driver::pluck('assigned_vehicle_id', 'id')->toArray();
    }

    private function vehicleOptions()
    {
        return Vehicle::orderBy('license_plate')->pluck('license_plate', 'id');
    }

    private function vehicleMileageMap(): array
    {
        return Vehicle::pluck('current_mileage', 'id')->toArray();
    }

    private function statusOptions(): array
    {
        return [
            DriverHandover::STATUS_PENDING => __('messages.pending'),
            DriverHandover::STATUS_CONFIRMED => __('messages.confirmed'),
        ];
    }

    private function prepareData(StoreDriverHandoverRequest $request, ?DriverHandover $handover = null): array
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? DriverHandover::STATUS_PENDING;

        // Auto-populate driver_from_name from driver_from_id
        if (!empty($data['driver_from_id'])) {
            $driverFrom = Driver::find($data['driver_from_id']);
            if ($driverFrom && $driverFrom->full_name) {
                $data['driver_from_name'] = $driverFrom->full_name;
            }
        }

        // Auto-populate driver_to_name from driver_to_id
        if (!empty($data['driver_to_id'])) {
            $driverTo = Driver::find($data['driver_to_id']);
            if ($driverTo && $driverTo->full_name) {
                $data['driver_to_name'] = $driverTo->full_name;
            }
        }

        // Handle cause field - if "other" is selected, use cause_other value
        if (isset($data['cause']) && $data['cause'] === 'other' && !empty($data['cause_other'])) {
            $data['cause'] = $data['cause_other'];
        }
        // Remove cause_other from data as it's not a database field
        unset($data['cause_other']);

        // Prepare documents data - documents for document items (table)
        $documents = $request->input('documents', []);
        
        // Handle document images uploads
        if ($request->hasFile('documents_images')) {
            $images = $request->file('documents_images');
            
            // Handle regular document images (exclude 'options' key)
            foreach ($images as $key => $file) {
                if ($key === 'options') {
                    continue; // Handle options separately
                }
                
                // Check if it's actually a file (not an array)
                if ($file && !is_array($file) && $file->isValid()) {
                    $path = $file->store('driver-handovers/documents', 'public');
                    $documents["{$key}_image"] = $path;
                    
                    // Delete old image if updating
                    if ($handover && isset($handover->documents["{$key}_image"])) {
                        $oldPath = $handover->documents["{$key}_image"];
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                }
            }
            
            // Handle document options images
            if (isset($images['options']) && is_array($images['options'])) {
                if (!isset($documents['options'])) {
                    $documents['options'] = [];
                }
                
                foreach ($images['options'] as $rowKey => $file) {
                    // Check if it's actually a file (not an array)
                    if ($file && !is_array($file) && $file->isValid()) {
                        $path = $file->store('driver-handovers/documents', 'public');
                        
                        if (!isset($documents['options'][$rowKey])) {
                            $documents['options'][$rowKey] = [];
                        }
                        
                        $documents['options'][$rowKey]['image'] = $path;
                        
                        // Delete old image if updating
                        if ($handover && isset($handover->documents['options'][$rowKey]['image'])) {
                            $oldPath = $handover->documents['options'][$rowKey]['image'];
                            if (Storage::disk('public')->exists($oldPath)) {
                                Storage::disk('public')->delete($oldPath);
                            }
                        }
                    }
                }
            }
        }
        
        // Always set documents, even if empty
        $data['documents'] = $documents;

        // Handle document_files - general document files for the handover (multiple files)
        $documentFiles = [];
        
        if ($request->hasFile('documents_files')) {
            $files = $request->file('documents_files');
            
            // Handle multiple files upload
            foreach ($files as $index => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('driver-handovers/documents/files', 'public');
                    $documentFiles[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ];
                }
            }
            
            // Merge with existing files if updating (excluding removed files)
            if ($handover && isset($handover->document_files) && is_array($handover->document_files)) {
                $removedFiles = json_decode($request->input('removed_files', '[]'), true) ?? [];
                $existingFiles = array_filter($handover->document_files, function($index) use ($removedFiles) {
                    return !in_array($index, $removedFiles);
                }, ARRAY_FILTER_USE_KEY);
                
                // Delete removed files from storage
                foreach ($removedFiles as $index) {
                    if (isset($handover->document_files[$index]['path'])) {
                        $oldPath = $handover->document_files[$index]['path'];
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                }
                
                // Re-index array and merge
                $existingFiles = array_values($existingFiles);
                $documentFiles = array_merge($existingFiles, $documentFiles);
            }
        } elseif ($handover && isset($handover->document_files)) {
            // Handle removed files even if no new files uploaded
            $removedFiles = json_decode($request->input('removed_files', '[]'), true) ?? [];
            if (!empty($removedFiles)) {
                // Delete removed files from storage
                foreach ($removedFiles as $index) {
                    if (isset($handover->document_files[$index]['path'])) {
                        $oldPath = $handover->document_files[$index]['path'];
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                }
                
                $existingFiles = array_filter($handover->document_files, function($index) use ($removedFiles) {
                    return !in_array($index, $removedFiles);
                }, ARRAY_FILTER_USE_KEY);
                $documentFiles = array_values($existingFiles);
            } else {
                $documentFiles = $handover->document_files;
            }
        }
        
        // Always set document_files, even if empty (for new handovers)
        $data['document_files'] = $documentFiles;

        // Prepare equipment data - equipment documents/images
        $equipment = $request->input('equipment', []);
        
        // Merge equipment_counts into equipment data
        if ($request->has('equipment_counts')) {
            $equipment['counts'] = $request->input('equipment_counts', []);
        }
        
        // Handle equipment images uploads
        if ($request->hasFile('equipment_images')) {
            foreach ($request->file('equipment_images') as $key => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('driver-handovers/equipment', 'public');
                    $equipment["{$key}_image"] = $path;
                    
                    // Delete old image if updating
                    if ($handover && isset($handover->equipment["{$key}_image"])) {
                        $oldPath = $handover->equipment["{$key}_image"];
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                }
            }
        }
        
        // Always set equipment, even if empty
        $data['equipment'] = $equipment;

        if ($request->hasFile('handover_file')) {
            if ($handover && $handover->handover_file_path) {
                Storage::disk('public')->delete($handover->handover_file_path);
            }

            $data['handover_file_path'] = $request->file('handover_file')->store('driver-handovers', 'public');
        }

        // Ensure JSON fields are properly formatted (Laravel will auto-cast, but ensure they're arrays)
        if (isset($data['documents']) && !is_array($data['documents'])) {
            $data['documents'] = [];
        }
        if (isset($data['document_files']) && !is_array($data['document_files'])) {
            $data['document_files'] = [];
        }
        if (isset($data['equipment']) && !is_array($data['equipment'])) {
            $data['equipment'] = [];
        }

        // Remove any fields that are not in fillable
        $fillable = (new DriverHandover())->getFillable();
        $data = array_intersect_key($data, array_flip($fillable));

        return $data;
    }

    /**
     * Serve a document file from document_files array
     */
    public function showDocumentFile(DriverHandover $driver_handover, int $index)
    {
        try {
            $documentFiles = $driver_handover->document_files ?? [];
            
            if (!isset($documentFiles[$index])) {
                abort(404);
            }

            $file = $documentFiles[$index];
            $path = $file['path'] ?? $file;

            if (is_array($path)) {
                $path = $path['path'] ?? null;
            }

            if (!$path) {
                abort(404);
            }

            $disk = Storage::disk('public');

            if (!$disk->exists($path)) {
                abort(404);
            }

            $filename = $file['name'] ?? basename($path);

            return response()->file($disk->path($path), [
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to serve handover document file', [
                'handover_id' => $driver_handover->id,
                'index' => $index,
                'error' => $e->getMessage(),
            ]);

            abort(404);
        }
    }

    /**
     * Serve a document image from documents array
     */
    public function showDocumentImage(DriverHandover $driver_handover, string $key)
    {
        try {
            $documents = $driver_handover->documents ?? [];
            $path = null;
            
            // Handle regular document images (e.g., cartes_grises_image)
            $imageKey = "{$key}_image";
            if (isset($documents[$imageKey])) {
                $path = $documents[$imageKey];
            } 
            // Handle options document images (e.g., row_0, row_1, etc.)
            elseif (isset($documents['options']) && is_array($documents['options'])) {
                // Try with the key directly (e.g., row_0)
                if (isset($documents['options'][$key]['image'])) {
                    $path = $documents['options'][$key]['image'];
                }
                // Try to find in any row
                else {
                    foreach ($documents['options'] as $rowKey => $rowData) {
                        if (isset($rowData['image']) && $rowKey === $key) {
                            $path = $rowData['image'];
                            break;
                        }
                    }
                }
            }
            // Try direct key
            elseif (isset($documents[$key])) {
                $path = is_array($documents[$key]) ? ($documents[$key]['image'] ?? null) : $documents[$key];
            }

            if (!$path) {
                abort(404);
            }

            $disk = Storage::disk('public');

            if (!$disk->exists($path)) {
                abort(404);
            }

            return response()->file($disk->path($path));
        } catch (\Throwable $e) {
            Log::error('Failed to serve handover document image', [
                'handover_id' => $driver_handover->id,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            abort(404);
        }
    }

    /**
     * Serve an equipment image from equipment array
     */
    public function showEquipmentImage(DriverHandover $driver_handover, string $key)
    {
        try {
            $equipment = $driver_handover->equipment ?? [];
            
            $imageKey = "{$key}_image";
            if (!isset($equipment[$imageKey])) {
                abort(404);
            }

            $path = $equipment[$imageKey];

            if (!$path) {
                abort(404);
            }

            $disk = Storage::disk('public');

            if (!$disk->exists($path)) {
                abort(404);
            }

            return response()->file($disk->path($path));
        } catch (\Throwable $e) {
            Log::error('Failed to serve handover equipment image', [
                'handover_id' => $driver_handover->id,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            abort(404);
        }
    }

    /**
     * Download the handover PDF
     */
    public function downloadPdf(DriverHandover $driver_handover)
    {
        try {
            $path = $driver_handover->handover_file_path;

            if (!$path) {
                abort(404);
            }

            $disk = Storage::disk('public');

            if (!$disk->exists($path)) {
                abort(404);
            }

            return response()->download($disk->path($path), 'handover-' . $driver_handover->id . '.pdf');
        } catch (\Throwable $e) {
            Log::error('Failed to download handover PDF', [
                'handover_id' => $driver_handover->id,
                'error' => $e->getMessage(),
            ]);

            abort(404);
        }
    }
}

