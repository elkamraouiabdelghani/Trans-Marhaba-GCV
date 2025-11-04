<div class="mb-3">
    <label class="form-label">{{ __('messages.methodes_prospection') }}</label>
    <div class="row">
        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="methodes_annonces" 
                       name="methodes[annonces]" 
                       value="1"
                       {{ old('methodes.annonces', $stepData['methodes']['annonces'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="methodes_annonces">
                    {{ __('messages.annonces') }}
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="methodes_reseaux" 
                       name="methodes[reseaux]" 
                       value="1"
                       {{ old('methodes.reseaux', $stepData['methodes']['reseaux'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="methodes_reseaux">
                    {{ __('messages.reseaux_sociaux') }}
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="methodes_recommandation" 
                       name="methodes[recommandation]" 
                       value="1"
                       {{ old('methodes.recommandation', $stepData['methodes']['recommandation'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="methodes_recommandation">
                    {{ __('messages.recommandation') }}
                </label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="methodes_autres" 
                       name="methodes[autres]" 
                       value="1"
                       {{ old('methodes.autres', $stepData['methodes']['autres'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="methodes_autres">
                    {{ __('messages.autres') }}
                </label>
            </div>
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="details" class="form-label">{{ __('messages.details_prospection') }} <span class="text-danger">*</span></label>
    <textarea class="form-control @error('details') is-invalid @enderror" 
              id="details" 
              name="details" 
              rows="4" 
              required>{{ old('details', $stepData['details'] ?? '') }}</textarea>
    @error('details')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="date_debut" class="form-label">{{ __('messages.date_debut_prospection') }}</label>
        <input type="date" 
               class="form-control @error('date_debut') is-invalid @enderror" 
               id="date_debut" 
               name="date_debut" 
               value="{{ old('date_debut', $stepData['date_debut'] ?? '') }}">
        @error('date_debut')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="candidats_contactes" class="form-label">{{ __('messages.candidats_contactes') }}</label>
        <input type="number" 
               class="form-control @error('candidats_contactes') is-invalid @enderror" 
               id="candidats_contactes" 
               name="candidats_contactes" 
               min="0"
               value="{{ old('candidats_contactes', $stepData['candidats_contactes'] ?? '') }}">
        @error('candidats_contactes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="notes" class="form-label">{{ __('messages.notes_supplementaires') }}</label>
    <textarea class="form-control @error('notes') is-invalid @enderror" 
              id="notes" 
              name="notes" 
              rows="3">{{ old('notes', $stepData['notes'] ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

