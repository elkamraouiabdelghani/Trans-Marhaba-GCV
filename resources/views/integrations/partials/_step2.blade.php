<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-2-circle me-2 text-primary"></i>
            {{ __('messages.driver_creation') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
        @endphp

        <form action="{{ route('integrations.save-step', ['integration' => $integration->id, 'stepNumber' => 2]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="submit_action" value="">
            @php
                $prevStepLink = $previousAvailableStep ?? ($stepNumber > 1 ? $stepNumber - 1 : null);
                $nextStepLink = $nextAvailableStep ?? ($stepNumber < 9 ? $stepNumber + 1 : null);
            @endphp

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="full_name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('full_name') is-invalid @enderror" 
                           id="full_name" 
                           name="full_name" 
                           value="{{ old('full_name', $stepData['full_name'] ?? '') }}" 
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
                           value="{{ old('email', $stepData['email'] ?? '') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">{{ __('messages.phone') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone', $stepData['phone'] ?? '') }}" 
                           required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="cin" class="form-label">{{ __('messages.cin') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('cin') is-invalid @enderror" 
                           id="cin" 
                           name="cin" 
                           value="{{ old('cin', $stepData['cin'] ?? '') }}" 
                           required>
                    @error('cin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="date_of_birth" class="form-label">{{ __('messages.date_of_birth') }} <span class="text-danger">*</span></label>
                    <input type="date" 
                           class="form-control @error('date_of_birth') is-invalid @enderror" 
                           id="date_of_birth" 
                           name="date_of_birth" 
                           value="{{ old('date_of_birth', $stepData['date_of_birth'] ?? '') }}" 
                           required>
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if($integration->type === 'driver')
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="license_number" class="form-label">{{ __('messages.license_number') }} <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('license_number') is-invalid @enderror"
                               id="license_number"
                               name="license_number"
                               value="{{ old('license_number', $stepData['license_number'] ?? '') }}"
                               required>
                        @error('license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="license_type" class="form-label">{{ __('messages.license_type') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('license_type') is-invalid @enderror"
                                id="license_type"
                                name="license_type"
                                required>
                            <option value="">{{ __('messages.select_option') }}</option>
                            @foreach(['B', 'C', 'D', 'E'] as $licenseOption)
                                <option value="{{ $licenseOption }}" {{ old('license_type', $stepData['license_type'] ?? '') === $licenseOption ? 'selected' : '' }}>
                                    {{ $licenseOption }}
                                </option>
                            @endforeach
                        </select>
                        @error('license_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="license_issue_date" class="form-label">{{ __('messages.license_issue_date') }} <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('license_issue_date') is-invalid @enderror"
                               id="license_issue_date"
                               name="license_issue_date"
                               value="{{ old('license_issue_date', $stepData['license_issue_date'] ?? '') }}"
                               required>
                        @error('license_issue_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="mb-3">
                <label for="address" class="form-label">{{ __('messages.address') }} <span class="text-danger">*</span></label>
                <textarea class="form-control @error('address') is-invalid @enderror" 
                          id="address" 
                          name="address" 
                          rows="2" 
                          required>{{ old('address', $stepData['address'] ?? '') }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="documents" class="form-label">{{ __('messages.other_documents') }}</label>
                <input type="file" 
                       class="form-control @error('documents') is-invalid @enderror" 
                       id="documents" 
                       name="documents[]" 
                       multiple>
                @error('documents')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @error('documents.*')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">{{ __('messages.multiple_files_allowed') }}</small>
                @if(isset($stepData['documents']) && is_array($stepData['documents']))
                    <div class="mt-2">
                        @foreach($stepData['documents'] as $doc)
                            <small class="d-block text-muted">{{ __('messages.file_uploaded') }}: {{ $doc['name'] ?? basename($doc['path']) }}</small>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($step && $step->isValidated())
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ __('messages.validated') }}
                </div>
            @elseif($step && $step->isRejected())
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ __('messages.rejected') }}
                </div>
            @endif

            <hr class="my-4">
            @if($step && $step->isValidated())
                {{-- Show Update and Next buttons when validated --}}
                <div class="d-flex gap-2 justify-content-between">
                    <div>
                        @if($prevStepLink)
                            <a href="{{ route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $prevStepLink]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                {{ __('messages.previous') }}
                            </a>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-success" data-submit-action="update">
                            <i class="bi bi-pencil me-1"></i>
                            {{ __('messages.update') }}
                        </button>
                        @if($nextStepLink)
                            <a href="{{ route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $nextStepLink]) }}" class="btn btn-primary">
                                {{ __('messages.next') }} <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @else
                {{-- Show Validate button when not validated --}}
                <div class="d-flex gap-2 justify-content-between">
                    <div>
                        @if($prevStepLink)
                            <a href="{{ route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $prevStepLink]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                {{ __('messages.previous') }}
                            </a>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit"
                                class="btn btn-success"
                                data-submit-action="validate">
                            <i class="bi bi-check-lg me-1"></i>
                            {{ __('messages.validate_and_next') }}
                        </button>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>


