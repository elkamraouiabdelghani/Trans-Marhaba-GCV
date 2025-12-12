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
                            <select name="driver_to_id" id="driver-to-select" class="form-select @error('driver_to_id') is-invalid @enderror">
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
                            <input type="number" name="vehicle_km" id="vehicle-km-input" value="{{ old('vehicle_km', $handover->vehicle_km) }}" placeholder="0" class="form-control @error('vehicle_km') is-invalid @enderror" min="0">
                            @error('vehicle_km')
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
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.handover_date') ?? 'Date de sortie' }}</label>
                            <input type="date" name="handover_date" value="{{ old('handover_date', optional($handover->handover_date)->toDateString()) }}" class="form-control @error('handover_date') is-invalid @enderror">
                            @error('handover_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6" id="back-date-container" style="display: none;">
                            <label class="form-label">{{ __('messages.back_date') ?? 'Date de retour' }}</label>
                            <input type="date" name="back_date" id="back-date-input" value="{{ old('back_date', optional($handover->back_date)->toDateString()) }}" class="form-control @error('back_date') is-invalid @enderror">
                            @error('back_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.location') }}</label>
                            <input type="text" name="location" value="{{ old('location', $handover->location) }}" placeholder="Tangier..." class="form-control @error('location') is-invalid @enderror">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- documents input --}}
                        <div class="col-12">
                            <label class="form-label fw-bold mb-3">{{ __('messages.upload_document_files') }}</label>
                            @php
                                $existingFiles = old('document_files', $handover->document_files ?? []);
                            @endphp
                            @if(!empty($existingFiles) && is_array($existingFiles))
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-2 fw-semibold">{{ __('messages.existing_files') }}:</small>
                                    <div class="row g-2">
                                        @foreach($existingFiles as $index => $file)
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center gap-2 p-2 bg-light border rounded">
                                                    <i class="bi bi-file-earmark text-primary"></i>
                                                    <div class="flex-grow-1">
                                                        <div class="small fw-semibold">{{ $file['name'] ?? basename($file['path'] ?? $file) }}</div>
                                                        @if(isset($file['size']))
                                                            <small class="text-muted">{{ number_format($file['size'] / 1024, 2) }} KB</small>
                                                        @endif
                                                    </div>
                                                    <a href="{{ asset('storage/' . ($file['path'] ?? $file)) }}" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger remove-existing-file-btn" 
                                                            data-index="{{ $index }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div class="border-2 border-dashed rounded p-4 text-center @error('documents_files') border-danger @else border-secondary @enderror" 
                                 id="document-files-dropzone"
                                 style="min-height: 150px; background-color: #f8f9fa; cursor: pointer; transition: all 0.3s ease;"
                                 onmouseover="this.style.backgroundColor='#e9ecef'" 
                                 onmouseout="this.style.backgroundColor='#f8f9fa'">
                                <input type="file" 
                                       name="documents_files[]" 
                                       id="documents-files-input"
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif"
                                       class="d-none @error('documents_files') is-invalid @enderror"
                                       multiple>
                                <div id="dropzone-content">
                                    <i class="bi bi-cloud-upload fs-1 text-dark mb-3"></i>
                                    <p class="mb-2 fw-semibold">{{ __('messages.upload_document_files') }}</p>
                                    <p class="text-muted small mb-2">{{ __('messages.multiple_files_hint') }}</p>
                                    <p class="text-muted small mb-0">{{ __('messages.accepted_formats') }}</p>
                                    <button type="button" class="btn btn-dark btn-sm mt-3" onclick="document.getElementById('documents-files-input').click()">
                                        <i class="bi bi-folder2-open me-2"></i>{{ __('messages.select_files') }}
                                    </button>
                                </div>
                                <div id="selected-files-list" class="mt-3 text-start" style="display: none;">
                                    <p class="fw-semibold mb-2">{{ __('messages.selected_files') }}:</p>
                                    <ul id="files-list" class="list-unstyled mb-0"></ul>
                                </div>
                            </div>
                            @error('documents_files')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <input type="hidden" name="removed_files" id="removed-files-input" value="">
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
                        'manuel_atlas' => 'MANUEL LES RISQUES ET AIRS REPOS',
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
                        'attestation_deplacement' => 'Attestation de déplacement (optionnel)',
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
                                                    <img src="{{ route('driver-handovers.document-image', ['driver_handover' => $handover, 'key' => $key]) }}" 
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
                                                    <img src="{{ route('driver-handovers.document-image', ['driver_handover' => $handover, 'key' => $key]) }}" 
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
                                                <img src="{{ route('driver-handovers.document-image', ['driver_handover' => $handover, 'key' => 'row_' . $loop->index]) }}" 
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
                                            <img src="{{ route('driver-handovers.equipment-image', ['driver_handover' => $handover, 'key' => $key]) }}" 
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
            const driverFromSelect = document.getElementById('driver-from-select');
            const driverToSelect = document.getElementById('driver-to-select');
            const vehicleSelect = document.getElementById('vehicle-select');
            const vehicleKmInput = document.getElementById('vehicle-km-input');
            const vehicleMileageMap = @json($vehicleMileageMap);

            if (!driverFromSelect || !vehicleSelect) {
                return;
            }

            // Function to filter drivers - exclude selected driver from the other dropdown
            function filterDriverOptions() {
                const driverFromValue = driverFromSelect.value;
                const driverToValue = driverToSelect ? driverToSelect.value : '';

                // Filter driver_to dropdown - exclude driver_from selection
                if (driverToSelect) {
                    Array.from(driverToSelect.options).forEach(option => {
                        if (option.value === '') {
                            // Keep the empty option visible
                            option.style.display = '';
                        } else if (option.value === driverFromValue && driverFromValue !== '' && option.value !== driverToValue) {
                            // Hide the selected driver_from in driver_to dropdown (unless it's the current selection)
                            option.style.display = 'none';
                        } else {
                            // Show all other options
                            option.style.display = '';
                        }
                    });
                }

                // Filter driver_from dropdown - exclude driver_to selection
                if (driverFromSelect) {
                    Array.from(driverFromSelect.options).forEach(option => {
                        if (option.value === '') {
                            // Keep the empty option visible
                            option.style.display = '';
                        } else if (option.value === driverToValue && driverToValue !== '' && option.value !== driverFromValue) {
                            // Hide the selected driver_to in driver_from dropdown (unless it's the current selection)
                            option.style.display = 'none';
                        } else {
                            // Show all other options
                            option.style.display = '';
                        }
                    });
                }
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

            driverFromSelect.addEventListener('change', () => {
                const selectedOption = driverFromSelect.options[driverFromSelect.selectedIndex];
                const vehicleId = selectedOption?.getAttribute('data-vehicle');
                applyVehicleSelection(vehicleId);
                // Filter driver options when driver_from changes
                filterDriverOptions();
            });

            // Filter driver options when driver_to changes
            if (driverToSelect) {
                driverToSelect.addEventListener('change', () => {
                    filterDriverOptions();
                });
            }

            vehicleSelect.addEventListener('change', () => {
                updateVehicleKm(vehicleSelect.value);
            });

            // Initialize filters on page load
            filterDriverOptions();
            // Initialize on page load
            if (vehicleSelect.value) {
                updateVehicleKm(vehicleSelect.value);
            }

            // Handle cause dropdown - show/hide "other" input and back_date
            const causeSelect = document.getElementById('cause-select');
            const causeOtherInput = document.getElementById('cause-other-input');
            const causeOtherText = document.getElementById('cause-other-text');
            const backDateContainer = document.getElementById('back-date-container');
            const backDateInput = document.getElementById('back-date-input');

            function toggleBackDate() {
                if (!causeSelect || !backDateContainer || !backDateInput) return;
                
                const cause = causeSelect.value;
                // Show back_date for malade, conge, or other
                if (cause === 'malade' || cause === 'conge' || cause === 'other') {
                    backDateContainer.style.display = 'block';
                    backDateInput.required = true;
                } else {
                    backDateContainer.style.display = 'none';
                    backDateInput.required = false;
                    if (cause !== 'malade' && cause !== 'conge' && cause !== 'other') {
                        backDateInput.value = '';
                    }
                }
            }

            if (causeSelect) {
                causeSelect.addEventListener('change', function() {
                    // Handle "other" input
                    if (causeOtherInput) {
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
                    }
                    
                    // Handle back_date
                    toggleBackDate();
                });

                // Trigger on page load to set initial state
                toggleBackDate();
                causeSelect.dispatchEvent(new Event('change'));
            }

            // Handle existing file removal
            const removedFiles = [];
            document.querySelectorAll('.remove-existing-file-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');
                    removedFiles.push(index);
                    document.getElementById('removed-files-input').value = JSON.stringify(removedFiles);
                    this.closest('.col-md-4').style.display = 'none';
                });
            });

            // Document files upload area
            const dropzone = document.getElementById('document-files-dropzone');
            const fileInput = document.getElementById('documents-files-input');
            const filesList = document.getElementById('files-list');
            const selectedFilesDiv = document.getElementById('selected-files-list');
            const dropzoneContent = document.getElementById('dropzone-content');

            if (dropzone && fileInput) {
                // Click on dropzone to trigger file input
                dropzone.addEventListener('click', function(e) {
                    if (e.target !== fileInput && !e.target.closest('button')) {
                        fileInput.click();
                    }
                });

                // Drag and drop functionality
                dropzone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.style.backgroundColor = '#e3f2fd';
                    this.style.borderColor = '#2196f3';
                });

                dropzone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.style.backgroundColor = '#f8f9fa';
                    this.style.borderColor = '#6c757d';
                });

                dropzone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.style.backgroundColor = '#f8f9fa';
                    this.style.borderColor = '#6c757d';
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        // Create a new FileList and add files
                        const dataTransfer = new DataTransfer();
                        Array.from(fileInput.files).forEach(file => dataTransfer.items.add(file));
                        Array.from(files).forEach(file => dataTransfer.items.add(file));
                        fileInput.files = dataTransfer.files;
                        updateFilesList();
                    }
                });

                // Handle file selection
                fileInput.addEventListener('change', function() {
                    updateFilesList();
                });

                function updateFilesList() {
                    const files = Array.from(fileInput.files);
                    if (files.length > 0) {
                        filesList.innerHTML = '';
                        files.forEach((file, index) => {
                            const li = document.createElement('li');
                            li.className = 'd-flex align-items-center justify-content-between mb-2 p-2 bg-white rounded border';
                            li.innerHTML = `
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-file-earmark text-primary"></i>
                                    <span class="small">${file.name}</span>
                                    <span class="badge bg-secondary small">${(file.size / 1024).toFixed(2)} KB</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-file-btn" data-index="${index}">
                                    <i class="bi bi-x"></i>
                                </button>
                            `;
                            filesList.appendChild(li);
                        });
                        selectedFilesDiv.style.display = 'block';
                        dropzoneContent.style.display = 'none';
                    } else {
                        selectedFilesDiv.style.display = 'none';
                        dropzoneContent.style.display = 'block';
                    }
                }

                // Remove file from list
                if (filesList) {
                    filesList.addEventListener('click', function(e) {
                        if (e.target.closest('.remove-file-btn')) {
                            const index = parseInt(e.target.closest('.remove-file-btn').getAttribute('data-index'));
                            const dataTransfer = new DataTransfer();
                            const files = Array.from(fileInput.files);
                            files.splice(index, 1);
                            files.forEach(file => dataTransfer.items.add(file));
                            fileInput.files = dataTransfer.files;
                            updateFilesList();
                        }
                    });
                }
            }
        });
    </script>
</x-app-layout>
