<?php

namespace App\Services;

use App\Models\DriverHandover;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DriverHandoverPdfService
{
    /**
     * Generate and store the handover PDF for the given handover.
     *
     * @return string Relative storage path of the generated PDF.
     */
    public function generateHandoverPdf(DriverHandover $handover): string
    {
        try {
            // Load handover with all necessary relationships
            $handover->loadMissing([
                'driverFrom',
                'driverTo',
                'vehicle',
            ]);

            // Prepare document labels
            $documentRows = [
                'cartes_grises' => 'CARTES GRISES',
                'certificats_visite' => 'CERTIFICATS DE LA VISITE TECHNIQUE',
                'cartes_autorisation' => "CARTES D'AUTORISATION",
                'jawaz_autoroute' => 'JAWAZ / AUTOROUTE N°',
                'attestation_assurance' => 'ATTESTATIONS ASSURANCE',
                'attestation_vignette' => 'ATTESTATION DE VIGNETTE',
                'carnet_metrologique' => 'CARNET METROLOGIQUE + ATTESTATION D\'INSTALLATION',
                'attestation_flexible' => 'ATTESTATION DE FLEXIBLE',
                'attestation_extincteurs' => 'ATTESTATION DES EXTINCTEURS',
                'manuel_atlas' => 'MANUEL ATLAS',
            ];

            $documentCheckboxes = [
                'fds' => 'F.D.S',
                'manuel_conducteur' => 'MANUEL CONDUCTEUR',
                'consignes_securite' => 'CONSIGNES DE SECURITE',
                'cahier_inspection' => 'CAHIER INSPECTION A.D',
                'cahier_feuille_route' => 'CAHIER FEUILLE DE ROUTE',
                'manuel_secourisme' => 'MANUEL DE SECOURISME',
                'disque_dernier_voyage' => 'DISQUE DERNIER VOYAGE',
                'cheque_dv' => 'CHEQUE D.V',
                'facture_bl_dv' => 'FACTURE & BL CACHETE D.V',
                'certificat_jaugeage' => 'CERTIFICAT DE JAUGEAGE',
                'attestation_deplacement' => 'Attestation de déplacement obligatoire',
            ];

            // Prepare equipment labels
            $equipmentRows = [
                'harnais' => 'HARNAIS (semta)',
                'clea_goujons' => 'CLE A GOUJONS',
                'cle_cabine' => 'CLE DE CABINE',
                'extincteurs' => 'EXTINCTEURS:',
                'calles' => 'CALLES',
                'radio_cassette' => 'RADIO CASSETTE',
                'cable_abs' => 'CABLE ABS INSTALLE',
                'flexibles' => 'FLEXIBLES',
                'plaques_signalisation' => 'PLAQUES SIGNALETIQUES DE PANNES',
                'plaques_immatriculation' => 'PLAQUES D\'IMMATRICULATION + PLAQUE 80',
                'nombre_flexibles' => 'NOMBRE DE FLEXIBLES:',
                'cle_vanne' => 'CLE A VANNE',
                'pince_plombage' => 'PINCE DE PLOMBAGE',
                'nombre_reduction' => 'NOMBRE DE REDUCTION:',
                'cones' => '3 CONES',
                'triangle_panne' => 'TRIANGLE DE PANNE',
                'pneu_secours' => 'UN PNEU DE SECOURS',
                'seau_aluminium' => 'UN SAUT EN ALUMINIUM AVEC PRODUIT ABSORBANT (SABLE)',
                'boite_pharmacie' => 'BOITE A PHARMACIE',
            ];

            // Prepare data for PDF
            $equipmentData = $handover->equipment ?? [];
            $equipmentCounts = $equipmentData['counts'] ?? [];
            
            // Convert image paths to absolute paths for PDF rendering
            $documents = $handover->documents ?? [];
            foreach ($documents as $key => $value) {
                if (is_string($value) && str_ends_with($key, '_image') && $value) {
                    $documents[$key] = Storage::disk('public')->path($value);
                } elseif (is_array($value) && isset($value['image']) && $value['image']) {
                    $documents[$key]['image'] = Storage::disk('public')->path($value['image']);
                }
            }
            
            // Handle document options images
            if (isset($documents['options']) && is_array($documents['options'])) {
                foreach ($documents['options'] as $rowKey => $optionData) {
                    if (is_array($optionData) && isset($optionData['image']) && $optionData['image']) {
                        $documents['options'][$rowKey]['image'] = Storage::disk('public')->path($optionData['image']);
                    }
                }
            }
            
            // Convert equipment image paths to absolute paths
            foreach ($equipmentData as $key => $value) {
                if (is_string($value) && str_ends_with($key, '_image') && $value) {
                    $equipmentData[$key] = Storage::disk('public')->path($value);
                }
            }
            
            $data = [
                'handover' => $handover,
                'documentRows' => $documentRows,
                'documentCheckboxes' => $documentCheckboxes,
                'equipmentRows' => $equipmentRows,
                'documents' => $documents,
                'equipment' => $equipmentData,
                'equipment_counts' => $equipmentCounts,
                'anomalies_description' => $handover->anomalies_description ?? '',
                'anomalies_actions' => $handover->anomalies_actions ?? '',
                'generated_at' => now()->format('d/m/Y H:i'),
            ];

            // Generate PDF
            $pdf = Pdf::loadView('driver-handovers.pdf.handover', $data)
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            // Create directory if it doesn't exist
            $directory = 'driver-handovers';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // Generate filename
            $timestamp = now()->format('YmdHis');
            $fileName = sprintf(
                '%s/handover-%d-%s.pdf',
                $directory,
                $handover->id,
                $timestamp
            );

            // Save PDF
            Storage::disk('public')->put($fileName, $pdf->output());

            Log::info('Driver handover PDF generated', [
                'handover_id' => $handover->id,
                'report_path' => $fileName,
            ]);

            return $fileName;
        } catch (\Throwable $e) {
            Log::error('Failed to generate driver handover PDF', [
                'handover_id' => $handover->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

