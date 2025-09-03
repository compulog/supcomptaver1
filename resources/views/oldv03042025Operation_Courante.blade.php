
@extends('layouts.user_type.auth')

@section('content')

    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="societe_id" content="{{ session('societeId') }}">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opérations Courantes</title>


  <!-- Bootstrap CSS (dernière version Bootstrap 5.3.0) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- SweetAlert2 CSS (dernière version) -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

  <!-- FontAwesome CSS (dernière version, ici 6.0.0) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

  <!-- Tabulator CSS (dernière version stable 6.3.4) -->
  <link href="https://unpkg.com/tabulator-tables@6.1.0/dist/css/tabulator_bootstrap5.min.css" rel="stylesheet">

  <!-- Flatpickr CSS -->
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
 
  <!-- Select2 CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<!-- Ajout de FontAwesome pour les icônes -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<!-- Inclure SheetJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

<!-- Inclure pdf.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
<!-- Inclure XLSX.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

<!-- Inclure jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.11/jspdf.plugin.autotable.min.js"></script>
 <!-- Inclure jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.11/jspdf.plugin.autotable.min.js"></script>

<!-- Votre script -->
<script src="path/to/Operation_Caisse_Banque.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.11/jspdf.plugin.autotable.min.js"></script>
 <style>





.tabulator-cell {
  padding: 2px 5px;
}

.tabulator-row {
  min-height: 30px;
}

#cumul-debit-achats,
#cumul-credit-achats,
#solde-debit-achats,
#solde-credit-achats {
  font-size: 12px; /* Ajustez la taille selon vos préférences */
}

/* Survol (hover) si vous voulez un effet au passage de la souris */
.tabulator-row:hover .tabulator-cell {
  background-color: #D0E7FD; /* un bleu un peu plus foncé */
}

.tabulator-row.tabulator-selected {
  background-color: #D2E8FF !important;
}

.tabulator-cell {
  padding: 2px 5px;
}

.tabulator-row {
  min-height: 30px;
}

#table-Caisse .tabulator-cell,
#table-Caisse.tabulator-header .tabulator-col {
  font-size: 12px; /* ou la valeur que tu souhaites */
}
#table-operations-diverses .tabulator-cell,
#table-operations-diverses .tabulator-header .tabulator-col {
  font-size: 12px; /* Ajustez la taille en fonction de vos besoins */
}

#table-ventes .tabulator-cell,
#table-ventes .tabulator-header .tabulator-col {
  font-size: 12px; /* Ajustez la taille en fonction de vos besoins */
}

/* Par exemple, dans ton fichier CSS ou dans un style <style> dans ta page */
    #table-achats .tabulator-cell,
#table-achats .tabulator-header .tabulator-col {
  font-size: 12px; /* ou la valeur que tu souhaites */
}

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
      background-color: #e5f504ec;
      animation: blink 1s steps(5, start) infinite;
    }
    @keyframes blink {
      to { visibility: hidden; }
    }

    .input-group .form-control {
      height: calc(1.5em + .75rem + 2px); /* Hauteur standard Bootstrap */
    }
    .input-group-append .btn {
      padding: 0.25rem 0.5rem;
      font-size: 0.875rem;
      line-height: 1.5;
    }
    .btn {
  position: relative;
  z-index: 1000;
}

    /* Style pour la liste dans le popup SweetAlert2 */
    .swal2-list-group {
      list-style: none;
      padding: 0;
      margin: 0;
      max-height: 300px;
      overflow-y: auto;
    }
    .swal2-list-group-item {
      padding: 8px 12px;
      border-bottom: 1px solid #eee;
      cursor: pointer;
    }
    .swal2-list-group-item:hover,
    .swal2-list-group-item.selected {
      background-color: #007bff;
      color: #fff;
    }

    .modal {
    display: none; /* Cacher par défaut */
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0); /* Fond semi-transparent */
    background-color: rgba(0,0,0,0.4);
    padding-top: 60px;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
}

