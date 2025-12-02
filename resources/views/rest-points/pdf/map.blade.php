<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.rest_points_map') ?? 'Rest Points Map' }}</title>
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
        .points-list {
            margin-top: 20px;
        }
        .points-list h3 {
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
            background-color: #4472C4;
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
        .type-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .type-area { background-color: #d4edda; color: #155724; }
        .type-station { background-color: #cfe2ff; color: #084298; }
        .type-parking { background-color: #fff3cd; color: #856404; }
        .type-other { background-color: #e2e3e5; color: #383d41; }
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
        <h1>{{ __('messages.rest_points') ?? 'Rest Points' }}</h1>
        <p>{{ __('messages.map_export') ?? 'Map Export' }} - {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="map-section">
        @if(count($restPoints) > 0)
            @php
                // Calculate center for display
                $lats = $restPoints->pluck('latitude')->filter();
                $lngs = $restPoints->pluck('longitude')->filter();
                $centerLat = $lats->isNotEmpty() ? $lats->avg() : null;
                $centerLng = $lngs->isNotEmpty() ? $lngs->avg() : null;
            @endphp

            @if($centerLat && $centerLng)
                <div style="text-align: center; margin-bottom: 15px; page-break-inside: avoid;">
                    @if($mapImageBase64)
                        <img src="{{ $mapImageBase64 }}" alt="Rest Points Map" class="map-image" style="max-height: 500px; width: 100%; border: 1px solid #ddd;" />
                    @elseif($staticMapUrl)
                        <img src="{{ $staticMapUrl }}" alt="Rest Points Map" class="map-image" style="max-height: 500px; width: 100%; border: 1px solid #ddd;" />
                    @else
                        <div style="height: 400px; background-color: #f5f5f5; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center;">
                            <p style="color: #999;">{{ __('messages.map_not_available') ?? 'Map image not available' }}</p>
                        </div>
                    @endif
                    <p style="font-size: 10px; color: #666; margin-top: 5px;">
                        <strong>{{ __('messages.map_center') ?? 'Map Center' }}:</strong> {{ number_format($centerLat, 6) }}, {{ number_format($centerLng, 6) }}
                        | <strong>{{ __('messages.total_points') ?? 'Total Points' }}:</strong> {{ count($restPoints) }}
                    </p>
                    @if($staticMapUrl && str_contains($staticMapUrl, 'maps.googleapis.com'))
                        <p style="font-size: 9px; color: #28a745; margin-top: 3px; font-weight: bold;">
                            ✓ {{ __('messages.markers_visible') ?? 'Markers are visible on the map above' }}
                        </p>
                    @elseif($mapImageBase64 || $staticMapUrl)
                        <p style="font-size: 9px; color: #999; margin-top: 3px;">
                            {{ __('messages.map_note') ?? 'Note: Map shows the center area. See table below for all point coordinates with location icons.' }}
                        </p>
                    @endif
                </div>
            @endif

            <div class="points-list">
                <h3>{{ __('messages.rest_points_list') ?? 'Rest Points List' }} ({{ count($restPoints) }})</h3>
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('messages.name') ?? 'Name' }}</th>
                            <th>{{ __('messages.type') ?? 'Type' }}</th>
                            <th>{{ __('messages.coordinates') ?? 'Coordinates' }}</th>
                            <th>{{ __('messages.description') ?? 'Description' }}</th>
                            <th>{{ __('messages.created_at') ?? 'Created At' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($restPoints as $point)
                            <tr>
                                <td>
                                    <strong>📍 {{ $point->name }}</strong>
                                </td>
                                <td>
                                    <span class="type-badge type-{{ $point->type }}">
                                        {{ $point->type_label }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ number_format($point->latitude, 6) }}, {{ number_format($point->longitude, 6) }}</strong>
                                </td>
                                <td>{{ Str::limit($point->description ?? '', 50) }}</td>
                                <td>{{ $point->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="text-align: center; color: #666; padding: 40px;">
                {{ __('messages.no_rest_points_found') ?? 'No rest points found' }}
            </p>
        @endif
    </div>

    <div class="footer">
        <p>{{ __('messages.generated_at') ?? 'Generated at' }}: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>{{ __('messages.brand') ?? 'MARHABA' }}</p>
    </div>
</body>
</html>

