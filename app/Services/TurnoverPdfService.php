<?php

namespace App\Services;

use App\Models\Turnover;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $questions = config('turnover_interview.questions', []);
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
        ])->setPaper('a4', 'portrait');

        $timestamp = now()->format('YmdHis');
        $fileName = sprintf('turnover-%d-exit-interview-%s.pdf', $turnover->id, $timestamp);
        $directory = 'turnover-reports';
        $storagePath = $directory . '/' . $fileName;

        Storage::disk('uploads')->makeDirectory($directory);
        Storage::disk('uploads')->put($storagePath, $pdf->output());

        return $storagePath;
    }
}

