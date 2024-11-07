<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Fournisseurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tabulator-tables@5.2.4/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>
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
    height: 30px; /* Ajustez la hauteur du header */
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
    transition: background-color!important 0.1s ease; /* Transition douce pour le survol */
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


.btn-custom-gradient:hover {
    background-image: linear-gradient(to right, #536fb2, #344767)!important; /* Inverser le dégradé au survol */
}

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



<div class="container mt-5">
    <h3>Liste des Fournisseurs</h3>
    <button class="btn btn-custom-gradient" id="addFournisseurBtn" data-toggle="modal" data-target="#fournisseurModaladd">Ajouter</button>
    <button class="btn btn-custom-gradient" id="addFournisseurBtn" data-toggle="modal" data-target="#importModal">Importer</button>

    <a href="{{ url('/export-fournisseurs-excel') }}" class="btn btn-custom-gradient">Exporter en Excel</a>

<a href="{{ url('/export-fournisseurs-pdf') }}" class="btn btn-custom-gradient">Exporter en PDF</a>

    <div id="fournisseur-table"></div>

</div>


<!-- Formulaire d'importation Excel -->

<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
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
        <div class="form-group">
            <label for="file">Fichier Excel</label>
            <input type="file" class="form-control" name="file" id="file" required>
        </div>
        <div class="form-row">
            <div class="col-md-6 form-group">
                <label for="colonne_compte">Colonne Compte</label>
                <input type="number" class="form-control" name="colonne_compte" id="colonne_compte" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="colonne_intitule">Colonne Intitulé</label>
                <input type="number" class="form-control" name="colonne_intitule" id="colonne_intitule" required>
            </div>
        </div>
        <div class="form-row">
            <div class="col-md-6 form-group">
                <label for="colonne_identifiant_fiscal">Colonne Identifiant Fiscal</label>
                <input type="number" class="form-control" name="colonne_identifiant_fiscal" id="colonne_identifiant_fiscal" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="colonne_ICE">Colonne ICE</label>
                <input type="number" class="form-control" name="colonne_ICE" id="colonne_ICE" required>
            </div>
        </div>
        <div class="form-row">
            <div class="col-md-6 form-group">
                <label for="colonne_nature_operation">Colonne Nature d'Opération</label>
                <input type="number" class="form-control" name="colonne_nature_operation" id="colonne_nature_operation" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="colonne_rubrique_tva">Colonne Rubrique TVA</label>
                <input type="number" class="form-control" name="colonne_rubrique_tva" id="colonne_rubrique_tva" required>
            </div>
        </div>
        <div class="form-row">
            <div class="col-md-6 form-group">
                <label for="colonne_designation">Colonne Désignation</label>
                <input type="number" class="form-control" name="colonne_designation" id="colonne_designation" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="colonne_contre_partie">Colonne Contre Partie</label>
                <input type="number" class="form-control" name="colonne_contre_partie" id="colonne_contre_partie" required>
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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fournisseurModalLabel">Ajouter</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="fournisseurFormAdd">
                    @csrf <!-- Token CSRF pour la sécurité -->
                    {{-- <!-- Champ masqué pour societe_id -->
                    <input type="hidden" id="societe_id" name="societe_id" value="{{ old('societe_id', session('societe_id')) }}"> --}}
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="compte">Compte</label>
                                
                                <!-- Options pour choisir entre saisie et auto-incrémentation -->
                                <div class="input-group">
                                    <input type="text" class="form-control" id="compte" name="compte" placeholder="4411XXXX" required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-secondary" id="autoIncrementBtn">Auto</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="intitule">Intitulé</label>
                                <input type="text" class="form-control" id="intitule" name="intitule" >
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="identifiant_fiscal">Identifiant Fiscal</label>
                                <input type="text" class="form-control" id="identifiant_fiscal" name="identifiant_fiscal" maxlength="8" pattern="\d*" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ICE">ICE</label>
                                <input type="text" class="form-control" id="ICE" name="ICE" maxlength="15" pattern="\d*" >
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nature_operation">Nature de l'opération</label>
                                <select class="form-control" id="nature_operation" name="nature_operation">
                                    <option value="">Sélectionner une option</option>
                                    <option value="1-Achat de biens d'équipement">1-Achat de biens d'équipement</option>
                                    <option value="2-Achat de travaux">2-Achat de travaux</option>
                                    <option value="3-Achat de services">3-Achat de services</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contre_partie">Contre Partie</label>
                                <select class="form-control" id="contre_partie" name="contre_partie">
                                    
                                    <option value="">Sélectionnez une contre partie</option>
                                    <!-- Les options seront ajoutées dynamiquement ici -->
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rubrique_tva">Rubrique TVA</label>
                                <select class="form-control" id="rubrique_tva" name="rubrique_tva">
                                    <option value="" selected>Sélectionnez une Rubrique</option>
                                    <!-- Les options seront ajoutées par JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="designation">Désignation</label>
                                <input type="text" class="form-control" id="designation" name="designation" placeholder="Designation" >
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary mr-2" id="resetModal"> <i class="bi bi-arrow-clockwise fs-6"></i> <!-- Icône de réinitialisation --></button>
                        <button type="submit" class="btn btn-primary ml-2">Valider<i class="bi bi-check-lg  bi-2x"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal edit-->
<div class="modal fade" id="fournisseurModaledit" tabindex="-1" role="dialog" aria-labelledby="fournisseurModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
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
                                <input type="text" class="form-control" id="editCompte" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editIntitule">Intitulé</label>
                                <input type="text" class="form-control" id="editIntitule" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editIdentifiantFiscal">Identifiant Fiscal</label>
                                <input type="text" class="form-control" id="editIdentifiantFiscal" maxlength="8" pattern="\d*" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editICE">ICE</label>
                                <input type="text" class="form-control" id="editICE" maxlength="15" pattern="\d*" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editNatureOperation">Nature de l'opération</label>
                                <select class="form-control" id="editNatureOperation">
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
        <select class="form-control" id="editContrePartie" required>
            <option value="">Sélectionnez une contre partie</option>
        </select>
    </div>
</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editRubriqueTVA">Rubrique TVA</label>
                               
                                <select class="form-control" id="editRubriqueTVA" required>
                                    <option value="">Sélectionnez une Rubrique</option>
                                    <!-- Les options seront ajoutées par JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editDesignation">Désignation</label>
                                <input type="text" class="form-control" id="editDesignation"placeholder="Designation" >
                            </div>
                        </div>
                    </div>
                    <button type="submit" class=" btn btn-secondary ladda-button">Sauvegarder  <i class="bi bi-save"></i> </button>
                </form>
            </div>
        </div>
    </div>
</div>


 <script >


// Écouter le clic sur le bouton auto-incrément
document.getElementById("autoIncrementBtn").addEventListener("click", function() {
    fetchNextCompte();
});

function fetchNextCompte() {
    fetch('/get-next-compte')
        .then(response => response.json())
        .then(data => {
            const nextCompte = data.next_compte;
            document.getElementById("compte").value = nextCompte;
        })
        .catch(error => console.error('Erreur lors de la récupération du numéro de compte:', error));
}

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
    selectableRollingSelection: false, // Limite la sélection de lignes à une seule (désactiver si vous voulez plusieurs sélections)
    pagination: "local", // Pagination locale pour un chargement rapide
    paginationSize: 10, // Afficher 10 lignes par page
    paginationSizeSelector: [5, 10, 20, 50], // Options pour sélectionner la taille de la page
    responsiveLayout: "collapse", // Rend le tableau responsive en petits écrans
    initialSort: [ // Tri initial par colonne 'Compte'
        { column: "compte", dir: "asc" }
    ],
    columns: [
        {
            titleFormatter: function() {
                // Ajout de la case de sélection et de l'icône de suppression dans l'en-tête
                return `
                    <div class="d-flex align-items-center">
                        <input type="checkbox" id="selectAllCheckbox" title="Sélectionner tout" />
                        <i class="fas fa-trash-alt text-danger ml-2" id="deleteSelectedIcon" title="Supprimer les lignes sélectionnées" style="cursor: pointer;"></i>
                    </div>`;
            },
            formatter: "rowSelection", // Ajoute des cases de sélection pour chaque ligne
            hozAlign: "center",
            headerSort: false,
            width: 70,
        },
        {title: "Compte", field: "compte", editor: "input", headerFilter: "input", minWidth: 80},
        {title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input", minWidth: 120},
        {title: "Identifiant Fiscal", field: "identifiant_fiscal", editor: "input", headerFilter: "input", minWidth: 50},
        {title: "ICE", field: "ICE", editor: "input", headerFilter: "input", minWidth: 80},
        {title: "Nature de l'opération", field: "nature_operation", editor: "input", headerFilter: "input", minWidth: 50},
        {title: "Rubrique TVA", field: "rubrique_tva", editor: "input", headerFilter: "input", minWidth: 50},
        {title: "Désignation", field: "designation", editor: "input", headerFilter: "input", minWidth: 80},
        {title: "Contre Partie", field: "contre_partie", editor: "input", headerFilter: "input", minWidth: 80},
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
                if (e.target.classList.contains('edit-icon')) {
                    var rowData = cell.getRow().getData();
                    editFournisseur(rowData);
                } else if (e.target.classList.contains('delete-icon')) {
                    var rowData = cell.getRow().getData();
                    deleteFournisseur(rowData.id);
                }
            },
            minWidth: 50,
        }
    ],
    rowSelected: function(row) {
        row.getElement().classList.add("bg-light"); // Ajoute une couleur de fond à la ligne sélectionnée
    },
    rowDeselected: function(row) {
        row.getElement().classList.remove("bg-light"); // Supprime la couleur de fond si la ligne est désélectionnée
    }
});

