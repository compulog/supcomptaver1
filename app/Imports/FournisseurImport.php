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

    public function __construct($societe_id, $nombre_chiffre_compte, array $mapping)
    {
        $this->societe_id = $societe_id;
        $this->nombre_chiffre_compte = $nombre_chiffre_compte;
        $this->mapping = $mapping;
    }

    private function sanitizeIce($value)
    {
        if (is_null($value)) return null;
        return ltrim((string) $value, "\t\n\r\0\x0B");
    }

    private function getMappedValue(array $row, $mappingKey)
    {
        if (!isset($this->mapping[$mappingKey]) || $this->mapping[$mappingKey] === null) return null;
        $col = (int)$this->mapping[$mappingKey];
        return isset($row[$col]) ? trim((string)$row[$col]) : null;
    }

    public function collection(Collection $rows)
    {
        $processedAccounts = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // skip headers

            $rowArr = $row->toArray();
            $compte = $this->getMappedValue($rowArr, 'compte');
            $intitule = $this->getMappedValue($rowArr, 'intitule');

            $identifiant_fiscal = $this->getMappedValue($rowArr, 'identifiant_fiscal');
            $iceRaw = $this->getMappedValue($rowArr, 'ICE');
            $ice = $iceRaw !== null ? $this->sanitizeIce($iceRaw) : null;

            $natureOperation = null;
            if (!empty($this->mapping['nature_operation'])) {
                $val = $this->getMappedValue($rowArr, 'nature_operation');
                $nature_map = [
                    '1' => 'Achat de biens d’équipement',
                    '2' => 'Achat de travaux',
                    '3' => 'Achat de services',
                    'Achat de biens d’équipement' => '1',
                    'Achat de travaux' => '2',
                    'Achat de services' => '3',
                ];
                if ($val !== null && array_key_exists($val, $nature_map)) {
                    $natureOperation = in_array($val, ['1','2','3']) ? $val : $nature_map[$val];
                } else {
                    $natureOperation = $val;
                }
            }

            $rubrique_tva = $this->getMappedValue($rowArr, 'rubrique_tva');
            $designation = $this->getMappedValue($rowArr, 'designation');
            $contre_partie = $this->getMappedValue($rowArr, 'contre_partie');

            $RC      = $this->getMappedValue($rowArr, 'RC');
            $ville   = $this->getMappedValue($rowArr, 'ville');
            $adresse = $this->getMappedValue($rowArr, 'adresse');
            $delai_p = $this->getMappedValue($rowArr, 'delai_p');

            $missingFields = [];
            if (empty($compte)) $missingFields[] = 'compte';
            if (empty($intitule)) $missingFields[] = 'intitule';
            $highlight = !empty($missingFields) ? 'highlight-yellow' : null;

            if (empty($compte)) {
                Log::warning("Ligne {$index} ignorée (compte vide), intitulé: " . ($intitule ?? 'NULL'));
                continue;
            }

            if (in_array($compte, $processedAccounts, true)) {
                Log::info("Compte {$compte} déjà traité (ligne {$index}) -> ignoré");
                continue;
            }
            $processedAccounts[] = $compte;

            $isInvalid = strlen($compte) !== (int)$this->nombre_chiffre_compte;

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
                'RC' => $RC ?? ($fournisseur->RC ?? null),
                'ville' => $ville ?? ($fournisseur->ville ?? null),
                'adresse' => $adresse ?? ($fournisseur->adresse ?? null),
                'delai_p' => $delai_p ?? ($fournisseur->delai_p ?? null),
                'invalid' => $isInvalid ? 1 : 0,
                'highlight' => $highlight,
            ];

            Log::info("Traitement ligne $index pour compte $compte", $dataToUpdate);

            try {
                Fournisseur::updateOrCreate(
                    ['compte' => $compte, 'societe_id' => $this->societe_id],
                    $dataToUpdate
                );
            } catch (\Exception $e) {
                Log::error("Erreur import compte $compte", [
                    'message' => $e->getMessage(),
                    'row_index' => $index,
                    'row' => $rowArr,
                ]);
            }
        }
    }
}