<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.exit_interview') }}</title>
    <style>
        @page { margin: 40px 30px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { font-size: 24px; color: #166534; margin: 0; }
        .sub-title { color: #166534; font-size: 18px; margin: 0; }
        .card { border: 1px solid #166534; padding: 12px; margin-bottom: 16px; }
        .section-title { background-color: #166534; color: #fff; padding: 6px 10px; font-weight: bold; margin: 20px 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #94a3b8; padding: 6px 8px; }
        th { background-color: #e2f3e9; font-weight: bold; text-align: center; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-sm { font-size: 11px; }
        .text-muted { color: #6b7280; }
        .info-table td { width: 33%; }
        .rtl { direction: rtl; text-align: right; font-weight: bold; }
        .rating-dot { display: inline-block; width: 14px; height: 14px; border-radius: 50%; border: 1px solid #166534; }
        .rating-dot.filled { background-color: #166534; }
        .page-break { page-break-after: always; }
        .signature-line { border-bottom: 1px solid #166534; width: 100%; height: 20px; margin-top: 10px; }
        .note { border: 1px solid #166534; padding: 10px; background-color: #f0fdf4; }
    </style>
</head>
@php
    $ratingQuestions = collect($questions)->where('type', 'rating')->values();
    $textQuestions = collect($questions)->where('type', 'text')->values();
    $firstRatingTable = $ratingQuestions->slice(0, 6);
    $remainingRatings = $ratingQuestions->slice(6);
@endphp
<body>
    <div class="header">
        <h2 class="sub-title">{{ __('messages.exit_interview_ar_title') }}</h2>
        <h1>{{ __('messages.exit_interview') }}</h1>
    </div>

    <div class="card">
        <table class="info-table">
            <tr>
                <td><strong>{{ __('messages.interview_date') }}:</strong> {{ $generalInfo['date'] }}</td>
                <td><strong>{{ __('messages.exit_interview_reference') }}:</strong> {{ $generalInfo['reference'] }}</td>
                <td><strong>{{ __('messages.employee_number') }}:</strong> {{ $generalInfo['employee_number'] }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('messages.employee_name') }}:</strong> {{ $generalInfo['name'] }}</td>
                <td><strong>{{ __('messages.position') }}:</strong> {{ $generalInfo['position'] }}</td>
                <td><strong>{{ __('messages.nationality') }}:</strong> {{ $generalInfo['nationality'] }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('messages.hiring_date') }}:</strong> {{ $generalInfo['hiring_date'] }}</td>
                <td><strong>{{ __('messages.departure_date') }}:</strong> {{ $generalInfo['departure_date'] }}</td>
                <td><strong>{{ __('messages.department') }}:</strong> {{ $generalInfo['department'] }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>{{ __('messages.direct_manager') }}:</strong> {{ $generalInfo['direct_manager'] }}</td>
                <td><strong>{{ __('messages.departure_reason') }}:</strong> {{ $generalInfo['departure_reason'] }}</td>
            </tr>
        </table>

        <div class="note text-sm">
            <strong>{{ __('messages.exit_interview_disclaimer_title') }}:</strong> {{ __('messages.exit_interview_disclaimer_body') }}
        </div>
    </div>

    <div class="section-title">{{ __('messages.rating_questions_section_title') }}</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>{{ __('messages.question_en') }}</th>
                <th>{{ __('messages.question_ar') }}</th>
                @foreach ($ratingScale as $value)
                    <th style="width: 40px;">{{ $value }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($firstRatingTable as $question)
                <tr>
                    <td class="text-center">{{ $question['number'] }}</td>
                    <td>{{ $question['text']['en'] }}</td>
                    <td class="rtl">{{ $question['text']['ar'] }}</td>
                    @foreach ($ratingScale as $value)
                        <td class="text-center">
                            <span class="rating-dot {{ ($answers[$question['key']] ?? null) == $value ? 'filled' : '' }}"></span>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="section-title">{{ __('messages.rating_questions_continued') }}</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>{{ __('messages.question_en') }}</th>
                <th>{{ __('messages.question_ar') }}</th>
                @foreach ($ratingScale as $value)
                    <th style="width: 40px;">{{ $value }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($remainingRatings as $question)
                <tr>
                    <td class="text-center">{{ $question['number'] }}</td>
                    <td>{{ $question['text']['en'] }}</td>
                    <td class="rtl">{{ $question['text']['ar'] }}</td>
                    @foreach ($ratingScale as $value)
                        <td class="text-center">
                            <span class="rating-dot {{ ($answers[$question['key']] ?? null) == $value ? 'filled' : '' }}"></span>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="section-title">{{ __('messages.text_questions_section_title') }}</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>{{ __('messages.question_en') }}</th>
                <th>{{ __('messages.question_ar') }}</th>
                <th>{{ __('messages.response') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($textQuestions as $question)
                <tr>
                    <td class="text-center">{{ $question['number'] }}</td>
                    <td>{{ $question['text']['en'] }}</td>
                    <td class="rtl">{{ $question['text']['ar'] }}</td>
                    <td style="height: 80px; vertical-align: top;">{!! nl2br(e($answers[$question['key']] ?? '')) !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">{{ __('messages.employee_signature_section') }}</div>
    <table>
        <tr>
            <td style="width: 50%;">
                <strong>{{ __('messages.employee_name') }}:</strong>
                <div class="signature-line">{{ $employeeName }}</div>
            </td>
            <td style="width: 25%;">
                <strong>{{ __('messages.interview_date') }}:</strong>
                <div class="signature-line">{{ $interviewDate }}</div>
            </td>
            <td style="width: 25%;">
                <strong>{{ __('messages.employee_signature') }}:</strong>
                <div class="signature-line">{{ $employeeSignature }}</div>
            </td>
        </tr>
    </table>
</body>
</html>

