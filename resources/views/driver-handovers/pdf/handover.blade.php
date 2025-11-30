<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de Remise de Véhicule</title>
    <style>
        @page { margin: 15px 10px; }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 9px; 
            color: #1f2937; 
        }
        .header { 
            margin-bottom: 15px; 
            padding-bottom: 10px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }
        .header-dates {
            text-align: right;
            font-size: 8px;
            color: #000;
        }
        .header-dates div {
            margin-bottom: 2px;
        }
        .form-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin-top: 10px;
            text-transform: uppercase;
        }
        .info-section {
            margin-bottom: 12px;
            font-size: 9px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #000;
        }
        .info-table td {
            width: 33.33% !important;
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
            background-color: #ffffff;
        }
        .info-label {
            font-weight: bold;
            color: #000;
            font-size: 9px;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .info-value {
            color: #000;
            font-size: 9px;
            padding: 4px 6px;
            vertical-align: middle;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px; 
            font-size: 8px;
            page-break-inside: avoid;
        }
        th, td { 
            border: 1px solid #94a3b8; 
            padding: 5px 3px; 
            text-align: left;
        }
        th { 
            background-color: #87ceeb; 
            color: #000; 
            font-weight: bold; 
            font-size: 9px;
            padding: 6px 4px;
            text-align: center;
        }
        td {
            background-color: #ffffff;
            font-size: 8px;
        }
        .section-header {
            background-color: #166534;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            font-size: 11px;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        .check-column {
            text-align: center;
            width: 50px;
            font-weight: bold;
            font-size: 10px;
        }
        .check-mark {
            color: #000;
        }
        .yes-mark {
            color: #059669;
            font-weight: bold;
        }
        .no-mark {
            color: #dc2626;
            font-weight: bold;
        }
        .anomalies-section {
            margin-top: 15px;
            margin-bottom: 10px;
        }
        .anomalies-title {
            background-color: #dc2626;
            color: #fff;
            font-weight: bold;
            padding: 6px;
            font-size: 10px;
            margin-bottom: 5px;
        }
        .anomalies-content {
            border: 1px solid #94a3b8;
            padding: 6px;
            min-height: 40px;
            font-size: 8px;
            background-color: #ffffff;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #6b7280;
            text-align: center;
        }
        .equipment-count {
            display: inline-block;
            margin-left: 5px;
            font-size: 7px;
            color: #6b7280;
        }
        .signatures-section {
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .signature-box {
            width: 33.33%;
            border: 1px solid #94a3b8;
            padding: 8px;
            min-height: 60px;
            text-align: center;
            background-color: #ffffff;
            vertical-align: top;
        }
        .signature-label {
            font-weight: bold;
            font-size: 8px;
            margin-bottom: 5px;
            color: #374151;
        }
        .signature-line {
            border-top: 1px solid #94a3b8;
            margin-top: 35px;
            padding-top: 3px;
            font-size: 7px;
            color: #6b7280;
        }
        .images-section {
            margin-top: 10px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .images-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .images-grid td {
            width: 50%;
            padding: 5px;
            vertical-align: top;
            border: 1px solid #e5e7eb;
            background-color: #ffffff;
        }
        .image-container {
            text-align: center;
            padding: 5px;
        }
        .image-label {
            font-size: 7px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .image-wrapper {
            display: inline-block;
            border: 1px solid #d1d5db;
            padding: 3px;
            background-color: #f9fafb;
        }
        .image-wrapper img {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
            display: block;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-top">
            <div class="company-name">TRANS-MARHABA</div>
            <div class="header-dates">
                <div>MEP: {{ $handover->created_at ? $handover->created_at->format('d/m/Y') : 'N/A' }}</div>
                <div>Révision: {{ $handover->updated_at ? $handover->updated_at->format('d/m/Y') : 'N/A' }}</div>
            </div>
        </div>
        <div class="form-title">FICHE DE PASSATION</div>
    </div>

    {{-- General Information --}}
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="info-label">
                    N° D'immatriculation : <span style="font-weight: bold;">{{ $handover->vehicle->license_plate ?? 'N/A' }}</span>
                </td>
                <td class="info-label">
                    Code : <span style="font-weight: bold;">{{ $handover->code ?? 'N/A' }}</span>
                </td>
                <td class="info-label">
                    Date : <span style="font-weight: bold;">{{ $handover->handover_date ? $handover->handover_date->format('d/m/Y') : 'N/A' }}</span>
                </td>
            </tr>
            <tr>
                <td class="info-label">
                    Lieu : <span style="font-weight: bold;">{{ $handover->location ?? 'N/A' }}</span>
                </td>
                <td class="info-label">
                    Motif : <span style="font-weight: bold;">{{ $handover->cause ?? 'N/A' }}</span>
                </td>
                <td class="info-label">
                    Nom / prénom chauffeur à Bord : <span style="font-weight: bold;">{{ $handover->driver_from_name ?? ($handover->driverFrom->full_name ?? 'N/A') }}</span>
                </td>
            </tr>
            <tr>
                <td class="info-label">
                    Km : <span style="font-weight: bold;">{{ $handover->vehicle_km ?? 'N/A' }}</span>
                </td>
                <td class="info-label">
                    GASOIL : <span style="font-weight: bold;">{{ $handover->gasoil ? number_format($handover->gasoil, 2) : 'N/A' }}</span>
                </td>
                <td class="info-label">
                    Nom / prénom chauffeur remplaçant : <span style="font-weight: bold;">{{ $handover->driver_to_name ?? ($handover->driverTo->full_name ?? 'N/A') }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Documents Section --}}
    <div class="section-header">DOCUMENTS</div>
    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Document</th>
                <th class="check-column">OUI</th>
                <th class="check-column">NON</th>
                <th style="width: 35%;">Observation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documentRows as $key => $label)
                <tr>
                    <td style="font-weight: bold; font-size: 8px;">{{ $label }}</td>
                    @if($key === 'jawaz_autoroute')
                        <td colspan="2" style="text-align: center; font-size: 8px;">
                            {{ $documents['jawaz_autoroute'] ?? 'N/A' }}
                        </td>
                        <td style="font-size: 7px; padding: 2px;">
                            {{ $documents["{$key}_observation"] ?? '' }}
                        </td>
                    @else
                        <td class="check-column">
                            @if(isset($documents[$key]) && $documents[$key] === 'oui')
                                <span class="yes-mark">✓</span>
                            @endif
                        </td>
                        <td class="check-column">
                            @if(isset($documents[$key]) && $documents[$key] === 'non')
                                <span class="no-mark">✗</span>
                            @endif
                        </td>
                        <td style="font-size: 7px; padding: 2px;">
                            {{ $documents["{$key}_observation"] ?? '' }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Document Images Section --}}
    @php
        $documentImages = [];
        foreach($documentRows as $key => $label) {
            if(isset($documents["{$key}_image"]) && $documents["{$key}_image"]) {
                $documentImages[] = [
                    'label' => $label,
                    'image' => $documents["{$key}_image"]
                ];
            }
        }
        // Add images from document options
        if(isset($documents['options']) && is_array($documents['options'])) {
            $optionsChunks = array_chunk($documentCheckboxes, 3, true);
            foreach($optionsChunks as $chunkIndex => $chunk) {
                $rowKey = 'row_' . $chunkIndex;
                if(isset($documents['options'][$rowKey]['image']) && $documents['options'][$rowKey]['image']) {
                    $labels = array_values($chunk);
                    $documentImages[] = [
                        'label' => implode(' / ', $labels),
                        'image' => $documents['options'][$rowKey]['image']
                    ];
                }
            }
        }
    @endphp
    @if(count($documentImages) > 0)
        <div class="images-section">
            <div style="font-weight: bold; font-size: 9px; margin-bottom: 5px; color: #166534;">IMAGES DES DOCUMENTS</div>
            <table class="images-grid">
                <tbody>
                    @foreach(array_chunk($documentImages, 2) as $imageRow)
                        <tr>
                            @foreach($imageRow as $imageData)
                                <td>
                                    <div class="image-container">
                                        <div class="image-label">{{ $imageData['label'] }}</div>
                                        <div class="image-wrapper">
                                            <img src="{{ $imageData['image'] }}" alt="{{ $imageData['label'] }}">
                                        </div>
                                    </div>
                                </td>
                            @endforeach
                            @if(count($imageRow) < 2)
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Document Options (Checkboxes) --}}
    @if(isset($documents['options']))
        <table style="margin-top: 5px;">
            <tbody>
                @php
                    $optionsChunks = array_chunk($documentCheckboxes, 3, true);
                @endphp
                @foreach($optionsChunks as $chunkIndex => $chunk)
                    <tr>
                        @foreach($chunk as $key => $label)
                            <td style="font-size: 7px; padding: 4px;">
                                <strong>{{ $label }}</strong>
                                @if(isset($documents['options'][$key]['checked']) && $documents['options'][$key]['checked'])
                                    <span class="yes-mark">✓ Présent</span>
                                @endif
                            </td>
                        @endforeach
                        @if(count($chunk) < 3)
                            @for($i = count($chunk); $i < 3; $i++)
                                <td></td>
                            @endfor
                        @endif
                        <td class="check-column">
                            @if(isset($documents['options']['row_' . $chunkIndex]['status']) && $documents['options']['row_' . $chunkIndex]['status'] === 'oui')
                                <span class="yes-mark">✓</span>
                            @endif
                        </td>
                        <td class="check-column">
                            @if(isset($documents['options']['row_' . $chunkIndex]['status']) && $documents['options']['row_' . $chunkIndex]['status'] === 'non')
                                <span class="no-mark">✗</span>
                            @endif
                        </td>
                        <td style="font-size: 7px; padding: 2px;">
                            {{ $documents['options']['row_' . $chunkIndex]['observation'] ?? '' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Equipment/Materials Section --}}
    <div class="section-header">OUTILLAGES / MATERIELS</div>
    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Équipement</th>
                <th class="check-column">OUI</th>
                <th class="check-column">NON</th>
                <th style="width: 35%;">Observation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipmentRows as $key => $label)
                <tr>
                    <td style="font-weight: bold; font-size: 8px;">
                        {{ $label }}
                        @if(in_array($key, ['extincteurs', 'nombre_flexibles', 'nombre_reduction']) && isset($equipment_counts[$key]))
                            <span class="equipment-count">
                                @php
                                    $counts = $key === 'extincteurs' ? ['N°1', 'N°2', 'N°3'] : ['1', '2', '3', '4'];
                                @endphp
                                @foreach($counts as $index => $countLabel)
                                    @if(isset($equipment_counts[$key][$index]) && $equipment_counts[$key][$index])
                                        {{ $countLabel }}✓
                                    @endif
                                @endforeach
                            </span>
                        @endif
                    </td>
                    <td class="check-column">
                        @if(isset($equipment[$key]) && $equipment[$key] === 'oui')
                            <span class="yes-mark">✓</span>
                        @endif
                    </td>
                    <td class="check-column">
                        @if(isset($equipment[$key]) && $equipment[$key] === 'non')
                            <span class="no-mark">✗</span>
                        @endif
                    </td>
                    <td style="font-size: 7px; padding: 2px;">
                        {{ $equipment["{$key}_observation"] ?? '' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Equipment Images Section --}}
    @php
        $equipmentImages = [];
        foreach($equipmentRows as $key => $label) {
            if(isset($equipment["{$key}_image"]) && $equipment["{$key}_image"]) {
                $equipmentImages[] = [
                    'label' => $label,
                    'image' => $equipment["{$key}_image"]
                ];
            }
        }
    @endphp
    @if(count($equipmentImages) > 0)
        <div class="images-section">
            <div style="font-weight: bold; font-size: 9px; margin-bottom: 5px; color: #166534;">IMAGES DES ÉQUIPEMENTS</div>
            <table class="images-grid">
                <tbody>
                    @foreach(array_chunk($equipmentImages, 2) as $imageRow)
                        <tr>
                            @foreach($imageRow as $imageData)
                                <td>
                                    <div class="image-container">
                                        <div class="image-label">{{ $imageData['label'] }}</div>
                                        <div class="image-wrapper">
                                            <img src="{{ $imageData['image'] }}" alt="{{ $imageData['label'] }}">
                                        </div>
                                    </div>
                                </td>
                            @endforeach
                            @if(count($imageRow) < 2)
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Anomalies Section --}}
    <div class="anomalies-section">
        <div class="anomalies-title">ANOMALIES CONSTATEES</div>
        <div class="anomalies-content">
            {{ $handover->anomalies_description ?? 'Aucune anomalie constatée.' }}
        </div>
    </div>

    <div class="anomalies-section">
        <div class="anomalies-title" style="background-color: #059669;">ACTIONS PRISES</div>
        <div class="anomalies-content">
            {{ $handover->anomalies_actions ?? 'Aucune action prise.' }}
        </div>
    </div>

    {{-- Signatures Section --}}
    <div class="signatures-section">
        <table class="signatures-table">
            <tr>
                <td class="signature-box">
                    <div class="signature-label">VISA CHAUFFEUR À BORD</div>
                    <div class="signature-line">
                        Signature
                    </div>
                </td>
                <td class="signature-box">
                    <div class="signature-label">VISA CHAUFFEUR REMPLAÇANT</div>
                    <div class="signature-line">
                        Signature
                    </div>
                </td>
                <td class="signature-box">
                    <div class="signature-label">VISA SUPERVISEUR</div>
                    <div class="signature-line">
                        Signature
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div>Document généré le {{ $generated_at }}</div>
        <div>Fiche de remise N° #{{ str_pad((string) $handover->id, 5, '0', STR_PAD_LEFT) }}</div>
    </div>
</body>
</html>