// Fonction pour supprimer les lignes sélectionnées
function deleteSelectedRows() {
    var selectedRows = table.getSelectedRows(); // Récupère les lignes sélectionnées
    selectedRows.forEach(function(row) {
        row.delete(); // Supprime chaque ligne sélectionnée
    });
}

// Gestionnaire d'événements pour la case de sélection de toutes les lignes et l'icône de suppression
document.getElementById("fournisseur-table").addEventListener("click", function(e) {
    if (e.target.id === "selectAllCheckbox") {
        if (e.target.checked) {
            table.selectRow(); // Sélectionne toutes les lignes
        } else {
            table.deselectRow(); // Désélectionne toutes les lignes
        }
    }
    if (e.target.id === "deleteSelectedIcon") {
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
$("#fournisseurFormAdd").on("submit", function(e) {
    e.preventDefault(); // Empêche la soumission par défaut du formulaire

    // Mettre à jour le champ designation avec la valeur sélectionnée
    $("#designation").val(designationValue);

    // Appeler la fonction d'envoi des données
    envoyerDonnees(); // Ne pas passer de paramètres car designation est déjà mise à jour
});

// Fonction pour envoyer les données via AJAX
function envoyerDonnees() {
    var url = "/fournisseurs"; // URL pour l'ajout

 // Debug: Vérifiez que l'ID de la société est bien récupéré
 console.log("ID de la société :", $("#societe_id").val());

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
            designation: $("#designation").val(), // Utiliser la designation remplie
            contre_partie: $("#contre_partie").val(),
            //societe_id: $("#societe_id").val(), // Ajout de l'ID de la société ici
            _token: '{{ csrf_token() }}' // Assurez-vous d'inclure votre CSRF token
        },
        success: function(response) {
            table.setData("/fournisseurs/data"); // Recharger les données
            $("#fournisseurModaladd").modal("hide"); // Fermer le modal
            $("#fournisseurFormAdd")[0].reset(); // Réinitialiser le formulaire d'ajout
        },
        error: function(xhr) {
            console.error("Erreur lors de l'enregistrement des données :", xhr.responseText);
            alert("Erreur lors de l'enregistrement des données !");
        }
    });
}

