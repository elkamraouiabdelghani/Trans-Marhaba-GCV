<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h1 class="h4 mb-1">{{ __('messages.coaching_checklist_title') ?? 'Checklist de coaching' }}</h1>
                <p class="text-muted mb-0">{{ __('messages.coaching_checklist_subtitle') ?? 'Saisissez les scores et informations de la session' }}</p>
            </div>
            <a href="{{ route('coaching-cabines.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> {{ __('messages.back_to_list') }}
            </a>
        </div>

        <div class="row w-100 mx-auto">
            <div class="col-md-10 mx-auto card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <div class="text-muted small">
                        {{ $session->driver->full_name ?? '-' }} &middot;
                        {{ $session->date ? $session->date->format('d/m/Y') : '-' }}
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('coaching.checklists.store', $session) }}">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('messages.name') ?? 'Name' }} <span class="text-danger">*</span></label>
                                <input type="text" name="meta[name]" class="form-control" value="{{ old('meta.name', $session->driver->full_name ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('messages.company') ?? 'Company' }} <span class="text-danger">*</span></label>
                                <input type="text" name="meta[company]" class="form-control" value="{{ old('meta.company', 'Trans Marhaba') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('messages.realized_date') ?? 'Realized Date' }} <span class="text-danger">*</span></label>
                                <input type="date" name="meta[realized_date]" class="form-control" value="{{ old('meta.realized_date', $session->date_fin ? $session->date_fin->format('Y-m-d') : date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('messages.vehicle_registration_tractor') ?? 'Truck License Plate' }}</label>
                                <input type="text" name="meta[vehicle_tractor_registration]" class="form-control" value="{{ old('meta.vehicle_tractor_registration', $session->driver->assignedVehicle->license_plate ?? '') }}" placeholder="{{ __('messages.vehicle_registration_tractor') ?? 'Truck License Plate' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('messages.vehicle_registration_citerne') ?? 'Citerne License Plate' }}</label>
                                <input type="text" name="meta[vehicle_tanker_registration]" class="form-control" value="{{ old('meta.vehicle_tanker_registration') }}" placeholder="{{ __('messages.vehicle_registration_citerne') ?? 'Citerne License Plate' }}">
                            </div>
                        </div>
    
                        @foreach($categories as $category)
                            <div class="mb-4">
                                <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                    <table class="table table-sm align-middle table-bordered" style="min-width: 600px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ $category->name }}</th>
                                                <th class="text-center" style="width: 10%">{{ __('messages.score') ?? 'Score' }}</th>
                                                <th class="text-center" style="width: 10%">
                                                    {{ __('messages.check') ?? 'Check' }}
                                                    <input class="form-check-input" style="margin-bottom: 6px !important;" type="checkbox" id="checkAllItems_{{ $category->id }}" title="{{ __('messages.check_all') ?? 'Check All' }}">
                                                </th>
                                                <th style="width: 30%">{{ __('messages.comment') ?? 'Comment' }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($category->items as $item)
                                                <tr>
                                                    <td>{{ $item->label }}</td>
                                                    <td class="text-center fw-bold">{{ $item->score ?? 1 }}</td>
                                                    <td class="text-center">
                                                        <input class="form-check-input category-{{ $category->id }}-item-checkbox" type="checkbox" name="answers[{{ $item->id }}][checked]" id="item{{ $item->id }}_checked" value="1" {{ old('answers.' . $item->id . '.checked') ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="answers[{{ $item->id }}][comment]" class="form-control form-control-sm" value="{{ old('answers.' . $item->id . '.comment') }}" placeholder="{{ __('messages.comment') ?? 'Comment' }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach

                        <div class="mb-4">
                            <h6 class="mb-2 fw-bold">{{ __('messages.test_results') ?? 'Test Results' }}</h6>
                            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                <table class="table table-sm align-middle table-bordered" style="min-width: 500px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 20%">{{ __('messages.test') ?? 'Test' }}</th>
                                            <th class="text-center" style="width: 20%">{{ __('messages.practique') ?? 'PRATIQUE' }}</th>
                                            <th class="text-center" style="width: 20%">{{ __('messages.non_practique') ?? 'NON PRATIQUE' }}</th>
                                            <th class="text-center" style="width: 20%">{{ __('messages.positif') ?? 'POSITIF' }}</th>
                                            <th class="text-center" style="width: 20%">{{ __('messages.negatif') ?? 'NEGATIF' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold">{{ __('messages.alcohol_test') ?? 'ALCOOL' }}</td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="radio" name="meta[test_alcohol_drug][alcohol]" id="alcohol_practique" value="PRATIQUE" {{ old('meta.test_alcohol_drug.alcohol') == 'PRATIQUE' ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="radio" name="meta[test_alcohol_drug][alcohol]" id="alcohol_non_practique" value="NON PRATIQUE" {{ old('meta.test_alcohol_drug.alcohol') == 'NON PRATIQUE' ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="radio" name="meta[test_alcohol_drug][alcohol]" id="alcohol_positif" value="POSITIF" {{ old('meta.test_alcohol_drug.alcohol') == 'POSITIF' ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="radio" name="meta[test_alcohol_drug][alcohol]" id="alcohol_negatif" value="NEGATIF" {{ old('meta.test_alcohol_drug.alcohol') == 'NEGATIF' ? 'checked' : '' }}>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">{{ __('messages.drugs_test') ?? 'DROGUES' }}</td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="radio" name="meta[test_alcohol_drug][drugs]" id="drugs_practique" value="PRATIQUE" {{ old('meta.test_alcohol_drug.drugs') == 'PRATIQUE' ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="radio" name="meta[test_alcohol_drug][drugs]" id="drugs_non_practique" value="NON PRATIQUE" {{ old('meta.test_alcohol_drug.drugs') == 'NON PRATIQUE' ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="radio" name="meta[test_alcohol_drug][drugs]" id="drugs_positif" value="POSITIF" {{ old('meta.test_alcohol_drug.drugs') == 'POSITIF' ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="radio" name="meta[test_alcohol_drug][drugs]" id="drugs_negatif" value="NEGATIF" {{ old('meta.test_alcohol_drug.drugs') == 'NEGATIF' ? 'checked' : '' }}>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                <table class="table table-sm align-middle table-bordered" style="min-width: 500px;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 75%">{{ __('messages.epi_control') ?? 'Contrôle des EPI' }}</td>
                                            <td class="text-center fw-bold" style="width: 20%">2</td>
                                            <td style="width: 5%" class="text-center">
                                                <input class="form-check-input" type="checkbox" name="meta[epi_control][exists]" id="epi_control_exists" value="1" {{ old('meta.epi_control.exists') ? 'checked' : '' }}>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('messages.adr_equipment_control') ?? 'Contrôle des équipements ADR du véhicule' }}</td>
                                            <td class="text-center fw-bold">2</td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox" name="meta[adr_equipment_control][exists]" id="adr_equipment_control_exists" value="1" {{ old('meta.adr_equipment_control.exists') ? 'checked' : '' }}>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 d-flex flex-column">
                                <h6 class="mb-2 fw-bold">{{ __('messages.topics_covered_reminder') ?? 'Thèmes abordés (RAPPEL)' }}</h6>
                                <div class="table-responsive flex-grow-1" id="topicsTableContainer" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                    <table class="table table-sm align-middle table-bordered mb-0" style="min-width: 400px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 10%" class="text-center">
                                                    <input class="form-check-input" type="checkbox" id="checkAllTopics" title="{{ __('messages.check_all') ?? 'Check All' }}">
                                                </th>
                                                <th>{{ __('messages.topic') ?? 'Topic' }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
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
                                                $oldTopics = old('meta.topics_covered', []);
                                            @endphp
                                            @foreach($topics as $key => $label)
                                                <tr>
                                                    <td class="text-center">
                                                        <input class="form-check-input topic-checkbox" type="checkbox" name="meta[topics_covered][]" id="topic_{{ $key }}" value="{{ $key }}" {{ in_array($key, $oldTopics) ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <label class="form-check-label mb-0" for="topic_{{ $key }}">
                                                            {{ $label }}
                                                        </label>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex flex-column">
                                <h6 class="mb-2 fw-bold">{{ __('messages.general_notes') ?? 'General Notes' }}</h6>
                                <textarea name="meta[notes]" id="generalNotesTextarea" class="form-control flex-grow-1" style="resize: none;" placeholder="{{ __('messages.enter_general_notes') ?? 'Enter general notes here...' }}">{{ old('meta.notes') }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-save me-1"></i> {{ __('messages.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkAllCheckbox = document.getElementById('checkAllTopics');
                const topicCheckboxes = document.querySelectorAll('.topic-checkbox');

                if (checkAllCheckbox && topicCheckboxes.length > 0) {
                    // Check/uncheck all topics
                    checkAllCheckbox.addEventListener('change', function() {
                        topicCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });

                    // Update "check all" state when individual checkboxes change
                    topicCheckboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const allChecked = Array.from(topicCheckboxes).every(cb => cb.checked);
                            const someChecked = Array.from(topicCheckboxes).some(cb => cb.checked);
                            checkAllCheckbox.checked = allChecked;
                            checkAllCheckbox.indeterminate = someChecked && !allChecked;
                        });
                    });

                    // Initialize "check all" state
                    const allChecked = Array.from(topicCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(topicCheckboxes).some(cb => cb.checked);
                    checkAllCheckbox.checked = allChecked;
                    checkAllCheckbox.indeterminate = someChecked && !allChecked;
                }

                // Match textarea height to table height
                function matchTextareaHeight() {
                    const topicsTableContainer = document.getElementById('topicsTableContainer');
                    const generalNotesTextarea = document.getElementById('generalNotesTextarea');
                    
                    if (topicsTableContainer && generalNotesTextarea) {
                        const tableHeight = topicsTableContainer.offsetHeight;
                        generalNotesTextarea.style.height = tableHeight + 'px';
                    }
                }

                // Match heights on load and resize
                matchTextareaHeight();
                window.addEventListener('resize', matchTextareaHeight);

                // Check all items per category
                document.querySelectorAll('[id^="checkAllItems_"]').forEach(function(checkAllCheckbox) {
                    const categoryId = checkAllCheckbox.id.replace('checkAllItems_', '');
                    const itemCheckboxes = document.querySelectorAll('.category-' + categoryId + '-item-checkbox');

                    if (itemCheckboxes.length > 0) {
                        // Check/uncheck all items in this category
                        checkAllCheckbox.addEventListener('change', function() {
                            itemCheckboxes.forEach(checkbox => {
                                checkbox.checked = this.checked;
                            });
                        });

                        // Update "check all" state when individual checkboxes change
                        itemCheckboxes.forEach(checkbox => {
                            checkbox.addEventListener('change', function() {
                                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                                const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
                                checkAllCheckbox.checked = allChecked;
                                checkAllCheckbox.indeterminate = someChecked && !allChecked;
                            });
                        });

                        // Initialize "check all" state
                        const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                        const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
                        checkAllCheckbox.checked = allChecked;
                        checkAllCheckbox.indeterminate = someChecked && !allChecked;
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>

