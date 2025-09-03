<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau des Sociétés</title>

    <!-- Tabulator CSS -->
    <link href="https://unpkg.com/tabulator-tables@5.3.2/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://unpkg.com/tabulator-tables@5.3.2/dist/js/tabulator.min.js"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">



    <!-- Chargement de Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <!-- Chargement de Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <!-- Chargement de Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <style>
        body {
            background-color: #f9f9f9;
            color: #333;
        }
        /* Cibler les inputs de filtre dans Tabulator */
        .tabulator .tabulator-header .tabulator-header-filter input {
            width: 100px; /* Ajuster la largeur selon vos besoins */
            height: 20px;
            font-size: 12px; /* Réduire la taille de la police */
            padding: 5px; /* Ajuster les marges internes */
        }
/* Personnaliser la largeur des options dans le dropdown */
/* .select2-dropdown.custom-dropdown {
    width: 400px !important;   
}

 .select2-results__option {
    width: 50% !important;  
} */

    </style>
</head>
<body>


@extends('layouts.user_type.auths')
@section('content')

<h2>Liste des Sociétés</h2>
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">

                <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2" id="open-modal-btn" style="color: #007bff; border-color: #007bff;">+&nbsp; Nouvelle société</button>
                    <button id="import-societes" class="btn btn-outline-secondary d-flex align-items-center gap-2">Importer Sociétés</button>

                    <button id="export-button" class="btn btn-outline-success d-flex align-items-center gap-2">Liste Des Dossiers    </button>




                </div><br>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <div id="societes-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une nouvelle société -->
