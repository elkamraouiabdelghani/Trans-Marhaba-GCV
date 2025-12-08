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
        
        .map-container {
            text-align: center;
            margin: 15px 0;
            page-break-inside: avoid;
        }
        
        .map-image {
            max-width: 100%;
            height: auto;
            border: 2px solid #ddd;
            border-radius: 4px;
        }
        
        .rest-places-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }
        
        .rest-places-list li {
            padding: 6px 10px;
            margin: 4px 0;
            background: #f8f9fa;
            border-left: 3px solid #3498db;
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
                    <span class="badge badge-{{ $coachingCabine->getTypeColor() }}">
                        {{ $coachingCabine->getTypeTitle() }}
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

    {{-- Route Information --}}
    @if($coachingCabine->from_latitude && $coachingCabine->from_longitude && $coachingCabine->to_latitude && $coachingCabine->to_longitude)
    <div class="info-section">
        <div class="section-title">{{ __('messages.route_taken') ?? 'Itinéraire emprunté' }}</div>
        
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">{{ __('messages.from_location') ?? 'Lieu de départ' }}:</div>
                <div class="info-value">
                    @if($coachingCabine->from_location_name)
                        <strong>{{ $coachingCabine->from_location_name }}</strong>
                    @endif
                    <small class="text-muted">({{ number_format($coachingCabine->from_latitude, 6) }}, {{ number_format($coachingCabine->from_longitude, 6) }})</small>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.to_location') ?? 'Lieu d\'arrivée' }}:</div>
                <div class="info-value">
                    @if($coachingCabine->to_location_name)
                        <strong>{{ $coachingCabine->to_location_name }}</strong>
                    @endif
                    <small class="text-muted">({{ number_format($coachingCabine->to_latitude, 6) }}, {{ number_format($coachingCabine->to_longitude, 6) }})</small>
                </div>
            </div>
            @php
                // Calculate distance using Haversine formula
                $lat1 = deg2rad($coachingCabine->from_latitude);
                $lon1 = deg2rad($coachingCabine->from_longitude);
                $lat2 = deg2rad($coachingCabine->to_latitude);
                $lon2 = deg2rad($coachingCabine->to_longitude);
                
                $earthRadius = 6371; // Earth's radius in kilometers
                $dLat = $lat2 - $lat1;
                $dLon = $lon2 - $lon1;
                
                $a = sin($dLat / 2) * sin($dLat / 2) +
                     cos($lat1) * cos($lat2) *
                     sin($dLon / 2) * sin($dLon / 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distance = $earthRadius * $c;
            @endphp
            <div class="info-row">
                <div class="info-label">{{ __('messages.distance') ?? 'Distance' }}:</div>
                <div class="info-value">
                    <strong>{{ number_format($distance, 1) }} km</strong>
                </div>
            </div>
        </div>
        
        {{-- Map Image --}}
        @if($mapImageBase64)
        <div class="map-container">
            <img src="{{ $mapImageBase64 }}" alt="Route Map" class="map-image" />
        </div>
        @elseif($staticMapUrl)
        <div class="map-container">
            <img src="{{ $staticMapUrl }}" alt="Route Map" class="map-image" />
        </div>
        @elseif($coachingCabine->from_latitude && $coachingCabine->from_longitude && $coachingCabine->to_latitude && $coachingCabine->to_longitude)
        <div class="map-container">
            <div style="height: 400px; background-color: #f5f5f5; border: 2px solid #ddd; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                <p style="color: #999; font-size: 12pt;">{{ __('messages.map_not_available') ?? 'Map image not available' }}</p>
            </div>
        </div>
        @endif
    </div>
    @elseif($coachingCabine->route_taken)
    <div class="info-section">
        <div class="section-title">{{ __('messages.route_taken') ?? 'Itinéraire emprunté' }}</div>
        <div class="text-content">
            {{ $coachingCabine->route_taken }}
        </div>
    </div>
    @endif

    {{-- Rest Places --}}
    @if($coachingCabine->rest_places && count($coachingCabine->rest_places) > 0)
    <div class="info-section">
        <div class="section-title">{{ __('messages.rest_places') ?? 'Lieux de repos' }}</div>
        <ul class="rest-places-list">
            @foreach($coachingCabine->rest_places as $index => $place)
                <li>
                    <strong>{{ __('messages.day') ?? 'Jour' }} {{ $index + 1 }}:</strong> {{ $place }}
                </li>
            @endforeach
        </ul>
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

