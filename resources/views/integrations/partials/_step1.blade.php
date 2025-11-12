<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-1-circle me-2 text-primary"></i>
            {{ __('messages.identification_besoin') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
            $nextStepLink = $nextAvailableStep ?? ($stepNumber < 9 ? $stepNumber + 1 : null);
        @endphp

        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label fw-bold">{{ __('messages.identification_besoin') }}</label>
                <p class="text-muted">{{ $stepData['identification_besoin'] ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">{{ __('messages.poste_type') }}</label>
                <select class="form-select" name="poste_type" id="poste_type">
                    <option value="chauffeur">{{ __('messages.chauffeurs') }}</option>
                    <option value="administration">{{ __('messages.administratife') }}</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">{{ __('messages.prospection') }}</label>
                <p class="text-muted">
                    @php
                        $methods = [
                            'reseaux_social' => __('messages.reseaux_sociaux'),
                            'bouche_a_oreil' => __('messages.bouche_a_oreille'),
                            'bureau_recrutement' => __('messages.bureau_recrutement'),
                            'autre' => __('messages.autres'),
                        ];
                        $method = $stepData['prospection_method'] ?? '';
                    @endphp
                    {{ $methods[$method] ?? $method }}
                </p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label fw-bold">{{ __('messages.description_poste') }}</label>
                <p class="text-muted">{{ $stepData['description_poste'] ?? 'N/A' }}</p>
            </div>
        </div>

        @if($step && $step->isValidated())
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                {{ __('messages.validated') }} - {{ $step->validated_at ? $step->validated_at->format('d/m/Y H:i') : '' }}
            </div>
        @elseif($step && $step->isRejected())
            <div class="alert alert-danger">
                <i class="bi bi-x-circle me-2"></i>
                {{ __('messages.rejected') }} - {{ $step->rejection_reason ?? '' }}
            </div>
        @endif

        {{-- Next button if this step is validated and not the last step --}}
        @if($step && $step->isValidated() && $nextStepLink)
            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $nextStepLink]) }}" class="btn btn-primary">
                    {{ __('messages.next') }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        @endif
    </div>
</div>

