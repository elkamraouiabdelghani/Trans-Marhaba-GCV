<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.journeys_map') ?? 'Journeys Map' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 11px;
        }
        .map-section {
            margin-bottom: 20px;
        }
        .map-image {
            width: 100%;
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }
        .journeys-list {
            margin-top: 20px;
        }
        .journeys-list h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #2563eb;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('messages.journeys') ?? 'Journeys' }}</h1>
        <p>{{ __('messages.map_export') ?? 'Map Export' }} - {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="map-section">
        @if(count($journeys) > 0)
            @php
                // Calculate center for display
                $lats = collect();
                $lngs = collect();
                foreach ($journeys as $journey) {
                    if ($journey->from_latitude && $journey->from_longitude) {
                        $lats->push($journey->from_latitude);
                        $lngs->push($journey->from_longitude);
                    }
                    if ($journey->to_latitude && $journey->to_longitude) {
                        $lats->push($journey->to_latitude);
                        $lngs->push($journey->to_longitude);
                    }
                }
                $centerLat = $lats->isNotEmpty() ? $lats->avg() : null;
                $centerLng = $lngs->isNotEmpty() ? $lngs->avg() : null;
            @endphp

            @if($centerLat && $centerLng)
                <div style="text-align: center; margin-bottom: 15px; page-break-inside: avoid;">
                    @if($mapImageBase64)
                        <img src="{{ $mapImageBase64 }}" alt="Journeys Map" class="map-image" style="max-height: 500px; width: 100%; border: 1px solid #ddd;" />
                    @else
                        <div style="height: 400px; background-color: #f5f5f5; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center;">
                            <p style="color: #999;">{{ __('messages.map_not_available') ?? 'Map image not available' }}</p>
                        </div>
                    @endif
                    <p style="font-size: 10px; color: #666; margin-top: 5px;">
                        <strong>{{ __('messages.map_center') ?? 'Map Center' }}:</strong> {{ number_format($centerLat, 6) }}, {{ number_format($centerLng, 6) }}
                        | <strong>{{ __('messages.total_journeys') ?? 'Total Journeys' }}:</strong> {{ count($journeys) }}
                    </p>
                </div>
            @endif

            <div class="journeys-list">
                <h3>{{ __('messages.journeys_list') ?? 'Journeys List' }} ({{ count($journeys) }})</h3>
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('messages.name') ?? 'Name' }}</th>
                            <th>{{ __('messages.from_location') ?? 'From Location' }}</th>
                            <th>{{ __('messages.from_location_name') ?? 'From Location Name' }}</th>
                            <th>{{ __('messages.to_location') ?? 'To Location' }}</th>
                            <th>{{ __('messages.to_location_name') ?? 'To Location Name' }}</th>
                            <th>{{ __('messages.total_score') ?? 'Total Score' }}</th>
                            <th>{{ __('messages.created_at') ?? 'Created At' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journeys as $journey)
                            <tr>
                                <td>
                                    <strong>🚗 {{ $journey->name }}</strong>
                                </td>
                                <td>
                                    @if($journey->from_latitude && $journey->from_longitude)
                                        <strong>{{ number_format($journey->from_latitude, 6) }}, {{ number_format($journey->from_longitude, 6) }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $journey->from_location_name ?? '-' }}</td>
                                <td>
                                    @if($journey->to_latitude && $journey->to_longitude)
                                        <strong>{{ number_format($journey->to_latitude, 6) }}, {{ number_format($journey->to_longitude, 6) }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $journey->to_location_name ?? '-' }}</td>
                                <td>{{ number_format($journey->total_score ?? 0, 2) }}</td>
                                <td>{{ $journey->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="text-align: center; color: #666; padding: 40px;">
                {{ __('messages.no_journeys_found') ?? 'No journeys found' }}
            </p>
        @endif
    </div>

    <div class="footer">
        <p>{{ __('messages.generated_at') ?? 'Generated at' }}: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>{{ __('messages.brand') ?? 'MARHABA' }}</p>
    </div>
</body>
</html>

