<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 card-header bg-white border-0 p-3 rounded-3 shadow-sm">
            <h5 class="mb-0 text-dark fw-bold">
                <i class="bi bi-arrow-left-right me-2 text-primary"></i>
                {{ __('messages.driver_handover_details') }}
            </h5>
            <a href="{{ route('driver-handovers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('messages.back') }}
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif

        <div class="row">
            <!-- Main Content -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="bi bi-arrow-left-right me-2 text-primary"></i>
                                {{ __('messages.driver_handover_details') }}
                            </h5>
                            <small class="text-muted">
                                {{ __('messages.created_at') }}: {{ $handover->created_at?->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <span class="badge bg-{{ $handover->status === 'confirmed' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $handover->status === 'confirmed' ? 'success' : 'secondary' }}">
                            {{ __('messages.status') }}: {{ __('messages.' . ($handover->status ?? 'pending')) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.driver_replace') }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ $handover->driver_from_name ?? optional($handover->driverFrom)->full_name ?? __('messages.not_available') }}
                                </p>
                                @if($handover->driverFrom)
                                    <a href="{{ route('drivers.show', $handover->driverFrom) }}" class="small text-decoration-underline text-success">
                                        {{ __('messages.view_driver_profile') }}
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.driver_replacement') }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ $handover->driver_to_name ?? optional($handover->driverTo)->full_name ?? __('messages.not_available') }}
                                </p>
                                @if($handover->driverTo)
                                    <a href="{{ route('drivers.show', $handover->driverTo) }}" class="small text-decoration-underline text-success">
                                        {{ __('messages.view_driver_profile') }}
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.vehicle') }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ optional($handover->vehicle)->license_plate ?? __('messages.not_available') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.code') ?? 'Code' }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ $handover->code ?? __('messages.not_available') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.km_in_handover') ?? 'KM in handover' }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ $handover->vehicle_km ?? __('messages.not_available') }} km
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.gasoil') ?? 'Gasoil' }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ $handover->gasoil ? number_format($handover->gasoil, 2) . ' L' : __('messages.not_available') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.handover_date') ?? 'Date de sortie' }}</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <p class="mb-1 fw-semibold">{{ optional($handover->handover_date)->format('d/m/Y') ?? '—' }}</p>
                                    @if($handover->location)
                                        -<small class="fw-semibold">{{ $handover->location }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.back_date') ?? 'Date de retour' }}</h6>
                                <p class="mb-1 fw-semibold">{{ optional($handover->back_date)->format('d/m/Y') ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.cause') }}</h6>
                                <p class="mb-1 fw-semibold">{{ $handover->cause ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar with Quick Actions -->
            <div class="col-12 col-lg-4">
                <aside class="position-sticky" style="top: 0.5rem;margin-bottom: 1.5rem;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="mb-0 text-dark fw-bold text-uppercase small">
                                <i class="bi bi-lightning-charge text-warning me-2"></i>
                                {{ __('messages.quick_actions') }}
                            </h6>
                        </div>
                        <div class="card-body d-grid gap-2">
                            @if($handover->status !== 'confirmed')
                                <a href="{{ route('driver-handovers.edit', $handover) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil me-1"></i>
                                    {{ __('messages.edit') }}
                                </a>
                            @endif
                            
                            @if($handover->status !== 'confirmed')
                                <button type="button" 
                                        class="btn btn-success btn-sm"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#confirmHandoverModal"
                                        data-handover-id="{{ $handover->id }}"
                                        data-driver-from="{{ $handover->driver_from_name ?? optional($handover->driverFrom)->full_name ?? 'N/A' }}"
                                        data-driver-to="{{ $handover->driver_to_name ?? optional($handover->driverTo)->full_name ?? 'N/A' }}"
                                        data-vehicle="{{ optional($handover->vehicle)->license_plate ?? 'N/A' }}">
                                    <i class="bi bi-check-circle me-1"></i>
                                    {{ __('messages.confirm') }}
                                </button>
                            @endif

                            @if($handover->handover_file_path)
                                <a href="{{ route('driver-handovers.pdf', $handover) }}" 
                                   target="_blank" 
                                   class="btn btn-primary btn-sm"
                                   download>
                                    <i class="bi bi-download me-1"></i>
                                    {{ __('messages.download') ?? 'Download PDF' }}
                                </a>
                            @endif

                            <hr class="my-2">

                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteHandoverModal"
                                    data-handover-id="{{ $handover->id }}"
                                    data-handover-date="{{ optional($handover->handover_date)->format('d/m/Y') ?? 'N/A' }}">
                                <i class="bi bi-trash me-1"></i>
                                {{ __('messages.delete') }}
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        {{-- Document Files Section --}}
        @if(isset($handover->document_files) && !empty($handover->document_files) && is_array($handover->document_files))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 text-dark fw-bold text-uppercase small">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                        {{ __('messages.document_files') ?? 'Document Files' }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($handover->document_files as $index => $file)
                            <div class="col-md-3 col-sm-6">
                                <div class="border rounded p-3 h-100 d-flex flex-column">
                                    <div class="text-center mb-2">
                                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <div class="text-center mb-2 flex-grow-1">
                                        <div class="fw-semibold small text-truncate" title="{{ $file['name'] ?? 'Document ' . ($index + 1) }}">
                                            {{ $file['name'] ?? 'Document ' . ($index + 1) }}
                                        </div>
                                        @if(isset($file['size']))
                                            <small class="text-muted">{{ number_format($file['size'] / 1024, 2) }} KB</small>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('driver-handovers.document-file', ['driver_handover' => $handover, 'index' => $index]) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="{{ __('messages.view_file') ?? 'View' }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('driver-handovers.document-file', ['driver_handover' => $handover, 'index' => $index]) }}" 
                                           class="btn btn-sm btn-outline-success"
                                           download
                                           title="{{ __('messages.download') ?? 'Download' }}">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Documents Table --}}
        <div class="card border-0 shadow-sm mb-4">
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
                    'cones' => '3 CONES',
                    'triangle_panne' => 'TRIANGLE DE PANNE',
                    'pneu_secours' => 'UN PNEU DE SECOURS',
                    'seau_aluminium' => 'UN SAUT EN ALUMINIUM AVEC PRODUIT ABSORBANT (SABLE)',
                    'boite_pharmacie' => 'BOITE A PHARMACIE',
                ];

                $documents = $handover->documents ?? [];
                $documentFiles = $handover->document_files ?? [];
                $equipment = $handover->equipment ?? [];
                $equipmentCounts = $equipment['counts'] ?? [];
            @endphp
            
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 text-dark fw-bold text-uppercase small">
                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                    {{ __('messages.documents') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.documents') }}</th>
                                <th class="text-center" style="width: 80px;">{{ __('messages.yes') }}</th>
                                <th class="text-center" style="width: 80px;">{{ __('messages.no') }}</th>
                                <th style="width: 250px;">{{ __('messages.observation') ?? 'Observation' }}</th>
                                <th style="width: 150px;">{{ __('messages.image') ?? 'Image' }}</th>
                                <th style="width: 150px;">{{ __('messages.document_file') ?? 'Document File' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentRows as $key => $label)
                                <tr>
                                    <td class="fw-semibold text-uppercase small">{{ $label }}</td>
                                    @if($key === 'jawaz_autoroute')
                                        <td colspan="2" class="text-center">
                                            <span class="small">{{ $documents['jawaz_autoroute'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <small>{{ $documents["{$key}_observation"] ?? '—' }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if(isset($documents["{$key}_image"]) && $documents["{$key}_image"])
                                                <img src="{{ route('driver-handovers.document-image', ['driver_handover' => $handover, 'key' => $key]) }}" 
                                                     alt="Image" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 80px; max-height: 80px; cursor: pointer;"
                                                     onclick="window.open(this.src, '_blank')">
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(isset($documentFiles[$key]['path']) && $documentFiles[$key]['path'])
                                                <a href="{{ asset('storage/' . $documentFiles[$key]['path']) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   download>
                                                    <i class="bi bi-download me-1"></i>
                                                    {{ $documentFiles[$key]['name'] ?? __('messages.download') }}
                                                </a>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    @else
                                        <td class="text-center">
                                            @if(isset($documents[$key]) && $documents[$key] === 'oui')
                                                <span class="badge bg-success">✓</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(isset($documents[$key]) && $documents[$key] === 'non')
                                                <span class="badge bg-danger">✗</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $documents["{$key}_observation"] ?? '—' }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if(isset($documents["{$key}_image"]) && $documents["{$key}_image"])
                                                <img src="{{ route('driver-handovers.document-image', ['driver_handover' => $handover, 'key' => $key]) }}" 
                                                     alt="Image" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 80px; max-height: 80px; cursor: pointer;"
                                                     onclick="window.open(this.src, '_blank')">
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(isset($documentFiles[$key]['path']) && $documentFiles[$key]['path'])
                                                <a href="{{ asset('storage/' . $documentFiles[$key]['path']) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   download>
                                                    <i class="bi bi-download me-1"></i>
                                                    {{ $documentFiles[$key]['name'] ?? __('messages.download') }}
                                                </a>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if(isset($documents['options']))
                        <table class="table table-bordered align-middle mt-3">
                            <tbody>
                                @foreach(array_chunk($documentCheckboxes, 3, true) as $chunkIndex => $chunk)
                                    <tr>
                                        @foreach($chunk as $key => $label)
                                            <td>
                                                <div class="fw-semibold text-uppercase small mb-2">{{ $label }}</div>
                                                @if(isset($documents['options'][$key]['checked']) && $documents['options'][$key]['checked'])
                                                    <span class="badge bg-info">✓ Présent</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        @if(count($chunk) < 3)
                                            @for($i = count($chunk); $i < 3; $i++)
                                                <td></td>
                                            @endfor
                                        @endif
                                        <td class="text-center" style="width: 80px;">
                                            @if(isset($documents['options']['row_' . $chunkIndex]['status']) && $documents['options']['row_' . $chunkIndex]['status'] === 'oui')
                                                <span class="badge bg-success">✓</span>
                                            @endif
                                        </td>
                                        <td class="text-center" style="width: 80px;">
                                            @if(isset($documents['options']['row_' . $chunkIndex]['status']) && $documents['options']['row_' . $chunkIndex]['status'] === 'non')
                                                <span class="badge bg-danger">✗</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $documents['options']['row_' . $chunkIndex]['observation'] ?? '—' }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if(isset($documents['options']['row_' . $chunkIndex]['image']) && $documents['options']['row_' . $chunkIndex]['image'])
                                                <img src="{{ route('driver-handovers.document-image', ['driver_handover' => $handover, 'key' => 'row_' . $chunkIndex]) }}" 
                                                     alt="Image" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 80px; max-height: 80px; cursor: pointer;"
                                                     onclick="window.open(this.src, '_blank')">
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(isset($documentFiles['options'][$chunkIndex]['path']) && $documentFiles['options'][$chunkIndex]['path'])
                                                <a href="{{ asset('storage/' . $documentFiles['options'][$chunkIndex]['path']) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   download>
                                                    <i class="bi bi-download me-1"></i>
                                                    {{ $documentFiles['options'][$chunkIndex]['name'] ?? __('messages.download') }}
                                                </a>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        {{-- Equipment Table --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 text-dark fw-bold text-uppercase small">
                    <i class="bi bi-tools me-2 text-primary"></i>
                    {{ __('messages.tools_equipment') ?? 'OUTILLAGES / MATERIELS' }}
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
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
                                        @if(in_array($key, ['extincteurs', 'nombre_flexibles']))
                                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                                @php
                                                    $counts = $key === 'extincteurs' ? ['N°1', 'N°2', 'N°3'] : ['1', '2', '3', '4'];
                                                @endphp
                                                @foreach($counts as $index => $countLabel)
                                                    @if(isset($equipmentCounts[$key][$index]) && $equipmentCounts[$key][$index])
                                                        <span class="badge bg-secondary small">{{ $countLabel }} ✓</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(isset($equipment[$key]) && $equipment[$key] === 'oui')
                                            <span class="badge bg-success">✓</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(isset($equipment[$key]) && $equipment[$key] === 'non')
                                            <span class="badge bg-danger">✗</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $equipment["{$key}_observation"] ?? '—' }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if(isset($equipment["{$key}_image"]) && $equipment["{$key}_image"])
                                            <img src="{{ route('driver-handovers.equipment-image', ['driver_handover' => $handover, 'key' => $key]) }}" 
                                                 alt="Image" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 80px; max-height: 80px; cursor: pointer;"
                                                 onclick="window.open(this.src, '_blank')">
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Anomalies Section --}}
        <div class="card border-0 shadow-sm mb-4">
            @if($handover->anomalies_description || $handover->anomalies_actions)
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 text-dark fw-bold text-uppercase small">
                        <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                        {{ __('messages.anomalies') ?? 'Anomalies' }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($handover->anomalies_description)
                        <div class="mb-3">
                            <h6 class="text-muted text-uppercase small mb-2">{{ __('messages.description') ?? 'Description' }}</h6>
                            <p class="mb-0">{{ $handover->anomalies_description }}</p>
                        </div>
                    @endif
                    @if($handover->anomalies_actions)
                        <div>
                            <h6 class="text-muted text-uppercase small mb-2">{{ __('messages.actions') ?? 'Actions' }}</h6>
                            <p class="mb-0">{{ $handover->anomalies_actions }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Confirm Handover Modal -->
    <div class="modal fade" id="confirmHandoverModal" tabindex="-1" aria-labelledby="confirmHandoverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="confirmHandoverModalLabel">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ __('messages.confirm_handover') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        {{ __('messages.confirm_handover_question') }}
                    </p>
                    <div class="alert alert-info mb-0">
                        <strong>{{ __('messages.driver_replace') }}:</strong> <span id="confirmDriverFrom"></span><br>
                        <strong>{{ __('messages.driver_replacement') }}:</strong> <span id="confirmDriverTo"></span><br>
                        <strong>{{ __('messages.vehicle') }}:</strong> <span id="confirmVehicle"></span>
                    </div>
                    <p class="mt-3 mb-0 text-muted small">
                        {{ __('messages.confirm_handover_warning') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') }}
                    </button>
                    <form id="confirmHandoverForm" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ __('messages.confirm') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Handover Modal -->
    <div class="modal fade" id="deleteHandoverModal" tabindex="-1" aria-labelledby="deleteHandoverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteHandoverModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ __('messages.delete_handover') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        {{ __('messages.delete_confirmation') }}
                    </p>
                    <div class="alert alert-warning mb-0">
                        <strong>{{ __('messages.date') }}:</strong> <span id="deleteHandoverDate"></span>
                    </div>
                    <p class="mt-3 mb-0 text-danger small">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        {{ __('messages.delete_handover_warning') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') }}
                    </button>
                    <form id="deleteHandoverForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('messages.delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Confirm Handover Modal
            const confirmModal = document.getElementById('confirmHandoverModal');
            if (confirmModal) {
                confirmModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const handoverId = button.getAttribute('data-handover-id');
                    const driverFrom = button.getAttribute('data-driver-from');
                    const driverTo = button.getAttribute('data-driver-to');
                    const vehicle = button.getAttribute('data-vehicle');

                    const modalBody = confirmModal.querySelector('#confirmDriverFrom');
                    const modalBodyTo = confirmModal.querySelector('#confirmDriverTo');
                    const modalBodyVehicle = confirmModal.querySelector('#confirmVehicle');
                    const form = confirmModal.querySelector('#confirmHandoverForm');

                    if (modalBody) modalBody.textContent = driverFrom;
                    if (modalBodyTo) modalBodyTo.textContent = driverTo;
                    if (modalBodyVehicle) modalBodyVehicle.textContent = vehicle;
                    if (form) {
                        form.action = '{{ route("driver-handovers.confirm", ":id") }}'.replace(':id', handoverId);
                    }
                });
            }

            // Delete Handover Modal
            const deleteModal = document.getElementById('deleteHandoverModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const handoverId = button.getAttribute('data-handover-id');
                    const handoverDate = button.getAttribute('data-handover-date');

                    const modalBody = deleteModal.querySelector('#deleteHandoverDate');
                    const form = deleteModal.querySelector('#deleteHandoverForm');

                    if (modalBody) modalBody.textContent = handoverDate;
                    if (form) {
                        form.action = '{{ route("driver-handovers.destroy", ":id") }}'.replace(':id', handoverId);
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>

