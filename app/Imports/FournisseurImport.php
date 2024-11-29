<?php

namespace App\Imports;

use App\Models\Fournisseur;
use App\Models\Societe;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithProgressBar;

class FournisseurImport implements ToCollection, WithHeadingRow
{
    /**
     * Traitement des lignes importées
     * @param Collection $rows
     * @return void
     */
    public function collection(Collection $rows)
    {
        // Récupérer l'id de la société à partir de la session
        $societe_id = session('societeId');
        $societe = Societe::find($societe_id);

        // Récupérer le nombre de chiffres du compte
        $nombre_chiffre_compte = $societe->nombre_chiffre_compte;

        // Tableau pour suivre les comptes déjà traités
        $processedAccounts = [];

        // Parcourir chaque ligne du fichier Excel
        foreach ($rows as $row) {
            // Log pour examiner la ligne importée
            Log::info('Ligne importée:', ['row' => $row]);

            // Vérifier et logguer les valeurs de 'ICE' et 'Nature de l\'Opération'
            if (isset($row['ICE']) && !empty($row['ICE'])) {
                Log::info("ICE: " . $row['ICE']);
            } else {
                Log::info("Clé 'ICE' manquante ou vide", ['row' => $row]);
            }

            if (isset($row['Nature de l\'Opération']) && !empty($row['Nature de l\'Opération'])) {
                Log::info("Nature de l'opération: " . $row['Nature de l\'Opération']);
            } else {
                Log::info("Clé 'Nature de l'Opération' manquante ou vide", ['row' => $row]);
            }

            // Vérifier si le compte existe et s'il n'est pas déjà traité
            if (isset($row['compte']) && !empty($row['compte'])) {
                $compte = trim($row['compte']);

                // Sauter les comptes déjà traités
                if (in_array($compte, $processedAccounts)) {
                    continue;
                }
                $processedAccounts[] = $compte;

                // Vérifier la longueur du compte et marquer comme invalide si nécessaire
                $compteLength = strlen($compte);
                $isInvalid = $compteLength !== (int) $nombre_chiffre_compte;

                // Tentative d'importation du fournisseur
                try {
                    Fournisseur::updateOrCreate(
                        [
                            'compte' => $compte,
                            'societe_id' => $societe_id,
                        ],
                        [
                            'intitule' => $row['intitule'] ?? null,
                            'identifiant_fiscal' => $row['identifiant_fiscal'] ?? null,
                            'ICE' => $row['ICE'] ?? null,
                            'nature_operation' => $row['Nature de l\'Opération'] ?? null, // Utiliser le nom correct de la colonne
                            'rubrique_tva' => $row['rubrique_tva'] ?? null,
                            'designation' => $row['designation'] ?? null,
                            'contre_partie' => $row['contre_partie'] ?? null,
                            'invalid' => $isInvalid ? 1 : 0,
                        ]
                    );
                } catch (\Exception $e) {
                    // Log d'erreur si l'importation échoue
                    Log::error("Erreur lors de l'importation du fournisseur pour le compte : $compte", [
                        'message' => $e->getMessage(),
                        'row' => $row,
                    ]);
                }
            }
        }
    }
}
