<?php

namespace App\Imports;

use App\Models\Fournisseur;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Facades\Log;

class FournisseurImport implements ToCollection, WithCalculatedFormulas
{
    protected $societe_id;
    protected $nombre_chiffre_compte;
    protected $mapping;

    /**
     * Constructeur
     */
    public function __construct($societe_id, $nombre_chiffre_compte, array $mapping)
    {
        $this->societe_id = $societe_id;
        $this->nombre_chiffre_compte = $nombre_chiffre_compte;
        $this->mapping = $mapping;
    }

    /**
     * Traitement brut de ICE : conserver les zéros à gauche
     */
    private function sanitizeIce($value)
    {
        if (is_null($value)) {
            return null;
        }

        return ltrim((string) $value, "\t\n\r\0\x0B"); // nettoie sans toucher aux zéros
    }

    /**
     * Traitement des lignes Excel
     */
    public function collection(Collection $rows)
    {
        $processedAccounts = [];

        foreach ($rows as $index => $row) {
            // Ignorer la première ligne (en-têtes)
            if ($index === 0) continue;

            $compteIndex = $this->mapping['colonne_compte'] - 1;
            $intituleIndex = $this->mapping['colonne_intitule'] - 1;

            $compte = isset($row[$compteIndex]) ? trim((string) $row[$compteIndex]) : null;
            $intitule = isset($row[$intituleIndex]) ? trim((string) $row[$intituleIndex]) : null;

            $identifiant_fiscal = (isset($this->mapping['colonne_identifiant_fiscal']) && $this->mapping['colonne_identifiant_fiscal'] !== null)
                ? trim((string) $row[$this->mapping['colonne_identifiant_fiscal'] - 1] ?? '')
                : null;

            $ice = (isset($this->mapping['colonne_ICE']) && $this->mapping['colonne_ICE'] !== null)
                ? $this->sanitizeIce($row[$this->mapping['colonne_ICE'] - 1] ?? '')
                : null;

            $nature_map = [
                '1' => 'Achat de biens d’équipement',
                '2' => 'Achat de travaux',
                '3' => 'Achat de services',
                'Achat de biens d’équipement' => '1',
                'Achat de travaux' => '2',
                'Achat de services' => '3',
            ];

            $natureOperation = null;
            if (!empty($this->mapping['colonne_nature_operation'])) {
                $colIndex = $this->mapping['colonne_nature_operation'] - 1;
                $val = trim((string) $row[$colIndex] ?? '');

                if (array_key_exists($val, $nature_map)) {
                    $natureOperation = in_array($val, ['1', '2', '3']) ? $val : $nature_map[$val];
                } else {
                    $natureOperation = $val;
                }
            }

            $rubrique_tva = (isset($this->mapping['colonne_rubrique_tva']) && $this->mapping['colonne_rubrique_tva'] !== null)
                ? trim((string) $row[$this->mapping['colonne_rubrique_tva'] - 1] ?? '')
                : null;

            $designation = (isset($this->mapping['colonne_designation']) && $this->mapping['colonne_designation'] !== null)
                ? trim((string) $row[$this->mapping['colonne_designation'] - 1] ?? '')
                : null;

            $contre_partie = (isset($this->mapping['colonne_contre_partie']) && $this->mapping['colonne_contre_partie'] !== null)
                ? trim((string) $row[$this->mapping['colonne_contre_partie'] - 1] ?? '')
                : null;

            // Champs obligatoires
            $missingFields = [];
            if (empty($compte)) $missingFields[] = 'compte';
            if (empty($intitule)) $missingFields[] = 'intitule';
            $highlight = !empty($missingFields) ? 'highlight-yellow' : null;

            if (!in_array($compte, $processedAccounts)) {
                $processedAccounts[] = $compte;

                $compteLength = strlen($compte ?? '');
                $isInvalid = $compteLength !== (int) $this->nombre_chiffre_compte;

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
