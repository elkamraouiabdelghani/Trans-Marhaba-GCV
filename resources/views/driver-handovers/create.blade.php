<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 card-header bg-white border-0 p-3 rounded-3 shadow-sm">
            <h5 class="mb-0 text-dark fw-bold">
                <i class="bi bi-plus-circle me-2 text-primary"></i>
                {{ __('messages.new_driver_handover') }}
            </h5>
            <a href="{{ route('driver-handovers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('messages.back') }}
            </a>
        </div>

        <div class="card border-0 shadow-sm col-md-10 mx-auto">
            <form method="POST" action="{{ route('driver-handovers.store') }}" enctype="multipart/form-data">
                @csrf
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
                                        {{ (string) old('driver_from_id') === (string) $id ? 'selected' : '' }}>
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
                                    <option value="{{ $id }}" {{ (string) old('driver_to_id') === (string) $id ? 'selected' : '' }}>
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
                                        {{ (string) old('vehicle_id') === (string) $id ? 'selected' : '' }}>
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
                            <input type="number" name="vehicle_km" id="vehicle-km-input" value="{{ old('vehicle_km') }}" placeholder="0" class="form-control @error('vehicle_km') is-invalid @enderror" min="0">
                            @error('vehicle_km')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.code') }}</label>
                            <input type="text" 
                                   name="code" 
                                   value="{{ old('code') }}" 
                                   class="form-control @error('code') is-invalid @enderror">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.gasoil') ?? 'Gasoil (L)' }}</label>
                            <input type="number" 
                                   name="gasoil" 
                                   value="{{ old('gasoil') }}" 
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
                            <input type="date" name="handover_date" value="{{ old('handover_date') }}" class="form-control @error('handover_date') is-invalid @enderror">
                            @error('handover_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.location') }}</label>
                            <input type="text" name="location" value="{{ old('location') }}" placeholder="Tangier..." class="form-control @error('location') is-invalid @enderror">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="">
                            <label class="form-label">{{ __('messages.cause') }}</label>
                            <textarea name="cause" rows="3" class="form-control @error('cause') is-invalid @enderror">{{ old('cause') }}</textarea>
                            @error('cause')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                @endphp
                <div class="table-responsive m-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.documents') }}</th>
                                <th class="text-center" style="width: 80px;">{{ __('messages.yes') }}</th>
                                <th class="text-center" style="width: 80px;">{{ __('messages.no') }}</th>
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
                                                   value="{{ old('documents.jawaz_autoroute') }}">
                                        </td>
                                    @else
                                        <td class="text-center">
                                            <input type="radio"
                                                   name="documents[{{ $key }}]"
                                                   value="oui"
                                                   class="form-check-input"
                                                   {{ old("documents.$key") === 'oui' ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio"
                                                   name="documents[{{ $key }}]"
                                                   value="non"
                                                   class="form-check-input"
                                                   {{ old("documents.$key") === 'non' ? 'checked' : '' }}>
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
                                                       id="doc-option-{{ $key }}"
                                                       name="documents[options][{{ $key }}][checked]"
                                                       value="1"
                                                       {{ old("documents.options.$key.checked") ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="doc-option-{{ $key }}">
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
                                               {{ old("documents.options.row_$loop->index.status") === 'oui' ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-center" style="width: 80px;">
                                        <input type="radio"
                                               name="documents[options][row_{{ $loop->index }}][status]"
                                               value="non"
                                               class="form-check-input"
                                               {{ old("documents.options.row_$loop->index.status") === 'non' ? 'checked' : '' }}>
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
                @endphp

                <div class="table-responsive m-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.tools_equipment') ?? 'OUTILLAGES / MATERIELS' }}</th>
                                <th class="text-center" style="width: 80px;">{{ __('messages.yes') }}</th>
                                <th class="text-center" style="width: 80px;">{{ __('messages.no') }}</th>
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
                                                        <span class="small">{{ $countLabel }}</span>
                                                        <input type="text"
                                                               name="equipment_counts[{{ $key }}][{{ $index }}]"
                                                               value="{{ old("equipment_counts.$key.$index") }}"
                                                               class="form-control form-control-sm rounded border-gray-300 h-2">
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
                                               {{ old("equipment.$key") === 'oui' ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-center">
                                        <input type="radio"
                                               name="equipment[{{ $key }}]"
                                               value="non"
                                               class="form-check-input"
                                               {{ old("equipment.$key") === 'non' ? 'checked' : '' }}>
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
                                  class="form-control @error('anomalies_description') is-invalid @enderror">{{ old('anomalies_description') }}</textarea>
                        @error('anomalies_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-uppercase small">{{ __('messages.actions_prises') ?? 'Actions prises :' }}</label>
                        <textarea name="anomalies_actions"
                                  rows="3"
                                  class="form-control @error('anomalies_actions') is-invalid @enderror">{{ old('anomalies_actions') }}</textarea>
                        @error('anomalies_actions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <hr class="my-4" style="width: 97%; margin: 0 auto;">
                <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2">
                    <a href="{{ route('driver-handovers.index') }}" class="btn btn-outline-secondary">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-dark">
                        {{ __('messages.save') }}
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
                vehicleKmInput.value = mileage !== undefined && mileage !== null ? mileage : '';
            };

            driverSelect.addEventListener('change', () => {
                const selectedOption = driverSelect.options[driverSelect.selectedIndex];
                const vehicleId = selectedOption?.getAttribute('data-vehicle');
                applyVehicleSelection(vehicleId);
            });

            vehicleSelect.addEventListener('change', () => {
                updateVehicleKm(vehicleSelect.value);
            });

            driverSelect.dispatchEvent(new Event('change'));
            updateVehicleKm(vehicleSelect.value);
        });
    </script>
</x-app-layout>

