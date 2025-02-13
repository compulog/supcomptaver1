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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="raison_sociale" class="form-label">Raison sociale</label>
                            <input type="text" class="form-control" name="raison_sociale" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="forme_juridique" class="form-label">Forme Juridique</label>
                            <select class="form-control" name="forme_juridique">
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
                        <div class="col-md-6 mb-3">
                            <label for="siege_social" class="form-label">Siège Social</label>
                            <input type="text" class="form-control" name="siege_social" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="patente" class="form-label">Patente</label>
                            <input type="text" class="form-control" name="patente" required>
                        </div>
                        <div class="col-md-6 mb-3">
    <label for="rc" class="form-label">RC</label>
    <input type="text" class="form-control" name="rc" id="rc" required
           oninput="this.value=this.value.replace(/[^0-9]/g, '')">
</div>

                        <div class="col-md-6 mb-3">
                            <label for="centre_rc" class="form-label">Centre RC</label>

                        <select id="ctl00_ctl36_g_69b20002_9278_429e_be53_84b78f0af32b_ctl00_DropDownList_Ville" class="form-control" name="centre_rc" required>
						<option value="AGADIR">AGADIR</option>
						<option value="AL HOCEIMA">AL HOCEIMA</option>
						<option value="AZILAL">AZILAL</option>
						<option value="AZROU">AZROU</option>
						<option value="BEN AHMED">BEN AHMED</option>
						<option value="BEN GUERIR">BEN GUERIR</option>
						<option value="BENI MELLAL">BENI MELLAL</option>
						<option value="BENSLIMANE">BENSLIMANE</option>
						<option value="BERKANE">BERKANE</option>
						<option value="BERRECHID">BERRECHID</option>
						<option value="BOUARFA">BOUARFA</option>
						<option value="BOUJAAD">BOUJAAD</option>
						<option value="BOULEMANE">BOULEMANE</option>
						<option value="CASABLANCA">CASABLANCA</option>
						<option value="CHEFCHAOUEN">CHEFCHAOUEN</option>
						<option value="CHICHAOUA">CHICHAOUA</option>
						<option value="DAKHLA">DAKHLA</option>
						<option value="EL JADIDA">EL JADIDA</option>
						<option value="EL KALAA SRAGHNA">EL KALAA SRAGHNA</option>
						<option value="ERRACHIDIA">ERRACHIDIA</option>
						<option value="ES SMARA">ES SMARA</option>
						<option value="ESSAOUIRA">ESSAOUIRA</option>
						<option value="FES">FES</option>
						<option value="FIGUIG">FIGUIG</option>
						<option value="FKIH BEN SALEH">FKIH BEN SALEH</option>
						<option value="GUELMIM">GUELMIM</option>
						<option value="GUERCIF">GUERCIF</option>
						<option value="IMINTANOUTE">IMINTANOUTE</option>
						<option value="INZEGANE">INZEGANE</option>
						<option value="KASBA TADLA">KASBA TADLA</option>
						<option value="KENITRA">KENITRA</option>
						<option value="KHEMISSET">KHEMISSET</option>
						<option value="KHENIFRA">KHENIFRA</option>
						<option value="KHOURIBGA">KHOURIBGA</option>
						<option value="KSAR EL KEBIR">KSAR EL KEBIR</option>
						<option value="LAAYOUNE">LAAYOUNE</option>
						<option value="LARACHE">LARACHE</option>
						<option value="MARRAKECH">MARRAKECH</option>
						<option value="MEKNES">MEKNES</option>
						<option value="MIDELT">MIDELT</option>
						<option value="MOHAMMEDIA">MOHAMMEDIA</option>
						<option value="NADOR">NADOR</option>
						<option value="OUARZAZATE">OUARZAZATE</option>
						<option value="Oued Ed-Dahab">Oued Ed-Dahab</option>
						<option value="OUED ZEM">OUED ZEM</option>
						<option value="OUEZZANE">OUEZZANE</option>
						<option value="OUJDA">OUJDA</option>
						<option value="RABAT">RABAT</option>
						<option value="SAFI">SAFI</option>
						<option value="SALE">SALE</option>
						<option value="SEFROU">SEFROU</option>
						<option value="SETTAT">SETTAT</option>
						<option value="SIDI BENNOUR">SIDI BENNOUR</option>
						<option value="SIDI KACEM">SIDI KACEM</option>
						<option value="SIDI SLIMANE">SIDI SLIMANE</option>
						<option value="SOUK LARBAA">SOUK LARBAA</option>
						<option value="TAN TAN">TAN TAN</option>
						<option value="TANGER">TANGER</option>
						<option value="TAOUNATE">TAOUNATE</option>
						<option value="TAOURIRT">TAOURIRT</option>
						<option value="TAROUDANT">TAROUDANT</option>
						<option value="TATA">TATA</option>
						<option value="TAZA">TAZA</option>
						<option value="TEMARA">TEMARA</option>
						<option value="TETOUAN">TETOUAN</option>
						<option value="TIFELT">TIFELT</option>
						<option value="TINGHIR">TINGHIR</option>
						<option value="TIZNIT">TIZNIT</option>
						<option value="YOUSSOUFIA">YOUSSOUFIA</option>
						<option value="ZAGORA">ZAGORA</option>

					</select>
                    </div>
                        <div class="col-md-6 mb-3">
                            <label for="identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" class="form-control" name="identifiant_fiscal" required id="identifiant_fiscal" maxlength="8" title="Veuillez entrer uniquement des chiffres (max 8 chiffres)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ice" class="form-label">ICE</label>
                            <input type="text" id="ice" class="form-control" name="ice" required maxlength="15" title="Veuillez entrer uniquement des chiffres (max 15 chiffres)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_creation" class="form-label">Date de Création</label>
                            <input type="date" class="form-control" name="date_creation" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="exercice_social_debut" class="form-label">Exercice Social Début</label>
                            <input type="date" name="exercice_social_debut" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="exercice_social_fin" class="form-label">Exercice Social Fin</label>
                            <input type="date" name="exercice_social_fin" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                    <label for="modele_comptable" class="form-label">Modèle Comptable</label>
                    <select class="form-control" name="modele_comptable" id="modele_comptable" required>
                        <option value="Normal">Normal</option>
                        <option value="Simplifié">Simplifié</option>
                    </select>
                </div>

                        <div class="col-md-6 mb-3">
                            <label for="nombre_chiffre_compte" class="form-label">Nombre caractères Compte</label>
                            <input type="number" class="form-control" name="nombre_chiffre_compte" required>
                        </div>


                        <div class="col-md-6 mb-3">
                            <label for="nature_activite" class="form-label">Nature de l'Activité</label>
                            <select class="form-control" name="nature_activite">

                                <option value="4.Vente de biens d'équipement">Vente de biens d'équipement</option>
                                <option value="5.Vente de travaux">Vente de travaux</option>
                                <option value="6.Vente de services">Vente de services</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="activite" class="form-label">Activité</label>
                            <input type="text" class="form-control" name="activite" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <select class="form-control" name="assujettie_partielle_tva" id="assujettie_partielle_tva" required>
                            <option value="Null">choisir un option</option>
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="text" class="form-control" name="prorata_de_deduction" id="prorata_de_deduction" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="regime_declaration" class="form-label">Régime de Déclaration de TVA</label>

                            <select class="form-control" name="regime_declaration" required>
                                 <option value="Mensuel de droit commun">Mensuel de droit commun</option>
                                <option value="Trimestriel de droit commun">Trimestriel de droit commun</option>
                                <option value="Mensuel de la marge">Mensuel de la marge</option>
                                <option value="Trimestriel de la marge">Trimestriel de la marge</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fait_generateur" class="form-label">Fait Générateur</label>
                            <select class="form-control" name="fait_generateur" required>
                                 <option value="Encaissement">Encaissement</option>
                                <option value="Débit">Débit</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="rubrique_tva">Rubrique TVA</label>
                                <select class="form-control" id="rubrique_tva"  name="rubrique_tva">
                                    <!-- Les options seront ajoutées par JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="designation" class="form-label">Désignation</label>
                            <input type="text" class="form-control" name="designation" required>
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
                <form id="import-societe-form" action="{{ route('societes.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="import_file" class="form-label">Choisir le fichier d'importation</label>
                        <input type="file" class="form-control" id="import_file" name="file" required>
                    </div>

                    <div class="row">
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
                        <!-- Emplacement pour Centre RC -->
                        <div class="col-md-6 mb-3">
                            <label for="import_centre_rc" class="form-label">Emplacement Centre RC</label>
                            <input type="number" class="form-control" id="import_centre_rc" name="centre_rc">
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
                                <label for="import_exercice_social_debut" class="form-label">Emplacement Exercice Social Début</label>
                                <input type="number" class="form-control" id="import_exercice_social_debut" name="exercice_social_debut">
                            </div>
                            <!-- Emplacement pour Exercice Social Fin -->
                            <div style="flex: 1;">
                                <label for="import_exercice_social_fin" class="form-label">Emplacement Exercice Social Fin</label>
                                <input type="number" class="form-control" id="import_exercice_social_fin" name="exercice_social_fin">
                            </div>
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
                        <!-- Emplacement pour Activité -->
                        <div class="col-md-6 mb-3">
                            <label for="import_activite" class="form-label">Emplacement Activité</label>
                            <input type="number" class="form-control" id="import_activite" name="activite">
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
                        <!-- Emplacement pour Désignation -->
                        <div class="col-md-6 mb-3">
                            <label for="import_designation" class="form-label">Emplacement Désignation</label>
                            <input type="number" class="form-control" id="import_designation" name="designation">
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mod_raison_sociale" class="form-label">Raison Sociale</label>
                            <input type="text" class="form-control" id="mod_raison_sociale" name="raison_sociale" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_forme_juridique" class="form-label">Forme Juridique</label>
                            <input type="text" class="form-control" id="mod_forme_juridique" name="forme_juridique">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_siège_social" class="form-label">Siège Social</label>
                            <input type="text" class="form-control" id="mod_siège_social" name="siege_social">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_patente" class="form-label">Patente</label>
                            <input type="text" class="form-control" id="mod_patente" name="patente">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_rc" class="form-label">RC</label>
                            <input type="text" class="form-control" id="mod_rc" name="rc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_centre_rc" class="form-label">Centre RC</label>
                            <input type="text" class="form-control" id="mod_centre_rc" name="centre_rc">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" class="form-control" id="mod_identifiant_fiscal" name="identifiant_fiscal" required>
                        </div>


                        <div class="col-md-6 mb-3">
                            <label for="mod_ice" class="form-label">ICE</label>
                            <input type="text" class="form-control" id="mod_ice" name="ice" required maxlength="15" title="Veuillez entrer uniquement des chiffres (max 15 chiffres)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_date_creation" class="form-label">Date de Création</label>
                            <input type="date" class="form-control" id="mod_date_creation" name="date_creation">
                        </div>



                        <div class="col-md-6 mb-3 d-flex">
                            <div class="me-2" style="flex: 1;">
                                <label for="mod_exercice_social_debut" class="form-label">Exercice Social Début</label>
                                <input type="date" class="form-control" id="mod_exercice_social_debut" name="exercice_social_debut">
                            </div>
                            <div style="flex: 1;">
                                <label for="mod_exercice_social_fin" class="form-label">Exercice Social Fin</label>
                                <input type="date" class="form-control" id="mod_exercice_social_fin" name="exercice_social_fin">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="mod_model_comptable" class="form-label">Modèle Comptable</label>
                            <input type="text" class="form-control" id="mod_model_comptable" name="modele_comptable" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_nombre_chiffre_compte" class="form-label">Nombre caractères Compte</label>
                            <input type="text" class="form-control" id="mod_nombre_chiffre_compte" name="nombre_chiffre_compte">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="mod_assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <input type="text" class="form-control" id="mod_assujettie_partielle_tva" name="assujettie_partielle_tva">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="text" class="form-control" id="mod_prorata_de_deduction" name="prorata_de_deduction">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_nature_activite" class="form-label">Nature d'Activité</label>
                            <input type="text" class="form-control" id="mod_nature_activite" name="nature_activite">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_activite" class="form-label">Activité</label>
                            <input type="text" class="form-control" id="mod_activite" name="activite">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_regime_declaration" class="form-label">Régime de Déclaration de TVA</label>
                            <input type="text" class="form-control" id="mod_regime_declaration" name="regime_declaration">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_fait_generateur" class="form-label">Fait Générateur</label>
                            <input type="text" class="form-control" id="mod_fait_generateur" name="fait_generateur">
                        </div>
                        <div class="col-md-6 mb-3">
                        <label for="editRubriqueTVA">Rubrique TVA</label>

                               <select class="form-control select2" id="editRubriqueTVA" name="rubrique_tva" required>

                                   <!-- Les options seront ajoutées par JavaScript -->
                               </select>
                              </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_designation" class="form-label">Désignation</label>
                            <input type="text" class="form-control" id="mod_designation" name="designation">
                        </div>

                    </div>
                     <!-- Bouton Réinitialiser avec marge très grande à droite -->
                     <button type="reset" class="btn btn-secondary me-8">
                            <i class="fas fa-undo"></i>
                        </button>
                    <button type="submit" class="btn " style="background-color:#007bff;color:white;">Modifier</button>
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