<div class="modal fade" id="nouvelleSocieteModal" tabindex="-1" aria-labelledby="nouvelleSocieteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nouvelleSocieteModalLabel">Nouvelle Société</h5>
                <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>
                </div>
            <div class="modal-body">
            <form id="societe-form" action="{{ route('societes.store') }}" method="POST">
            @csrf
                                <input type="hidden" name="dbName" value="{{ DB::getDatabaseName() }}">
                             <!-- Première ligne : 3 inputs -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="code_societe" class="form-label">Code Société :</label>
                            <input type="text" id="code_societe" name="code_societe" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="raison_sociale" class="form-label">Raison sociale</label>
                            <input type="text" class="form-control" name="raison_sociale" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="forme_juridique" class="form-label">Forme Juridique</label>
                            <select class="form-control" name="forme_juridique" required>
                                <option value="">choisir une option</option>
                                <option value="SARL">SARL</option>
                                <option value="SARL-AU">SARL-AU</option>
                                <option value="SA">SA</option>
                                <option value="SAS">SAS</option>
                                <option value="SNC">SNC</option>
                                <option value="SCS">SCS</option>
                                <option value="SCI">SCI</option>
                                <option value="SEP">SEP</option>
                                <option value="GIE">GIE</option>
                            </select>
                        </div>
                    </div>

                    <!-- Deuxième ligne : Siège Social et Patente sur une seule ligne -->
                    <div class="row">
                        <div class="col-md-8 mb-3"> <!-- 2/3 de la largeur -->
                            <label for="siege_social" class="form-label">Siège Social</label>
                            <input type="text" class="form-control" name="siege_social" required>
                        </div>
                        <div class="col-md-4 mb-3"> <!-- 1/3 de la largeur -->
                            <label for="patente" class="form-label">Patente</label>
                            <input type="text" class="form-control" name="patente" required>
                        </div>
                    </div>


                    <!-- Troisième ligne : 3 inputs -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="rc" class="form-label">RC</label>
                            <input type="text" class="form-control" name="rc" id="rc" required
                                   oninput="this.value=this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" class="form-control" name="identifiant_fiscal" required id="identifiant_fiscal" maxlength="8" title="Veuillez entrer uniquement des chiffres (max 8 chiffres)">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="ice" class="form-label">ICE</label>
                            <input type="text" id="ice" class="form-control" name="ice" required maxlength="15" title="Veuillez entrer uniquement des chiffres (max 15 chiffres)">
                        </div>
                    </div>

                

                    <!-- Quatrième ligne : 3 inputs -->
                    <div class="row">
                            <!-- Ajouter ici le champ CNSS avant le modèle comptable -->
                  
                        <div class="col-md-4 mb-3">
                            <label for="cnss" class="form-label">CNSS</label>
                            <input type="text" class="form-control" name="cnss" id="cnss" maxlength="15" >
                        </div>
                  
                        <div class="col-md-4 mb-3">
                            <label for="modele_comptable" class="form-label">Modèle Comptable</label>
                            <select class="form-control" name="modele_comptable" id="modele_comptable" required>
                                <option value="">choisir une option</option>    
                                <option value="Normal">Normal</option>
                                <option value="Simplifié">Simplifié</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="nombre_chiffre_compte" class="form-label">Nombre caractères Compte</label>
                            <input type="number" class="form-control" name="nombre_chiffre_compte" required>
                        </div>
                      
                    </div>

                    <!-- Cinquième ligne : 3 inputs -->
                    <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="date_creation" class="form-label">Date de Création</label>
                            <input type="date" class="form-control" name="date_creation">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="exercice_social_debut" class="form-label">Exercice comptable début</label>
                            <input type="date" name="exercice_social_debut" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="exercice_social_fin" class="form-label">Exercice comptable fin</label>
                            <input type="date" name="exercice_social_fin" class="form-control" required>
                        </div>
                    
                    </div>

                    <!-- Sixième ligne : 3 inputs -->
                    <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="nature_activite" class="form-label">Nature de l'Activité</label>
                            <select class="form-control" name="nature_activite">
                                <option value="">choisir une option</option>    
                                <option value="4.Vente de biens d'équipement">4.Vente de biens d'équipement</option>
                                <option value="5.Vente de travaux">5.Vente de travaux</option>
                                <option value="6.Vente de services">6.Vente de services</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <select class="form-control" name="assujettie_partielle_tva" id="assujettie_partielle_tva" >
                            <option value="">choisir une option</option>    
                            <option value="1">Oui</option>    
                            <option value="0">Non</option>
                               
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="text" class="form-control" name="prorata_de_deduction" id="prorata_de_deduction" >
                        </div>
                      
                    </div>

                    <!-- Septième ligne : 3 inputs -->
                    <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="regime_declaration" class="form-label">Régime de Déclaration de TVA</label>
                            <select class="form-control" name="regime_declaration">
                                <option value="">choisir une option</option>    
                                <option value="1.Mensuel">1.Mensuel </option>
                                <option value="2.Trimestriel">2.Trimestriel</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="fait_generateur" class="form-label">Fait Générateur</label>
                            <select class="form-control" name="fait_generateur">
                                <option value="">choisir une option</option>    
                                <option value="Encaissement">1.Encaissement</option>
                                <option value="Débit">2.Débit</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="rubrique_tva">Rubrique TVA</label>
                                <select class="form-control" id="rubrique_tva" name="rubrique_tva">
                                    <!-- Les options seront ajoutées par JavaScript -->
                                </select>
                            </div>
                        </div>
                    </div>
                   <!-- Boutons -->
                   <div class="d-flex justify-content-end">
                            <!-- Bouton Réinitialiser avec une très grande marge droite -->
                            <button type="reset" class="btn btn-secondary me-12">
                                <i class="fas fa-undo"></i>
                            </button>
                            <!-- Bouton Ajouter avec une très grande marge gauche -->
                            <button type="submit" class="btn" style="background-color:#007bff;color:white;" id="ajouter-societe">
                                <i class="fas fa-check"></i> Ajouter
                            </button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal pour importer des sociétés -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Importer Sociétés</h5>
                <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>
            </div>
            <div class="modal-body">
                <!-- Formulaire d'Importation -->
                <form id="import-societe-form" action="/societes/import" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="import_file" class="form-label">Choisir le fichier d'importation</label>
                        <input type="file" class="form-control" id="import_file" name="file" required>
                    </div>

                    <div class="row">
                          <!-- Emplacement pour Code Société -->
                            <div class="col-md-6 mb-3">
                                <label for="import_code_societe" class="form-label">Emplacement Code Société</label>
                                <input type="number" class="form-control" id="import_code_societe" name="code_societe">
                            </div>
                        <!-- Emplacement pour Raison Sociale -->
                        <div class="col-md-6 mb-3">
                            <label for="import_raison_sociale" class="form-label">Emplacement Raison Sociale</label>
                            <input type="number" class="form-control" id="import_raison_sociale" name="raison_sociale" required>
                        </div>
                        <!-- Emplacement pour Forme Juridique -->
                        <div class="col-md-6 mb-3">
                            <label for="import_forme_juridique" class="form-label">Emplacement Forme Juridique</label>
                            <input type="number" class="form-control" id="import_forme_juridique" name="forme_juridique">
                        </div>
                        <!-- Emplacement pour Siège Social -->
                        <div class="col-md-6 mb-3">
                            <label for="import_siège_social" class="form-label">Emplacement Siège Social</label>
                            <input type="number" class="form-control" id="import_siège_social" name="siege_social">
                        </div>
                        <!-- Emplacement pour Patente -->
                        <div class="col-md-6 mb-3">
                            <label for="import_patente" class="form-label">Emplacement Patente</label>
                            <input type="number" class="form-control" id="import_patente" name="patente">
                        </div>

                        <!-- Emplacement pour RC -->
                        <div class="col-md-6 mb-3">
                            <label for="import_rc" class="form-label">Emplacement RC</label>
                            <input type="number" class="form-control" id="import_rc" name="rc" required>
                        </div>
  

                        <!-- Emplacement pour Identifiant Fiscal -->
                        <div class="col-md-6 mb-3">
                            <label for="import_identifiant_fiscal" class="form-label">Emplacement Identifiant Fiscal</label>
                            <input type="number" class="form-control" id="import_identifiant_fiscal" name="identifiant_fiscal" required>
                        </div>
                        <!-- Emplacement pour ICE -->
                        <div class="col-md-6 mb-3">
                            <label for="import_ice" class="form-label">Emplacement ICE</label>
                            <input type="number" class="form-control" id="import_ice" name="ice" required maxlength="15">
                        </div>

                        <!-- Emplacement pour Date de Création -->
                        <div class="col-md-6 mb-3">
                            <label for="import_date_creation" class="form-label">Emplacement Date de Création</label>
                            <input type="number" class="form-control" id="import_date_creation" name="date_creation">
                        </div>

                        <!-- Emplacement pour Exercice Social Début -->
                        <div class="col-md-6 mb-3 d-flex">
                            <div class="me-2" style="flex: 1;">
                                <label for="import_exercice_social_debut" class="form-label">Emplacement Exercice comptable début</label>
                                <input type="number" class="form-control" id="import_exercice_social_debut" name="exercice_social_debut">
                            </div>
                            <!-- Emplacement pour Exercice Social Fin -->
                            <div style="flex: 1;">
                                <label for="import_exercice_social_fin" class="form-label">Emplacement Exercice comptable fin</label>
                                <input type="number" class="form-control" id="import_exercice_social_fin" name="exercice_social_fin">
                            </div>
                        </div>
    <!-- Emplacement pour CNSS -->
    <div class="col-md-6 mb-3">
                            <label for="import_cnss" class="form-label">Emplacement CNSS</label>
                            <input type="number" class="form-control" id="import_cnss" name="cnss">
                        </div>
                        <!-- Emplacement pour Modèle Comptable -->
                        <div class="col-md-6 mb-3">
                            <label for="import_model_comptable" class="form-label">Emplacement Modèle Comptable</label>
                            <input type="number" class="form-control" id="import_model_comptable" name="modele_comptable" required>
                        </div>
                        <!-- Emplacement pour Nombre caractères Compte -->
                        <div class="col-md-6 mb-3">
                            <label for="import_nombre_chiffre_compte" class="form-label">Emplacement Nombre caractères Compte</label>
                            <input type="number" class="form-control" id="import_nombre_chiffre_compte" name="nombre_chiffre_compte">
                        </div>
                        <!-- Emplacement pour Nature d'Activité -->
                        <div class="col-md-6 mb-3">
                            <label for="import_nature_activite" class="form-label">Emplacement Nature d'Activité</label>
                            <input type="number" class="form-control" id="import_nature_activite" name="nature_activite">
                        </div>
                       

                        <!-- Emplacement pour Assujettie Partielle TVA -->
                        <div class="col-md-6 mb-3">
                            <label for="import_assujettie_partielle_tva" class="form-label">Emplacement Assujettie Partielle TVA</label>
                            <input type="number" class="form-control" id="import_assujettie_partielle_tva" name="assujettie_partielle_tva">
                        </div>
                        <!-- Emplacement pour Prorata de Déduction -->
                        <div class="col-md-6 mb-3">
                            <label for="import_prorata_de_deduction" class="form-label">Emplacement Prorata de Déduction</label>
                            <input type="number" class="form-control" id="import_prorata_de_deduction" name="prorata_de_deduction">
                        </div>

                        <!-- Emplacement pour Régime de Déclaration de TVA -->
                        <div class="col-md-6 mb-3">
                            <label for="import_regime_declaration" class="form-label">Emplacement Régime de Déclaration de TVA</label>
                            <input type="number" class="form-control" id="import_regime_declaration" name="regime_declaration">
                        </div>
                        <!-- Emplacement pour Fait Générateur -->
                        <div class="col-md-6 mb-3">
                            <label for="import_fait_generateur" class="form-label">Emplacement Fait Générateur</label>
                            <input type="number" class="form-control" id="import_fait_generateur" name="fait_generateur">
                        </div>
                        <!-- Emplacement pour Rubrique TVA -->
                        <div class="col-md-6 mb-3">
                            <label for="import_rubrique_tva" class="form-label">Emplacement Rubrique TVA</label>
                            <input type="number" class="form-control" id="import_rubrique_tva" name="rubrique_tva">
                        </div>
                      
                    

                      
                    </div>

                    <button type="submit" class="btn btn-primary">Importer</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Modal Modifier Société -->
