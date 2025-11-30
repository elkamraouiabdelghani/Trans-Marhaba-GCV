<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 card-header bg-white border-0 p-3 rounded-3 shadow-sm">
            <h5 class="mb-0 text-dark fw-bold">
                <i class="bi bi-pencil me-2 text-primary"></i>
                {{ __('messages.edit_driver_handover') ?? __('messages.edit') }}
            </h5>
            <a href="{{ route('driver-handovers.show', $handover) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('messages.back') }}
            </a>
        </div>

        <div class="card border-0 shadow-sm col-md-10 mx-auto">
            <form method="POST" action="{{ route('driver-handovers.update', $handover) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.driver_replace') }}</label>
                            <select name="driver_from_id"
                                    id="driver-from-select"
                                    class="form-select @error('driver_from_id') is-invalid @enderror">
                                <option value="">{{ __('messages.select_driver') }}</option>
                                @foreach($drivers as $id => $name)
                                    <option value="{{ $id }}"
                                        data-vehicle="{{ $driverVehicleMap[$id] ?? '' }}"
                                        {{ (string) old('driver_from_id', $handover->driver_from_id) === (string) $id ? 'selected' : '' }}>
                                        {{ $name ?? __('messages.driver_number') . $id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('driver_from_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.driver_replacement') }}</label>
                            <select name="driver_to_id" class="form-select @error('driver_to_id') is-invalid @enderror">
                                <option value="">{{ __('messages.select_driver') }}</option>
                                @foreach($drivers as $id => $name)
                                    <option value="{{ $id }}" {{ (string) old('driver_to_id', $handover->driver_to_id) === (string) $id ? 'selected' : '' }}>
                                        {{ $name ?? __('messages.driver_number') . $id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('driver_to_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.vehicle') }}</label>
                            <select name="vehicle_id" id="vehicle-select" class="form-select @error('vehicle_id') is-invalid @enderror">
                                <option value="">{{ __('messages.select_vehicle') }}</option>
                                @foreach($vehicles as $id => $plate)
                                    <option value="{{ $id }}"
                                        data-mileage="{{ $vehicleMileageMap[$id] ?? '' }}"
                                        {{ (string) old('vehicle_id', $handover->vehicle_id) === (string) $id ? 'selected' : '' }}>
                                        {{ $plate }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.vehicle_km') }}</label>
                            <input type="number" name="vehicle_km" id="vehicle-km-input" value="{{ old('vehicle_km', $handover->vehicle_km) }}" class="form-control @error('vehicle_km') is-invalid @enderror" min="0">
                            @error('vehicle_km')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.code') ?? 'Code' }}</label>
                            <input type="text" 
                                   name="code" 
                                   value="{{ old('code', $handover->code) }}" 
                                   class="form-control @error('code') is-invalid @enderror">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.gasoil') ?? 'Gasoil (L)' }}</label>
                            <input type="number" 
                                   name="gasoil" 
                                   value="{{ old('gasoil', $handover->gasoil) }}" 
                                   step="0.01"
                                   placeholder="0.00" 
                                   class="form-control @error('gasoil') is-invalid @enderror" 
                                   min="0">
                            @error('gasoil')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.date') }}</label>
                            <input type="date" name="handover_date" value="{{ old('handover_date', optional($handover->handover_date)->toDateString()) }}" class="form-control @error('handover_date') is-invalid @enderror">
                            @error('handover_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.location') }}</label>
                            <input type="text" name="location" value="{{ old('location', $handover->location) }}" class="form-control @error('location') is-invalid @enderror">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="">
                            <label class="form-label">{{ __('messages.cause') }}</label>
                            @php
                                $currentCause = old('cause', $handover->cause);
                                $isOtherCause = $currentCause && !in_array($currentCause, ['malade', 'demission', 'conge']);
                                $causeValue = $isOtherCause ? 'other' : $currentCause;
                                $causeOtherValue = $isOtherCause ? $currentCause : old('cause_other', '');
                            @endphp
                            <select name="cause" id="cause-select" class="form-select @error('cause') is-invalid @enderror">
                                <option value="">{{ __('messages.select_cause') ?? 'Select cause' }}</option>
                                <option value="malade" {{ $causeValue === 'malade' ? 'selected' : '' }}>{{ __('messages.cause_malade') ?? 'Malade' }}</option>
                                <option value="demission" {{ $causeValue === 'demission' ? 'selected' : '' }}>{{ __('messages.cause_demission') ?? 'Démission' }}</option>
                                <option value="conge" {{ $causeValue === 'conge' ? 'selected' : '' }}>{{ __('messages.cause_conge') ?? 'Congé' }}</option>
                                <option value="other" {{ $causeValue === 'other' || $isOtherCause ? 'selected' : '' }}>{{ __('messages.cause_other') ?? 'Autre' }}</option>
                            </select>
                            @error('cause')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="cause-other-input" style="display: {{ $isOtherCause || $causeValue === 'other' ? 'block' : 'none' }};" class="mt-2">
                                <label class="form-label small">{{ __('messages.cause_other_specify') ?? 'Spécifier la cause' }}</label>
                                <input type="text" 
                                       name="cause_other" 
                                       id="cause-other-text" 
                                       value="{{ $causeOtherValue }}" 
                                       class="form-control @error('cause_other') is-invalid @enderror" 
                                       placeholder="{{ __('messages.cause_other_placeholder') ?? 'Entrez la cause' }}">
                                @error('cause_other')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="my-4" style="width: 97%; margin: 0 auto;">

                {{-- documents --}}
                @php
                    $documentRows = [
                        'cartes_grises' => 'CARTES GRISES',
                        'certificats_visite' => 'CERTIFICATS DE LA VISITE TECHNIQUE',
                        'cartes_autorisation' => "CARTES D'AUTORISATION",
                        'jawaz_autoroute' => 'JAWAZ / AUTOROUTE N°',
                        'attestation_assurance' => 'ATTESTATIONS ASSURANCE',
                        'attestation_vignette' => 'ATTESTATION DE VIGNETTE',
                        'carnet_metrologique' => 'CARNET METROLOGIQUE + ATTESTATION D\'INSTALLATION',
                        'attestation_flexible' => 'ATTESTATION DE FLEXIBLE',
                        'attestation_extincteurs' => 'ATTESTATION DES EXTINCTEURS',
                        'manuel_atlas' => 'MANUEL ATLAS',
                    ];

                    $documentCheckboxes = [
                        'fds' => 'F.D.S',
                        'manuel_conducteur' => 'MANUEL CONDUCTEUR',
                        'consignes_securite' => 'CONSIGNES DE SECURITE',
                        'cahier_inspection' => 'CAHIER INSPECTION A.D',
                        'cahier_feuille_route' => 'CAHIER FEUILLE DE ROUTE',
                        'manuel_secourisme' => 'MANUEL DE SECOURISME',
                        'disque_dernier_voyage' => 'DISQUE DERNIER VOYAGE',
                        'cheque_dv' => 'CHEQUE D.V',
                        'facture_bl_dv' => 'FACTURE & BL CACHETE D.V',
                        'certificat_jaugeage' => 'CERTIFICAT DE JAUGEAGE',
                        'attestation_deplacement' => 'Attestation de déplacement obligatoire',
                    ];

                    $documents = old('documents', $handover->documents ?? []);
                @endphp
                <div class="table-responsive m-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.documents') }}</th>
                                <th class="text-center" style="width: 80px;">{{ __('messages.yes') }}</th>
                                <th class="text-center" style="width: 80px;">{{ __('messages.no') }}</th>
                                <th style="width: 250px;">{{ __('messages.observation') ?? 'Observation' }}</th>
                                <th style="width: 150px;">{{ __('messages.image') ?? 'Image' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentRows as $key => $label)
                                <tr>
                                    <td class="fw-semibold text-uppercase small">{{ $label }}</td>
                                    @if($key === 'jawaz_autoroute')
                                        <td colspan="2">
                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   name="documents[jawaz_autoroute]"
                                                   value="{{ old('documents.jawaz_autoroute', $documents['jawaz_autoroute'] ?? '') }}">
                                        </td>
                                        <td>
                                            <textarea name="documents[{{ $key }}_observation]" 
                                                      rows="2" 
                                                      class="form-control form-control-sm" 
                                                      placeholder="{{ __('messages.observation_placeholder') ?? 'Observation...' }}">{{ old("documents.{$key}_observation", $documents["{$key}_observation"] ?? '') }}</textarea>
                                        </td>
                                        <td>
                                            @if(isset($documents["{$key}_image"]) && $documents["{$key}_image"])
                                                <div class="mb-2">
                                                    <img src="{{ route('uploads.serve', $documents["{$key}_image"]) }}" 
                                                         alt="Image" 
                                                         class="img-thumbnail" 
                                                         style="max-width: 80px; max-height: 80px;">
                                                </div>
                                            @endif
                                            <input type="file" 
                                                   name="documents_images[{{ $key }}]" 
                                                   accept="image/*"
                                                   class="form-control form-control-sm">
                                        </td>
                                    @else
                                        <td class="text-center">
                                            <input type="radio"
                                                   name="documents[{{ $key }}]"
                                                   value="oui"
                                                   class="form-check-input"
                                                   {{ old("documents.$key", $documents[$key] ?? '') === 'oui' ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio"
                                                   name="documents[{{ $key }}]"
                                                   value="non"
                                                   class="form-check-input"
                                                   {{ old("documents.$key", $documents[$key] ?? '') === 'non' ? 'checked' : '' }}>
                                        </td>
                                        <td>
                                            <textarea name="documents[{{ $key }}_observation]" 
                                                      rows="2" 
                                                      class="form-control form-control-sm" 
                                                      placeholder="{{ __('messages.observation_placeholder') ?? 'Observation...' }}">{{ old("documents.{$key}_observation", $documents["{$key}_observation"] ?? '') }}</textarea>
                                        </td>
                                        <td>
                                            @if(isset($documents["{$key}_image"]) && $documents["{$key}_image"])
                                                <div class="mb-2">
                                                    <img src="{{ route('uploads.serve', $documents["{$key}_image"]) }}" 
                                                         alt="Image" 
                                                         class="img-thumbnail" 
                                                         style="max-width: 80px; max-height: 80px;">
                                                </div>
                                            @endif
                                            <input type="file" 
                                                   name="documents_images[{{ $key }}]" 
                                                   accept="image/*"
                                                   class="form-control form-control-sm">
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="table table-bordered align-middle">
                        <tbody>
                            @foreach(array_chunk($documentCheckboxes, 3, true) as $chunk)
                                <tr>
                                    @foreach($chunk as $key => $label)
                                        <td>
                                            <div class="fw-semibold text-uppercase small mb-2">{{ $label }}</div>
                                            <div class="form-check">
                                                <input type="checkbox"
                                                       class="form-check-input"
                                                       id="doc-option-{{ $key }}-edit"
                                                       name="documents[options][{{ $key }}][checked]"
                                                       value="1"
                                                       {{ old("documents.options.$key.checked", $documents['options'][$key]['checked'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="doc-option-{{ $key }}-edit">
                                                    {{ __('messages.present') ?? 'Présent' }}
                                                </label>
                                            </div>
                                        </td>
                                    @endforeach
                                    @if(count($chunk) < 3)
                                        @for($i = count($chunk); $i < 3; $i++)
                                            <td></td>
                                        @endfor
                                    @endif
                                    <td class="text-center" style="width: 80px;">
                                        <input type="radio"
                                               name="documents[options][row_{{ $loop->index }}][status]"
                                               value="oui"
                                               class="form-check-input"
                                               {{ old("documents.options.row_$loop->index.status", $documents['options']['row_' . $loop->index]['status'] ?? '') === 'oui' ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-center" style="width: 80px;">
                                        <input type="radio"
                                               name="documents[options][row_{{ $loop->index }}][status]"
                                               value="non"
                                               class="form-check-input"
                                               {{ old("documents.options.row_$loop->index.status", $documents['options']['row_' . $loop->index]['status'] ?? '') === 'non' ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        @if(isset($documents['options']['row_' . $loop->index]['observation']) && $documents['options']['row_' . $loop->index]['observation'])
                                            <textarea name="documents[options][row_{{ $loop->index }}][observation]" 
                                                      rows="2" 
                                                      class="form-control form-control-sm" 
                                                      placeholder="{{ __('messages.observation_placeholder') ?? 'Observation...' }}">{{ old("documents.options.row_$loop->index.observation", $documents['options']['row_' . $loop->index]['observation'] ?? '') }}</textarea>
                                        @else
                                            <textarea name="documents[options][row_{{ $loop->index }}][observation]" 
                                                      rows="2" 
                                                      class="form-control form-control-sm" 
                                                      placeholder="{{ __('messages.observation_placeholder') ?? 'Observation...' }}"></textarea>
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($documents['options']['row_' . $loop->index]['image']) && $documents['options']['row_' . $loop->index]['image'])
                                            <div class="mb-2">
                                                <img src="{{ route('uploads.serve', $documents['options']['row_' . $loop->index]['image']) }}" 
                                                     alt="Image" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 80px; max-height: 80px;">
                                            </div>
                                        @endif
                                        <input type="file" 
                                               name="documents_images[options][row_{{ $loop->index }}]" 
                                               accept="image/*"
                                               class="form-control form-control-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <hr class="my-4" style="width: 97%; margin: 0 auto;">

                {{-- outillages/materiels --}}
                @php
                    $equipmentRows = [
                        'harnais' => 'HARNAIS (semta)',
                        'clea_goujons' => 'CLE A GOUJONS',
                        'cle_cabine' => 'CLE DE CABINE',
                        'extincteurs' => 'EXTINCTEURS:',
                        'calles' => 'CALLES',
                        'radio_cassette' => 'RADIO CASSETTE',
                        'cable_abs' => 'CABLE ABS INSTALLE',
                        'flexibles' => 'FLEXIBLES',
                        'plaques_signalisation' => 'PLAQUES SIGNALETIQUES DE PANNES',
                        'plaques_immatriculation' => 'PLAQUES D\'IMMATRICULATION + PLAQUE 80',
                        'nombre_flexibles' => 'NOMBRE DE FLEXIBLES:',
                        'cle_vanne' => 'CLE A VANNE',
                        'pince_plombage' => 'PINCE DE PLOMBAGE',
                        'nombre_reduction' => 'NOMBRE DE REDUCTION:',
                        'cones' => '3 CONES',
                        'triangle_panne' => 'TRIANGLE DE PANNE',
                        'pneu_secours' => 'UN PNEU DE SECOURS',
                        'seau_aluminium' => 'UN SAUT EN ALUMINIUM AVEC PRODUIT ABSORBANT (SABLE)',
                        'boite_pharmacie' => 'BOITE A PHARMACIE',
                    ];

                    $equipment = old('equipment', $handover->equipment ?? []);
                    $equipmentCounts = old('equipment_counts', $equipment['counts'] ?? []);
                @endphp

                <div class="table-responsive m-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.tools_equipment') ?? 'OUTILLAGES / MATERIELS' }}</th>
                                <th class="text-center" style="width: 80px;">{{ __('messages.yes') }}</th>
                                <th class="text-center" style="width: 80px;">{{ __('messages.no') }}</th>
                                <th style="width: 250px;">{{ __('messages.observation') ?? 'Observation' }}</th>
                                <th style="width: 150px;">{{ __('messages.image') ?? 'Image' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipmentRows as $key => $label)
                                <tr>
                                    <td class="fw-semibold text-uppercase small d-flex align-items-center gap-2">
                                        {{ $label }}
                                        @if(in_array($key, ['extincteurs', 'nombre_flexibles', 'nombre_reduction']))
                                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                                @php
                                                    $counts = $key === 'extincteurs' ? ['N°1', 'N°2', 'N°3'] : ['1', '2', '3', '4'];
                                                @endphp
                                                @foreach($counts as $index => $countLabel)
                                                    <div class="d-flex align-items-center gap-1">
                                                        <input type="checkbox"
                                                               id="equipment_count_{{ $key }}_{{ $index }}_edit"
                                                               name="equipment_counts[{{ $key }}][{{ $index }}]"
                                                               value="1"
                                                               class="form-check-input"
                                                               {{ old("equipment_counts.$key.$index", isset($equipmentCounts[$key][$index]) && $equipmentCounts[$key][$index] ? true : false) ? 'checked' : '' }}>
                                                        <label class="form-check-label small mb-0" for="equipment_count_{{ $key }}_{{ $index }}_edit">
                                                            {{ $countLabel }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <input type="radio"
                                               name="equipment[{{ $key }}]"
                                               value="oui"
                                               class="form-check-input"
                                               {{ old("equipment.$key", $equipment[$key] ?? '') === 'oui' ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-center">
                                        <input type="radio"
                                               name="equipment[{{ $key }}]"
                                               value="non"
                                               class="form-check-input"
                                               {{ old("equipment.$key", $equipment[$key] ?? '') === 'non' ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <textarea name="equipment[{{ $key }}_observation]" 
                                                  rows="2" 
                                                  class="form-control form-control-sm" 
                                                  placeholder="{{ __('messages.observation_placeholder') ?? 'Observation...' }}">{{ old("equipment.{$key}_observation", $equipment["{$key}_observation"] ?? '') }}</textarea>
                                    </td>
                                    <td>
                                        @if(isset($equipment["{$key}_image"]) && $equipment["{$key}_image"])
                                            <div class="mb-2">
                                                <img src="{{ route('uploads.serve', $equipment["{$key}_image"]) }}" 
                                                     alt="Image" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 80px; max-height: 80px;">
                                            </div>
                                        @endif
                                        <input type="file" 
                                               name="equipment_images[{{ $key }}]" 
                                               accept="image/*"
                                               class="form-control form-control-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <hr class="my-4" style="width: 97%; margin: 0 auto;">

                {{-- anomalies --}}
                <div class="m-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-uppercase small">{{ __('messages.anomalies_constatees') ?? 'ANOMALIES CONSTATEES' }}</label>
                        <textarea name="anomalies_description"
                                  rows="4"
                                  class="form-control @error('anomalies_description') is-invalid @enderror">{{ old('anomalies_description', $handover->anomalies_description) }}</textarea>
                        @error('anomalies_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-uppercase small">{{ __('messages.actions_prises') ?? 'Actions prises :' }}</label>
                        <textarea name="anomalies_actions"
                                  rows="3"
                                  class="form-control @error('anomalies_actions') is-invalid @enderror">{{ old('anomalies_actions', $handover->anomalies_actions) }}</textarea>
                        @error('anomalies_actions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <hr class="my-4" style="width: 97%; margin: 0 auto;">
                <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2">
                    <a href="{{ route('driver-handovers.show', $handover) }}" class="btn btn-outline-secondary">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-dark">
                        {{ __('messages.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const driverSelect = document.getElementById('driver-from-select');
            const vehicleSelect = document.getElementById('vehicle-select');
            const vehicleKmInput = document.getElementById('vehicle-km-input');
            const vehicleMileageMap = @json($vehicleMileageMap);

            if (!driverSelect || !vehicleSelect) {
                return;
            }

            const applyVehicleSelection = (vehicleId) => {
                if (!vehicleId) {
                    return;
                }

                vehicleSelect.value = vehicleId.toString();
                updateVehicleKm(vehicleId);
            };

            const updateVehicleKm = (vehicleId) => {
                if (!vehicleKmInput) {
                    return;
                }

                const mileage = vehicleMileageMap[vehicleId];
                // Only update if input is empty or if explicitly changing vehicle
                if (!vehicleKmInput.value || vehicleSelect.value !== vehicleKmInput.dataset.originalVehicle) {
                    vehicleKmInput.value = mileage !== undefined && mileage !== null ? mileage : '';
                }
            };

            driverSelect.addEventListener('change', () => {
                const selectedOption = driverSelect.options[driverSelect.selectedIndex];
                const vehicleId = selectedOption?.getAttribute('data-vehicle');
                applyVehicleSelection(vehicleId);
            });

            vehicleSelect.addEventListener('change', () => {
                updateVehicleKm(vehicleSelect.value);
            });

            // Initialize on page load
            if (vehicleSelect.value) {
                updateVehicleKm(vehicleSelect.value);
            }

            // Handle cause dropdown - show/hide "other" input
            const causeSelect = document.getElementById('cause-select');
            const causeOtherInput = document.getElementById('cause-other-input');
            const causeOtherText = document.getElementById('cause-other-text');

            if (causeSelect && causeOtherInput) {
                causeSelect.addEventListener('change', function() {
                    if (this.value === 'other') {
                        causeOtherInput.style.display = 'block';
                        if (causeOtherText) {
                            causeOtherText.required = true;
                        }
                    } else {
                        causeOtherInput.style.display = 'none';
                        if (causeOtherText) {
                            causeOtherText.required = false;
                            if (this.value !== 'other') {
                                causeOtherText.value = '';
                            }
                        }
                    }
                });

                // Trigger on page load to set initial state
                causeSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
</x-app-layout>
