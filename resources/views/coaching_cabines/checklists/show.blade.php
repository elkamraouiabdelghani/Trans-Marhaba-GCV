<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h1 class="h4 mb-1">{{ __('messages.coaching_checklist_title') ?? 'Checklist de coaching' }}</h1>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('coaching-cabines.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('messages.back') }}
                </a>
                <a href="{{ route('coaching.checklists.pdf', [$session, $checklist]) }}" class="btn btn-danger btn-sm" target="_blank">
                    <i class="bi bi-file-pdf me-1"></i> {{ __('messages.export_pdf') }}
                </a>
            </div>
        </div>

        <div class="row">
            {{-- Main Section --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-list-check me-2 text-primary"></i>
                                {{ __('messages.checklist_items') ?? 'Checklist Items' }}
                            </h6>
                            <div class="text-muted small">
                                {{ $checklist->completed_at ? $checklist->completed_at->format('d/m/Y H:i') : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $groupedAnswers = $checklist->answers->groupBy(function($answer) {
                                return $answer->item->category->id ?? 'uncategorized';
                            });
                        @endphp

                        @foreach($groupedAnswers as $categoryId => $answers)
                            @php
                                $category = $answers->first()->item->category ?? null;
                            @endphp
                            @if($category)
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-2 text-primary">{{ $category->name }}</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 50%">{{ __('messages.item') ?? 'Item' }}</th>
                                                    <th class="text-center" style="width: 15%">{{ __('messages.score') ?? 'Score' }}</th>
                                                    <th class="text-center" style="width: 10%">{{ __('messages.checked') ?? 'Checked' }}</th>
                                                    <th style="width: 25%">{{ __('messages.comment') ?? 'Comment' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($answers as $answer)
                                                    <tr>
                                                        <td>{{ $answer->item->label ?? '' }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary">{{ $answer->score }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <i class="bi bi-check-circle-fill text-success"></i>
                                                        </td>
                                                        <td class="small">{{ $answer->comment ?? '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if($groupedAnswers->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                {{ __('messages.no_items_found') ?? 'No items found' }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Score Summary --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-trophy me-2 text-primary"></i>
                            {{ __('messages.checklist_score') ?? 'Checklist Score' }}
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $totalScore = $checklist->getTotalScore();
                            $status = $checklist->getScoreStatus();
                            $statusColor = $checklist->getScoreStatusColor();
                        @endphp
                        <div class="mb-3">
                            <div class="display-4 fw-bold text-{{ $statusColor }}">{{ $totalScore }}</div>
                            <div class="small text-muted">{{ __('messages.total_score') ?? 'Total Score' }}</div>
                        </div>
                        <div>
                            <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} fs-6 px-3 py-2">
                                {{ $status }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Session Info --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            {{ __('messages.session_info') ?? 'Session Information' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="small text-muted">{{ __('messages.driver') ?? 'Driver' }}</div>
                            <div class="fw-semibold">{{ $session->driver->full_name ?? '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted">{{ __('messages.date') ?? 'Date' }}</div>
                            <div class="fw-semibold">{{ $session->date ? $session->date->format('d/m/Y') : '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted">{{ __('messages.completed_by') ?? 'Completed By' }}</div>
                            <div class="fw-semibold">{{ $checklist->completedByUser->name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="small text-muted">{{ __('messages.completed_at') ?? 'Completed At' }}</div>
                            <div class="fw-semibold">{{ $checklist->completed_at ? $checklist->completed_at->format('d/m/Y H:i') : '—' }}</div>
                        </div>
                    </div>
                </div>

                @php $meta = $checklist->meta ?? []; @endphp

                {{-- Basic Information --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-person me-2 text-primary"></i>
                            {{ __('messages.basic_information') ?? 'Basic Information' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="small text-muted">{{ __('messages.name') ?? 'Name' }}</div>
                            <div class="fw-semibold">{{ $meta['name'] ?? '—' }}</div>
                        </div>
                        <div class="mb-2">
                            <div class="small text-muted">{{ __('messages.company') ?? 'Company' }}</div>
                            <div class="fw-semibold">{{ $meta['company'] ?? '—' }}</div>
                        </div>
                        <div class="mb-2">
                            <div class="small text-muted">{{ __('messages.realized_date') ?? 'Realized Date' }}</div>
                            <div class="fw-semibold">
                                @if(!empty($meta['realized_date']))
                                    {{ \Carbon\Carbon::parse($meta['realized_date'])->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="small text-muted">{{ __('messages.vehicle_registration_tractor') ?? 'Truck License Plate' }}</div>
                            <div class="fw-semibold">{{ $meta['vehicle_tractor_registration'] ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="small text-muted">{{ __('messages.vehicle_registration_citerne') ?? 'Citerne License Plate' }}</div>
                            <div class="fw-semibold">{{ $meta['vehicle_tanker_registration'] ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Test Results --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-clipboard-check me-2 text-primary"></i>
                            {{ __('messages.test_results') ?? 'Test Results' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%">{{ __('messages.test') ?? 'Test' }}</th>
                                        <th class="text-center" style="width: 60%">{{ __('messages.result') ?? 'Result' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold">{{ __('messages.alcohol_test') ?? 'ALCOOL' }}</td>
                                        <td class="text-center">
                                            @if(!empty($meta['test_alcohol_drug']['alcohol']))
                                                <span class="badge bg-info">{{ $meta['test_alcohol_drug']['alcohol'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">{{ __('messages.drugs_test') ?? 'DROGUES' }}</td>
                                        <td class="text-center">
                                            @if(!empty($meta['test_alcohol_drug']['drugs']))
                                                <span class="badge bg-info">{{ $meta['test_alcohol_drug']['drugs'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- EPI & ADR Controls --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-shield-check me-2 text-primary"></i>
                            {{ __('messages.controls') ?? 'Controls' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle table-bordered mb-0">
                                <tbody>
                                    <tr>
                                        <td style="width: 70%">{{ __('messages.epi_control') ?? 'Contrôle des EPI' }}</td>
                                        <td class="text-center fw-bold" style="width: 20%">2</td>
                                        <td class="text-center" style="width: 10%">
                                            @if(!empty($meta['epi_control']['exists']))
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-x-circle text-muted"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('messages.adr_equipment_control') ?? 'Contrôle des équipements ADR du véhicule' }}</td>
                                        <td class="text-center fw-bold">2</td>
                                        <td class="text-center">
                                            @if(!empty($meta['adr_equipment_control']['exists']))
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-x-circle text-muted"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Topics Covered --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-book me-2 text-primary"></i>
                            {{ __('messages.topics_covered_reminder') ?? 'Topics Covered (REMINDER)' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $topics = [
                                'observation_comportement' => __('messages.topic_observation_comportement') ?? 'Observation du comportement',
                                'procedure_stationnement' => __('messages.topic_procedure_stationnement') ?? 'Procédure de Stationnement',
                                'obligations_interdictions' => __('messages.topic_obligations_interdictions') ?? 'Obligations et interdictions du chauffeur',
                                'effet_fatigue_stress' => __('messages.topic_effet_fatigue_stress') ?? 'Effet de la fatigue et du stress sur le comportement',
                                'impact_medicaments_substances' => __('messages.topic_impact_medicaments_substances') ?? 'Impact de certains médicaments, du tabac, de l\'alcool, des narcotiques et des drogues, risques liés au sommeil',
                                'optimisation_carburant' => __('messages.topic_optimisation_carburant') ?? 'Optimisation de la consommation du carburant',
                                'code_route_conduite_defensive' => __('messages.topic_code_route_conduite_defensive') ?? 'Code de la route, signalisation transport, conduite défensive (l\'identification des dangers et l\'anticipation des risques)',
                                'utilisation_ralentisseur' => __('messages.topic_utilisation_ralentisseur') ?? 'Utilisation du ralentisseur',
                                'respect_plan_gestion_deplacement' => __('messages.topic_respect_plan_gestion_deplacement') ?? 'Respect de l\'application du plan de gestion de déplacement',
                                'procedure_urgence_sos' => __('messages.topic_procedure_urgence_sos') ?? 'Procédure en cas d\'urgence / Bouton SOS',
                                'permis_points' => __('messages.topic_permis_points') ?? 'Permis à points',
                            ];
                            $selectedTopics = is_array($meta['topics_covered'] ?? null) ? $meta['topics_covered'] : [];
                        @endphp
                        @if(!empty($selectedTopics))
                            <ul class="list-unstyled mb-0">
                                @foreach($topics as $key => $label)
                                    @if(in_array($key, $selectedTopics))
                                        <li class="mb-2">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <span class="small">{{ $label }}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted small mb-0">—</p>
                        @endif
                    </div>
                </div>

                {{-- General Notes --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-sticky me-2 text-primary"></i>
                            {{ __('messages.general_notes') ?? 'General Notes' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(!empty($meta['notes']))
                            <p class="mb-0 small">{{ $meta['notes'] }}</p>
                        @else
                            <p class="text-muted small mb-0">—</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
