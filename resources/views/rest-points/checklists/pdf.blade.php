<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.rest_point_checklist') ?? 'قائمة مرجعية لمحلـة الاستراحة' }}</title>
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
            text-align: right;
        }
        .meta-table td {
            text-align: right;
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
        .category-row {
            background: #e6e6e6;
            font-weight: bold;
        }
        .yes-cell {
            font-weight: bold;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="header-right">
            {{ __('messages.rest_point_checklist_title') ?? 'قائمة مرجعية لتحليل محطات الاستراحة على الطرق' }}
        </div>
        <div class="header-left">
            <strong>TRANS MARHABA</strong>
        </div>
        <div class="clearfix"></div>
    </div>

    @php
        $effectiveDate = $effectiveInspectionDate;
        $nextDue = $nextInspectionDue;
        $completedBy = $checklist->completedByUser->name ?? '-';
    @endphp

    <table class="meta-table">
        <tr>
            <th>{{ __('messages.completed_at') ?? 'تاريخ الزيارة' }}</th>
            <td>{{ $effectiveDate ? $effectiveDate->format('d/m/Y') : '-' }}</td>
            <th>{{ __('messages.next_inspection_due') ?? 'تاريخ المراجعة القادمة' }}</th>
            <td>{{ $nextDue ? $nextDue->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr>
            <th>{{ __('messages.rest_point') ?? 'نقطة الاستراحة' }}</th>
            <td>{{ $restPoint->name }}</td>
            <th>{{ __('messages.completed_by') ?? 'المراقب' }}</th>
            <td>{{ $completedBy }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 55%;">{{ __('messages.item') ?? 'البند' }}</th>
                <th style="width: 10%;">{{ __('messages.yes') ?? 'نعم' }}</th>
                <th style="width: 10%;">{{ __('messages.no') ?? 'لا' }}</th>
                <th style="width: 20%;">{{ __('messages.comment') ?? 'ملاحظات' }}</th>
            </tr>
        </thead>
        <tbody>
        @php $row = 1; @endphp
        @foreach($categories as $category)
            @if($category->items->count() > 0)
                <tr class="category-row">
                    <td colspan="5">{{ $category->name }}</td>
                </tr>
                @foreach($category->items as $item)
                    @php
                        $answer = $answersByItemId[$item->id] ?? null;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $row++ }}</td>
                        <td>{{ $item->label }}</td>
                        <td class="text-center yes-cell">
                            {{ $answer && $answer->is_checked ? '✓' : '' }}
                        </td>
                        <td class="text-center">
                            {{ $answer && !$answer->is_checked ? '✗' : '' }}
                        </td>
                        <td>{{ $answer && $answer->comment ? $answer->comment : '' }}</td>
                    </tr>
                @endforeach
            @endif
        @endforeach
        </tbody>
    </table>

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
        {{ __('messages.generated_at') ?? 'تم التوليد في' }} {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>


