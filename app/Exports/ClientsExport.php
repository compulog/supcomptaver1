<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientsExport implements FromCollection, WithHeadings
{
    // Ajouter un paramètre $societe_id
    protected $societeId;

    public function __construct($societeId)
    {
        $this->societeId = $societeId; // Initialiser la valeur
    }

    public function collection()
    {
        $societeId = session()->get('societeId');

        // Filtrer les clients selon le societe_id
        return Client::where('societe_id', $societeId)
            ->select('compte', 'intitule', 'identifiant_fiscal', 'ICE', 'type_client')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Compte',
            'Intitulé',
            'Identifiant Fiscal',
            'ICE',
            'Type Client',
        ];
    }
   

}
