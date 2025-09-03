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
</script>

<!-- Onglet Achats -->
<div id="achats" class="tab-content active" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; font-family: Arial, sans-serif; font-size: 10px; color: #333; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
  <div class="filter-container" style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">

    <!-- Code et Journal -->
    <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
      <label for="journal-achats" style="font-size: 11px; font-weight: bold;">Code journal:</label>
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

      <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR " type="radio" name="filter-period-achats" id="filter-mois-achats" value="mois" checked>
        <label class="form-check-label" for="filter-mois-achats" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Mois</label>
      </div>
      <div class="form-check form-check-inline" style="font-size: 9px;">
        <input class="formR" type="radio" name="filter-period-achats" id="filter-exercice-achats" value="exercice">
        <label class="form-check-label" for="filter-exercice-achats" style="font-size: 9px; font-weight: 600; margin-left: 5px;">Exercice entier</label>
      </div>
          <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
      <label for="periode-achats" style="font-size: 11px; font-weight: bold;">Période :</label>
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
      <a id="print-table" href="#" class="text-dark" title="Imprimer">
        <i class="fa fa-print" style="font-size:16px;"></i>
      </a>
    </div>
  </div>

  <!-- Conteneur Tabulator Achats -->
  <!-- <div id="table-achats" style="height:400px;"></div> -->
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
            <label for="journal-ventes" style="font-size: 11px; font-weight: bold;">Code journal :</label>
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
                     <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
            <label for="filter-period-ventes" style="font-size: 11px; font-weight: bold;">Période :</label>
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
            <a      id="print-tableV"       href="#" class="text-dark" title="Imprimer la table">
              <i class="fa fa-print" style="font-size:16px;"></i>
            </a>
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
    <label for="journal-Caisse" style="font-size: 11px; font-weight: bold;">Code journal :</label>
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
    <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
    <label for="periode-Caisse" style="font-size: 11px; font-weight: bold;">Période :</label>
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
            <label for="journal-Banque" style="font-size: 11px; font-weight: bold;">Code journal :</label>
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
    <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; padding: 6px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
    <label for="periode-Banque" style="font-size: 11px; font-weight: bold;">Période :</label>
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
            <!-- <button class="icon-button border-0 bg-transparent" id="print-btn" title="Impression" onclick="window.print();">
        <i class="fas fa-print text-info" style="font-size: 14px;"></i>
    </button> -->

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
            <label for="journal-operations-diverses" style="font-size: 11px; font-weight: bold;">Code journal :</label>
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

<style>
  #files_banque_Modal.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0; top: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.45);
    justify-content: center; align-items: center;
    transition: background 0.3s;
  }
  #files_banque_Modal .modal-content {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    max-width: 400px;
    width: 90vw;
    font-size: 1em;
    color: #34495e;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(44,62,80,0.07);
    transition: background 0.2s, color 0.2s;
    width: 100%; text-align: left;
    display: flex; align-items: center;
    gap: 10px;
  }
  #files_banque_Modal .file-button:hover, #files_banque_Modal .file-button.selected {
    background: #e3f2fd;
    color: #1976d2;
  }
  #files_banque_Modal .file-button i {
    font-size: 1.2em; color: #1976d2;
  }
  #files_banque_Modal .modal-footer {
    display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px;
  }
  #files_banque_Modal .cancel-btn, #files_banque_Modal .confirm-btn {
    background: #1976d2;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 8px 18px;
    font-size: 1em;
    cursor: pointer;
    transition: background 0.2s;
  }
  #files_banque_Modal .cancel-btn {
    background: #b0bec5;
    color: #37474f;
  }
  #files_banque_Modal .cancel-btn:hover {
    background: #78909c;
  }
  #files_banque_Modal .confirm-btn:hover {
    background: #1565c0;
  }
  @media (max-width: 600px) {
    #files_banque_Modal .modal-content { max-width: 98vw; padding: 18px 6vw; }
  }
 
.modal-content {
  overflow-y: auto;
  flex-grow: 1;
}

</style>
<div id="files_banque_Modal" class="modal" style="display: none;">
  <div class="modal-content" style="width: 80%; max-width: 1000px;height: 80%; height-width: 1000px; margin: 5% auto; padding: 20px; border-radius: 10px; background-color: #fff;">
