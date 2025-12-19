@extends('layouts.user_type.auth')

@section('content')

    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="societe_id" content="{{ session('societeId') }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title> Opérations Courantes</title>

<!-- Tabulator Bootstrap5 theme -->

<!-- Tabulator Bootstrap5 theme -->
<link
  href="https://unpkg.com/tabulator-tables@5.5.0/dist/css/tabulator_bootstrap5.min.css"
  rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


  <!-- Bootstrap CSS (dernière version Bootstrap 5.3.0) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- SweetAlert2 CSS (dernière version) -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

  <!-- FontAwesome CSS (dernière version, ici 6.0.0) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

  <!-- Tabulator CSS (dernière version stable 6.3.4) -->
  {{-- <link href="https://unpkg.com/tabulator-tables@6.1.0/dist/css/tabulator_bootstrap5.min.css" rel="stylesheet"> --}}

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

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
<!-- Inclure XLSX.js -->


<script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Votre script -->
<!-- Aucun script JS chargé par défaut -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.11/jspdf.plugin.autotable.min.js"></script>




 <style>

.save-button {
  background-color: #28a745 !important;
  color: white !important;
  border-radius: 4px;
  margin-left: 0.5rem;
}

.save-button:hover {
  background-color: #218838 !important;
}



.overlay-spinner {
  position: fixed; top:0; left:0; right:0; bottom:0;
  background: rgba(255,255,255,0.7);
  display:flex; align-items:center; justify-content:center;
  z-index: 9999;
}
.spinner {
  width:40px; height:40px;
  border:4px solid #ccc;
  border-top-color:#007bff;
  border-radius:50%;
  animation: spin 1s linear infinite;
}
@keyframes spin { to{transform: rotate(360deg);} }


.tabulator-cell {
  padding: 2px 5px;
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
/* Réduction de la taille de police dans les cellules */
.tabulator .tabulator-cell {
  font-size: 12px;
  padding: 2px 4px;
  line-height: 1.2;
}

/* Réduction de la taille de police dans les en-têtes */
.tabulator .tabulator-header .tabulator-col {
  font-size: 12px;
  padding: 2px 4px;
}

/* Réduction de la hauteur minimale des lignes */
.tabulator .tabulator-row {
  min-height: 24px;
}

/* Réduction des éditeurs dans les cellules (input, select, textarea) */
.tabulator .tabulator-cell input,
.tabulator .tabulator-cell select,
.tabulator .tabulator-cell textarea {
  font-size: 11px;
  padding: 2px 4px;
  height: 22px;
}

/* Réduction des boutons personnalisés dans les cellules */
.tabulator .tabulator-cell button {
  font-size: 11px;
  padding: 2px 6px;
  height: 22px;
  line-height: 1;
}

/* Optionnel : réduire la taille des icônes si présentes */
.tabulator .tabulator-icon {
  font-size: 12px;
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

  /* Simuler visuellement le focus persistant sur l'éditeur */
  .persistent-focus {
      outline: 2px solid blue;
      /* Vous pouvez ajuster les styles (couleur, épaisseur) selon vos préférences */
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



    .overlay-spinner { position:absolute; top:0; left:0; right:0; bottom:0;
  background:rgba(255,255,255,0.8); display:flex; align-items:center; justify-content:center; z-index:1000; }
.spinner { width:40px; height:40px; border:4px solid #ccc; border-top-color:#007bff;
  border-radius:50%; animation:spin 1s linear infinite; }
@keyframes spin{ to{transform:rotate(360deg);} }

    </style>



<body>

<br><br><br>

        <div class="tabs" style="display: flex; gap: 5px; margin-bottom: 10px; border-bottom: 2px solid #ccc;">
            <!-- Onglet Achats -->
          <div class="tab active" data-tab="achats"
            tabindex="0" style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #007bff; transition: background-color 0.3s, border-color 0.3s; box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);">
                Achats
            </div>

            <!-- Onglet Ventes -->
            <div class="tab" data-tab="ventes"
            tabindex="0" style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; transition: background-color 0.3s, border-color 0.3s;">
                Ventes
            </div>
            
     <!-- Onglet banque -->
             <div class="tab" data-tab="Banque"
             tabindex="0" style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; transition: background-color 0.3s, border-color 0.3s;">
            Banque
        </div>

            <!-- Onglet caisse -->
            <div class="tab" data-tab="Caisse"
            tabindex="0" style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; transition: background-color 0.3s, border-color 0.3s;">
                Caisse
            </div>



            <!-- Onglet Opérations Diverses -->
            <div class="tab" data-tab="operations-diverses"
            tabindex="0" style="font-size: 9px; padding: 4px 8px; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; transition: background-color 0.3s, border-color 0.3s;">
                Opérations Diverses
            </div>
        </div>

<!-- 
<script>
// Fonction pour charger dynamiquement un script JS
function loadScript(scriptUrl) {
  if (document.querySelector('script[data-dynamic="' + scriptUrl + '"]')) return; // déjà chargé
  const script = document.createElement('script');
  script.src = scriptUrl;
  script.setAttribute('data-dynamic', scriptUrl);
  document.body.appendChild(script);
}

// Fonction pour charger dynamiquement un script JS et annuler les autres scripts dynamiques
function loadScriptExclusive(scriptUrl) {
    // Supprimer tous les scripts dynamiques précédemment chargés
    document.querySelectorAll('script[data-dynamic]').forEach(s => s.remove());
    // Charger le nouveau script
    const script = document.createElement('script');
    script.src = scriptUrl;
    script.setAttribute('data-dynamic', scriptUrl);
    document.body.appendChild(script);
}

// Gestionnaire d'événements pour les onglets
// S'assurer que le DOM est prêt
  window.addEventListener('DOMContentLoaded', function() {
    // Chargement automatique du script si l'onglet actif est achats, ventes ou operations-diverses
    var activeTab = document.querySelector('.tab.active');
    if (activeTab) {
      var tabName = activeTab.getAttribute('data-tab');
      if (["achats", "ventes", "operations-diverses"].includes(tabName)) {
        console.log('tab active: achat');
        loadScriptExclusive('js/Operation_Courante.js');
      }
      if (tabName === "Banque") {
        loadScriptExclusive('js/Operation_Banque.js');
      }
      if (tabName === "Caisse") {
        loadScriptExclusive('js/Operation_Caisse_Banque.js');
      }
    }

    document.querySelectorAll('.tab').forEach(tab => {
      tab.addEventListener('click', function() {
        document.querySelectorAll('.tab-content').forEach(content => {
          content.classList.remove('active');
        });
        const activeTabContent = document.getElementById(this.getAttribute('data-tab'));
        if (activeTabContent) activeTabContent.classList.add('active');

        // Charger le script correspondant à l'onglet
        switch (this.getAttribute('data-tab')) {
          case 'Banque':
            loadScriptExclusive('js/Operation_Banque.js');
            break;
          case 'Caisse':
            loadScriptExclusive('js/Operation_Caisse_Banque.js');
            break;
          case 'achats':
          case 'ventes':
          case 'operations-diverses':
            loadScriptExclusive('js/Operation_Courante.js');
            break;
          // Ajoutez d'autres cas si besoin
        }
      });
    });
  });
</script> -->
<!-- <script>
    let currentScript = null;

    function loadScriptWithCallback(src, callbackName) {
        if (currentScript) {
            currentScript.remove();
            currentScript = null;
            console.log("Ancien script supprimé");
        }

        const script = document.createElement('script');
        script.src = src + '?v=' + Date.now(); // force le rechargement
        script.type = 'text/javascript';
        script.async = false; // IMPORTANT : synchronise le chargement
        script.onload = () => {
            console.log("Script chargé :", src);
            if (typeof window[callbackName] === 'function') {
                window[callbackName](); // appelle la fonction du script
            } else {
                console.error(`Fonction ${callbackName} non trouvée`);
            }
        };

        document.body.appendChild(script);
        currentScript = script;
    }

    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const tabName = tab.getAttribute('data-tab').toLowerCase();

            if (tabName === 'banque') {
                loadScriptWithCallback('js/Operation_Banque.js');
            } else if (tabName === 'caisse') {
                loadScriptWithCallback('js/Operation_Banque.js');
            } else {
                loadScriptWithCallback('js/Operation_Courante.js', 'afficherCourant');
            }
        });
    });
</script>
 -->

<!-- Onglet Achats -->
<div id="achats" class="tab-content active" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 10px; color: #333; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
  <div class="filter-container" style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">

    <!-- Code et Journal -->
    <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
      <label for="journal-achats" style="font-size: 11px; font-weight: bold;">journal:</label>
      <select id="journal-achats" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;"></select>
      <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">
      <input type="text" id="filter-intitule-achats" readonly placeholder="Journal" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
    </div>
      <label for="filter-achats" style="font-size: 11px; font-weight: bold;">Saisie par:</label>

    <!-- Saisie par -->
    <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
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

      <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR " type="radio" name="filter-period-achats" id="filter-mois-achats" value="mois" checked>
        <label class="form-check-label" for="filter-mois-achats" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
      </div>
      <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR" type="radio" name="filter-period-achats" id="filter-exercice-achats" value="exercice">
        <label class="form-check-label" for="filter-exercice-achats" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
      </div>
