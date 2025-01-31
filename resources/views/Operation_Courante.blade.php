

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opérations Courantes</title>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link href="https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">
     <!-- jQuery et Luxon -->
<!-- Luxon -->
<script src="https://cdn.jsdelivr.net/npm/luxon@3.1.0/build/global/luxon.min.js"></script>

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script type="text/javascript" src="https://oss.sheetjs.com/sheetjs/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
    <style>
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
    </style>
</head>
@extends('layouts.user_type.auth')

@section('content')
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
<div id="achats" class="tab-content active" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 9px; color: #333;">
    <div class="filter-container">
        <!-- Tous les filtres organisés -->
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
            <!-- Code et Journal -->
            <label for="journal-achats"  style="font-size: 8px;">Code :</label>
         <select id="journal-achats" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;"></select>

            <input type="text" id="filter-intitule-achats" readonly placeholder="Journal" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;" />

          <!-- Saisie par -->
<label style="font-size: 8px;">Saisie par :</label>
<div style="display: flex; align-items: center; gap: 5px;">
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-period-achats" value="mois" id="filter-mois-achats" checked style="margin-right: 2px; transform: scale(0.8);" /> Mois
    </label>
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-period-achats" value="exercice" id="filter-exercice-achats" style="margin-right: 2px; transform: scale(0.8);" /> Exercice entier
    </label>
</div>

<!-- Période ou Année -->
<div id="periode-container" style="display: flex; align-items: center; gap: 8px;">
    <label for="periode" style="font-size: 8px;">Période :</label>
    <select id="periode-achats" style="padding: 2px; width: 120px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;">
        <!-- Les options seront ajoutées dynamiquement par JavaScript -->
        <option value="selectionner un mois">selectionner un mois</option>
    </select>
    <label for="annee" style="font-size: 8px;"></label>

    <input type="text" id="annee-achats" readonly style="padding: 2px; width: 50px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;" />
</div>

           <!-- Filtres -->
<div style="display: flex; align-items: center; gap: 5px;">
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-achats" id="filter-libre-achats" value="libre" style="margin-right: 2px; transform: scale(0.8);" /> Libre
    </label>
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-achats" id="filter-contre-partie-achats" value="contre-partie" style="margin-right: 2px; transform: scale(0.8);" /> CP Auto
    </label>
</div>

            <!-- Boutons avec icônes -->
            <div style="display: flex; gap: 8px;">
                <button class="icon-button" id="import-achats" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-import" style="font-size: 14px; color: #28a745;" title="Importer"></i>
                </button>
                <button class="icon-button" id="download-xlsx" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-excel" style="font-size: 14px; color: #007bff;" title="Exporter Excel"></i>
                </button>
                <button class="icon-button" id="download-pdf" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-pdf" style="font-size: 14px; color: #dc3545;" title="Exporter PDF"></i>

                </button>
                <button class="icon-button" id="delete-row-btn" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-trash-alt" style="font-size: 14px; color: #dc3545;" title="Supprimer"></i>
                </button>
                <!-- Icône pour imprimer -->
                <a id="print-table" href="#" title="Imprimer la table" style="text-decoration: none;">
                    <i class="fa fa-print" style="font-size: 24px; color: #333; transition: color 0.3s;"></i>
                </a>



            </div>
        </div>
    </div>

    <!-- Table des achats -->
    {{-- <button id="save-button" class="btn btn-primary mt-3">Enregistrer les lignes</button> --}}

    <div id="table-achats" style="border: 1px solid #ddd; padding: 8px; border-radius: 5px; margin-top: 10px; background-color: #fff;">
        <!-- Contenu de la table -->
    </div>

</div>

<!-- Ajouter le lien vers la bibliothèque Font Awesome -->




<!-- Onglet Ventes -->
<div id="ventes" class="tab-content" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 9px; color: #333;">
    <div class="filter-container">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
            <!-- Code et Journal -->
            <label for="journal-ventes" style="font-weight: 600;">Code :</label>
            <select id="journal-ventes" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-ventes" readonly placeholder="Journal" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;" />

            <!-- Saisie par -->
            <label style="font-weight: 600;">Saisie par :</label>
            <div style="display: flex; align-items: center; gap: 5px;">
                <label style="display: flex; align-items: center;">
                    <input type="radio" name="filter-period-ventes" value="mois" id="filter-mois-ventes" checked style="margin-right: 2px; transform: scale(0.8);" /> Mois
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="radio" name="filter-period-ventes" value="exercice" id="filter-exercice-ventes" style="margin-right: 2px; transform: scale(0.8);" /> Exercice entier
                </label>
            </div>

            <div id="periode-container-ventes" style="display: flex; align-items: center; gap: 8px;">
                <label for="periode-ventes" style="font-size: 8px;">Période :</label>
                <select id="periode-ventes" style="padding: 2px; width: 120px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;">
                    <!-- Les options seront ajoutées dynamiquement par JavaScript -->
                </select>
                <label for="annee-ventes" style="font-size: 8px;"></label>
                <input type="text" id="annee-ventes" readonly style="padding: 2px; width: 50px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;" />
            </div>
                 <!-- Filtres -->
