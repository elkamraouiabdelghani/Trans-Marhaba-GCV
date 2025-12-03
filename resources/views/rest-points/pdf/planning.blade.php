<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.rest_points_planning_title') ?? 'Rest points annual inspection plan' }} - {{ $year }}</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
        }

        body {
            margin: 10px;
        }

        h1 {
            font-size: 16px;
            margin: 0 0 4px 0;
        }

        .subtitle {
            font-size: 11px;
            color: #555;
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 2px 4px;
            text-align: center;
        }

        th.rest-point {
            text-align: left;
        }

        .legend {
            margin: 6px 0 10px 0;
        }

        .legend-item {
            display: inline-block;
            margin-right: 12px;
            font-size: 10px;
        }

        .legend-color {
            display: inline-block;
            width: 12px;
            height: 8px;
            margin-right: 4px;
            border: 1px solid #999;
        }

        .bg-warning {
            background-color: #ffe59a;
        }

        .bg-primary {
            background-color: #9ec5fe;
        }

        .bg-success {
            background-color: #a3cfbb;
        }
    </style>
</head>
<body>
    <h1>{{ __('messages.rest_points_planning_title') ?? 'Rest points annual inspection plan' }} - {{ $year }}</h1>

    <div class="legend">
        <span class="legend-item">
            <span class="legend-color bg-warning"></span>
            {{ __('messages.planned_only') ?? 'Planned (no checklist yet)' }}
        </span>
        <span class="legend-item">
            <span class="legend-color bg-primary"></span>
            {{ __('messages.realized_only') ?? 'Realized (unplanned)' }}
        </span>
        <span class="legend-item">
            <span class="legend-color bg-success"></span>
            {{ __('messages.planned_and_realized') ?? 'Planned and realized' }}
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th class="rest-point" rowspan="2" style="min-width: 200px;">
                    {{ __('messages.rest_points') ?? 'Rest points' }}
                </th>
                @foreach($months as $monthNumber => $label)
                    <th colspan="3">{{ $label }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($months as $monthNumber => $label)
                    <th>P</th>
                    <th>R</th>
                    <th>NJ</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($planningData as $row)
                @php
                    /** @var \App\Models\RestPoint $rp */
                    $rp = $row['restPoint'];
                    $realized = $row['realized'];
                    $planned = $row['planned'];
                    $nj = $row['nj'];
                @endphp
                <tr>
                    <td class="rest-point">{{ $rp->name }}</td>
                    @foreach($months as $monthNumber => $label)
                        @php
                            $plannedCount = $planned[$monthNumber] ?? 0;
                            $realizedCount = $realized[$monthNumber] ?? 0;
                            $njCount = $nj[$monthNumber] ?? 0;

                            $pClass = $plannedCount > 0 ? 'bg-warning' : '';
                            $rClass = $realizedCount > 0 ? 'bg-primary' : '';
                            $njClass = $njCount > 0 ? 'bg-success' : '';
                        @endphp
                        <td class="{{ $pClass }}">{{ $plannedCount > 0 ? $plannedCount : '' }}</td>
                        <td class="{{ $rClass }}">{{ $realizedCount > 0 ? $realizedCount : '' }}</td>
                        <td class="{{ $njClass }}">{{ $njCount > 0 ? $njCount : '' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 1 + count($months) * 3 }}">
                        {{ __('messages.no_rest_points_found') ?? 'No rest points found.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @php
        $totalPlanned = $stats['planned'] ?? 0;
        $totalRealized = $stats['realized'] ?? 0;
        $percentage = $stats['percentage'] ?? 0;
    @endphp

    <br>

    <table style="width: 40%; margin-top: 8px;">
        <thead>
            <tr>
                <th>{{ __('messages.total_planned') ?? 'Total planned' }}</th>
                <th>{{ __('messages.total_realized') ?? 'Total realized' }}</th>
                <th>{{ __('messages.percentage') ?? 'Percentage' }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $totalPlanned }}</td>
                <td>{{ $totalRealized }}</td>
                <td>{{ $percentage }}%</td>
            </tr>
        </tbody>
    </table>
</body>
</html>


