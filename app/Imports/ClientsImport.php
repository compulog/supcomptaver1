<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;

class ClientsImport implements ToModel, WithHeadingRow
{
    protected $failedRows = [];

    public function model(array $row)
    {
        Log::info('Row data: ', $row); // Log des données de chaque ligne
        $this->validateRow($row);
        
        return new Client([
            'compte' => $row['compte'],
            'intitule' => $row['intitule'],
            'identifiant_fiscal' => $row['identifiant_fiscal'],
            'ICE' => $row['ice'],
            'type_client' => $row['type_client'],
        ]);
    }
    

    protected function validateRow(array $row)
    {
        $requiredFields = ['compte', 'intitule', 'identifiant_fiscal', 'ice', 'type_client'];

        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                throw new \InvalidArgumentException("Le champ '$field' est requis.");
            }
        }

        // Validation pour l'ICE
        if (!preg_match('/^[0-9]{15}$/', $row['ice'])) {
            throw new \InvalidArgumentException("Le champ 'ICE' doit contenir 15 chiffres.");
        }
    }

    public function getFailedRows(): array
    {
        return $this->failedRows;
    }
}
