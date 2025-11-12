<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-5-circle me-2 text-primary"></i>
            {{ __('messages.step6_deroulement_formation') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
            $isValidated = $step && $step->isValidated();
            $isRejected = $step && $step->isRejected();
        @endphp

        @if(!$isRejected)
            <form action="{{ route('formation-processes.save-step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber]) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="actual_start_date" class="form-label">{{ __('messages.actual_start_date') }}</label>
                    <input type="date" 
                           class="form-control @error('actual_start_date') is-invalid @enderror" 
                           id="actual_start_date" 
                           name="actual_start_date" 
                           value="{{ old('actual_start_date', $stepData['actual_start_date'] ?? '') }}">
                    @error('actual_start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="attendance_sheet" class="form-label">{{ __('messages.attendance_sheet') }}</label>
                        <input type="file" 
                               class="form-control @error('attendance_sheet') is-invalid @enderror" 
                               id="attendance_sheet" 
                               name="attendance_sheet" 
                               accept=".pdf,.doc,.docx,.xlsx">
                        @error('attendance_sheet')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(!empty($stepData['attendance_sheet_path']))
                            <small class="text-muted">{{ __('messages.file_uploaded') }}: {{ basename($stepData['attendance_sheet_path']) }}</small>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="training_materials" class="form-label">{{ __('messages.training_materials') }}</label>
                        <input type="file" 
                               class="form-control @error('training_materials') is-invalid @enderror" 
                               id="training_materials" 
                               name="training_materials" 
                               accept=".pdf,.doc,.docx">
                        @error('training_materials')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(!empty($stepData['training_materials_path']))
                            <small class="text-muted">{{ __('messages.file_uploaded') }}: {{ basename($stepData['training_materials_path']) }}</small>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label for="delivery_notes" class="form-label">{{ __('messages.delivery_notes') }}</label>
                    <textarea class="form-control @error('delivery_notes') is-invalid @enderror" 
                              id="delivery_notes" 
                              name="delivery_notes" 
                              rows="4">{{ old('delivery_notes', $stepData['delivery_notes'] ?? '') }}</textarea>
                    @error('delivery_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">
                @if($step && $step->isValidated())
                    {{-- Show Update and Next buttons when validated --}}
                    <div class="d-flex gap-2 justify-content-between">
                        <div>
                            @if($stepNumber > 1)
                                <a href="{{ route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber - 1]) }}" class="btn btn-outline-secondary">
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
                            @if($stepNumber < 7)
                                <a href="{{ route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber + 1]) }}" class="btn btn-primary">
                                    {{ __('messages.next') }} <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Show Validate button when not validated --}}
                    <div class="d-flex gap-2 justify-content-between">
                        <div>
                            @if($stepNumber > 1)
                                <a href="{{ route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber - 1]) }}" class="btn btn-outline-secondary">
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
                            @if(!$isRejected && !$formationProcess->isValidated() && !$formationProcess->isRejected())
                                <button type="button" class="btn btn-danger" onclick="rejectStep({{ $formationProcess->id }}, {{ $stepNumber }})">
                                    <i class="bi bi-x-lg me-1"></i>
                                    {{ __('messages.reject') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </form>
        @else
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.actual_start_date') }}</label>
                    <p class="text-muted">{{ $stepData['actual_start_date'] ? \Carbon\Carbon::parse($stepData['actual_start_date'])->format('d/m/Y') : 'N/A' }}</p>
                </div>
            </div>

            @if($isValidated)
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ __('messages.validated') }} - {{ $step->validated_at ? $step->validated_at->format('d/m/Y H:i') : '' }}
                </div>
            @elseif($isRejected)
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ __('messages.rejected') }} - {{ $step->rejection_reason ?? '' }}
                </div>
            @endif
        @endif

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
    crossorigin="anonymous" defer></script>