<div class="modal fade" id="modifierSocieteModal" tabindex="-1" aria-labelledby="modifierSocieteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifierSocieteModalLabel">Modifier Société</h5>
                <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>
            </div>
            <div class="modal-body">
                <form id="societe-modification-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="modification_id">
                  

                    <!-- Ligne 1 -->
                    <div class="row">

                    <div class="col-md-4 mb-3">
                        <label for="mod_code-societe" class="form-label">Code Société :</label>
                        <input type="text" id="mod_code-societe" name="mod_code-societe" class="form-control" required readOnly>
                    </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_raison_sociale" class="form-label">Raison Sociale</label>
                            <input type="text" class="form-control" id="mod_raison_sociale" name="raison_sociale" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_forme_juridique" class="form-label">Forme Juridique</label>
                            <select class="form-control" id="mod_forme_juridique" name="forme_juridique" required>
                                <option value="SARL">SARL</option>
                                <option value="SARL-AU">SARL-AU</option>
                                <option value="SA">SA</option>
                                <option value="SAS">SAS</option>
                                <option value="SNC">SNC</option>
                                <option value="SCS">SCS</option>
                                <option value="SCI">SCI</option>
                                <option value="SEP">SEP</option>
                                <option value="GIE">GIE</option>
                            </select>
                        </div>
                      
                    </div>

                    <!-- Ligne 2 -->
                    <div class="row">
    <div class="col-md-8 mb-3"> <!-- 2/3 de la largeur -->
        <label for="mod_siège_social" class="form-label">Siège Social</label>
        <input type="text" class="form-control" id="mod_siège_social" required name="siege_social">
    </div>
    <div class="col-md-4 mb-3"> <!-- 1/3 de la largeur -->
        <label for="mod_patente" class="form-label">Patente</label>
        <input type="text" class="form-control" id="mod_patente" required name="patente">
    </div>
