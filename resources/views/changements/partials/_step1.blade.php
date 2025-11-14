<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-1-circle me-2 text-primary"></i>
            {{ __('messages.step_1_identification') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
            $isValidated = $step && $step->isValidated();
        @endphp

        {{-- Step 1 is read-only as it's already validated during creation --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">{{ __('messages.changement_type') }}</label>
                <p class="text-muted">{{ $changement->changementType->name ?? __('messages.not_available') }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">{{ __('messages.date_changement') }}</label>
                <p class="text-muted">{{ $changement->date_changement->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">{{ __('messages.responsable') }}</label>
                <p class="text-muted">{{ $changement->responsable_changement }}</p>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label fw-bold">{{ __('messages.description_changement') }}</label>
                <p class="text-muted">{{ $changement->description_changement }}</p>
            </div>
            @if($changement->impact)
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold">{{ __('messages.impact') }}</label>
                    <p class="text-muted">{{ $changement->impact }}</p>
                </div>
            @endif
            @if($changement->action)
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold">{{ __('messages.action') }}</label>
                    <p class="text-muted">{{ $changement->action }}</p>
                </div>
            @endif
        </div>

        @if($isValidated)
            <div class="alert alert-success mt-3">
                <i class="bi bi-check-circle me-2"></i>
                {{ __('messages.validated') }} - {{ $step->validated_at ? $step->validated_at->format('d/m/Y H:i') : '' }}
            </div>
        @endif

        <hr class="my-4">

        <div class="d-flex justify-content-end">
            @if($stepNumber < 6)
                <a href="{{ route('changements.step', ['changement' => $changement->id, 'stepNumber' => $stepNumber + 1]) }}" class="btn btn-primary">
                    {{ __('messages.next') }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            @endif
        </div>
    </div>
</div>

