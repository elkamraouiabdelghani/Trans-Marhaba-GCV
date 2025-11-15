<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.coaching_cabines_planning_title') ?? 'Planning Annuel' }} - {{ $year }}</title>
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
        
        .driver-name {
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
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 7pt;
            font-weight: bold;
        }
        
        .badge-planned {
            background: #3498db;
            color: white;
        }
        
        .badge-completed {
            background: #27ae60;
            color: white;
        }
        
        .badge-cancelled {
            background: #e74c3c;
            color: white;
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
        <h1>{{ __('messages.coaching_cabines_planning_title') ?? 'Planning Annuel des Sessions de Coaching' }}</h1>
        <div class="subtitle">{{ __('messages.year') ?? 'Année' }}: {{ $year }} - {{ __('messages.generated_on') ?? 'Généré le' }}: {{ date('d/m/Y à H:i') }}</div>
    </div>

    <div class="stats-row">
        <span class="stat-item">
            <span class="stat-label">{{ __('messages.total') ?? 'Total' }}:</span>
            <span class="stat-value">{{ number_format($totalSessions) }}</span>
        </span>
        <span class="stat-item">
            <span class="stat-label">{{ __('messages.status_planned') ?? 'Planifié' }}:</span>
            <span class="stat-value">{{ number_format($grandTotal['planned']) }}</span>
        </span>
        <span class="stat-item">
            <span class="stat-label">{{ __('messages.status_completed') ?? 'Terminé' }}:</span>
            <span class="stat-value">{{ number_format($grandTotal['completed']) }}</span>
        </span>
        <span class="stat-item">
            <span class="stat-label">{{ __('messages.completed_percentage') ?? 'Pourcentage de réalisation' }}:</span>
            <span class="stat-value">{{ number_format($completedPercentage, 1) }}%</span>
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="driver-name" style="width: 120px;color: #2c3e50;">{{ __('messages.driver') ?? 'Chauffeur' }}</th>
                @foreach($monthNames as $monthNum => $monthName)
                    <th colspan="3" class="month-header">{{ $monthName }}</th>
                @endforeach
                <th rowspan="2" class="total-cell" style="width: 60px;">{{ __('messages.total') ?? 'Total' }}</th>
            </tr>
            <tr>
                @foreach($monthNames as $monthNum => $monthName)
                    <th class="sub-header">P</th>
                    <th class="sub-header">R</th>
                    <th class="sub-header">NJ</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($planningData as $driverData)
                @php
                    $driver = $driverData['driver'];
                    $totalSessions = 0;
                    foreach($driverData['months'] as $monthData) {
                        $totalSessions += $monthData['planned'] + $monthData['completed'] + $monthData['cancelled'];
                    }
                @endphp
                <tr>
                    <td class="driver-name">{{ $driver->full_name }}</td>
                    @foreach($monthNames as $monthNum => $monthName)
                        @php
                            $monthData = $driverData['months'][$monthNum];
                            $planned = $monthData['planned'];
                            $completed = $monthData['completed'];
                            $cancelled = $monthData['cancelled'];
                        @endphp
                        <td>{{ $planned > 0 ? $planned : '-' }}</td>
                        <td>{{ $completed > 0 ? $completed : '-' }}</td>
                        <td>{{ $cancelled > 0 ? $cancelled : '-' }}</td>
                    @endforeach
                    <td class="total-cell">{{ $totalSessions }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="grand-total-cell">{{ __('messages.total') ?? 'Total' }}</td>
                @foreach($monthNames as $monthNum => $monthName)
                    <td class="grand-total-cell">{{ $monthTotals[$monthNum]['planned'] }}</td>
                    <td class="grand-total-cell">{{ $monthTotals[$monthNum]['completed'] }}</td>
                    <td class="grand-total-cell">{{ $monthTotals[$monthNum]['cancelled'] }}</td>
                @endforeach
                <td class="grand-total-cell">{{ $grandTotal['planned'] + $grandTotal['completed'] + $grandTotal['cancelled'] }}</td>
            </tr>
            <tr>
                <td class="grand-total-cell">{{ __('messages.grand_total') ?? 'Total général' }}</td>
                @foreach($monthNames as $monthNum => $monthName)
                    <td colspan="3" class="grand-total-cell">
                        {{ $monthTotals[$monthNum]['planned'] + $monthTotals[$monthNum]['completed'] + $monthTotals[$monthNum]['cancelled'] }}
                    </td>
                @endforeach
                <td class="grand-total-cell">
                    {{ $grandTotal['planned'] + $grandTotal['completed'] + $grandTotal['cancelled'] }}
                </td>
            </tr>
            <tr>
                <td class="percentage-cell">{{ __('messages.completed_percentage') ?? 'Pourcentage de réalisation' }}</td>
                @foreach($monthNames as $monthNum => $monthName)
                    @php
                        $monthTotal = $monthTotals[$monthNum]['planned'] + $monthTotals[$monthNum]['completed'] + $monthTotals[$monthNum]['cancelled'];
                        $monthCompleted = $monthTotals[$monthNum]['completed'];
                        $monthPercentage = $monthTotal > 0 ? round(($monthCompleted / $monthTotal) * 100, 1) : 0;
                    @endphp
                    <td colspan="3" class="percentage-cell">
                        {{ number_format($monthPercentage, 1) }}%
                    </td>
                @endforeach
                <td class="percentage-cell">
                    {{ number_format($completedPercentage, 1) }}%
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="legend">
        <span class="legend-item">
            <span class="badge badge-planned">P</span> = {{ __('messages.planifie') ?? 'Planifié' }}
        </span>
        <span class="legend-item">
            <span class="badge badge-completed">R</span> = {{ __('messages.realise') ?? 'Réalisé' }}
        </span>
        <span class="legend-item">
            <span class="badge badge-cancelled">NJ</span> = {{ __('messages.non_justifie') ?? 'Non justifié' }}
        </span>
    </div>

    <div class="footer">
        <p>{{ __('messages.generated_on') ?? 'Généré le' }}: {{ date('d/m/Y à H:i') }} | {{ __('messages.year') ?? 'Année' }}: {{ $year }}</p>
    </div>
</body>
</html>

