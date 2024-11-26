<?php

// namespace App\Imports;

// use App\Models\Societe;
// use Maatwebsite\Excel\Concerns\ToModel;

// class SocietesImport implements ToModel
// {
//     protected $mapping;

//     public function __construct($mapping)
//     {
//         $this->mapping = $mapping;
//     }

//     public function model(array $row)
//     {
//         // Récupérer la raison sociale à partir de la ligne importée
//         $raisonSociale = $row[$this->mapping['raison_sociale']] ?? null;

//         // Vérifier si la société existe déjà
//         $societe = Societe::where('raison_sociale', $raisonSociale)->first();

//         // Si la société existe, mettez à jour ses informations
//         if ($societe) {
//             $societe->update([
//                 'siege_social' => $row[$this->mapping['siege_social']],
//                 'ice' => $row[$this->mapping['ice']],
//                 'rc' => $row[$this->mapping['rc']],
//                 'identifiant_fiscal' => $row[$this->mapping['identifiant_fiscal']],
//                 'patente' => $row[$this->mapping['patente']],
//                 'centre_rc' => $row[$this->mapping['centre_rc']],
//                 'forme_juridique' => $row[$this->mapping['forme_juridique']],
//                 'exercice_social_debut' => $row[$this->mapping['exercice_social_debut']],
//                 'exercice_social_fin' => $row[$this->mapping['exercice_social_fin']],
//                 'date_creation' => $row[$this->mapping['date_creation']],
//                 'assujettie_partielle_tva' => $row[$this->mapping['assujettie_partielle_tva']],
//                 'prorata_de_deduction' => $row[$this->mapping['prorata_de_deduction']],
//                 'nature_activite' => $row[$this->mapping['nature_activite']],
//                 'activite' => $row[$this->mapping['activite']],
//                 'regime_declaration' => $row[$this->mapping['regime_declaration']],
//                 'fait_generateur' => $row[$this->mapping['fait_generateur']],
//                 'rubrique_tva' => $row[$this->mapping['rubrique_tva']],
//                 'designation' => $row[$this->mapping['designation']],
//                 'nombre_chiffre_compte' => $row[$this->mapping['nombre_chiffre_compte']],
//                 'modele_comptable' => $row[$this->mapping['modele_comptable']],
//             ]);
//             return $societe; // Retourner l'instance mise à jour
//         }

//         // Si la société n'existe pas, en créer une nouvelle
//         return new Societe([
//             'raison_sociale' => $raisonSociale,
//             'siege_social' => $row[$this->mapping['siege_social']],
//             'ice' => $row[$this->mapping['ice']],
//             'rc' => $row[$this->mapping['rc']],
//             'identifiant_fiscal' => $row[$this->mapping['identifiant_fiscal']],
//             'patente' => $row[$this->mapping['patente']],
//             'centre_rc' => $row[$this->mapping['centre_rc']],
//             'forme_juridique' => $row[$this->mapping['forme_juridique']],
//             'exercice_social_debut' => $row[$this->mapping['exercice_social_debut']],
//             'exercice_social_fin' => $row[$this->mapping['exercice_social_fin']],
//             'date_creation' => $row[$this->mapping['date_creation']],
//             'assujettie_partielle_tva' => $row[$this->mapping['assujettie_partielle_tva']],
//             'prorata_de_deduction' => $row[$this->mapping['prorata_de_deduction']],
//             'nature_activite' => $row[$this->mapping['nature_activite']],
//             'activite' => $row[$this->mapping['activite']],
//             'regime_declaration' => $row[$this->mapping['regime_declaration']],
//             'fait_generateur' => $row[$this->mapping['fait_generateur']],
//             'rubrique_tva' => $row[$this->mapping['rubrique_tva']],
//             'designation' => $row[$this->mapping['designation']],
//             'nombre_chiffre_compte' => $row[$this->mapping['nombre_chiffre_compte']],
//             'modele_comptable' => $row[$this->mapping['modele_comptable']],
//         ]);
//     }
// }





namespace App\Imports;

use App\Models\Societe;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Log;  // Pour loguer les erreurs

class SocietesImport implements ToModel
{
    protected $mapping;

    public function __construct($mapping)
    {
        $this->mapping = $mapping;
    }

    public function model(array $row)
    {
        // Déboguer pour vérifier le contenu des données avant de les insérer
        Log::info('Données en cours d\'importation : ', $row);
    
        try {
            // Créer ou mettre à jour la société
            $societe = new Societe([
                'raison_sociale' => $row[$this->mapping['raison_sociale']] ?? null,
                'forme_juridique' => $row[$this->mapping['forme_juridique']] ?? null,
                'siege_social' => $row[$this->mapping['siege_social']] ?? null,
                'patente' => $row[$this->mapping['patente']] ?? null,
                'rc' => $row[$this->mapping['rc']] ?? null,
                'centre_rc' => $row[$this->mapping['centre_rc']] ?? null,
                'identifiant_fiscal' => $row[$this->mapping['identifiant_fiscal']] ?? null,
                'ice' => $row[$this->mapping['ice']] ?? null,
                'assujettie_partielle_tva' => $row[$this->mapping['assujettie_partielle_tva']] ?? null,
                'prorata_de_deduction' => $row[$this->mapping['prorata_de_deduction']] ?? null,
                'exercice_social_debut' => $row[$this->mapping['exercice_social_debut']] ?? null,
                'exercice_social_fin' => $row[$this->mapping['exercice_social_fin']] ?? null,
                'date_creation' => $row[$this->mapping['date_creation']] ?? null,
                'nature_activite' => $row[$this->mapping['nature_activite']] ?? null,
                'activite' => $row[$this->mapping['activite']] ?? null,
                'regime_declaration' => $row[$this->mapping['regime_declaration']] ?? null,
                'fait_generateur' => $row[$this->mapping['fait_generateur']] ?? null,
                'rubrique_tva' => $row[$this->mapping['rubrique_tva']] ?? null,
                'designation' => $row[$this->mapping['designation']] ?? null,
                'nombre_chiffre_compte' => $row[$this->mapping['nombre_chiffre_compte']] ?? null,
                'modele_comptable' => $row[$this->mapping['modele_comptable']] ?? null,
            ]);
            
            // Enregistrer la société dans la base de données
            $societe->save();
            
            return $societe;
        } catch (\Exception $e) {
            // Loguer l'erreur si l'importation échoue
            Log::error('Erreur lors de l\'insertion de la société : ' . $e->getMessage());
            return null; // Retourner null si une erreur survient
        }
    }
    
}
