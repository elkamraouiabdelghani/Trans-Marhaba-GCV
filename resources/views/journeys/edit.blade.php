<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h2 class="mb-1 fw-bold text-dark fs-4">
                    <i class="bi bi-pencil me-2 text-primary"></i>
                    {{ __('messages.edit_journey') ?? 'Edit Journey' }}
                </h2>
            </div>
            <div>
                <a href="{{ route('journeys.show', $journey) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    {{ __('messages.back') ?? 'Back' }}
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-10">
                <div class="card border-0 shadow-sm">
                    <form action="{{ route('journeys.update', $journey) }}" method="POST" id="editJourneyForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body p-4">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="edit_name" class="form-label fw-semibold">
                                        {{ __('messages.name') ?? 'Name' }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="edit_name" name="name" value="{{ old('name', $journey->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- From Location -->
                                <div class="col-md-6">
                                    <div class="d-flex">
                                        <div class="d-flex flex-column">
                                            <label class="form-label fw-semibold mb-0">
                                                {{ __('messages.from_location') ?? 'From Location' }} <span class="text-danger">*</span>
                                            </label>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary mt-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#fromLocationModal">
                                                <i class="bi bi-map me-1"></i>
                                                {{ __('messages.select_on_map') ?? 'Select on Map' }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Hidden fields and resolved coordinates label -->
                                    <input type="hidden" name="from_latitude" id="edit_from_latitude" value="{{ old('from_latitude', $journey->from_latitude) }}" required>
                                    <input type="hidden" name="from_longitude" id="edit_from_longitude" value="{{ old('from_longitude', $journey->from_longitude) }}" required>
                                    <input type="hidden" name="from_location_name" id="edit_from_location_name_hidden" value="{{ old('from_location_name', $journey->from_location_name) }}">
                                    <small class="text-muted d-block mt-1" id="edit-from-coordinates-label">
                                        @if(old('from_latitude') && old('from_longitude'))
                                            {{ __('messages.location_coords_label') ?? 'Coordinates' }}:
                                            <span class="fw-semibold">{{ old('from_latitude') }}, {{ old('from_longitude') }}</span>
                                        @else
                                            {{ __('messages.location_coords_label') ?? 'Coordinates' }}:
                                            <span class="fw-semibold">{{ $journey->from_latitude }}, {{ $journey->from_longitude }}</span>
                                        @endif
                                    </small>
                                    <small class="text-muted d-block mt-1" id="edit-from-location-name-label">
                                        @if(old('from_location_name') || $journey->from_location_name)
                                            {{ __('messages.location_name_label') ?? 'Location Name' }}:
                                            <span class="fw-semibold">{{ old('from_location_name') ?? $journey->from_location_name }}</span>
                                        @endif
                                    </small>
                                    @error('from_latitude')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('from_longitude')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- To Location -->
                                <div class="col-md-6">
                                    <div class="d-flex">
                                        <div class="d-flex flex-column">
                                            <label class="form-label fw-semibold mb-0">
                                                {{ __('messages.to_location') ?? 'To Location' }} <span class="text-danger">*</span>
                                            </label>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary mt-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#toLocationModal">
                                                <i class="bi bi-map me-1"></i>
                                                {{ __('messages.select_on_map') ?? 'Select on Map' }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Hidden fields and resolved coordinates label -->
                                    <input type="hidden" name="to_latitude" id="edit_to_latitude" value="{{ old('to_latitude', $journey->to_latitude) }}" required>
                                    <input type="hidden" name="to_longitude" id="edit_to_longitude" value="{{ old('to_longitude', $journey->to_longitude) }}" required>
                                    <input type="hidden" name="to_location_name" id="edit_to_location_name_hidden" value="{{ old('to_location_name', $journey->to_location_name) }}">
                                    <small class="text-muted d-block mt-1" id="edit-to-coordinates-label">
                                        @if(old('to_latitude') && old('to_longitude'))
                                            {{ __('messages.location_coords_label') ?? 'Coordinates' }}:
                                            <span class="fw-semibold">{{ old('to_latitude') }}, {{ old('to_longitude') }}</span>
                                        @else
                                            {{ __('messages.location_coords_label') ?? 'Coordinates' }}:
                                            <span class="fw-semibold">{{ $journey->to_latitude }}, {{ $journey->to_longitude }}</span>
                                        @endif
                                    </small>
                                    <small class="text-muted d-block mt-1" id="edit-to-location-name-label">
                                        @if(old('to_location_name') || $journey->to_location_name)
                                            {{ __('messages.location_name_label') ?? 'Location Name' }}:
                                            <span class="fw-semibold">{{ old('to_location_name') ?? $journey->to_location_name }}</span>
                                        @endif
                                    </small>
                                    @error('to_latitude')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('to_longitude')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="edit_details" class="form-label fw-semibold">
                                        {{ __('messages.details') ?? 'Details' }}
                                    </label>
                                    <textarea class="form-control @error('details') is-invalid @enderror"
                                              id="edit_details" name="details" rows="4">{{ old('details', $journey->details) }}</textarea>
                                    @error('details')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Black Points Section -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0 fw-bold">
                                            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                                            {{ __('messages.black_points') ?? 'Black Points' }}
                                        </h5>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary" 
                                                id="addBlackPointBtn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#addBlackPointModal">
                                            <i class="bi bi-plus-circle me-1"></i>
                                            {{ __('messages.add_black_point') ?? 'Add Black Point' }}
                                        </button>
                                    </div>
                                    <div id="blackPointsContainer">
                                        @foreach($journey->blackPoints as $index => $blackPoint)
                                            <div class="card mb-3 border" data-black-point-index="{{ $index }}">
                                                <div class="card-body p-3">
                                                    <div class="row g-2 align-items-center">
                                                        <div class="col-md-4">
                                                            <strong>{{ $blackPoint->name }}</strong>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <small class="text-muted">{{ $blackPoint->latitude }}, {{ $blackPoint->longitude }}</small>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <small class="text-muted">{{ $blackPoint->description ? Str::limit($blackPoint->description, 30) : '-' }}</small>
                                                        </div>
                                                        <div class="col-md-1 text-end">
                                                            <button type="button" class="btn btn-sm btn-outline-danger remove-black-point" data-index="{{ $index }}">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="black_points[{{ $index }}][name]" value="{{ $blackPoint->name }}">
                                                    <input type="hidden" name="black_points[{{ $index }}][latitude]" value="{{ $blackPoint->latitude }}">
                                                    <input type="hidden" name="black_points[{{ $index }}][longitude]" value="{{ $blackPoint->longitude }}">
                                                    <input type="hidden" name="black_points[{{ $index }}][description]" value="{{ $blackPoint->description }}">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Checklist Section -->
                            @if(isset($checklistItems) && $checklistItems->count() > 0)
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="mb-3 fw-bold">
                                            <i class="bi bi-list-check me-2 text-primary"></i>
                                            {{ __('messages.checklist') ?? 'Checklist' }}
                                        </h5>
                                        <p class="text-muted mb-4">
                                            {{ __('messages.checklist_optional_help') ?? 'Checklist is optional. Fill only the items you want for this journey.' }}
                                        </p>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 30%;">{{ __('messages.item') ?? 'Item' }}</th>
                                                        <th style="width: 15%;" class="text-center">{{ __('messages.weight') ?? 'Weight' }} (1-10)</th>
                                                        <th style="width: 15%;" class="text-center">{{ __('messages.score') ?? 'Score' }} (1-5)</th>
                                                        <th style="width: 15%;" class="text-center">{{ __('messages.note') ?? 'Note' }}</th>
                                                        <th style="width: 25%;">{{ __('messages.comment') ?? 'Comment' }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($checklistItems as $item)
                                                        @php
                                                            $existingAnswer = $existingChecklist ? $existingChecklist->answers->firstWhere('journeys_checklist_id', $item->id) : null;
                                                        @endphp
                                                        <tr>
                                                            <td class="align-middle">
                                                                <div>
                                                                    <strong>{{ $item->donnees }}</strong>
                                                                    @if($item->cirees_appreciation)
                                                                        <br><small class="text-muted">{{ $item->cirees_appreciation }}</small>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <input type="number" 
                                                                       class="form-control form-control-sm text-center checklist-weight" 
                                                                       name="checklist[{{ $item->id }}][weight]" 
                                                                       id="checklist_{{ $item->id }}_weight"
                                                                       min="1" 
                                                                       max="10" 
                                                                       value="{{ old("checklist.{$item->id}.weight", $existingAnswer->weight ?? 1) }}"
                                                                       data-item-id="{{ $item->id }}">
                                                                @error("checklist.{$item->id}.weight")
                                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                                @enderror
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <input type="number" 
                                                                       class="form-control form-control-sm text-center checklist-score" 
                                                                       name="checklist[{{ $item->id }}][score]" 
                                                                       id="checklist_{{ $item->id }}_score"
                                                                       min="1" 
                                                                       max="5" 
                                                                       value="{{ old("checklist.{$item->id}.score", $existingAnswer->score ?? 1) }}"
                                                                       data-item-id="{{ $item->id }}">
                                                                @error("checklist.{$item->id}.score")
                                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                                @enderror
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <input type="text" 
                                                                       class="form-control form-control-sm text-center checklist-note" 
                                                                       id="checklist_{{ $item->id }}_note"
                                                                       value="{{ $existingAnswer ? number_format($existingAnswer->note ?? 0, 2) : '0' }}"
                                                                       readonly
                                                                       style="background-color: #f8f9fa;">
                                                                <input type="hidden" 
                                                                       name="checklist[{{ $item->id }}][note]" 
                                                                       id="checklist_{{ $item->id }}_note_hidden"
                                                                       value="{{ $existingAnswer ? number_format($existingAnswer->note ?? 0, 2) : '0' }}">
                                                            </td>
                                                            <td class="align-middle">
                                                                <textarea class="form-control form-control-sm" 
                                                                          name="checklist[{{ $item->id }}][comment]" 
                                                                          id="checklist_{{ $item->id }}_comment"
                                                                          rows="2" 
                                                                          placeholder="{{ __('messages.comment_optional') ?? 'Comment (optional)' }}">{{ old("checklist.{$item->id}.comment", $existingAnswer->comment ?? '') }}</textarea>
                                                                @error("checklist.{$item->id}.comment")
                                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                                @enderror
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Checklist Documents -->
                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <label for="checklist_documents" class="form-label fw-semibold">
                                                    <i class="bi bi-images me-2"></i>
                                                    {{ __('messages.checklist_documents') ?? 'Checklist Documents' }}
                                                </label>
                                                @if($existingChecklist && $existingChecklist->documents && count($existingChecklist->documents) > 0)
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block mb-2">{{ __('messages.existing_documents') ?? 'Existing Documents' }}:</small>
                                                        <div class="row g-3">
                                                            @foreach($existingChecklist->documents as $doc)
                                                                @php
                                                                    $docUrl = route('journeys.checklists.document', ['encoded' => base64_encode($doc)]);
                                                                @endphp
                                                                <div class="col-md-3 col-sm-6">
                                                                    <div class="card border">
                                                                        <a href="{{ $docUrl }}" target="_blank" class="text-decoration-none">
                                                                            <img src="{{ $docUrl }}"
                                                                                 alt="Document"
                                                                                 class="card-img-top"
                                                                                 style="height: 200px; object-fit: cover; cursor: pointer;">
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                <input type="file" 
                                                       class="form-control @error('checklist_documents') is-invalid @enderror" 
                                                       id="checklist_documents" 
                                                       name="checklist_documents[]" 
                                                       multiple 
                                                       accept="image/*">
                                                <small class="text-muted">
                                                    {{ __('messages.pictures_help') ?? 'You can select multiple pictures. Accepted formats: JPG, PNG, GIF.' }}
                                                </small>
                                                @error('checklist_documents')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @error('checklist_documents.*')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                                
                                                {{-- Preview area for selected images --}}
                                                <div id="checklist_documents_preview" class="mt-3 row g-2"></div>
                                            </div>
                                        </div>

                                        <!-- Checklist Status and Notes -->
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">
                                                    {{ __('messages.status') ?? 'Status' }}
                                                </label>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="checklist_status" 
                                                           id="checklist_status_accepted" 
                                                           value="accepted" 
                                                           {{ old('checklist_status', $existingChecklist->status ?? 'accepted') === 'accepted' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-success" for="checklist_status_accepted">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        {{ __('messages.accepted') ?? 'Accepted' }}
                                                    </label>

                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="checklist_status" 
                                                           id="checklist_status_rejected" 
                                                           value="rejected" 
                                                           {{ old('checklist_status', $existingChecklist->status ?? '') === 'rejected' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-danger" for="checklist_status_rejected">
                                                        <i class="bi bi-x-circle me-1"></i>
                                                        {{ __('messages.rejected') ?? 'Rejected' }}
                                                    </label>
                                                </div>
                                                @error('checklist_status')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="checklist_notes" class="form-label fw-semibold">
                                                    {{ __('messages.general_comment') ?? 'General Comment' }}
                                                </label>
                                                <textarea class="form-control @error('checklist_notes') is-invalid @enderror"
                                                          id="checklist_notes" 
                                                          name="checklist_notes" 
                                                          rows="3" 
                                                          placeholder="{{ __('messages.general_comment_placeholder') ?? 'Enter any general comments or notes about this checklist...' }}">{{ old('checklist_notes', $existingChecklist->notes ?? '') }}</textarea>
                                                @error('checklist_notes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    {{ __('messages.no_checklist_items') ?? 'No active checklist items found. Please create checklist items first.' }}
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('journeys.show', $journey) }}" class="btn btn-outline-secondary">
                                    {{ __('messages.cancel') ?? 'Cancel' }}
                                </a>
                                <button type="submit" class="btn btn-dark">
                                    <i class="bi bi-save me-1"></i>
                                    {{ __('messages.update') ?? 'Update' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- From Location Modal -->
    <div class="modal fade" id="fromLocationModal" tabindex="-1" aria-labelledby="fromLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fromLocationModalLabel">
                        {{ __('messages.select_from_location') ?? 'Select From Location' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Mode selector inside modal -->
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm mb-2" role="group" aria-label="From location input mode">
                            <button type="button" class="btn btn-outline-secondary active" data-location-mode="from-address">
                                {{ __('messages.address') ?? 'Address' }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-location-mode="from-place">
                                {{ __('messages.location') ?? 'Location' }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-location-mode="from-latlng">
                                {{ __('messages.coordinates') ?? 'Lat / Lng' }}
                            </button>
                        </div>

                        <!-- Address mode -->
                        <div class="mb-2 location-mode-block" id="from-address-block">
                            <label for="from_address_input" class="form-label small mb-1">
                                {{ __('messages.address') ?? 'Address' }}
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="text"
                                       id="from_address_input"
                                       class="form-control"
                                       placeholder="Ex: Boulevard Mohammed V, Casablanca">
                                <button class="btn btn-outline-primary" type="button" id="from_address_search">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                {{ __('messages.location_help') ?? 'Type an address then search, or click on the map.' }}
                            </small>
                            <div class="text-danger small mt-1 d-none" id="from_address_error"></div>
                        </div>

                        <!-- Place / free-text location mode -->
                        <div class="mb-2 location-mode-block d-none" id="from-place-block">
                            <label for="from_place_input" class="form-label small mb-1">
                                {{ __('messages.location') ?? 'Location' }}
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="text"
                                       id="from_place_input"
                                       class="form-control"
                                       placeholder="Ex: Rest area near Rabat, highway km 22">
                                <button class="btn btn-outline-primary" type="button" id="from_place_search">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                {{ __('messages.location_help') ?? 'Type a location name then search, or click on the map.' }}
                            </small>
                            <div class="text-danger small mt-1 d-none" id="from_place_error"></div>
                        </div>

                        <!-- Lat/Lng mode -->
                        <div class="mb-2 location-mode-block d-none" id="from-latlng-block">
                            <label class="form-label small mb-1">
                                {{ __('messages.coordinates') ?? 'Coordinates' }}
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number"
                                           step="0.000001"
                                           class="form-control form-control-sm"
                                           id="from_lat_input"
                                           placeholder="Lat (e.g. 33.573100)">
                                </div>
                                <div class="col-6">
                                    <input type="number"
                                           step="0.000001"
                                           class="form-control form-control-sm"
                                           id="from_lng_input"
                                           placeholder="Lng (e.g. -7.589800)">
                                </div>
                            </div>
                            <div class="mt-1">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="from_latlng_apply">
                                    {{ __('messages.apply') ?? 'Apply' }}
                                </button>
                            </div>
                            <small class="text-muted d-block">
                                {{ __('messages.location_coords_hint') ?? 'Latitude between -90 and 90, longitude between -180 and 180.' }}
                            </small>
                            <div class="text-danger small mt-1 d-none" id="from_latlng_error"></div>
                        </div>
                    </div>
                    
                    <!-- From Location Name -->
                    <div class="mt-3">
                        <label for="edit_from_location_name" class="form-label fw-semibold">
                            {{ __('messages.from_location_name') ?? 'From Location Name' }} <small class="text-muted">({{ __('messages.optional') ?? 'Optional' }})</small>
                        </label>
                        <input type="text" 
                               class="form-control @error('from_location_name') is-invalid @enderror" 
                               id="edit_from_location_name" 
                               value="{{ old('from_location_name', $journey->from_location_name) }}"
                               placeholder="{{ __('messages.location_name_placeholder') ?? 'e.g., Casablanca, Rabat, etc.' }}">
                        @error('from_location_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted" id="from-location-coordinates-label">
                            {{ __('messages.location_map_help') ?? 'Click on the map to set the coordinates.' }}
                        </small>
                    </div>

                    <div id="from-location-map" style="width: 100%; height: 400px; border-radius: 0.5rem;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') ?? 'Cancel' }}
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmFromLocation" data-bs-dismiss="modal">
                        {{ __('messages.confirm') ?? 'Confirm' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Black Point Modal -->
    <div class="modal fade" id="addBlackPointModal" tabindex="-1" aria-labelledby="addBlackPointModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBlackPointModalLabel">
                        {{ __('messages.add_black_point') ?? 'Add Black Point' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="black_point_name_input" class="form-label fw-semibold">
                            {{ __('messages.name') ?? 'Name' }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="black_point_name_input" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            {{ __('messages.location') ?? 'Location' }} <span class="text-danger">*</span>
                        </label>
                        <div id="black-point-map-modal" style="width: 100%; height: 300px; border-radius: 0.5rem; margin-bottom: 0.5rem;"></div>
                        <small class="text-muted" id="black-point-modal-coordinates-label">
                            {{ __('messages.location_map_help') ?? 'Click on the map to set the coordinates.' }}
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="black_point_description_input" class="form-label fw-semibold">
                            {{ __('messages.description') ?? 'Description' }}
                        </label>
                        <textarea class="form-control" id="black_point_description_input" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') ?? 'Cancel' }}
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmAddBlackPoint" data-bs-dismiss="modal">
                        {{ __('messages.add') ?? 'Add' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- To Location Modal -->
    <div class="modal fade" id="toLocationModal" tabindex="-1" aria-labelledby="toLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="toLocationModalLabel">
                        {{ __('messages.select_to_location') ?? 'Select To Location' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Mode selector inside modal -->
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm mb-2" role="group" aria-label="To location input mode">
                            <button type="button" class="btn btn-outline-secondary active" data-location-mode="to-address">
                                {{ __('messages.address') ?? 'Address' }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-location-mode="to-place">
                                {{ __('messages.location') ?? 'Location' }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-location-mode="to-latlng">
                                {{ __('messages.coordinates') ?? 'Lat / Lng' }}
                            </button>
                        </div>

                        <!-- Address mode -->
                        <div class="mb-2 location-mode-block" id="to-address-block">
                            <label for="to_address_input" class="form-label small mb-1">
                                {{ __('messages.address') ?? 'Address' }}
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="text"
                                       id="to_address_input"
                                       class="form-control"
                                       placeholder="Ex: Station service, Tanger">
                                <button class="btn btn-outline-primary" type="button" id="to_address_search">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                {{ __('messages.location_help') ?? 'Type an address then search, or click on the map.' }}
                            </small>
                            <div class="text-danger small mt-1 d-none" id="to_address_error"></div>
                        </div>

                        <!-- Place / free-text location mode -->
                        <div class="mb-2 location-mode-block d-none" id="to-place-block">
                            <label for="to_place_input" class="form-label small mb-1">
                                {{ __('messages.location') ?? 'Location' }}
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="text"
                                       id="to_place_input"
                                       class="form-control"
                                       placeholder="Ex: Port de Tanger Med">
                                <button class="btn btn-outline-primary" type="button" id="to_place_search">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                {{ __('messages.location_help') ?? 'Type a location name then search, or click on the map.' }}
                            </small>
                            <div class="text-danger small mt-1 d-none" id="to_place_error"></div>
                        </div>

                        <!-- Lat/Lng mode -->
                        <div class="mb-2 location-mode-block d-none" id="to-latlng-block">
                            <label class="form-label small mb-1">
                                {{ __('messages.coordinates') ?? 'Coordinates' }}
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number"
                                           step="0.000001"
                                           class="form-control form-control-sm"
                                           id="to_lat_input"
                                           placeholder="Lat (e.g. 35.779600)">
                                </div>
                                <div class="col-6">
                                    <input type="number"
                                           step="0.000001"
                                           class="form-control form-control-sm"
                                           id="to_lng_input"
                                           placeholder="Lng (e.g. -5.799750)">
                                </div>
                            </div>
                            <div class="mt-1">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="to_latlng_apply">
                                    {{ __('messages.apply') ?? 'Apply' }}
                                </button>
                            </div>
                            <small class="text-muted d-block">
                                {{ __('messages.location_coords_hint') ?? 'Latitude between -90 and 90, longitude between -180 and 180.' }}
                            </small>
                            <div class="text-danger small mt-1 d-none" id="to_latlng_error"></div>
                        </div>
                    </div>
                    
                    <!-- To Location Name -->
                    <div class="mt-3">
                        <label for="edit_to_location_name" class="form-label fw-semibold">
                            {{ __('messages.to_location_name') ?? 'To Location Name' }} <small class="text-muted">({{ __('messages.optional') ?? 'Optional' }})</small>
                        </label>
                        <input type="text" 
                               class="form-control @error('to_location_name') is-invalid @enderror" 
                               id="edit_to_location_name" 
                               value="{{ old('to_location_name', $journey->to_location_name) }}"
                               placeholder="{{ __('messages.location_name_placeholder') ?? 'e.g., Casablanca, Rabat, etc.' }}">
                        @error('to_location_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-3">
                        <small class="text-muted" id="to-location-coordinates-label">
                            {{ __('messages.location_map_help') ?? 'Click on the map to set the coordinates.' }}
                        </small>
                    </div>

                    <div id="to-location-map" style="width: 100%; height: 400px; border-radius: 0.5rem;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') ?? 'Cancel' }}
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmToLocation" data-bs-dismiss="modal">
                        {{ __('messages.confirm') ?? 'Confirm' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet CSS and JS -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Wait for Leaflet to load
        function waitForLeaflet(callback) {
            if (window.L) {
                callback();
            } else {
                setTimeout(function() {
                    waitForLeaflet(callback);
                }, 100);
            }
        }

        waitForLeaflet(function() {
            if (!window.L) {
                console.error('Leaflet library not loaded');
                return;
            }

            let fromMap = null;
            let fromMarker = null;
            let fromLat = null;
            let fromLng = null;

            let toMap = null;
            let toMarker = null;
            let toLat = null;
            let toLng = null;

            // Initialize coordinates from existing values
            const existingFromLat = document.getElementById('edit_from_latitude')?.value;
            const existingFromLng = document.getElementById('edit_from_longitude')?.value;
            if (existingFromLat && existingFromLng) {
                fromLat = parseFloat(existingFromLat);
                fromLng = parseFloat(existingFromLng);
            }

            const existingToLat = document.getElementById('edit_to_latitude')?.value;
            const existingToLng = document.getElementById('edit_to_longitude')?.value;
            if (existingToLat && existingToLng) {
                toLat = parseFloat(existingToLat);
                toLng = parseFloat(existingToLng);
            }

            // --- Helper functions for setting coordinates & syncing fields ---
            function setFromCoordinates(lat, lng, centerMap = true) {
                fromLat = lat;
                fromLng = lng;

                const hiddenLat = document.getElementById('edit_from_latitude');
                const hiddenLng = document.getElementById('edit_from_longitude');
                if (hiddenLat) hiddenLat.value = lat.toFixed(6);
                if (hiddenLng) hiddenLng.value = lng.toFixed(6);

                // Update lat/lng inputs in Lat/Lng mode
                const latInput = document.getElementById('from_lat_input');
                const lngInput = document.getElementById('from_lng_input');
                if (latInput) latInput.value = lat.toFixed(6);
                if (lngInput) lngInput.value = lng.toFixed(6);

                // Update label
                const formLabel = document.getElementById('edit-from-coordinates-label');
                if (formLabel) {
                    const coordsLabel = @json(__('messages.location_coords_label') ?? 'Coordinates');
                    formLabel.innerHTML = coordsLabel + ': <span class="fw-semibold">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span>';
                }

                // Update modal label
                updateFromCoordsLabel();

                // Update map marker
                if (fromMap) {
                    if (fromMarker) {
                        fromMarker.setLatLng([lat, lng]);
                    } else {
                        fromMarker = L.marker([lat, lng]).addTo(fromMap);
                    }
                    if (centerMap) {
                        fromMap.setView([lat, lng], 13);
                    }
                }
            }

            function setToCoordinates(lat, lng, centerMap = true) {
                toLat = lat;
                toLng = lng;

                const hiddenLat = document.getElementById('edit_to_latitude');
                const hiddenLng = document.getElementById('edit_to_longitude');
                if (hiddenLat) hiddenLat.value = lat.toFixed(6);
                if (hiddenLng) hiddenLng.value = lng.toFixed(6);

                // Update lat/lng inputs in Lat/Lng mode
                const latInput = document.getElementById('to_lat_input');
                const lngInput = document.getElementById('to_lng_input');
                if (latInput) latInput.value = lat.toFixed(6);
                if (lngInput) lngInput.value = lng.toFixed(6);

                // Update label
                const formLabel = document.getElementById('edit-to-coordinates-label');
                if (formLabel) {
                    const coordsLabel = @json(__('messages.location_coords_label') ?? 'Coordinates');
                    formLabel.innerHTML = coordsLabel + ': <span class="fw-semibold">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span>';
                }

                // Update modal label
                updateToCoordsLabel();

                // Update map marker
                if (toMap) {
                    if (toMarker) {
                        toMarker.setLatLng([lat, lng]);
                    } else {
                        toMarker = L.marker([lat, lng]).addTo(toMap);
                    }
                    if (centerMap) {
                        toMap.setView([lat, lng], 13);
                    }
                }
            }

            // Simple geocoding via Nominatim
            async function geocodeQuery(query) {
                const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query);
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!response.ok) {
                    throw new Error('Geocoding request failed');
                }
                const data = await response.json();
                if (!Array.isArray(data) || data.length === 0) {
                    throw new Error('No results found');
                }
                const first = data[0];
                const lat = parseFloat(first.lat);
                const lon = parseFloat(first.lon);
                if (Number.isNaN(lat) || Number.isNaN(lon)) {
                    throw new Error('Invalid coordinates in response');
                }
                return { lat, lng: lon };
            }

            // Initialize From Location Map
            const fromLocationModal = document.getElementById('fromLocationModal');
            if (fromLocationModal) {
                fromLocationModal.addEventListener('shown.bs.modal', function () {
                    const mapContainer = document.getElementById('from-location-map');
                    if (!mapContainer) return;

                    if (!fromMap) {
                        fromMap = L.map(mapContainer).setView([33.5731, -7.5898], 7);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(fromMap);

                        // Load existing coordinates if any
                        const existingLat = document.getElementById('edit_from_latitude')?.value;
                        const existingLng = document.getElementById('edit_from_longitude')?.value;
                        if (existingLat && existingLng) {
                            const lat = parseFloat(existingLat);
                            const lng = parseFloat(existingLng);
                            fromMap.setView([lat, lng], 13);
                            fromMarker = L.marker([lat, lng]).addTo(fromMap);
                            fromLat = lat;
                            fromLng = lng;
                            updateFromCoordsLabel();
                        }

                        fromMap.on('click', function (e) {
                            setFromCoordinates(e.latlng.lat, e.latlng.lng, false);
                        });
                    }
                    
                    // Always invalidate size when modal is shown
                    setTimeout(function() {
                        if (fromMap) {
                            fromMap.invalidateSize();
                        }
                    }, 100);
                    
                    // Sync location name from hidden input to modal input
                    const hiddenLocationName = document.getElementById('edit_from_location_name_hidden')?.value || '';
                    const modalLocationNameInput = document.getElementById('edit_from_location_name');
                    if (modalLocationNameInput) {
                        modalLocationNameInput.value = hiddenLocationName;
                    }
                });
            }

            // Initialize To Location Map
            const toLocationModal = document.getElementById('toLocationModal');
            if (toLocationModal) {
                toLocationModal.addEventListener('shown.bs.modal', function () {
                    const mapContainer = document.getElementById('to-location-map');
                    if (!mapContainer) return;

                    if (!toMap) {
                        toMap = L.map(mapContainer).setView([33.5731, -7.5898], 7);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(toMap);

                        // Load existing coordinates if any
                        const existingLat = document.getElementById('edit_to_latitude')?.value;
                        const existingLng = document.getElementById('edit_to_longitude')?.value;
                        if (existingLat && existingLng) {
                            const lat = parseFloat(existingLat);
                            const lng = parseFloat(existingLng);
                            toMap.setView([lat, lng], 13);
                            toMarker = L.marker([lat, lng]).addTo(toMap);
                            toLat = lat;
                            toLng = lng;
                            updateToCoordsLabel();
                        }

                        toMap.on('click', function (e) {
                            setToCoordinates(e.latlng.lat, e.latlng.lng, false);
                        });
                    }
                    
                    // Always invalidate size when modal is shown
                    setTimeout(function() {
                        if (toMap) {
                            toMap.invalidateSize();
                        }
                    }, 100);
                    
                    // Sync location name from hidden input to modal input
                    const hiddenLocationName = document.getElementById('edit_to_location_name_hidden')?.value || '';
                    const modalLocationNameInput = document.getElementById('edit_to_location_name');
                    if (modalLocationNameInput) {
                        modalLocationNameInput.value = hiddenLocationName;
                    }
                });
            }

            function updateFromCoordsLabel() {
                const label = document.getElementById('from-location-coordinates-label');
                if (label && fromLat !== null && fromLng !== null) {
                    const coordsLabel = @json(__('messages.location_coords_label') ?? 'Coordinates');
                    label.innerHTML = coordsLabel + ': <span class="fw-semibold">' + fromLat.toFixed(6) + ', ' + fromLng.toFixed(6) + '</span>';
                }
            }

            function updateToCoordsLabel() {
                const label = document.getElementById('to-location-coordinates-label');
                if (label && toLat !== null && toLng !== null) {
                    const coordsLabel = @json(__('messages.location_coords_label') ?? 'Coordinates');
                    label.innerHTML = coordsLabel + ': <span class="fw-semibold">' + toLat.toFixed(6) + ', ' + toLng.toFixed(6) + '</span>';
                }
            }

            // Confirm From Location
            document.getElementById('confirmFromLocation')?.addEventListener('click', function () {
                if (fromLat !== null && fromLng !== null) {
                    setFromCoordinates(fromLat, fromLng, false);
                }
                // Sync location name from modal to hidden input
                const modalLocationName = document.getElementById('edit_from_location_name')?.value || '';
                const hiddenLocationName = document.getElementById('edit_from_location_name_hidden');
                if (hiddenLocationName) {
                    hiddenLocationName.value = modalLocationName;
                }
            });

            // Confirm To Location
            document.getElementById('confirmToLocation')?.addEventListener('click', function () {
                if (toLat !== null && toLng !== null) {
                    setToCoordinates(toLat, toLng, false);
                }
                // Sync location name from modal to hidden input
                const modalLocationName = document.getElementById('edit_to_location_name')?.value || '';
                const hiddenLocationName = document.getElementById('edit_to_location_name_hidden');
                if (hiddenLocationName) {
                    hiddenLocationName.value = modalLocationName;
                }
            });

            // Function to fetch route from OSRM and draw it on the map
            async function fetchRouteAndDraw(fromLat, fromLng, toLat, toLng) {
                try {
                    // Use OSRM routing service (public demo server)
                    const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`;
                    
                    const response = await fetch(osrmUrl);
                    if (!response.ok) {
                        throw new Error('Route service unavailable');
                    }
                    
                    const data = await response.json();
                    
                    if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                        throw new Error('No route found');
                    }
                    
                    const route = data.routes[0];
                    const coordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]); // Convert [lng, lat] to [lat, lng]
                    
                    // Remove existing route if any
                    if (blackPointsRoutePolyline) {
                        blackPointsMap.removeLayer(blackPointsRoutePolyline);
                    }
                    if (blackPointsMap._routeShadow) {
                        blackPointsMap.removeLayer(blackPointsMap._routeShadow);
                    }
                    
                    // Add a subtle shadow effect using a slightly thicker, semi-transparent line behind
                    blackPointsMap._routeShadow = L.polyline(coordinates, {
                        color: '#1e40af',
                        weight: 7,
                        opacity: 0.3,
                        lineJoin: 'round',
                        lineCap: 'round'
                    }).addTo(blackPointsMap);
                    
                    // Draw the route with professional styling on top
                    blackPointsRoutePolyline = L.polyline(coordinates, {
                        color: '#2563eb',
                        weight: 5,
                        opacity: 0.8,
                        lineJoin: 'round',
                        lineCap: 'round'
                    }).addTo(blackPointsMap);
                    
                    // Fit map to show entire route with padding
                    const bounds = L.latLngBounds(coordinates);
                    blackPointsMap.fitBounds(bounds, { padding: [50, 50] });
                    
                } catch (error) {
                    console.warn('Failed to fetch route, using straight line:', error);
                    // Fallback to straight line if routing fails
                    if (blackPointsRoutePolyline) {
                        blackPointsMap.removeLayer(blackPointsRoutePolyline);
                    }
                    blackPointsRoutePolyline = L.polyline(
                        [[fromLat, fromLng], [toLat, toLng]],
                        { color: '#2563eb', weight: 4, opacity: 0.7, dashArray: '10, 5' }
                    ).addTo(blackPointsMap);
                    
                    // Fit map to show both points
                    const bounds = L.latLngBounds([[fromLat, fromLng], [toLat, toLng]]);
                    blackPointsMap.fitBounds(bounds, { padding: [50, 50] });
                }
            }

            // Black Points Management
            let blackPointsMap = null;
            let blackPointsMarker = null;
            let blackPointsRoutePolyline = null;
            let blackPointsFromMarker = null;
            let blackPointsToMarker = null;
            let blackPointsLat = null;
            let blackPointsLng = null;
            let blackPointIndex = {{ $journey->blackPoints->count() }};

            const addBlackPointModal = document.getElementById('addBlackPointModal');
            if (addBlackPointModal) {
                addBlackPointModal.addEventListener('shown.bs.modal', function () {
                    // Get journey coordinates
                    const fromLat = parseFloat(document.getElementById('edit_from_latitude')?.value);
                    const fromLng = parseFloat(document.getElementById('edit_from_longitude')?.value);
                    const toLat = parseFloat(document.getElementById('edit_to_latitude')?.value);
                    const toLng = parseFloat(document.getElementById('edit_to_longitude')?.value);

                    if (!fromLat || !fromLng || !toLat || !toLng) {
                        alert(@json(__('messages.location_required') ?? 'Please select both from and to locations first.'));
                        const modal = bootstrap.Modal.getInstance(addBlackPointModal);
                        if (modal) modal.hide();
                        return;
                    }

                    if (!blackPointsMap) {
                        const mapContainer = document.getElementById('black-point-map-modal');
                        blackPointsMap = L.map(mapContainer);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(blackPointsMap);

                        blackPointsMap.on('click', function (e) {
                            blackPointsLat = e.latlng.lat;
                            blackPointsLng = e.latlng.lng;
                            if (blackPointsMarker) {
                                blackPointsMarker.setLatLng([blackPointsLat, blackPointsLng]);
                            } else {
                                blackPointsMarker = L.marker([blackPointsLat, blackPointsLng]).addTo(blackPointsMap);
                            }
                            updateBlackPointModalCoordsLabel();
                        });
                    }
                    
                    // Always invalidate size when modal is shown
                    setTimeout(function() {
                        if (blackPointsMap) {
                            blackPointsMap.invalidateSize();
                        }
                    }, 100);

                    // Clear existing route markers and polyline
                    if (blackPointsRoutePolyline) {
                        blackPointsMap.removeLayer(blackPointsRoutePolyline);
                    }
                    if (blackPointsMap._routeShadow) {
                        blackPointsMap.removeLayer(blackPointsMap._routeShadow);
                        blackPointsMap._routeShadow = null;
                    }
                    if (blackPointsFromMarker) {
                        blackPointsMap.removeLayer(blackPointsFromMarker);
                    }
                    if (blackPointsToMarker) {
                        blackPointsMap.removeLayer(blackPointsToMarker);
                    }

                    // Add From marker (green)
                    blackPointsFromMarker = L.marker([fromLat, fromLng], {
                        icon: L.divIcon({
                            className: 'custom-div-icon',
                            html: "<div style='background-color:#28a745;color:white;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-weight:bold;border:2px solid white;'>F</div>",
                            iconSize: [30, 30],
                            iconAnchor: [15, 15]
                        })
                    }).addTo(blackPointsMap);

                    // Add To marker (red)
                    blackPointsToMarker = L.marker([toLat, toLng], {
                        icon: L.divIcon({
                            className: 'custom-div-icon',
                            html: "<div style='background-color:#dc3545;color:white;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-weight:bold;border:2px solid white;'>T</div>",
                            iconSize: [30, 30],
                            iconAnchor: [15, 15]
                        })
                    }).addTo(blackPointsMap);

                    // Fetch and draw actual route using OSRM
                    fetchRouteAndDraw(fromLat, fromLng, toLat, toLng);
                });

                addBlackPointModal.addEventListener('hidden.bs.modal', function () {
                    // Reset form
                    document.getElementById('black_point_name_input').value = '';
                    document.getElementById('black_point_description_input').value = '';
                    blackPointsLat = null;
                    blackPointsLng = null;
                    if (blackPointsMarker) {
                        blackPointsMap.removeLayer(blackPointsMarker);
                        blackPointsMarker = null;
                    }
                    // Keep route markers and polyline visible for next time
                    const label = document.getElementById('black-point-modal-coordinates-label');
                    if (label) {
                        label.textContent = @json(__('messages.location_map_help') ?? 'Click on the map to set the coordinates.');
                    }
                });
            }

            function updateBlackPointModalCoordsLabel() {
                const label = document.getElementById('black-point-modal-coordinates-label');
                if (label && blackPointsLat !== null && blackPointsLng !== null) {
                    const coordsLabel = @json(__('messages.location_coords_label') ?? 'Coordinates');
                    label.innerHTML = coordsLabel + ': <span class="fw-semibold">' + blackPointsLat.toFixed(6) + ', ' + blackPointsLng.toFixed(6) + '</span>';
                }
            }

            document.getElementById('confirmAddBlackPoint')?.addEventListener('click', function () {
                const name = document.getElementById('black_point_name_input').value.trim();
                const description = document.getElementById('black_point_description_input').value.trim();

                if (!name) {
                    alert(@json(__('messages.name_required') ?? 'Please enter a name for the black point.'));
                    return;
                }

                if (!blackPointsLat || !blackPointsLng) {
                    alert(@json(__('messages.location_required') ?? 'Please select a location on the map.'));
                    return;
                }

                // Add black point to container
                addBlackPointToForm(name, blackPointsLat, blackPointsLng, description);
            });

            function addBlackPointToForm(name, lat, lng, description) {
                const container = document.getElementById('blackPointsContainer');
                const index = blackPointIndex++;

                const blackPointHtml = `
                    <div class="card mb-3 border" data-black-point-index="${index}">
                        <div class="card-body p-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <strong>${name}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">${lat.toFixed(6)}, ${lng.toFixed(6)}</small>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">${description ? description.substring(0, 30) + (description.length > 30 ? '...' : '') : '-'}</small>
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-black-point" data-index="${index}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="black_points[${index}][name]" value="${name}">
                            <input type="hidden" name="black_points[${index}][latitude]" value="${lat.toFixed(6)}">
                            <input type="hidden" name="black_points[${index}][longitude]" value="${lng.toFixed(6)}">
                            <input type="hidden" name="black_points[${index}][description]" value="${description}">
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('beforeend', blackPointHtml);

                // Add remove event listener
                const removeBtn = container.querySelector(`[data-index="${index}"]`);
                if (removeBtn) {
                    removeBtn.addEventListener('click', function () {
                        const card = this.closest('.card');
                        if (card) {
                            card.remove();
                        }
                    });
                }
            }

            // Handle remove buttons for existing black points
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-black-point')) {
                    const card = e.target.closest('.card');
                    if (card) {
                        card.remove();
                    }
                }
            });

            // --- Location mode switching (inside modals) ---
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('button[data-location-mode]');
                if (!btn) return;

                const mode = btn.getAttribute('data-location-mode');
                if (!mode) return;

                const [prefix, subMode] = mode.split('-'); // from-address, to-place, etc.

                // Determine which modal to scope to
                let scope;
                if (prefix === 'from') {
                    scope = document.getElementById('fromLocationModal');
                } else if (prefix === 'to') {
                    scope = document.getElementById('toLocationModal');
                } else {
                    scope = btn.closest('.modal');
                }
                if (!scope) return;

                // Toggle active class in this modal's mode group
                const group = btn.parentElement;
                if (group) {
                    Array.from(group.querySelectorAll('button[data-location-mode]')).forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                }

                // Hide all blocks for this side, show selected one
                const blocks = scope.querySelectorAll('.location-mode-block');
                blocks.forEach(block => block.classList.add('d-none'));

                const selectedId = prefix + '-' + subMode + '-block'; // e.g. from-address-block
                const selectedBlock = scope.querySelector('#' + selectedId);
                if (selectedBlock) {
                    selectedBlock.classList.remove('d-none');
                }
            });

            // --- Geocoding handlers (Address / Place) ---
            async function handleGeocodeClick(inputId, errorId, setCoordsFn) {
                const input = document.getElementById(inputId);
                const errorEl = document.getElementById(errorId);
                if (errorEl) {
                    errorEl.classList.add('d-none');
                    errorEl.textContent = '';
                }
                if (!input) return;
                const query = input.value.trim();
                if (!query) return;

                try {
                    const { lat, lng } = await geocodeQuery(query);
                    setCoordsFn(lat, lng);
                } catch (err) {
                    if (errorEl) {
                        errorEl.textContent = err.message || 'Unable to find this location.';
                        errorEl.classList.remove('d-none');
                    }
                }
            }

            document.getElementById('from_address_search')?.addEventListener('click', function () {
                handleGeocodeClick('from_address_input', 'from_address_error', (lat, lng) => {
                    setFromCoordinates(lat, lng);
                });
            });

            document.getElementById('from_place_search')?.addEventListener('click', function () {
                handleGeocodeClick('from_place_input', 'from_place_error', (lat, lng) => {
                    setFromCoordinates(lat, lng);
                });
            });

            document.getElementById('to_address_search')?.addEventListener('click', function () {
                handleGeocodeClick('to_address_input', 'to_address_error', (lat, lng) => {
                    setToCoordinates(lat, lng);
                });
            });

            document.getElementById('to_place_search')?.addEventListener('click', function () {
                handleGeocodeClick('to_place_input', 'to_place_error', (lat, lng) => {
                    setToCoordinates(lat, lng);
                });
            });

            // --- Lat/Lng Apply handlers ---
            function validateLatLng(lat, lng) {
                if (Number.isNaN(lat) || Number.isNaN(lng)) {
                    return 'Both latitude and longitude are required.';
                }
                if (lat < -90 || lat > 90) {
                    return 'Latitude must be between -90 and 90.';
                }
                if (lng < -180 || lng > 180) {
                    return 'Longitude must be between -180 and 180.';
                }
                return null;
            }

            document.getElementById('from_latlng_apply')?.addEventListener('click', function () {
                const latInput = document.getElementById('from_lat_input');
                const lngInput = document.getElementById('from_lng_input');
                const errorEl = document.getElementById('from_latlng_error');
                if (errorEl) {
                    errorEl.classList.add('d-none');
                    errorEl.textContent = '';
                }
                if (!latInput || !lngInput) return;
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                const err = validateLatLng(lat, lng);
                if (err) {
                    if (errorEl) {
                        errorEl.textContent = err;
                        errorEl.classList.remove('d-none');
                    }
                    return;
                }
                setFromCoordinates(lat, lng);
            });

            document.getElementById('to_latlng_apply')?.addEventListener('click', function () {
                const latInput = document.getElementById('to_lat_input');
                const lngInput = document.getElementById('to_lng_input');
                const errorEl = document.getElementById('to_latlng_error');
                if (errorEl) {
                    errorEl.classList.add('d-none');
                    errorEl.textContent = '';
                }
                if (!latInput || !lngInput) return;
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                const err = validateLatLng(lat, lng);
                if (err) {
                    if (errorEl) {
                        errorEl.textContent = err;
                        errorEl.classList.remove('d-none');
                    }
                    return;
                }
                setToCoordinates(lat, lng);
            });

            // Auto-calculate note (weight × score) for checklist items
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('checklist-weight') || e.target.classList.contains('checklist-score')) {
                    const itemId = e.target.getAttribute('data-item-id');
                    const weightInput = document.getElementById(`checklist_${itemId}_weight`);
                    const scoreInput = document.getElementById(`checklist_${itemId}_score`);
                    const noteInput = document.getElementById(`checklist_${itemId}_note`);
                    const noteHiddenInput = document.getElementById(`checklist_${itemId}_note_hidden`);

                    if (weightInput && scoreInput && noteInput && noteHiddenInput) {
                        const weight = parseFloat(weightInput.value) || 0;
                        const score = parseFloat(scoreInput.value) || 0;
                        const note = weight * score;

                        noteInput.value = note.toFixed(2);
                        noteHiddenInput.value = note.toFixed(2);
                    }
                }
            });

            // Initialize note values on page load
            document.querySelectorAll('.checklist-weight, .checklist-score').forEach(function(input) {
                const itemId = input.getAttribute('data-item-id');
                const weightInput = document.getElementById(`checklist_${itemId}_weight`);
                const scoreInput = document.getElementById(`checklist_${itemId}_score`);
                const noteInput = document.getElementById(`checklist_${itemId}_note`);
                const noteHiddenInput = document.getElementById(`checklist_${itemId}_note_hidden`);

                if (weightInput && scoreInput && noteInput && noteHiddenInput) {
                    const weight = parseFloat(weightInput.value) || 0;
                    const score = parseFloat(scoreInput.value) || 0;
                    const note = weight * score;

                    noteInput.value = note.toFixed(2);
                    noteHiddenInput.value = note.toFixed(2);
                }
            });

            // Handle checklist documents preview
            const checklistDocumentsInput = document.getElementById('checklist_documents');
            const checklistDocumentsPreview = document.getElementById('checklist_documents_preview');
            
            if (checklistDocumentsInput && checklistDocumentsPreview) {
                checklistDocumentsInput.addEventListener('change', function(e) {
                    checklistDocumentsPreview.innerHTML = '';
                    
                    if (e.target.files && e.target.files.length > 0) {
                        Array.from(e.target.files).forEach((file, index) => {
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const col = document.createElement('div');
                                    col.className = 'col-md-3 col-sm-4 col-6';
                                    col.innerHTML = `
                                        <div class="position-relative">
                                            <img src="${e.target.result}" 
                                                 alt="Preview ${index + 1}" 
                                                 class="img-thumbnail w-100" 
                                                 style="height: 150px; object-fit: cover;">
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-preview-image" 
                                                    data-index="${index}"
                                                    title="${@json(__('messages.remove_picture') ?? 'Remove')}">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    `;
                                    checklistDocumentsPreview.appendChild(col);
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    }
                });
                
                // Handle remove preview image
                checklistDocumentsPreview.addEventListener('click', function(e) {
                    if (e.target.closest('.remove-preview-image')) {
                        const button = e.target.closest('.remove-preview-image');
                        const index = parseInt(button.getAttribute('data-index'));
                        const dt = new DataTransfer();
                        const files = Array.from(checklistDocumentsInput.files);
                        files.forEach((file, i) => {
                            if (i !== index) {
                                dt.items.add(file);
                            }
                        });
                        checklistDocumentsInput.files = dt.files;
                        button.closest('.col-md-3').remove();
                    }
                });
            }

            // Form validation
            const form = document.getElementById('editJourneyForm');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const fromLat = document.getElementById('edit_from_latitude')?.value;
                    const fromLng = document.getElementById('edit_from_longitude')?.value;
                    const toLat = document.getElementById('edit_to_latitude')?.value;
                    const toLng = document.getElementById('edit_to_longitude')?.value;

                    if (!fromLat || !fromLng || !toLat || !toLng) {
                        e.preventDefault();
                        alert(@json(__('messages.location_required') ?? 'Please select both from and to locations on the map before submitting.'));
                        return false;
                    }
                });
            }
        }); // End of waitForLeaflet callback
    }); // End of DOMContentLoaded
    </script>
</x-app-layout>