<!-- POP-UP -->
<div id="myPopup">
    <span class="close-btn" style="float: right; font-size: 24px; cursor: pointer;">&times;</span>
 </div>

<script>
    // Fermer le pop-up au clic sur le bouton
    document.querySelector('.close-btn').addEventListener('click', function () {
        document.getElementById('myPopup').style.display = 'none';
    });
</script>
    <!-- <h2 style="margin-top: 0;">Choisir un fichier ou un dossier</h2> -->
    
    <div class="modal-body">
  <nav id="banqueBreadcrumb" style="margin-bottom: 1rem; font-size: 14px; color: #1976d2;"></nav>
  <h3>Dossiers</h3>
  <ul id="folderList" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; padding-left: 0; margin: 0;">
        @if(isset($folders_banque) && $folders_banque->count() > 0)
          @foreach($folders_banque as $folder)
            <li style="list-style-type: none;">
              <button 
                class="folder-button" 
                data-folderid="{{ $folder->id }}" 
                data-foldername="{{ $folder->name }}"
                style="padding: 0.5rem; background-color: #ffc107; border-radius: 17px; color: #fff; border: none; width: 100%; height: 100%;"
              >
                <i class="fas fa-folder"></i> {{ $folder->name }}
              </button>
            </li>
          @endforeach
        @else
          <li style="list-style-type: none;">Aucun dossier disponible</li>
        @endif
      </ul>

      <h3 style="margin-top: 2rem;">Fichiers</h3>
      <div id="fileListBanque" style="padding-left: 0; margin: 0;">
        @if(isset($files_banque) && $files_banque->count() > 0)
          <div class="row row-cols-4 g-3">
          @foreach($files_banque as $file)
            <div class="col" ondblclick="viewFile({{ $file->id }})" style="padding-bottom: 32px;padding-right:200px;">
              <div class="card shadow-sm" style="width: 13rem; height: 250px; padding-bottom: 16px;">
                <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;">
                  @if(in_array(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                    <img src="{{ asset($file->path) }}" alt="{{ $file->name }}" class="img-fluid mb-2" style="height: 200px">
                  @elseif(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'pdf')
                    <canvas id="pdf-preview-{{ $file->id }}" class="img-fluid mb-2" style="height: 200px"></canvas>
                  @elseif(in_array(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)), ['xls', 'xlsx']))
                    <div id="excel-preview-{{ $file->id }}" class="excel-preview" style="height: 200px"></div>
                  @elseif(in_array(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)), ['doc', 'docx']))
                    <img src="https://via.placeholder.com/80x100.png?text=Word" class="img-fluid mb-2" style="height: 200px">
                  @else
                    <img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2" style="height: 200px">
                  @endif

                  <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">{{ $file->name }}</h5>
                  <!-- <div style="font-size: 0.8rem; color: #888; margin-top: 4px;">
                    Dernière modification : {{ \Carbon\Carbon::parse($file->updated_at)->format('d/m/Y H:i') }}<br>par : {{ $file->updatedBy ? $file->updatedBy->name : ($file->user ? $file->user->name : 'Inconnu') }}
                  </div> -->
                </div>
              </div>
              <script>
                document.addEventListener("DOMContentLoaded", function() {
                  @if(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'pdf')
                    var url = '{{ asset($file->path) }}';
                    var canvas = document.getElementById
                    <img src="{{ asset($file->path) }}" alt="{{ $file->name }}" class="img-fluid mb-2" style="height: 200px">
                  @elseif(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'pdf')
                    <canvas id="pdf-preview-{{ $file->id }}" class="img-fluid mb-2" style="height: 200px"></canvas>
                  @elseif(in_array(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)), ['xls', 'xlsx']))
                    <div id="excel-preview-{{ $file->id }}" class="excel-preview" style="height: 200px"></div>
                  @elseif(in_array(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)), ['doc', 'docx']))
                    <img src="https://via.placeholder.com/80x100.png?text=Word" class="img-fluid mb-2" style="height: 200px">
                  @else
                    <img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2" style="height: 200px">
                  @endif

                  <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">{{ $file->name }}</h5>
                  <!-- <div style="font-size: 0.8rem; color: #888; margin-top: 4px;">
                    Dernière modification : {{ \Carbon\Carbon::parse($file->updated_at)->format('d/m/Y H:i') }}<br>par : {{ $file->updatedBy ? $file->updatedBy->name : ($file->user ? $file->user->name : 'Inconnu') }}
                  </div> -->
                </div>
              </div>
              <script>
                document.addEventListener("DOMContentLoaded", function() {
                  @if(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'pdf')
                    var url = '{{ asset($file->path) }}';
                    var canvas = document.getElementById('pdf-preview-{{ $file->id }}');
                    if (canvas && window['pdfjsLib']) {
                      var ctx = canvas.getContext('2d');
                      pdfjsLib.getDocument(url).promise.then(function(pdf) {
                        pdf.getPage(1).then(function(page) {
                          var scale = 0.5;
                          var viewport = page.getViewport({ scale: scale });
                          canvas.height = viewport.height;
                          canvas.width = viewport.width;
                          page.render({ canvasContext: ctx, viewport: viewport });
                        });
                      });
                    }
                  @elseif(in_array(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)), ['xls', 'xlsx']))
                    var url = '{{ asset($file->path) }}';
                    var previewElement = document.getElementById('excel-preview-{{ $file->id }}');
                    fetch(url)
                      .then(response => response.arrayBuffer())
                      .then(data => {
                        var workbook = XLSX.read(data, { type: 'array' });
                        var sheet = workbook.Sheets[workbook.SheetNames[0]];
                        var html = XLSX.utils.sheet_to_html(sheet, { id: 'excel-preview', editable: false, className: 'excel-preview' });
                        previewElement.innerHTML = html;
                      });
                  @endif
                });
              </script>
            </div>
          @endforeach
          </div>
        @else
          <p>Aucun fichier disponible.</p>
        @endif
      </div>
    </div>

    <!-- <div class="modal-footer" style="margin-top: 2rem; text-align: right;">
      <button class="cancel-btn" style="padding: 0.5rem 1rem; margin-right: 10px;">Annuler</button>
      <button id="confirmBtn_Banque" class="confirm-btn" style="padding: 0.5rem 1rem; background-color: #28a745; color: #fff; border: none; border-radius: 5px;">Confirmer</button>
    </div> -->
  </div>
