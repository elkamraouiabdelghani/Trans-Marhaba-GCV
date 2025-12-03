@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h2 class="mb-1 fw-bold text-dark fs-4">
                    <i class="bi bi-pencil me-2 text-primary"></i>
                    {{ __('messages.edit_rest_point') ?? 'Edit Rest Point' }}
                </h2>
            </div>
            <div>
                <a href="{{ route('rest-points.show', $restPoint) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    {{ __('messages.back_to_show') ?? 'Back to details' }}
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-10">
                <div class="card border-0 shadow-sm">
                    <form action="{{ route('rest-points.update', $restPoint) }}" method="POST" id="editRestPointForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body p-4">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

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
                                <div class="col-md-6">
                                    <label for="edit_name" class="form-label fw-semibold">
                                        {{ __('messages.name') ?? 'Name' }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="edit_name" name="name"
                                           value="{{ old('name', $restPoint->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="edit_type" class="form-label fw-semibold">
                                        {{ __('messages.type') ?? 'Type' }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('type') is-invalid @enderror"
                                            id="edit_type" name="type" required>
                                        <option value="">{{ __('messages.select_type') ?? 'Select Type' }}</option>
                                        @foreach($types as $key => $label)
                                            <option value="{{ $key }}" @selected(old('type', $restPoint->type) === $key)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        {{ __('messages.location') ?? 'Location' }} <span class="text-danger">*</span>
                                    </label>
                                    <div id="edit-location-map"
                                         style="width: 100%; height: 350px; border-radius: 0.5rem; border: 1px solid #dee2e6; margin-bottom: 0.5rem;"></div>
                                    <input type="hidden" name="latitude" id="edit_latitude"
                                           value="{{ old('latitude', $restPoint->latitude) }}" required>
                                    <input type="hidden" name="longitude" id="edit_longitude"
                                           value="{{ old('longitude', $restPoint->longitude) }}" required>
                                    <small class="text-muted d-block mt-1" id="edit-location-coordinates-label">
                                        @php
                                            $lat = old('latitude', $restPoint->latitude);
                                            $lng = old('longitude', $restPoint->longitude);
                                        @endphp
                                        @if($lat && $lng)
                                            {{ __('messages.location_coords_label') ?? 'Coordinates' }}:
                                            <span class="fw-semibold">{{ $lat }}, {{ $lng }}</span>
                                        @else
                                            {{ __('messages.location_map_help') ?? 'Click on the map to set the coordinates.' }}
                                        @endif
                                    </small>
                                    @error('latitude')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('longitude')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="edit_description" class="form-label fw-semibold">
                                        {{ __('messages.description') ?? 'Description' }}
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="edit_description" name="description" rows="3">{{ old('description', $restPoint->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Checklist Section --}}
                            @if(isset($categories) && $categories->count() > 0)
                                <hr class="my-4">
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="fw-bold mb-3">
                                            <i class="bi bi-list-check me-2 text-primary"></i>
                                            {{ __('messages.checklist') ?? 'Checklist' }}
                                            <span class="text-danger">*</span>
                                        </h5>
                                        <p class="text-muted mb-4">
                                            {{ __('messages.checklist_required_help') ?? 'Please complete the checklist for this rest point. All items are required.' }}
                                        </p>

                                        <div class="accordion" id="checklistAccordion">
                                            @foreach($categories as $index => $category)
                                                <div class="accordion-item mb-2 border rounded">
                                                    <h2 class="accordion-header" id="heading{{ $category->id }}">
                                                        <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" 
                                                                type="button" 
                                                                data-bs-toggle="collapse" 
                                                                data-bs-target="#collapse{{ $category->id }}" 
                                                                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                                                aria-controls="collapse{{ $category->id }}">
                                                            <i class="bi bi-list-check me-2 text-primary"></i>
                                                            <strong>{{ $category->name }}</strong>
                                                            <span class="badge bg-info bg-opacity-10 text-info ms-2">
                                                                {{ $category->items->count() }} {{ __('messages.items') ?? 'items' }}
                                                            </span>
                                                        </button>
                                                    </h2>
                                                    <div id="collapse{{ $category->id }}" 
                                                         class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                                                         aria-labelledby="heading{{ $category->id }}" 
                                                         data-bs-parent="#checklistAccordion">
                                                        <div class="accordion-body p-4">
                                                            @if($category->items->count() > 0)
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered table-hover">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th style="width: 50%;">{{ __('messages.item') ?? 'Item' }}</th>
                                                                                <th style="width: 25%;" class="text-center">{{ __('messages.yes') ?? 'Yes' }} / {{ __('messages.no') ?? 'No' }}</th>
                                                                                <th style="width: 25%;">{{ __('messages.comment') ?? 'Comment' }}</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($category->items as $item)
                                                                                @php
                                                                                    $existingAnswer = $answersByItemId[$item->id] ?? null;
                                                                                    $oldIsChecked = old("checklist.{$item->id}.is_checked");
                                                                                    $isChecked = $oldIsChecked !== null ? $oldIsChecked : ($existingAnswer ? ($existingAnswer->is_checked ? '1' : '0') : null);
                                                                                    $oldComment = old("checklist.{$item->id}.comment");
                                                                                    $comment = $oldComment !== null ? $oldComment : ($existingAnswer ? $existingAnswer->comment : '');
                                                                                @endphp
                                                                                <tr>
                                                                                    <td class="align-middle">
                                                                                        <label class="mb-0 fw-semibold">{{ $item->label }}</label>
                                                                                    </td>
                                                                                    <td class="text-center align-middle">
                                                                                        <div class="btn-group" role="group" data-toggle="buttons">
                                                                                            <input type="radio" 
                                                                                                   class="btn-check" 
                                                                                                   name="checklist[{{ $item->id }}][is_checked]" 
                                                                                                   id="item_{{ $item->id }}_yes" 
                                                                                                   value="1" 
                                                                                                   required
                                                                                                   {{ $isChecked === '1' ? 'checked' : '' }}>
                                                                                            <label class="btn btn-outline-success btn-sm" for="item_{{ $item->id }}_yes">
                                                                                                <i class="bi bi-check-circle"></i> {{ __('messages.yes') ?? 'Yes' }}
                                                                                            </label>

                                                                                            <input type="radio" 
                                                                                                   class="btn-check" 
                                                                                                   id="item_{{ $item->id }}_no" 
                                                                                                   name="checklist[{{ $item->id }}][is_checked]" 
                                                                                                   value="0" 
                                                                                                   required
                                                                                                   {{ $isChecked === '0' ? 'checked' : '' }}>
                                                                                            <label class="btn btn-outline-danger btn-sm" for="item_{{ $item->id }}_no">
                                                                                                <i class="bi bi-x-circle"></i> {{ __('messages.no') ?? 'No' }}
                                                                                            </label>
                                                                                        </div>
                                                                                        @error("checklist.{$item->id}.is_checked")
                                                                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                                                                        @enderror
                                                                                    </td>
                                                                                    <td class="align-middle">
                                                                                        <textarea class="form-control form-control-sm" 
                                                                                                  name="checklist[{{ $item->id }}][comment]" 
                                                                                                  id="item_{{ $item->id }}_comment" 
                                                                                                  rows="2" 
                                                                                                  placeholder="{{ __('messages.comment_optional') ?? 'Comment (optional)' }}">{{ $comment }}</textarea>
                                                                                        @error("checklist.{$item->id}.comment")
                                                                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                                                                        @enderror
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @else
                                                                <div class="alert alert-info mb-0">
                                                                    <i class="bi bi-info-circle me-2"></i>
                                                                    {{ __('messages.no_items_in_category') ?? 'No active items in this category.' }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                <hr class="my-4">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    {{ __('messages.no_checklist_categories') ?? 'No active checklist categories found. Please create categories first.' }}
                                </div>
                            @endif

                            {{-- Checklist Status and General Comment --}}
                            @if(isset($categories) && $categories->count() > 0)
                                <hr class="my-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="row g-3 mb-3">
                                            <div class="col-12">
                                                <label for="checklist_notes" class="form-label fw-semibold">
                                                    {{ __('messages.general_comment') ?? 'General Comment' }}
                                                </label>
                                                <textarea class="form-control @error('checklist_notes') is-invalid @enderror"
                                                          id="checklist_notes" 
                                                          name="checklist_notes" 
                                                          rows="4" 
                                                          placeholder="{{ __('messages.general_comment_placeholder') ?? 'Enter any general comments or notes about this checklist...' }}">{{ old('checklist_notes', $existingChecklist->notes ?? '') }}</textarea>
                                                @error('checklist_notes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">
                                                    {{ __('messages.status') ?? 'Status' }} <span class="text-danger">*</span>
                                                </label>
                                                <div class="btn-group w-100" role="group">
                                                    @php
                                                        $currentStatus = old('checklist_status', $existingChecklist->status ?? 'accepted');
                                                    @endphp
                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="checklist_status" 
                                                           id="checklist_status_accepted" 
                                                           value="accepted" 
                                                           required
                                                           {{ $currentStatus === 'accepted' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-success" for="checklist_status_accepted">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        {{ __('messages.accepted') ?? 'Accepted' }}
                                                    </label>

                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="checklist_status" 
                                                           id="checklist_status_rejected" 
                                                           value="rejected" 
                                                           required
                                                           {{ $currentStatus === 'rejected' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-danger" for="checklist_status_rejected">
                                                        <i class="bi bi-x-circle me-1"></i>
                                                        {{ __('messages.rejected') ?? 'Rejected' }}
                                                    </label>
                                                </div>
                                                @error('checklist_status')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Documents/Pictures Upload --}}
                                        <div class="row g-3 mt-2">
                                            <div class="col-12">
                                                <label for="checklist_documents" class="form-label fw-semibold">
                                                    <i class="bi bi-images me-2"></i>
                                                    {{ __('messages.pictures') ?? 'Pictures' }}
                                                </label>
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
                                                
                                                {{-- Display existing pictures --}}
                                                @if(isset($existingChecklist) && $existingChecklist->documents && count($existingChecklist->documents) > 0)
                                                    <div class="mt-3">
                                                        <label class="form-label fw-semibold small">
                                                            {{ __('messages.existing_pictures') ?? 'Existing Pictures' }}
                                                        </label>
                                                        <div class="row g-2" id="existing_pictures_preview">
                                                            @foreach($existingChecklist->documents as $index => $document)
                                                                @php
                                                                    $docUrl = route('rest-points.checklists.document', ['encoded' => base64_encode($document)]);
                                                                @endphp
                                                                <div class="col-md-3 col-sm-4 col-6 existing-picture-item" data-index="{{ $index }}">
                                                                    <div class="position-relative">
                                                                        <img src="{{ $docUrl }}"
                                                                             alt="Picture {{ $index + 1 }}"
                                                                             class="img-thumbnail w-100"
                                                                             style="height: 150px; object-fit: cover;">
                                                                        <button type="button" 
                                                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-existing-picture" 
                                                                                data-index="{{ $index }}"
                                                                                title="{{ __('messages.remove_picture') ?? 'Remove' }}">
                                                                            <i class="bi bi-x"></i>
                                                                        </button>
                                                                    </div>
                                                                    <input type="hidden" name="existing_documents[]" value="{{ $document }}">
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                {{-- Preview area for newly selected images --}}
                                                <div id="checklist_documents_preview" class="mt-3 row g-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <hr class="my-4">
                        <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2 pb-3 px-4">
                            <a href="{{ $backUrl ?? route('rest-points.index') }}" class="btn btn-outline-secondary">
                                {{ __('messages.cancel') ?? 'Cancel' }}
                            </a>
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-save me-1"></i>
                                {{ __('messages.update') ?? 'Update' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

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
    if (!window.L) {
        console.error('Leaflet library not loaded');
        return;
    }

    const mapContainer = document.getElementById('edit-location-map');
    if (!mapContainer) {
        return;
    }

    const latInput = document.getElementById('edit_latitude');
    const lngInput = document.getElementById('edit_longitude');
    let initialLat = 33.5731;
    let initialLng = -7.5898;
    let initialZoom = 7;

    if (latInput && lngInput && latInput.value && lngInput.value) {
        initialLat = parseFloat(latInput.value);
        initialLng = parseFloat(lngInput.value);
        initialZoom = 13;
    }

    const map = L.map(mapContainer).setView([initialLat, initialLng], initialZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    let marker = null;
    const coordsLabel = document.getElementById('edit-location-coordinates-label');

    if (latInput && lngInput && latInput.value && lngInput.value) {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        marker = L.marker([lat, lng]).addTo(map);
    }

    function updateCoords(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }

        if (latInput) {
            latInput.value = lat.toFixed(6);
        }
        if (lngInput) {
            lngInput.value = lng.toFixed(6);
        }
        if (coordsLabel) {
            const label = @json(__('messages.location_coords_label') ?? 'Coordinates');
            coordsLabel.innerHTML = label + ': <span class="fw-semibold">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span>';
        }
    }

    map.on('click', function (e) {
        updateCoords(e.latlng.lat, e.latlng.lng);
    });

    const form = document.getElementById('editRestPointForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const lat = latInput ? latInput.value : '';
            const lng = lngInput ? lngInput.value : '';

            if (!lat || !lng) {
                e.preventDefault();
                alert(@json(__('messages.location_required') ?? 'Please select a location on the map before submitting.'));
                return false;
            }

            // Validate checklist - ensure all items have yes/no selected
            const checklistInputs = form.querySelectorAll('input[name^="checklist["][name$="[is_checked]"]');
            const unansweredItems = [];
            
            checklistInputs.forEach(function(input) {
                const itemId = input.name.match(/checklist\[(\d+)\]/)[1];
                const itemName = form.querySelector('label[for="item_' + itemId + '_yes"]')?.closest('tr')?.querySelector('label.fw-semibold')?.textContent?.trim() || 'Item ' + itemId;
                
                // Check if this item has a checked radio button
                const itemGroup = form.querySelectorAll('input[name="' + input.name + '"]');
                const isAnswered = Array.from(itemGroup).some(radio => radio.checked);
                
                if (!isAnswered) {
                    unansweredItems.push(itemName);
                }
            });

            if (unansweredItems.length > 0) {
                e.preventDefault();
                const message = @json(__('messages.checklist_answer_required') ?? 'Please provide a Yes/No answer for all checklist items.') + '\n\n' + 
                               unansweredItems.map(item => '- ' + item).join('\n');
                alert(message);
                
                // Scroll to first unanswered item
                const firstUnanswered = form.querySelector('input[name^="checklist["][name$="[is_checked]"]:not(:checked)');
                if (firstUnanswered) {
                    firstUnanswered.closest('.accordion-item')?.querySelector('.accordion-button')?.click();
                    firstUnanswered.closest('tr')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                return false;
            }
        });
    }

    // Handle checklist documents preview
    const documentsInput = document.getElementById('checklist_documents');
    const documentsPreview = document.getElementById('checklist_documents_preview');
    
    if (documentsInput && documentsPreview) {
        documentsInput.addEventListener('change', function(e) {
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
                            documentsPreview.appendChild(col);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
        
        // Handle remove preview image
        documentsPreview.addEventListener('click', function(e) {
            if (e.target.closest('.remove-preview-image')) {
                const button = e.target.closest('.remove-preview-image');
                const index = parseInt(button.getAttribute('data-index'));
                const dt = new DataTransfer();
                const files = Array.from(documentsInput.files);
                files.forEach((file, i) => {
                    if (i !== index) {
                        dt.items.add(file);
                    }
                });
                documentsInput.files = dt.files;
                button.closest('.col-md-3').remove();
            }
        });
    }

    // Handle remove existing pictures
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-existing-picture')) {
            const button = e.target.closest('.remove-existing-picture');
            const item = button.closest('.existing-picture-item');
            const hiddenInput = item.querySelector('input[type="hidden"]');
            if (hiddenInput) {
                hiddenInput.remove();
            }
            item.remove();
        }
    });
});
</script>