.close-btn {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close-btn:hover,
.close-btn:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
/* Style global pour la modale */
.modal {
    display: none; /* Masquer la modale par défaut */
    position: fixed;
    z-index: 999; /* Assurez-vous que la modale soit au-dessus du contenu */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Fond semi-transparent */
}

/* Contenu de la modale */
.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 60%;
    max-height: 80%;
    overflow-y: auto;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Bouton de fermeture de la modale */
.close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 30px;
    color: #888;
    cursor: pointer;
}

.close-btn:hover {
    color: #333;
}

/* Titre de la modale */
h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}

/* Contenu de la modale (liste de fichiers) */
.modal-body {
    margin-bottom: 20px;
}

/* Liste des fichiers */
#fileList {
    list-style: none;
    padding: 0;
    margin: 0;
}

#fileList li {
    margin: 10px 0;
}

/* Bouton de fichier */
.file-button {
    display: flex;
    align-items: center;
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    width: 100%;
    cursor: pointer;
    font-size: 16px;
    text-align: left;
    transition: background-color 0.3s ease;
}

.file-button:hover {
    background-color: #0056b3;
}

.file-button i {
    margin-right: 10px;
}

/* Footer de la modale */
.modal-footer {
    display: flex;
    justify-content: flex-end;
}

/* Bouton annuler */
.cancel-btn {
    background-color: #ccc;
    color: #fff;
    padding: 10px 15px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

.cancel-btn:hover {
    background-color: #999;
}
/* Style du bouton Confirmer */
.confirm-btn {
    background-color: #28a745; /* Couleur verte */
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-size: 16px;
    margin-left: 10px; /* Espacement entre le bouton "Annuler" et "Confirmer" */
}

.confirm-btn:hover {
    background-color: #218838; /* Teinte plus foncée de vert au survol */
}


#table-Caisse .tabulator-cell,
#table-Caisse.tabulator-header .tabulator-col,
#tableBanque .tabulator-cell,
#tableBanque.tabulator-header .tabulator-col {
  font-size: 12px; /* Ajustez la taille selon vos préférences */
  padding: 2px 5px; /* Ajout de padding pour les cellules */
}

.tabulator-row {
  min-height: 30px; /* Hauteur minimale des lignes */
}

.tabulator-row:hover .tabulator-cell {
  background-color: #D0E7FD; /* Couleur de survol */
}

.tabulator-row.tabulator-selected {
  background-color: #D2E8FF !important; /* Couleur pour la ligne sélectionnée */
}


    </style>

<body>
        <div class="tabs" style="display: flex; gap: 5px; margin-bottom: 10px; border-bottom: 2px solid #ccc;">
            <!-- Onglet Achats -->
            <div class="tab active" data-tab="achats"
                 style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #007bff; transition: background-color 0.3s, border-color 0.3s; box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);">
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
    // Fonction pour charger le script JavaScript
    function loadScript(scriptUrl) {
        const script = document.createElement('script');
        script.src = scriptUrl;
        document.body.appendChild(script);
    }

    // Gestionnaire d'événements pour les onglets
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Supprimer tous les scripts précédemment chargés
            const existingScripts = document.querySelectorAll('script[src]');
            existingScripts.forEach(existingScript => existingScript.remove());

            // Activer l'onglet sélectionné
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            const activeTabContent = document.getElementById(this.getAttribute('data-tab'));
            activeTabContent.classList.add('active');

            // Charger le script correspondant à l'onglet
            switch (this.getAttribute('data-tab')) {
                
                case 'Caisse':
                    loadScript('js/Operation_Caisse_Banque.js');
                    break;
                case 'Banque':
                    loadScript('js/Operation_Banque.js');
                    break;
                 
            }
        });
    });
</script>
        



<!-- Onglet Achats -->
<div id="achats" class="tab-content active" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 10px; color: #333; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
    <div class="filter-container" style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">

        <!-- Code et Journal -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="journal-achats" style="font-size: 11px; font-weight: bold;">Code :</label>
