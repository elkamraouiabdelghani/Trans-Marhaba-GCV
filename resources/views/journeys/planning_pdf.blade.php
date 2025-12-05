<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.journeys_planning_title') ?? 'Planning Annuel' }} - {{ $year }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #333;
            background: #fff;
            padding: 10mm 8mm;
        }
        
        .header {
            border-bottom: 3px solid #2c3e50;
            padding: 10px 0;
            margin-bottom: 15px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 18pt;
            margin-bottom: 3px;
            font-weight: bold;
        }
        
        .header .subtitle {
            color: #7f8c8d;
            font-size: 9pt;
        }
        
        .stats-row {
            margin-bottom: 12px;
            padding: 8px;
            background: #f8f9fa;
            border-left: 3px solid #3498db;
        }
        
        .stats-row .stat-item {
            display: inline-block;
            margin-right: 20px;
            font-size: 9pt;
        }
        
        .stats-row .stat-label {
            font-weight: bold;
            color: #555;
        }
        
        .stats-row .stat-value {
            color: #2c3e50;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 7.5pt;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
        }
        
        thead th {
            background: #2c3e50;
            color: white;
            font-weight: bold;
            font-size: 8pt;
        }
        
        tbody td {
            background: white;
        }
        
        tbody tr:nth-child(even) td {
            background: #f8f9fa;
        }
        
        .journey-name {
            text-align: left;
            font-weight: bold;
            background: #ecf0f1 !important;
            padding-left: 8px;
        }
        
        .month-header {
            background: #34495e !important;
            color: white;
            font-weight: bold;
        }
        
        .sub-header {
            background: #7f8c8d !important;
            color: white;
            font-size: 7pt;
        }
        
        .total-cell {
            background: #3498db !important;
            color: white;
            font-weight: bold;
        }
        
        .grand-total-cell {
            background: #2c3e50 !important;
            color: white;
            font-weight: bold;
        }
        
        .percentage-cell {
            background: #f39c12 !important;
            color: white;
            font-weight: bold;
        }
        
        .bg-warning {
            background-color: #ffc107 !important;
            color: #000;
        }
        
        .bg-primary {
            background-color: #0d6efd !important;
            color: #fff;
        }
        
        .bg-success {
            background-color: #198754 !important;
            color: #fff;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            color: #7f8c8d;
            font-size: 7pt;
        }
        
        .legend {
            margin-top: 10px;
            padding: 8px;
            background: #f8f9fa;
            font-size: 7.5pt;
        }
        
        .legend-item {
            display: inline-block;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('messages.journeys_planning_title') ?? 'Planning Annuel des Inspections des Trajets' }}</h1>
        <div class="subtitle">
            {{ __('messages.year') ?? 'Année' }}: {{ $year }}
            - {{ __('messages.generated_on') ?? 'Généré le' }}: {{ date('d/m/Y à H:i') }}
        </div>
    </div>

    @if($totalPlanned > 0 || $totalRealized > 0)
        <div class="stats-row">
            <span class="stat-item">
                <span class="stat-label">{{ __('messages.total_planned') ?? 'Total Planifié' }}:</span>
                <span class="stat-value">{{ number_format($totalPlanned) }}</span>
            </span>
            <span class="stat-item">
                <span class="stat-label">{{ __('messages.total_realized') ?? 'Total Réalisé' }}:</span>
                <span class="stat-value">{{ number_format($totalRealized) }}</span>
            </span>
            <span class="stat-item">
                <span class="stat-label">{{ __('messages.completion_rate') ?? 'Taux de Réalisation' }}:</span>
                <span class="stat-value">{{ number_format($percentage, 1) }}%</span>
            </span>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="journey-name" style="width: 120px;color: #2c3e50;">{{ __('messages.journeys') ?? 'Trajets' }}</th>
                @foreach($months as $monthNum => $monthName)
                    <th colspan="3" class="month-header">{{ $monthName }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($months as $monthNum => $monthName)
                    <th class="sub-header">P</th>
                    <th class="sub-header">R</th>
                    <th class="sub-header">NJ</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($planningData as $row)
                @php
                    $journey = $row['journey'];
                    $realized = $row['realized'];
                    $planned = $row['planned'];
                    $nj = $row['nj'];
                @endphp
                <tr>
                    <td class="journey-name">{{ $journey->name }}</td>
                    @foreach($months as $monthNum => $monthName)
                        @php
                            $plannedCount = $planned[$monthNum] ?? 0;
                            $realizedCount = $realized[$monthNum] ?? 0;
                            $njCount = $nj[$monthNum] ?? 0;

                            // Background colors by type
                            $pClass = $plannedCount > 0 ? 'bg-warning' : '';
                            $rClass = $realizedCount > 0 ? 'bg-primary' : '';
                            $njClass = $njCount > 0 ? 'bg-success' : '';
                        @endphp
                        <td class="{{ $pClass }}">
                            {{ $plannedCount > 0 ? $plannedCount : '-' }}
                        </td>
                        <td class="{{ $rClass }}">
                            {{ $realizedCount > 0 ? $realizedCount : '-' }}
                        </td>
                        <td class="{{ $njClass }}">
                            {{ $njCount > 0 ? $njCount : '-' }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        @if($totalPlanned > 0 || $totalRealized > 0)
            <tfoot>
                <tr>
                    <td class="grand-total-cell">{{ __('messages.total') ?? 'Total' }}</td>
                    @foreach($months as $monthNum => $monthName)
                        @php
                            $monthPlanned = 0;
                            $monthRealized = 0;
                            $monthNj = 0;
                            foreach ($planningData as $row) {
                                $monthPlanned += $row['planned'][$monthNum] ?? 0;
                                $monthRealized += $row['realized'][$monthNum] ?? 0;
                                $monthNj += $row['nj'][$monthNum] ?? 0;
                            }
                        @endphp
                        <td class="grand-total-cell">{{ $monthPlanned > 0 ? $monthPlanned : '-' }}</td>
                        <td class="grand-total-cell">{{ $monthRealized > 0 ? $monthRealized : '-' }}</td>
                        <td class="grand-total-cell">{{ $monthNj > 0 ? $monthNj : '-' }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td class="percentage-cell">{{ __('messages.completion_rate') ?? 'Taux de Réalisation' }}</td>
                    @foreach($months as $monthNum => $monthName)
                        @php
                            $monthPlanned = 0;
                            $monthRealized = 0;
                            foreach ($planningData as $row) {
                                $monthPlanned += $row['planned'][$monthNum] ?? 0;
                                $monthRealized += $row['realized'][$monthNum] ?? 0;
                            }
                            $monthPercentage = $monthPlanned > 0 ? round(($monthRealized / $monthPlanned) * 100, 1) : 0;
                        @endphp
                        <td colspan="3" class="percentage-cell">
                            {{ number_format($monthPercentage, 1) }}%
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="legend">
        <span class="legend-item">
            <strong>P</strong> = {{ __('messages.planned_only') ?? 'Planifié (pas encore de checklist)' }}
        </span>
        <span class="legend-item">
            <strong>R</strong> = {{ __('messages.realized_only') ?? 'Réalisé (non planifié)' }}
        </span>
        <span class="legend-item">
            <strong>NJ</strong> = {{ __('messages.planned_and_realized') ?? 'Planifié et réalisé' }}
        </span>
    </div>

    <div class="footer">
        <p>{{ __('messages.generated_on') ?? 'Généré le' }}: {{ date('d/m/Y à H:i') }} | {{ __('messages.year') ?? 'Année' }}: {{ $year }}</p>
    </div>
</body>
</html>

