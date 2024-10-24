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

    <style>
        body {
            background-color: #f9f9f9; 
            color: #333;
        }
    </style>
</head>
<body>

@extends('layouts.user_type.auth')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <div>
                        <h5 class="mb-0">Sociétés</h5>
                    </div>
                    <button type="button" class="btn bg-gradient-primary btn-sm mb-0" id="open-modal-btn">+&nbsp; Nouvelle société</button>
                    <button id="import-societes" class="btn bg-gradient-primary btn-sm mb-0" >Importer Sociétés</button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="societe-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="raison_sociale" class="form-label">Raison sociale</label>
                            <input type="text" class="form-control" name="raison_sociale" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="forme_juridique" class="form-label">Forme Juridique</label>
                            <input type="text" class="form-control" name="forme_juridique" required>
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
                            <input type="text" class="form-control" name="rc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="centre_rc" class="form-label">Centre RC</label>
                            <input type="text" class="form-control" name="centre_rc" required>
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
                            <label for="assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <select class="form-control" name="assujettie_partielle_tva" id="assujettie_partielle_tva" required>
                                <option value="" disabled selected>Choisir une option</option>
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="text" class="form-control" name="prorata_de_deduction" id="prorata_de_deduction" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_creation" class="form-label">Date de Création</label>
                            <input type="date" class="form-control" name="date_creation" required>
                        </div>
                        <div class="col-md-6 mb-3 d-flex">
                            <div class="me-2" style="flex: 1;">
                                <label for="exercice_social_debut" class="form-label">Exercice Social Début</label>
                                <input type="date" name="exercice_social_debut" class="form-control" required>
                            </div>
                            <div style="flex: 1;">
                                <label for="exercice_social_fin" class="form-label">Exercice Social Fin</label>
                                <input type="date" name="exercice_social_fin" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
    <label for="nombre_chiffre_compte" class="form-label">Nombre Chiffre Compte</label>
    <input type="number" class="form-control" name="nombre_chiffre_compte" required>
</div>

                        <div class="col-md-6 mb-3">
                            <label for="nature_activite" class="form-label">Nature de l'Activité</label>
                            <select class="form-control" name="nature_activite">
                                <option value="">Choisir une activité</option>
                                <option value="Vente de biens d'équipement">Vente de biens d'équipement</option>
                                <option value="Vente de travaux">Vente de travaux</option>
                                <option value="Vente de services">Vente de services</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="activite" class="form-label">Activité</label>
                            <input type="text" class="form-control" name="activite" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="regime_declaration" class="form-label">Régime de Déclaration</label>
                            <input type="text" class="form-control" name="regime_declaration" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fait_generateur" class="form-label">Fait Générateur</label>
                            <input type="date" class="form-control" name="fait_generateur" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rubrique_tva" class="form-label">Rubrique TVA</label>
                            <input type="text" class="form-control" name="rubrique_tva" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="designation" class="form-label">Désignation</label>
                            <input type="text" class="form-control" name="designation" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importForm" action="{{ route('societes.import') }}" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="file" class="form-label">Choisir un fichier Excel</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="colonne_nom_entreprise" class="form-label">Colonne pour le Nom d'entreprise</label>
                            <input type="text" class="form-control" id="colonne_nom_entreprise" name="colonne_nom_entreprise" placeholder="Ex: A" required>
                        </div>
                        <div class="col">
                            <label for="colonne_forme_juridique" class="form-label">Colonne pour la Forme Juridique</label>
                            <input type="text" class="form-control" id="colonne_forme_juridique" name="colonne_forme_juridique" placeholder="Ex: B" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="colonne_siege_social" class="form-label">Colonne pour le Siège Social</label>
                            <input type="text" class="form-control" id="colonne_siege_social" name="colonne_siege_social" placeholder="Ex: C" required>
                        </div>
                        <div class="col">
                            <label for="colonne_patente" class="form-label">Colonne pour la Patente</label>
                            <input type="text" class="form-control" id="colonne_patente" name="colonne_patente" placeholder="Ex: D" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="colonne_rc" class="form-label">Colonne pour le RC</label>
                            <input type="text" class="form-control" id="colonne_rc" name="colonne_rc" placeholder="Ex: E" required>
                        </div>
                        <div class="col">
                            <label for="colonne_centre_rc" class="form-label">Colonne pour le Centre RC</label>
                            <input type="text" class="form-control" id="colonne_centre_rc" name="colonne_centre_rc" placeholder="Ex: F" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="colonne_identifiant_fiscal" class="form-label">Colonne pour l'Identifiant Fiscal</label>
                            <input type="text" class="form-control" id="colonne_identifiant_fiscal" name="colonne_identifiant_fiscal" placeholder="Ex: G" required>
                        </div>
                        <div class="col">
                            <label for="colonne_ice" class="form-label">Colonne pour l'ICE</label>
                            <input type="text" class="form-control" id="colonne_ice" name="colonne_ice" placeholder="Ex: H" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="colonne_assujettie_partielle_tva" class="form-label">Colonne pour Assujettie Partielle TVA</label>
                            <input type="text" class="form-control" id="colonne_assujettie_partielle_tva" name="colonne_assujettie_partielle_tva" placeholder="Ex: I" required>
                        </div>
                        <div class="col">
                            <label for="colonne_prorata_de_deduction" class="form-label">Colonne pour Prorata de Déduction</label>
                            <input type="text" class="form-control" id="colonne_prorata_de_deduction" name="colonne_prorata_de_deduction" placeholder="Ex: J" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="colonne_date_creation" class="form-label">Colonne pour la Date de Création</label>
                            <input type="text" class="form-control" id="colonne_date_creation" name="colonne_date_creation" placeholder="Ex: K" required>
                        </div>
                        <div class="col">
                            <label for="colonne_date_debut_exercice" class="form-label">Colonne pour la Date de Début d'Exercice</label>
                            <input type="text" class="form-control" id="colonne_date_debut_exercice" name="colonne_date_debut_exercice" placeholder="Ex: L" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="colonne_date_fin_exercice" class="form-label">Colonne pour la Date de Fin d'Exercice</label>
                            <input type="text" class="form-control" id="colonne_date_fin_exercice" name="colonne_date_fin_exercice" placeholder="Ex: M" required>
                        </div>
                        <div class="col">
                            <label for="colonne_nature_activite" class="form-label">Colonne pour la Nature de l'Activité</label>
                            <input type="text" class="form-control" id="colonne_nature_activite" name="colonne_nature_activite" placeholder="Ex: N" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="colonne_regime_de_declaration" class="form-label">Colonne pour le Régime de Déclaration</label>
                            <input type="text" class="form-control" id="colonne_regime_de_declaration" name="colonne_regime_de_declaration" placeholder="Ex: O" required>
                        </div>
                        <div class="col">
                            <label for="colonne_nombre_chiffre_compte" class="form-label">Colonne pour le Nombre de Chiffre Compte</label>
                            <input type="text" class="form-control" id="colonne_nombre_chiffre_compte" name="colonne_nombre_chiffre_compte" placeholder="Ex: P" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Importer</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal pour modifier une société -->