<select id="journal-achats" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;"></select>
<input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

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
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

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
    <select id="journal-Caisse" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;">
        <option value="">Sélectionner un journal</option> <!-- Option par défaut -->
    </select>
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
    
    <!-- Choix du type de période -->
    <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR" type="radio" name="filter-period-Caisse" id="filter-mois-Caisse" value="mois" checked>
        <label class="form-check-label" for="filter-mois-Caisse" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
    </div>
    <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR" type="radio" name="filter-period-Caisse" id="filter-exercice-Caisse" value="exercice">
        <label class="form-check-label" for="filter-exercice-Caisse" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
    </div>

    <!-- Liste déroulante pour les mois -->
    <select id="periode-Caisse" style="font-size: 10px; width: 150px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;">
        <option value="selectionner un mois">Sélectionner un mois</option>
        <option value="janvier">Janvier</option>
        <option value="fevrier">Février</option>
        <option value="mars">Mars</option>
        <option value="avril">Avril</option>
        <option value="mai">Mai</option>
        <option value="juin">Juin</option>
        <option value="juillet">Juillet</option>
        <option value="aout">Août</option>
        <option value="septembre">Septembre</option>
        <option value="octobre">Octobre</option>
        <option value="novembre">Novembre</option>
        <option value="decembre">Décembre</option>
    </select>
    
    <!-- Input pour l'année -->
    <input type="text" id="annee-Caisse" readonly style="font-size: 10px; width: 90px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;" />

    <!-- Stocker la date d'exercice (dans l'attribut data-exercice-date) -->
    <div id="exercice-date" style="display: none;" data-exercice-date="{{ $societe->exercice_social_debut }}"></div>
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
            <button class="icon-button border-0 bg-transparent" id="delete-row-btn-caisse" title="Supprimer">
                <i class="fas fa-trash-alt text-danger" style="font-size: 14px;"></i>
            </button>

            <button class="icon-button border-0 bg-transparent" id="print-btn" title="Impression" onclick="printTable();">
    <i class="fas fa-print text-info" style="font-size: 14px;"></i>
</button>

        </div>
    </div>

    <!-- Table de la caisse -->
    <div id="table-Caisse" class="border rounded p-3 mt-2 bg-white shadow-sm">
        <!-- Contenu de la table -->
    </div>

    <!-- <input type="file" id="file-input" accept=".xlsx, .xls, .pdf"> -->
    
    <!-- Tableau pour afficher les données du fichier Excel -->
    <table id="excel-table" style="display: none;">
        <thead>
            <tr id="table-header"></tr>
        </thead>
        <tbody id="table-body"></tbody>
    </table>

    <!-- Paragraphe pour afficher les données du fichier Excel -->
    <p id="excel-content" style="display: none;"></p>

    <!-- Tableau pour afficher les données du fichier PDF -->
    <table id="pdf-table">
        <thead>
            <tr id="pdf-header"></tr>
        </thead>
        <tbody id="pdf-table-body"></tbody>
    </table>

    <!-- Zone pour afficher le contenu brut du fichier PDF -->
    <div id="pdf-content" style="display: none;"></div>



</div>


<!-- Onglet Banque -->
<div id="Banque" class="tab-content" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 10px; color: #333; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
    <div class="filter-container" style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">

        <!-- Code et Journal -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="journal-Banque" style="font-size: 11px; font-weight: bold;">Code :</label>
            <select id="journal-Banque" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;">
            <option value="">Sélectionner un journal</option>

            </select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-Banque" readonly placeholder="Journal" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
        </div>

        <!-- Saisie par -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label style="font-size: 11px; font-weight: bold;">Saisie par :</label>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-Banque" id="filter-contre-partie-Banque" value="contre-partie" checked>
                <label class="form-check-label" for="filter-contre-partie-Banque" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Contre Partie Auto</label>
            </div>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-Banque" id="filter-libre-Banque" value="libre">
                <label class="form-check-label" for="filter-libre-Banque" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Libre</label>
            </div>
        </div>

    <!-- Période -->
