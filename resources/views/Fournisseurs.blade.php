<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Gestion des Fournisseurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tabulator-tables@5.2.4/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>
    <link href="https://unpkg.com/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chargement de jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!-- Chargement de Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<!-- Chargement de Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Chargement de Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>

    <style>
/* Style pour le conteneur du tableau */
#tabulator-table {
    overflow-y: auto; /* Activer le défilement vertical */
    border: 1px solid #ddd; /* Ajouter une bordure si nécessaire */
}

/* Optionnel : Style pour le tableau */
.tabulator {
    border-collapse: collapse; /* Pour un meilleur rendu visuel */
}


    #tabulator-table .tabulator-header {
    height: 15px; /* Ajustez la hauteur du header */
    font-size: 0.9em; /* Réduisez la taille de la police */
    padding: 2px 5px; /* Ajustez le padding pour réduire l'espacement */
    background-color: #f8f9fa; /* Couleur de l'en-tête */
}


#tabulator-table .tabulator-header .tabulator-col-title {
    font-size: 0.85em; /* Taille de police des titres des colonnes */
}

/* Ajuste le champ de recherche dans le header */
.tabulator .tabulator-header input[type="search"] {
    height: 20px; /* Diminue la hauteur */
    padding: 1px 3px; /* Ajuste le padding interne */
    font-size: 0.8em; /* Diminue légèrement la police */}
    .btn-custom-gradient {
    background-image: linear-gradient(to right, #344767, #31477a) !important; /* Dégradé de gauche à droite */
    color: white !important; /* Couleur du texte en blanc */
    border: none; /* Pas de bordure */
    /* transition: background-color!important 0.1s ease; Transition douce pour le survol */
}


   

#fournisseur-table .tabulator-row {
    transition: all 0.1s ease-in-out; /* Animation pour un effet dynamique */
}

    /* background-color: #e9ecef !important; Fond gris clair au survol */
    .tabulator .tabulator-row:hover {
    background-color: #31477a !important;  /* Couleur de survol */
    color: white;  /* Texte en blanc lors du survol pour plus de contraste */
}


.bg-light {
    background-color: #d1ecf1 !important; /* Fond bleu clair pour la sélection de ligne */
}

.tabulator .tabulator-col, .tabulator .tabulator-header {
    font-weight: bold;
    color: #495057 !important; /* Couleur de texte sombre */
}

/*
.btn-custom-gradient:hover {
    background-image: linear-gradient(to right, #536fb2, #344767)!important;  Inverser le dégradé au survol 
  
}  */

#fournisseur-table {
    overflow: auto; /* Permet le défilement */
    max-height: 800px; /* Hauteur maximale du conteneur */
}
/* Applique un style ajusté tout en gardant le style du bouton btn-secondary */
.input-group .btn-secondary {
    padding: 0.375rem 0.75rem; /* Ajuste le padding horizontal et vertical */
    font-size: 1rem; /* Taille du texte cohérente avec celle de l'input */
    font-weight: 400; /* Poids de police standard */
    color: #6c757d; /* Couleur par défaut du bouton secondaire (qui est la couleur d'origine de btn-secondary) */
    background-color: #e2e6ea; /* Couleur d'arrière-plan du bouton secondaire */
    border-color: #adb5bd; /* Bordure du bouton secondaire */
    border-radius: 0.25rem; /* Coins arrondis pour un look plus moderne */
   
}




</style>

</head>


<body>

@extends('layouts.user_type.auth')

@section('content')