</div>

<!-- ✅ Script JavaScript pour gérer le double-clic -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  // Gestion du breadcrumb
  let folderPath = [];

  function updateFolderAndFileLists(response) {
    console.log(response);
    renderBreadcrumb();
    // Mise à jour des dossiers
    $('#folderList').html('');
    if (response.folders_banque.length > 0) {
      response.folders_banque.forEach(folder => {
        $('#folderList').append(`
            <li style="list-style-type: none;">
            <button class="folder-button"    style="padding: 0.5rem; background-color: #ffc107; border-radius: 17px; color: #fff; border: none; width: 100%; height: 100%;"
 data-folderid="${folder.id}" data-foldername="${folder.name}">
              <i class="fas fa-folder"></i> ${folder.name}
            </button>
          </li>
        `);
      });
    } else {
      $('#folderList').append('<li>Aucun dossier disponible</li>');
    }

    // Mise à jour des fichiers
      $('#fileListBanque').html('');
      if (response.files_banque.length > 0) {
    let filesHtml = '<div class="row row-cols-4 g-3">';
        response.files_banque.forEach(file => {
          let ext = file.name.split('.').pop().toLowerCase();
          let previewHtml = '';
          if (["jpg","jpeg","png","gif"].includes(ext)) {
            previewHtml = `<img src="${file.path}" alt="${file.name}" class="img-fluid mb-2" style="height: 200px">`;
          } else if (ext === "pdf") {
            previewHtml = `<canvas id="pdf-preview-${file.id}" class="img-fluid mb-2" style="height: 200px"></canvas>`;
          } else if (["xls","xlsx"].includes(ext)) {
            previewHtml = `<div id="excel-preview-${file.id}" class="excel-preview" style="height: 200px"></div>`;
          } else if (["doc","docx"].includes(ext)) {
            previewHtml = `<img src="https://via.placeholder.com/80x100.png?text=Word" class="img-fluid mb-2" style="height: 200px">`;
          } else {
            previewHtml = `<img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2" style="height: 200px">`;
          }
          filesHtml += `
            <div class="col" ondblclick="viewFile(${file.id})" style="padding-bottom: 32px;padding-right:200px;">
              <div class="card shadow-sm" style="width: 13rem; height: 250px; padding-bottom: 16px;">
                <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;">
                  ${previewHtml}
                  <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">${file.name}</h5>
                </div>
              </div>
            </div>
          `;
        });
        filesHtml += '</div>';
        $('#fileListBanque').html(filesHtml);
        // Initialiser les aperçus PDF/Excel
        response.files_banque.forEach(file => {
          let ext = file.name.split('.').pop().toLowerCase();
          if (ext === "pdf" && window['pdfjsLib']) {
            let url = file.path;
            let canvas = document.getElementById('pdf-preview-' + file.id);
            if (canvas) {
              let ctx = canvas.getContext('2d');
              pdfjsLib.getDocument(url).promise.then(function(pdf) {
                pdf.getPage(1).then(function(page) {
                  let scale = 0.5;
                  let viewport = page.getViewport({ scale: scale });
                  canvas.height = viewport.height;
                  canvas.width = viewport.width;
                  page.render({ canvasContext: ctx, viewport: viewport });
                });
              });
            }
          } else if (["xls","xlsx"].includes(ext)) {
            let url = file.path;
            let previewElement = document.getElementById('excel-preview-' + file.id);
            if (previewElement) {
              fetch(url)
                .then(response => response.arrayBuffer())
                .then(data => {
                  var workbook = XLSX.read(data, { type: 'array' });
                  var sheet = workbook.Sheets[workbook.SheetNames[0]];
                  var html = XLSX.utils.sheet_to_html(sheet, { id: 'excel-preview', editable: false, className: 'excel-preview' });
                  previewElement.innerHTML = html;
                });
            }
          }
        });
      } else {
        $('#fileListBanque').html('<p>Aucun fichier disponible.</p>');
      }

    // Réattacher les événements
    attachFolderEvents();
  }

  function renderBreadcrumb() {
    const nav = document.getElementById('banqueBreadcrumb');
    if (!folderPath.length) {
      nav.innerHTML = '<span style="cursor:pointer;" id="breadcrumb-root">Banque</span>';
    } else {
      let html = '<span style="cursor:pointer;" id="breadcrumb-root">Banque</span>';
      folderPath.forEach((folder, idx) => {
        html += ' / <span style="cursor:pointer;" class="breadcrumb-folder" data-idx="' + idx + '" data-id="' + folder.id + '">' + folder.name + '</span>';
      });
      nav.innerHTML = html;
    }
    // Attache les événements pour revenir en arrière
    document.getElementById('breadcrumb-root').onclick = function() {
      folderPath = [];
      // Requête AJAX pour la Banque
      $.ajax({
        type: 'GET',
        url: '/operation-courante/select-folder',
        data: { id: 0 },
        success: function(response) {
          updateFolderAndFileLists(response);
        }
      });
    };
    document.querySelectorAll('.breadcrumb-folder').forEach(el => {
      el.onclick = function() {
        const idx = parseInt(this.dataset.idx);
        folderPath = folderPath.slice(0, idx + 1);
        $.ajax({
          type: 'GET',
          url: '/operation-courante/select-folder',
          data: { id: this.dataset.id },
          success: function(response) {
            updateFolderAndFileLists(response);
          }
        });
      };
    });
  }

  function attachFolderEvents() {
    document.querySelectorAll(".folder-button").forEach(button => {
      button.addEventListener("dblclick", function () {
        const folderId = this.dataset.folderid;
    const folderName = this.dataset.foldername;
    folderPath.push({ id: folderId, name: folderName });
        $.ajax({
          type: 'GET',
          url: '/operation-courante/select-folder',
          data: { id: folderId },
          success: updateFolderAndFileLists,
          error: function(xhr) {
            alert("Erreur : " + xhr.responseJSON?.error || "Une erreur est survenue.");
          }
        });
      });
    });
  }

  // Premier attachement initial
  attachFolderEvents();
  renderBreadcrumb();
});