<div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
    <label for="periode-Banque" style="font-size: 11px; font-weight: bold;">Période :</label>
    
    <!-- Choix du type de période -->
    <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR" type="radio" name="filter-period-Banque" id="filter-mois-Banque" value="mois" checked>
        <label class="form-check-label" for="filter-mois-Banque" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
    </div>
    <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR" type="radio" name="filter-period-Banque" id="filter-exercice-Banque" value="exercice">
        <label class="form-check-label" for="filter-exercice-Banque" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
    </div>

    <!-- Liste déroulante pour les mois -->
    <select id="periode-Banque" style="font-size: 10px; width: 150px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;">
        <option value="selectionner un mois">Sélectionner un mois</option>
        <option value="janvier">Janvier</option>
        <option value="fevrier">Février</option>
        <option value="mars">Mars</option>
        <option value="avril">Avril</option>
        <option value="mai">Mai</option>
        <option value="juin">Juin</option>
        <option value="juillet">Juillet</option>
        <option value="aout">Août</option>
        <option value="septembre">Septembre</option>
        <option value="octobre">Octobre</option>
        <option value="novembre">Novembre</option>
        <option value="decembre">Décembre</option>
    </select>
    
    <!-- Input pour l'année -->
    <input type="text" id="annee-Banque" readonly style="font-size: 10px; width: 90px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;" />

    <!-- Stocker la date d'exercice (dans l'attribut data-exercice-date) -->
    <div id="exercice-date" style="display: none;" data-exercice-date="{{ $societe->exercice_social_debut }}"></div>
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
            <button class="icon-button border-0 bg-transparent" id="delete-row-btn_Banque" title="Supprimer">
                <i class="fas fa-trash-alt text-danger" style="font-size: 14px;"></i>
            </button>
            <button class="icon-button border-0 bg-transparent" id="print-btn" title="Impression" onclick="window.print();">
        <i class="fas fa-print text-info" style="font-size: 14px;"></i>
    </button>

        </div>
    </div>

    <!-- Table de la banque -->
    <div id="tableBanque" class="border rounded p-3 mt-2 bg-white shadow-sm">
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
            <input type="text" id="filter-intitule-operations-diverses" readonly placeholder="Journal"
                   style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
        </div>

        <!-- Saisie par -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label style="font-size: 11px; font-weight: bold;">Saisie par :</label>
              <!-- Filtres -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-operations-diverses" value="contre-partie" id="filter-contre-partie-operations-diverses" checked>
                <label class="form-check-label" for="filter-contre-partie-operations-diverses"
                       style="font-size: 9px; font-weight: 600; margin-left: 5px;">Contre Partie Auto</label>
            </div>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-operations-diverses" value="libre" id="filter-libre-operations-diverses">
                <label class="form-check-label" for="filter-libre-operations-diverses"
                       style="font-size: 9px; font-weight: 600; margin-left: 5px;">Libre</label>
            </div>
        </div>
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-period-operations-diverses" value="mois" id="filter-mois-operations-diverses" checked>
                <label class="form-check-label" for="filter-mois-operations-diverses"
                       style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
            </div>

            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-period-operations-diverses" value="exercice" id="filter-exercice-operations-diverses">
                <label class="form-check-label" for="filter-exercice-operations-diverses"
                       style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
            </div>
        </div>

        <!-- Période ou Année pour Opérations Diverses -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="periode-operations-diverses" style="font-size: 11px; font-weight: bold;">Période :</label>
            <select id="periode-operations-diverses" style="padding: 5px; width: 150px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;">
                <option value="selectionner un mois">Sélectionner un mois</option>
            </select>
            <input type="text" id="annee-operations-diverses" readonly
                   style="font-size: 10px; width: 90px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;" />
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
            <button class="icon-button border-0 bg-transparent" id="delete-row-btnOD" title="Supprimer">
                <i class="fas fa-trash-alt text-danger" style="font-size: 14px;"></i>
            </button>
            <a id="print-tableOp" href="#" title="Imprimer la table" class="text-dark">
                <i class="fa fa-print" style="font-size: 16px;"></i>
            </a>
        </div>
    </div>

    <!-- Table des opérations diverses -->
    <div id="table-operations-diverses" class="border rounded p-3 mt-2 bg-white shadow-sm">
        <!-- Contenu de la table -->
    </div>
