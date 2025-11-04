<div class="row mb-3">
    <div class="col-md-6">
        <label for="review_date" class="form-label">{{ __('messages.review_date') }} <span class="text-danger">*</span></label>
        <input type="date" 
               class="form-control @error('review_date') is-invalid @enderror" 
               id="review_date" 
               name="review_date" 
               value="{{ old('review_date', $stepData['review_date'] ?? date('Y-m-d')) }}"
               required>
        @error('review_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="reviewed_by" class="form-label">{{ __('messages.reviewed_by') }} <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control @error('reviewed_by') is-invalid @enderror" 
               id="reviewed_by" 
               name="reviewed_by" 
               value="{{ old('reviewed_by', $stepData['reviewed_by'] ?? auth()->user()->name ?? '') }}"
               required>
        @error('reviewed_by')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('messages.documents') }}</label>
    <div class="row">
        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="doc_cv" 
                       name="documents[cv]" 
                       value="1"
                       {{ old('documents.cv', $stepData['documents']['cv'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="doc_cv">
                    {{ __('messages.cv') }}
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="doc_lettre_motivation" 
                       name="documents[lettre_motivation]" 
                       value="1"
                       {{ old('documents.lettre_motivation', $stepData['documents']['lettre_motivation'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="doc_lettre_motivation">
                    {{ __('messages.lettre_motivation') }}
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="doc_permis_conduire" 
                       name="documents[permis_conduire]" 
                       value="1"
                       {{ old('documents.permis_conduire', $stepData['documents']['permis_conduire'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="doc_permis_conduire">
                    {{ __('messages.permis_conduire') }}
                </label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="doc_casier_judiciaire" 
                       name="documents[casier_judiciaire]" 
                       value="1"
                       {{ old('documents.casier_judiciaire', $stepData['documents']['casier_judiciaire'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="doc_casier_judiciaire">
                    {{ __('messages.casier_judiciaire') }}
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="doc_certificat_medical" 
                       name="documents[certificat_medical]" 
                       value="1"
                       {{ old('documents.certificat_medical', $stepData['documents']['certificat_medical'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="doc_certificat_medical">
                    {{ __('messages.certificat_medical') }}
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="doc_autres" 
                       name="documents[autres]" 
                       value="1"
                       {{ old('documents.autres', $stepData['documents']['autres'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="doc_autres">
                    {{ __('messages.autres') }}
                </label>
            </div>
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="evaluation" class="form-label">{{ __('messages.evaluation') }} <span class="text-danger">*</span></label>
    <textarea class="form-control @error('evaluation') is-invalid @enderror" 
              id="evaluation" 
              name="evaluation" 
              rows="4" 
              required>{{ old('evaluation', $stepData['evaluation'] ?? '') }}</textarea>
    @error('evaluation')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="decision" class="form-label">{{ __('messages.decision') }} <span class="text-danger">*</span></label>
    <select class="form-select @error('decision') is-invalid @enderror" 
            id="decision" 
            name="decision" 
            required>
        <option value="">{{ __('messages.select_decision') }}</option>
        <option value="accepted" {{ old('decision', $stepData['decision'] ?? '') == 'accepted' ? 'selected' : '' }}>
            {{ __('messages.accepted') }}
        </option>
        <option value="rejected" {{ old('decision', $stepData['decision'] ?? '') == 'rejected' ? 'selected' : '' }}>
            {{ __('messages.rejected') }}
        </option>
        <option value="pending" {{ old('decision', $stepData['decision'] ?? '') == 'pending' ? 'selected' : '' }}>
            {{ __('messages.pending') }}
        </option>
    </select>
    @error('decision')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
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

