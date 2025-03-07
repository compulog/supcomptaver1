<?php

namespace App\Imports;

use App\Models\Fournisseur;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas; // <-- Ajout de cette interface
use Illuminate\Support\Facades\Log;

class FournisseurImport implements ToCollection, WithCalculatedFormulas
{
    protected $societe_id;
    protected $nombre_chiffre_compte;
    protected $mapping;

    /**
     * Constructeur pour recevoir les paramètres d'import.
     *
     * @param int   $societe_id
     * @param int   $nombre_chiffre_compte
     * @param array $mapping
     */
    public function __construct($societe_id, $nombre_chiffre_compte, array $mapping)
    {
        $this->societe_id = $societe_id;
        $this->nombre_chiffre_compte = $nombre_chiffre_compte;
        $this->mapping = $mapping;
    }

    /**
     * Optionnel : Convertit les valeurs scientifiques en texte si nécessaire.
     *
     * @param mixed $value
     * @return string|null
     */
    private function sanitizeIce($value)
    {
        if (is_numeric($value)) {
            return number_format($value, 0, '', '');
        }
        return $value;
    }

    /**
     * Traite chaque ligne du fichier.
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $processedAccounts = [];

        foreach ($rows as $index => $row) {
            // Ignorer la première ligne (l'en-tête)
            if ($index === 0) {
                continue;
            }

            // Récupérer les valeurs en fonction du mapping (les indices commencent à 0)
            $compteIndex = $this->mapping['colonne_compte'] - 1;
            $intituleIndex = $this->mapping['colonne_intitule'] - 1;

            $compte = isset($row[$compteIndex]) ? trim($row[$compteIndex]) : null;
            $intitule = isset($row[$intituleIndex]) ? trim($row[$intituleIndex]) : null;

            // Les autres colonnes optionnelles
            $identifiant_fiscal = (isset($this->mapping['colonne_identifiant_fiscal']) && $this->mapping['colonne_identifiant_fiscal'] !== null)
                ? trim($row[$this->mapping['colonne_identifiant_fiscal'] - 1] ?? '')
                : null;
            $ice = (isset($this->mapping['colonne_ICE']) && $this->mapping['colonne_ICE'] !== null)
                ? $this->sanitizeIce(trim($row[$this->mapping['colonne_ICE'] - 1] ?? ''))
                : null;
            $natureOperation = (isset($this->mapping['colonne_nature_operation']) && $this->mapping['colonne_nature_operation'] !== null)
                ? trim($row[$this->mapping['colonne_nature_operation'] - 1] ?? '')
                : null;
            $rubrique_tva = (isset($this->mapping['colonne_rubrique_tva']) && $this->mapping['colonne_rubrique_tva'] !== null)
                ? trim($row[$this->mapping['colonne_rubrique_tva'] - 1] ?? '')
                : null;
            $designation = (isset($this->mapping['colonne_designation']) && $this->mapping['colonne_designation'] !== null)
                ? trim($row[$this->mapping['colonne_designation'] - 1] ?? '')
                : null;
            $contre_partie = (isset($this->mapping['colonne_contre_partie']) && $this->mapping['colonne_contre_partie'] !== null)
                ? trim($row[$this->mapping['colonne_contre_partie'] - 1] ?? '')
                : null;

            // Vérifier la présence des champs obligatoires
            $missingFields = [];
            if (empty($compte)) {
                $missingFields[] = 'compte';
            }
            if (empty($intitule)) {
                $missingFields[] = 'intitule';
            }
            $highlight = !empty($missingFields) ? 'highlight-yellow' : null;

            // Pour éviter les doublons si le même compte apparaît plusieurs fois
            if (!in_array($compte, $processedAccounts)) {
                $processedAccounts[] = $compte;

                $compteLength = strlen($compte ?? '');
                $isInvalid = $compteLength !== (int) $this->nombre_chiffre_compte;

                // Vérifier si le fournisseur existe déjà pour cette société
                $fournisseur = Fournisseur::where('compte', $compte)
                    ->where('societe_id', $this->societe_id)
                    ->first();

                $dataToUpdate = [
                    'intitule' => $intitule ?? ($fournisseur->intitule ?? null),
                    'identifiant_fiscal' => $identifiant_fiscal ?? ($fournisseur->identifiant_fiscal ?? null),
                    'ICE' => $ice ?? ($fournisseur->ICE ?? null),
                    'nature_operation' => $natureOperation ?? ($fournisseur->nature_operation ?? null),
                    'rubrique_tva' => $rubrique_tva ?? ($fournisseur->rubrique_tva ?? null),
                    'designation' => $designation ?? ($fournisseur->designation ?? null),
                    'contre_partie' => $contre_partie ?? ($fournisseur->contre_partie ?? null),
                    'invalid' => $isInvalid ? 1 : 0,
                    'highlight' => $highlight,
                ];
                Log::info("Traitement de la ligne $index pour le compte $compte", $dataToUpdate);

                try {
                    Fournisseur::updateOrCreate(
                        [
                            'compte' => $compte,
                            'societe_id' => $this->societe_id,
                        ],
                        $dataToUpdate
                    );
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'import pour le compte $compte", [
                        'message' => $e->getMessage(),
                        'row' => $row,
                    ]);
                }
            }
        }
    }
}
