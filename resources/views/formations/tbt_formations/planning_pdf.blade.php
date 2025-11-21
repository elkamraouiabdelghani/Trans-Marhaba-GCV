<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.tbt_planning_title') }} - {{ $year }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 6pt;
            line-height: 1.1;
            color: #333;
            background: #fff;
            padding: 3mm;
        }
        
        .header {
            border-bottom: 2px solid #2c3e50;
            padding: 3px 0;
            margin-bottom: 3px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 12pt;
            margin-bottom: 1px;
            font-weight: bold;
        }
        
        .header .subtitle {
            color: #7f8c8d;
            font-size: 7pt;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            page-break-inside: avoid;
        }
        
        table th, table td {
            border: 0.5px solid #ddd;
            padding: 1px 2px;
            text-align: center;
            vertical-align: middle;
            font-size: 5.5pt;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .month-header {
            background-color: #e9ecee !important;
            font-weight: bold;
        }
        
        .week-number {
            font-weight: bold;
            background-color: #f8f9fa;
            font-size: 5.5pt;
            padding: 2px;
        }
        
        .day-name {
            font-size: 5pt;
            padding: 1px;
        }
        
        .day-number {
            font-size: 5pt;
            color: #666;
            padding: 1px;
        }
        
        .formation-cell {
            background-color: #e3f2fd;
            border: 0.5px solid #2196f3;
            padding: 2px;
            min-height: 15px;
            font-size: 5.5pt;
        }
        
        .formation-code {
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 1px;
            font-size: 5.5pt;
        }
        
        .formation-title {
            color: #333;
            font-size: 5pt;
            line-height: 1.1;
        }
        
        .empty-cell {
            background-color: #f5f5f5;
            border: 0.5px solid #ddd;
            min-height: 15px;
            padding: 2px;
        }
        
        .month-spacer {
            height: 1px;
            background-color: #f8f9fa;
            padding: 0;
        }
        
        @page {
            margin: 3mm;
            size: A4 landscape;
        }
        
        /* Prevent page breaks */
        tr {
            page-break-inside: avoid;
        }
        
        table {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('messages.tbt_planning_title') }} - {{ $year }}</h1>
        <div class="subtitle">{{ __('messages.generated_at') }}: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table>
        <tbody>
            @foreach($calendarData as $month => $monthData)
                @php
                    $weekCount = count($monthData['weeks']);
                @endphp
                
                <!-- Month Name Row -->
                <tr class="month-header">
                    <td rowspan="5" class="text-center fw-bold" style="width: 50px; font-size: 6pt;">
                        {{ $monthData['shortName'] }}
                    </td>
                </tr>
                
                <!-- Week Numbers Row -->
                <tr>
                    @foreach($monthData['weeks'] as $weekIndex => $week)
                        <td class="week-number" style="min-width: 60px;">
                            {{ __('messages.tbt_planning_week') }}{{ $weekIndex + 1 }}
                        </td>
                    @endforeach
                </tr>
                
                <!-- Day Names Row -->
                <tr>
                    @foreach($monthData['weeks'] as $weekIndex => $week)
                        <td class="day-name">
                            @php
                                $dayNames = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
                                $dayCount = 0;
                                foreach($week as $key => $day) {
                                    if($key === 'formation') continue;
                                    $dayCount++;
                                }
                            @endphp
                            @for($i = 0; $i < 7; $i++)
                                @if($i < $dayCount)
                                    {{ $dayNames[$i] }}@if($i < 6) @endif
                                @endif
                            @endfor
                        </td>
                    @endforeach
                </tr>
                
                <!-- Day Numbers Row -->
                <tr>
                    @foreach($monthData['weeks'] as $weekIndex => $week)
                        <td class="day-number">
                            @php
                                $dayNumbers = [];
                                foreach($week as $key => $day) {
                                    if($key === 'formation') continue;
                                    $dayNumbers[] = [
                                        'value' => $day['day'],
                                        'inMonth' => $day['isInMonth'] ?? false,
                                    ];
                                }
                            @endphp
                            @foreach($dayNumbers as $dayData)
                                <span style="padding:0 1px; {{ $dayData['inMonth'] ? 'font-weight:bold;color:#333;' : 'color:#aaa;' }}">
                                    {{ $dayData['value'] }}
                                </span>
                            @endforeach
                        </td>
                    @endforeach
                </tr>
                
                <!-- Formation Names Row -->
                <tr>
                    @foreach($monthData['weeks'] as $weekIndex => $week)
                        <td class="{{ isset($week['formation']) && $week['formation'] ? 'formation-cell' : 'empty-cell' }}">
                            @if(isset($week['formation']) && $week['formation'])
                                <div class="formation-title">{{ Str::limit($week['formation']->title, 30) }}</div>
                            @endif
                        </td>
                    @endforeach
                </tr>
                
                <!-- Spacer row between months -->
                @if($month < 12)
                    <tr>
                        <td colspan="{{ $weekCount + 1 }}" class="month-spacer"></td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</body>
</html>