</div>
      <label for="periode-achats" style="font-size: 11px; font-weight: bold;">Période:</label>

    <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
      <select id="periode-achats" style="font-size: 10px; width: 150px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;">
        <option value="selectionner un mois">Sélectionner un mois</option>
      </select>
      <input type="text" id="annee-achats" readonly style="font-size: 10px; width: 90px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;" />
    </div>

    <!-- Boutons avec icônes -->
    <div style="display: flex; align-items: center; gap: 12px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
      <button id="import-achats" class="icon-button border-0 bg-transparent" title="Importer">
        <i class="fas fa-file-import text-success" style="font-size:14px;"></i>
      </button>
      <button id="download-xlsx" class="icon-button border-0 bg-transparent" title="Exporter Excel">
        <i class="fas fa-file-excel text-primary" style="font-size:14px;"></i>
      </button>
      <button id="download-pdf" class="icon-button border-0 bg-transparent" title="Exporter PDF">
        <i class="fas fa-file-pdf text-danger" style="font-size:14px;"></i>
      </button>
      <button id="delete-row-btnAch" class="icon-button border-0 bg-transparent" title="Supprimer">
        <i class="fas fa-trash-alt text-danger" style="font-size:14px;"></i>
      </button>
      <!-- <a id="print-table" href="#" class="text-dark" title="Imprimer">
        <i class="fa fa-print" style="font-size:16px;"></i>
      </a> -->
    </div>
  </div>

  <!-- Conteneur Tabulator Achats -->
  <div id="table-achats" style="height:400px;"></div>
  <script src="https://unpkg.com/tabulator-tables@5.5.0/dist/js/tabulator.min.js"></script>



</div>

<!-- Modal de mapping & import -->
<div id="mapping-modal" class="custom-modal">
  <div class="modal-content">
    <h3>Importer un fichier Excel et mapper les colonnes</h3>
    <div style="margin-bottom:12px;">
      <input type="file" id="modal-excel-input" accept=".xls,.xlsx" />
    </div>
    <form id="mapping-form"></form>
    <div class="buttons">
      <button id="mapping-cancel" type="button">Annuler</button>
      <button id="mapping-confirm" type="button" disabled>Confirmer</button>
    </div>
  </div>
</div>

<!-- Styles pour le modal -->
<style>
.custom-modal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0; top: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
}

.custom-modal .modal-content {
  background: #fff;
  padding: 24px;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  text-align: center;
}

.custom-modal .buttons {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
}

.custom-modal .buttons button {
  padding: 8px 14px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

#mapping-cancel {
  background: #ccc;
}

#mapping-confirm {
  background: #28a745;
  color: white;
}
</style>

<!-- Script pour afficher/fermer le modal -->
<script>
  document.getElementById('import-achats').addEventListener('click', () => {
    document.getElementById('mapping-modal').style.display = 'flex';
  });

  document.getElementById('mapping-cancel').addEventListener('click', () => {
    document.getElementById('mapping-modal').style.display = 'none';
  });

  document.getElementById('mapping-modal').addEventListener('click', function (e) {
    if (e.target.id === 'mapping-modal') {
      this.style.display = 'none';
    }
  });
</script>

<!-- Ajouter le lien vers la bibliothèque Font Awesome -->


<!-- Onglet Ventes -->
<div id="ventes" class="tab-content" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 10px; color: #333; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
    <div class="filter-container" style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">
        <!-- Code et Journal -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="journal-ventes" style="font-size: 11px; font-weight: bold;">journal:</label>
            <select id="journal-ventes" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-ventes" readonly placeholder="Journal" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
        </div>

        <!-- Saisie par -->
                     <label style="font-size: 11px; font-weight: bold;">Saisie par:</label>

        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-ventes" id="filter-contre-partie-ventes" value="contre-partie" checked>
                <label class="form-check-label" for="filter-contre-partie-ventes" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Contre Partie Auto</label>
            </div>
     
            <div class="form-check form-check-inline" style="font-size: 9px;">
                <input class="formR" type="radio" name="filter-ventes" id="filter-libre-ventes" value="libre">
                <label class="form-check-label" for="filter-libre-ventes" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Libre</label>
            </div>
   </div>

        <!-- Période -->
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">

                <div class="form-check form-check-inline" style="font-size: 9px;">
                    <input class="formR" type="radio" name="filter-period-ventes" id="filter-mois-ventes" value="mois" checked>
                    <label class="form-check-label" for="filter-mois-ventes" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
                </div>
                <div class="form-check form-check-inline" style="font-size: 9px;">
                    <input class="formR" type="radio" name="filter-period-ventes" id="filter-exercice-ventes" value="exercice">
                    <label class="form-check-label" for="filter-exercice-ventes" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
                </div>
                </div>
                <!-- Mois Sélection -->
                             <div style="display: flex; gap: 8px; align-items: center;">
            <label for="filter-period-ventes" style="font-size: 11px; font-weight: bold;">Période:</label>

                     <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
                <select id="periode-ventes" style="font-size: 10px; width: 150px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="selectionner un mois">Sélectionner un mois</option>
                </select>
            </div>
            <input type="text" id="annee-ventes" readonly style="font-size: 10px; width: 90px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;" />
        </div>


        <!-- Boutons avec icônes -->
        <div style="display: flex; align-items: center; gap: 12px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
            <button id="import-ventes"     class="icon-button border-0 bg-transparent" title="Importer">
              <i class="fas fa-file-import text-success" style="font-size:14px;"></i>
            </button>
            <button id="export-ventesExcel" class="icon-button border-0 bg-transparent" title="Exporter Excel">
              <i class="fas fa-file-excel text-primary" style="font-size:14px;"></i>
            </button>
            <button id="export-ventesPDF"   class="icon-button border-0 bg-transparent" title="Exporter PDF">
              <i class="fas fa-file-pdf text-danger" style="font-size:14px;"></i>
            </button>
            <button id="delete-row-btnVte"  class="icon-button border-0 bg-transparent" title="Supprimer">
              <i class="fas fa-trash-alt text-danger" style="font-size:14px;"></i>
            </button>
            <!-- <a      id="print-tableV"       href="#" class="text-dark" title="Imprimer la table">
              <i class="fa fa-print" style="font-size:16px;"></i>
            </a> -->
          </div>

          <!-- Input caché pour sélectionner le fichier Excel Ventes -->
          {{-- <input type="file" id="excel-file-ventes" accept=".xls, .xlsx" style="display: none;" /> --}}
    </div>

    <!-- Table des ventes -->
    <div id="table-ventes" class="border rounded p-3 mt-2 bg-white shadow-sm">
        <!-- Contenu de la table -->
    </div>
    <div id="mapping-modal-ventes" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
        <div style="background:#fff;padding:20px;border-radius:8px;width:90%;max-width:600px;max-height:80vh;overflow:auto;">
          <h3>Importer Excel Ventes – Mapper colonnes</h3>
          <div style="margin:12px 0;"><input type="file" id="modal-excel-input-ventes" accept=".xls,.xlsx"></div>
          <form id="mapping-form-ventes"></form>
          <div style="text-align:right;margin-top:12px;"><button id="mapping-cancel-ventes">Annuler</button><button id="mapping-confirm-ventes" disabled>Confirmer</button></div>
        </div>
      </div>
</div>



<!-- Onglet caisse -->
<div id="Caisse" class="tab-content" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 10px; color: #333; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
    <div class="filter-container" style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">
<!-- Code et Journal -->
<div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
    <label for="journal-Caisse" style="font-size: 11px; font-weight: bold;">journal:</label>
    <select id="journal-Caisse" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;">
        <option value="">Sélectionner un journal</option> <!-- Option par défaut -->
    </select>
    <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">
    <input type="text" id="filter-intitule-Caisse" readonly placeholder="Journal" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
</div>

        <!-- Saisie par -->
                     <label style="font-size: 11px; font-weight: bold;">Saisie par:</label>

        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
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


    <!-- Choix du type de période -->
             <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">

    <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR" type="radio" name="filter-period-Caisse" id="filter-mois-Caisse" value="mois" checked>
        <label class="form-check-label" for="filter-mois-Caisse" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
    </div>
    <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR" type="radio" name="filter-period-Caisse" id="filter-exercice-Caisse" value="exercice">
        <label class="form-check-label" for="filter-exercice-Caisse" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
    </div>
