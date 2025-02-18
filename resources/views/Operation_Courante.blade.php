
@extends('layouts.user_type.auth')

@section('content')

    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="societe_id" content="{{ session('societeId') }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opérations Courantes</title>
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


     <link href="https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">
    {{-- <link rel="stylesheet" href="https://unpkg.com/tabulator-tables@6.3.4/dist/css/tabulator.min.css"> --}}

     <!-- jQuery et Luxon -->
<!-- Luxon -->
<script src="https://cdn.jsdelivr.net/npm/luxon@3.1.0/build/global/luxon.min.js"></script>

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script type="text/javascript" src="https://oss.sheetjs.com/sheetjs/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
{{-- <script src="https://unpkg.com/tabulator-tables@6.3.4/dist/js/tabulator.min.js"></script> --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>



.select2-container--open {
  z-index: 10700 !important; /* Ajustez la valeur si besoin */
}

         .custom-datalist-editor-container {
      position: relative;
      width: 100%;
      display: flex;
      flex-direction: column-reverse;
    }
    /* Le conteneur principal de l'éditeur est en position relative */
    .custom-list-editor-container {
      position: relative;
    }
    /* Le conteneur de la liste est en position absolue et placé au-dessus de l'input */
    .list-container {
      position: absolute;
      bottom: 100%;  /* Affiche la liste au-dessus de l'input */
      left: 0;
      width: 100%;
      background-color: #fff;
      border: 1px solid #ccc;
      max-height: 150px;
      overflow-y: auto;
      z-index: 1000;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .list-container li {
      padding: 5px;
      cursor: pointer;
      list-style: none;
    }
    .list-container li:hover {
      background-color: #ddd;
    }
        /* Style personnalisé pour réduire la taille de l'alerte */
.small-alert-popup {
    width: 200px; /* Réduit la largeur de l'alerte */
    padding: 10px; /* Réduit le padding */
}

.small-alert-title {
    font-size: 13px; /* Taille plus petite pour le titre */
}

.small-alert-content {
    font-size: 13px; /* Taille plus petite pour le contenu */
}
.custom-cell-style {
    background-color: lightblue;
}


        .tabs {
            display: flex;
            border-bottom: 1px solid #ccc;
        }
        .tab {
            padding: 8px 15px;
            cursor: pointer;
            border: 1px solid #ccc;
            border-bottom: none;
            background-color: #f9f9f9;
        }
        .tab.active {
            background-color: #fff;
            border-top: 2px solid #007bff;
        }
        .tab-content {
            display: none;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #fff;
        }
        .tab-content.active {
            display: block;
        }
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            max-width: 100%;
            margin-bottom: 10px;
        }
        .filter-container label, .filter-container input, .filter-container select {
            font-size: 10px;
        }
        .filter-container input, .filter-container select {
            padding: 4px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .radio-container, .checkbox-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .radio-container label, .checkbox-container label {
            font-size: 10px;
        }
        .import-button, .export-button {
            padding: 6px 12px;
            font-size: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
        }
        .import-button {
            background-color: #28a745;
            color: white;
        }
        .export-button {
            background-color: #007bff;
            color: white;
        }
        #print-table:hover i {
                        color: #007bff; /* Couleur au survol */
                    }
                    /*Horizontally center header and footer*/
.tabulator-print-header, tabulator-print-footer{
    text-align:center;
}

/* Mauvais */
.element {
    left: 50px;
    top: 20px;
}

/* Bon */
.element {
    transform: translate(50px, 20px);
}
/* Suppression du style de base des boutons radio */
.formR  {
    top:2px;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    width: 14px; /* Taille réduite du bouton radio */
    height: 14px; /* Taille réduite du bouton radio */
    border-radius: 50%;
    border: 2px solid #007bff;
    background-color: #fff;
    position: relative;
    display: inline-block;
    transition: all 0.3s ease;
    cursor: pointer;
}

/* Le point central du bouton radio quand il est coché */
.formR:checked::before {
    content: "";
    position: absolute;
    top: 3px; /* Ajusté pour un meilleur centrage */
    left: 3px; /* Ajusté pour un meilleur centrage */
    width: 6px; /* Taille réduite du point */
    height: 6px; /* Taille réduite du point */
    border-radius: 50%;
    background-color: #007bff;
}

/* Changement de couleur au survol */
.formR:hover {
    border-color: #0056b3;
}

/* Changer la couleur du point central au survol */
.formR:hover:checked::before {
    background-color: #0056b3;
}

/* Lorsqu'il est coché, le cercle devient plus foncé */
.formR:checked {
    border-color: #0056b3;
}



/* Ajouter un focus pour le bouton radio lorsque sélectionné */
.formR:focus {
    outline: none;
    box-shadow: 0 0 3px rgba(0, 123, 255, 0.5); /* Légère ombre bleue lors du focus */
}

/*
.clignotant-jaune {
    animation: clignote 1s infinite;
} */
/* Effet de surbrillance clignotante */
.highlight-error {
    animation: highlight 1s infinite;
    background-color: yellow;
}

@keyframes highlight {
    0% { background-color: yellow; }
    50% { background-color: transparent; }
    100% { background-color: yellow; }
}




    </style>

<body>
        <div class="tabs" style="display: flex; gap: 5px; margin-bottom: 10px; border-bottom: 2px solid #ccc;">
            <!-- Onglet Achats -->
            <div class="tab active" data-tab="achats"
                 style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #f0f0f0; transition: background-color 0.3s, border-color 0.3s; box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);">
                Achats
            </div>

            <!-- Onglet Ventes -->
            <div class="tab" data-tab="ventes"
                 style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; transition: background-color 0.3s, border-color 0.3s;">
                Ventes
            </div>

            <!-- Onglet caisse -->
            <div class="tab" data-tab="Caisse"
                 style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; transition: background-color 0.3s, border-color 0.3s;">
                Caisse
            </div>

             <!-- Onglet banque -->
             <div class="tab" data-tab="Banque"
             style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; transition: background-color 0.3s, border-color 0.3s;">
            Banque
        </div>

            <!-- Onglet Opérations Diverses -->
            <div class="tab" data-tab="operations-diverses"
                 style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; transition: background-color 0.3s, border-color 0.3s;">
                Opérations Diverses
            </div>
        </div>

        <script>
            // Script pour gérer l'activation de l'onglet et le changement de couleur

        </script>



<!-- Onglet Achats -->
<div id="achats" class="tab-content active" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 10px; color: #333; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
    <div class="filter-container" style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">

        <!-- Code et Journal -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="journal-achats" style="font-size: 11px; font-weight: bold;">Code :</label>
            <select id="journal-achats" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;"></select>
            <input type="text" id="filter-intitule-achats" readonly placeholder="Journal" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
        </div>

        <!-- Saisie par -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="filter-achats" style="font-size: 11px; font-weight: bold;">Saisie par :</label>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-achats" id="filter-contre-partie-achats" value="contre-partie" checked>
                <label class="form-check-label" for="filter-contre-partie-achats" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Contre Partie Auto</label>
            </div>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-achats" id="filter-libre-achats" value="libre">
                <label class="form-check-label" for="filter-libre-achats" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Libre</label>
            </div>

        </div>

        <!-- Période -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="periode-achats" style="font-size: 11px; font-weight: bold;">Période :</label>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR " type="radio" name="filter-period-achats" id="filter-mois-achats" value="mois" checked>
                <label class="form-check-label" for="filter-mois-achats" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
            </div>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-period-achats" id="filter-exercice-achats" value="exercice">
                <label class="form-check-label" for="filter-exercice-achats" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
            </div>

            <select id="periode-achats" style="font-size: 10px; width: 150px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;">
                <option value="selectionner un mois">Sélectionner un mois</option>
            </select>
            <input type="text" id="annee-achats" readonly style="font-size: 10px; width: 90px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;" />
        </div>

        <!-- Boutons avec icônes -->
        <div style="display: flex; align-items: center; gap: 12px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <button class="icon-button border-0 bg-transparent" id="import-achats" title="Importer">
                <i class="fas fa-file-import text-success" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="download-xlsx" title="Exporter Excel">
                <i class="fas fa-file-excel text-primary" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="download-pdf" title="Exporter PDF">
                <i class="fas fa-file-pdf text-danger" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="delete-row-btn" title="Supprimer">
                <i class="fas fa-trash-alt text-danger" style="font-size: 14px;"></i>
            </button>
            <a id="print-table" href="#" title="Imprimer la table" class="text-dark">
                <i class="fa fa-print" style="font-size: 16px;"></i>
            </a>
        </div>
    </div>

    <!-- Table des achats -->
    <div id="table-achats" class="border rounded p-3 mt-2 bg-white shadow-sm">
        <!-- Contenu de la table -->
    </div>
</div>




<!-- Ajouter le lien vers la bibliothèque Font Awesome -->


<!-- Onglet Ventes -->
<div id="ventes" class="tab-content" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 10px; color: #333; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
    <div class="filter-container" style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">
        <!-- Code et Journal -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="journal-ventes" style="font-size: 11px; font-weight: bold;">Code :</label>
            <select id="journal-ventes" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;"></select>
            <input type="text" id="filter-intitule-ventes" readonly placeholder="Journal" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
        </div>

        <!-- Saisie par -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label style="font-size: 11px; font-weight: bold;">Saisie par :</label>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-ventes" id="filter-contre-partie-ventes" value="contre-partie" checked>
                <label class="form-check-label" for="filter-contre-partie-ventes" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Contre Partie Auto</label>
            </div>
        </div>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-ventes" id="filter-libre-ventes" value="libre">
                <label class="form-check-label" for="filter-libre-ventes" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Libre</label>
            </div>


        <!-- Période -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="filter-period-ventes" style="font-size: 11px; font-weight: bold;">Période :</label>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div class="form-check form-check-inline" style="font-size: 9px;">
                    <input class="formR" type="radio" name="filter-period-ventes" id="filter-mois-ventes" value="mois" checked>
                    <label class="form-check-label" for="filter-mois-ventes" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
                </div>
                <div class="form-check form-check-inline" style="font-size: 9px;">
                    <input class="formR" type="radio" name="filter-period-ventes" id="filter-exercice-ventes" value="exercice">
                    <label class="form-check-label" for="filter-exercice-ventes" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
                </div>
                <!-- Mois Sélection -->
                <select id="periode-ventes" style="font-size: 10px; width: 150px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="selectionner un mois">Sélectionner un mois</option>
                </select>
            </div>
            <input type="text" id="annee-ventes" readonly style="font-size: 10px; width: 90px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;" />
        </div>

        <!-- Boutons avec icônes -->
        <div style="display: flex; align-items: center; gap: 12px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <button class="icon-button border-0 bg-transparent" id="import-ventes" title="Importer">
                <i class="fas fa-file-import text-success" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="export-ventesExcel" title="Exporter Excel">
                <i class="fas fa-file-excel text-primary" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="export-ventesPDF" title="Exporter PDF">
                <i class="fas fa-file-pdf text-danger" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="delete-row-btn" title="Supprimer">
                <i class="fas fa-trash-alt text-danger" style="font-size: 14px;"></i>
            </button>
            <a id="print-tableV" href="#" title="Imprimer la table" class="text-dark">
                <i class="fa fa-print" style="font-size: 16px;"></i>
            </a>
        </div>
    </div>

    <!-- Table des ventes -->
    <div id="table-ventes" class="border rounded p-3 mt-2 bg-white shadow-sm">
        <!-- Contenu de la table -->
    </div>
</div>



<!-- Onglet caisse -->
<div id="Caisse" class="tab-content" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 10px; color: #333; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
    <div class="filter-container" style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">

        <!-- Code et Journal -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="journal-Caisse" style="font-size: 11px; font-weight: bold;">Code :</label>
            <select id="journal-Caisse" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-Caisse" readonly placeholder="Journal" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
        </div>

        <!-- Saisie par -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label style="font-size: 11px; font-weight: bold;">Saisie par :</label>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-Caisse" id="filter-contre-partie-Caisse" value="contre-partie" checked>
                <label class="form-check-label" for="filter-contre-partie-Caisse" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Contre Partie Auto</label>
            </div>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-Caisse" id="filter-libre-Caisse" value="libre">
                <label class="form-check-label" for="filter-libre-Caisse" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Libre</label>
            </div>

        </div>

        <!-- Période -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="periode-Caisse" style="font-size: 11px; font-weight: bold;">Période :</label>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-period-Caisse" id="filter-mois-Caisse" value="mois" checked>
                <label class="form-check-label" for="filter-mois-Caisse" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
            </div>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-period-Caisse" id="filter-exercice-Caisse" value="exercice">
                <label class="form-check-label" for="filter-exercice-Caisse" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
            </div>

            <select id="periode-Caisse" style="font-size: 10px; width: 150px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;">
                <option value="selectionner un mois">Sélectionner un mois</option>
            </select>
            <input type="text" id="annee-Caisse" readonly style="font-size: 10px; width: 90px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;" />
        </div>

        <!-- Boutons avec icônes -->
        <div style="display: flex; align-items: center; gap: 12px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <button class="icon-button border-0 bg-transparent" id="import-Caisse" title="Importer">
                <i class="fas fa-file-import text-success" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="export-CaisseExcel" title="Exporter Excel">
                <i class="fas fa-file-excel text-primary" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="export-CaissePDF" title="Exporter PDF">
                <i class="fas fa-file-pdf text-danger" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="delete-row-btn" title="Supprimer">
                <i class="fas fa-trash-alt text-danger" style="font-size: 14px;"></i>
            </button>
        </div>
    </div>

    <!-- Table de la caisse -->
    <div id="table-Caisse" class="border rounded p-3 mt-2 bg-white shadow-sm">
        <!-- Contenu de la table -->
    </div>
</div>


<!-- Onglet Banque -->
<div id="Banque" class="tab-content" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 10px; color: #333; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
    <div class="filter-container" style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">

        <!-- Code et Journal -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="journal-Banque" style="font-size: 11px; font-weight: bold;">Code :</label>
            <select id="journal-Banque" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-Banque" readonly placeholder="Journal" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
        </div>

        <!-- Saisie par -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label style="font-size: 11px; font-weight: bold;">Saisie par :</label>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-period-Banque" id="filter-mois-Banque" value="mois" checked>
                <label class="form-check-label" for="filter-mois-Banque" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
            </div>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-period-Banque" id="filter-exercice-Banque" value="exercice">
                <label class="form-check-label" for="filter-exercice-Banque" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
            </div>
        </div>

        <!-- Période -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="periode-Banque" style="font-size: 11px; font-weight: bold;">Période :</label>
            <select id="periode-Banque" style="font-size: 10px; width: 150px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;">
                <option value="selectionner un mois">Sélectionner un mois</option>
            </select>
            <input type="text" id="annee-Banque" readonly style="font-size: 10px; width: 90px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;" />
        </div>

        <!-- Boutons avec icônes -->
        <div style="display: flex; align-items: center; gap: 12px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <button class="icon-button border-0 bg-transparent" id="import-Banque" title="Importer">
                <i class="fas fa-file-import text-success" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="export-BanqueExcel" title="Exporter Excel">
                <i class="fas fa-file-excel text-primary" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="export-BanquePDF" title="Exporter PDF">
                <i class="fas fa-file-pdf text-danger" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="delete-row-btn" title="Supprimer">
                <i class="fas fa-trash-alt text-danger" style="font-size: 14px;"></i>
            </button>
        </div>
    </div>

    <!-- Table de la banque -->
    <div id="table-Banque" class="border rounded p-3 mt-2 bg-white shadow-sm">
        <!-- Contenu de la table -->
    </div>
</div>


<!-- Onglet Opérations Diverses -->
<div id="operations-diverses" class="tab-content" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 10px; color: #333; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
    <div class="filter-container" style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">
        <!-- Code et Journal -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="journal-operations-diverses" style="font-size: 11px; font-weight: bold;">Code :</label>
            <select id="journal-operations-diverses" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">
            <input type="text" id="filter-intitule-operations-diverses" readonly placeholder="Journal" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
        </div>

        <!-- Saisie par -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label style="font-size: 11px; font-weight: bold;">Saisie par :</label>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-period-operations-diverses" value="mois" id="filter-mois-operations-diverses" checked>
                <label class="form-check-label" for="filter-mois-operations-diverses" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
            </div>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-period-operations-diverses" value="exercice" id="filter-exercice-operations-diverses">
                <label class="form-check-label" for="filter-exercice-operations-diverses" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
            </div>
        </div>

        <!-- Période ou Année pour Opérations Diverses -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="periode-operations-diverses" style="font-size: 11px; font-weight: bold;">Période :</label>
            <select id="periode-operations-diverses" style="padding: 5px; width: 150px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;">
                <option value="selectionner un mois">Sélectionner un mois</option>
            </select>
            <input type="text" id="annee-operations-diverses" readonly style="font-size: 10px; width: 90px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;" />
        </div>

        <!-- Filtres -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">

            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-operations-diverses" value="contre-partie" id="filter-contre-partie-operations-diverses" checked>
                <label class="form-check-label" for="filter-contre-partie-operations-diverses" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Contre Partie Auto</label>
            </div>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-operations-diverses" value="libre" id="filter-libre-operations-diverses">
                <label class="form-check-label" for="filter-libre-operations-diverses" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Libre</label>
            </div>

        </div>

        <!-- Boutons avec icônes -->
        <div style="display: flex; align-items: center; gap: 12px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <button class="icon-button border-0 bg-transparent" id="import-operations-diverses" title="Importer">
                <i class="fas fa-file-import text-success" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="export-operations-diversesExcel" title="Exporter Excel">
                <i class="fas fa-file-excel text-primary" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="export-operations-diversesPDF" title="Exporter PDF">
                <i class="fas fa-file-pdf text-danger" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="delete-row-btn" title="Supprimer">
                <i class="fas fa-trash-alt text-danger" style="font-size: 14px;"></i>
            </button>
        </div>
    </div>

    <!-- Table des opérations diverses -->
    <div id="table-operations-diverses" class="border rounded p-3 mt-2 bg-white shadow-sm">
        <!-- Contenu de la table -->
    </div>
</div>


</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js"></script>
    <script>
        window.comptesClients = window.comptesClients || [];

        </script>
        <script>
            window.nombreChiffresCompte = {{ $societe->nombre_chiffre_compte }};
          </script>



    <script type="text/javascript" src="{{URL::asset('js/Operation_Courante.js')}}"></script>

@endsection
