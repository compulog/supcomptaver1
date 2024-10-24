<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;

class ClientsImport implements ToModel, WithHeadingRow
{
    /**
     * Transforme chaque ligne du fichier Excel en modèle Client.
     *
     * @param array $row
     * @return Client|null
     */
    public function model(array $row)
    {
        // Log chaque ligne importée pour le débogage
        \Log::info('Importing row: ', $row);

        // Validation des données avant création
        $this->validateRow($row);

        // Crée une nouvelle instance du modèle Client
        return new Client([
            'compte' => $row['compte'],
            'intitule' => $row['intitule'],
            'identifiant_fiscal' => $row['identifiant_fiscal'],
            'ICE' => $row['ice'],
            'type_client' => $row['type_client'],
        ]);
    }

    /**
     * Valide les données de la ligne avant l'importation.
     *
     * @param array $row
     * @throws \InvalidArgumentException
     */
    protected function validateRow(array $row)
    {
        // Vérification des champs requis
        $requiredFields = ['compte', 'intitule', 'identifiant_fiscal', 'ice', 'type_client'];
        
        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                throw new \InvalidArgumentException("Le champ '$field' est requis.");
            }
        }

        // Ajoutez ici d'autres validations si nécessaire (ex. format d'ICE, etc.)
    }

    /**
     * Si une erreur de validation se produit, renvoie les échecs.
     *
     * @return array
     */
    public function getFailedRows(): array
    {
        return [];
    }
}
