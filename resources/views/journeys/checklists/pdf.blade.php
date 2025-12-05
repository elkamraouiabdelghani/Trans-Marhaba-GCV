<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.journey_checklist') ?? 'Journey Checklist Report' }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            margin: 20px;
            color: #000;
        }
        .header {
            width: 100%;
            margin-bottom: 12px;
        }
        .header-left {
            float: left;
            text-align: left;
            font-size: 9pt;
        }
        .header-right {
            float: right;
            text-align: right;
            font-size: 11pt;
            font-weight: bold;
        }
        .clearfix {
            clear: both;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9pt;
        }
        .meta-table th,
        .meta-table td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .meta-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: left;
        }
        .meta-table td {
            text-align: left;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 3px 4px;
        }
        .items-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .items-table td.text-center {
            text-align: center;
        }
        .map-image {
            width: 100%;
            max-height: 300px;
            margin-top: 12px;
            margin-bottom: 8px;
            border: 1px solid #000;
        }
        .document-image {
            width: 100%;
            max-height: 350px;
            margin-top: 8px;
            margin-bottom: 4px;
            border: 1px solid #000;
            object-fit: contain;
        }
        .footer {
            margin-top: 10px;
            font-size: 8pt;
            text-align: left;
        }
        .notes-section {
            margin-top: 10px;
            padding: 8px;
            border: 1px solid #000;
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-right">
            {{ __('messages.journey_checklist') ?? 'Journey Checklist Report' }}
        </div>
        <div class="header-left">
            <strong>TRANS MARHABA</strong>
        </div>
        <div class="clearfix"></div>
    </div>

    @php
        $completedAt = $completedAt;
        $nextDue = $nextInspectionDue;
        $completedBy = $checklist->completedByUser->name ?? $checklist->completedByUser->email ?? '-';
    @endphp

    <table class="meta-table">
        <tr>
            <th>{{ __('messages.completed_at') ?? 'Completed At' }}</th>
            <td>{{ $completedAt ? $completedAt->format('d/m/Y H:i') : '-' }}</td>
            <th>{{ __('messages.next_inspection_due') ?? 'Next Inspection Due' }}</th>
            <td>{{ $nextDue ? $nextDue->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr>
            <th>{{ __('messages.journey') ?? 'Journey' }}</th>
            <td>{{ $journey->name }}</td>
            <th>{{ __('messages.completed_by') ?? 'Completed By' }}</th>
            <td>{{ $completedBy }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 40%;">{{ __('messages.item') ?? 'Item' }}</th>
                <th style="width: 10%;">{{ __('messages.weight') ?? 'Weight' }}</th>
                <th style="width: 10%;">{{ __('messages.score') ?? 'Score' }}</th>
                <th style="width: 10%;">{{ __('messages.note') ?? 'Note' }}</th>
                <th style="width: 25%;">{{ __('messages.comment') ?? 'Comment' }}</th>
            </tr>
        </thead>
        <tbody>
        @php $row = 1; @endphp
        @foreach($checklistItems as $item)
            @php
                $answer = $answersByItemId[$item->id] ?? null;
            @endphp
            <tr>
                <td class="text-center">{{ $row++ }}</td>
                <td>
                    <strong>{{ $item->donnees ?? '-' }}</strong>
                    @if($item->cirees_appreciation)
                        <br><small>{{ $item->cirees_appreciation }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $answer ? $answer->weight : '-' }}</td>
                <td class="text-center">{{ $answer ? $answer->score : '-' }}</td>
                <td class="text-center">{{ $answer && $answer->note ? number_format($answer->note, 2) : '-' }}</td>
                <td>{{ $answer && $answer->comment ? $answer->comment : '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if($checklist->notes)
        <div class="notes-section">
            <strong>{{ __('messages.general_comment') ?? 'General Comment' }}:</strong><br>
            {{ $checklist->notes }}
        </div>
    @endif

    {{-- Map screenshot --}}
    @if(!empty($mapImageBase64))
        <img src="{{ $mapImageBase64 }}" alt="Map" class="map-image">
    @endif

    {{-- Checklist documents (one under another) --}}
    @if($checklist->documents && count($checklist->documents) > 0)
        @foreach($checklist->documents as $docPath)
            <img src="{{ public_path('storage/' . $docPath) }}" alt="Document" class="document-image">
        @endforeach
    @endif

    <div class="footer">
        {{ __('messages.generated_at') ?? 'Generated at' }} {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>