<div class="modal fade" id="modifierSocieteModal" tabindex="-1" aria-labelledby="modifierSocieteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifierSocieteModalLabel">Modifier la Société</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="modifierSocieteForm">
                    <div class="mb-3">
                        <label for="mod_raison_sociale" class="form-label">Raison Sociale</label>
                        <input type="text" class="form-control" id="mod_raison_sociale" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_ice" class="form-label">ICE</label>
                        <input type="text" class="form-control" id="mod_ice" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_rc" class="form-label">RC</label>
                        <input type="text" class="form-control" id="mod_rc" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                        <input type="text" class="form-control" id="mod_identifiant_fiscal" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_patente" class="form-label">Patente</label>
                        <input type="text" class="form-control" id="mod_patente" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_centre_rc" class="form-label">Centre RC</label>
                        <input type="text" class="form-control" id="mod_centre_rc" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_forme_juridique" class="form-label">Forme Juridique</label>
                        <input type="text" class="form-control" id="mod_forme_juridique" required>
                    </div>
                    <div class="mb-3 row">
                        <div class="col">
                            <label for="mod_exercice_social_debut" class="form-label">Exercice Social Début</label>
                            <input type="text" class="form-control" id="mod_exercice_social_debut" required>
                        </div>
                        <div class="col">
                            <label for="mod_exercice_social_fin" class="form-label">Exercice Social Fin</label>
                            <input type="text" class="form-control" id="mod_exercice_social_fin" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="mod_date_creation" class="form-label">Date de Création</label>
                        <input type="date" class="form-control" id="mod_date_creation" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                        <input type="text" class="form-control" id="mod_assujettie_partielle_tva" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                        <input type="text" class="form-control" id="mod_prorata_de_deduction" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_nature_activite" class="form-label">Nature d'Activité</label>
                        <input type="text" class="form-control" id="mod_nature_activite" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_activite" class="form-label">Activité</label>
                        <input type="text" class="form-control" id="mod_activite" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_regime_declaration" class="form-label">Régime de Déclaration</label>
                        <input type="text" class="form-control" id="mod_regime_declaration" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_fait_generateur" class="form-label">Fait Générateur</label>
                        <input type="text" class="form-control" id="mod_fait_generateur" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_rubrique_tva" class="form-label">Rubrique TVA</label>
                        <input type="text" class="form-control" id="mod_rubrique_tva" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_designation" class="form-label">Désignation</label>
                        <input type="text" class="form-control" id="mod_designation" required>
                    </div>
                    <div class="mb-3">
                        <label for="mod_nombre_chiffre_compte" class="form-label">Nombre de Chiffre Compte</label>
                        <input type="number" class="form-control" id="mod_nombre_chiffre_compte" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Table Tabulator -->
