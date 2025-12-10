<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 10px; 
            color: #111; 
            margin: 0;
            padding: 10px;
        }
        h1, h2, h3, h4 { 
            margin: 4px 0; 
        }
        h2 {
            font-size: 16px;
            color: #333;
        }
        h3 {
            font-size: 12px;
            color: #666;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 8px; 
            margin-bottom: 10px;
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 6px; 
            vertical-align: top; 
        }
        th { 
            background: #f5f5f5; 
            font-weight: bold;
            text-align: left;
        }
        .section-title { 
            margin-top: 12px; 
            margin-bottom: 6px;
            font-weight: bold; 
            font-size: 11px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
        }
        .small { 
            font-size: 9px; 
            color: #555; 
        }
        .meta-row {
            margin-bottom: 8px;
        }
        .meta-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .check-mark {
            color: #28a745;
            font-weight: bold;
        }
        .x-mark {
            color: #999;
        }
        .header-section {
            margin-bottom: 15px;
        }
        .footer-section {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #333;
        }
    </style>
</head>
<body>
    @php $meta = $checklist->meta ?? []; @endphp

    {{-- Header --}}
    <div class="header-section">
        <table>
            <tr>
                <td width="60%">
                    <h2>Trans Marhaba</h2>
                    <h3>{{ __('messages.coaching_checklist_title') ?? 'Checklist de coaching' }}</h3>
                </td>
                <td width="40%" style="text-align: right;">
                    {{-- <div class="small"><strong>{{ __('messages.session') }}:</strong> #{{ $session->id }}</div> --}}
                    <div class="small"><strong>{{ __('messages.date') }}:</strong> {{ $session->date ? $session->date->format('d/m/Y') : '—' }}</div>
                    <div class="small"><strong>{{ __('messages.driver') }}:</strong> {{ $session->driver->full_name ?? '—' }}</div>
                    <div class="small"><strong>{{ __('messages.completed_at') }}:</strong> {{ $checklist->completed_at ? $checklist->completed_at->format('d/m/Y H:i') : '—' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Score Summary --}}
    @php
        $totalScore = $checklist->getTotalScore();
        $status = $checklist->getScoreStatus();
    @endphp
    <div style="background: #f5f5f5; padding: 10px; margin-bottom: 15px; border: 2px solid #333;">
        <table style="margin: 0; border: none;">
            <tr style="border: none;">
                <td width="50%" style="border: none; padding: 4px;">
                    <strong style="font-size: 14px;">{{ __('messages.checklist_score') ?? 'Checklist Score' }}: </strong>
                    <span style="font-size: 20px; font-weight: bold; color: #0066cc;">{{ $totalScore }}</span>
                </td>
                <td width="50%" style="border: none; padding: 4px; text-align: right;">
                    <strong style="font-size: 14px;">{{ __('messages.status') ?? 'Status' }}: </strong>
                    <span style="font-size: 14px; font-weight: bold;">{{ $status }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Basic Information --}}
    <div class="section-title">{{ __('messages.basic_information') ?? 'Basic Information' }}</div>
    <table>
        <tr>
            <td width="33%">
                <div class="meta-row">
                    <span class="meta-label">{{ __('messages.name') ?? 'Name' }}:</span>
                    <strong>{{ $meta['name'] ?? '—' }}</strong>
                </div>
            </td>
            <td width="33%">
                <div class="meta-row">
                    <span class="meta-label">{{ __('messages.company') ?? 'Company' }}:</span>
                    <strong>{{ $meta['company'] ?? 'Trans Marhaba' }}</strong>
                </div>
            </td>
            <td width="34%">
                <div class="meta-row">
                    <span class="meta-label">{{ __('messages.realized_date') ?? 'Realized Date' }}:</span>
                    <strong>
                        @if(!empty($meta['realized_date']))
                            {{ \Carbon\Carbon::parse($meta['realized_date'])->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </strong>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="meta-row">
                    <span class="meta-label">{{ __('messages.vehicle_registration_tractor') ?? 'Truck License Plate' }}:</span>
                    {{ $meta['vehicle_tractor_registration'] ?? '—' }}
                </div>
            </td>
            <td>
                <div class="meta-row">
                    <span class="meta-label">{{ __('messages.vehicle_registration_citerne') ?? 'Citerne License Plate' }}:</span>
                    {{ $meta['vehicle_tanker_registration'] ?? '—' }}
                </div>
            </td>
            <td>
                <div class="meta-row">
                    <span class="meta-label">{{ __('messages.completed_by') ?? 'Completed By' }}:</span>
                    {{ $checklist->completedByUser->name ?? '—' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Test Results --}}
    <div class="section-title">{{ __('messages.test_results') ?? 'Test Results' }}</div>
    <table>
        <thead>
            <tr>
                <th style="width: 30%">{{ __('messages.test') ?? 'Test' }}</th>
                <th style="width: 17.5%; text-align: center;">{{ __('messages.practique') ?? 'PRATIQUE' }}</th>
                <th style="width: 17.5%; text-align: center;">{{ __('messages.non_practique') ?? 'NON PRATIQUE' }}</th>
                <th style="width: 17.5%; text-align: center;">{{ __('messages.positif') ?? 'POSITIF' }}</th>
                <th style="width: 17.5%; text-align: center;">{{ __('messages.negatif') ?? 'NEGATIF' }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="fw-semibold">{{ __('messages.alcohol_test') ?? 'ALCOOL' }}</td>
                <td style="text-align: center;">{{ !empty($meta['test_alcohol_drug']['alcohol']) && $meta['test_alcohol_drug']['alcohol'] == 'PRATIQUE' ? '✓' : '' }}</td>
                <td style="text-align: center;">{{ !empty($meta['test_alcohol_drug']['alcohol']) && $meta['test_alcohol_drug']['alcohol'] == 'NON PRATIQUE' ? '✓' : '' }}</td>
                <td style="text-align: center;">{{ !empty($meta['test_alcohol_drug']['alcohol']) && $meta['test_alcohol_drug']['alcohol'] == 'POSITIF' ? '✓' : '' }}</td>
                <td style="text-align: center;">{{ !empty($meta['test_alcohol_drug']['alcohol']) && $meta['test_alcohol_drug']['alcohol'] == 'NEGATIF' ? '✓' : '' }}</td>
            </tr>
            <tr>
                <td class="fw-semibold">{{ __('messages.drugs_test') ?? 'DROGUES' }}</td>
                <td style="text-align: center;">{{ !empty($meta['test_alcohol_drug']['drugs']) && $meta['test_alcohol_drug']['drugs'] == 'PRATIQUE' ? '✓' : '' }}</td>
                <td style="text-align: center;">{{ !empty($meta['test_alcohol_drug']['drugs']) && $meta['test_alcohol_drug']['drugs'] == 'NON PRATIQUE' ? '✓' : '' }}</td>
                <td style="text-align: center;">{{ !empty($meta['test_alcohol_drug']['drugs']) && $meta['test_alcohol_drug']['drugs'] == 'POSITIF' ? '✓' : '' }}</td>
                <td style="text-align: center;">{{ !empty($meta['test_alcohol_drug']['drugs']) && $meta['test_alcohol_drug']['drugs'] == 'NEGATIF' ? '✓' : '' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- EPI & ADR Controls --}}
    <div class="section-title">{{ __('messages.controls') ?? 'Controls' }}</div>
    <table>
        <thead>
            <tr>
                <th style="width: 70%">{{ __('messages.control_item') ?? 'Control Item' }}</th>
                <th style="width: 20%; text-align: center;">{{ __('messages.score') ?? 'Score' }}</th>
                <th style="width: 10%; text-align: center;">{{ __('messages.checked') ?? 'Checked' }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ __('messages.epi_control') ?? 'Contrôle des EPI' }}</td>
                <td style="text-align: center;"><strong>2</strong></td>
                <td style="text-align: center;">
                    @if(!empty($meta['epi_control']['exists']))
                        <span class="check-mark">✓</span>
                    @else
                        <span class="x-mark">—</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>{{ __('messages.adr_equipment_control') ?? 'Contrôle des équipements ADR du véhicule' }}</td>
                <td style="text-align: center;"><strong>2</strong></td>
                <td style="text-align: center;">
                    @if(!empty($meta['adr_equipment_control']['exists']))
                        <span class="check-mark">✓</span>
                    @else
                        <span class="x-mark">—</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Checklist Items by Category --}}
    <div class="section-title">{{ __('messages.checklist_items') ?? 'Checklist Items' }}</div>
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
            <div style="margin-top: 8px; margin-bottom: 8px;">
                <strong style="font-size: 11px; color: #0066cc;">{{ $category->name }}</strong>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%">{{ __('messages.item') ?? 'Item' }}</th>
                        <th style="width: 15%; text-align: center;">{{ __('messages.score') ?? 'Score' }}</th>
                        <th style="width: 10%; text-align: center;">{{ __('messages.checked') ?? 'Checked' }}</th>
                        <th style="width: 25%">{{ __('messages.comment') ?? 'Comment' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($answers as $answer)
                        <tr>
                            <td>{{ $answer->item->label ?? '' }}</td>
                            <td style="text-align: center;"><strong>{{ $answer->score }}</strong></td>
                            <td style="text-align: center;"><span class="check-mark">✓</span></td>
                            <td class="small">{{ $answer->comment ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    @if($groupedAnswers->isEmpty())
        <table>
            <tr>
                <td style="text-align: center; padding: 20px; color: #999;">
                    {{ __('messages.no_items_found') ?? 'No items found' }}
                </td>
            </tr>
        </table>
    @endif

    {{-- Topics Covered --}}
    @if(!empty($meta['topics_covered']))
        <div class="section-title">{{ __('messages.topics_covered_reminder') ?? 'Topics Covered (REMINDER)' }}</div>
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
            $selectedTopics = is_array($meta['topics_covered']) ? $meta['topics_covered'] : [];
        @endphp
        <table>
            <tbody>
                @foreach($topics as $key => $label)
                    @if(in_array($key, $selectedTopics))
                        <tr>
                            <td style="width: 5%; text-align: center;"><span class="check-mark">✓</span></td>
                            <td style="width: 95%;">{{ $label }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- General Notes --}}
    @if(!empty($meta['notes']))
        <div class="section-title">{{ __('messages.general_notes') ?? 'General Notes' }}</div>
        <table>
            <tr>
                <td style="padding: 10px;">{{ $meta['notes'] }}</td>
            </tr>
        </table>
    @endif

    {{-- Footer with Signatures --}}
    <div class="footer-section">
        <table>
            <tr>
                <td width="50%" style="padding: 15px;">
                    <div style="margin-bottom: 30px;">
                        <strong>{{ __('messages.signature_instructor') ?? 'Instructeur / Contrôleur' }}</strong>
                    </div>
                    <div style="border-top: 1px solid #333; padding-top: 5px;">
                        {{ $meta['signature_instructor'] ?? '' }}
                    </div>
                </td>
                <td width="50%" style="padding: 15px;">
                    <div style="margin-bottom: 30px;">
                        <strong>{{ __('messages.signature_driver') ?? 'Chauffeur' }}</strong>
                    </div>
                    <div style="border-top: 1px solid #333; padding-top: 5px;">
                        {{ $meta['signature_driver'] ?? '' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