</div>
    <!-- Liste déroulante pour les mois -->
         <label for="periode-Caisse" style="font-size: 11px; font-weight: bold;">Période:</label>

    <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
    <select id="periode-Caisse" style="font-size: 10px; width: 150px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;">
        <option value="selectionner un mois">Sélectionner un mois</option>
        <option value="1">Janvier {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="2">Février {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="3">Mars {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="4">Avril {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="5">Mai {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="6">Juin {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="7">Juillet {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="8">Août {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="9">Septembre {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="10">Octobre {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="11">Novembre {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="12">Décembre {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
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
<i class="fas fa-share" id="transfereCaisse"></i>

            <button class="icon-button border-0 bg-transparent" id="modifier-compte-caisse" title="Remplacer comptes">
                    <i class="fas fa-exchange-alt"></i>    
                    <!-- modifier compte -->
            </button>
            <!-- <button class="icon-button border-0 bg-transparent" id="print-btn" title="Impression" onclick="printTable();">
    <i class="fas fa-print text-info" style="font-size: 14px;"></i>
</button> -->

        </div>
        <label for="solde-actuel-Caisse">Solde Actuel:</label>
                <input type="text" id="solde-actuel-Caisse" disabled style="width:20px;"/>

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
            <label for="journal-Banque" style="font-size: 11px; font-weight: bold;">journal:</label>
            <select id="journal-Banque" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;">
            <option value="">Sélectionner un journal</option>

            </select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-Banque" readonly placeholder="Journal" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
        </div>

        <!-- Saisie par -->
                     <label style="font-size: 11px; font-weight: bold;">Saisie par:</label>

        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
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


    <!-- Choix du type de période -->
    <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
    <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR" type="radio" name="filter-period-Banque" id="filter-mois-Banque" value="mois" checked>
        <label class="form-check-label" for="filter-mois-Banque" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
    </div>
    <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR" type="radio" name="filter-period-Banque" id="filter-exercice-Banque" value="exercice">
        <label class="form-check-label" for="filter-exercice-Banque" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
    </div>
</div>
    <!-- Liste déroulante pour les mois -->
         <label for="periode-Banque" style="font-size: 11px; font-weight: bold;">Période:</label>
    <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
    <select id="periode-Banque" style="font-size: 10px; width: 150px; padding: 5px; border: 1px solid #ccc; border-radius: 5px;">
        <option value="selectionner un mois">Sélectionner un mois</option>
        <option value="1">Janvier {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="2">Février {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="3">Mars {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="4">Avril {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="5">Mai {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="6">Juin {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="7">Juillet {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="8">Août {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="9">Septembre {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="10">Octobre {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="11">Novembre {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
        <option value="12">Décembre {{ date('Y', strtotime($societe->exercice_social_debut)) }}</option>
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
            <i class="fas fa-share" id="transfereBanque"></i>

            
            <button class="icon-button border-0 bg-transparent" id="modifier-compte-banque" title="Remplacer comptes">
              <i class="fas fa-exchange-alt"></i>    
            <!-- modifier compte -->
            </button>
            <!-- <button class="icon-button border-0 bg-transparent" id="print-btn" title="Impression" onclick="window.print();">
        <i class="fas fa-print text-info" style="font-size: 14px;"></i>
    </button> -->

        </div>
        <label for="solde-actuel">Solde Actuel:</label>
        <input type="text" id="solde-actuel" disabled style="width:20px;"/>
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
            <label for="journal-operations-diverses" style="font-size: 11px; font-weight: bold;">journal:</label>
            <select id="journal-operations-diverses" style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">
            <input type="text" id="filter-intitule-operations-diverses" readonly placeholder="Journal"
                   style="padding: 4px; width: 110px; border: 1px solid #ccc; border-radius: 5px; font-size: 10px;" />
        </div>

        <!-- Saisie par -->
                     <label style="font-size: 11px; font-weight: bold;">Saisie par:</label>

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
                <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">

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
                     <label for="periode-operations-diverses" style="font-size: 11px; font-weight: bold;">Période:</label>
        <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <select id="periode-operations-diverses" style="padding: 5px; width: 150px; border: 1px solid #ccc; border-radius: 5px;">
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
            <!-- <a id="print-tableOp" href="#" title="Imprimer la table" class="text-dark">
                <i class="fa fa-print" style="font-size: 16px;"></i>
            </a> -->
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
  {{-- <script src="https://cdn.jsdelivr.net/npm/luxon@3.1.0/build/global/luxon.min.js"></script> --}}

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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>


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
    var assujettiePartielleTVA = @json($societe->assujettie_partielle_tva);

    console.log("Injection depuis Blade - societeId:", societeId,
                "nombreChiffresCompte:", nombreChiffresCompte,
                "assujettiePartielleTVA:", assujettiePartielleTVA);
</script>

  <!-- L'appel à Operation_Courante.js est supprimé, il ne sera chargé que si nécessaire -->

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







<div id="file_achat_Modal" class="modal" style="display: none;">
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
            <button id="confirmBtnAchat" class="confirm-btn">Confirmer</button>
        </div>
    </div>
</div>
<div id="file_vente_Modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Choisir un fichier</h2>
        <div class="modal-body">
            <ul id="fileList">
                @if(isset($files_vente) && $files_vente->count() > 0)
                    @foreach($files_vente as $file)
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
            <button id="confirmBtnVente" class="confirm-btn">Confirmer</button>
        </div>
    </div>
</div>
<script>
    var planComptable = @json($planComptable);

</script>


<script>
  function getEditUrl(id) {
    return "{{ url('Operation_Courante') }}/" + id + "/edit";
  }

  
</script>




<style>
  /* Modal principal */
  #files_banque_Modal.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0; top: 0;
    width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.45);
    justify-content: center; align-items: center;
    transition: background 0.3s;
  }

  /* Contenu du modal */
  #files_banque_Modal .modal-content {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    max-width: 1000px;
    width: 80%;
    height: 80%;
    margin: 5% auto;
    padding: 20px;
    font-size: 1em;
    color: #34495e;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
  }

  /* Hover sur boutons */
  #files_banque_Modal .file-button:hover,
  #files_banque_Modal .file-button.selected {
    background: #e3f2fd;
    color: #1976d2;
  }

  /* Responsive mobile */
  @media (max-width: 600px) {
    #files_banque_Modal .modal-content {
      max-width: 98vw;
      padding: 18px 6vw;
    }
  }

  /* Breadcrumbs */
  #banqueBreadcrumb span {
    cursor: pointer;
    color: #1976d2;
  }
  #banqueBreadcrumb span:hover {
    text-decoration: underline;
  }

  .folder-button {
    padding: 0.5rem; 
    background-color: #ffc107; 
    border-radius: 17px;
    color: #fff; 
    border: none; 
    width: 100%; 
    height: 100%;
    cursor: pointer;
  }

  .file-card img, .file-card canvas {
    height: 200px;
    object-fit: cover;
  }
  #fileListBanque {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    padding-left: 0;
    margin: 0;
  }
  .file-card {
    display: none; /* masqué par défaut, puis affiché via JS */
  }
  
</style>

<!-- ✅ Font Awesome pour les icônes -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div id="files_banque_Modal" class="modal">
  <div class="modal-content">
   <span id="closeModal" 
          style="position: absolute; top: 10px; right: 15px; 
                 font-size: 24px; font-weight: bold; cursor: pointer; color: #333;">
      &times;
    </span>
    <!-- 🔹 Navigation Dashboard -->
    <div style="margin-bottom:1rem;">
      <a href="#" id="backToDashboard"
         style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">
         <!-- Tableau De Board -->
      </a>
      <span style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">
        <!-- ➢ -->
      </span>
    </div>

    <!-- Dashboard initial -->
    <div id="dashboardSection" style="display: flex; flex-wrap: wrap; gap: 20px;">
      <div id="TableauDeBordBanque" class="p-2 text-white"
           style="background-color: #ffc107; border-radius: 15px; font-size: 0.75rem;
                  height: 130px; cursor: pointer; width:30%; display:flex; align-items:center; justify-content:center; flex-direction:column;">
        <h4>Banque</h4>
      </div>

      <!-- Dossiers manuels -->
      @if(isset($dossierManuel) && $dossierManuel->count() > 0)
        @foreach($dossierManuel as $dossier)
          <div class="dossierManuelItem text-white"
               data-dossierid="{{ $dossier->id }}"
               data-dossiername="{{ $dossier->name }}"
               style="background-color: #17a2b8; border-radius: 15px;
                      font-size: 0.75rem; height: 130px; width:30%;
                      cursor: pointer; display:flex; align-items:center;
                      justify-content:center; flex-direction:column;">
            <h4>{{ $dossier->name }}</h4>
          </div>
        @endforeach
      @else
        <p>Aucun dossier manuel disponible.</p>
      @endif
    </div>

    <!-- Contenu dynamique banque/dossier -->
    <div id="banqueContent" style="display: none;">
      <nav id="banqueBreadcrumb" style="margin-bottom: 1rem; font-size: 14px;"></nav>
   
      <div id="filterZone" 
           style="display: flex; justify-content: flex-end; align-items: center; margin-top: 2rem; gap: 10px;">
        <label for="sortFiles" style="font-weight: bold;">Trier par :</label>
        <select id="sortFiles" 
                style="padding: 5px; border-radius: 5px; border: 1px solid #ccc;">
          <option value="name">Nom</option>
          <option value="date">Date</option>
        </select>

        <button id="sortToggle"
                title="Changer l’ordre de tri"
                style="display: flex; align-items: center; gap: 6px; 
                       background: #f8f9fa; border: 1px solid #ccc; border-radius: 6px;
                       cursor: pointer; font-size: 16px; color: #007bff; 
                       padding: 6px 12px; font-weight: bold; transition: all 0.3s;">
          <i class="fas fa-sort-amount-up"></i> 
          <span id="sortLabel">A → Z</span>
        </button>

        <!-- Icône de recherche -->
        <i class="fa-solid fa-magnifying-glass search-icon" id="search-icon"></i>
      </div>
 
      <h3>Dossiers</h3>
      <ul id="folderList" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; padding-left: 0; margin: 0;">
        @foreach($folders_banque as $folder)
          <li style="list-style-type: none;" data-typefolder="{{ $folder->type_folder }}">
            <button class="folder-button"
                    data-folderid="{{ $folder->id }}"
                    data-foldername="{{ $folder->name }}">
              <i class="fas fa-folder"></i> {{ $folder->name }}
            </button>
          </li>
        @endforeach
      </ul>

      <h3 style="margin-top: 2rem;">Fichiers</h3>
      <div id="fileListBanque" style="padding-left: 0; margin: 0;">
        @foreach($files_banque as $file)
          <div class="col file-card" data-type="{{ $file->type }}" 
               style="padding-bottom:32px; display:none;">
            <div class="card shadow-sm" 
                 style="width:13rem;height:250px;padding-bottom:16px;" 
                 data-fileid="{{ $file->id }}" 
                 data-filepath="{{ asset($file->path) }}">
              <div class="card-body text-center d-flex flex-column justify-content-between" 
                   style="padding:0.5rem;">
                @php $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION)); @endphp
                @if(in_array($ext, ['jpg','jpeg','png','gif']))
                  <img src="{{ asset($file->path) }}" alt="{{ $file->name }}" class="img-fluid mb-2">
                @elseif($ext == 'pdf')
                  <canvas class="img-fluid mb-2"></canvas>
                @else
                  <img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2">
                @endif
                <h5 class="card-title text-truncate" 
                    style="font-size:0.9rem;font-weight:bold;">{{ $file->name }}</h5>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("files_banque_Modal");
  const tableau = document.getElementById("TableauDeBordBanque");
  const banqueContent = document.getElementById("banqueContent");
  const dashboardSection = document.getElementById("dashboardSection");
  const backToDashboard = document.getElementById("backToDashboard");
  const breadcrumb = document.getElementById("banqueBreadcrumb");
  const folderList = document.getElementById("folderList");
  const fileList = document.getElementById("fileListBanque");

  let breadcrumbPath = [];

  // 🔹 Réinitialiser modal
  function resetModal() {
    modal.style.display = "none"; // <- fermeture réelle du modal
    dashboardSection.style.display = "flex";
    banqueContent.style.display = "none";
    breadcrumbPath = [];
    breadcrumb.innerHTML = "";

    // Réafficher dossiers manuels
    document.querySelectorAll(".dossierManuelItem").forEach(el => el.style.display = "flex");

    // Réinitialiser folders
    folderList.innerHTML = '';
    @foreach($folders_banque as $folder)
      folderList.innerHTML += `<li style="list-style:none" data-typefolder="{{ $folder->type_folder }}">
        <button class="folder-button" data-folderid="{{ $folder->id }}" data-foldername="{{ $folder->name }}">
          <i class="fas fa-folder"></i> {{ $folder->name }}
        </button>
      </li>`;
    @endforeach

    // Réinitialiser files
    fileList.innerHTML = '';
    @foreach($files_banque as $file)
      fileList.innerHTML += `<div class="col file-card" data-type="{{ $file->type }}" style="padding-bottom:32px; display:none;">
        <div class="card shadow-sm" style="width:13rem;height:250px;padding-bottom:16px;" 
             data-fileid="{{ $file->id }}" data-filepath="{{ asset($file->path) }}">
          <div class="card-body text-center d-flex flex-column justify-content-between" style="padding:0.5rem;">
            @php $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION)); @endphp
            @if(in_array($ext, ['jpg','jpeg','png','gif']))
              <img src="{{ asset($file->path) }}" alt="{{ $file->name }}" class="img-fluid mb-2">
            @elseif($ext == 'pdf')
              <canvas class="img-fluid mb-2"></canvas>
            @else
              <img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2">
            @endif
            <h5 class="card-title text-truncate" style="font-size:0.9rem;font-weight:bold;">{{ $file->name }}</h5>
          </div>
        </div>
      </div>`;
    @endforeach

    // Réinitialiser tri
    document.getElementById("sortFiles").value = "name";
    const sortLabel = document.getElementById("sortLabel");
    sortLabel.textContent = "A → Z";
    const sortIcon = document.querySelector("#sortToggle i");
    if(sortIcon) sortIcon.className = "fas fa-sort-amount-up";

    attachEvents();
  }

  // 🔹 Mettre à jour breadcrumb
  function updateBreadcrumb() {
    breadcrumb.innerHTML = breadcrumbPath.map((name, index) => {
      if(index < breadcrumbPath.length - 1){
        return `<span class="breadcrumb-folder" data-index="${index}">${name}</span> ➢ `;
      } else return `<span>${name}</span>`;
    }).join('');

    const tableauDeBoardSpan = document.createElement("span");
    tableauDeBoardSpan.textContent = "Tableau de Board";
    tableauDeBoardSpan.style.textDecoration = "underline";
    breadcrumb.insertAdjacentElement("afterbegin", tableauDeBoardSpan);

    document.querySelectorAll(".breadcrumb-folder").forEach(el => {
      el.addEventListener("click", function(){
        const idx = parseInt(this.dataset.index);
        breadcrumbPath = breadcrumbPath.slice(0, idx + 1);
        openContent(breadcrumbPath[breadcrumbPath.length-1]);
      });
    });
  }

  // 🔹 Ouvrir contenu
  function openContent(typeName) {
    dashboardSection.style.display = "none";
    banqueContent.style.display = "block";

    if(breadcrumbPath[breadcrumbPath.length-1] !== typeName) breadcrumbPath.push(typeName);
    updateBreadcrumb();

    document.querySelectorAll("#folderList li").forEach(li => {
      li.style.display = li.dataset.typefolder === typeName ? 'block' : 'none';
    });

    let hasFiles = false;
    document.querySelectorAll("#fileListBanque .file-card").forEach(f => {
      if(f.dataset.type === typeName){
        f.style.display = "block";
        hasFiles = true;
      } else f.style.display = "none";
    });

    fileList.querySelectorAll(".no-file-msg").forEach(el => el.remove());
    if(!hasFiles){
      const p = document.createElement("p");
      p.textContent = "Aucun fichier disponible.";
      p.classList.add("no-file-msg");
      fileList.appendChild(p);
    }
  }

  // 🔹 Charger sous-dossiers dynamiquement
  function loadFolder(folderId, folderName) {
    fileList.innerHTML = "<p>Chargement des fichiers...</p>";
    folderList.innerHTML = "<p>Chargement des sous-dossiers...</p>";

    fetch(`/operation-courante/select-folder?id=${folderId}`)
      .then(response => response.json())
      .then(data => {
        breadcrumbPath.push(folderName);
        updateBreadcrumb();

        folderList.innerHTML = "";
        if(data.folders_banque && data.folders_banque.length > 0){
          data.folders_banque.forEach(folder => {
            const li = document.createElement("li");
            li.style.listStyleType = "none";
            li.innerHTML = `<button class="folder-button" data-folderid="${folder.id}" data-foldername="${folder.name}">
                              <i class="fas fa-folder"></i> ${folder.name}
                            </button>`;
            folderList.appendChild(li);
            li.querySelector(".folder-button").addEventListener("dblclick", () => {
              loadFolder(folder.id, folder.name);
            });
          });
        } else folderList.innerHTML = "<p>Aucun sous-dossier.</p>";

        fileList.innerHTML = "";
        if(data.files_banque && data.files_banque.length > 0){
          data.files_banque.forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            let preview = "";
            if(["jpg","jpeg","png","gif"].includes(ext)) preview = `<img src="/${file.path}" class="img-fluid mb-2">`;
            else if(ext === "pdf") preview = `<canvas class="img-fluid mb-2"></canvas>`;
            else preview = `<img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2">`;

            const div = document.createElement("div");
            div.className = "col file-card";
            div.innerHTML = `<div class="card shadow-sm" style="width:13rem;height:250px;" data-fileid="${file.id}" data-filepath="/${file.path}">
                              <div class="card-body text-center d-flex flex-column justify-content-between" style="padding:0.5rem;">
                                ${preview}
                                <h5 class="card-title text-truncate" style="font-size:0.9rem;font-weight:bold;">${file.name}</h5>
                              </div>
                            </div>`;
            fileList.appendChild(div);
          });
        } else fileList.innerHTML = "<p>Aucun fichier disponible.</p>";

        document.getElementById("sortToggle").click();
        attachEvents();
      })
      .catch(err => console.error(err));
  }

  // 🔹 Attacher événements
  function attachEvents() {
    document.querySelectorAll(".folder-button").forEach(btn => {
      btn.addEventListener("dblclick", function() {
        loadFolder(this.dataset.folderid, this.dataset.foldername);
      });
    });

    document.querySelectorAll(".file-card").forEach(cardWrapper => {
      cardWrapper.addEventListener("dblclick", function () {
        const innerCard = this.querySelector('.card');
        const selectedFilePath = innerCard.dataset.filepath;
        if(selectedFilePath) window.open(selectedFilePath, '_blank');
      });
    });
  }

  // 🔹 Ouverture modal
  tableau.addEventListener("dblclick", () => {
    modal.style.display = "flex";
    breadcrumbPath = ['Banque'];
    openContent('banque');
  });

  document.querySelectorAll(".dossierManuelItem").forEach(item => {
    item.addEventListener("dblclick", function() {
      const dossierName = this.dataset.dossiername;
      modal.style.display = "flex";
      breadcrumbPath = ['Banque', dossierName];
      openContent(dossierName);
    });
  });

  // 🔹 Boutons fermeture modal
  backToDashboard.addEventListener("click", function(e){
    e.preventDefault();
    resetModal();
  });

  document.getElementById('closeModal').addEventListener('click', resetModal);
  modal.addEventListener("click", e => { if(e.target === modal) resetModal(); });

  attachEvents();
});
</script>









 





<style>
  /* Styles inchangés, je te les copie tel quel */
  #importModalBanque {
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5); 
    justify-content: center;
    align-items: center;
    z-index: 9999;
    overflow-y: auto;
    padding: 20px;
  }

  #importModalBanque .modal-content {
    background-color: #fff;
    border-radius: 8px;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    padding: 30px 40px;
    box-sizing: border-box;
    animation: fadeInScale 0.3s ease forwards;
  }

  @keyframes fadeInScale {
    0% {
      opacity: 0;
      transform: scale(0.95);
    }
    100% {
      opacity: 1;
      transform: scale(1);
    }
  }

  .modal-header {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 25px;
    text-align: center;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
  }

  #importForm label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #34495e;
    margin-top: 15px;
  }

  #importForm select,
  #importForm input[type="date"] {
    width: 100%;
    padding: 10px 12px;
    border: 1.8px solid #bdc3c7;
    border-radius: 5px;
    font-size: 1rem;
    color: #2c3e50;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
  }

  #importForm select:focus,
  #importForm input[type="date"]:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
  }

  /* Flex container for two inputs per row */
  #importForm .form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
  }

  /* Each input+label group */
  #importForm .form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  .buttons {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
    gap: 15px;
  }

  .buttons button {
    padding: 10px 25px;
    font-weight: 600;
    font-size: 1rem;
    border-radius: 6px;
    cursor: pointer;
    border: none;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }

  #cancelBtn {
    background-color: #e74c3c;
    color: white;
  }

  #cancelBtn:hover {
    background-color: #c0392b;
    box-shadow: 0 3px 8px rgba(192, 57, 43, 0.6);
  }

  .buttons button[type="submit"] {
    background-color: #3498db;
    color: white;
  }

  .buttons button[type="submit"]:hover {
    background-color: #2980b9;
    box-shadow: 0 3px 8px rgba(41, 128, 185, 0.6);
  }

  @media (max-height: 600px) {
    #importModalBanque {
      align-items: flex-start;
      padding-top: 40px;
      padding-bottom: 40px;
    }
  }