<h2>Liste des Sociétés</h2>

<!-- Tabulator JS -->
<script>
    // Assigner les données des sociétés à une variable JS depuis PHP
    var societes = {!! $societes !!};

    // Initialiser Tabulator avec les données
    var table = new Tabulator("#societes-table", {
        data: societes, // Charger les données passées depuis le contrôleur
        layout: "fitColumns", // Ajuster les colonnes à la largeur du tableau
        columns: [
            {title: "Raison Sociale", field: "raison_sociale", formatter: function(cell, formatterParams, onRendered){
            var nomEntreprise = cell.getData()["raison_sociale"];
            var formeJuridique = cell.getData().forme_juridique;
            return nomEntreprise + " " + formeJuridique;
        }, headerFilter: true},
            {title: "ICE", field: "ice", headerFilter: true},
            {title: "RC", field: "rc", headerFilter: true},
            {title: "Identifiant Fiscal", field: "identifiant_fiscal", headerFilter: true},
            {
            title: "Exercice Social",
            field: "exercice_social",
            formatter: function(cell) {
                const rowData = cell.getRow().getData(); // Obtenir les données de la ligne
                return `Du ${rowData.exercice_social_debut} au ${rowData.exercice_social_fin}`; // Formater les dates
            },
        },
         {
                title: "Actions",
                formatter: function(cell, formatterParams) {
                    return "<div class='action-icons'>" +
                        "<a href='#' class='text-primary mx-1' data-bs-toggle='modal' data-bs-target='#modifierSocieteModal' " +
                        "data-id='" + cell.getRow().getData().id + "' " +
                        "data-nom-entreprise='" + cell.getRow().getData().raison_sociale + "' " + 
                        "data-ice='" + cell.getRow().getData().ice + "' " +
                        "data-rc='" + cell.getRow().getData().rc + "' " +
                        "data-identifiant-fiscal='" + cell.getRow().getData().identifiant_fiscal + "'>" +
                        "<i class='fas fa-edit'></i></a>" +
                        "<a href='#' class='text-danger mx-1 delete-icon' data-id='" + cell.getRow().getData().id + "'>" +
                        "<i class='fas fa-trash'></i></a>" +
                        "</div>";
                },
                width: 150,
                hozAlign: "center"
            }
        ],
    });

    // Ouvrir le modal au clic sur le bouton
    document.getElementById('open-modal-btn').addEventListener('click', function() {
        var myModal = new bootstrap.Modal(document.getElementById('nouvelleSocieteModal'));
        myModal.show();
    });

    // Ajouter une société via Ajax sans rafraîchir la page
    document.getElementById('societe-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Empêcher l'envoi classique du formulaire

        let formData = new FormData(this);

        // Envoi Ajax
        fetch("{{ route('societes.store') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Ajouter la nouvelle société à la table Tabulator
                table.addData([data.societe]);
                // Réinitialiser le formulaire après l'ajout
                document.getElementById('societe-form').reset();
                // Fermer le modal
                var myModal = bootstrap.Modal.getInstance(document.getElementById('nouvelleSocieteModal'));
                myModal.hide();
            } else {
                alert("Erreur lors de l'ajout de la société : " + data.message);
            }
        })
        .catch(error => console.error("Erreur :", error));
    });

    // Gestion de la suppression d'une société
    document.getElementById('societes-table').addEventListener('click', function(e) {
        if (e.target.closest('.delete-icon')) {
            const id = e.target.closest('.delete-icon').getAttribute('data-id');

            if (confirm("Êtes-vous sûr de vouloir supprimer cette société ?")) {
                fetch("{{ url('societes') }}/" + id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        table.deleteRow(id); // Supprimer la ligne du tableau
                        alert("Société supprimée avec succès.");
                    } else {
                        alert("Erreur lors de la suppression : " + data.message);
                    }
                })
                .catch(error => console.error("Erreur :", error));
            }
        }
    });