</div>

                    <!-- Ligne 3 -->
                    <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="mod_rc" class="form-label">RC</label>
                            <input type="text" class="form-control" id="mod_rc" name="rc" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" class="form-control" id="mod_identifiant_fiscal" name="identifiant_fiscal" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_ice" class="form-label">ICE</label>
                            <input type="text" class="form-control" id="mod_ice" name="ice" required maxlength="15">
                        </div>
                       
                    </div>

                    <!-- Ligne 4 -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="mod_cnss" class="form-label">CNSS</label>
                            <input type="text" class="form-control" name="mod_cnss" id="mod_cnss" maxlength="15" >
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_modele_comptable" class="form-label">Modèle Comptable</label>
                            <select class="form-control" id="mod_modele_comptable" name="modele_comptable" required>
                                <option value="Normal">Normal</option>
                                <option value="Simplifié">Simplifié</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="mod_nombre_chiffre_compte" class="form-label">Nombre caractères Compte</label>
                            <input type="text" class="form-control" id="mod_nombre_chiffre_compte" name="nombre_chiffre_compte">
                        </div>
                       
                    </div>

                    <!-- Ligne 5 -->
                    <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="mod_date_creation" class="form-label">Date de Création</label>
                            <input type="date" class="form-control" id="mod_date_creation" name="date_creation">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_exercice_social_debut" class="form-label">Exercice comptable début</label>
                            <input type="date" class="form-control" id="mod_exercice_social_debut" required name="exercice_social_debut">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_exercice_social_fin" class="form-label">Exercice comptable fin</label>
                            <input type="date" class="form-control" id="mod_exercice_social_fin" required name="exercice_social_fin">
                        </div>
                        
                    </div>

                    <!-- Ligne 6 -->
                    <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="mod_nature_activite" class="form-label">Nature de l'Activité</label>
                            <select class="form-control" id="mod_nature_activite" name="nature_activite">
                                <option value="4.Vente de biens d'équipement"> 4.Vente de biens d'équipement</option>
                                <option value="5.Vente de travaux">5.Vente de travaux</option>
                                <option value="6.Vente de services">6.Vente de services</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <select class="form-control" id="mod_assujettie_partielle_tva" name="assujettie_partielle_tva">
                                <option value="Null">Choisir une option</option>
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="text" class="form-control" id="mod_prorata_de_deduction" name="prorata_de_deduction">
                        </div>
                       
                    </div>

                    <!-- Ligne 7 -->
                    <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="mod_regime_declaration" class="form-label">Régime de Déclaration de TVA</label>
                            <select class="form-control" id="mod_regime_declaration" name="regime_declaration">
                                <option value="1.Mensuel">1.Mensuel</option>
                                <option value="2.Trimestriel">2.Trimestriel</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_fait_generateur" class="form-label">Fait Générateur</label>
                            <select class="form-control" id="mod_fait_generateur" name="fait_generateur">
                                <option value="Encaissement">1.Encaissement</option>
                                <option value="Débit">2.Débit</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editRubriqueTVA">Rubrique TVA</label>
                            <select class="form-control select2" id="editRubriqueTVA" name="rubrique_tva">
                                <!-- Les options seront ajoutées par JavaScript -->
                            </select>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <button type="reset" class="btn btn-secondary me-8">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="submit" class="btn" style="background-color:#007bff;color:white;">Modifier</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
 

