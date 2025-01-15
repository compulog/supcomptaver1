

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

            <!-- Onglet Trésorerie -->
            <div class="tab" data-tab="tresorerie"
                 style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; transition: background-color 0.3s, border-color 0.3s;">
                Trésorerie
            </div>

            <!-- Onglet Opérations Diverses -->
            <div class="tab" data-tab="operations-diverses"
                 style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; transition: background-color 0.3s, border-color 0.3s;">
                Opérations Diverses
            </div>
        </div>

        <script>
            // Script pour gérer l'activation de l'onglet et le changement de couleur
            document.addEventListener('DOMContentLoaded', function() {
                const tabs = document.querySelectorAll('.tab');

                tabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        // Enlever la classe 'active' de tous les onglets
                        tabs.forEach(t => t.classList.remove('active'));

                        // Ajouter la classe 'active' à l'onglet cliqué
                        tab.classList.add('active');

                        // Modifier la couleur de fond des onglets
                        tabs.forEach(t => {
                            if (t.classList.contains('active')) {
                                t.style.backgroundColor = '#007bff'; // Fond bleu pour l'onglet actif
                                t.style.color = 'white'; // Texte en blanc
                                t.style.borderColor = '#0056b3'; // Bordure plus foncée pour l'onglet actif
                            } else {
                                t.style.backgroundColor = '#f9f9f9'; // Fond gris clair pour les onglets inactifs
                                t.style.color = 'black'; // Texte noir pour les onglets inactifs
                                t.style.borderColor = '#ccc'; // Bordure grise pour les onglets inactifs
                            }
                        });
                    });
                });
            });
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



<!-- Onglet Trésorerie -->
<!-- Onglet Trésorerie -->
<div id="tresorerie" class="tab-content" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 9px; color: #333;">
    <div class="filter-container">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
            <!-- Code et Journal -->
            <label for="journal-tresorerie" style="font-weight: 600;">Code :</label>
            <select id="journal-tresorerie" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-tresorerie" readonly placeholder="Journal" style="padding: 2px; width: 90px; border: 1px solid #ccc; border-radius: 3px; font-size: 9px;" />

            <!-- Saisie par -->
            <label style="font-weight: 600;">Saisie par :</label>
            <div style="display: flex; align-items: center; gap: 5px;">
                <label style="display: flex; align-items: center;">
                    <input type="radio" name="filter-period-tresorerie" value="mois" id="filter-mois-tresorerie" checked style="margin-right: 2px; transform: scale(0.8);" /> Mois
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="radio" name="filter-period-tresorerie" value="exercice" id="filter-exercice-tresorerie" style="margin-right: 2px; transform: scale(0.8);" /> Exercice entier
                </label>
            </div>

            <!-- Période ou Année pour Trésorerie -->
<div id="periode-container-tresorerie" style="display: flex; align-items: center; gap: 8px;">
    <label for="periode-tresorerie" style="font-size: 8px;">Période Trésorerie :</label>
    <select id="periode-tresorerie" style="padding: 2px; width: 120px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;">
        <!-- Les options seront ajoutées dynamiquement par JavaScript -->
    </select>
    <label for="annee-tresorerie" style="font-size: 8px;"></label>
    <input type="text" id="annee-tresorerie" readonly style="padding: 2px; width: 50px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;" />
</div>
                  <!-- Filtres -->
<div style="display: flex; align-items: center; gap: 5px;">
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-tresorerie" id="filter-libre-tresorerie" value="libre" style="margin-right: 2px; transform: scale(0.8);" /> Libre
    </label>
    <label style="display: flex; align-items: center;">
        <input type="radio" name="filter-tresorerie" id="filter-contre-partie-tresorerie" value="contre-partie" style="margin-right: 2px; transform: scale(0.8);" /> CP Auto
    </label>
</div>
            <!-- Boutons avec icônes -->
            <div style="display: flex; gap: 8px;">
                <button class="icon-button" id="import-tresorerie" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-import" style="font-size: 14px; color: #28a745;" title="Importer"></i>
                </button>
                <button class="icon-button" id="export-tresorerieExcel" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-excel" style="font-size: 14px; color: #007bff;" title="Exporter Excel"></i>
                </button>
                <button class="icon-button" id="export-tresoreriePDF" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-file-pdf" style="font-size: 14px; color: #dc3545;" title="Exporter PDF"></i>
                </button>
                <button class="icon-button" id="delete-row-btn" style="padding: 2px; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-trash-alt" style="font-size: 14px; color: #dc3545;" title="Supprimer"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table de la trésorerie -->
    <div id="table-tresorerie" style="border: 1px solid #ddd; padding: 8px; border-radius: 5px; margin-top: 10px; background-color: #fff;">
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
    <label for="periode-operations" style="font-size: 8px;">Période Opérations Diverses :</label>
    <select id="periode-operations" style="padding: 2px; width: 120px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;">
        <!-- Les options seront ajoutées dynamiquement par JavaScript -->
    </select>
    <label for="annee-operations" style="font-size: 8px;"></label>
    <input type="text" id="annee-operations" readonly style="padding: 2px; width: 50px; border: 1px solid #ccc; border-radius: 3px; font-size: 8px;" />
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
       document.addEventListener("DOMContentLoaded", function () {
    // Liste des sections
    const sections = ["achats", "ventes", "tresorerie", "operations-diverses"];

    // Fonction pour initialiser une section
    function initializeSection(section) {
        const radioMois = document.getElementById(`filter-mois-${section}`);
        const radioExercice = document.getElementById(`filter-exercice-${section}`);
        const periodeContainer = document.getElementById(`periode-${section}`);
        const anneeInput = document.getElementById(`annee-${section}`);

        // Fonction pour mettre à jour l'affichage
        function updateDisplay() {
            if (radioMois.checked) {
                periodeContainer.style.display = "inline-block";
                anneeInput.style.display = "none";
            } else if (radioExercice.checked) {
                periodeContainer.style.display = "none";
                anneeInput.style.display = "inline-block";
            }
        }

        // Ajouter des écouteurs d'événements pour les boutons radio
        radioMois.addEventListener("change", updateDisplay);
        radioExercice.addEventListener("change", updateDisplay);

        // Initialiser l'affichage au chargement
        updateDisplay();
    }

    // Initialiser toutes les sections
    sections.forEach(initializeSection);
});

        // Déclaration des tables Tabulator
var tableAch, tableVentes, tableTresorerie, tableOP;
// Liste des mois en anglais et en français
const moisAnglais = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
const moisFrancais = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

