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
                            <label for="raison_sociale" class="form-label">Nom d'entreprise</label>
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
                            <input type="text" class="form-control" name="identifiant_fiscal" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ice" class="form-label">ICE</label>
                            <input type="text" class="form-control" name="ice" required>
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
                        <div class="col-md-6 mb-3">            
                            <label for="exercice_social" class="form-label">Exercice Social</label>
                            <input type="text" class="form-control" name="exercice_social" required>
                        </div>
                      
                        <div class="col-md-6 mb-3">
    <label for="nature_activite" class="form-label">Nature de l'Activité</label>
    <select class="form-control" name="nature_activite" required>
        <option value="" disabled selected>Choisir une activité</option>
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

<!-- Modal pour modifier une société -->
<div class="modal fade" id="modifierSocieteModal" tabindex="-1" aria-labelledby="modifierSocieteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifierSocieteModalLabel">Modifier Société</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="modifierSocieteForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mod_raison_sociale" class="form-label">Nom d'entreprise</label>
                            <input type="text" class="form-control" id="mod_raison_sociale" name="raison_sociale" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_forme_juridique" class="form-label">Forme Juridique</label>
                            <input type="text" class="form-control" id="mod_forme_juridique" name="forme_juridique" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_siege_social" class="form-label">Siège Social</label>
                            <input type="text" class="form-control" id="mod_siege_social" name="siege_social" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_patente" class="form-label">Patente</label>
                            <input type="text" class="form-control" id="mod_patente" name="patente" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_rc" class="form-label">RC</label>
                            <input type="text" class="form-control" id="mod_rc" name="rc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_centre_rc" class="form-label">Centre RC</label>
                            <input type="text" class="form-control" id="mod_centre_rc" name="centre_rc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" class="form-control" id="mod_identifiant_fiscal" name="identifiant_fiscal" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_ice" class="form-label">ICE</label>
                            <input type="text" class="form-control" id="mod_ice" name="ice" required>
                        </div>
                        <div class="col-md-6 mb-3">
    <label for="assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
    <select class="form-control" name="assujettie_partielle_tva" required>
        <option value="" disabled selected>Choisir une option</option>
        <option value="Oui">Oui</option>
        <option value="Non">Non</option>
    </select>
</div>

                        <div class="col-md-6 mb-3">
                            <label for="mod_prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="text" class="form-control" id="mod_prorata_de_deduction" name="prorata_de_deduction" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_exercice_social" class="form-label">Exercice Social</label>
                            <input type="text" class="form-control" id="mod_exercice_social" name="exercice_social" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_date_creation" class="form-label">Date de Création</label>
                            <input type="date" class="form-control" id="mod_date_creation" name="date_creation" required>
                        </div>
                        <div class="col-md-6 mb-3">
    <label for="nature_activite" class="form-label">Nature de l'Activité</label>
    <select class="form-control" name="nature_activite" required>
        <option value="" disabled selected>Choisir une activité</option>
        <option value="Vente de biens d'équipement">Vente de biens d'équipement</option>
        <option value="Vente de travaux">Vente de travaux</option>
        <option value="Vente de services">Vente de services</option>
    </select>
</div>

                        <div class="col-md-6 mb-3">
                            <label for="mod_activite" class="form-label">Activité</label>
                            <input type="text" class="form-control" id="mod_activite" name="activite" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_regime_declaration" class="form-label">Régime de Déclaration</label>
                            <input type="text" class="form-control" id="mod_regime_declaration" name="regime_declaration" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_fait_generateur" class="form-label">Fait Générateur</label>
                            <input type="date" class="form-control" id="mod_fait_generateur" name="fait_generateur" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_rubrique_tva" class="form-label">Rubrique TVA</label>
                            <input type="text" class="form-control" id="mod_rubrique_tva" name="rubrique_tva" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_designation" class="form-label">Désignation</label>
                            <input type="text" class="form-control" id="mod_designation" name="designation" required>
                        </div>
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
            {title: "Nom d'entreprise", field: "raison_sociale"},
            {title: "ICE", field: "ice"},
            {title: "RC", field: "rc"},
            {title: "Identifiant Fiscal", field: "identifiant_fiscal"},
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

    // Logique de remplissage du modal de modification
    document.getElementById('societes-table').addEventListener('click', function(e) {
    if (e.target.closest('.text-primary')) {
        const item = e.target.closest('.text-primary');
        // Remplir les champs de la modale avec les données
        document.getElementById('mod_raison_sociale').value = item.getAttribute('data-nom-entreprise');
        document.getElementById('mod_ice').value = item.getAttribute('data-ice');
        document.getElementById('mod_rc').value = item.getAttribute('data-rc');
        document.getElementById('mod_identifiant_fiscal').value = item.getAttribute('data-identifiant-fiscal');
        // Remplir les autres champs ici...

        // Ajouter un champ caché pour stocker l'ID de la société
        document.getElementById('modifierSocieteForm').innerHTML += 
            `<input type="hidden" id="societe_id" name="societe_id" value="${item.getAttribute('data-id')}">`;

        // Afficher la modale de modification
        var myModal = new bootstrap.Modal(document.getElementById('modifierSocieteModal'));
        myModal.show();
    }
});




// Gérer la soumission du formulaire de modification
document.getElementById("modifierSocieteForm").addEventListener("submit", function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var societeId = document.getElementById("societe_id").value;

    axios.put(`/societes/${societeId}`, formData)
        .then(function(response) {
            $('#modifierSocieteModal').modal('hide');
            table.setData("{{ route('societes.index') }}"); // Recharger les données du tableau
            alert('Société modifiée avec succès.');
        })
        .catch(function(error) {
            console.error("Erreur :", error);
        });
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

@endsection

</body>
</html>
