<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opérations Courantes</title>
    <link href="https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">
     <!-- jQuery et Luxon -->
     <script src="https://cdn.jsdelivr.net/npm/luxon@2.3.0/build/global/luxon.min.js"></script>
    <style>
        .tabs {
            display: flex;
            border-bottom: 1px solid #ccc;
        }
        .tab {
            padding: 10px 20px;
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
            font-size: 12px;
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
            font-size: 12px;
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
    </style>
</head>
<body>
    <div class="tabs">
        <div class="tab active" data-tab="achats">Achats</div>
        <div class="tab" data-tab="ventes">Ventes</div>
        <div class="tab" data-tab="tresorerie">Trésorerie</div>
        <div class="tab" data-tab="operations-diverses">Opérations Diverses</div>
    </div>

    <!-- Onglet Achats -->
    <div id="achats" class="tab-content active">
        <div class="filter-container">
            <div style="display: flex; align-items: center; gap: 10px;">
                <label for="journal-achats" style="font-size: 12px;">Code :</label>
                <select id="journal-achats" style="font-size: 12px; padding: 4px; width: 120px;"></select>
                <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

                <input type="text" id="filter-intitule-achats" readonly placeholder="Journal" style="font-size: 12px; padding: 4px; width: 120px;" />

                <label for="periode" id="periode-label" style="font-size: 12px;">Période :</label>
                <select id="periode-achats" style="font-size: 12px; padding: 4px; width: 120px;">
                    <option value="janvier">Janvier</option>
                    <option value="fevrier">Février</option>
                </select>

                <div id="annee-input">
                    <label for="annee" style="font-size: 12px;">Année :</label>
                    <input type="text" id="annee-achats" readonly style="font-size: 12px; padding: 4px; width: 120px;" />
                </div>
            </div>

            <div class="radio-container">
                <label>Saisie par :</label>
                <label>
                    <input type="radio" name="filter-period-achats" value="mois" id="filter-mois-achats" checked /> Mois
                </label>
                <label>
                    <input type="radio" name="filter-period-achats" value="exercice" id="filter-exercice-achats" /> Exercice entier
                </label>
            </div>

            <div class="checkbox-container">
                <label>
                    <input type="checkbox" name="filter-type-achats" value="libre" id="filter-libre-achats" checked /> Libre
                </label>
                <label>
                    <input type="checkbox" name="filter-type-achats" value="contre-partie" id="filter-contre-partie-achats" /> Contre-partie automatique
                </label>
            </div>

            <input type="text" id="type-journal-achats" readonly placeholder="Type de Journal" />

            <button class="import-button" id="import-achats">Importer</button>
            <button class="export-button" id="export-achatsExcel">Exporter Excel</button>
            <button class="export-button" id="export-achatsPDF">Exporter PDF</button>
        </div>
        <div id="table-achats"></div>
        <button id="delete-row-btn">Supprrimer une ligne</button>
    </div>

    <!-- Onglet Ventes -->
    <div id="ventes" class="tab-content">
        <div class="filter-container">
            <label for="journal-ventes" style="font-size: 12px;">Code :</label>
            <select id="journal-ventes" style="font-size: 12px; padding: 4px; width: 120px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-ventes" readonly placeholder="Journal" style="font-size: 12px; padding: 4px; width: 120px;" />

            <label for="periode" id="periode-label" style="font-size: 12px;">Période :</label>
            <select id="periode-ventes" style="font-size: 12px; padding: 4px; width: 120px;">
                <option value="janvier">Janvier</option>
                <option value="fevrier">Février</option>
            </select>

            <div class="radio-container">
                <label>Saisie par :</label>
                <label>
                    <input type="radio" name="filter-period-ventes" value="mois" id="filter-mois-ventes" checked /> Mois
                </label>
                <label>
                    <input type="radio" name="filter-period-ventes" value="exercice" id="filter-exercice-ventes" /> Exercice entier
                </label>
            </div>

            <div class="checkbox-container">
                <label>
                    <input type="checkbox" name="filter-type-ventes" value="libre" id="filter-libre-ventes" checked /> Libre
                </label>
                <label>
                    <input type="checkbox" name="filter-type-ventes" value="contre-partie" id="filter-contre-partie-ventes" /> Contre-partie automatique
                </label>
            </div>

            <input type="text" id="type-journal-ventes" readonly placeholder="Type de Journal" />

            <button class="import-button" id="import-ventes">Importer</button>
            <button class="export-button" id="export-ventes">Exporter Excel</button>
        </div>
        <div id="table-ventes"></div>
        <button id="delete-row-btn">Supprrimer une ligne</button>
    </div>

    <!-- Onglet Trésorerie -->
    <div id="tresorerie" class="tab-content">
        <div class="filter-container">
            <label for="journal-tresorerie" style="font-size: 12px;">Code :</label>
            <select id="journal-tresorerie" style="font-size: 12px; padding: 4px; width: 120px;"></select>
            <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

            <input type="text" id="filter-intitule-tresorerie" readonly placeholder="Journal" style="font-size: 12px; padding: 4px; width: 120px;" />

            <label for="periode" id="periode-label" style="font-size: 12px;">Période :</label>
            <select id="periode-tresorerie" style="font-size: 12px; padding: 4px; width: 120px;">
                <option value="janvier">Janvier</option>
                <option value="fevrier">Février</option>
            </select>

            <div class="radio-container">
                <label>Saisie par :</label>
                <label>
                    <input type="radio" name="filter-period-tresorerie" value="mois" id="filter-mois-tresorerie" checked /> Mois
                </label>
                <label>
                    <input type="radio" name="filter-period-tresorerie" value="exercice" id="filter-exercice-tresorerie" /> Exercice entier
                </label>
            </div>

            <div class="checkbox-container">
                <label>
                    <input type="checkbox" name="filter-type-tresorerie" value="libre" id="filter-libre-tresorerie" checked /> Libre
                </label>
                <label>
                    <input type="checkbox" name="filter-type-tresorerie" value="contre-partie" id="filter-contre-partie-tresorerie" /> Contre-partie automatique
                </label>
            </div>

            <input type="text" id="type-journal-tresorerie" readonly placeholder="Type de Journal" />

            <button class="import-button" id="import-tresorerie">Importer</button>
            <button class="export-button" id="export-tresorerieExcel">Exporter Excel</button>
            <button class="export-button" id="export-tresoreriePDF">Exporter PDF</button>

        </div>
        <div id="table-tresorerie"></div>
        <button id="delete-row-btn">Supprrimer une ligne</button>
    </div>

    <!-- Onglet Opérations Diverses -->
    <div id="operations-diverses" class="tab-content">
            <div class="filter-container">
                <label for="journal-operations-diverses" style="font-size: 12px;">Code :</label>
                <select id="journal-operations-diverses" style="font-size: 12px; padding: 4px; width: 120px;"></select>
                <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

                <input type="text" id="filter-intitule-operations-diverses" readonly placeholder="Journal" style="font-size: 12px; padding: 4px; width: 120px;" />

                <label for="periode" id="periode-label" style="font-size: 12px;">Période :</label>
                <select id="periode-operations-diverses" style="font-size: 12px; padding: 4px; width: 120px;">
                    <option value="janvier">Janvier</option>
                    <option value="fevrier">Février</option>
                </select>

                <div class="radio-container">
                    <label>Saisie par :</label>
                    <label>
                        <input type="radio" name="filter-period-operations-diverses" value="mois" id="filter-mois-operations-diverses" checked /> Mois
                    </label>
                    <label>
                        <input type="radio" name="filter-period-operations-diverses" value="exercice" id="filter-exercice-operations-diverses" /> Exercice entier
                    </label>
                </div>

                <div class="checkbox-container">
                    <label>
                        <input type="checkbox" name="filter-type-operations-diverses" value="libre" id="filter-libre-operations-diverses" checked /> Libre
                    </label>
                    <label>
                        <input type="checkbox" name="filter-type-operations-diverses" value="contre-partie" id="filter-contre-partie-operations-diverses" /> Contre-partie automatique
                    </label>
                </div>

                <input type="text" id="type-journal-operations-diverses" readonly placeholder="Type de Journal" />

                <button class="import-button" id="import-operations-diverses">Importer</button>
                <button class="export-button" id="export-operations-diverses">Exporter Excel</button>
            </div>
            <div id="table-operations-diverses"></div>
            <button id="delete-row-btn">Supprrimer une ligne</button>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js"></script>
    <script>
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



            const fournisseurs = ['Fournisseur 1', 'Fournisseur 2', 'Fournisseur 3'];  // Exemple de fournisseurs
            const comptes = ['Compte 1', 'Compte 2', 'Compte 3'];  // Exemple de comptes
            const rubriquesTva = ['Rubrique 1', 'Rubrique 2'];  // Exemple de rubriques TVA
            const comptesFiltrés = [{compte: 'Compte TVA 1'}, {compte: 'Compte TVA 2'}];  // Exemple de comptes TVA filtrés

            const table = new Tabulator("#table-achats", {
                height: "400px",
                layout: "fitColumns",
                selectable: true,
                data: Array(1).fill({}), // Ajoute 3 lignes vides au démarrage
                ajaxURL: "/get-operations",
                ajaxResponse: function (url, params, response) {
                    return response;
                },
                columns: [
                    { title: "ID", field: "id", visible: false },
                    { title: "Date", field: "date", hozAlign: "center", sorter: "date" },
                    { title: "N° facture", field: "numero_facture", editor: "input" },
                    { title: "Compte", field: "compte", editor: "list", editorParams: { autocomplete: true, listOnEmpty: true, values: fournisseurs }},
                    { title: "Libellé", field: "libelle", editor: "input" },
                    { title: "Débit", field: "debit", editor: "number", bottomCalc: "sum" },
                    { title: "Crédit", field: "credit", editor: "number", bottomCalc: "sum" },
                    { title: "Contre-Partie", field: "contre_Partie", editor: "list", editorParams: { autocomplete: true, listOnEmpty: true, values: comptes } },
                    { title: "Rubrique TVA", field: "rubrique_tva", editor: "list", editorParams: { autocomplete: true, listOnEmpty: true, values: rubriquesTva } },
                    {
                        title: "Compte TVA",
                        field: "compte_tva",
                        editor: "list",
                        editorParams: {
                            autocomplete: true,
                            listOnEmpty: true,
                            values: comptesFiltrés.map(compte => compte.compte),
                        },
                    },
                    { title: "Prorat de deduction", field: "prorat_de_deduction", editor: "input" },
                    { title: "Pièce", field: "piece_justificative", editor: "input" },
                    { title: "type_journal", field: "type_Journal", visible: false },
                ],
            });

            new Tabulator("#table-ventes", {
                layout: "fitColumns",
                height: "400px",
                selectable: true,
                data: Array(1).fill({}), // Ajoute 3 lignes vides au démarrage

                columns: [
                    { title: "Date", field: "date" },
                    { title: "N° dossier", field: "numero_dossier", editor: "input" },
                    { title: "Compte", field: "compte" },
                    { title: "Libellé", field: "libelle" },
                    { title: "Montant", field: "montant", formatter: "money" },
                ],
                data: [],
            });

            new Tabulator("#table-tresorerie", {
                layout: "fitColumns",
                height: "400px",
                selectable: true,
                data: Array(1).fill({}), // Ajoute 3 lignes vides au démarrage

                columns: [
                    { title: "Date", field: "date" },
                    { title: "N° dossier", field: "numero_dossier", editor: "input" },
                    { title: "Compte", field: "compte" },
                    { title: "Libellé", field: "libelle" },
                    { title: "Montant", field: "montant", formatter: "money" },
                ],
                data: [],
            });

            new Tabulator("#table-operations-diverses", {
                layout: "fitColumns",
                height: "400px",
                selectable: true,
                data: Array(1).fill({}), // Ajoute 3 lignes vides au démarrage

                columns: [
                    { title: "Date", field: "date" },
                    { title: "N° dossier", field: "numero_dossier", editor: "input" },
                    { title: "Compte", field: "compte" },
                    { title: "Libellé", field: "libelle" },
                    { title: "Montant", field: "montant", formatter: "money" },
                ],
                data: [],
            });
            // Gestion des onglets
            $('.tab').on('click', function () {
                const tabId = $(this).data('tab');
                $('.tab').removeClass('active');
                $('.tab-content').removeClass('active');
                $(this).addClass('active');
                $('#' + tabId).addClass('active');
            });

            // Fonction pour charger l'exercice social
    function loadExerciceSocial() {
        $.get('/session-social', function(data) {
            const exerciceDebut = data.exercice_social_debut;
            const anneeDebut = new Date(exerciceDebut).getFullYear();

            // Afficher l'année de début de l'exercice
            $('#annee-achats').val(anneeDebut);

            // Appeler la fonction pour peupler les mois si le bouton "Mois" est sélectionné
            if ($('input[name="filter-period-achats"]:checked').val() === 'mois') {
                populateMonths(anneeDebut);
            }
        }).fail(function() {
            console.error('Erreur lors du chargement de l\'exercice social.');
        });
    }

    function populateMonths(anneeDebut) {
  const currentYear = new Date().getFullYear();
  const periodeAchatsSelect = $('#periode-achats');
  periodeAchatsSelect.empty(); // Effacer les options existantes

  for (let year = anneeDebut; year <= currentYear; year++) {
    for (let month = 1; month <= 12; month++) {
      const monthName = getMonthName(month);
      const optionValue = `${month}-${year}`;
      periodeAchatsSelect.append(`<option value="${optionValue}">${monthName} ${year}</option>`);
    }
  }
}


    // Fonction pour obtenir le nom du mois en français
    function getMonthName(month) {
        const date = new Date(0);
        date.setUTCMonth(month - 1);
        return date.toLocaleString('fr-FR', { month: 'long' });
    }

    // Appeler la fonction pour charger l'exercice social au chargement de la page
    loadExerciceSocial();

    // Gestionnaire d'événement pour les boutons radio
    $('input[name="filter-period-achats"]').on('change', function() {
        if ($(this).val() === 'mois') {
            $('#periode-achats').show();
            $('#annee-input').hide();
            populateMonths(new Date($('#annee-achats').val()).getFullYear()); // Utiliser l'année actuelle si l'exercice n'est pas défini
        } else if ($(this).val() === 'exercice') {
            $('#periode-achats').hide();
            $('#annee-input').show();
        }
    });
    $.get('/periodes', function(data) {
        $('#periode-achats').empty();

        data.forEach(periode => {
            $('#periode-achats').append(`<option value="${periode}">${periode}</option>`);
        });
    });

        });
    </script>
</body>
</html>