</div>

<!-- Modal Bootstrap pour l'upload -->
<!-- N'oubliez pas d'inclure FontAwesome pour l'icône (par exemple via CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Bootstrap JS -->

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Bootstrap JS Bundle (inclut Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Tabulator JS (dernière version stable 6.3.4) -->
  <script src="https://unpkg.com/tabulator-tables@6.1.0/dist/js/tabulator.min.js"></script>

  <!-- Luxon JS (pour la gestion des dates) -->
  <script src="https://cdn.jsdelivr.net/npm/luxon@3.1.0/build/global/luxon.min.js"></script>

  <!-- Flatpickr JS -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <!-- SheetJS (XLSX) -->
  <script src="https://oss.sheetjs.com/sheetjs/xlsx.full.min.js"></script>

  <!-- jsPDF (dernière version de jspdf.umd) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

  <!-- jsPDF Autotable -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>

  <!-- Select2 JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>


    <script>
        window.comptesClients = window.comptesClients || [];

        </script>
        <script>
            window.nombreChiffresCompte = {{ $societe->nombre_chiffre_compte }};
          </script>
  {{-- <pre>
    societe_id: {{ $societe->id }}<br>
    nombre_chiffre_compte: {{ $societe->nombre_chiffre_compte }}
</pre> --}}

<!-- Injection directe des variables globales -->
<script>
    var societeId = {{ $societe->id }};
    var nombreChiffresCompte = {{ $societe->nombre_chiffre_compte }};
    console.log("Injection depuis Blade - societeId:", societeId, "nombreChiffresCompte:", nombreChiffresCompte);
</script>

    <script type="text/javascript" src="{{URL::asset('js/Operation_Courante.js')}}"></script>
   
<!-- Modale pour afficher les fichiers -->
<div id="fileModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Choisir un fichier</h2>
        <div class="modal-body">
            <ul id="fileList">
                @if(isset($files) && $files->count() > 0)
                    @foreach($files as $file)
                        <li>
                            <button class="file-button" data-filename="{{ $file->name }}">
                                <i class="fas fa-file-alt"></i> {{ $file->name }}
                            </button>
                        </li>
                    @endforeach
                @else
                    <li>Aucun fichier disponible</li>
                @endif
            </ul>
        </div>
        <div class="modal-footer">
            <button class="cancel-btn">Annuler</button>
            <button id="confirmBtn" class="confirm-btn">Confirmer</button>
        </div>
    </div>
</div>
<div id="files_banque_Modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Choisir un fichier</h2>
        <div class="modal-body">
            <ul id="fileList">
                @if(isset($files_banque) && $files_banque->count() > 0)
                    @foreach($files_banque as $file)
                        <li>
                            <button class="file-button" data-filename="{{ $file->name }}">
                                <i class="fas fa-file-alt"></i> {{ $file->name }}
                            </button>
                        </li>
                    @endforeach
                @else
                    <li>Aucun fichier disponible</li>
                @endif
            </ul>
        </div>
        <div class="modal-footer">
            <button class="cancel-btn">Annuler</button>
            <!-- Nouveau bouton Confirmer -->
            <button id="confirmBtn_Banque" class="confirm-btn">Confirmer</button>
        </div>
    </div>
</div>

<div id="fileAchatModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Choisir un fichier</h2>
        <div class="modal-body">
            <ul id="fileList">
                @if(isset($files_achat) && $files_achat->count() > 0)
                    @foreach($files_achat as $file)
                        <li>
                            <button class="file-button" data-filename="{{ $file->name }}">
                                <i class="fas fa-file-alt"></i> {{ $file->name }}
                            </button>
                        </li>
                    @endforeach
                @else
                    <li>Aucun fichier disponible</li>
                @endif
            </ul>
        </div>
        <div class="modal-footer">
            <button class="cancel-btn">Annuler</button>
            <!-- Nouveau bouton Confirmer -->
            <button id="confirmBtn" class="confirm-btn">Confirmer</button>
        </div>
    </div>
</div>
<script>
    var planComptable = @json($planComptable);
    
</script>


@endsection
