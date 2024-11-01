<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ClientsImport implements ToModel, WithHeadings, WithChunkReading
{
    protected $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function model(array $row)
    {
        // Ignorer la première ligne
        static $isFirstRow = true; // Variable statique pour garder l'état entre les appels

        if ($isFirstRow) {
            $isFirstRow = false; // Marquer la première ligne comme traitée
            return null; // Ne rien retourner pour la première ligne
        }

        if (isset($row[$this->mapping['compte']]) &&
            isset($row[$this->mapping['intitule']]) &&
            isset($row[$this->mapping['identifiant_fiscal']]) &&
            isset($row[$this->mapping['ICE']]) &&
            isset($row[$this->mapping['type_client']])) {

            return new Client([
                'compte' => $row[$this->mapping['compte']],
                'intitule' => $row[$this->mapping['intitule']],
                'identifiant_fiscal' => $row[$this->mapping['identifiant_fiscal']],
                'ICE' => $row[$this->mapping['ICE']],
                'type_client' => $row[$this->mapping['type_client']],
            ]);
        }

        return null; // Ne pas enregistrer si une colonne requise est manquante
    }

    public function headings(): array
    {
        return [
            'Compte',
            'Intitule',
            'Identifiant Fiscal',
            'ICE',
            'Type Client',
        ];
    }

    public function chunkSize(): int
    {
        return 100; // Par exemple, traitez 100 lignes à la fois
    }
}