<!-- Conteneur principal -->
<!-- Section principale -->
<div class="container mt-5">
    <h3 class="mb-4">Liste des Fournisseurs</h3>

    <!-- Boutons d'actions -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <!-- Bouton Créer -->
        <button class="btn btn-primary d-flex align-items-center gap-2" id="addFournisseurBtn" data-bs-toggle="modal" data-bs-target="#fournisseurModaladd">
            <i class="bi bi-plus-circle"></i> Créer
        </button>

        <!-- Bouton Importer -->
        <button class="btn btn-secondary d-flex align-items-center gap-2" id="importFournisseurBtn" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-arrow-up"></i> Importer
        </button>

        <!-- Bouton Exporter en Excel -->
        <a href="{{ url('/export-fournisseurs-excel') }}" class="btn btn- d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-excel"></i> Exporter en Excel
        </a>

        <!-- Bouton Exporter en PDF -->
        <a href="{{ url('/export-fournisseurs-pdf') }}" class="btn btn d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-pdf"></i> Exporter en PDF
        </a>
    </div>


<!-- Bootstrap Icons -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>




    <div id="fournisseur-table"></div>

</div>


<!-- Formulaire d'importation Excel -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> <!-- Ajout de la classe modal-lg ici -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Importation des Fournisseurs</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importForm" action="{{ route('fournisseurs.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">
                    
                    <div class="form-group">
                        <label for="file">Fichier Excel</label>
                        <input type="file" class="form-control form-control-lg shadow-sm" name="file" id="file" required>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="colonne_compte">Colonne Compte</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_compte" id="colonne_compte" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="colonne_intitule">Colonne Intitulé</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_intitule" id="colonne_intitule" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="colonne_identifiant_fiscal">Colonne Identifiant Fiscal</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_identifiant_fiscal" id="colonne_identifiant_fiscal" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="colonne_ICE">Colonne ICE</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_ICE" id="colonne_ICE" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="colonne_nature_operation">Colonne Nature d'Opération</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_nature_operation" id="colonne_nature_operation" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="colonne_rubrique_tva">Colonne Rubrique TVA</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_rubrique_tva" id="colonne_rubrique_tva" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="colonne_designation">Colonne Désignation</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_designation" id="colonne_designation" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="colonne_contre_partie">Colonne Contre Partie</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_contre_partie" id="colonne_contre_partie" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Importer</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal add-->
<div class="modal fade" id="fournisseurModaladd" tabindex="-1" role="dialog" aria-labelledby="fournisseurModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fournisseurModalLabel">Créer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="fournisseurFormAdd">
                    @csrf 
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="compte">Compte</label>
                                <input type="hidden" id="societe_id" name="societe_id" value="{{ session('societeId') }}"> <!-- Societe ID ici -->
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-lg shadow-sm" id="compte" name="compte" placeholder="4411XXXX" required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-secondary" id="autoIncrementBtn">Auto</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="intitule">Intitulé</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="intitule" name="intitule">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="identifiant_fiscal">Identifiant Fiscal</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="identifiant_fiscal" name="identifiant_fiscal" maxlength="8" pattern="\d*">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ICE">ICE</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="ICE" name="ICE" maxlength="15" pattern="\d*">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nature_operation">Nature de l'opération</label>
                                <select class="form-select form-select-lg shadow-sm" id="nature_operation" name="nature_operation">
                                    <option value="">Sélectionner une option</option>
                                    <option value="1-Achat de biens d'équipement">1-Achat de biens d'équipement</option>
                                    <option value="2-Achat de travaux">2-Achat de travaux</option>
                                    <option value="3-Achat de services">3-Achat de services</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contre_partie" class="mr-2">Contre Partie</label>
                                <div class="position-relative w-100">
                                    <select class="form-control form-control-lg shadow-sm select2" id="contre_partie" name="contre_partie" required>
                                        <option value="">Sélectionner une contre partie</option>
                                    </select>
                                    <button type="button" class="btn btn-link position-absolute" style="top:90%; right: 10px; transform: translateY(-20%);" data-toggle="modal" data-target="#planComptableModalAdd">
                                        Nouveau compte  <i class="bi bi-plus-circle" style="font-size: 1.3rem;"></i> 
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rubrique_tva">Rubrique TVA</label>
                                <select class="form-select form-select-lg shadow-sm" id="rubrique_tva" name="rubrique_tva">
                                    <option value="" selected>Sélectionnez une Rubrique</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="designation">Désignation</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="designation" name="designation" placeholder="Designation">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary mr-2" id="resetModal"> <i class="bi bi-arrow-clockwise fs-6"></i> <!-- Icône de réinitialisation --></button>
                        <button type="submit" class="btn btn-primary ml-2">Valider<i class="bi bi-check-lg  bi-2x"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal edit-->
