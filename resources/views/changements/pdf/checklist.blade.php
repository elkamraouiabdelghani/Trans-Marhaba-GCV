<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Checklist de Changement</title>
    <style>
        @page { margin: 20px 15px; }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 10px; 
            color: #1f2937; 
        }
        .header { 
            text-align: center; 
            margin-bottom: 15px; 
            border-bottom: 2px solid #166534;
            padding-bottom: 10px;
        }
        .header h1 { 
            font-size: 20px; 
            color: #166534; 
            margin: 0 0 5px 0; 
        }
        .header h2 { 
            font-size: 14px; 
            color: #6b7280; 
            margin: 0; 
            font-weight: normal;
        }
        .info-section {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #374151;
        }
        .info-value {
            color: #6b7280;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px; 
            font-size: 9px;
        }
        th, td { 
            border: 1px solid #94a3b8; 
            padding: 6px 4px; 
            text-align: center;
        }
        th { 
            background-color: #87ceeb; 
            color: #000; 
            font-weight: bold; 
            font-size: 10px;
            padding: 8px 4px;
        }
        td {
            background-color: #d3d3d3;
        }
        .changement-type-header {
            background-color: #87ceeb;
            text-align: left;
            font-weight: bold;
            padding: 8px;
        }
        .section-header {
            background-color: #8b6f47;
            color: #fff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
        }
        .sous-cretaire-name {
            text-align: left;
            font-weight: normal;
            background-color: #d3d3d3;
            padding: 6px 8px;
        }
        .check-column {
            text-align: center;
            width: 60px;
            font-weight: bold;
            font-size: 12px;
        }
        .check-mark {
            color: #000;
        }
        .observation-cell {
            text-align: left;
            font-size: 8px;
            padding: 6px 8px;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CHECKLIST DE CHANGEMENT</h1>
        <h2>{{ $changement->changementType->name ?? 'N/A' }}</h2>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Date de changement:</span>
            <span class="info-value">{{ $changement->date_changement->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Description:</span>
            <span class="info-value">{{ \Illuminate\Support\Str::limit($changement->description_changement, 80) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Responsable:</span>
            <span class="info-value">{{ $changement->responsable_changement }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Généré le:</span>
            <span class="info-value">{{ $generated_at }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="changement-type-header">{{ $changement->changementType->name ?? 'N/A' }}</th>
                <th>OK</th>
                <th>KO</th>
                <th>N/A</th>
                <th>OBSERVATIONS</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Group sous cretaire by principale cretaire
                $groupedSousCretaires = collect();
                foreach ($principaleCretaires as $principale) {
                    $sousList = collect();
                    foreach ($principale->sousCretaires as $sous) {
                        $sousList->push([
                            'sous' => $sous,
                            'principale' => $principale,
                        ]);
                    }
                    if ($sousList->isNotEmpty()) {
                        $sortedSousList = $sousList->sortBy(function($item) {
                            return $item['sous']->name;
                        });
                        $groupedSousCretaires->push([
                            'principale' => $principale,
                            'sousList' => $sortedSousList,
                        ]);
                    }
                }
                $groupedSousCretaires = $groupedSousCretaires->sortBy(function($group) {
                    return $group['principale']->name;
                });
            @endphp

            @foreach($groupedSousCretaires as $group)
                @php
                    $principale = $group['principale'];
                    $sousList = $group['sousList'];
                @endphp
                <tr>
                    <td colspan="5" class="section-header">{{ $principale->name }}</td>
                </tr>
                @foreach($sousList as $item)
                    @php
                        $sous = $item['sous'];
                        $result = $checklistResults->get($sous->id);
                        $status = $result ? $result->status : 'N/A';
                    @endphp
                    <tr>
                        <td class="sous-cretaire-name">{{ $sous->name }}</td>
                        <td class="check-column">
                            @if($status === 'OK')
                                <span class="check-mark">X</span>
                            @else
                                &nbsp;
                            @endif
                        </td>
                        <td class="check-column">
                            @if($status === 'KO')
                                <span class="check-mark">X</span>
                            @else
                                &nbsp;
                            @endif
                        </td>
                        <td class="check-column">
                            @if($status === 'N/A')
                                <span class="check-mark">X</span>
                            @else
                                &nbsp;
                            @endif
                        </td>
                        <td class="observation-cell">{{ $result ? ($result->observation ?? '') : '' }}</td>
                    </tr>
                @endforeach
            @endforeach

            @if($groupedSousCretaires->isEmpty())
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #6b7280;">
                        Aucun sous-critère disponible
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Document généré automatiquement le {{ $generated_at }}</p>
        <p>Référence: CHG-{{ str_pad((string) $changement->id, 5, '0', STR_PAD_LEFT) }}</p>
    </div>
</body>
</html>

