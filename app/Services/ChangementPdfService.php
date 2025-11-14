<?php

namespace App\Services;

use App\Models\Changement;
use App\Models\PrincipaleCretaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ChangementPdfService
{
    /**
     * Generate and store the checklist PDF for the given changement.
     *
     * @return string Relative storage path of the generated PDF.
     */
    public function generateChecklistPdf(Changement $changement): string
    {
        try {
            // Load changement with all necessary relationships
            $changement->loadMissing([
                'changementType',
                'checklistResults.sousCretaire.principaleCretaire',
            ]);

            // Get all principale cretaire for this changement type
            $principaleCretaires = PrincipaleCretaire::where('changement_type_id', $changement->changement_type_id)
                ->where('is_active', true)
                ->with(['sousCretaires' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('name')
                ->get();

            // Get all checklist results keyed by sous_cretaire_id
            $checklistResults = $changement->checklistResults()
                ->with('sousCretaire.principaleCretaire')
                ->get()
                ->keyBy('sous_cretaire_id');

            // Prepare data for PDF
            $data = [
                'changement' => $changement,
                'principaleCretaires' => $principaleCretaires,
                'checklistResults' => $checklistResults,
                'generated_at' => now()->format('d/m/Y H:i'),
            ];

            // Generate PDF
            $pdf = Pdf::loadView('changements.pdf.checklist', $data)
                ->setPaper('a4', 'landscape'); // Landscape for better table display

            // Create directory if it doesn't exist
            $directory = 'changement-reports';
            if (!Storage::disk('uploads')->exists($directory)) {
                Storage::disk('uploads')->makeDirectory($directory);
            }

            // Generate filename
            $timestamp = now()->format('YmdHis');
            $fileName = sprintf(
                '%s/checklist-%d-%s.pdf',
                $directory,
                $changement->id,
                $timestamp
            );

            // Save PDF
            Storage::disk('uploads')->put($fileName, $pdf->output());

            Log::info('Changement checklist PDF generated', [
                'changement_id' => $changement->id,
                'report_path' => $fileName,
            ]);

            return $fileName;
        } catch (\Throwable $e) {
            Log::error('Failed to generate changement checklist PDF', [
                'changement_id' => $changement->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

