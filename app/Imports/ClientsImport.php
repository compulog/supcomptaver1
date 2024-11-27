<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\TypeClient; // Importer le modèle TypeClient
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

        // Vérifiez si les colonnes nécessaires existent (en vérifiant que les mappings ne sont pas zéro)
        $compte = $this->getValue($row, 'compte');
        $intitule = $this->getValue($row, 'intitule');
        $identifiantFiscal = $this->getValue($row, 'identifiant_fiscal');
        $ICE = $this->getValue($row, 'ICE');
        $typeClient = $this->getValue($row, 'type_client');

        // Traitement de la valeur de type_client
        if ($typeClient) {
            if (is_numeric($typeClient)) {
                $typeClientNumero = (int) $typeClient;
                $typeClientDescription = $typeClientNumero >= 1 && $typeClientNumero <= 5
                    ? TypeClient::where('numero', $typeClientNumero)->first()->description ?? null
                    : null;
            } else {
                $typeClientDescription = $typeClient;
            }
        } else {
            $typeClientDescription = null;
        }

        // Chercher le client existant par le compte et societe_id
        $client = Client::where('compte', $compte)
                        ->where('societe_id', $this->societe_id)
                        ->first();

        // Appliquer la logique d'ajout ou de mise à jour
        if ($client) {
            // Mettre à jour le client existant avec les nouvelles valeurs
            $client->intitule = $intitule;
            $client->identifiant_fiscal = $identifiantFiscal;
            $client->ICE = $ICE;
            $client->type_client = $typeClientDescription;
            $client->save(); // Enregistrer les modifications
        } else {
            // Créer un nouveau client si celui-ci n'existe pas
            return new Client([
                'compte' => $compte,
                'intitule' => $intitule,
                'identifiant_fiscal' => $identifiantFiscal,
                'ICE' => $ICE,
                'type_client' => $typeClientDescription,
                'societe_id' => $this->societe_id, // Ajouter societe_id ici
            ]);
        }

        return null; // Ne pas enregistrer si une colonne requise est manquante
    }

    // Fonction pour obtenir la valeur d'un champ, ou 0 si le champ n'existe pas dans le mapping
    private function getValue(array $row, $field)
    {
        $columnIndex = $this->mapping[$field] - 1; // Conversion de l'indice pour correspondre à l'index du tableau (commence à 0)
        
        // Si le champ est configuré avec 0, on retourne 0
        if ($this->mapping[$field] == 0 || !isset($row[$columnIndex])) {
            return 0;
        }

        return $row[$columnIndex] ?? 0; // Retourne la valeur ou 0 si la valeur est vide
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
