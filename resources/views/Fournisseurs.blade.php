<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- Liens CSS et JS externes -->
   <!-- Bootstrap CSS -->
   <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

   <!-- Font Awesome -->
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

   <!-- Select2 CSS -->
   {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
   <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" /> --}}
   <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


   <!-- Tabulator CSS -->
   <link href="https://unpkg.com/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">

   <!-- Icônes Bootstrap -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">





</head>

    <style>
.invalid-row {
            background-color: rgba(228, 20, 20, 0.453)!important; /* Rouge clair */
            color: black!important; /* Texte rouge foncé */
        }



</style>


</head>


<body>
 <!-- jQuery -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Tabulator JS -->
    <script src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>


@extends('layouts.user_type.auth')

@section('content')


@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif


<!-- Conteneur principal -->
<!-- Section principale -->
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary">Liste des Fournisseurs</h3>
    </div>

    <!-- Boutons d'actions -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <!-- Bouton Créer -->
        <button class="btn btn-outline-primary d-flex align-items-center gap-2" id="addFournisseurBtn" data-bs-toggle="modal" data-bs-target="#fournisseurModaladd">
            <i class="bi bi-plus-circle"></i> Créer
        </button>

        <!-- Bouton Importer -->
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2" id="importFournisseurBtn" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-arrow-up"></i> Importer
        </button>

        <!-- Bouton Exporter en Excel -->
        <a href="{{ url('/export-fournisseurs-excel') }}" class="btn btn-outline-success d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-excel"></i> Exporter en Excel
        </a>

        <!-- Bouton Exporter en PDF -->
        <a href="{{ url('/export-fournisseurs-pdf') }}" class="btn btn-outline-danger d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-pdf"></i> Exporter en PDF
        </a>
    </div>

    <!-- Tableau des Fournisseurs -->
    <div id="fournisseur-table" class="border rounded shadow-sm bg-white p-3"></div>
</div>

<p style="font-size: 14px; color: black; margin-top: 10px;">
    <span style="background-color: rgba(233, 233, 13, 0.838); /* Jaune clair/orangé */
                 color: black;
                 padding: 2px 4px;
                 border-radius: 4px;
                 text-align:center;
                 border: 1px solid black;
                 display: inline-block;
                 width: 20px;
                 height: 20px;">
    </span>
    Champ Manquant Obligatoire
</p>

<p style="font-size: 14px; color: black; margin-top: 10px;">
    <span style="background-color: rgba(228, 20, 20, 0.453);
                 color: black;
                 padding: 2px 4px;
                 border-radius: 4px;
                 border: 1px solid black;
                 display: inline-block;
                 width: 20px;
                 height: 20px;">
    </span>
    Compte Erroné
</p>



<!-- Formulaire d'importation Excel -->
<!-- Exemple d'un modal avec des améliorations visuelles et des commentaires -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true" data-bs-animation="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="importModalLabel"><i class="fas fa-upload"></i> Importation des Fournisseurs</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="importForm" action="{{ route('fournisseurs.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">
                    <input type="hidden" name="nombre_chiffre_compte" value="{{ $societe->nombre_chiffre_compte }}">

                    <div class="form-group mb-3">
                        <label for="file" class="form-label"><strong>Fichier Excel</strong></label>
     <input type="file" class="form-control form-control-lg shadow-sm" name="file" id="file" accept=".xlsx, .xls, .csv" required>
                    </div>
                    {{-- <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_chiffre_compte_display" class="form-label">Nombre de Chiffres du Compte</label>
                            <input type="text" class="form-control form-control-lg shadow-sm" id="nombre_chiffre_compte_display" value="{{ $societe->nombre_chiffre_compte }}" readonly>
                        </div>
                    </div> --}}

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="colonne_compte" class="form-label">Colonne Compte</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_compte" id="colonne_compte"   required>


                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="colonne_intitule" class="form-label">Colonne Intitulé</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_intitule" id="colonne_intitule"   required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="colonne_identifiant_fiscal" class="form-label">Colonne Identifiant Fiscal</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_identifiant_fiscal" id="colonne_identifiant_fiscal" >
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="colonne_ICE" class="form-label">Colonne ICE</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_ICE" id="colonne_ICE"  >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="colonne_nature_operation" class="form-label">Colonne Nature de l'Opération</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_nature_operation" id="colonne_nature_operation"  >
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="colonne_rubrique_tva" class="form-label">Colonne Rubrique TVA</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_rubrique_tva" id="colonne_rubrique_tva" >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="colonne_designation" class="form-label">Colonne Désignation</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_designation" id="colonne_designation" >
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="colonne_contre_partie" class="form-label">Colonne Contre Partie</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_contre_partie" id="colonne_contre_partie" >
                        </div>
                    </div>


                        <div class="d-flex justify-content-between">
                            <!-- Bouton de réinitialisation -->
                            <button type="button" class="btn btn-secondary mr-2" id="resetModal">
                                <i class="bi bi-arrow-clockwise fs-6"></i> Réinitialiser
                            </button>
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-check"></i> Importer
                        </button>
                        <div id="loadingSpinner" class="d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="mt-4">
</div>


<div id="errorMessages" class="alert alert-danger d-none">
    <ul id="errorList"></ul>
</div>


<!-- Modal add-->
<!-- Modal Ajouter Fournisseur -->
<div class="modal fade" id="fournisseurModaladd" tabindex="-1" aria-labelledby="fournisseurModalLabel" aria-hidden="true" data-bs-animation="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light text-white">
                <h5 class="modal-title" id="fournisseurModalLabel"><i class="fas fa-plus-circle"></i> Créer Fournisseur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="fournisseurFormAdd">
                    @csrf
                    <input type="hidden" id="nombre_chiffre_compte" value="{{ $societe->nombre_chiffre_compte }}">
                    <input type="hidden" id="societe_id" name="societe_id" value="{{ session('societeId') }}">
                    <div class="row">
                        <!-- Champ Compte -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="compte">Compte</label>
                                <div class="d-flex">
                                    <input type="text" class="form-control form-control-sm shadow-sm " id="compte" name="compte" placeholder="4411XXXX" required>
                                    {{-- <button type="button" class="btn btn-secondary btn-sm" id="autoIncrementBtn" >
                                       auto
                                    </button> --}}
                                </div>
                            </div>
                        </div>
                        <!-- Champ Intitulé -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="intitule">Intitulé</label>
                                <input type="text" class="form-control form-control-sm shadow-sm" id="intitule" name="intitule" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Champ Identifiant Fiscal -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="identifiant_fiscal">Identifiant Fiscal</label>
                                <input type="text" class="form-control form-control-sm shadow-sm" id="identifiant_fiscal" name="identifiant_fiscal" maxlength="8" pattern="\d*">
                            </div>
                        </div>
                        <!-- Champ ICE -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ICE">ICE</label>
                                <input type="text" class="form-control form-control-sm shadow-sm" id="ICE" name="ICE" maxlength="15" pattern="\d*">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Nature de l'opération -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nature_operation">Nature de l'opération</label>
                                <select class="form-select form-select-sm shadow-sm" id="nature_operation" name="nature_operation">
                                    <option value="">Sélectionner une option</option>
                                    <option value="1-Achat de biens d'équipement">1-Achat de biens d'équipement</option>
                                    <option value="2-Achat de travaux">2-Achat de travaux</option>
                                    <option value="3-Achat de services">3-Achat de services</option>
                                </select>
                            </div>
                        </div>
                        <!-- Contre Partie -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contre_partie">Contre Partie</label>
                                <select class="form-select" id="contre_partie" name="contre_partie" required>
                                    <option value="">Sélectionner une contre partie</option>
                                    <option value="add_new" class="text-primary">+ Ajouter un nouveau compte</option>
                                    <!-- Les autres options peuvent être ajoutées ici -->
                                </select>
                                <p class="text-muted mt-1" style="font-size: 0.875rem;">Si vous ne trouvez pas la contrepartie souhaitée, <a href="#" id="ajouterCompteLink">ajoutez une nouvelle contrepartie</a>.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Rubrique TVA -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rubrique_tva">Rubrique TVA</label>
                                <select class="form-select2" id="rubrique_tva" name="rubrique_tva">
                                    <option value="" selected>Sélectionner une rubrique</option>
                                </select>
                            </div>
                        </div>
                        <!-- Désignation -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="designation">Désignation</label>
                                <input type="text" class="form-control form-control-sm shadow-sm" id="designation" name="designation" placeholder="Désignation">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary btn-sm" id="resetModal"><i class="bi bi-arrow-clockwise fs-6"></i> Réinitialiser</button>
                        <button type="submit" class="btn btn-primary btn-sm">Valider <i class="bi bi-check-lg"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal Edit -->
<div class="modal fade" id="fournisseurModaledit" tabindex="-1" role="dialog" aria-labelledby="fournisseurModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="fournisseurModalLabel">Modifier un compte</h5>
                <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="fournisseurFormEdit">
                    <input type="hidden" id="editFournisseurId" value="">

                    <!-- Formulaire -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editCompte">Compte</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editCompte" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editIntitule">Intitulé</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editIntitule" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editIdentifiantFiscal">Identifiant Fiscal</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editIdentifiantFiscal" maxlength="8" pattern="\d*">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editICE">ICE</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editICE" maxlength="15" pattern="\d*">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editNatureOperation">Nature de l'opération</label>
                                <select class="form-select form-select-lg shadow-sm" id="editNatureOperation">
                                    <option value="">Sélectionner une option</option>
                                    <option value="1-Achat de biens d'équipement">1-Achat de biens d'équipement</option>
                                    <option value="2-Achat de travaux">2-Achat de travaux</option>
                                    <option value="3-Achat de services">3-Achat de services</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editContrePartie">Contre Partie</label>
                                <select class="form-select form-select-lg shadow-sm select2" id="editContrePartie">
                                    <option value="">Sélectionnez une contre partie</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editRubriqueTVA">Rubrique TVA</label>
                                <select class="form-select form-select-lg shadow-sm" id="editRubriqueTVA">
                                    <option value="">Sélectionnez une Rubrique</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editDesignation">Désignation</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editDesignation" placeholder="Designation">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <!-- Bouton de réinitialisation -->
                        <button type="button" class="btn btn-secondary mr-2" id="resetModal">
                            <i class="bi bi-arrow-clockwise fs-6"></i> Réinitialiser
                        </button>
                        <!-- Bouton de validation -->
                        <button type="submit" class="btn btn-primary ml-2">Valider
                            <i class="bi bi-check-lg bi-2x"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Plan Comptable -->
<div class="modal fade" id="planComptableModalAdd" tabindex="-1" aria-labelledby="planComptableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="planComptableModalLabel">Ajouter un compte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="planComptableFormAdd">
                    <!-- Champs du formulaire -->
                    <div class="form-group ">
                        <input type="hidden" id="nombre_chiffre_compte" value="{{ $societe->nombre_chiffre_compte }}">
                        <input type="hidden" id="societe_id" name="societe_id" value="{{ session('societeId') }}">
                        <label for="compte_add" class="form-label">Compte</label>
                        <input type="text" class="form-control form-control-lg shadow-sm" id="compte_add" name="compte" placeholder="Entrer le numéro du compte">

                    </div>
                    <div class="form-group ">
                        <label for="intitule_add" class="form-label">Intitulé</label>
                        <input type="text" class="form-control form-control-lg shadow-sm" id="intitule_add" name="intitule" placeholder="Entrer l'intitulé">
                    </div>
                    <div class="d-flex justify-content-between">
                        <!-- Bouton de réinitialisation -->
                        <button type="button" class="btn btn-secondary mr-2" id="resetModal">
                            <i class="bi bi-arrow-clockwise fs-6"></i> Réinitialiser
                        </button>
                        <!-- Bouton de validation -->
                        <button type="submit" class="btn btn-primary ml-2">Ajouter
                            <i class="bi bi-check-lg bi-2x"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

 <!-- Statistiques -->
 <span id="select-stats" class="text-muted"></span>

 <script type="text/javascript">
    // Initialisation du tableau Tabulator
    var table = new Tabulator("#fournisseur-table", {
    ajaxURL: "/fournisseurs/data", // URL pour récupérer les données
    layout: "fitColumns",
    height: "600px", // Hauteur du tableau
    selectable: true, // Permet de sélectionner les lignes
    initialSort: [
        { column: "compte", dir: "asc" } // Tri initial
    ],
    columns: [
        {
            title: `
                <i class="fas fa-check-square" id="selectAllIcon" title="Sélectionner tout" style="cursor: pointer;"></i>
                <i class="fas fa-trash-alt" id="deleteAllIcon" title="Supprimer toutes les lignes sélectionnées" style="cursor: pointer;"></i>
            `,
            field: "select",
            formatter: "rowSelection", // Active la sélection de ligne
            headerSort: false,
            hozAlign: "center",
            width: 60,
            cellClick: function (e, cell) {
                cell.getRow().toggleSelect(); // Basculer la sélection de ligne
            },
        },
        { title: "Compte", field: "compte", editor: "input", headerFilter: "input" },
        { title: "Intitulé", field: "intitule", headerFilter: "input" },
        { title: "Identifiant Fiscal", field: "identifiant_fiscal", headerFilter: "input" },
        { title: "ICE", field: "ICE", headerFilter: "input" },
        { title: "Nature de l'opération", field: "nature_operation", headerFilter: "input" },
        { title: "Rubrique TVA", field: "rubrique_tva", headerFilter: "input" },
        { title: "Désignation", field: "designation", headerFilter: "input" },
        { title: "Contre Partie", field: "contre_partie", headerFilter: "input" },
        { title: "Invalid", field: "invalid", visible: false }, // Champs caché mais utile pour les validations
        {
            title: "Actions",
            field: "action-icons",
            formatter: function () {
                return `
                    <i class='fas fa-edit text-primary edit-icon' style='font-size: 0.9em; cursor: pointer;'></i>
                    <i class='fas fa-trash-alt text-danger delete-icon' style='font-size: 0.9em; cursor: pointer;'></i>
                `;
            },
            cellClick: function (e, cell) {
                var row = cell.getRow();

                // Vérifier quel élément a été cliqué
                if (e.target.classList.contains("edit-icon")) {
                    var rowData = cell.getRow().getData();
                    editFournisseur(rowData); // Fonction de modification
                } else if (e.target.classList.contains("delete-icon")) {
                    var rowData = cell.getRow().getData();
                    deleteFournisseur(rowData.id);
                }
            },
            hozAlign: "center",
            headerSort: false,
        },
    ],
    rowFormatter: function (row) {
        let data = row.getData();
        let rowElement = row.getElement();

        // Réinitialiser les styles au début
        rowElement.style.backgroundColor = "";
        rowElement.classList.remove("invalid-row");

        // Vérification pour compte et intitulé vides ou nuls
        if ((!data.compte && !data.intitule) || (!data.compte && data.intitule) || (!data.intitule && data.compte)) {
            rowElement.style.backgroundColor = "rgba(233, 233, 13, 0.838)"; // Jaune orangé
        } else if (data.invalid === 1) {
            rowElement.classList.add("invalid-row");
        }
    },
});

// Définir un événement pour l'édition des cellules
table.on("cellEdited", function (cell) {
    let rowData = cell.getRow().getData();
    if (cell.getField() === "compte") {
        fetch(`/fournisseurs/update/${rowData.id}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({ compte: rowData.compte }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert("Succès: " + data.message);
                    cell.getRow().update({ invalid: 0 }); // Supprimer le surlignement rouge
                } else {
                    alert("Erreur: " + data.message);
                }
            })
            .catch((error) => {
                alert("Erreur: Impossible de mettre à jour le fournisseur.");
                console.error(error);
            });
    }
});

// Fonction pour supprimer les lignes sélectionnées côté serveur
function deleteSelectedRows() {
    var selectedRows = table.getSelectedRows();
    var idsToDelete = selectedRows.map(function (row) {
        return row.getData().id;
    });

    if (idsToDelete.length > 0) {
        if (confirm("Voulez-vous vraiment supprimer les lignes sélectionnées ?")) {
            fetch("/fournisseurs/delete-selected", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({ ids: idsToDelete }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert("Succès: " + data.message);
                        selectedRows.forEach((row) => row.delete());
                    } else {
                        alert("Erreur: " + data.message);
                    }
                })
                .catch((error) => {
                    alert("Erreur: Impossible de supprimer les fournisseurs sélectionnés.");
                    console.error(error);
                });
        }
    } else {
        alert("Aucune ligne sélectionnée.");
    }
}

// Gestionnaire pour sélectionner/désélectionner toutes les lignes
document.getElementById("fournisseur-table").addEventListener("click", function (e) {
    if (e.target.id === "selectAllIcon") {
        if (table.getSelectedRows().length === table.getRows().length) {
            table.deselectRow(); // Désélectionner tout
        } else {
            table.selectRow(); // Sélectionner tout
        }
    } else if (e.target.id === "deleteAllIcon") {
        deleteSelectedRows();
    }
});


// Intégration des formulaires d'ajout et de modification
// (Code précédent ajouté ici, sans aucune modification)

// Initialisation globale
var designationValue = ''; // Variable globale pour stocker l'intitulé

// Fonction pour remplir les rubriques TVA

// Fonction pour remplir les options de contrepartie
// Fonction pour remplir un champ de sélection avec des données provenant d'une API
function remplirContrePartie(selectId, selectedValue = null, callback = null) {
    $.ajax({
        url: '/comptes',
        type: 'GET',
        success: function (data) {
            console.log("Données reçues de l'API :", data); // Log des données pour débogage

            // Sélectionner l'élément avec l'ID fourni
            var select = $("#" + selectId);

            // Vérifier si l'élément existe
            if (select.length === 0) {
                console.error("Élément avec l'ID", selectId, "non trouvé dans le DOM.");
                return;
            }

            // Si Select2 est initialisé, le détruire pour éviter des conflits
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2("destroy");
            }

            // Réinitialiser le champ de sélection et ajouter une option par défaut
            select.empty();
            select.append(new Option("Sélectionnez une contre partie", ""));

            // Trier les données par ordre alphabétique (par le champ compte)
            data.sort((a, b) => a.compte.localeCompare(b.compte));

            // Ajouter les options au champ de sélection
            data.forEach(function (compte) {
                let option = new Option(`${compte.compte} - ${compte.intitule}`, compte.compte);
                select.append(option);
            });

            // Réinitialiser et appliquer Select2
            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true
            });

            // Si une valeur sélectionnée est fournie, la définir
            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }

            // Exécuter le callback si défini
            if (callback && typeof callback === 'function') {
                callback();
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des comptes :', textStatus, errorThrown);
        }
    });
}

// Soumission du formulaire d'ajout
$("#fournisseurFormAdd").on("submit", function (e) {
    e.preventDefault();
    var designationValue = $('#designation').val();

    if (designationValue === '') {
        var contrePartieIntitule = $('#contre_partie').find('option:selected').text();
        var intitule = contrePartieIntitule.split('-')[1]?.trim();
        if (intitule) {
            $('#designation').val(intitule);
        }
    }

    envoyerDonnees();
});

// Envoi des données via AJAX
function envoyerDonnees() {
    $.ajax({
        url: "/fournisseurs",
        type: "POST",
        data: {
            compte: $("#compte").val(),
            intitule: $("#intitule").val(),
            identifiant_fiscal: $("#identifiant_fiscal").val(),
            ICE: $("#ICE").val(),
            nature_operation: $("#nature_operation").val(),
            rubrique_tva: $("#rubrique_tva option:selected").text(), // Texte complet récupéré
            designation: $("#designation").val(),
            contre_partie: $("#contre_partie").val(),
            societe_id: $("#societe_id").val(),
            _token: '{{ csrf_token() }}'
        },
        success: function (response) {
            table.setData("/fournisseurs/data");
            $("#fournisseurModaladd").modal("hide");
            $("#fournisseurFormAdd")[0].reset();
        },
        error: function (xhr) {
            console.error("Erreur lors de l'envoi des données:", xhr.responseText);
        }
    });
}
// Remplir les rubriques TVA
function remplirRubriquesTva(selectId, selectedValue = null) {
    $.ajax({
        url: '/get-rubriques-tva',
        type: 'GET',
        success: function (data) {
            var select = $("#" + selectId);

            // Réinitialisation de Select2 s'il est déjà initialisé
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2("destroy");
            }
            select.empty();
            select.append(new Option("Sélectionnez une Rubrique", ""));

            let categoriesArray = [];
            $.each(data.rubriques, function (categorie, rubriques) {
                let categories = categorie.split('/').map(cat => cat.trim());
                let mainCategory = categories[0];
                let subCategory = categories[1] ? categories[1].trim() : '';
                categoriesArray.push({
                    mainCategory: mainCategory,
                    subCategory: subCategory,
                    rubriques: rubriques.rubriques
                });
            });

            categoriesArray.sort((a, b) => a.mainCategory.localeCompare(b.mainCategory));
            let categoryCounter = 1;
            const excludedNumRacines = [147, 151, 152, 148, 144];

            $.each(categoriesArray, function (index, categoryObj) {
                let mainCategoryOption = new Option(`${categoryCounter}. ${categoryObj.mainCategory}`, '', true, true);
                mainCategoryOption.className = 'category';
                mainCategoryOption.disabled = true;
                select.append(mainCategoryOption);
                categoryCounter++;

                if (categoryObj.subCategory) {
                    let subCategoryOption = new Option(` ${categoryObj.subCategory}`, '', true, true);
                    subCategoryOption.className = 'subcategory';
                    subCategoryOption.disabled = true;
                    select.append(subCategoryOption);
                }

                categoryObj.rubriques.forEach(function (rubrique) {
                    if (!excludedNumRacines.includes(rubrique.Num_racines)) {
                        let option = new Option(`${rubrique.Num_racines}: ${rubrique.Nom_racines} : ${Math.round(rubrique.Taux)}%`, rubrique.Num_racines);
                        option.setAttribute('data-search-text', `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.mainCategory}`);
                        select.append(option);
                    }
                });
            });

            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true,
                templateResult: function (data) {
                    if (!data.id) return data.text;
                    if ($(data.element).hasClass('category')) {
                        return $('<span style="font-weight: bold;">' + data.text + '</span>');
                    } else if ($(data.element).hasClass('subcategory')) {
                        return $('<span style="font-weight: bold; padding-left: 10px;">' + data.text + '</span>');
                    }
                    return $('<span>' + data.text + '</span>');
                },
                matcher: function (params, data) {
                    if ($.trim(params.term) === '') return data;
                    var searchText = $(data.element).data('search-text');
                    return searchText && searchText.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
                }
            });

            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des rubriques :', textStatus, errorThrown);
        }
    });
}

    // edit
// Gestion du formulaire de modification
$("#fournisseurFormEdit").on("submit", function(e) {
    e.preventDefault();
    var fournisseurId = $("#editFournisseurId").val();
    var url = "/fournisseurs/" + fournisseurId;

    var designationValue = $('#editDesignation').val();
    if (designationValue === '') {
        var contrePartieIntitule = $('#editContrePartie').find('option:selected').text();
        var intitule = contrePartieIntitule.split('-')[1]?.trim();
        if (intitule) {
            $('#editDesignation').val(intitule);
        }
    }

    $.ajax({
        url: url,
        type: "PUT",
        data: {
            compte: $("#editCompte").val(),
            intitule: $("#editIntitule").val(),
            identifiant_fiscal: $("#editIdentifiantFiscal").val(),
            ICE: $("#editICE").val(),
            nature_operation: $("#editNatureOperation").val(),
            rubrique_tva: $("#editRubriqueTVA option:selected").text(), // Texte complet récupéré
            designation: $("#editDesignation").val(),
            contre_partie: $("#editContrePartie").val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            table.setData("/fournisseurs/data");
            $("#fournisseurModaledit").modal("hide");
            $("#fournisseurFormEdit")[0].reset();
        },
        error: function(xhr) {
            alert("Erreur lors de l'enregistrement des données !");
        }
    });
});

// Remplissage du formulaire pour modification
function editFournisseur(data) {
    $("#editFournisseurId").val(data.id);
    $("#editCompte").val(data.compte);
    $("#editIntitule").val(data.intitule);
    $("#editIdentifiantFiscal").val(data.identifiant_fiscal);
    $("#editICE").val(data.ICE);
    $("#editNatureOperation").val(data.nature_operation);
    // remplirRubriquesTva("editRubriqueTVA", data.rubrique_tva);
     $("#editRubriqueTVA option:selected").text(data.rubrique_tva); // Texte complet récupéré

    remplirContrePartie("editContrePartie", data.contre_partie);
    $("#editDesignation").val(data.designation);
    $("#fournisseurModaledit").modal("show");
}

//gestion add
$(document).ready(function () {
    // Variables initiales
    var initialValue = '4411'; // Préfixe des fournisseurs
    var societeId = $('#societe_id').val(); // ID de la société
    var nombreChiffresCompte = parseInt($('#nombre_chiffre_compte').val()); // Nombre de chiffres du compte

    // Fonction pour envoyer les données via AJAX
    function envoyerDonnees() {
        $.ajax({
            url: "/fournisseurs",
            type: "POST",
            data: {
                compte: $("#compte").val(),
                intitule: $("#intitule").val(),
                identifiant_fiscal: $("#identifiant_fiscal").val(),
                ICE: $("#ICE").val(),
                nature_operation: $("#nature_operation").val(),
                rubrique_tva: $("#rubrique_tva option:selected").text(),
                designation: $("#designation").val(),
                contre_partie: $("#contre_partie").val(),
                societe_id: $("#societe_id").val(),
                nombre_chiffre_compte: nombreChiffresCompte,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                console.log(response);  // Pour vérifier la réponse complète
                if (response.success) {
                    // Mise à jour de la table Tabulator sans recharger la page
                    table.addData([{
                        compte: $("#compte").val(),
                        intitule: $("#intitule").val(),
                        identifiant_fiscal: $("#identifiant_fiscal").val(),
                        ICE: $("#ICE").val(),
                        nature_operation: $("#nature_operation").val(),
                        rubrique_tva: $("#rubrique_tva option:selected").text(),
                        designation: $("#designation").val(),
                        contre_partie: $("#contre_partie").val(),
                        societe_id: $("#societe_id").val()
                    }]);

                    // Mise à jour de la table Plan Comptable
                    $.ajax({
                        url: '/plancomptable',
                        type: 'POST',
                        data: {
                            compte: $("#compte").val(),
                            intitule: $("#intitule").val(),
                            societe_id: $("#societe_id").val(),
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (planComptableResponse) {
                            if (planComptableResponse.success) {
                                alert("Fournisseur et compte ajoutés avec succès.");
                            } else {
                                alert("Erreur lors de l'ajout du compte dans le plan comptable.");
                            }
                        },
                        error: function (xhr) {
                            console.error("Erreur lors de l'ajout du compte dans le plan comptable:", xhr.responseText);
                            alert("Erreur lors de l'ajout du compte dans le plan comptable.");
                        }
                    });

                    // Réinitialisation du modal
                    $("#fournisseurModaladd").modal("hide");
                    $("#fournisseurFormAdd")[0].reset();
                    $('#fournisseurFormAdd select').val('').trigger('change');
                } else {
                    alert("Erreur lors de l'ajout du fournisseur : " + response.error);
                }
            },
            error: function (xhr) {
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    alert("Erreur de validation : " + JSON.stringify(errors));
                } else {
                    alert("Erreur lors de l'envoi des données.");
                }
            }
        });
    }

    // Lors de l'ouverture du modal
    $('#fournisseurModaladd').on('shown.bs.modal', function () {
        // Ajouter le backdrop
        $('body').append('<div class="modal-backdrop fade show"></div>');

        // Initialisation des champs
        remplirRubriquesTva('rubrique_tva');
        remplirContrePartie('contre_partie');
        $('#compte').focus();
        $('#rubrique_tva').val('').trigger('change');
        $('#designation').val('');
        // Générer un compte automatiquement au chargement
        genererCompteAuto();
    });

    // Suppression du backdrop à la fermeture du modal
    $('#fournisseurModaladd').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
    });

    // Remplir les rubriques TVA
    function remplirRubriquesTva(selectId, selectedValue = null) {
    $.ajax({
        url: '/get-rubriques-tva',
        type: 'GET',
        success: function (data) {
            var select = $("#" + selectId);

            // Réinitialisation de Select2 s'il est déjà initialisé
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2("destroy");
            }
            select.empty();
            select.append(new Option("Sélectionnez une Rubrique", ""));

            let categoriesArray = [];
            $.each(data.rubriques, function (categorie, rubriques) {
                let categories = categorie.split('/').map(cat => cat.trim());
                let mainCategory = categories[0];
                let subCategory = categories[1] ? categories[1].trim() : '';
                categoriesArray.push({
                    mainCategory: mainCategory,
                    subCategory: subCategory,
                    rubriques: rubriques.rubriques
                });
            });

            categoriesArray.sort((a, b) => a.mainCategory.localeCompare(b.mainCategory));
            let categoryCounter = 1;
            const excludedNumRacines = [147, 151, 152, 148, 144];

            $.each(categoriesArray, function (index, categoryObj) {
                let mainCategoryOption = new Option(`${categoryCounter}. ${categoryObj.mainCategory}`, '', true, true);
                mainCategoryOption.className = 'category';
                mainCategoryOption.disabled = true;
                select.append(mainCategoryOption);
                categoryCounter++;

                if (categoryObj.subCategory) {
                    let subCategoryOption = new Option(` ${categoryObj.subCategory}`, '', true, true);
                    subCategoryOption.className = 'subcategory';
                    subCategoryOption.disabled = true;
                    select.append(subCategoryOption);
                }

                categoryObj.rubriques.forEach(function (rubrique) {
                    if (!excludedNumRacines.includes(rubrique.Num_racines)) {
                        let option = new Option(`${rubrique.Num_racines}: ${rubrique.Nom_racines} : ${Math.round(rubrique.Taux)}%`, rubrique.Num_racines);
                        option.setAttribute('data-search-text', `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.mainCategory}`);
                        select.append(option);
                    }
                });
            });

            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true,
                templateResult: function (data) {
                    if (!data.id) return data.text;
                    if ($(data.element).hasClass('category')) {
                        return $('<span style="font-weight: bold;">' + data.text + '</span>');
                    } else if ($(data.element).hasClass('subcategory')) {
                        return $('<span style="font-weight: bold; padding-left: 10px;">' + data.text + '</span>');
                    }
                    return $('<span>' + data.text + '</span>');
                },
                matcher: function (params, data) {
                    if ($.trim(params.term) === '') return data;
                    var searchText = $(data.element).data('search-text');
                    return searchText && searchText.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
                }
            });

            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des rubriques :', textStatus, errorThrown);
        }
    });
}

    // Remplir les options de contrepartie
    function remplirContrePartie(selectId, selectedValue = null) {
        $.ajax({
            url: '/comptes',
            type: 'GET',
            success: function (data) {
                var select = $("#" + selectId);
                if (select.hasClass("select2-hidden-accessible")) {
                    select.select2("destroy");
                }
                select.empty();
                select.append(new Option("Sélectionnez une contre partie", ""));
                data.sort((a, b) => a.compte.localeCompare(b.compte));
                data.forEach(function (compte) {
                    let option = new Option(`${compte.compte} - ${compte.intitule}`, compte.compte);
                    select.append(option);
                });
                select.select2({
                    width: '100%',
                    minimumResultsForSearch: 0,
                    dropdownAutoWidth: true
                });
                if (selectedValue) {
                    select.val(selectedValue).trigger('change');
                }
            }
        });
    }

    // Générer un compte automatiquement
    function genererCompteAuto() {
        $.ajax({
            url: `/get-next-compte/${societeId}`,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    $('#compte').val(response.nextCompte);
                } else {
                    alert('Erreur lors de la génération du compte.');
                }
            }
        });
    }

    // Evénement pour auto-incrémenter le compte
    $('#autoIncrementBtn').on('click', function () {
        genererCompteAuto();
    });

    // Evénement pour changer la société et mettre à jour les paramètres
    $('#societe_id').on('change', function () {
        societeId = $(this).val(); // Mettre à jour l'ID de la société sélectionnée
        nombreChiffresCompte = parseInt($('#nombre_chiffre_compte').val()); // Mettre à jour la configuration
        // Mettre à jour la longueur maximale du champ "compte"
        $('#compte').attr('maxlength', nombreChiffresCompte);
        genererCompteAuto(); // Régénérer le compte
    });

    // Validation des champs ICE et identifiant_fiscal
    $("#ICE, #identifiant_fiscal").on("input", function () {
        this.value = this.value.replace(/[^0-9]/g, ''); // Supprimer tout sauf les chiffres
        if (this.value.length > 15) {
            this.value = this.value.slice(0, 15); // Limiter à 15 caractères
        }
    });
});


// gestion bouton plan
$(document).ready(function () {
    // Gestion de la soumission du formulaire d'ajout
    $('#planComptableFormAdd').on('submit', function (e) {
        e.preventDefault(); // Empêche la soumission classique

        // Récupération des données
        const compte = $('#compte_add').val().trim();
        const intitule = $('#intitule_add').val().trim();
        const societeId = $('#societe_id').val();

        if (!compte || !intitule) {
            alert("Veuillez remplir tous les champs obligatoires.");
            return;
        }

        // Requête AJAX pour ajouter un nouveau compte
        $.ajax({
            url: '/plancomptable', // Route définie dans votre contrôleur Laravel
            type: 'POST',
            data: {
                compte: compte,
                intitule: intitule,
                societe_id: societeId,
                _token: '{{ csrf_token() }}' // Protection CSRF
            },
            beforeSend: function () {
                // Désactiver le bouton pour éviter les doubles soumissions
                $('#planComptableFormAdd button[type="submit"]').prop('disabled', true).text('Ajout en cours...');
            },
            success: function (response) {
                if (response.success && response.data) {
                    const newOption = new Option(
                        `${response.data.compte} - ${response.data.intitule}`,
                        response.data.compte,
                        true,
                        true
                    );
                    $('#contre_partie').append(newOption).trigger('change'); // Ajouter le nouveau compte au select

                    // Fermer le modal et réinitialiser le formulaire
                    $('#planComptableModalAdd').modal('hide');
                    $('#planComptableFormAdd')[0].reset();

                    alert(response.message || "Compte ajouté avec succès !");
                } else {
                    alert("Erreur : " + (response.message || "Réponse inattendue du serveur."));
                }
            },
            error: function (xhr) {
                console.error("Erreur :", xhr.responseText);
                alert("Une erreur est survenue. Veuillez réessayer.");
            },
            complete: function () {
                $('#planComptableFormAdd button[type="submit"]').prop('disabled', false).text('Ajouter');
            }
        });
    });

    // Réinitialisation du formulaire
    $('#resetModal').on('click', function () {
        $('#planComptableFormAdd')[0].reset();
    });

    // Gestion de l'ouverture du modal depuis le lien "Ajouter un compte"
    $('#ajouterCompteLink').on('click', function (e) {
        e.preventDefault();
        $('#planComptableFormAdd')[0].reset();
        $('#planComptableModalAdd').modal('show');
    });

    // Gestion de l'ouverture du modal depuis le menu déroulant "contre_partie"
    $('#contre_partie').on('change', function () {
        const selectedValue = $(this).val();
        if (selectedValue === 'add_new') {
            $('#planComptableFormAdd')[0].reset();
            $('#planComptableModalAdd').modal('show');
            $(this).val('').trigger('change');
        }
    });
});



// excel
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const reader = new FileReader();

    reader.onload = function(event) {
        const data = new Uint8Array(event.target.result);
        const workbook = XLSX.read(data, { type: 'array' });

        const sheetName = workbook.SheetNames[0]; // Sélection de la première feuille
        const worksheet = workbook.Sheets[sheetName];
        const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1 }); // Lecture ligne par ligne

        if (rows.length > 1) {
            const headers = rows[0]; // La première ligne comme en-têtes de colonnes

            // Vérifier si le fichier contient des colonnes suffisantes
            if (headers.length < 2) {
                alert("Le fichier Excel doit contenir au moins 2 colonnes.");
                return;
            }

            // Références vers les sélecteurs pour lier les colonnes avec des champs
            const selectors = {
                compte: document.querySelector('input[name="colonne_compte"]'),
                intitule: document.querySelector('input[name="colonne_intitule"]'),
                identifiantFiscal: document.querySelector('input[name="colonne_identifiant_fiscal"]'),
                ICE: document.querySelector('input[name="colonne_ICE"]'),
                natureOperation: document.querySelector('input[name="colonne_nature_operation"]'),
                rubriqueTva: document.querySelector('input[name="colonne_rubrique_tva"]'),
                designation: document.querySelector('input[name="colonne_designation"]'),
                contrePartie: document.querySelector('input[name="colonne_contre_partie"]'),
            };

            // Génération des listes de colonnes disponibles
            const columnOptions = headers.map((header, index) => ({
                label: header || `Colonne ${index + 1}`, // Nom de la colonne ou défaut
                value: index + 1 // Numéro de colonne (1-indexé)
            }));

            // Associer chaque champ de sélection à une liste de colonnes
            Object.keys(selectors).forEach(key => {
                const optionsHtml = columnOptions.map(option => `
                    <option value="${option.value}">${option.label}</option>
                `).join('');
                selectors[key].setAttribute("list", `${key}-datalist`);
                if (!document.getElementById(`${key}-datalist`)) {
                    const dataList = document.createElement("datalist");
                    dataList.id = `${key}-datalist`;
                    dataList.innerHTML = optionsHtml;
                    document.body.appendChild(dataList);
                }
            });
        } else {
            alert("Le fichier Excel semble être vide !");
        }
    };

    reader.readAsArrayBuffer(file);
});


document.getElementById('resetModal').addEventListener('click', function () {
    const form = document.getElementById('importForm');
    form.reset(); // Réinitialise tous les champs du formulaire
});
// Navigation avec la touche Entrée
document.getElementById('importForm').addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
        event.preventDefault(); // Empêche le comportement par défaut (soumission du formulaire)

        const formElements = Array.from(event.target.form.elements); // Récupère tous les champs du formulaire
        const index = formElements.indexOf(event.target); // Trouve l'index du champ actuel

        // Déplace le focus au champ suivant si disponible
        if (index > -1 && index < formElements.length - 1) {
            formElements[index + 1].focus();
        }
    }
});




  // Fonction pour supprimer un fournisseur
  function deleteFournisseur(id) {
    // Demande de confirmation
    if (confirm("Êtes-vous sûr de vouloir supprimer ce fournisseur ?")) {
        // Appel à la route de suppression
        $.ajax({
            url: "/fournisseurs/" + id,
            type: "DELETE",
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                table.setData("/fournisseurs/data"); // Recharger les données
            },
            error: function(xhr) {
                alert("Erreur lors de la suppression des données !");
            }
        });
    }
}


// Convertir une date du format "yyyy-MM-dd" au format "dd/MM/yyyy"
function formatToDDMMYYYY(dateString) {
    const [year, month, day] = dateString.split('-');
    return `${day}/${month}/${year}`;
}

// Convertir une date du format "dd/MM/yyyy" au format "yyyy-MM-dd"
function formatToYYYYMMDD(dateString) {
    const [day, month, year] = dateString.split('/');
    return `${year}-${month}-${day}`;
}


</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>



</body>

</html>

@endsection
