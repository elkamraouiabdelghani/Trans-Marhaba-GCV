<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">{{ __('messages.drivers') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('drivers.show', $driver) }}">{{ $driver->full_name ?? __('messages.driver_number') . $driver->id }}</a></li>
                <li class="breadcrumb-item active">{{ __('messages.edit') }}</li>
            </ol>
        </nav>

        <!-- Success/Error Messages -->
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

        <!-- Edit Form -->
        <div class="card border-0 shadow-sm col-md-10 mx-auto">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>
                        {{ __('messages.edit_driver') }}: {{ $driver->full_name ?? __('messages.driver_number') . $driver->id }}
                    </h5>
                    <a href="{{ route('drivers.show', $driver) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('drivers.update', $driver) }}" method="POST" enctype="multipart/form-data" id="driverEditForm">
                    @csrf
                    @method('PATCH')
                    <div id="removedDocumentsContainer"></div>
                    <div id="existingDocumentsContainer"></div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ __('messages.validation_errors') ?? 'Please fix the following errors:' }}
                            </h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Personal Information -->
                    <h6 class="text-primary mb-3 border-bottom pb-2">
                        <i class="bi bi-person me-2"></i>
                        {{ __('messages.personal_information') }}
                    </h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('full_name') is-invalid @enderror" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="{{ old('full_name', $driver->full_name ?? '') }}" 
                                   required>
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">{{ __('messages.email') }}</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $driver->email ?? '') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">{{ __('messages.phone') }}</label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $driver->phone ?? '') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="cin" class="form-label">{{ __('messages.cin') }}</label>
                            <input type="text" 
                                   class="form-control @error('cin') is-invalid @enderror" 
                                   id="cin" 
                                   name="cin" 
                                   value="{{ old('cin', $driver->cin ?? '') }}">
                            @error('cin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="date_of_birth" class="form-label">{{ __('messages.date_of_birth') }}</label>
                            <input type="date" 
                                   class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   id="date_of_birth" 
                                   name="date_of_birth" 
                                   value="{{ old('date_of_birth', $driver->date_of_birth ? $driver->date_of_birth->format('Y-m-d') : '') }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">{{ __('messages.city') }}</label>
                            <input type="text" 
                                   class="form-control @error('city') is-invalid @enderror" 
                                   id="city" 
                                   name="city" 
                                   value="{{ old('city', $driver->city ?? '') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="profile_photo" class="form-label">{{ __('messages.profile_photo') }}</label>
                            <input type="file"
                                   class="form-control @error('profile_photo') is-invalid @enderror"
                                   id="profile_photo"
                                   name="profile_photo"
                                   accept="image/*">
                            @error('profile_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($driver->profile_photo_path)
                                <div class="d-flex align-items-center gap-3 mt-2">
                                    @if($driver->profile_photo_path)
                                    <img src="{{ $driver->profile_photo_path ? asset('uploads/' . $driver->profile_photo_path) : asset('images/default-profile.png') }}" 
                                         alt="{{ $driver->full_name ?? __('messages.profile_photo') }}" 
                                         class="rounded-circle" 
                                         style="width: 70px; height: 70px; object-fit: cover;">
                                    @else
                                        <i class="bi bi-person-gear text-primary" style="font-size: 4rem;"></i>
                                    @endif
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="remove_photo" name="remove_photo">
                                        <label class="form-check-label" for="remove_photo">
                                            {{ __('messages.remove_photo') }}
                                        </label>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-12 mb-3">
                            <label for="address" class="form-label">{{ __('messages.address') }}</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="2">{{ old('address', $driver->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- License Information -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-card-text me-2"></i>
                        {{ __('messages.license_information') }}
                    </h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="license_number" class="form-label">{{ __('messages.license_number') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('license_number') is-invalid @enderror" 
                                   id="license_number" 
                                   name="license_number" 
                                   value="{{ old('license_number', $driver->license_number ?? '') }}" 
                                   required>
                            @error('license_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="license_type" class="form-label">{{ __('messages.license_type') }}</label>
                            <input type="text" 
                                   class="form-control @error('license_type') is-invalid @enderror" 
                                   id="license_type" 
                                   name="license_type" 
                                   value="{{ old('license_type', $driver->license_type ?? '') }}">
                            @error('license_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="license_issue_date" class="form-label">{{ __('messages.license_issue_date') }}</label>
                            <input type="date" 
                                   class="form-control @error('license_issue_date') is-invalid @enderror" 
                                   id="license_issue_date" 
                                   name="license_issue_date" 
                                   value="{{ old('license_issue_date', $driver->license_issue_date ? $driver->license_issue_date->format('Y-m-d') : '') }}">
                            @error('license_issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="license_class" class="form-label">{{ __('messages.license_class') }}</label>
                            <input type="text" 
                                   class="form-control @error('license_class') is-invalid @enderror" 
                                   id="license_class" 
                                   name="license_class" 
                                   value="{{ old('license_class', $driver->license_class ?? '') }}">
                            @error('license_class')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Medical & Formation Information -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-heart-pulse me-2"></i>
                        {{ __('messages.medical_formation') }}
                    </h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="visite_medical" class="form-label">{{ __('messages.visite_medical') }}</label>
                            <input type="date" 
                                   class="form-control @error('visite_medical') is-invalid @enderror" 
                                   id="visite_medical" 
                                   name="visite_medical" 
                                   value="{{ old('visite_medical', $driver->visite_medical ? $driver->visite_medical->format('Y-m-d') : '') }}">
                            @error('visite_medical')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="visite_yeux" class="form-label">{{ __('messages.visite_yeux') }}</label>
                            <input type="date" 
                                   class="form-control @error('visite_yeux') is-invalid @enderror" 
                                   id="visite_yeux" 
                                   name="visite_yeux" 
                                   value="{{ old('visite_yeux', $driver->visite_yeux ? $driver->visite_yeux->format('Y-m-d') : '') }}">
                            @error('visite_yeux')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="formation_imd" class="form-label">{{ __('messages.formation_imd') }}</label>
                            <input type="date" 
                                   class="form-control @error('formation_imd') is-invalid @enderror" 
                                   id="formation_imd" 
                                   name="formation_imd" 
                                   value="{{ old('formation_imd', $driver->formation_imd ? $driver->formation_imd->format('Y-m-d') : '') }}">
                            @error('formation_imd')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="formation_16_module" class="form-label">{{ __('messages.formation_16_module') }}</label>
                            <input type="date" 
                                   class="form-control @error('formation_16_module') is-invalid @enderror" 
                                   id="formation_16_module" 
                                   name="formation_16_module" 
                                   value="{{ old('formation_16_module', $driver->formation_16_module ? $driver->formation_16_module->format('Y-m-d') : '') }}">
                            @error('formation_16_module')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Administrative Information -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-briefcase me-2"></i>
                        {{ __('messages.administrative_information') }}
                    </h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="date_integration" class="form-label">{{ __('messages.date_integration') }}</label>
                            <input type="date" 
                                   class="form-control @error('date_integration') is-invalid @enderror" 
                                   id="date_integration" 
                                   name="date_integration" 
                                   value="{{ old('date_integration', $driver->date_integration ? $driver->date_integration->format('Y-m-d') : '') }}">
                            @error('date_integration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">{{ __('messages.status') }}</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status">
                                <option value="">{{ __('messages.select_option') }}</option>
                                <option value="active" {{ old('status', $driver->status) === 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                <option value="inactive" {{ old('status', $driver->status) === 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="n_cnss" class="form-label">{{ __('messages.n_cnss') }}</label>
                            <input type="text" 
                                   class="form-control @error('n_cnss') is-invalid @enderror" 
                                   id="n_cnss" 
                                   name="n_cnss" 
                                   value="{{ old('n_cnss', $driver->n_cnss ?? '') }}">
                            @error('n_cnss')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="rib" class="form-label">{{ __('messages.rib') }}</label>
                            <input type="text" 
                                   class="form-control @error('rib') is-invalid @enderror" 
                                   id="rib" 
                                   name="rib" 
                                   value="{{ old('rib', $driver->rib ?? '') }}">
                            @error('rib')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="attestation_travail" class="form-label">{{ __('messages.attestation_travail') }}</label>
                            <input type="text" 
                                   class="form-control @error('attestation_travail') is-invalid @enderror" 
                                   id="attestation_travail" 
                                   name="attestation_travail" 
                                   value="{{ old('attestation_travail', $driver->attestation_travail ?? '') }}">
                            @error('attestation_travail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="carte_profession" class="form-label">{{ __('messages.carte_profession') }}</label>
                            <input type="text" 
                                   class="form-control @error('carte_profession') is-invalid @enderror" 
                                   id="carte_profession" 
                                   name="carte_profession" 
                                   value="{{ old('carte_profession', $driver->carte_profession ?? '') }}">
                            @error('carte_profession')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Assignment Information -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-truck me-2"></i>
                        {{ __('messages.assignment_information') }}
                    </h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="flotte_id" class="form-label">{{ __('messages.flotte') }}</label>
                            <select class="form-select @error('flotte_id') is-invalid @enderror" 
                                    id="flotte_id" 
                                    name="flotte_id">
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($flottes as $flotte)
                                    <option value="{{ $flotte->id }}" {{ old('flotte_id', $driver->flotte_id) == $flotte->id ? 'selected' : '' }}>
                                        {{ $flotte->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('flotte_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="assigned_vehicle_id" class="form-label">{{ __('messages.assigned_vehicle') }}</label>
                            <select class="form-select @error('assigned_vehicle_id') is-invalid @enderror" 
                                    id="assigned_vehicle_id" 
                                    name="assigned_vehicle_id">
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ old('assigned_vehicle_id', $driver->assigned_vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                        {{ $vehicle->license_plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_vehicle_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Supporting Documents -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        {{ __('messages.driver_documents') }}
                    </h6>
                    <div class="mb-4">
                        @php
                            $documents = is_array($driver->documents) ? $driver->documents : [];
                        @endphp
                        @if(!empty($documents))
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('messages.uploaded_documents') }} ({{ count($documents) }})</label>
                                <div class="row g-3" id="existingDocuments">
                                    @foreach($documents as $index => $doc)
                                        @php
                                            $path = is_array($doc) ? ($doc['path'] ?? $doc['file_path'] ?? '') : $doc;
                                            $name = is_array($doc) ? ($doc['name'] ?? basename($path)) : basename($path);
                                            $extension = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : '';
                                            $isImage = in_array($extension, ['png','jpg','jpeg','gif','bmp','webp']);
                                            $isUrl = $path && filter_var($path, FILTER_VALIDATE_URL);
                                            $previewUrl = $path
                                                ? ($isUrl ? $path : Storage::disk('uploads')->url($path))
                                                : null;
                                            $token = $path ? rtrim(strtr(base64_encode($path), '+/', '-_'), '=') : null;
                                            $viewUrl = $token ? route('drivers.documents.show', ['driver' => $driver->id, 'document' => $token]) : null;
                                            $downloadUrl = $token ? route('drivers.documents.show', ['driver' => $driver->id, 'document' => $token, 'download' => 1]) : null;
                                            $normalizedDoc = [
                                                'name' => $name,
                                                'original_name' => $doc['original_name'] ?? $name,
                                                'path' => $path,
                                                'uploaded_at' => $doc['uploaded_at'] ?? null,
                                                'extension' => $extension,
                                            ];
                                        @endphp
                                        <div class="col-lg-3 col-md-4 col-sm-6 col-12 existing-document" id="document-{{ $index }}" data-document='@json($normalizedDoc)'>
                                            <div class="document-card h-100">
                                                <div class="document-image-container">
                                                    @if($isImage && $previewUrl)
                                                        <img src="{{ $previewUrl }}" alt="{{ $name }}" class="document-image">
                                                    @else
                                                        <div class="document-placeholder">
                                                            <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div class="document-overlay">
                                                        <div class="document-actions">
                                                            @if($viewUrl)
                                                                <a href="{{ $viewUrl }}" target="_blank" class="btn btn-sm btn-light me-1" title="{{ __('messages.view') }}">
                                                                    <i class="bi bi-eye"></i>
                                                                </a>
                                                            @endif
                                                            @if($downloadUrl)
                                                                <a href="{{ $downloadUrl }}" class="btn btn-sm btn-light me-1" title="{{ __('messages.download') }}">
                                                                    <i class="bi bi-download"></i>
                                                                </a>
                                                            @endif
                                                            <button type="button" class="btn btn-sm btn-danger" data-action="remove-document" title="{{ __('messages.delete') }}">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="document-info">
                                                    <h6 class="document-name" title="{{ $name }}">{{ $name }}</h6>
                                                    <small class="text-muted d-block">{{ __('messages.uploaded') }}:
                                                        {{ $doc['uploaded_at'] ? \Carbon\Carbon::parse($doc['uploaded_at'])->format('d/m/Y H:i') : __('messages.unknown_date') }}
                                                    </small>
                                                    <small class="text-muted text-uppercase">{{ $extension ?: '—' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <label class="form-label fw-semibold">{{ __('messages.upload_documents') }}</label>
                        <div class="file-upload-area @error('documents') is-invalid @enderror"
                             id="fileUploadArea"
                             ondrop="handleDrop(event)"
                             ondragover="handleDragOver(event)"
                             ondragleave="handleDragLeave(event)"
                             onclick="document.getElementById('documents').click()">

                            <input type="file"
                                   class="d-none @error('documents') is-invalid @enderror @error('documents.*') is-invalid @enderror"
                                   id="documents"
                                   name="documents[]"
                                   multiple
                                   onchange="handleFileSelect(this)">

                            <div class="file-upload-content text-center">
                                <div class="file-upload-icon mb-3">
                                    <i class="bi bi-cloud-upload display-5 text-dark"></i>
                                </div>
                                <h6 class="text-dark mb-2">{{ __('messages.drag_drop') ?? 'Drag & drop files here' }}</h6>
                                <p class="text-muted mb-3">{{ __('messages.or_click_to_browse') ?? 'or click to browse' }}</p>
                                <div class="file-types">
                                    <span class="badge bg-light text-dark">{{ __('messages.multiple_files_allowed') }}</span>
                                </div>
                            </div>

                            <div class="file-upload-drag-over" style="display: none;">
                                <div class="drag-over-content text-center text-white">
                                    <i class="bi bi-cloud-upload display-5 mb-2"></i>
                                    <p class="mb-0">{{ __('messages.drop_to_upload') ?? 'Drop to upload' }}</p>
                                </div>
                            </div>
                        </div>
                        @error('documents')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('documents.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <div id="filePreview" class="mt-3" style="display: none;">
                            <h6 class="text-dark fw-semibold mb-2">{{ __('messages.selected_files') ?? 'Selected files' }}</h6>
                            <div class="row g-2" id="previewContainer"></div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-sticky me-2"></i>
                        {{ __('messages.notes') }}
                    </h6>
                    <div class="mb-4">
                        <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="4">{{ old('notes', $driver->notes ?? '') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-center gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('drivers.show', $driver) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>
                            {{ __('messages.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-dark" id="submitBtn">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('driverEditForm');
            const submitBtn = document.getElementById('submitBtn');

            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __('messages.saving') ?? "Saving..." }}';
                    ensureExistingDocumentsIncluded();
                });
            }

            document.addEventListener('click', function(event) {
                const target = event.target.closest('[data-action="remove-document"]');
                if (!target) return;

                const documentCard = target.closest('.existing-document');
                if (!documentCard) return;

                const docData = documentCard.getAttribute('data-document');
                const documentInfo = docData ? JSON.parse(docData) : null;
                if (!documentInfo) return;

                removeExistingDocument(documentCard, documentInfo);
            });
        });

        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            const uploadArea = document.getElementById('fileUploadArea');
            uploadArea.classList.add('drag-over');
            uploadArea.querySelector('.file-upload-drag-over').style.display = 'flex';
            uploadArea.querySelector('.file-upload-content').style.display = 'none';
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            const uploadArea = document.getElementById('fileUploadArea');
            uploadArea.classList.remove('drag-over');
            uploadArea.querySelector('.file-upload-drag-over').style.display = 'none';
            uploadArea.querySelector('.file-upload-content').style.display = 'block';
        }

        function handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            const files = e.dataTransfer.files;
            const fileInput = document.getElementById('documents');
            fileInput.files = files;
            handleFileSelect(fileInput);
            handleDragLeave(e);
        }

        function handleFileSelect(input) {
            const files = Array.from(input.files || []);
            const previewContainer = document.getElementById('previewContainer');
            const previewWrapper = document.getElementById('filePreview');

            if (!previewContainer || !previewWrapper) return;

            previewContainer.innerHTML = '';

            if (!files.length) {
                previewWrapper.style.display = 'none';
                return;
            }

            previewWrapper.style.display = 'block';

            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 col-sm-4 col-6';
                    col.innerHTML = `
                        <div class="document-card new-document h-100">
                            <div class="document-image-container">
                                ${file.type.startsWith('image/')
                                    ? `<img src="${event.target.result}" alt="${file.name}" class="document-image">`
                                    : `<div class="document-placeholder"><i class="bi bi-file-earmark-text display-4 text-muted"></i></div>`
                                }
                                <div class="document-overlay">
                                    <div class="document-actions">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeSelectedFile(${index})" title="{{ __('messages.delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="document-info">
                                <h6 class="document-name" title="${file.name}">${file.name}</h6>
                                <small class="text-muted">${(file.size / 1024).toFixed(1)} KB</small>
                            </div>
                        </div>
                    `;
                    previewContainer.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        }

        function removeSelectedFile(index) {
            const fileInput = document.getElementById('documents');
            if (!fileInput || !fileInput.files.length) return;

            const dt = new DataTransfer();
            Array.from(fileInput.files).forEach((file, fileIndex) => {
                if (fileIndex !== index) {
                    dt.items.add(file);
                }
            });
            fileInput.files = dt.files;

            if (!dt.files.length) {
                document.getElementById('filePreview').style.display = 'none';
            } else {
                handleFileSelect(fileInput);
            }
        }

        function removeExistingDocument(element, documentInfo) {
            addToRemovedDocuments(documentInfo);
            element.style.transition = 'all 0.3s ease';
            element.style.transform = 'scale(0.95)';
            element.style.opacity = '0';
            setTimeout(() => element.remove(), 200);
        }

        function addToRemovedDocuments(documentInfo) {
            const container = document.getElementById('removedDocumentsContainer');
            if (!container) return;

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'removed_documents[]';
            hiddenInput.value = JSON.stringify(documentInfo);
            container.appendChild(hiddenInput);
        }

        function ensureExistingDocumentsIncluded() {
            const container = document.getElementById('existingDocumentsContainer');
            if (!container) return;

            container.innerHTML = '';

            document.querySelectorAll('.existing-document').forEach((element) => {
                const docData = element.getAttribute('data-document');
                if (!docData) return;

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'existing_documents[]';
                input.value = docData;
                container.appendChild(input);
            });
        }

    </script>

    <style>
        .document-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: #fff;
        }
        .document-card:hover {
            box-shadow: 0 6px 12px rgba(0,0,0,0.08);
            transform: translateY(-3px);
        }
        .document-image-container {
            position: relative;
            height: 140px;
            background: #f8f9fa;
        }
        .document-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .document-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .document-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.7);
            opacity: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s ease;
        }
        .document-card:hover .document-overlay,
        .new-document .document-overlay {
            opacity: 1;
        }
        .document-actions {
            display: flex;
        }
        .document-info {
            padding: 10px;
        }
        .document-name {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .file-upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 40px 20px;
            position: relative;
            cursor: pointer;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        .file-upload-area.drag-over {
            border-color: #0d6efd;
            background: #eef4ff;
        }
        .file-upload-drag-over {
            position: absolute;
            inset: 0;
            border-radius: 8px;
            background: rgba(13, 110, 253, 0.85);
            display: none;
            align-items: center;
            justify-content: center;
        }
    </style>
    @endpush
</x-app-layout>
