<?php

namespace App\Services;

use App\Models\Turnover;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ArPHP\I18N\Arabic;

class TurnoverPdfService
{
    /**
     * Generate and store the exit interview PDF for the given turnover.
     *
     * @return string Relative storage path of the generated PDF.
     */
    public function generateInterviewPdf(Turnover $turnover): string
    {
        $turnover->loadMissing(['driver', 'user', 'confirmedBy']);

        $questionsConfig = config('turnover_interview.questions', []);
        $arabicShaper = new Arabic();

        $questions = array_map(function (array $question) use ($arabicShaper) {
            $question['text']['ar_shaped'] = $arabicShaper->utf8Glyphs($question['text']['ar'] ?? '');
            return $question;
        }, $questionsConfig);
        $ratingScale = config('turnover_interview.rating_scale', [1, 2, 3, 4, 5]);

        $interviewData = $turnover->interview_answers ?? [];
        $answers = $interviewData['answers'] ?? [];
        $employeeName = $interviewData['employee_name'] ?? $turnover->person_name;
        $interviewDate = $interviewData['interview_date'] ?? now()->toDateString();
        $employeeSignature = $interviewData['employee_signature'] ?? '';

        $driver = $turnover->driver;
        $user = $turnover->user;
        $hiringDate = $driver && $driver->date_integration
            ? $driver->date_integration->format('Y-m-d')
            : '-';
        $nationality = $driver->nationality ?? $user->nationality ?? 'Marocaine';

        $generalInfo = [
            'date' => $interviewDate,
            'reference' => Str::upper('TURN-' . str_pad((string) $turnover->id, 5, '0', STR_PAD_LEFT)),
            'name' => $employeeName ?? '-',
            'position' => $turnover->position ?? '-',
            'nationality' => $nationality,
            'employee_number' => $turnover->driver_id ?? $turnover->user_id ?? '-',
            'hiring_date' => $hiringDate,
            'departure_date' => optional($turnover->departure_date)->format('Y-m-d') ?? '-',
            'department' => $turnover->flotte ?? '-',
            'direct_manager' => $turnover->interviewed_by ?? '-',
            'departure_reason' => $turnover->departure_reason ?? '-',
        ];

        $pdf = Pdf::loadView('turnovers.pdf.interview', [
            'turnover' => $turnover,
            'questions' => $questions,
            'ratingScale' => $ratingScale,
            'answers' => $answers,
            'generalInfo' => $generalInfo,
            'employeeName' => $employeeName,
            'interviewDate' => $interviewDate,
            'employeeSignature' => $employeeSignature,
            'exitInterviewTitleAr' => $arabicShaper->utf8Glyphs(__('messages.exit_interview_ar_title')),
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        $timestamp = now()->format('YmdHis');
        $fileName = sprintf('turnover-%d-exit-interview-%s.pdf', $turnover->id, $timestamp);
        $directory = 'turnover-reports';
        $storagePath = $directory . '/' . $fileName;

        Storage::disk('public')->makeDirectory($directory);
        Storage::disk('public')->put($storagePath, $pdf->output());

        return $storagePath;
    }
}

