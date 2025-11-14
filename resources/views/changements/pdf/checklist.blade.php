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
            background-color: #166534; 
            color: #fff; 
            font-weight: bold; 
            font-size: 9px;
        }
        td {
            background-color: #fff;
        }
        .sous-cretaire-name {
            text-align: left;
            font-weight: bold;
            background-color: #f3f4f6;
            width: 150px;
        }
        .status-ok {
            background-color: #d1fae5;
            color: #065f46;
            font-weight: bold;
        }
        .status-ko {
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: bold;
        }
        .status-na {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        .observation-cell {
            text-align: left;
            font-size: 8px;
            max-width: 200px;
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
                <th class="sous-cretaire-name">Sous-Critère</th>
                @foreach($principaleCretaires as $principale)
                    <th>{{ $principale->name }}</th>
                @endforeach
                <th>Observation</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Group sous cretaire by principale cretaire
                $allSousCretaires = collect();
                foreach ($principaleCretaires as $principale) {
                    foreach ($principale->sousCretaires as $sous) {
                        $allSousCretaires->push([
                            'sous' => $sous,
                            'principale' => $principale,
                        ]);
                    }
                }
                // Sort by principale name, then sous name
                $allSousCretaires = $allSousCretaires->sortBy([
                    ['principale.name', 'asc'],
                    ['sous.name', 'asc'],
                ]);
            @endphp

            @foreach($allSousCretaires as $item)
                @php
                    $sous = $item['sous'];
                    $result = $checklistResults->get($sous->id);
                    $sousPrincipaleId = $sous->principale_cretaire_id;
                @endphp
                <tr>
                    <td class="sous-cretaire-name">{{ $sous->name }}</td>
                    @foreach($principaleCretaires as $principale)
                        @if($principale->id === $sousPrincipaleId)
                            @php
                                $status = $result ? $result->status : 'N/A';
                                $statusClass = match($status) {
                                    'OK' => 'status-ok',
                                    'KO' => 'status-ko',
                                    default => 'status-na',
                                };
                            @endphp
                            <td class="{{ $statusClass }}">{{ $status }}</td>
                        @else
                            <td>-</td>
                        @endif
                    @endforeach
                    <td class="observation-cell">{{ $result ? ($result->observation ?? '-') : '-' }}</td>
                </tr>
            @endforeach

            @if($allSousCretaires->isEmpty())
                <tr>
                    <td colspan="{{ $principaleCretaires->count() + 2 }}" style="text-align: center; padding: 20px; color: #6b7280;">
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