var societes = {!! json_encode($societes) !!};  // Utilisez json_encode pour convertir les données en format JSON valide

document.getElementById('societes-table').addEventListener('click', function(e) {
    if (e.target.closest('.delete-icon')) {
        const id = e.target.closest('.delete-icon').getAttribute('data-id');

        // Confirmer la suppression sans demander de mot de passe
        if (confirm("Êtes-vous sûr de vouloir supprimer cette société ?")) {
            // Envoyer la requête de suppression sans vérifier le mot de passe
            fetch("{{ url('societes') }}/" + id, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    "Content-Type": "application/json"
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau lors de la suppression');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Suppression de la ligne dans Tabulator
                    table.deleteRow(id); // Utilisez deleteRow avec l'ID pour supprimer la ligne visuellement
                    alert("Société supprimée avec succès.");
                } else {
                    alert("Erreur lors de la suppression : " + data.message);
                }
            })
            .catch(error => {
                console.error("Erreur :", error);
                alert("Une erreur s'est produite : " + error.message);
            });
        }
    }
});
</script>

<!-- Table Tabulator -->
<div id="societes-table"></div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>



<p style="margin-left:30px;">information obligatoire manquante </p>
<div style="background-color: rgba(233, 233, 13, 0.838);width:15px;height:15px;margin-top:-35px;border:1px solid #333;">

</div>
<script src="{{ asset('js/dashboard.js') }}"></script>

@endsection

</body>
</html>


