<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.coaching_session') ?? 'Session de Coaching' }} - {{ $coachingCabine->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #333;
            background: #fff;
            padding: 0 40px;
            margin: 0;
        }
        
        .header {
            border-bottom: 3px solid #2c3e50;
            padding: 15px 0;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 20pt;
            margin-bottom: 3px;
            font-weight: bold;
        }
        
        .header .subtitle {
            color: #7f8c8d;
            font-size: 10pt;
        }
        
        .info-section {
            margin-bottom: 18px;
        }
        
        .section-title {
            background: #ecf0f1;
            color: #2c3e50;
            padding: 8px 12px;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
            border-left: 4px solid #3498db;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 35%;
            padding: 6px 8px;
            font-weight: bold;
            color: #555;
            border-bottom: 1px solid #ecf0f1;
            font-size: 9.5pt;
        }
        
        .info-value {
            display: table-cell;
            padding: 6px 8px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 9.5pt;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8.5pt;
            font-weight: bold;
        }
        
        .badge-primary {
            background: #3498db;
            color: white;
        }
        
        .badge-success {
            background: #27ae60;
            color: white;
        }
        
        .badge-warning {
            background: #f39c12;
            color: white;
        }
        
        .badge-danger {
            background: #e74c3c;
            color: white;
        }
        
        .badge-info {
            background: #17a2b8;
            color: white;
        }
        
        .text-content {
            padding: 10px 12px;
            background: #f8f9fa;
            border-left: 3px solid #3498db;
            margin-top: 8px;
            line-height: 1.6;
            font-size: 9.5pt;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            color: #7f8c8d;
            font-size: 8pt;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .score-badge {
            font-size: 10pt;
            padding: 4px 10px;
        }
        
        .score-excellent {
            background: #27ae60;
            color: white;
        }
        
        .score-good {
            background: #f39c12;
            color: white;
        }
        
        .score-poor {
            background: #e74c3c;
            color: white;
        }
        
        @page {
            margin: 20mm 15mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('messages.coaching_session') ?? 'Session de Coaching' }}</h1>
        <div class="subtitle">{{ __('messages.session_details') ?? 'Détails de la session' }} - {{ date('d/m/Y H:i') }}</div>
    </div>

    <div class="info-section">
        <div class="section-title">{{ __('messages.basic_information') ?? 'Informations de base' }}</div>
        
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">{{ __('messages.driver') ?? 'Chauffeur' }}:</div>
                <div class="info-value">{{ $coachingCabine->driver->full_name ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.flotte') ?? 'Flotte' }}:</div>
                <div class="info-value">
                    @if($coachingCabine->flotte)
                        <span class="badge badge-info">{{ $coachingCabine->flotte->name }}</span>
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.type') ?? 'Type' }}:</div>
                <div class="info-value">
                    @php
                        $typeLabels = [
                            'initial' => __('messages.type_initial') ?? 'Initial',
                            'suivi' => __('messages.type_suivi') ?? 'Suivi',
                            'correctif' => __('messages.type_correctif') ?? 'Correctif'
                        ];
                        $typeColors = [
                            'initial' => 'primary',
                            'suivi' => 'info',
                            'correctif' => 'warning'
                        ];
                    @endphp
                    <span class="badge badge-{{ $typeColors[$coachingCabine->type] ?? 'secondary' }}">
                        {{ $typeLabels[$coachingCabine->type] ?? $coachingCabine->type }}
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.status') ?? 'Statut' }}:</div>
                <div class="info-value">
                    @php
                        $statusLabels = [
                            'planned' => __('messages.status_planned') ?? 'Planifié',
                            'in_progress' => __('messages.status_in_progress') ?? 'En cours',
                            'completed' => __('messages.status_completed') ?? 'Terminé',
                            'cancelled' => __('messages.status_cancelled') ?? 'Annulé'
                        ];
                        $statusColors = [
                            'planned' => 'primary',
                            'in_progress' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                    @endphp
                    <span class="badge badge-{{ $statusColors[$coachingCabine->status] ?? 'secondary' }}">
                        {{ $statusLabels[$coachingCabine->status] ?? $coachingCabine->status }}
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.from_date') ?? 'Date de début' }}:</div>
                <div class="info-value">{{ $coachingCabine->date ? $coachingCabine->date->format('d/m/Y') : '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.date_fin') ?? 'Date de fin' }}:</div>
                <div class="info-value">{{ $coachingCabine->date_fin ? $coachingCabine->date_fin->format('d/m/Y') : '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.moniteur') ?? 'Moniteur' }}:</div>
                <div class="info-value">{{ $coachingCabine->moniteur ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.validity_days') ?? 'Jours de validité' }}:</div>
                <div class="info-value">{{ $coachingCabine->validity_days ?? '-' }} {{ __('messages.days') ?? 'jours' }}</div>
            </div>
            @if($coachingCabine->score !== null)
            <div class="info-row">
                <div class="info-label">{{ __('messages.score') ?? 'Score' }}:</div>
                <div class="info-value">
                    <span class="badge score-badge score-{{ $coachingCabine->score >= 70 ? 'excellent' : ($coachingCabine->score >= 50 ? 'good' : 'poor') }}">
                        {{ $coachingCabine->score }}/100
                    </span>
                </div>
            </div>
            @endif
            @if($coachingCabine->next_planning_session)
            <div class="info-row">
                <div class="info-label">{{ __('messages.next_planning_session') ?? 'Prochaine session planifiée' }}:</div>
                <div class="info-value">{{ $coachingCabine->next_planning_session->format('d/m/Y') }}</div>
            </div>
            @endif
        </div>
    </div>

    @if($coachingCabine->route_taken)
    <div class="info-section">
        <div class="section-title">{{ __('messages.route_taken') ?? 'Itinéraire emprunté' }}</div>
        <div class="text-content">
            {{ $coachingCabine->route_taken }}
        </div>
    </div>
    @endif

    @if($coachingCabine->assessment)
    <div class="info-section">
        <div class="section-title">{{ __('messages.assessment') ?? 'Évaluation' }}</div>
        <div class="text-content">
            {{ $coachingCabine->assessment }}
        </div>
    </div>
    @endif

    @if($coachingCabine->notes)
    <div class="info-section">
        <div class="section-title">{{ __('messages.notes') ?? 'Notes' }}</div>
        <div class="text-content">
            {{ $coachingCabine->notes }}
        </div>
    </div>
    @endif

    <div class="footer">
        <p>{{ __('messages.generated_on') ?? 'Généré le' }}: {{ date('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>