// Fonction pour charger les exercices sociaux et les périodes
function loadExerciceSocialAndPeriodes() {
    const sessionSocialRequest = $.get('/session-social');
    const periodesRequest = $.get('/periodes');

    $.when(sessionSocialRequest, periodesRequest)
        .done(function (sessionSocialResponse, periodesResponse) {
            const sessionSocialData = sessionSocialResponse[0];
            const periodesData = periodesResponse[0];

            if (!sessionSocialData || !sessionSocialData.exercice_social_debut) {
                console.error('Données de l\'exercice social invalides.');
                return;
            }

            if (!Array.isArray(periodesData) || periodesData.length === 0) {
                console.error('Les périodes reçues sont invalides ou vides.');
                return;
            }

            const anneeDebut = new Date(sessionSocialData.exercice_social_debut).getFullYear();
            $('#annee-achats').val(anneeDebut);
            $('#annee-ventes').val(anneeDebut);
            $('#annee-tresorerie').val(anneeDebut);
            $('#annee-operations-diverses').val(anneeDebut);

            // Peupler les périodes pour tous les onglets
            populateMonths('achats', periodesData);
            populateMonths('ventes', periodesData);
            populateMonths('tresorerie', periodesData);
            populateMonths('operations-diverses', periodesData);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors du chargement des données :', textStatus, errorThrown);
        });
}

// Fonction pour peupler les périodes dans le select de chaque onglet
function populateMonths(onglet, periodes) {
    const periodeSelect = $(`#periode-${onglet}`);
    const previousSelection = periodeSelect.data('selected');

    periodeSelect.empty();

    periodes.forEach(function (periode) {
        const [moisEnAnglais, annee] = periode.split(' ');

        if (moisEnAnglais && annee) {
            const moisIndex = moisAnglais.indexOf(moisEnAnglais);
            if (moisIndex !== -1) {
                const moisNom = moisFrancais[moisIndex];
                const optionValue = `${(moisIndex + 1).toString().padStart(2, '0')}-${annee}`;
                const optionText = `${moisNom} ${annee}`;

                periodeSelect.append(`<option value="${optionValue}">${optionText}</option>`);
            } else {
                console.error('Mois anglais inconnu:', moisEnAnglais);
            }
        } else {
            console.error('Format de la période incorrect:', periode);
        }
    });

    if (previousSelection) {
        periodeSelect.val(previousSelection);
    } else if (periodes.length > 0) {
        const firstOptionValue = `${('01').padStart(2, '0')}-${periodes[0].split(' ')[1]}`;
        periodeSelect.val(firstOptionValue);
        $(`#annee-${onglet}`).val(firstOptionValue.split('-')[1]);
    }

    console.log("Options ajoutées dans #" + onglet + ":", periodeSelect.html());
}

// Fonction pour mettre à jour la date dans toutes les tables Tabulator
function updateTabulatorDate(year, month) {
    const formattedDate = `${year}-${month.padStart(2, '0')}-01`;

    // Met à jour la date dans toutes les tables Tabulator
    [tableAch, tableVentes, tableTresorerie, tableOP].forEach(function(table) {
        table.updateData(table.getData().map(row => ({
            ...row,
            date: formattedDate,
        })));
    });
}

// Fonction de gestion des changements dans le select
function setupPeriodChangeHandler(onglet) {
    $(`#periode-${onglet}`).on('change', function () {
        const selectedValue = $(this).val();
        const selectedText = $(this).find("option:selected").text();

        console.log('Valeur sélectionnée pour ' + onglet + ':', selectedValue);
        console.log('Texte sélectionné pour ' + onglet + ':', selectedText);

        $(this).data('selected', selectedValue);

        if (selectedValue) {
            const selectedYear = selectedValue.split('-')[1];
            const selectedMonth = selectedValue.split('-')[0];
            $(`#annee-${onglet}`).val(selectedYear);
            updateTabulatorDate(selectedYear, selectedMonth);
        }
    });
}

// Fonction de gestion des changements dans les boutons radio
function setupFilterEventHandlers() {
    // Liste des onglets à gérer
    const onglets = ['Achats', 'Ventes', 'Tresorerie', 'Operations'];

    // Parcourir chaque onglet et mettre en place un gestionnaire pour chaque
    onglet => {
        // Sélectionner les boutons radio associés à chaque onglet
        $(`input[name="filter-period-${onglet}"]`).on('change', function () {
            if ($(this).val() === 'mois') {
                $(`#periode-${onglet}`).show();
                $(`#annee-${onglet}`).hide();
            } else if ($(this).val() === 'exercice') {
                $(`#periode-${onglet}`).hide();
                $(`#annee-${onglet}`).show();
            }
        });
    };
}

// Initialisation de la fonction
$(document).ready(function () {
    loadExerciceSocialAndPeriodes();
    setupFilterEventHandlers();
    ['achats', 'ventes', 'tresorerie', 'operations'].forEach(onglet => {
        setupPeriodChangeHandler(onglet);
    });
});


    </script>
    <script>