// Gestion du changement de la valeur "Assujettie partielle à la TVA"
document.getElementById('assujettie_partielle_tva').addEventListener('change', function() {
    var prorataField = document.getElementById('prorata_de_deduction');
    
    if (this.value === "0") {
        prorataField.value = "100"; // Mettre la valeur à 10
        prorataField.setAttribute("readonly", true); // Rendre le champ non modifiable
    } else {
        prorataField.removeAttribute("readonly"); // Rendre le champ modifiable
        prorataField.value = ""; // Réinitialiser le champ si nécessaire
    }
});
 
</script>
<script>

document.getElementById('societes-table').addEventListener('click', function(e) {
    if (e.target.closest('.text-primary')) {
        const item = e.target.closest('.text-primary');
        
        // Récupérer l'ID de la société
        const societeId = item.getAttribute('data-id');

        // Faire une requête AJAX pour récupérer les données de la société
        fetch(`/societes/${societeId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                // Remplir les champs de la modale avec les données
                document.getElementById('mod_raison_sociale').value = data.raison_sociale;
                document.getElementById('mod_ice').value = data.ice;
                document.getElementById('mod_rc').value = data.rc;
                document.getElementById('mod_identifiant_fiscal').value = data.identifiant_fiscal;
                document.getElementById('mod_patente').value = data.patente;
                document.getElementById('mod_centre_rc').value = data.centre_rc;
                document.getElementById('mod_forme_juridique').value = data.forme_juridique;
                document.getElementById('mod_exercice_social_debut').value = data.exercice_social_debut; // Nouveau champ
                document.getElementById('mod_exercice_social_fin').value = data.exercice_social_fin; // Nouveau champ
                document.getElementById('mod_date_creation').value = data.date_creation;
                document.getElementById('mod_assujettie_partielle_tva').value = data.assujettie_partielle_tva;
                document.getElementById('mod_prorata_de_deduction').value = data.prorata_de_deduction;
                document.getElementById('mod_nature_activite').value = data.nature_activite;
                document.getElementById('mod_activite').value = data.activite;
                document.getElementById('mod_regime_declaration').value = data.regime_declaration;
                document.getElementById('mod_fait_generateur').value = data.fait_generateur;
                document.getElementById('mod_rubrique_tva').value = data.rubrique_tva;
                document.getElementById('mod_designation').value = data.designation;
                document.getElementById('mod_nombre_chiffre_compte').value = data.nombre_chiffre_compte; // Nouveau champ

                // Afficher la modale de modification
                var myModal = new bootstrap.Modal(document.getElementById('modifierSocieteModal'));
                myModal.show();
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
    }
});



</script>


<script>
    document.getElementById("identifiant_fiscal").addEventListener("input", function() {
    // Remplace tous les caractères non numériques par une chaîne vide
    this.value = this.value.replace(/\D/g, '');

    // Limite la longueur à 8 chiffres
    if (this.value.length > 8) {
        this.value = this.value.slice(0, 8);
    }
});

</script>

<script>
    document.getElementById("ice").addEventListener("input", function() {
    // Remplace tous les caractères non numériques par une chaîne vide
    this.value = this.value.replace(/\D/g, '');

    // Limite la longueur à 15 chiffres
    if (this.value.length > 15) {
        this.value = this.value.slice(0, 15);
    }
});

</script>

<script>
$(function() {
  $('#exercice_social').daterangepicker({
    opens: 'left',
    startDate: moment('2018-01-01'), // Date de début par défaut
    endDate: moment('2019-01-15'), // Date de fin par défaut
    locale: {
      format: 'YYYY-MM-DD'
    },
    // Permet de choisir une plage de dates
    singleDatePicker: false,
    showDropdowns: true,
    autoUpdateInput: true
  }, function(start, end) {
    // Met à jour le champ d'entrée avec les dates sélectionnées
    $('#exercice_social').val(start.format('YYYY-MM-DD') + ' au ' + end.format('YYYY-MM-DD'));
  });
});
</script>
<script>
    document.getElementById('import-societes').addEventListener('click', function() {
    // Logique d'importation, par exemple, ouvrir un modal
    openImportModal();
});

function openImportModal() {
    // Code pour afficher le modal d'importation
    $('#importModal').modal('show'); // Utiliser Bootstrap modal si vous l'avez
}

</script>


<script>

    // Écouter l'événement de clic sur les icônes de suppression
document.addEventListener("click", function(e) {
    if (e.target && e.target.closest(".delete-icon")) {
        // Récupérer l'ID de la société à partir de l'attribut data-id
        var societeId = e.target.closest(".delete-icon").getAttribute("data-id");

        // Demander confirmation avant de supprimer
        if (confirm("Êtes-vous sûr de vouloir supprimer cette société ?")) {
            // Appeler la fonction pour supprimer la société
            deleteSociete(societeId);
        }
    }
});

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

@endsection

</body>
</html>
