<?php

namespace App\Exports;

use App\Models\Client; // Assurez-vous d'importer le modèle approprié
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Client::all(['compte', 'intitule', 'identifiant_fiscal', 'ICE', 'type_client']);
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
