<?php

namespace App\Imports;

use App\Models\PlanComptable;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PlanComptableImport implements ToCollection, WithStartRow, WithChunkReading, ShouldQueue
{
    protected $societeId;
    protected $colonneCompte;
    protected $colonneIntitule;

    public function __construct($societeId, $colonneCompte, $colonneIntitule)
    {
        $this->societeId       = $societeId;
        $this->colonneCompte   = $colonneCompte;
        $this->colonneIntitule = $colonneIntitule;
    }

    /** Commence à la ligne 2 (ignore l’en‑tête) */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * On reçoit une Collection de toutes les lignes,
     * que l’on trie numériquement sur “compte”
     */
    public function collection(Collection $rows)
    {
        $rows
            ->sortBy(function ($row) {
                // cast numérique pour trier “1, 2, 10” dans le bon ordre
                return (float) str_replace(',', '.', $row[$this->colonneCompte - 1]);
            })
            ->each(function ($row) {
                PlanComptable::create([
                    'societe_id' => $this->societeId,
                    'compte'     => $row[$this->colonneCompte - 1],
                    'intitule'   => $row[$this->colonneIntitule - 1],
                ]);
            });
    }

    /** Taille de chaque chunk (à ajuster selon vos besoins) */
    public function chunkSize(): int
    {
        return 1000;
    }
}