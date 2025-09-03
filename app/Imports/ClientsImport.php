<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\PlanComptable; // Importer le modèle PlanComptable
use Illuminate\Support\Facades\Log; // Importer le facade Log
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
        static $isFirstRow = true;
    
        if ($isFirstRow) {
            $isFirstRow = false;
            return null;
        }
    
        // Normaliser les données
        $compte = trim($this->getValue($row, 'compte'));
        $intitule = trim($this->getValue($row, 'intitule'));
        $identifiantFiscal = trim($this->getValue($row, 'identifiant_fiscal'));
        $ICE = trim($this->getValue($row, 'ICE'));
        $typeClient = trim($this->getValue($row, 'type_client'));
    
        // Traitement de type_client
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
    
        // Chercher le client existant
        $client = Client::where('compte', $compte)
                        ->where('societe_id', $this->societe_id)
                        ->first();
    
        if ($client) {
            // Mettre à jour le client existant
            $client->intitule = $intitule;
            $client->identifiant_fiscal = $identifiantFiscal;
            $client->ICE = $ICE;
            $client->type_client = $typeClientDescription;
            $client->save();
        } else {
            // Créer un nouveau client
            $client = new Client([
                'compte' => $compte,
                'intitule' => $intitule,
                'identifiant_fiscal' => $identifiantFiscal,
                'ICE' => $ICE,
                'type_client' => $typeClientDescription,
                'societe_id' => $this->societe_id,
            ]);
            $client->save();
        }
    
        // Ajouter ou mettre à jour dans la table plan_comptable
        $planComptable = PlanComptable::where('compte', $compte)
                                      ->where('societe_id', $this->societe_id)
                                      ->first();
    
        if ($planComptable) {
            // Mettre à jour l'entrée existante
            $planComptable->intitule = $intitule;
            $planComptable->save();
        } else {
            // Créer une nouvelle entrée
            PlanComptable::create([
                'societe_id' => $this->societe_id,
                'compte' => $compte,
                'intitule' => $intitule,
            ]);
        }
    
        return null;
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
