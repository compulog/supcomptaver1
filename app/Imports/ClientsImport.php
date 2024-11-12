<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ClientsImport implements ToModel, WithHeadings, WithChunkReading
{
    protected $mapping;
    protected $societe_id; // Déclaration de la variable pour le societe_id

    public function __construct(array $mapping, $societe_id)
    {
        $this->mapping = $mapping;
        $this->societe_id = $societe_id; // Récupération de societe_id
    }

    public function model(array $row)
    {
        // Ignorer la première ligne
        static $isFirstRow = true; // Variable statique pour garder l'état entre les appels

        if ($isFirstRow) {
            $isFirstRow = false; // Marquer la première ligne comme traitée
            return null; // Ne rien retourner pour la première ligne
        }

        // Vérifiez si les colonnes nécessaires existent
        if (isset($row[$this->mapping['compte'] - 1]) &&
            isset($row[$this->mapping['intitule'] - 1]) &&
            isset($row[$this->mapping['identifiant_fiscal'] - 1]) &&
            isset($row[$this->mapping['ICE'] - 1]) &&
            isset($row[$this->mapping['type_client'] - 1])) {

            $compte = $row[$this->mapping['compte'] - 1];

            // Chercher le client existant par le compte et societe_id
            $client = Client::where('compte', $compte)
                            ->where('societe_id', $this->societe_id) // Filtrer par societe_id
                            ->first();

            if ($client) {
                // Mettre à jour le client existant
                $client->intitule = $row[$this->mapping['intitule'] - 1];
                $client->identifiant_fiscal = $row[$this->mapping['identifiant_fiscal'] - 1];
                $client->ICE = $row[$this->mapping['ICE'] - 1];
                $client->type_client = $row[$this->mapping['type_client'] - 1];
                $client->save(); // Enregistrer les modifications
            } else {
                // Créer un nouveau client si celui-ci n'existe pas
                return new Client([
                    'compte' => $compte,
                    'intitule' => $row[$this->mapping['intitule'] - 1],
                    'identifiant_fiscal' => $row[$this->mapping['identifiant_fiscal'] - 1],
                    'ICE' => $row[$this->mapping['ICE'] - 1],
                    'type_client' => $row[$this->mapping['type_client'] - 1],
                    'societe_id' => $this->societe_id, // Ajouter societe_id ici
                ]);
            }
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

