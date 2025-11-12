<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-3-circle me-2 text-primary"></i>
            {{ __('messages.verification_documentaire') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
            $prevStepLink = $previousAvailableStep ?? ($stepNumber > 1 ? $stepNumber - 1 : null);
            $nextStepLink = $nextAvailableStep ?? ($stepNumber < 9 ? $stepNumber + 1 : null);
        @endphp

        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ __('messages.verification_documentaire_warning') }}
        </div>

        <form action="{{ route('integrations.save-step', ['integration' => $integration->id, 'stepNumber' => 3]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="submit_action" value="">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="verification_date" class="form-label">{{ __('messages.verification_date') }} <span class="text-danger">*</span></label>
                    <input type="date" 
                           class="form-control @error('verification_date') is-invalid @enderror" 
                           id="verification_date" 
                           name="verification_date" 
                           value="{{ old('verification_date', $stepData['verification_date'] ?? '') }}" 
                           required>
                    @error('verification_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="verified_by" class="form-label">{{ __('messages.verified_by') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('verified_by') is-invalid @enderror" 
                           id="verified_by" 
                           name="verified_by" 
                           value="{{ old('verified_by', $stepData['verified_by'] ?? '') }}" 
                           required>
                    @error('verified_by')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="result" class="form-label">{{ __('messages.verification_result') }} <span class="text-danger">*</span></label>
                <select class="form-select @error('result') is-invalid @enderror" 
                        id="result" 
                        name="result" 
                        required>
                    <option value="">{{ __('messages.select_result') }}</option>
                    <option value="passed" {{ old('result', $stepData['result'] ?? '') === 'passed' ? 'selected' : '' }}>{{ __('messages.passed') }}</option>
                    <option value="failed" {{ old('result', $stepData['result'] ?? '') === 'failed' ? 'selected' : '' }}>{{ __('messages.failed') }}</option>
                </select>
                @error('result')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('messages.documents') }}</label>
                @php
                    $documentsReviewed = old('documents_reviewed', $stepData['documents_reviewed'] ?? []);
                    $documentOptions = [
                        'cv' => __('messages.cv'),
                        'lettre_motivation' => __('messages.lettre_motivation'),
                        'permis_conduire' => __('messages.permis_conduire'),
                        'casier_judiciaire' => __('messages.casier_judiciaire'),
                        'certificat_medical' => __('messages.certificat_medical'),
                        'cin' => __('messages.doc_cin'),
                        'carte_professionnelle' => __('messages.doc_carte_professionelle'),
                        'certificat_yeux' => __('messages.doc_certificat_yeux'),
                        'attestation_travail' => __('messages.attestation_travail'),
                        'attestation_demission' => __('messages.doc_attestation_demission'),
                        'formations' => __('messages.doc_formations'),
                        'sold_permis' => __('messages.doc_sold_permis'),
                        'rib' => __('messages.rib'),
                    ];
                @endphp
                <div class="row">
                    @foreach($documentOptions as $value => $label)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="documents_reviewed[]"
                                       value="{{ $value }}"
                                       id="doc_{{ $value }}"
                                       {{ in_array($value, $documentsReviewed) ? 'checked' : '' }}>
                                <label class="form-check-label" for="doc_{{ $value }}">{{ $label }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-3">
                <label for="documents_files" class="form-label">{{ __('messages.supporting_documents') }}</label>
                <input type="file"
                       class="form-control @error('documents_files') is-invalid @enderror @error('documents_files.*') is-invalid @enderror"
                       id="documents_files"
                       name="documents_files[]"
                       multiple>
                @error('documents_files')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @error('documents_files.*')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">{{ __('messages.multiple_files_allowed') }}</small>
                @if(!empty($stepData['documents_files']) && is_array($stepData['documents_files']))
                    <div class="mt-2">
                        @foreach($stepData['documents_files'] as $doc)
                            <small class="d-block text-muted">{{ __('messages.file_uploaded') }}: {{ $doc['name'] ?? basename($doc['path']) }}</small>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" 
                          id="notes" 
                          name="notes" 
                          rows="3">{{ old('notes', $stepData['notes'] ?? '') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if($step && $step->isValidated())
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ __('messages.validated') }}
                </div>
            @elseif($step && $step->isRejected())
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ __('messages.rejected') }} - {{ $step->rejection_reason ?? '' }}
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
                        @if($step && $step->isPending() && auth()->check())
                            <button type="button"
                                    class="btn btn-danger"
                                    onclick="rejectStep({{ $integration->id }}, 3)">
                                <i class="bi bi-x-lg me-1"></i>
                                {{ __('messages.reject') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>