<div class="modal fade" id="fournisseurModaledit" tabindex="-1" role="dialog" aria-labelledby="fournisseurModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fournisseurModalLabel">Modifier</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="fournisseurFormEdit">
                    <input type="hidden" id="editFournisseurId" value="">
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
                                <select class="form-select form-select-lg shadow-sm" id="editContrePartie">
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
                        <button type="button" class="btn btn-secondary mr-2" id="resetModal"> <i class="bi bi-arrow-clockwise fs-6"></i> <!-- Icône de réinitialisation --></button>
                        <button type="submit" class="btn btn-primary ml-2">Valider<i class="bi bi-check-lg  bi-2x"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un plan comptable-->
<div class="modal fade" id="planComptableModalAdd" tabindex="-1" role="dialog" aria-labelledby="planComptableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planComptableModalLabel">Ajouter un compte </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="planComptableFormAdd">
                    <div class="form-group">
                        <label for="compte">compte</label>
                        <input type="text" class="form-control form-control-lg shadow-sm" id="compte_add" name="compte" required>
                    </div>
                    <div class="form-group">
                        <label for="intitule">Intitulé</label>
                        <input type="text" class="form-control form-control-lg shadow-sm" id="intitule_add" name="intitule" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>

 <script >

// Générer automatiquement un numéro de compte
document.getElementById("autoIncrementBtn").addEventListener("click", function () {
    const societeId = document.getElementById("societe_id").value;

    fetch(`/get-next-compte/${societeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.next_compte) {
                document.getElementById("compte").value = data.next_compte;
            } else {
                alert("Erreur lors de la génération du compte : " + (data.error || "inconnue"));
            }
        })
        .catch(error => console.error("Erreur :", error));
});


document.getElementById("compte").addEventListener("input", function() {
    const compteField = document.getElementById("compte");
    if (!compteField.value.startsWith("4411")) {
        compteField.setCustomValidity("Le compte doit commencer par 4411");
    } else {
        compteField.setCustomValidity("");
    }
});

// Gérer le déplacement automatique du focus lors de l'appui sur "Entrée"
document.getElementById("fournisseurFormAdd").addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault(); // Empêche le formulaire de se soumettre à la pression de la touche "Entrée"
        
        // Récupère tous les champs du formulaire
        const inputs = Array.from(this.querySelectorAll("input, select,select2"));
        
        // Trouve l'index de l'élément actuellement focus
        const currentIndex = inputs.indexOf(document.activeElement);
        
        // Si un élément suivant existe, place le focus dessus
        if (currentIndex > -1 && currentIndex < inputs.length - 1) {
            inputs[currentIndex + 1].focus();
        }
    }
});


// Validation pour le champ ICE
$("#ICE").on("input", function() {
  // Remplacer le contenu du champ par uniquement les chiffres
  this.value = this.value.replace(/[^0-9]/g, '');

  // Limiter la longueur à 15 caractères
  if (this.value.length > 15) {
      this.value = this.value.slice(0, 15);
  }
});

// Validation pour le champ identifiant_fiscal
$("#identifiant_fiscal").on("input", function() {
  // Remplacer le contenu du champ par uniquement les chiffres
  this.value = this.value.replace(/[^0-9]/g, '');

  // Limiter la longueur à 15 caractères
  if (this.value.length > 15) {
      this.value = this.value.slice(0, 15);
  }
});

var table = new Tabulator("#fournisseur-table", {
    ajaxURL: "/fournisseurs/data", // URL pour récupérer les données
    layout: "fitColumns",
    height: "800px", // Hauteur du tableau
    selectable: true, // Permet de sélectionner les lignes
    rowSelection: true,
    initialSort: [ // Tri initial par colonne 'Compte'
        { column: "compte", dir: "asc" }
    ],
    columns: [
        {
            title: ` 
                <i class="fas fa-check-square" id="selectAllIcon" title="Sélectionner tout" style="cursor: pointer;"></i> 
                <i class="fas fa-trash-alt " id="deleteAllIcon" title="Supprimer toutes les lignes sélectionnées" style="cursor: pointer;"></i>
            `,
            field: "select",
            formatter: "rowSelection", // Active la sélection de ligne
            headerSort: false,
            hozAlign: "center",
            width: 60, // Fixe la largeur de la colonne de sélection
            cellClick: function(e, cell) {
                cell.getRow().toggleSelect();  // Basculer la sélection de ligne
            }
        },
        {title: "Compte", field: "compte", editor: "input", headerFilter: "input"},
        {title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input"},
        {title: "Identifiant Fiscal", field: "identifiant_fiscal", editor: "input", headerFilter: "input"},
        {title: "ICE", field: "ICE", editor: "input", headerFilter: "input"},
        {title: "Nature de l'opération", field: "nature_operation", editor: "input", headerFilter: "input"},
        {title: "Rubrique TVA", field: "rubrique_tva", editor: "input", headerFilter: "input"},
        {title: "Désignation", field: "designation", editor: "input", headerFilter: "input"},
        {title: "Contre Partie", field: "contre_partie", editor: "input", headerFilter: "input"},
        {
            title: "Actions",
            field: "action-icons",
            formatter: function() {
                return `
                    <i class='fas fa-edit text-primary edit-icon' style='font-size: 0.9em; cursor: pointer;'></i>
                    <i class='fas fa-trash-alt text-danger delete-icon' style='font-size: 0.9em; cursor: pointer;'></i>
                `;
            },
            cellClick: function(e, cell) {
    var row = cell.getRow();
    
    // Vérifier quel élément a été cliqué
    if (e.target.classList.contains('row-select-checkbox')) {
        // Synchronise la sélection de la ligne avec l'état de la checkbox
        if (e.target.checked) {
            row.select();
        } else {
            row.deselect();
        }
    } else if (e.target.classList.contains('edit-icon')) {
        var rowData = cell.getRow().getData();

        // Ouvrir directement le modal de modification
        editFournisseur(rowData); // Appel à votre fonction de modification
    } else if (e.target.classList.contains('delete-icon')) {
        var rowData = cell.getRow().getData();
        deleteFournisseur(rowData.id);
    }
},
hozAlign: "center",
headerSort: false

        }
    ],
   
});

// Fonction pour supprimer les lignes sélectionnées côté serveur
function deleteSelectedRows() {
    var selectedRows = table.getSelectedRows(); // Récupère les lignes sélectionnées
    var idsToDelete = selectedRows.map(function(row) {
        return row.getData().id; // Récupère les IDs des lignes sélectionnées
    });

    // Envoie les IDs au serveur pour suppression
    if (idsToDelete.length > 0) {
        fetch("/fournisseurs/delete-selected", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids: idsToDelete })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Affiche un message de succès
            table.deleteRow(selectedRows); // Supprime les lignes du tableau côté client
        })
        .catch(error => {
            console.error('Erreur de suppression:', error);
            alert('Erreur lors de la suppression des lignes.');
        });
    }
}

// Gestionnaire d'événements pour sélectionner/désélectionner toutes les lignes et supprimer les lignes sélectionnées
document.getElementById("fournisseur-table").addEventListener("click", function(e) {
    if (e.target.id === "selectAllIcon") {
        if (table.getSelectedRows().length === table.getRows().length) {
            table.deselectRow(); // Désélectionner toutes les lignes
        } else {
            table.selectRow(); // Sélectionner toutes les lignes
        }
    }
    if (e.target.id === "deleteAllIcon") {
        deleteSelectedRows(); // Appelle la fonction de suppression pour les lignes sélectionnées
    }
});



var designationValue = ''; // Variable globale pour stocker l'intitulé

// Fonction pour remplir les rubriques TVA
function remplirRubriquesTva(selectId, selectedValue = null) {
    $.ajax({
        url: '/rubriques-tva?type=Achat',
        type: 'GET',
        success: function(data) {
            var select = $("#" + selectId);
            
            // Détruire Select2 s'il est déjà initialisé
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2("destroy");
            }
            
            select.empty();
              // Ajouter l'option vide
              select.append(new Option("Sélectionnez une Rubrique", ""));

            let categoriesArray = [];
            $.each(data.rubriques, function(categorie, rubriques) {
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

            $.each(categoriesArray, function(index, categoryObj) {
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

                categoryObj.rubriques.forEach(function(rubrique) {
                    if (!excludedNumRacines.includes(rubrique.Num_racines)) {
                        let searchText = `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.mainCategory}`;
                        let option = new Option(`${rubrique.Num_racines}: ${rubrique.Nom_racines} : ${Math.round(rubrique.Taux)}%`, rubrique.Num_racines);
                        option.setAttribute('data-search-text', searchText);
                        option.setAttribute('data-nom-racine', rubrique.Nom_racines);
                        select.append(option);
                    }
                });
            });

            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true,
                templateResult: function(data) {
                    if (!data.id) return data.text;
                    if ($(data.element).hasClass('category')) {
                        return $('<span style="font-weight: bold;">' + data.text + '</span>');
                    } else if ($(data.element).hasClass('subcategory')) {
                        return $('<span style="font-weight: bold; padding-left: 10px;">' + data.text + '</span>');
                    }
                    return $('<span>' + data.text + '</span>');
                },
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') return data;
                    var searchText = $(data.element).data('search-text');
                    return searchText && searchText.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
                }
            });

            select.on("select2:open", function() {
                setTimeout(function() {
                    $('.select2-search__field').focus();
                }, 10);
            });

            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des rubriques :', textStatus, errorThrown);
        }
    });
}

// Gestion de la soumission du formulaire d'ajout
$("#fournisseurFormAdd").on("submit", function (e) {
        e.preventDefault(); // Empêche la soumission par défaut du formulaire

        // Appeler la fonction pour envoyer les données
        envoyerDonnees();
    });

    // Fonction pour envoyer les données via AJAX
    function envoyerDonnees() {
        var url = "/fournisseurs"; // URL pour l'ajout

        $.ajax({
            url: url,
            type: "POST",
            data: {
                compte: $("#compte").val(),
                intitule: $("#intitule").val(),
                identifiant_fiscal: $("#identifiant_fiscal").val(),
                ICE: $("#ICE").val(),
                nature_operation: $("#nature_operation").val(),
                rubrique_tva: $("#rubrique_tva").val(),
                designation: $("#designation").val(),
                contre_partie: $("#contre_partie").val(),
                societe_id: $("#societe_id").val(),
                _token: '{{ csrf_token() }}' // Assurez-vous d'inclure votre CSRF token
            },
            success: function (response) {
                if (response.success) {
                    table.setData("/fournisseurs/data"); // Recharger les données
                    $("#fournisseurModaladd").modal("hide"); // Fermer le modal
                    $("#fournisseurFormAdd")[0].reset(); // Réinitialiser le formulaire d'ajout
                } else if (response.error) {
                    alert(response.error); // Afficher le message d'erreur du serveur
                }
            },
            error: function (xhr) {
                console.error("Erreur lors de l'enregistrement des données :", xhr.responseText);
                alert("Erreur lors de l'enregistrement des données !");
            }
        });
    }
// Réinitialiser le formulaire à la fermeture
$('#resetModal').on('click', function () {
    $('#fournisseurFormAdd')[0].reset(); // Réinitialiser le formulaire

    // Réinitialiser les champs select2
    $('#fournisseurFormAdd select').each(function () {
        $(this).val('').trigger('change'); // Réinitialise les champs select2
    });
});

// Appel pour remplir les options de contrepartie lors du chargement
$(document).ready(function () {
    remplirContrePartie('contre_partie');
    $('.select2').select2(); // Initialisation de select2
    $('#fournisseurModaladd').on('shown.bs.modal', function () {
        remplirRubriquesTva('rubrique_tva');
        $('#compte').focus();
        $('#rubrique_tva').val('').trigger('change');
    });
});

// Fonction pour remplir les options de contrepartie
function remplirContrePartie(selectId, selectedValue = null, callback = null) {
    $.ajax({
        url: '/comptes', // La route pour récupérer les comptes
        type: 'GET',
        success: function(data) {
            var select = $("#" + selectId);

            // Détruire Select2 s'il est déjà initialisé
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2("destroy");
            }

            // Vider le sélecteur et ajouter les nouvelles options
            select.empty(); 

              // Ajouter l'option vide
              select.append(new Option("Sélectionnez une contre partie", ""));


            // Tri des comptes par numéro
            data.sort((a, b) => a.compte.localeCompare(b.compte));

            // Ajout des comptes au sélecteur
            data.forEach(function(compte) {
                let option = new Option(`${compte.compte} - ${compte.intitule}`, compte.compte);
                select.append(option);
            });

            // Réinitialiser Select2 avec les paramètres de configuration
            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true
            });

            // Mettre le focus sur la recherche lorsqu'on ouvre le sélecteur
            select.on("select2:open", function() {
                setTimeout(function() {
                    $('.select2-search__field').focus();
                }, 10);
            });

            // Sélectionner la valeur si fournie
            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }

            // Gestionnaire d'événement pour la sélection d'un compte
            select.on('select2:select', function(e) {
                var data = e.params.data;
                designationValue = data.text.split(' - ')[1]; // Mettre à jour la variable globale
                console.log("Intitulé sélectionné (sans l'afficher dans le champ) :", designationValue);
            });

            // Appeler le callback si fourni
            if (callback) callback();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des comptes :', textStatus, errorThrown);
        }
    });
}


 // Fonction pour obtenir le prochain compte
 function getNextCompte() {
        $.ajax({
            url: '/get-next-compte', // Route vers votre contrôleur
            type: 'GET',
            success: function(data) {
                $('#compte').val(data.compte); // Remplir le champ compte
                $('#intitule').focus(); // Mettre le focus sur le champ intitule
            },
            error: function() {
                alert('Erreur lors de la récupération du compte.');
            }
        });
    }

    $("#fournisseurFormEdit").on("submit", function(e) {
    e.preventDefault(); // Empêche la soumission par défaut du formulaire

    var fournisseurId = $("#editFournisseurId").val();
    var url = "/fournisseurs/" + fournisseurId; // URL pour la modification

  
    // Vérifier si le champ editDesignation est vide
    if ($("#editDesignation").val().trim() === '') {
        // Remplir editDesignation avec le designationValue
        $("#editDesignation").val(designationValue.trim());
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
            rubrique_tva: $("#editRubriqueTVA").val(), // Inclure la valeur sélectionnée dans les données
            designation: $("#editDesignation").val(), // Utiliser la designation remplie
            contre_partie: $("#editContrePartie").val(),
           // societe_id: $("#societe_id").val(), // Ajout de l'ID de la société ici
            _token: '{{ csrf_token() }}' // Assurez-vous que le token CSRF est inclus
        },
        success: function(response) {
            table.setData("/fournisseurs/data"); // Recharger les données
            $("#fournisseurModaledit").modal("hide");
            $("#fournisseurFormEdit")[0].reset(); // Réinitialiser le formulaire de modification
            $("#editFournisseurId").val(""); // Réinitialiser l'ID
            // Remplir de nouveau les rubriques TVA pour le prochain affichage
            remplirRubriquesTva('rubrique_tva'); 
            remplirContrePartie('contre_partie');
        },
        error: function(xhr) {
            alert("Erreur lors de l'enregistrement des données !");
        }
    });
});



// Fonction pour remplir le formulaire pour la modification
function editFournisseur(data) {
    $("#editFournisseurId").val(data.id);
    $("#editCompte").val(data.compte);
    $("#editIntitule").val(data.intitule);
    $("#editIdentifiantFiscal").val(data.identifiant_fiscal);
    $("#editICE").val(data.ICE);
    $("#editNatureOperation").val(data.nature_operation);
    
    remplirRubriquesTva('rubrique_tva'); 
    remplirContrePartie('contre_partie');
    
    // Remplir la liste déroulante de rubrique TVA avec la valeur actuelle
    remplirRubriquesTva("editRubriqueTVA", data.rubrique_tva);

    $("#editDesignation").val(data.designation);
    $("#editContrePartie").val(data.contre_partie);
    remplirContrePartie("editContrePartie", data.contre_partie);
    
    $("#fournisseurModaledit").modal("show");
}

// excel

    document.getElementById('file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const reader = new FileReader();

        reader.onload = function(event) {
            const data = new Uint8Array(event.target.result);
            const workbook = XLSX.read(data, { type: 'array' });

            const sheetName = workbook.SheetNames[0]; // Prendre la première feuille
            const worksheet = workbook.Sheets[sheetName];
            const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1 }); // Lire toutes les lignes

            if (rows.length > 1) {
                // Remplir les options avec les en-têtes de colonnes
                const headers = rows[0]; // Utiliser la première ligne comme en-têtes
                const compteSelect = document.querySelector('select[name="colonne_compte"]');
                const intituleSelect = document.querySelector('select[name="colonne_intitule"]');
                const identifiantFiscalSelect = document.querySelector('select[name="colonne_identifiant_fiscal"]');
                const ICESelect = document.querySelector('select[name="colonne_ICE"]');
                const natureOperationSelect = document.querySelector('select[name="colonne_nature_operation"]');
                const rubriqueTvaSelect = document.querySelector('select[name="colonne_rubrique_tva"]');
                const designationSelect = document.querySelector('select[name="colonne_designation"]');
                const contrePartieSelect = document.querySelector('select[name="colonne_contre_partie"]');

                // Réinitialiser les listes de sélection
                compteSelect.innerHTML = '';
                intituleSelect.innerHTML = '';
                identifiantFiscalSelect.innerHTML = '';
                ICESelect.innerHTML = '';
                natureOperationSelect.innerHTML = '';
                rubriqueTvaSelect.innerHTML = '';
                designationSelect.innerHTML = '';
                contrePartieSelect.innerHTML = '';

                // Afficher les colonnes disponibles dans les champs de sélection
                for (let i = 0; i < headers.length; i++) {
                    const option = new Option(headers[i], i + 1); // Les indices de colonnes sont à partir de 1
                    compteSelect.add(option.cloneNode(true));
                    intituleSelect.add(option.cloneNode(true));
                    identifiantFiscalSelect.add(option.cloneNode(true));
                    ICESelect.add(option.cloneNode(true));
                    natureOperationSelect.add(option.cloneNode(true));
                    rubriqueTvaSelect.add(option.cloneNode(true));
                    designationSelect.add(option.cloneNode(true));
                    contrePartieSelect.add(option.cloneNode(true));
                }
            }
        };

        reader.readAsArrayBuffer(file);
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



  // Gestion de la soumission du formulaire d'ajout de contrepartie
$("#planComptableFormAdd").on("submit", function(e) {
    e.preventDefault(); // Empêche la soumission par défaut du formulaire

    // Récupérer les valeurs des champs
    var compte = $("#compte_add").val();
    var intitule = $("#intitule_add").val();
    var designation = $("#designation_add").val(); // Champ designation
    var societeId = $("#societe_id").val();

    // Vérifier si le champ designation est vide
    if (!designation) {
        // Extraire l'intitulé de l'option sélectionnée dans le select 'contre_partie'
        var optionText = $("#contre_partie option:selected").text();
        var intituleFromOption = optionText.split(' - ')[1]; // Supposant le format "compte - intitulé"
        designation = intituleFromOption || ''; // Mettre l'intitulé ou une chaîne vide
        $("#designation_add").val(designation); // Remplir le champ designation
    }

    // Validation simple avant l'envoi
    if (!compte || !intitule) {
        alert("Veuillez remplir tous les champs requis avant de soumettre !");
        return;
    }

    // Requête AJAX pour ajouter une contrepartie
    $.ajax({
        url: "{{ route('ajouterContrePartie') }}", // Route Laravel pour ajouter une contrepartie
        type: "POST",
        data: {
            compte: compte,
            intitule: intitule,
            designation: designation, // Envoyer le champ designation (mis à jour si vide)
            societe_id: societeId, // ID de la société
            _token: '{{ csrf_token() }}' // CSRF token pour sécuriser la requête
        },
        success: function(response) {
            if (response.contre_partie) {
                // Ajouter la contrepartie nouvellement créée dans le sélecteur 'contre_partie'
                var newOption = new Option(
                    `${response.contre_partie.compte} - ${response.contre_partie.intitule}`, 
                    response.contre_partie.compte, 
                    true, 
                    true
                );
                $('#contre_partie').append(newOption).trigger('change'); // Met à jour le select avec Select2

                // Fermer le modal
                $("#planComptableModalAdd").modal("hide");

                // Réinitialiser le formulaire
                $("#planComptableFormAdd")[0].reset();

                // Afficher une notification de succès
                alert("Contrepartie ajoutée avec succès !");
            } else {
                alert("Erreur : la contrepartie n'a pas pu être ajoutée.");
            }
        },
        error: function(xhr) {
            console.error("Erreur lors de l'ajout de la contrepartie :", xhr.responseText);
            alert("Erreur lors de l'ajout de la contrepartie. Veuillez réessayer.");
        }
    });
});

// Réinitialiser le formulaire à la fermeture du modal
$("#planComptableModalAdd").on("hidden.bs.modal", function() {
    $("#planComptableFormAdd")[0].reset(); // Réinitialise les champs du formulaire
    $('#planComptableFormAdd select').val('').trigger('change'); // Réinitialise les sélecteurs Select2
});


$(document).ready(function() {
    // Initialiser Select2
    $('#contre_partie').select2({
        // Personnalisation de l'apparence des options dans le dropdown
        templateResult: function(data) {
            // Ajouter une icône à l'option "Ajouter un compte"
            if (data.id === 'ajouter_compte') {
                return $('<span><i class="bi bi-plus-circle"></i> Ajouter un compte</span>');
            }
            return data.text;  // Retourner le texte de l'option par défaut
        }
    });

    // Ajouter l'option "Ajouter un compte" au select2
    var addOption = new Option('<i class="bi bi-plus-circle"></i> Ajouter un compte', 'ajouter_compte', false, false);
    $('#contre_partie').append(addOption).trigger('change');

    // Écouter l'événement de sélection dans le select2
    $('#contre_partie').on('select2:select', function(e) {
        var selectedValue = $(this).val();

        // Si l'utilisateur sélectionne "Ajouter un compte"
        if (selectedValue === 'ajouter_compte') {
            // Ouvrir le modal pour ajouter un compte
            $('#planComptableModalAdd').modal('show');
        }
    });
});




</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>

</body>
</html>

@endsection