<div style="display: flex; align-items: center; gap: 5px;">
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-ventes" id="filter-libre-ventes" value="libre" style="margin-right: 2px; transform: scale(0.8);" /> Libre
    </label>
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-ventes" id="filter-contre-partie-ventes" value="contre-partie" style="margin-right: 2px; transform: scale(0.8);" /> CP Auto
    </label>
</div>

            <!-- Boutons avec icônes -->
            <div style="display: flex; gap: 8px;">
                <button class="icon-button" id="import-ventes" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-import" style="font-size: 14px; color: #28a745;" title="Importer"></i>
                </button>
                <button class="icon-button" id="export-ventesExcel" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-excel" style="font-size: 14px; color: #007bff;" title="Exporter Excel"></i>
                </button>
                <button class="icon-button" id="export-ventesPDF" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-pdf" style="font-size: 14px; color: #dc3545;" title="Exporter PDF"></i>
                </button>
                <button class="icon-button" id="delete-row-btn" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-trash-alt" style="font-size: 14px; color: #dc3545;" title="Supprimer"></i>
                </button>
                 <!-- Icône pour imprimer -->
                 <a id="print-tableV" href="#" title="Imprimer la table" style="text-decoration: none;">
                    <i class="fa fa-print" style="font-size: 24px; color: #333; transition: color 0.3s;"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Table des ventes -->
    <div id="table-ventes" style="border: 1px solid #ddd; padding: 8px; border-radius: 5px; margin-top: 10px; background-color: #fff;">
        <!-- Contenu de la table -->
    </div>
</div>




<!-- Onglet caisse -->
<div id="Caisse" class="tab-content" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 9px; color: #333;">
    <div class="filter-container">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
            <!-- Code et Journal -->
            <label for="journal-Caisse" style="font-weight: 600;">Code :</label>
            <select id="journal-Caisse" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-Caisse" readonly placeholder="Journal" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;" />

            <!-- Saisie par -->
            <label style="font-weight: 600;">Saisie par :</label>
            <div style="display: flex; align-items: center; gap: 5px;">
                <label style="display: flex; align-items: center;">
                    <input type="radio" name="filter-period-Caisse" value="mois" id="filter-mois-Caisse" checked style="margin-right: 2px; transform: scale(0.8);" /> Mois
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="radio" name="filter-period-Caisse" value="exercice" id="filter-exercice-Caisse" style="margin-right: 2px; transform: scale(0.8);" /> Exercice entier
                </label>
            </div>

            <!-- Période ou Année pour Trésorerie -->
<div id="periode-container-Caisse" style="display: flex; align-items: center; gap: 8px;">
    <label for="periode-Caisse" style="font-size: 8px;">Période :</label>
    <select id="periode-Caisse" style="padding: 2px; width: 120px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;">
        <!-- Les options seront ajoutées dynamiquement par JavaScript -->
    </select>
    <label for="annee-Caisse" style="font-size: 8px;"></label>
    <input type="text" id="annee-Caisse" readonly style="padding: 2px; width: 50px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;" />
</div>
                  <!-- Filtres -->
<div style="display: flex; align-items: center; gap: 5px;">
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-Caisse" id="filter-libre-Caisse" value="libre" style="margin-right: 2px; transform: scale(0.8);" /> Libre
    </label>
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-Caisse" id="filter-contre-partie-Caisse" value="contre-partie" style="margin-right: 2px; transform: scale(0.8);" /> CP Auto
    </label>
</div>
            <!-- Boutons avec icônes -->
            <div style="display: flex; gap: 8px;">
                <button class="icon-button" id="import-Caisse" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-import" style="font-size: 14px; color: #28a745;" title="Importer"></i>
                </button>
                <button class="icon-button" id="export-CaisseExcel" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-excel" style="font-size: 14px; color: #007bff;" title="Exporter Excel"></i>
                </button>
                <button class="icon-button" id="export-CaissePDF" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-pdf" style="font-size: 14px; color: #dc3545;" title="Exporter PDF"></i>
                </button>
                <button class="icon-button" id="delete-row-btn" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-trash-alt" style="font-size: 14px; color: #dc3545;" title="Supprimer"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table de la caisse -->
    <div id="table-Caisse" style="border: 1px solid #ddd; padding: 8px; border-radius: 5px; margin-top: 10px; background-color: #fff;">
        <!-- Contenu de la table -->
    </div>
</div>

<!-- Onglet Banque -->
<div id="Banque" class="tab-content" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 9px; color: #333;">
    <div class="filter-container">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
            <!-- Code et Journal -->
            <label for="journal-Banque" style="font-weight: 600;">Code :</label>
            <select id="journal-Banque" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-Banque" readonly placeholder="Journal" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;" />

            <!-- Saisie par -->
            <label style="font-weight: 600;">Saisie par :</label>
            <div style="display: flex; align-items: center; gap: 5px;">
                <label style="display: flex; align-items: center;">
                    <input type="radio" name="filter-period-Banque" value="mois" id="filter-mois-Banque" checked style="margin-right: 2px; transform: scale(0.8);" /> Mois
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="radio" name="filter-period-Banque" value="exercice" id="filter-exercice-Banque" style="margin-right: 2px; transform: scale(0.8);" /> Exercice entier
                </label>
            </div>

            <!-- Période ou Année pour banque -->