</style>
 

<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<div id="importModalBanque" class="modal">
  <div class="modal-content">
    <div class="modal-header">Importation</div>
 <form id="importForm" action="{{ route('importerOperationCouranteBanque') }}" method="POST" enctype="multipart/form-data">
  @csrf      
      <div class="form-row">
        <div class="form-group">
          <label for="importFile">Fichier à importer</label>
          <input type="file" id="importFile" name="importFile" accept=".csv, .xls, .xlsx" required />
        </div>
        <div class="form-group">
          <label for="date">Date (colonne)</label>
          <select id="date" name="date" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <!-- Tous les autres champs comme avant, en select -->

      <div class="form-row">
        <div class="form-group">
          <label for="modePaiement">Mode de paiement (colonne)</label>
          <select id="modePaiement" name="modePaiement" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
        <div class="form-group">
          <label for="compte">Compte (colonne)</label>
          <select id="compte" name="compte" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="libelle">Libellé (colonne)</label>
          <select id="libelle" name="libelle" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
        <div class="form-group">
          <label for="debit">Débit (colonne)</label>
          <select id="debit" name="debit" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="credit">Crédit (colonne)</label>
          <select id="credit" name="credit" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
        <div class="form-group">
          <label for="nFactureLettre">N° Facture Lettré (colonne)</label>
          <select id="nFactureLettre" name="nFactureLettre" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="tauxRasTva">Taux RAS TVA (%) (colonne)</label>
          <select id="tauxRasTva" name="tauxRasTva" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
        <div class="form-group">
          <label for="natureOperation">Nature de l'opération (colonne)</label>
          <select id="natureOperation" name="natureOperation" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="dateLettrage">Date de lettrage (colonne)</label>
          <select id="dateLettrage" name="dateLettrage" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
        <div class="form-group">
          <label for="contrePartie">Contre partie (colonne)</label>
          <select id="contrePartie" name="contrePartie" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <div class="buttons">
        <button type="button" id="cancelBtn">Annuler</button>
        <button type="submit">Importer</button>
      </div>
    </form>
  </div>