// Réinitialiser le formulaire à la fermeture
$('#resetModal').on('click', function () {
    $('#fournisseurFormAdd')[0].reset(); // Réinitialiser le formulaire

    // Réinitialiser les champs select2
    $('#fournisseurFormAdd select').each(function() {
        $(this).val('').trigger('change'); // Réinitialise les champs select2
    });
});

// Appel pour remplir les options de contrepartie lors du chargement
$(document).ready(function() {
    remplirContrePartie('contre_partie');
    $('.select2').select2(); // Initialisation de select2
    // Remplir les rubriques TVA lors de l'ouverture du modal
    $('#fournisseurModaladd').on('shown.bs.modal', function () {
        remplirRubriquesTva('rubrique_tva'); // Remplir les rubriques TVA
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
    //$("#societe_id").val(data.societe_id); // Remplir l'ID de la société si nécessaire
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
          const headers = XLSX.utils.sheet_to_json(worksheet, { header: 1 })[0]; // Obtenir les en-têtes de colonnes

          // Remplir les sélecteurs de colonnes
          const compteSelect = document.querySelector('select[name="colonne_compte"]');
          const intituleSelect = document.querySelector('select[name="colonne_intitule"]');
          
          compteSelect.innerHTML = '';
          intituleSelect.innerHTML = '';
          
          headers.forEach((header, index) => {
              const optionCompte = new Option(header, index);
              const optionIntitule = new Option(header, index);
              compteSelect.add(optionCompte);
              intituleSelect.add(optionIntitule);
          });
      };

      reader.readAsArrayBuffer(file);
  });


  // Fonction pour supprimer un fournisseur
  function deleteFournisseur(id) {
      if (confirm("Êtes-vous sûr de vouloir supprimer ce fournisseur ?")) {
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
// Fonction pour sauvegarder les valeurs dans localStorage
function saveInputValues() {
      const inputs = [
          'colonne_compte',
          'colonne_intitule',
          'colonne_identifiant_fiscal',
          'colonne_ICE',
          'colonne_nature_operation',
          'colonne_rubrique_tva',
          'colonne_designation',
          'colonne_contre_partie',
      ];

      inputs.forEach(inputId => {
          const input = document.getElementById(inputId);
          localStorage.setItem(inputId, input.value);
          input.addEventListener('input', () => {
              localStorage.setItem(inputId, input.value);
          });
      });
  }

  // Fonction pour restaurer les valeurs de localStorage
  function restoreInputValues() {
      const inputs = [
          'colonne_compte',
          'colonne_intitule',
          'colonne_identifiant_fiscal',
          'colonne_ICE',
          'colonne_nature_operation',
          'colonne_rubrique_tva',
          'colonne_designation',
          'colonne_contre_partie',
      ];

      inputs.forEach(inputId => {
          const input = document.getElementById(inputId);
          if (localStorage.getItem(inputId)) {
              input.value = localStorage.getItem(inputId);
          }
      });
  }

  // Appeler les fonctions lors de l'ouverture du modal
  document.addEventListener('DOMContentLoaded', () => {
      restoreInputValues(); // Restaurer les valeurs lorsque la page est chargée
      saveInputValues();    // Sauvegarder les valeurs lors de la saisie
  });





</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>

</body>
</html>

@endsection
