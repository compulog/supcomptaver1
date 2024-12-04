<?php

namespace App\Imports;

use App\Models\Fournisseur;
use App\Models\Societe;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class FournisseurImport implements ToCollection, WithHeadingRow
{
    /**
     * Convertit les valeurs scientifiques en texte si nécessaire
     *
     * @param mixed $value
     * @return string|null
     */
    private function sanitizeIce($value)
    {
        if (is_numeric($value)) {
            return number_format($value, 0, '', ''); // Supprime la notation scientifique
        }
        return $value;
    }

    public function collection(Collection $rows)
    {
        $societe_id = session('societeId');
        $societe = Societe::find($societe_id);
        $nombre_chiffre_compte = $societe->nombre_chiffre_compte;
        $processedAccounts = [];

        foreach ($rows as $row) {
            Log::info('Ligne importée:', ['row' => $row]);

            $compte = isset($row['compte']) ? trim($row['compte']) : null;
            $intitule = isset($row['intitule']) ? trim($row['intitule']) : null;
            $ice = isset($row['ICE']) ? $this->sanitizeIce($row['ICE']) : null;
            $natureOperation = isset($row['nature_operation']) ? trim($row['nature_operation']) : null;

            // Détection des champs manquants
            $missingFields = [];
            if (empty($compte)) {
                $missingFields[] = 'compte';
            }
            if (empty($intitule)) {
                $missingFields[] = 'intitule';
            }

            $highlight = !empty($missingFields) ? 'highlight-yellow' : null;

            if (!in_array($compte, $processedAccounts)) {
                $processedAccounts[] = $compte;

                $compteLength = strlen($compte ?? '');
                $isInvalid = $compteLength !== (int) $nombre_chiffre_compte;

                $fournisseur = Fournisseur::where('compte', $compte)
                    ->where('societe_id', $societe_id)
                    ->first();

                $dataToUpdate = [
                    'intitule' => $intitule ?? $fournisseur->intitule ?? null,
                    'identifiant_fiscal' => $row['identifiant_fiscal'] ?? $fournisseur->identifiant_fiscal ?? null,
                    'ICE' => $ice ?? $fournisseur->ICE ?? null,
                    'nature_operation' => $natureOperation ?? $fournisseur->nature_operation ?? null,
                    'rubrique_tva' => $row['rubrique_tva'] ?? $fournisseur->rubrique_tva ?? null,
                    'designation' => $row['designation'] ?? $fournisseur->designation ?? null,
                    'contre_partie' => $row['contre_partie'] ?? $fournisseur->contre_partie ?? null,
                    'invalid' => $isInvalid ? 1 : 0,  // Indique si le compte est invalide
                    'highlight' => $highlight,
                ];

                Log::info("Données mises à jour pour le compte $compte :", $dataToUpdate);

                try {
                    Fournisseur::updateOrCreate(
                        [
                            'compte' => $compte,
                            'societe_id' => $societe_id,
                        ],
                        $dataToUpdate
                    );
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'importation pour le compte : $compte", [
                        'message' => $e->getMessage(),
                        'row' => $row,
                    ]);
                }
            }
        }
    }
}