</div>



<style>
  /* Styles pour le modal Caisse */
  #importModalCaisse {
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5); 
    justify-content: center;
    align-items: center;
    z-index: 9999;
    overflow-y: auto;
    padding: 20px;
  }

  #importModalCaisse .modal-content {
    background-color: #fff;
    border-radius: 8px;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    padding: 30px 40px;
    box-sizing: border-box;
    animation: fadeInScale 0.3s ease forwards;
  }

  @keyframes fadeInScale {
    0% {
      opacity: 0;
      transform: scale(0.95);
    }
    100% {
      opacity: 1;
      transform: scale(1);
    }
  }

  .modal-header {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 25px;
    text-align: center;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
  }

  #importFormCaisse label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #34495e;
    margin-top: 15px;
  }

  #importFormCaisse select,
  #importFormCaisse input[type="date"],
  #importFormCaisse input[type="file"] {
    width: 100%;
    padding: 10px 12px;
    border: 1.8px solid #bdc3c7;
    border-radius: 5px;
    font-size: 1rem;
    color: #2c3e50;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
  }

  #importFormCaisse select:focus,
  #importFormCaisse input[type="date"]:focus,
  #importFormCaisse input[type="file"]:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
  }

  #importFormCaisse .form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
  }

  #importFormCaisse .form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  .buttons {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
    gap: 15px;
  }

  .buttons button {
    padding: 10px 25px;
    font-weight: 600;
    font-size: 1rem;
    border-radius: 6px;
    cursor: pointer;
    border: none;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }

  #cancelBtnCaisse {
    background-color: #e74c3c;
    color: white;
  }

  #cancelBtnCaisse:hover {
    background-color: #c0392b;
    box-shadow: 0 3px 8px rgba(192, 57, 43, 0.6);
  }

  .buttons button[type="submit"] {
    background-color: #3498db;
    color: white;
  }

  .buttons button[type="submit"]:hover {
    background-color: #2980b9;
    box-shadow: 0 3px 8px rgba(41, 128, 185, 0.6);
  }

  @media (max-height: 600px) {
    #importModalCaisse {
      align-items: flex-start;
      padding-top: 40px;
      padding-bottom: 40px;
    }
  }
</style>





<div id="importModalCaisse" class="modal">
  <div class="modal-content">
    <div class="modal-header">Importation - Caisse</div>
    <form id="importFormCaisse" action="importerOperationCouranteCaisse" method="POST" enctype="multipart/form-data">
      @csrf      
      <div class="form-row">
        <div class="form-group">
          <label for="importFileCaisse">Fichier à importer</label>
          <input type="file" id="importFileCaisse" name="importFileCaisse" accept=".csv, .xls, .xlsx" required />
        </div>
        <div class="form-group">
          <label for="dateCaisse">Date (colonne)</label>
          <select id="dateCaisse" name="dateCaisse" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <!-- Tous les autres champs comme avant, en select -->

      <div class="form-row">
        <div class="form-group">
          <label for="modePaiementCaisse">Mode de paiement (colonne)</label>
          <select id="modePaiementCaisse" name="modePaiementCaisse" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
        <div class="form-group">
          <label for="compteCaisse">Compte (colonne)</label>
          <select id="compteCaisse" name="compteCaisse" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="libelleCaisse">Libellé (colonne)</label>
          <select id="libelleCaisse" name="libelleCaisse" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
        <div class="form-group">
          <label for="debitCaisse">Débit (colonne)</label>
          <select id="debitCaisse" name="debitCaisse" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="creditCaisse">Crédit (colonne)</label>
          <select id="creditCaisse" name="creditCaisse" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
        <div class="form-group">
          <label for="nFactureLettreCaisse">N° Facture Lettré (colonne)</label>
          <select id="nFactureLettreCaisse" name="nFactureLettreCaisse" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="tauxRasTvaCaisse">Taux RAS TVA (%) (colonne)</label>
          <select id="tauxRasTvaCaisse" name="tauxRasTvaCaisse" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
        <div class="form-group">
          <label for="natureOperationCaisse">Nature de l'opération (colonne)</label>
          <select id="natureOperationCaisse" name="natureOperationCaisse" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="dateLettrageCaisse">Date de lettrage (colonne)</label>
          <select id="dateLettrageCaisse" name="dateLettrageCaisse" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
        <div class="form-group">
          <label for="contrePartieCaisse">Contre partie (colonne)</label>
          <select id="contrePartieCaisse" name="contrePartieCaisse" disabled>
            <option>Importez un fichier pour voir les colonnes</option>
          </select>
        </div>
      </div>

      <div class="buttons">
        <button type="button" id="cancelBtnCaisse">Annuler</button>
        <button type="submit">Importer</button>
      </div>
    </form>
  </div>