<div id="periode-container-Banque" style="display: flex; align-items: center; gap: 8px;">
    <label for="periode-Banque" style="font-size: 8px;">Période :</label>
    <select id="periode-Banque" style="padding: 2px; width: 120px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;">
        <!-- Les options seront ajoutées dynamiquement par JavaScript -->
    </select>
    <label for="annee-Banque" style="font-size: 8px;"></label>
    <input type="text" id="annee-Banque" readonly style="padding: 2px; width: 50px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;" />
</div>
                  <!-- Filtres -->
<div style="display: flex; align-items: center; gap: 5px;">
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-Banque" id="filter-libre-Banque" value="libre" style="margin-right: 2px; transform: scale(0.8);" /> Libre
    </label>
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-Banque" id="filter-contre-partie-Banque" value="contre-partie" style="margin-right: 2px; transform: scale(0.8);" /> CP Auto
    </label>
</div>
            <!-- Boutons avec icônes -->
            <div style="display: flex; gap: 8px;">
                <button class="icon-button" id="import-Banque" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-import" style="font-size: 14px; color: #28a745;" title="Importer"></i>
                </button>
                <button class="icon-button" id="export-BanqueExcel" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-excel" style="font-size: 14px; color: #007bff;" title="Exporter Excel"></i>
                </button>
                <button class="icon-button" id="export-BanquePDF" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-pdf" style="font-size: 14px; color: #dc3545;" title="Exporter PDF"></i>
                </button>
                <button class="icon-button" id="delete-row-btn" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-trash-alt" style="font-size: 14px; color: #dc3545;" title="Supprimer"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table de la banque -->
    <div id="table-Banque" style="border: 1px solid #ddd; padding: 8px; border-radius: 5px; margin-top: 10px; background-color: #fff;">
        <!-- Contenu de la table -->
    </div>
</div>

<!-- Onglet Opérations Diverses -->
<div id="operations-diverses" class="tab-content" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 9px; color: #333;">
    <div class="filter-container">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
            <!-- Code et Journal -->
            <label for="journal-operations-diverses" style="font-weight: 600;">Code :</label>
            <select id="journal-operations-diverses" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-operations-diverses" readonly placeholder="Journal" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;" />

            <!-- Saisie par -->
            <label style="font-weight: 600;">Saisie par :</label>
            <div style="display: flex; align-items: center; gap: 5px;">
                <label style="display: flex; align-items: center;">
                    <input type="radio" name="filter-period-operations-diverses" value="mois" id="filter-mois-operations-diverses" checked style="margin-right: 2px; transform: scale(0.8);" /> Mois
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="radio" name="filter-period-operations-diverses" value="exercice" id="filter-exercice-operations-diverses" style="margin-right: 2px; transform: scale(0.8);" /> Exercice entier
                </label>
            </div>

            <!-- Période ou Année pour Opérations Diverses -->
<div id="periode-container-operations" style="display: flex; align-items: center; gap: 8px;">
    <label for="periode-operations-diverses" style="font-size: 8px;">Période Opérations Diverses :</label>
    <select id="periode-operations-diverses" style="padding: 2px; width: 120px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;">
        <!-- Les options seront ajoutées dynamiquement par JavaScript -->
    </select>
    <label for="annee-operations" style="font-size: 8px;"></label>
    <input type="text" id="annee-operations-diverses" readonly style="padding: 2px; width: 50px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;" />
</div>

                  <!-- Filtres -->
<div style="display: flex; align-items: center; gap: 5px;">
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-operations-diverses" id="filter-libre-operations-diverses" value="libre" style="margin-right: 2px; transform: scale(0.8);" /> Libre
    </label>
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-operations-diverses" id="filter-contre-partie-operations-diverses" value="contre-partie" style="margin-right: 2px; transform: scale(0.8);" /> CP Auto
    </label>
</div>

            <!-- Boutons avec icônes -->
            <div style="display: flex; gap: 8px;">
                <button class="icon-button" id="import-operations-diverses" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-import" style="font-size: 14px; color: #28a745;" title="Importer"></i>
                </button>
                <button class="icon-button" id="export-operations-diversesExcel" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-excel" style="font-size: 14px; color: #007bff;" title="Exporter Excel"></i>
                </button>
                <button class="icon-button" id="export-operations-diversesPDF" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-pdf" style="font-size: 14px; color: #dc3545;" title="Exporter PDF"></i>
                </button>
                <button class="icon-button" id="delete-row-btn" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-trash-alt" style="font-size: 14px; color: #dc3545;" title="Supprimer"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table des opérations diverses -->
    <div id="table-operations-diverses" style="border: 1px solid #ddd; padding: 8px; border-radius: 5px; margin-top: 10px; background-color: #fff;">
        <!-- Contenu de la table -->
    </div>
</div>


</body>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js"></script>
    <script>

    </script>
    <script type="text/javascript" src="{{URL::asset('js/Operation_Courante.js')}}"></script>


</html>
@endsection