var tableAch, tableVentes, tableTresorerie, tableOP;

       $(document).ready(function () {

// Fonction pour charger les journaux dans le select
function loadJournaux(typeJournal, selectId) {
    $.ajax({
        url: '/journaux-' + typeJournal,
        method: 'GET',
        success: function (data) {
            if (data.error) {
                console.error(data.error);
                return;
            }
            $(selectId).empty();
            $(selectId).append('<option value="">Sélectionner un journal</option>');
            data.forEach(function (journal) {
                $(selectId).append(
                    '<option value="' +
                        journal.code_journal +
                        '" data-type="' +
                        journal.type_journal +
                        '" data-intitule="' +
                        journal.intitule +
                        '">' +
                        journal.code_journal + // Affiche le code_journal avec le intitule
                        '</option>'
                );
            });
        },
        error: function (err) {
            console.error('Erreur lors du chargement des journaux', err);
        },
    });
}

// Vérifier si un journal est sélectionné
function checkJournalSelection() {
    const selectedJournal = $('#journal-achats').val() || $('#journal-ventes').val() || $('#journal-tresorerie').val() || $('#journal-operations-diverses').val();

    if (!selectedJournal) {
        // Afficher l'alerte si aucun journal n'est sélectionné
        alert('Veuillez renseigner le code journal avant de continuer.');
        return false; // Empêcher l'action suivante
    }
    return true; // Permet de continuer si un journal est sélectionné
}

// Gestion des changements d'input (autre que le code_journal)
$('input, select').on('change', function () {
    if (!checkJournalSelection()) {
        // Empêcher l'action de changement si le code journal n'est pas sélectionné
        // Vous pouvez aussi ajouter ici un focus sur le select journal si nécessaire
    }
});

// Charger les journaux pour chaque onglet
loadJournaux('achats', '#journal-achats');
loadJournaux('ventes', '#journal-ventes');
loadJournaux('tresorerie', '#journal-tresorerie');
loadJournaux('operations-diverses', '#journal-operations-diverses');

// Gestion des changements de journal
$('select').on('change', function () {
    const selectedOption = $(this).find(':selected');
    const intituleJournal = selectedOption.data('intitule');
    const tabId = $(this).attr('id').replace('journal-', 'filter-intitule-');
    $('#' + tabId).val(intituleJournal ? 'journal - ' + intituleJournal : '');
});








const { DateTime } = luxon;

let societeId = $('#societe_id').val();
if (!societeId) {
    alert('L\'ID de la société est introuvable.');
    throw new Error("ID de la société manquant.");
}

// Fonction pour récupérer les rubriques TVA
async function fetchRubriquesTva() {
    const [ventesResponse, achatsResponse] = await Promise.all([
        fetch('/get-rubriques-tva-vente').then(res => res.json()),
        fetch('/get-rubriques-tva').then(res => res.json())
    ]);

    const ventes = ventesResponse.rubriques ? Object.values(ventesResponse.rubriques).flatMap(r => r.rubriques.map(rubrique => `${rubrique.Num_racines} - ${rubrique.Nom_racines} (${rubrique.Taux}%)`)) : [];
    const achats = achatsResponse.rubriques ? Object.values(achatsResponse.rubriques).flatMap(r => r.rubriques.map(rubrique => `${rubrique.Num_racines} - ${rubrique.Nom_racines} (${rubrique.Taux}%)`)) : [];

    return { ventes, achats };
}

// Fonction pour récupérer les comptes TVA
async function fetchComptesTva() {
    const [ventes, achats] = await Promise.all([
        fetch('/get-compte-tva-vente').then(res => res.json()),
        fetch('/get-compte-tva-ach').then(res => res.json())
    ]);

    return { ventes, achats };
}

// Initialisation des tables après récupération des données
(async function initTables() {
    try {
        const { ventes: rubriquesVentes, achats: rubriquesAchats } = await fetchRubriquesTva();
        const { ventes: comptesVentes, achats: comptesAchats } = await fetchComptesTva();

        // Récupération des clients et fournisseurs
        const clients = await fetch(`/get-clients?societe_id=${societeId}`).then(res => res.json());
        const fournisseurs = await fetch(`/get-fournisseurs-avec-details?societe_id=${societeId}`).then(res => res.json());

        const comptesClients = clients.map(client => `${client.compte} - ${client.intitule}`);
        const comptesFournisseurs = fournisseurs.map(fournisseur => `${fournisseur.compte} - ${fournisseur.intitule}`);


                // Fonction pour formater les valeurs en monnaie
                function formatCurrency(value) {
                    if (value == null) return '0,00';
                    return value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').replace('.', ',');
                }


                let numeroIncrementGlobal = 1; // Compteur global pour les pièces justificatives

        // Table des achats
        var tableAch = new Tabulator("#table-achats", {
            height: "600px",
            clipboard:true,
           clipboardPasteAction:"replace",
           rowHeader:{headerSort:false, resizable: true, frozen:true,width:50,minwidth:40, headerHozAlign:"center", hozAlign:"center", formatter:"rowSelection", titleFormatter:"rowSelection", cellClick:function(e, cell){
      cell.getRow().toggleSelect();
    }},
            layout: "fitColumns",
            printAsHtml:true,
            printHeader:"<h1>Table Achats<h1>",
            printFooter:"<h2>Example Table Footer<h2>",
            selectable: true,
            footerElement: "<table style='width: 30%; margin-top: 6px; border-collapse: collapse;'>" +
                                    "<tr>" +
                                        "<td style='padding: 8px; text-align: left; font-weight: bold;'>Cumul Débit :</td>" +
                                        "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='cumul-debit-achats'></span></td>" +
                                        "<td style='padding: 8px; text-align: left; font-weight: bold;'>Cumul Crédit :</td>" +
                                        "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='cumul-credit-achats'></span></td>" +
                                    "</tr>" +
                                    "<tr>" +
                                        "<td style='padding: 8px; text-align: left; font-weight: bold;'>Solde Débiteur :</td>" +
                                        "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='solde-debit-achats'></span></td>" +
                                        "<td style='padding: 8px; text-align: left; font-weight: bold;'>Solde Créditeur :</td>" +
                                        "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='solde-credit-achats'></span></td>" +
                                    "</tr>" +
                                    "</table>",  // Footer sous forme de tableau avec des styles inline
            data: Array(1).fill({}),
            ajaxURL: "/get-operations",
            columns: [
                { title: "ID", field: "id", visible: false },

                {
    title: "Date",
    field: "date",
    hozAlign: "center",
    cellStyle: { fontSize: "7px" },
    headerFilter: "input",
    sorter: "date",
    editor: function (cell, onRendered, success, cancel) {
        // Conteneur de l'éditeur
        const container = document.createElement("div");
        container.style.display = "flex";
        container.style.alignItems = "center";

        // Champ de saisie pour la date
        const input = document.createElement("input");
        input.type = "text";
        input.style.flex = "1";
        input.placeholder = "Saisir la date"; // Placeholder par défaut

        // Récupération des éléments pour déterminer la saisie
        const radioMois = document.getElementById("filter-mois-achats");
        const radioExercice = document.getElementById("filter-exercice-achats");
        const moisSelect = document.getElementById("periode-achats");
        const anneeInput = document.getElementById("annee-achats");

        // Adapter le placeholder et la logique selon la sélection
        const updatePlaceholder = () => {
            if (radioMois.checked) {
                input.placeholder = "jj/"; // Format jour uniquement avec "/"
            } else if (radioExercice.checked) {
                input.placeholder = "jj/mm"; // Format jour/mois
            }
        };

        // Initialiser le placeholder
        updatePlaceholder();

        // Préremplir le champ si une valeur existe déjà
        const currentValue = cell.getValue();
        if (currentValue) {
            const date = luxon.DateTime.fromISO(currentValue);
            if (date.isValid) {
                input.value = radioMois.checked
                    ? `${date.toFormat("dd")}/` // Affiche uniquement le jour avec le "/"
                    : date.toFormat("dd/MM"); // Affiche jour/mois
            }
        }

        // Fonction pour valider et ajuster la saisie au format attendu
        const formatInput = () => {
            let value = input.value.replace(/[^\d/]/g, ""); // Supprime tout sauf les chiffres et "/"

            if (radioMois.checked) {
                // Mode "Mois" : Affiche uniquement "jj/"
                if (!value.includes("/")) {
                    value = value.slice(0, 2) + "/"; // Ajoute "/" automatiquement après le jour
                } else {
                    const parts = value.split("/");
                    value = parts[0].slice(0, 2) + "/"; // Garde le jour et "/"
                }
            } else if (radioExercice.checked) {
                // Mode "Exercice" : Affiche "jj/mm"
                const parts = value.split("/");
                const day = parts[0]?.slice(0, 2) || ""; // Limite à 2 caractères pour le jour
                const month = parts[1]?.slice(0, 2) || ""; // Limite à 2 caractères pour le mois
                value = day + (day.length === 2 ? "/" : "") + month; // Ajoute "/" après le jour
            }

            input.value = value;
        };

        // Événement pour la saisie en temps réel et le formatage
        input.addEventListener("input", formatInput);

        // Validation et construction de la date
        input.addEventListener("blur", function () {
            const dateParts = input.value.split("/");
            const jour = parseInt(dateParts[0], 10);
            const mois = radioMois.checked
                ? parseInt(moisSelect.value, 10) // Récupère le mois sélectionné
                : parseInt(dateParts[1], 10); // Récupère le mois saisi
            const annee = parseInt(anneeInput.value, 10); // Récupère l'année

            // Validation et création de la date
            if (!isNaN(jour) && !isNaN(mois) && !isNaN(annee)) {
                const date = luxon.DateTime.local(annee, mois, jour);
                if (date.isValid) {
                    // Génération automatique de la pièce justificative
                    const row = cell.getRow();
                    const codeJournal = document.getElementById("journal-achats").value || "CJ"; // Récupération du code journal
                    const mois = date.month; // Mois de la date sélectionnée
                    const numeroIncrement = "0001"; // Valeur par défaut ou logique personnalisée
                    const pieceJustificative = `P${mois}${codeJournal}${numeroIncrement}`;

                    // Met à jour le champ "Pièce" de la ligne
                    row.update({ piece_justificative: pieceJustificative });

                    success(date.toISODate());
                } else {
                    alert("La date saisie est invalide.");
                    cancel();
                }
            } else {
                alert("Veuillez renseigner une date valide.");
                cancel();
            }
        });

        // Événement pour basculer entre les modes de saisie
        [radioMois, radioExercice].forEach((radio) => {
            radio.addEventListener("change", updatePlaceholder);
        });

        // Ajouter le champ au conteneur
        container.appendChild(input);

        onRendered(() => input.focus());

        return container;
    },
    formatter: function (cell) {
        const dateValue = cell.getValue();
        if (dateValue) {
            const dt = luxon.DateTime.fromISO(dateValue);
            return dt.isValid ? dt.toFormat("dd/MM/yyyy") : "Date invalide";
        }
        return "";
    },
},



           { title: "N° facture", field: "numero_facture",headerFilter: "input", cellStyle: { fontSize: "7px" },
           editor: "input" },
           {
    title: "Compte",
    field: "compte",
    headerFilter: "input",
    cellStyle: { fontSize: "7px" },

    editor: "list",
    editorParams: {
        autocomplete: true,
        listOnEmpty: true,
        values: comptesFournisseurs // Liste des comptes fournisseurs
    },

    cellEdited: function (cell) {
        const compteFournisseur = cell.getValue();
        const row = cell.getRow();

        fetch(`/get-fournisseurs-avec-details?societe_id=${societeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error("Erreur lors de la récupération des détails :", data.error);
                    return;
                }

                const fournisseur = data.find(f => `${f.compte} - ${f.intitule}` === compteFournisseur);
                if (fournisseur) {
                    const tauxTVA = parseFloat(fournisseur.taux_tva) || 0;
                    const rubriqueTVA = fournisseur.rubrique_tva || "";
                    const contrePartie = fournisseur.contre_partie || "";
                    const numeroFacture = row.getCell("numero_facture").getValue() || "Inconnu";

                    row.update({
                        contre_Partie: contrePartie,
                        rubrique_tva: rubriqueTVA,
                        taux_tva: tauxTVA,
                        libelle: `F° ${numeroFacture} ${fournisseur.intitule}`,
                        compte_tva: comptesVentes.length > 0
                            ? `${comptesVentes[0].compte} - ${comptesVentes[0].intitule}`
                            : "",
                    });

                    const rowData = row.getData();
                    const typeLigne = rowData.type_ligne || "ligne1";
                    calculerDebit(rowData, typeLigne);

                    row.update({
                        debit: rowData.debit
                    });

                    const creditCell = row.getCell("credit");
                    if (creditCell) {
                        creditCell.getElement().focus();
                    }
                } else {
                    console.warn("Aucun fournisseur correspondant trouvé pour :", compteFournisseur);
                }
            })
            .catch(error => {
                console.error("Erreur réseau :", error);
                alert("Une erreur est survenue lors de la récupération des détails du fournisseur.");
            });
    }
},


                {
                    title: "Libellé",
                    field: "libelle",
                    headerFilter: "input",
                    editor: "input"
                },
                {
    title: "Débit",
    field: "debit",
    headerFilter: "input",
    editor: "number", // Permet l'édition en tant que nombre
    bottomCalc: "sum", // Calcul du total dans le bas de la colonne
    formatter: function(cell) {
        // Formater pour afficher 0.00 si la cellule est vide ou nulle
        const value = cell.getValue();
        return value ? parseFloat(value).toFixed(2) : "0.00";
    },
    mutatorEdit: function(value) {
        // Retourner "0.00" comme valeur par défaut si vide lors de l'édition
        return value || "0.00";



        // Mettre à jour la valeur du champ "Débit"
        cell.setValue(debit.toFixed(2)); // Format en 2 décimales
    }
},

{
    title: "Crédit",
    field: "credit",
    headerFilter: "input",
    editor: "number",
    bottomCalc: "sum",
    formatter: function (cell) {
        const value = cell.getValue();
        return value ? parseFloat(value).toFixed(2) : "0.00";
    },
    mutatorEdit: function (value) {
        return value || "0.00";
    },
    cellEdited: async function (cell) {
        const creditValue = parseFloat(cell.getValue() || 0);

        // Vérifier si une valeur a été saisie dans le champ Crédit
        if (creditValue > 0) {
            const row = cell.getRow();

            // Laisser le champ "Débit" à 0 pour la ligne en cours (celle où vous modifiez "Crédit")
            row.update({
                debit: "0.00"
            });

            // Récupérer le taux TVA directement de la ligne
            const tauxTVA = parseFloat(row.getData().taux_tva || 0) / 100;

            // Vérifier la valeur du champ "Prorata de déduction"
            const prorataDeDeduction = row.getData().prorat_de_deduction || "Non"; // Par défaut, "Non"
            const isProrataOui = prorataDeDeduction.toLowerCase() === "Oui";

            // Appeler l'API pour récupérer le pourcentage de prorata (si nécessaire)
            const prorata = isProrataOui
                ? await fetch('/get-session-prorata')
                    .then(response => response.json())
                    .then(data => data.prorata_de_deduction || 0)
                    .catch(() => 0)
                : 0;

            // Logs pour vérification
            console.log("Taux TVA :", tauxTVA);
            console.log("Prorata de déduction :", prorata);
            console.log("Prorata activé :", isProrataOui);

            // Calcul du débit pour la ligne suivante (si prorata est activé)
            const debitLigne2 = isProrataOui
                ? ((creditValue / (1 + tauxTVA)) * tauxTVA) * (prorata / 100)
                : (creditValue / (1 + tauxTVA)) * tauxTVA;

            console.log("Débit Ligne 2 calculé :", debitLigne2);

            // Mise à jour du débit et du crédit pour la ligne suivante
            const nextRow = cell.getTable().getRows()[row.getIndex() + 1];
            if (nextRow) {
                nextRow.update({
                    debit: debitLigne2.toFixed(2),
                    credit: "0.00" // Mettre le crédit à 0 pour la ligne suivante
                });
            }

            // Mettre le crédit à 0 dans les autres lignes où le débit a été calculé
            const table = cell.getTable();
            table.getRows().forEach((otherRow, index) => {
                if (index !== row.getIndex()) {
                    const debitOtherRow = parseFloat(otherRow.getData().debit || 0);
                    if (debitOtherRow > 0) {
                        otherRow.update({
                            credit: "0.00" // Forcer le crédit à 0 si le débit est calculé dans une autre ligne
                        });
                    }
                }
            });

            // Déplacer le focus sur le champ suivant (compte TVA) dans la ligne actuelle
            const compteTvaCell = row.getCell("compte_tva");
            if (compteTvaCell) {
                compteTvaCell.getElement().focus();
            }
        } else {
            // Si la valeur du crédit est 0, réinitialiser les champs débit des deux lignes
            const row = cell.getRow();
            row.update({ debit: "0.00" });

            const nextRow = cell.getTable().getRows()[row.getIndex() + 1];
            if (nextRow) {
                nextRow.update({ debit: "0.00" });
            }
        }
    }
},


{
                                            title: "Contre-Partie",
                                            field: "contre_Partie",
                                            headerFilter: "input",
                                            editor: "list",
                                            editorParams: {
                                                autocomplete: true,
                                                listOnEmpty: true,
                                                values: fournisseurs.map(f => f.contre_partie)  // Remplir avec les valeurs de "contre_partie" de fournisseurs
                                            }
                                        },

                {
                    title: "Rubrique TVA",
                    field: "rubrique_tva",
                    headerFilter: "input",
                    editor: "list",
                    editorParams: {
                        autocomplete: true,
                        listOnEmpty: true,
                        values: rubriquesAchats
                    }
                },
                {
                    title: "Compte TVA",
                    field: "compte_tva",
                    headerFilter: "input",
                    editor: "list",

                    editorParams: {
                        autocomplete: true,
                        listOnEmpty: true,

                        values: comptesVentes.map(compte => `${compte.compte} - ${compte.intitule}`)
                    }
                },
                {
    title: "Prorat de deduction",
    field: "prorat_de_deduction",
    headerFilter: "input",
    editor: "list",  // Utilisation de l'éditeur de type 'list' pour une datalist
    editorParams: {
        autocomplete: true,  // Active l'autocomplétion
        listOnEmpty: true,   // Affiche la liste même si la cellule est vide
        values: ["Oui", "Non"]  // Valeurs possibles dans la datalist
    }
},

{
            title: "Pièce",
            field: "piece_justificative",
            editor: "input", // Éditeur pour permettre la modification manuelle
            headerFilter: "input",
            mutator: function (value, data, type, params, component) {
                // Génération automatique de la pièce justificative
                if (data.credit === data.debit && data.credit > 0) {
                    const mois = new Date().getMonth() + 1; // Récupère le mois courant (base 1)
                    const codeJournal = document.getElementById("journal-achats").value || "CJ"; // Code journal
                    const numeroIncrement = String(numeroIncrementGlobal).padStart(4, "0"); // Numéro formaté

                    return `P${mois}${codeJournal}${numeroIncrement}`;
                }
                return value; // Si les conditions ne sont pas remplies, retourne la valeur existante
            },
            mutatorParams: { increment: "0001" }, // Paramètre par défaut pour l'incrément
            cellClick: function (e, cell) {
                const row = cell.getRow();
                const data = row.getData();

                // Vérifie si les conditions pour générer une pièce sont remplies
                if (data.credit === data.debit && data.credit > 0) {
                    const mois = new Date().getMonth() + 1; // Mois courant
                    const codeJournal = document.getElementById("journal-achats").value || "CJ";
                    const numeroIncrement = String(numeroIncrementGlobal).padStart(4, "0");
                    const pieceJustificative = `P${mois}${codeJournal}${numeroIncrement}`;

                    // Mise à jour de la ligne
                    row.update({ piece_justificative: pieceJustificative });
                    numeroIncrementGlobal++; // Incrémentation globale
                } else {
                    alert("Le débit et le crédit doivent être égaux et supérieurs à 0 pour générer une pièce.");
                }
            },
        },

  { title: "Code_journal", field: "type_Journal", visible: false },

                ],

                rowFormatter: function(row) {
    let debitTotal = 0;
    let creditTotal = 0;

    // Calcul des totaux pour toutes les lignes
    row.getTable().getRows().forEach(function(r) {
        debitTotal += parseFloat(r.getData().debit || 0);
        creditTotal += parseFloat(r.getData().credit || 0);
    });

    // Règles de calcul pour le solde débiteur et créditeur
    let soldeDebiteur = debitTotal > creditTotal ? debitTotal - creditTotal : 0.00;
    let soldeCrediteur = creditTotal > debitTotal ? creditTotal - debitTotal : 0.00;

    // Mise à jour du footer avec les résultats
    document.getElementById('cumul-debit-achats').innerText = formatCurrency(debitTotal);
    document.getElementById('cumul-credit-achats').innerText = formatCurrency(creditTotal);
    document.getElementById('solde-debit-achats').innerText = formatCurrency(soldeDebiteur);
    document.getElementById('solde-credit-achats').innerText = formatCurrency(soldeCrediteur);
}


 });

// Ajouter l'écouteur pour mettre à jour "type_Journal"
document.querySelector("#journal-achats").addEventListener("change", function (e) {
    const selectedCode = e.target.value;

    let ligneSelectionnee = table.getSelectedRows()[0];
    if (ligneSelectionnee) {
        ligneSelectionnee.update({ type_Journal: selectedCode });
    }
});


document.getElementById("print-table").addEventListener("click", function () {
    if (tableAch) {
      tableAch.print(false, true); // Utilise la méthode d'impression de Tabulator
    } else {
        console.error("Le Tabulator n'est pas initialisée.");
    }
});

document.getElementById("download-xlsx").addEventListener("click", function () {
    if (tableAch) {
       tableAch.download("xlsx", "data.xlsx", { sheetName: "My Data" });
    } else {
        console.error("La table Tabulator n'est pas initialisée.");
    }
});

document.getElementById("download-pdf").addEventListener("click", function () {
    if (tableAch) {
       tableAch.download("pdf", "data.pdf", {
            orientation: "portrait",
            title: "Rapport des achats",
        });
    } else {
        console.error("La table Tabulator n'est pas initialisée.");
    }
});



// Table des ventes
      var tableVentes  = new Tabulator("#table-ventes", {
            height: "500px",
            clipboard:true,
            clipboardPasteAction:"replace",
            rowHeader:{headerSort:false, resizable: true, frozen:true,width:50,minwidth:40, headerHozAlign:"center", hozAlign:"center", formatter:"rowSelection", titleFormatter:"rowSelection", cellClick:function(e, cell){
      cell.getRow().toggleSelect();
    }},
            layout: "fitColumns",
            selectable: true,
            footerElement: "<table style='width: 30%; margin-top: 6px; border-collapse: collapse;'>" +
                                    "<tr>" +
                                        "<td style='padding: 6px; text-align: left; font-weight: bold;'>Cumul Débit :</td>" +
                                        "<td style='padding: 6px; text-align: right; font-size: 12px;'><span id='cumul-debit-ventes'></span></td>" +
                                        "<td style='padding: 6px; text-align: left; font-weight: bold;'>Cumul Crédit :</td>" +
                                        "<td style='padding: 6px; text-align: right; font-size: 12px;'><span id='cumul-credit-ventes'></span></td>" +
                                    "</tr>" +
                                    "<tr>" +
                                        "<td style='padding: 6px; text-align: left; font-weight: bold;'>Solde Débiteur :</td>" +
                                        "<td style='padding: 6px; text-align: right; font-size: 12px;'><span id='solde-debit-ventes'></span></td>" +
                                        "<td style='padding: 6px; text-align: left; font-weight: bold;'>Solde Créditeur :</td>" +
                                        "<td style='padding: 6px; text-align: right; font-size: 12px;'><span id='solde-credit-ventes'></span></td>" +
                                    "</tr>" +
                                    "</table>",  // Footer sous forme de tableau avec des styles inline
            data: Array(1).fill({}),
            ajaxURL: "/get-operations",
            columns: [
                { title: "ID", field: "id", visible: false },

                {
            title: "Date",
            field: "date",
            hozAlign: "center",
            headerFilter: "input",
            sorter: "date",
            editor: function(cell, onRendered, success, cancel) {
                const input = document.createElement("input");
                input.type = "text"; // Utilisation d'un champ de texte pour flatpickr

                // Si une valeur existe, on la formate en "dd/MM/yyyy", sinon on laisse vide
                const currentValue = cell.getValue();
                input.value = currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '';

                // Ajout du placeholder vide "jj/mm/aaaa"
                input.placeholder = "jj/mm/aaaa";

                // Initialisation de flatpickr
                flatpickr(input, {
                    dateFormat: "d/m/Y", // Format de date personnalisé
                    defaultDate: currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '', // Si aucune valeur n'est définie, laisse vide
                    onChange: function(selectedDates) {
                        // Si une date est sélectionnée, on la convertit en ISO avec Luxon
                        success(luxon.DateTime.fromJSDate(selectedDates[0]).toISODate());
                    },
                    allowInput: true, // Permet à l'utilisateur de saisir la date manuellement
                });

                onRendered(function() {
                    input.focus(); // Focus sur le champ lors de l'édition
                });

                return input;
            },
            formatter: function(cell) {
                // Formate la date en "dd/MM/yyyy" lors de l'affichage de la cellule
                let dateValue = cell.getValue();
                if (dateValue) {
                    const dt = luxon.DateTime.fromISO(dateValue);
                    return dt.isValid ? dt.toFormat('dd/MM/yyyy') : "Date invalide";
                }
                return ""; // Si la valeur est vide, ne rien afficher
            },
        },
                { title: "N° dossier", field: "numero_dossier",headerFilter: "input", editor: "input" },
                { title: "N° Facture", field: "numero_facture",headerFilter: "input", editor: "input" },

                {
    title: "Compte",
    field: "compte",
    headerFilter: "input",
    editor: "list",
    editorParams: {
        autocomplete: true,
        listOnEmpty: true,
        values: comptesClients, // Liste des comptes clients déjà définie
    },
    cellEdited: function (cell) {
        // Récupérer la ligne associée
        const row = cell.getRow();

        // Valeur sélectionnée dans la liste
        const compteSelectionne = cell.getValue();

        // Récupérer les autres champs de la ligne
        const numeroDossier = row.getCell("numero_dossier").getValue() || "";
        const numeroFacture = row.getCell("numero_facture").getValue() || "";

        // Recherche de l'intitulé dans comptesClients
        const client = clients.find(c => `${c.compte} - ${c.intitule}` === compteSelectionne);
        const intituleClient = client ? client.intitule : compteSelectionne.split(" - ")[1] || "Inconnu";

        // Mise à jour du champ "Libellé" au format souhaité
        row.update({
            libelle: `F°${numeroFacture} D°${numeroDossier} ${intituleClient}`,
        });
    },
},

        {
            title: "Libellé",
            field: "libelle",
            headerFilter: "input",
            editor: "input", // Optionnel, si modification manuelle est permise
            editable: false, // Non éditable automatiquement
        },
                { title: "Débit", field: "debit", headerFilter: "input", formatter: "money" },
                { title: "Crédit", field: "credit", headerFilter: "input", formatter: "money" },
                {
                                            title: "Contre-Partie",
                                            field: "contre_Partie",
                                            headerFilter: "input",
                                            editor: "list",
                                            editorParams: {
                                                autocomplete: true,
                                                listOnEmpty: true,
                                                values: fournisseurs.map(f => f.contre_partie)  // Remplir avec les valeurs de "contre_partie" de fournisseurs
                                            }
                                        },
                {
                    title: "Compte TVA",
                    field: "compte_tva",
                    headerFilter: "input",
                    editor: "list",
                    editorParams: {
                        autocomplete: true,
                        listOnEmpty: true,
                        values: comptesAchats.map(compte => `${compte.compte} - ${compte.intitule}`)
                    }
                },
                {
                    title: "Rubrique TVA",
                    field: "rubrique_tva",
                    headerFilter: "input",
                    editor: "list",
                    editorParams: {
                        autocomplete: true,
                        listOnEmpty: true,
                        values: rubriquesVentes
                    }
                }



                ],
                    rowFormatter: function(row) {
                        let data = row.getData();
                        // Calcul des totaux
                        let debitTotal = 0;
                        let creditTotal = 0;

                        row.getTable().getRows().forEach(function(r) {
                            debitTotal += parseFloat(r.getData().debit || 0);
                            creditTotal += parseFloat(r.getData().credit || 0);
                        });

                        // Calcul des soldes
                        let soldeDebiteur = debitTotal - creditTotal; // Solde débiteur = Débit - Crédit
                        let soldeCrediteur = creditTotal - debitTotal; // Solde créditeur = Crédit - Débit

                        // Mise à jour du footer avec les totaux
                        document.getElementById('cumul-debit-ventes').innerText = formatCurrency(debitTotal);
                        document.getElementById('cumul-credit-ventes').innerText = formatCurrency(creditTotal);
                        document.getElementById('solde-debit-ventes').innerText = formatCurrency(soldeDebiteur);
                        document.getElementById('solde-credit-ventes').innerText = formatCurrency(soldeCrediteur);
                    }

        });

// Fonction pour générer un ID unique
function generateUniqueId() {
    return 'id_' + Math.random().toString(36).substr(2, 9); // Génère un ID unique
}

async function calculerDebit(rowData, typeLigne) {
    const credit = parseFloat(rowData.credit || 0);
    const tauxTVA = parseFloat(rowData.taux_tva || 0) / 100;
    console.log(`Taux TVA récupéré : ${tauxTVA * 100}%`); // Affichage du taux TVA (en pourcentage)

    const prorataDeDeduction = (rowData.prorat_de_deduction || "Non").trim().toLowerCase(); // Normalisation de la valeur
    const isProrataOui = prorataDeDeduction === "Oui";

    let prorata = 0;

    if (isProrataOui) {
        // Récupérer le prorata via l'API
        try {
            const response = await fetch('/get-session-prorata');
            if (!response.ok) {
                throw new Error(`Erreur réseau : ${response.statusText}`);
            }

            const data = await response.json();

            if (data && typeof data.prorata_de_deduction !== 'undefined') {
                prorata = parseFloat(data.prorata_de_deduction) || 0; // Assurez-vous que c'est un nombre
            } else {
                console.warn('prorata_de_deduction absent de la réponse.');
                prorata = 0; // Par défaut à 0 si non défini
            }
        } catch (error) {
            console.error('Erreur lors de la récupération du prorata de déduction :', error);
            prorata = 0; // Par défaut à 0 en cas d'échec
        }

        console.log(`Prorata récupéré : ${prorata}`);
    }

    // Calculs en fonction du type de ligne
    let debit = 0;
    if (typeLigne === "ligne1") {
        if (isProrataOui) {
            debit = (credit / (1 + tauxTVA)) + (((credit / (1 + tauxTVA)) * tauxTVA) * (1 - prorata / 100));
        } else {
            debit = credit / (1 + tauxTVA);
        }
    } else if (typeLigne === "ligne2") {
        if (isProrataOui) {
            debit = ((credit / (1 + tauxTVA)) * tauxTVA) * (prorata / 100);
        } else {
            debit = (credit / (1 + tauxTVA)) * tauxTVA;
        }
    }

    // Mise à jour des données de la ligne
    rowData.debit = debit;
    rowData.credit = "0.00"; // Mise à jour du crédit à 0 après le calcul
    console.log(`Débit calculé pour ${typeLigne} : ${debit}`);
}

function ajouterLigne(table, preRemplir = false, precedente = null, prorata = 0, tauxTva = 0) {
    let nouvellesLignes = [];

    if (preRemplir && precedente) {
        let ligne1 = { ...precedente };
        let ligne2 = { ...precedente };

        // Ligne 1: Ajustement des champs
        ligne1.compte = precedente.contre_Partie || '';
        ligne1.contre_Partie = precedente.compte || '';
        ligne1.compte_tva = '';
        ligne1.credit = precedente.credit || 0; // Assigner le crédit de la ligne précédente
        ligne1.debit = 0; // Initialiser le débit à 0

        // Calculer le débit pour la ligne 1
        calculerDebit(ligne1, "ligne1", prorata, tauxTva);

        table.addRow(ligne1);
        nouvellesLignes.push(ligne1);

        // Ligne 2: Ajustement des champs
        ligne2.compte = precedente.compte_tva || '';
        ligne2.contre_Partie = ligne1.compte || '';
        ligne2.compte_tva = '';
        ligne2.credit = precedente.credit || 0; // Assigner le crédit de la ligne précédente
        ligne2.debit = 0; // Initialiser le débit à 0

        // Calculer le débit pour la ligne 2
        calculerDebit(ligne2, "ligne2", prorata, tauxTva);

        table.addRow(ligne2);
        nouvellesLignes.push(ligne2);
    } else {
        let ligneVide = {
            compte: '',
            contre_Partie: '',
            compte_tva: '',
            debit: 0,
            credit: 0
        };

        table.addRow(ligneVide);
        nouvellesLignes.push(ligneVide);
    }

    return nouvellesLignes;
}

// Fonction pour écouter l'événement "Entrer"
function ecouterEntrer(table) {
    table.element.addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
            const contrePartieVentesActive = document.querySelector("#filter-contre-partie-ventes").checked;
            const contrePartieAchatsActive = document.querySelector("#filter-contre-partie-achats").checked;

            if (contrePartieVentesActive || contrePartieAchatsActive) {
                let ligneSelectionnee = table.getSelectedRows()[0];
                if (!ligneSelectionnee) return;

                let ligne = ligneSelectionnee.getData();
                let nouvellesLignes = ajouterLigne(table, true, ligne);

                nouvellesLignes.forEach(function (rowData, index) {
                    const typeLigne = index === 0 ? "ligne1" : "ligne2";
                    calculerDebit(rowData, typeLigne);
                });

                event.preventDefault();
            }
        }
    });
}




// Initialisation des filtres
document.querySelector("#filter-contre-partie-ventes").addEventListener("change", function (e) {
    if (e.target.checked) {}
});

document.querySelector("#filter-contre-partie-achats").addEventListener("change", function (e) {
    if (e.target.checked) {}
});

// Application de l'écouteur pour la touche "Entrée"
ecouterEntrer(tableVentes);
ecouterEntrer(tableAch);


                 // Gestionnaire pour importer les données
                 document.getElementById("import-ventes").addEventListener("click", function () {
                alert("Fonction d'import non implémentée !");
                // Ajoutez ici votre logique pour l'importation (par ex. ouvrir un modal ou lire un fichier)
            });

            // Gestionnaire pour exporter vers Excel
            document.getElementById("export-ventesExcel").addEventListener("click", function () {
                tableVentes .download("xlsx", "ventes.xlsx", { sheetName: "Ventes" });
            });

            // Gestionnaire pour exporter vers PDF
            document.getElementById("export-ventesPDF").addEventListener("click", function () {
                tableVentes .download("pdf", "ventes.pdf", {
                    orientation: "portrait", // Orientation de la page
                    title: "Rapport des Ventes", // Titre du rapport
                });
            });

            // Gestionnaire pour supprimer une ligne sélectionnée
            document.getElementById("delete-row-btn").addEventListener("click", function () {
                let selectedRows = tableVentes .getSelectedRows(); // Récupérer les lignes sélectionnées
                if (selectedRows.length > 0) {
                    selectedRows.forEach(function (row) {
                        row.delete(); // Supprimer chaque ligne sélectionnée
                    });
                    alert("Les lignes sélectionnées ont été supprimées.");
                } else {
                    alert("Veuillez sélectionner une ou plusieurs lignes à supprimer.");
                }
            });

            document.getElementById("print-tableV").addEventListener("click", function () {
    if (tableVentes ) {
        tableVentes .print(false, true); // Utilise la méthode d'impression de Tabulator
    } else {
        console.error("La table Tabulator n'est pas initialisée.");
    }
});


    } catch (error) {
        console.error("Erreur lors de l'initialisation des tables :", error);
    }
})();

function formatDate(cell) {
    let dateValue = cell.getValue();
    if (dateValue) {
        const dt = DateTime.fromISO(dateValue);
        return dt.isValid ? dt.toFormat('dd/MM/yyyy') : "Date invalide";
    }
    return "";
}


function formatCurrency(value) {
                    if (value == null) return '0,00';
                    return value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').replace('.', ',');
                }




// Configuration du tableau Trésorerie
var tableTresorerie = new Tabulator("#table-tresorerie", {
    layout: "fitColumns",
    height: "600px",
    rowHeader:{headerSort:false, resizable: true, frozen:true,width:50,minwidth:40, headerHozAlign:"center", hozAlign:"center", formatter:"rowSelection", titleFormatter:"rowSelection", cellClick:function(e, cell){
      cell.getRow().toggleSelect();
    }},
    selectable: true,
    data: Array(1).fill({}),
    columns: [
        { title: "ID", field: "id", visible: false },
        {
            title: "Date",
            field: "date",
            hozAlign: "center",
            headerFilter: "input",

            sorter: "date",
            editor: function(cell, onRendered, success, cancel) {
                const input = document.createElement("input");
                input.type = "text"; // Utilisation d'un champ de texte pour flatpickr
                const currentValue = cell.getValue();
                input.value = currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '';
                input.placeholder = "jj/mm/aaaa";
                flatpickr(input, {
                    dateFormat: "d/m/Y",
                    defaultDate: currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '',
                    onChange: function(selectedDates) {
                        success(luxon.DateTime.fromJSDate(selectedDates[0]).toISODate());
                    },
                    allowInput: true,
                });
                onRendered(function() {
                    input.focus();
                });
                return input;
            },
            formatter: function(cell) {
                let dateValue = cell.getValue();
                if (dateValue) {
                    const dt = luxon.DateTime.fromISO(dateValue);
                    return dt.isValid ? dt.toFormat('dd/MM/yyyy') : "Date invalide";
                }
                return "";
            },
        },
        { title: "N°Facture", field: "numero_facture", headerFilter: "input", editor: "input" },
        { title: "Compte", field: "compte", headerFilter: "input" },
        { title: "Libellé", field: "libelle", headerFilter: "input" },
        { title: "Débit", field: "debit", headerFilter: "input", editor: "number", bottomCalc: "sum" },
        { title: "Crédit", field: "credit", headerFilter: "input", editor: "number", bottomCalc: "sum" },
    ],
    footerElement: "<table style='width: 30%; margin-top: 10px; border-collapse: collapse;'>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Débit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-debit-tresorerie'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Crédit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-credit-tresorerie'></span></td>" +
                    "</tr>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Débiteur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-debit-tresorerie'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Créditeur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-credit-tresorerie'></span></td>" +
                    "</tr>" +
                    "</table>", // Footer sous forme de tableau
    rowFormatter: function(row) {
        let debitTotal = 0;
        let creditTotal = 0;

        row.getTable().getRows().forEach(function(r) {
            debitTotal += parseFloat(r.getData().montant || 0);
            creditTotal += parseFloat(r.getData().montant || 0);
        });

        let soldeDebiteur = debitTotal - creditTotal;
        let soldeCrediteur = creditTotal - debitTotal;

        // Mise à jour des éléments dans le footer
        document.getElementById('cumul-debit-tresorerie').innerText = formatCurrency(debitTotal);
        document.getElementById('cumul-credit-tresorerie').innerText = formatCurrency(creditTotal);
        document.getElementById('solde-debit-tresorerie').innerText = formatCurrency(soldeDebiteur);
        document.getElementById('solde-credit-tresorerie').innerText = formatCurrency(soldeCrediteur);
    }
});

// Configuration du tableau Opérations Diverses
var tableOP = new Tabulator("#table-operations-diverses", {
    layout: "fitColumns",
    height: "600px",
    rowHeader:{headerSort:false, resizable: true, frozen:true,width:50,minwidth:40, headerHozAlign:"center", hozAlign:"center", formatter:"rowSelection", titleFormatter:"rowSelection", cellClick:function(e, cell){
      cell.getRow().toggleSelect();
    }},
    selectable: true,
    data: Array(1).fill({}),
    columns: [
        { title: "ID", field: "id", visible: false },
        {
            title: "Date",
            field: "date",
            hozAlign: "center",
            headerFilter: "input",

            sorter: "date",
            editor: function(cell, onRendered, success, cancel) {
                const input = document.createElement("input");
                input.type = "text"; // Utilisation d'un champ de texte pour flatpickr
                const currentValue = cell.getValue();
                input.value = currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '';
                input.placeholder = "jj/mm/aaaa";
                flatpickr(input, {
                    dateFormat: "d/m/Y",
                    defaultDate: currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '',
                    onChange: function(selectedDates) {
                        success(luxon.DateTime.fromJSDate(selectedDates[0]).toISODate());
                    },
                    allowInput: true,
                });
                onRendered(function() {
                    input.focus();
                });
                return input;
            },
            formatter: function(cell) {
                let dateValue = cell.getValue();
                if (dateValue) {
                    const dt = luxon.DateTime.fromISO(dateValue);
                    return dt.isValid ? dt.toFormat('dd/MM/yyyy') : "Date invalide";
                }
                return "";
            },
        },
        { title: "N°Facture", field: "numero_facture", headerFilter: "input", editor: "input" },

        { title: "Compte", field: "compte" , headerFilter: "input"},
        { title: "Libellé", field: "libelle", editor: "input" , headerFilter: "input",},
        { title: "Débit", field: "debit", headerFilter: "input", editor: "number", bottomCalc: "sum" },
        { title: "Crédit", field: "credit", headerFilter: "input", editor: "number", bottomCalc: "sum" },

     ],
    footerElement: "<table style='width: 30%; margin-top: 10px; border-collapse: collapse;'>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Débit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-debit-operations-diverses'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Crédit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-credit-operations-diverses'></span></td>" +
                    "</tr>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Débiteur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-debit-operations-diverses'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Créditeur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-credit-operations-diverses'></span></td>" +
                    "</tr>" +
                    "</table>", // Footer sous forme de tableau
    rowFormatter: function(row) {
        let debitTotal = 0;
        let creditTotal = 0;

        row.getTable().getRows().forEach(function(r) {
            debitTotal += parseFloat(r.getData().montant || 0);
            creditTotal += parseFloat(r.getData().montant || 0);
        });

        let soldeDebiteur = debitTotal - creditTotal;
        let soldeCrediteur = creditTotal - debitTotal;

        // Mise à jour des éléments dans le footer
        document.getElementById('cumul-debit-operations-diverses').innerText = formatCurrency(debitTotal);
        document.getElementById('cumul-credit-operations-diverses').innerText = formatCurrency(creditTotal);
        document.getElementById('solde-debit-operations-diverses').innerText = formatCurrency(soldeDebiteur);
        document.getElementById('solde-credit-operations-diverses').innerText = formatCurrency(soldeCrediteur);
    }
});



            // Gestion des onglets
            $('.tab').on('click', function () {
                const tabId = $(this).data('tab');
                $('.tab').removeClass('active');
                $('.tab-content').removeClass('active');
                $(this).addClass('active');
                $('#' + tabId).addClass('active');
            });




        });

    </script>


</html>
@endsection