</div>




























<script>


(function(){
  const css = `


    
    .pj-cell .pj-input { width: 100%; font-size: 13px; }
  
    
     
    

   `;
  const style = document.createElement('style');
  style.appendChild(document.createTextNode(css));
  document.head.appendChild(style);
})();

 
 
 



</script>

<!-- SheetJS pour preview Excel -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- Modal pour afficher le fichier (preview) -->
<div id="viewFileModal" class="modal" style="display:none;">
  <div class="modal-content" style="width:80%; max-width:900px; margin:5% auto; padding:20px; border-radius:8px; background:#fff; position:relative;">
    <span class="close" style="position:absolute; top:10px; right:20px; font-size:24px; cursor:pointer;">&times;</span>
    <div class="modal-body">
      <!-- Le fichier chargé via AJAX sera injecté ici -->
    </div>
  </div>
</div>
<script src="{{ asset('js/Operation_Courante.js') }}"></script>



<style>
  /* Modal principal */
  #banqueModal_main.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0; top: 0;
    width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.45);
    justify-content: center; align-items: center;
    transition: background 0.3s;
  }

  /* Contenu du modal */
  #banqueModal_main .banqueModal_content {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    max-width: 1000px;
    width: 80%;
    height: 80%;
    margin: 5% auto;
    padding: 20px;
    font-size: 1em;
    color: #34495e;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
  }

  /* Hover sur boutons */
  #banqueModal_main .banqueModal_file-btn:hover,
  #banqueModal_main .banqueModal_file-btn.selected {
    background: #e3f2fd;
    color: #1976d2;
  }

  /* Responsive mobile */
  @media (max-width: 600px) {
    #banqueModal_main .banqueModal_content {
      max-width: 98vw;
      padding: 18px 6vw;
    }
  }

  /* Breadcrumbs */
  #banqueModal_breadcrumb span {
    cursor: pointer;
    color: #1976d2;
  }
  #banqueModal_breadcrumb span:hover {
    text-decoration: underline;
  }

  .banqueModal_folder-btn {
    padding: 0.5rem; 
    background-color: #ffc107; 
    border-radius: 17px;
    color: #fff; 
    border: none; 
    width: 100%; 
    height: 100%;
    cursor: pointer;
  }

  .banqueModal_file-card img, .banqueModal_file-card canvas {
    height: 200px;
    object-fit: cover;
  }

  #banqueModal_fileList {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    padding-left: 0;
    margin: 0;
  }

  .banqueModal_file-card {
    display: none; /* masqué par défaut, affiché via JS */
  }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div id="banqueModal_main" class="modal">
  <div class="banqueModal_content">

    <div style="margin-bottom:1rem;">
      <a href="#" id="banqueModal_backDashboard"
         style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">
         Tableau De Board
      </a>
      <span style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">➢</span>
    </div>

    <div id="banqueModal_dashboard" style="display: flex; flex-wrap: wrap; gap: 20px;">
      <div id="banqueModal_tableau" class="banqueModal_dashboard-item p-2 text-white"
           style="background-color: #ffc107; border-radius: 15px; font-size: 0.75rem;
                  height: 130px; cursor: pointer; width:30%; display:flex; align-items:center; justify-content:center; flex-direction:column;">
        <h4>Banque</h4>
      </div>

      <!-- Dossiers manuels -->
      @if(isset($dossierManuel) && $dossierManuel->count() > 0)
        @foreach($dossierManuel as $dossier)
          <div class="banqueModal_dossierManuel text-white"
               data-dossierid="{{ $dossier->id }}"
               data-dossiername="{{ $dossier->name }}"
               style="background-color: #17a2b8; border-radius: 15px;
                      font-size: 0.75rem; height: 130px; width:30%;
                      cursor: pointer; display:flex; align-items:center;
                      justify-content:center; flex-direction:column;">
            <h4>{{ $dossier->name }}</h4>
          </div>
        @endforeach
      @else
        <p>Aucun dossier manuel disponible.</p>
      @endif
    </div>

    <div id="banqueModal_contentSection" style="display: none;">
      <nav id="banqueModal_breadcrumb" style="margin-bottom: 1rem; font-size: 14px;"></nav>

      <div id="banqueModal_filterZone" 
           style="display: flex; justify-content: flex-end; align-items: center; margin-top: 2rem; gap: 10px;">
        <label for="banqueModal_sortFiles" style="font-weight: bold;">Trier par :</label>
        <select id="banqueModal_sortFiles" 
                style="padding: 5px; border-radius: 5px; border: 1px solid #ccc;">
          <option value="name">Nom</option>
          <option value="date">Date</option>
        </select>

        <button id="banqueModal_sortToggle"
                title="Changer l’ordre de tri"
                style="display: flex; align-items: center; gap: 6px; 
                       background: #f8f9fa; border: 1px solid #ccc; border-radius: 6px;
                       cursor: pointer; font-size: 16px; color: #007bff; 
                       padding: 6px 12px; font-weight: bold; transition: all 0.3s;">
          <i class="fas fa-sort-amount-up"></i> 
          <span id="banqueModal_sortLabel">A → Z</span>
        </button>

        <i class="fa-solid fa-magnifying-glass banqueModal_search-icon" id="banqueModal_searchIcon"></i>
      </div>

      <h3>Dossiers</h3>
      <ul id="banqueModal_folderList" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; padding-left: 0; margin: 0;">
        @foreach($folders_banque as $folder)
          <li style="list-style-type: none;" data-typefolder="{{ $folder->type_folder }}">
            <button class="banqueModal_folder-btn"
                    data-folderid="{{ $folder->id }}"
                    data-foldername="{{ $folder->name }}">
              <i class="fas fa-folder"></i> {{ $folder->name }}
            </button>
          </li>
        @endforeach
      </ul>

      <h3 style="margin-top: 2rem;">Fichiers</h3>
      <div id="banqueModal_fileList" style="padding-left: 0; margin: 0;">
        @foreach($files_banque as $file)
          <div class="col banqueModal_file-card" data-type="{{ $file->type }}" style="padding-bottom:32px; display:none;">
            <div class="banqueModal_card card shadow-sm" 
                 style="width:13rem;height:250px;padding-bottom:16px;" 
                 data-fileid="{{ $file->id }}" 
                 data-filepath="{{ asset($file->path) }}">
              <div class="card-body text-center d-flex flex-column justify-content-between" style="padding:0.5rem;">
                @php $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION)); @endphp
                @if(in_array($ext, ['jpg','jpeg','png','gif']))
                  <img src="{{ asset($file->path) }}" alt="{{ $file->name }}" class="img-fluid mb-2">
                @elseif($ext == 'pdf')
                  <canvas class="img-fluid mb-2"></canvas>
                @else
                  <img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2">
                @endif
                <h5 class="card-title text-truncate" style="font-size:0.9rem;font-weight:bold;">{{ $file->name }}</h5>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("banqueModal_main");
  const tableau = document.getElementById("banqueModal_tableau");
  const contentSection = document.getElementById("banqueModal_contentSection");
  const dashboardSection = document.getElementById("banqueModal_dashboard");
  const backToDashboard = document.getElementById("banqueModal_backDashboard");
  const breadcrumb = document.getElementById("banqueModal_breadcrumb");

  let breadcrumbPath = [];

  function showDashboard() {
    dashboardSection.style.display = "flex";
    contentSection.style.display = "none";
    breadcrumbPath = [];
    breadcrumb.innerHTML = "";
    document.querySelectorAll("#banqueModal_folderList li, #banqueModal_fileList .banqueModal_file-card").forEach(el => el.style.display = "block");
    document.querySelectorAll("#banqueModal_fileList .no-file-msg").forEach(el => el.remove());
  }

  function updateBreadcrumb() {
    breadcrumb.innerHTML = breadcrumbPath.map((name, index) => {
      if(index < breadcrumbPath.length - 1){
        return `<span class="banqueModal_breadcrumb-folder" data-index="${index}">${name}</span> ➢ `;
      } else return `<span>${name}</span>`;
    }).join('');
    document.querySelectorAll(".banqueModal_breadcrumb-folder").forEach(el => {
      el.addEventListener("click", function(){
        const idx = parseInt(this.dataset.index);
        breadcrumbPath = breadcrumbPath.slice(0, idx + 1);
        openContent(breadcrumbPath[breadcrumbPath.length-1]);
      });
    });
  }

  function openContent(typeName) {
    dashboardSection.style.display = "none";
    contentSection.style.display = "block";
    if(breadcrumbPath[breadcrumbPath.length-1] !== typeName) breadcrumbPath.push(typeName);
    updateBreadcrumb();

    document.querySelectorAll("#banqueModal_folderList li").forEach(li => {
      li.style.display = li.dataset.typefolder === typeName ? 'block' : 'none';
    });

    const files = document.querySelectorAll("#banqueModal_fileList .banqueModal_file-card");
    let hasFiles = false;
    files.forEach(f => {
      if(f.dataset.type === typeName){
        f.style.display = "block";
        hasFiles = true;
      } else f.style.display = "none";
    });

    const fileList = document.getElementById("banqueModal_fileList");
    fileList.querySelectorAll(".no-file-msg").forEach(el => el.remove());
    if(!hasFiles){
      const p = document.createElement("p");
      p.textContent = "Aucun fichier disponible.";
      p.classList.add("no-file-msg");
      fileList.appendChild(p);
    }
  }

  tableau.addEventListener("dblclick", () => {
    modal.style.display = "flex";
    breadcrumbPath = ['Banque'];
    openContent('banque');
  });

  document.querySelectorAll(".banqueModal_dossierManuel").forEach(item => {
    item.addEventListener("dblclick", function() {
      const dossierName = this.dataset.dossiername;
      modal.style.display = "flex";
      breadcrumbPath = ['Banque', dossierName];
      openContent(dossierName);
    });
  });

  backToDashboard.addEventListener("click", function(e){
    e.preventDefault();
    showDashboard();
  });

  modal.addEventListener("click", e => { if(e.target === modal) modal.style.display = "none"; });

  document.querySelectorAll('.banqueModal_file-card').forEach(cardWrapper => {
    cardWrapper.addEventListener('dblclick', function () {
      const innerCard = this.querySelector('.banqueModal_card');
      const selectedFilePath = innerCard.dataset.filepath;
      if(selectedFilePath) window.open(selectedFilePath, '_blank');
    });
  });

  document.querySelectorAll(".banqueModal_folder-btn").forEach(btn => {
    btn.addEventListener("dblclick", function() {
      loadFolder(this.dataset.folderid, this.dataset.foldername);
    });
  });

  function loadFolder(folderId, folderName) {
    const fileList = document.getElementById("banqueModal_fileList");
    const folderList = document.getElementById("banqueModal_folderList");
    fileList.innerHTML = "<p>Chargement des fichiers...</p>";
    folderList.innerHTML = "<p>Chargement des sous-dossiers...</p>";

    fetch(`/operation-courante/select-folder?id=${folderId}`)
      .then(response => response.json())
      .then(data => {
        breadcrumbPath.push(folderName);
        updateBreadcrumb();

        folderList.innerHTML = "";
        if (data.folders_banque && data.folders_banque.length > 0) {
          data.folders_banque.forEach(folder => {
            const li = document.createElement("li");
            li.style.listStyleType = "none";
            li.innerHTML = `<button class="banqueModal_folder-btn" data-folderid="${folder.id}" data-foldername="${folder.name}">
                              <i class="fas fa-folder"></i> ${folder.name}
                            </button>`;
            folderList.appendChild(li);
            li.querySelector(".banqueModal_folder-btn").addEventListener("dblclick", () => {
              loadFolder(folder.id, folder.name);
            });
          });
        } else folderList.innerHTML = "<p>Aucun sous-dossier.</p>";

        fileList.innerHTML = "";
        if (data.files_banque && data.files_banque.length > 0) {
          data.files_banque.forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            let preview = "";
            if (["jpg","jpeg","png","gif"].includes(ext)) preview = `<img src="/${file.path}" class="img-fluid mb-2">`;
            else if (ext === "pdf") preview = `<canvas class="img-fluid mb-2"></canvas>`;
            else preview = `<img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2">`;

            const div = document.createElement("div");
            div.className = "col banqueModal_file-card";
            div.innerHTML = `<div class="banqueModal_card card shadow-sm" style="width:13rem;height:250px;" data-fileid="${file.id}" data-filepath="/${file.path}">
                              <div class="card-body text-center d-flex flex-column justify-content-between" style="padding:0.5rem;">
                                ${preview}
                                <h5 class="card-title text-truncate" style="font-size:0.9rem;font-weight:bold;">${file.name}</h5>
                              </div>
                            </div>`;
            fileList.appendChild(div);

            div.querySelector(".banqueModal_card").addEventListener("dblclick", () => {
              window.open(`/${file.path}`, "_blank");
            });
          });
        } else fileList.innerHTML = "<p>Aucun fichier disponible.</p>";

        document.getElementById("banqueModal_sortToggle").click();
      })
      .catch(err => console.error(err));
  }
});
</script>

 
 