</script>





<!-- <div id="files_banque_Modal" class="modal" style="display: none;">
  <div class="modal-content" style="width: 80%; max-width: 1000px; margin: 5% auto; padding: 20px; border-radius: 10px; background-color: #fff;">
     <div id="myPopup">
      <span class="close-btn" style="float: right; font-size: 24px; cursor: pointer;">&times;</span>
    </div>

    <script>
      // Fermer le pop-up au clic sur le bouton
      document.querySelector('.close-btn').addEventListener('click', function () {
        document.getElementById('myPopup').style.display = 'none';
      });
    </script>

    <div class="modal-body">
      <nav id="banqueBreadcrumb" style="margin-bottom: 1rem; font-size: 14px; color: #1976d2;"></nav>
      
      <h3>Dossiers</h3>
      <ul id="folderList" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; padding-left: 0; margin: 0;">
        @if(isset($folders_banque) && $folders_banque->count() > 0)
          @foreach($folders_banque as $folder)
            <li style="list-style-type: none;">
              <button 
                class="folder-button" 
                data-folderid="{{ $folder->id }}" 
                data-foldername="{{ $folder->name }}"
                style="padding: 0.5rem; background-color: #ffc107; border-radius: 17px; color: #fff; border: none; width: 100%; height: 100%;"
              >
                <i class="fas fa-folder"></i> {{ $folder->name }}
              </button>
            </li>
          @endforeach
        @else
          <li style="list-style-type: none;">Aucun dossier disponible</li>
        @endif
      </ul>

      <h3 style="margin-top: 2rem;">Fichiers</h3>
      <div id="fileListBanque" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; padding-left: 0; margin: 0;">
        @if(isset($files_banque) && $files_banque->count() > 0)
          <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach($files_banque as $file)
              <div class="col file-card" data-file-id="{{ $file->id }}" ondblclick="viewFile({{ $file->id }})" style="padding-bottom: 32px; padding-right: 200px;">
                <div class="card shadow-sm" style="width: 13rem; height: 250px; padding-bottom: 16px;">
                  <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;">
                    @php
                      $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                    @endphp
                    @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                      <img src="{{ asset($file->path) }}" alt="{{ $file->name }}" class="img-fluid mb-2" style="height: 200px">
                    @elseif($ext == 'pdf')
                      <canvas id="pdf-preview-{{ $file->id }}" class="img-fluid mb-2" style="height: 200px"></canvas>
                    @elseif(in_array($ext, ['xls', 'xlsx']))
                      <div id="excel-preview-{{ $file->id }}" class="excel-preview" style="height: 200px"></div>
                    @elseif(in_array($ext, ['doc', 'docx']))
                      <img src="https://via.placeholder.com/80x100.png?text=Word" class="img-fluid mb-2" style="height: 200px">
                    @else
                      <img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2" style="height: 200px">
                    @endif
                    <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">{{ $file->name }}</h5>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <p>Aucun fichier disponible.</p>
        @endif
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  let folderPath = [];

  function renderBreadcrumb() {
    const nav = document.getElementById('banqueBreadcrumb');
    if (!folderPath.length) {
      nav.innerHTML = '<span style="cursor:pointer;" id="breadcrumb-root">Banque</span>';
    } else {
      let html = '<span style="cursor:pointer;" id="breadcrumb-root">Banque</span>';
      folderPath.forEach((folder, idx) => {
        html += ' / <span style="cursor:pointer;" class="breadcrumb-folder" data-idx="' + idx + '" data-id="' + folder.id + '">' + folder.name + '</span>';
      });
      nav.innerHTML = html;
    }
    document.getElementById('breadcrumb-root').onclick = function () {
      folderPath = [];
      fetchFolderContents(0);
    };
    document.querySelectorAll('.breadcrumb-folder').forEach(el => {
      el.onclick = function () {
        const idx = parseInt(this.dataset.idx);
        folderPath = folderPath.slice(0, idx + 1);
        fetchFolderContents(this.dataset.id);
      };
    });
  }

  // Fonction qui crée le HTML complet d’une carte fichier
  function createFileCard(file) {
    const ext = file.name.split('.').pop().toLowerCase();
    let previewHtml = '';

    if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
      previewHtml = `<img src="${file.path}" alt="${file.name}" class="img-fluid mb-2" style="height: 200px">`;
    } else if (ext === 'pdf') {
      previewHtml = `<canvas id="pdf-preview-${file.id}" class="img-fluid mb-2" style="height: 200px"></canvas>`;
    } else if (['xls', 'xlsx'].includes(ext)) {
      previewHtml = `<div id="excel-preview-${file.id}" class="excel-preview" style="height: 200px"></div>`;
    } else if (['doc', 'docx'].includes(ext)) {
      previewHtml = `<img src="https://via.placeholder.com/80x100.png?text=Word" class="img-fluid mb-2" style="height: 200px">`;
    } else {
      previewHtml = `<img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2" style="height: 200px">`;
    }

    return `
      <div class="col file-card" ondblclick="viewFile(${file.id})" style="padding-bottom: 32px; padding-right: 200px;">
        <div class="card shadow-sm" style="width: 13rem; height: 250px; padding-bottom: 16px;">
          <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;">
            ${previewHtml}
            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">${file.name}</h5>
          </div>
        </div>
      </div>
    `;
  }

  function attachFolderEvents() {
    document.querySelectorAll(".folder-button").forEach(button => {
      button.addEventListener("dblclick", function () {
        const folderId = this.dataset.folderid;
        const folderName = this.dataset.foldername;
        folderPath.push({ id: folderId, name: folderName });
        fetchFolderContents(folderId);
      });
    });
  }

  function fetchFolderContents(folderId) {
    $.ajax({
      type: 'GET',
      url: '/operation-courante/select-folder',
      data: { id: folderId },
      success: function (response) {
        renderBreadcrumb();

        // Dossiers
        const folderList = document.getElementById('folderList');
        folderList.innerHTML = '';
        if (response.folders_banque.length > 0) {
          response.folders_banque.forEach(folder => {
            const li = document.createElement('li');
            li.style.listStyleType = 'none';

            const btn = document.createElement('button');
            btn.className = 'folder-button';
            btn.style.cssText = "padding: 0.5rem; background-color: #ffc107; border-radius: 17px; color: #fff; border: none; width: 100%; height: 100%;";
            btn.dataset.folderid = folder.id;
            btn.dataset.foldername = folder.name;
            btn.innerHTML = `<i class="fas fa-folder"></i> ${folder.name}`;

            li.appendChild(btn);
            folderList.appendChild(li);
          });
        } else {
          folderList.innerHTML = '<li style="list-style-type:none;">Aucun dossier disponible</li>';
        }

        // Fichiers
        const fileList = document.getElementById('fileListBanque');
        fileList.innerHTML = '';
        if (response.files_banque.length > 0) {
          // Création d’une div.row pour la grille
          const rowDiv = document.createElement('div');
          rowDiv.className = 'row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3';

          response.files_banque.forEach(file => {
            const fileCardHTML = createFileCard(file);
            // Insère le HTML dans un élément temporaire pour convertir en node
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = fileCardHTML.trim();
            rowDiv.appendChild(tempDiv.firstChild);
          });
          fileList.appendChild(rowDiv);

          // Initialiser les aperçus PDF et Excel après l'ajout
          initPreviews(response.files_banque);
        } else {
          fileList.innerHTML = '<p>Aucun fichier disponible.</p>';
        }

        attachFolderEvents();
      },
      error: function (xhr) {
        alert("Erreur : " + (xhr.responseJSON?.error || "Une erreur est survenue."));
      }
    });
  }

  // Initialiser les aperçus PDF/Excel
  function initPreviews(files) {
    files.forEach(file => {
      const ext = file.name.split('.').pop().toLowerCase();
      if (ext === 'pdf') {
        const url = file.path;
        const canvas = document.getElementById('pdf-preview-' + file.id);
        if (canvas && window.pdfjsLib) {
          const ctx = canvas.getContext('2d');
          pdfjsLib.getDocument(url).promise.then(pdf => {
            pdf.getPage(1).then(page => {
              const scale = 0.5;
              const viewport = page.getViewport({ scale: scale });
              canvas.height = viewport.height;
              canvas.width = viewport.width;
              page.render({ canvasContext: ctx, viewport: viewport });
            });
          });
        }
      } else if (ext === 'xls' || ext === 'xlsx') {
        const url = file.path;
        const previewElement = document.getElementById('excel-preview-' + file.id);
        if (previewElement) {
          fetch(url)
            .then(response => response.arrayBuffer())
            .then(data => {
              const workbook = XLSX.read(data, { type: 'array' });
              const sheet = workbook.Sheets[workbook.SheetNames[0]];
              const html = XLSX.utils.sheet_to_html(sheet, { id: 'excel-preview', editable: false, className: 'excel-preview' });
              previewElement.innerHTML = html;
            });
        }
      }
    });
  }

  // Au chargement initial, attacher les events et afficher breadcrumb
  attachFolderEvents();
  renderBreadcrumb();
});
</script> -->

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




 

@endsection