<style>
  /* Modal principal Achat */
  #achatModal_main.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0; top: 0;
    width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.45);
    justify-content: center; align-items: center;
    transition: background 0.3s;
  }
  #achatModal_main .achatModal_content {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    max-width: 1000px;
    width: 80%;
    height: 80%;
    margin: 5% auto;
    padding: 20px;
    font-size: 1em;
    color: #34495e;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
  }
  #achatModal_main .achatModal_folder-btn {
    padding: 0.5rem;
    background-color: #ffc107;
    border-radius: 17px;
    color: #fff;
    border: none;
    width: 100%;
    height: 100%;
    cursor: pointer;
  }
  #achatModal_fileList { display: grid; grid-template-columns: repeat(4, 1fr); gap:1rem; padding-left:0; margin:0; }
  .achatModal_file-card img, .achatModal_file-card canvas { height:200px; object-fit:cover; }
  .achatModal_file-card { display:none; }
</style>

 
<!-- Modal Achat -->
<div id="achatModal_main" class="modal">
  <div class="achatModal_content">

    <div style="margin-bottom:1rem;">
      <a href="#" id="achatModal_backDashboard"
         style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">
         Tableau De Board
      </a>
      <span style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">➢</span>
    </div>

    <div id="achatModal_dashboard" style="display:flex; flex-wrap:wrap; gap:20px;">
      <div id="achatModal_tableau" class="achatModal_dashboard-item p-2 text-white"
           style="background-color:#ffc107;border-radius:15px;font-size:0.75rem;height:130px;cursor:pointer;width:30%;display:flex;align-items:center;justify-content:center;flex-direction:column;">
        <h4>Achat</h4>
      </div>

      @if(isset($dossierManuel) && $dossierManuel->count() > 0)
        @foreach($dossierManuel as $dossier)
          <div class="achatModal_dossierManuel text-white"
               data-dossierid="{{ $dossier->id }}"
               data-dossiername="{{ $dossier->name }}"
               style="background-color:#17a2b8;border-radius:15px;font-size:0.75rem;height:130px;width:30%;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-direction:column;">
            <h4>{{ $dossier->name }}</h4>
          </div>
        @endforeach
      @else
        <p>Aucun dossier manuel disponible.</p>
      @endif
    </div>

    <div id="achatModal_contentSection" style="display:none;">
      <nav id="achatModal_breadcrumb" style="margin-bottom:1rem;font-size:14px;"></nav>

      <div id="achatModal_filterZone" style="display:flex;justify-content:flex-end;align-items:center;margin-top:2rem;gap:10px;">
        <label for="achatModal_sortFiles" style="font-weight:bold;">Trier par :</label>
        <select id="achatModal_sortFiles" style="padding:5px;border-radius:5px;border:1px solid #ccc;">
          <option value="name">Nom</option>
          <option value="date">Date</option>
        </select>
        <button id="achatModal_sortToggle" title="Changer l’ordre de tri" style="display:flex;align-items:center;gap:6px;background:#f8f9fa;border:1px solid #ccc;border-radius:6px;cursor:pointer;font-size:16px;color:#007bff;padding:6px 12px;font-weight:bold;">
          <i class="fas fa-sort-amount-up"></i><span id="achatModal_sortLabel">A → Z</span>
        </button>
        <i class="fa-solid fa-magnifying-glass achatModal_search-icon" id="achatModal_searchIcon"></i>
      </div>

      <h3>Dossiers</h3>
      <ul id="achatModal_folderList" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;padding-left:0;margin:0;">
        @foreach($folders_achat ?? collect() as $folder)
          <li style="list-style-type:none;" data-typefolder="{{ $folder->type_folder }}">
            <button class="achatModal_folder-btn" data-folderid="{{ $folder->id }}" data-foldername="{{ $folder->name }}">
              <i class="fas fa-folder"></i> {{ $folder->name }}
            </button>
          </li>
        @endforeach
      </ul>

      <h3 style="margin-top:2rem;">Fichiers</h3>
      <div id="achatModal_fileList" style="padding-left:0;margin:0;">
        @foreach($files_achat ?? collect() as $file)
          <div class="col achatModal_file-card" data-type="{{ $file->type }}" style="padding-bottom:32px; display:none;">
            <div class="achatModal_card card shadow-sm" style="width:13rem;height:250px;padding-bottom:16px;" data-fileid="{{ $file->id }}" data-filepath="{{ asset($file->path) }}">
              <div class="card-body text-center d-flex flex-column justify-content-between" style="padding:0.5rem;">
                @php $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION)); @endphp
                @if(in_array($ext, ['jpg','jpeg','png','gif']))
                  <img src="{{ asset($file->path) }}" alt="{{ $file->name }}" class="img-fluid mb-2">
                @elseif($ext == 'pdf')
                  <canvas class="img-fluid mb-2"></canvas>
                @else
                  <img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2">
                @endif
                <h5 class="card-title text-truncate" style="font-size:0.9rem;font-weight:bold;">{{ $file->name }}</h5>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("achatModal_main");
  const tableau = document.getElementById("achatModal_tableau");
  const contentSection = document.getElementById("achatModal_contentSection");
  const dashboardSection = document.getElementById("achatModal_dashboard");
  const backToDashboard = document.getElementById("achatModal_backDashboard");
  const breadcrumb = document.getElementById("achatModal_breadcrumb");
  const sortSelect = document.getElementById("achatModal_sortFiles");
  const sortToggle = document.getElementById("achatModal_sortToggle");
  const sortLabel = document.getElementById("achatModal_sortLabel");

  let breadcrumbPath = [];

  function showDashboard() {
    dashboardSection.style.display = "flex";
    contentSection.style.display = "none";
    breadcrumbPath = [];
    breadcrumb.innerHTML = "";
    document.querySelectorAll("#achatModal_folderList li, #achatModal_fileList .achatModal_file-card").forEach(el => el.style.display = "block");
    document.querySelectorAll("#achatModal_fileList .no-file-msg").forEach(el => el.remove());
  }

  function updateBreadcrumb() {
    breadcrumb.innerHTML = breadcrumbPath.map((name, index) => {
      if(index < breadcrumbPath.length - 1){
        return `<span class="achatModal_breadcrumb-folder" data-index="${index}">${name}</span> ➢ `;
      } else return `<span>${name}</span>`;
    }).join('');
    document.querySelectorAll(".achatModal_breadcrumb-folder").forEach(el => {
      el.addEventListener("click", function(){
        const idx = parseInt(this.dataset.index);
        breadcrumbPath = breadcrumbPath.slice(0, idx + 1);
        openContent(breadcrumbPath[breadcrumbPath.length-1]);
      });
    });
  }

  function openContent(typeName) {
    dashboardSection.style.display = "none";
    contentSection.style.display = "block";
    if(breadcrumbPath[breadcrumbPath.length-1] !== typeName) breadcrumbPath.push(typeName);
    updateBreadcrumb();

    document.querySelectorAll("#achatModal_folderList li").forEach(li => {
      li.style.display = li.dataset.typefolder === typeName ? 'block' : 'none';
    });

    const files = document.querySelectorAll("#achatModal_fileList .achatModal_file-card");
    let hasFiles = false;
    files.forEach(f => {
      if(f.dataset.type === typeName){
        f.style.display = "block";
        hasFiles = true;
      } else f.style.display = "none";
    });

    const fileList = document.getElementById("achatModal_fileList");
    fileList.querySelectorAll(".no-file-msg").forEach(el => el.remove());
    if(!hasFiles){
      const p = document.createElement("p");
      p.textContent = "Aucun fichier disponible.";
      p.classList.add("no-file-msg");
      fileList.appendChild(p);
    }

    sortAchatFiles();
  }

  tableau.addEventListener("dblclick", () => {
    modal.style.display = "flex";
    breadcrumbPath = ['Achat'];
    openContent('achat');
  });

  document.querySelectorAll(".achatModal_dossierManuel").forEach(item => {
    item.addEventListener("dblclick", function() {
      const dossierName = this.dataset.dossiername;
      modal.style.display = "flex";
      breadcrumbPath = ['Achat', dossierName];
      openContent(dossierName);
    });
  });

  backToDashboard.addEventListener("click", function(e){
    e.preventDefault();
    showDashboard();
  });

  modal.addEventListener("click", e => { if(e.target === modal) modal.style.display = "none"; });


  

  document.querySelectorAll(".achatModal_folder-btn").forEach(btn => {
    btn.addEventListener("dblclick", function() {
      loadFolderAchat(this.dataset.folderid, this.dataset.foldername);
    });
  });

  function loadFolderAchat(folderId, folderName) {
    const fileList = document.getElementById("achatModal_fileList");
    const folderList = document.getElementById("achatModal_folderList");
    fileList.innerHTML = "<p>Chargement des fichiers...</p>";
    folderList.innerHTML = "<p>Chargement des sous-dossiers...</p>";

    fetch(`/select-folder-achat?id=${folderId}`)
      .then(response => response.json())
      .then(data => {
        breadcrumbPath.push(folderName);
        updateBreadcrumb();

        folderList.innerHTML = "";
        if (data.folders_achat && data.folders_achat.length > 0) {
          data.folders_achat.forEach(folder => {
            const li = document.createElement("li");
            li.style.listStyleType = "none";
            li.setAttribute("data-typefolder", folder.type_folder || "achat");
            li.innerHTML = `<button class="achatModal_folder-btn" data-folderid="${folder.id}" data-foldername="${folder.name}">
                              <i class="fas fa-folder"></i> ${folder.name}
                            </button>`;
            folderList.appendChild(li);
            li.querySelector(".achatModal_folder-btn").addEventListener("dblclick", () => {
              loadFolderAchat(folder.id, folder.name);
            });
          });
        } else folderList.innerHTML = "<p>Aucun sous-dossier.</p>";

        fileList.innerHTML = "";
        if (data.files_achat && data.files_achat.length > 0) {
          data.files_achat.forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            let preview = "";
            if (["jpg","jpeg","png","gif"].includes(ext)) preview = `<img src="/${file.path}" class="img-fluid mb-2">`;
            else if (ext === "pdf") preview = `<canvas class="img-fluid mb-2"></canvas>`;
            else preview = `<img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2">`;

            const div = document.createElement("div");
            div.className = "col achatModal_file-card";
            div.setAttribute("data-type", file.type || "achat");
            div.style.paddingBottom = "32px";
            div.style.display = "none";

            div.innerHTML = `<div class="achatModal_card card shadow-sm" 
                                style="width:13rem;height:250px;" 
                                data-fileid="${file.id}" 
                                data-filepath="/${file.path}">
                                <div class="card-body text-center d-flex flex-column justify-content-between" style="padding:0.5rem;">
                                  ${preview}
                                  <h5 class="card-title text-truncate" style="font-size:0.9rem;font-weight:bold;">${file.name}</h5>
                                </div>
                              </div>`;
            fileList.appendChild(div);

            div.querySelector(".achatModal_card").addEventListener("dblclick", () => {
              window.open(`/${file.path}`, "_blank");
            });
          });
        } else fileList.innerHTML = "<p>Aucun fichier disponible.</p>";

        sortAchatFiles();
      })
      .catch(err => console.error(err));
  }

  // -----------------------
  // TRI DES FICHIERS
  // -----------------------
  function sortAchatFiles() {
    const fileList = document.getElementById("achatModal_fileList");
    const mode = sortSelect.value;
    const descending = sortLabel.textContent.includes("Z");

    const cards = Array.from(fileList.querySelectorAll(".achatModal_file-card"))
                  .filter(card => card.style.display !== "none");

    cards.sort((a, b) => {
      const nameA = a.querySelector(".card-title").textContent.trim().toLowerCase();
      const nameB = b.querySelector(".card-title").textContent.trim().toLowerCase();

      if (mode === "name") {
        if (nameA < nameB) return descending ? 1 : -1;
        if (nameA > nameB) return descending ? -1 : 1;
        return 0;
      }

      // Mode DATE : on utilise fileid comme timestamp pour exemple
      const dateA = parseInt(a.querySelector(".achatModal_card").dataset.fileid);
      const dateB = parseInt(b.querySelector(".achatModal_card").dataset.fileid);
      return descending ? dateB - dateA : dateA - dateB;
    });

    cards.forEach(c => fileList.appendChild(c));
  }

  sortSelect.addEventListener("change", sortAchatFiles);
  sortToggle.addEventListener("click", () => {
    sortLabel.textContent = sortLabel.textContent.includes("A") ? "Z → A" : "A → Z";
    sortAchatFiles();
  });
});
</script>

 

@endsection
