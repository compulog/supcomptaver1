
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


document.addEventListener("DOMContentLoaded", function () {
    // Liste des sections
    const sections = ["achats", "ventes", "Caisse", "Banque","operations-diverses"];

    // Fonction pour initialiser une section
    function initializeSection(section) {
        const radioMois = document.getElementById(`filter-mois-${section}`);
        const radioExercice = document.getElementById(`filter-exercice-${section}`);
        const periodeContainer = document.getElementById(`periode-${section}`);
        const anneeInput = document.getElementById(`annee-${section}`);

        // Vérification des éléments requis
        if (!radioMois || !radioExercice || !periodeContainer || !anneeInput) {
            console.warn(`Certains éléments de la section "${section}" sont manquants.`);
            return; // Sortir si des éléments sont introuvables
        }

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
var tableAch, tableVentes, tableBanque, tableCaisse,tableOP;
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
            $('#annee-Caisse').val(anneeDebut);
            $('#annee-Banque').val(anneeDebut);
          $('#annee-operations-diverses').val(anneeDebut);

            // Peupler les périodes pour tous les onglets
            populateMonths('achats', periodesData);
            populateMonths('ventes', periodesData);
            populateMonths('Banque', periodesData);
            populateMonths('Caisse', periodesData);
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

    // Ajouter l'option "Sélectionner un mois" comme option par défaut
    periodeSelect.append('<option value="selectionner un mois">Sélectionner un mois</option>');

    // Ajouter les options de mois et années
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

    // Si aucune sélection précédente n'existe, laisser "Sélectionner un mois" sélectionné
    if (previousSelection) {
        periodeSelect.val(previousSelection);
    } else {
        // S'assurer que l'option "Sélectionner un mois" reste sélectionnée
        periodeSelect.val('selectionner un mois');
    }

    console.log("Options ajoutées dans #" + onglet + ":", periodeSelect.html());
}

// Fonction pour mettre à jour la date dans toutes les tables Tabulator
function updateTabulatorDate(year, month) {
    const formattedDate = `${year}-${month.padStart(2, '0')}-01`;

    // Met à jour la date dans toutes les tables Tabulator
    [tableAch, tableVentes, tableBanque,tableCaisse,tableOP].forEach(function(table) {
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
    const onglets = ['Achats', 'Ventes', 'Banque','Caisse','Operations-diverses'];

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
    ['achats', 'ventes', 'Banque','Caisse','operations'].forEach(onglet => {
        setupPeriodChangeHandler(onglet);
    });
});



var tableAch, tableVentes, tableBanque,tableCaisse,tableOP;

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
    const selectedJournal = $('#journal-achats').val() || $('#journal-ventes').val() || $('#journal-Banque').val() || $('#journal-Caisse').val() || $('#journal-operations-diverses').val();

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
loadJournaux('Banque', '#journal-Banque');
loadJournaux('Caisse', '#journal-Caisse');
loadJournaux('operations-diverses', '#journal-operations-diverses');

// Gestion des changements de journal
$('select').on('change', function () {
    const selectedOption = $(this).find(':selected');
    const intituleJournal = selectedOption.data('intitule');
    const tabId = $(this).attr('id').replace('journal-', 'filter-intitule-');
    $('#' + tabId).val(intituleJournal ? 'journal - ' + intituleJournal : '');
});

class TabulatorManager {
    constructor(journalSelectors) {
        this.journalSelectors = journalSelectors;
        this.initEventListeners();
    }

    // Vérifie si un journal est sélectionné
    isJournalSelected() {
        return this.journalSelectors.some(selector => $(selector).val());
    }

    // Affiche une alerte et empêche l'action si aucun journal n'est sélectionné
    validateJournalSelection() {
        if (!this.isJournalSelected()) {
            alert("Veuillez renseigner le code journal avant de continuer.");
            return false;
        }
        return true;
    }

    // Empêche l'édition d'une cellule si aucun journal n'est sélectionné
    preventEditingIfNoJournal(cell) {
        if (!this.validateJournalSelection()) {
            cell.cancelEdit();
            return false;
        }
    }

    // Applique la validation à une instance Tabulator donnée
    applyToTabulator(tableInstance) {
        tableInstance.on("cellEditing", (cell) => this.preventEditingIfNoJournal(cell));
    }

    // Ajoute un écouteur global pour empêcher les actions si aucun journal n'est sélectionné
    initEventListeners() {
        $("input, select").on("change", () => this.validateJournalSelection());
    }
}
// Liste des sélecteurs des journaux (à adapter selon votre projet)
const journalSelectors = [
    '#journal-achats',
    '#journal-ventes',
    '#journal-Banque',
    '#journal-Caisse',
    '#journal-operations-diverses'
];
// Initialiser le gestionnaire de Tabulator avec les sélecteurs des journaux
const tabulatorManager = new TabulatorManager(journalSelectors);






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

                let selectedCodeJournal = null; // Stocker le code journal sélectionné

                // Récupérer le code journal lorsqu'il change dans le dropdown
                document.getElementById("journal-achats").addEventListener("change", function () {
                    selectedCodeJournal = this.value; // Mettre à jour la variable globale
                    console.log("Code journal sélectionné :", selectedCodeJournal);
                });

        // Table des achats
        var tableAch = new Tabulator("#table-achats", {
            height: "500px",
            layout: "fitColumns",
             clipboard:true,
           clipboardPasteAction:"replace",
           placeholder: "Aucune donnée disponible",
    ajaxResponse: function(url, params, response) {
        console.log("Données reçues :", response);

        // Ajouter une ligne vide si elle n'est pas déjà présente
        if (response.length === 0 || response[0].id !== "") {
            response.unshift({
                id: "",
                date: "",
                debit: "",
                credit: "",
            });
        }

        return response; // Passe les données modifiées à Tabulator
    },
    ajaxError: function(xhr, textStatus, errorThrown) {
        console.error("Erreur AJAX :", textStatus, errorThrown);
    },
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
            // data: Array(1).fill({}),

            columns: [
                { title: "ID", field: "id", visible: false },

                {
                    title: "Date",
                    field: "date",
                    hozAlign: "center",
                    headerFilter: "input",
                    sorter: "date",
                    editor: function (cell, onRendered, success, cancel) {
                      // Création d'un conteneur pour l'éditeur
                      const container = document.createElement("div");
                      container.style.display = "flex";
                      container.style.alignItems = "center";

                      // Création de l'input pour saisir la date (jour seulement, le mois et l'année étant gérés via des filtres externes)
                      const input = document.createElement("input");
                      input.type = "text";
                      input.style.flex = "1";
                      input.placeholder = "jj/";

                      // Si une date existe déjà, on l'affiche au format "dd/MM/yyyy"
                      const currentValue = cell.getValue();
                      if (currentValue) {
                        let dt = luxon.DateTime.fromFormat(currentValue, "yyyy-MM-dd HH:mm:ss");
                        if (!dt.isValid) {
                          dt = luxon.DateTime.fromISO(currentValue);
                        }
                        if (dt.isValid) {
                          input.value = dt.toFormat("dd/MM/yyyy");
                        }
                      }

                      input.addEventListener("blur", function () {
                        // Récupération des filtres ou champs externes pour le mois, l'année et le code journal
                        const moisSelect = document.getElementById("periode-achats");
                        const anneeInput = document.getElementById("annee-achats");
                        const codeJournalInput = document.getElementById("journal-achats");

                        // Extraction des valeurs saisies
                        const dayStr = input.value.slice(0, 2);
                        const day = parseInt(dayStr, 10);
                        const month = parseInt(moisSelect.value, 10);
                        const year = parseInt(anneeInput.value, 10);
                        const codeJournal = (codeJournalInput.value || "CJ").trim();

                        if (!isNaN(day) && !isNaN(month) && !isNaN(year)) {
                          const dt = luxon.DateTime.local(year, month, day);
                          if (dt.isValid) {
                            // Récupération de la table et de ses lignes
                            const table = cell.getTable();
                            const rows = table.getRows();
                            const currentRow = cell.getRow();
                            const rowData = currentRow.getData();
                            const numeroFacture = rowData.numero_facture;

                            // Calculer les totaux débit et crédit pour toutes les lignes de la même facture
                            let totalDebit = 0, totalCredit = 0;
                            const lignesFacture = rows.filter(r => {
                              const data = r.getData();
                              return data.numero_facture === numeroFacture;
                            });
                            lignesFacture.forEach(r => {
                              const data = r.getData();
                              totalDebit += parseFloat(data.debit) || 0;
                              totalCredit += parseFloat(data.credit) || 0;
                            });

                            // Si la facture est équilibrée (et non nulle)
                            if (totalDebit === totalCredit && totalDebit !== 0) {
                              const moisFormatted = dt.toFormat("MM");
                              const prefix = `P${moisFormatted}${codeJournal}`;

                              // Rechercher dans l'ensemble des lignes les numéros déjà attribués pour ce mois et ce code journal
                              const existingNumbers = [];
                              rows.forEach(r => {
                                const data = r.getData();
                                if (data.piece_justificative && data.piece_justificative.startsWith(prefix)) {
                                  const numStr = data.piece_justificative.substring(prefix.length);
                                  const num = parseInt(numStr, 10);
                                  if (!isNaN(num)) {
                                    existingNumbers.push(num);
                                  }
                                }
                              });
                              existingNumbers.sort((a, b) => a - b);
                              const newIncrement = existingNumbers.length > 0 ? existingNumbers[existingNumbers.length - 1] + 1 : 1;
                              const numeroFormate = String(newIncrement).padStart(4, "0");
                              const pieceJustificative = `P${moisFormatted}${codeJournal}${numeroFormate}`;

                              // Mettre à jour la pièce justificative pour la ligne en cours
                              currentRow.update({ piece_justificative: pieceJustificative });
                              console.log("Nouvelle pièce générée :", pieceJustificative);
                            }

                            // Retourner la date au format stocké (par exemple "yyyy-MM-dd HH:mm:ss")
                            success(dt.toFormat("yyyy-MM-dd HH:mm:ss"));
                            return;
                          }
                        }

                        // En cas de saisie invalide, on annule la modification
                        alert("Veuillez saisir une date valide (jour, et vérifier les filtres pour le mois et l'année).");
                        cancel();
                      });

                      container.appendChild(input);
                      onRendered(() => input.focus());
                      return container;
                    },
                    formatter: function (cell) {
                      const dateValue = cell.getValue();
                      if (dateValue) {
                        let dt = luxon.DateTime.fromFormat(dateValue, "yyyy-MM-dd HH:mm:ss");
                        if (!dt.isValid) {
                          dt = luxon.DateTime.fromISO(dateValue);
                        }
                        return dt.isValid ? dt.toFormat("dd/MM/yyyy") : dateValue;
                      }
                      return "";
                    },
                  },



           { title: "N° facture", field: "numero_facture",headerFilter: "input",

           editor: "input" },
           {
    title: "Compte",
    field: "compte",
    headerFilter: "input",

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
                        contre_partie: contrePartie,
                        rubrique_tva: rubriqueTVA,
                        taux_tva: tauxTVA,
                        libelle: `F° ${numeroFacture} ${fournisseur.intitule}`,
                        compte_tva: comptesVentes.length > 0
                            ? `${comptesVentes[0].compte} - ${comptesVentes[0].intitule}`
                            : "",
                    });

                    const rowData = row.getData();


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
    cellEdited: function (cell) {
        // Simple log de la valeur éditée (aucune autre condition ou traitement)
        console.log("Valeur Crédit mise à jour :", cell.getValue());
    }
},



{
    title: "Contre-Partie",
    field: "contre_partie",
    headerFilter: "input",
    editor: "list",
    editorParams: {
        autocomplete: true,
        listOnEmpty: true,
        valuesLookup: async function (cell) {
            if (!selectedCodeJournal) {
                alert("Veuillez sélectionner un code journal avant de modifier la Contre-Partie.");
                return []; // Retourner une liste vide si aucun code journal n'est sélectionné
            }

            try {
                const response = await fetch(`/get-contre-parties?code_journal=${selectedCodeJournal}`);
                if (!response.ok) {
                    throw new Error("Erreur réseau ou code journal non valide.");
                }

                const data = await response.json();

                if (data.error) {
                    console.error("Erreur serveur :", data.error);
                    return [];
                }

                console.log("Contre-Parties récupérées :", data);
                return data; // Retourner les valeurs récupérées
            } catch (error) {
                console.error("Erreur réseau :", error);
                alert("Impossible de récupérer les contre-parties.");
                return [];
            }
        },
    },
    cellEdited: function (cell) {
        console.log("Contre-Partie mise à jour :", cell.getValue());
    },
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
                    },
                    // visible: assujettiePartielleTVA == 1 // Affichage conditionnel
                },
                {
    title: "Solde Cumulé",
    field: "value", // Ce champ contient le solde cumulé calculé (issu de ton mapping: value: ligne.solde_cumule)
    // editor: "input", // Permet l'édition manuelle si besoin (tu peux le supprimer si le solde doit être uniquement calculé)
    headerFilter: "input",
    formatter: function(cell, formatterParams, onRendered) {
      // Formatage en nombre avec 2 décimales (si la valeur est numérique)
      let val = cell.getValue();
      if(val !== "" && !isNaN(val)) {
        return parseFloat(val).toFixed(2);
      }
      return val;
    }
  },

{
    title: "Pièce",
field: "piece_justificative",
editor: "input", // Éditeur pour permettre la modification manuelle
headerFilter: "input",

},

{
            title: "Sélectionner",
            headerSort: false,
            resizable: true,
            frozen: true,
            width: 50,
            minWidth: 40,
            headerHozAlign: "center",
            hozAlign: "center",
            formatter: "rowSelection",
            titleFormatter: "rowSelection",
            cellClick: function(e, cell){
                cell.getRow().toggleSelect();
            }
        },

  { title: "Code_journal", field: "type_Journal", visible: false },

                ],

                initialSort: [
                    { column: "ordre", dir: "asc" }
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

// Événement de mise à jour des champs
// Récupérer le token CSRF du meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Empêcher l'édition d'une ligne sans ID
tableAch.on("cellEditing", function (cell) {
    const row = cell.getRow();
    const data = row.getData();

    if (!data.id) {
        console.log("Édition ignorée : nouvelle ligne sans ID.");
        return false; // Annuler l'édition sans alerte
    }

    return true; // Autoriser l'édition si la ligne a un ID
});

tableAch.on("cellEdited", function (cell) {
    const row = cell.getRow();
    const data = row.getData();

    // Vérifier si la ligne a un ID (éviter les erreurs)
    if (!data.id) {
        console.log("Modification ignorée : ligne sans ID.");
        return;
    }

    // Vérifier si la ligne est vide
    const isEmpty = !data.numero_facture && !data.compte && !data.credit;
    if (isEmpty) {
        console.log("Mise à jour ignorée : ligne existante mais vide.");
        return; // Ne rien envoyer si la ligne est vide
    }

    // Vérifier si tous les champs obligatoires sont remplis
    if (!data.numero_facture || !data.compte || !data.credit) {
        console.log("Mise à jour bloquée : certains champs obligatoires sont vides.");
        return; // Bloquer la mise à jour sans message d'alerte
    }

    // Si on arrive ici, la mise à jour est autorisée
    const field = cell.getField();
    const value = cell.getValue();
    const numeroFacture = data.numero_facture;

    // Préparer les données à envoyer sans `debit` et `credit`
    const updateData = {
        field: field,
        value: value,
        numero_facture: numeroFacture
    };

    // Si la modification concerne le champ `debit` ou `credit`, ne pas l'inclure dans la mise à jour
    if (field === "debit" || field === "credit") {
        console.log(`Modification de "${field}" ignorée : champ exclu de la mise à jour.`);
        return; // Ne pas envoyer ces champs
    }

    // Préparer la requête PUT pour la ligne concernée
    fetch(`/operations/${data.id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify(updateData),
    })
    .then(response => {
        if (!response.ok) {
            console.error(`Erreur HTTP ${response.status}`);
            return;
        }
        return response.json();
    })
    .then(result => {
        console.log("Mise à jour réussie :", result);
    })
    .catch(error => {
        console.error("Erreur de mise à jour :", error);
    });
});



// Ajouter l'écouteur pour mettre à jour "type_Journal"
document.querySelector("#journal-achats").addEventListener("change", function (e) {
    const selectedCode = e.target.value;

    let ligneSelectionnee = tableAch.getSelectedRows()[0];
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

},
{ title: "Crédit", field: "credit", headerFilter: "input", editor: "number", // Permet l'édition en tant que nombre
bottomCalc: "sum", // Calcul du total dans le bas de la colonne
formatter: function(cell) {
// Formater pour afficher 0.00 si la cellule est vide ou nulle
const value = cell.getValue();
return value ? parseFloat(value).toFixed(2) : "0.00";
},
 },
        {
                                    title: "Contre-Partie",
                                    field: "contre_partie",
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
        },
        {
    title: "Sélectionner",
    headerSort: false,
    resizable: true,
    frozen: true,
    width: 50,
    minWidth: 40,
    headerHozAlign: "center",
    hozAlign: "center",
    formatter: "rowSelection",
    titleFormatter: "rowSelection",
    cellClick: function(e, cell){
        cell.getRow().toggleSelect();
    }
},



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


// Fonction pour supprimer les doublons en fonction de l'id
function supprimerDoublonsLignes(lignes) {
    const lignesUniquement = []; // Tableau pour stocker les lignes sans doublons
    const idsDejaAjoutes = new Set(); // Un Set pour suivre les ids déjà rencontrés

    lignes.forEach(ligne => {
        if (!idsDejaAjoutes.has(ligne.id)) {
            lignesUniquement.push(ligne); // Ajouter la ligne si son id n'a pas encore été rencontré
            idsDejaAjoutes.add(ligne.id); // Ajouter l'id au Set
        }
    });

    return lignesUniquement; // Retourner le tableau sans doublons
}

// =====================================================================
// Fonction pour ajouter une ligne au tableau
async function ajouterLigne(table, preRemplir = false, ligneActive = null) {
    let nouvellesLignes = []; // Tableau vide pour stocker les nouvelles lignes
    let idCounter = table.getData().length + 1; // Générer un ID unique pour chaque ligne ajoutée

    let codeJournal = document.querySelector("#journal-achats").value;
    let moisActuel = new Date().getMonth() + 1; // Mois courant (1-12)

    // Récupérer la valeur du filtre sélectionné
    let filterAchats = document.querySelector('input[name="filter-achats"]:checked')?.value;
    if (!filterAchats) {
        alert("Veuillez sélectionner un filtre.");
        return;
    }

    if (preRemplir && ligneActive) {
        // La fonction ajouterLignePreRemplie doit retourner un tableau
        nouvellesLignes = await ajouterLignePreRemplie(idCounter, ligneActive, codeJournal, moisActuel, filterAchats);
        console.log("Lignes pré-remplies générées:", nouvellesLignes);
    } else {
        let ligneVide = ajouterLigneVide(idCounter, ligneActive, codeJournal, moisActuel);
        nouvellesLignes.push(ligneVide);
    }

    // Vérifier que nouvellesLignes est bien un tableau
    if (Array.isArray(nouvellesLignes)) {
        nouvellesLignes.forEach(ligne => {
            table.addRow(ligne, false);
        });
    } else {
        console.error("Erreur: nouvellesLignes n'est pas un tableau.");
    }

    console.log("Toutes les lignes du tableau après ajout:", table.getData());

    // Optionnel : supprimer les doublons si nécessaire
    const lignesSansDoublons = supprimerDoublonsLignes(table.getData());
    console.log("Lignes après suppression des doublons:", lignesSansDoublons);

    return nouvellesLignes;
}

// =====================================================================
// Exemple de fonction pour ajouter une ligne pré-remplie
async function ajouterLignePreRemplie(idCounter, ligneActive, codeJournal, moisActuel, filterAchats) {
    let lignes = [];
    let ligne1 = { ...ligneActive, id: idCounter++ };
    let ligne2 = { ...ligneActive, id: idCounter++ };

    console.log("Ajout des lignes pré-remplies avec filterAchats:", filterAchats);

    const creditPremierLigne = parseFloat(ligneActive.credit) || 0;
    console.log("Crédit de la première ligne:", creditPremierLigne);

    if (filterAchats === 'contre-partie') {
        // Ligne 1
        ligne1.compte = ligneActive.contre_partie || '';
        ligne1.contre_partie = ligneActive.compte || '';
        ligne1.debit = 0;  // Calculé ultérieurement
        ligne1.credit = 0; // Forcer à 0
        ligne1.piece = ligneActive.piece; // (Si vous utilisez ce champ ailleurs)
        ligne1.type_journal = codeJournal || '';
        lignes.push(ligne1);

        // Ligne 2
        ligne2.compte = ligneActive.compte_tva || '';
        ligne2.contre_partie = ligne1.compte || '';
        ligne2.debit = 0;
        ligne2.credit = 0;
        ligne2.piece = ligneActive.piece;
        ligne2.type_journal = codeJournal || '';
        lignes.push(ligne2);
    }

    console.log("Lignes pré-remplies générées:", lignes);

    if (Array.isArray(lignes)) {
        for (let i = 0; i < lignes.length; i++) {
            const typeLigne = (i === 0) ? "ligne1" : "ligne2";
            console.log(`Calcul du débit pour ${typeLigne}:`, lignes[i]);
            await calculerDebit(lignes[i], typeLigne, creditPremierLigne);
            console.log(`Débit calculé pour ${typeLigne}:`, lignes[i].debit);
        }
    } else {
        console.error("Erreur: 'lignes' n'est pas un tableau:", lignes);
    }

    return lignes;
}

// =====================================================================
// Fonction pour calculer dynamiquement le débit
async function calculerDebit(rowData, typeLigne, credit) {
    const tauxTVA = parseFloat(rowData.taux_tva || 0) / 100;
    console.log(`Calcul du débit pour ${typeLigne}: Crédit = ${credit}, Taux TVA = ${tauxTVA}`);

    if (isNaN(credit) || isNaN(tauxTVA)) {
        console.error("Crédit ou Taux TVA invalides !");
        rowData.debit = 0;
        return;
    }

    const prorataDeDeduction = (rowData.prorat_de_deduction || "Non").trim().toLowerCase();
    const isProrataOui = prorataDeDeduction === "oui";
    let prorata = 0;

    if (isProrataOui) {
        try {
            const response = await fetch('/get-session-prorata');
            if (!response.ok) throw new Error(`Erreur réseau : ${response.statusText}`);
            const data = await response.json();
            prorata = parseFloat(data.prorata_de_deduction) || 0;
        } catch (error) {
            console.error('Erreur lors de la récupération du prorata de déduction :', error);
        }
    }

    let debit = 0;
    if (typeLigne === "ligne1") {
        debit = isProrataOui
            ? (credit / (1 + tauxTVA)) + (((credit / (1 + tauxTVA)) * tauxTVA) * (1 - prorata / 100))
            : credit / (1 + tauxTVA);
    } else if (typeLigne === "ligne2") {
        debit = isProrataOui
            ? ((credit / (1 + tauxTVA)) * tauxTVA) * (prorata / 100)
            : (credit / (1 + tauxTVA)) * tauxTVA;
    }

    rowData.debit = parseFloat(debit.toFixed(2));
    console.log(`Débit final pour ${typeLigne}: ${rowData.debit}`);
}

// =====================================================================
// Fonction pour mettre à jour le champ piece_justificative selon les règles
function updatePieceJustificative(data) {
    // Regrouper les lignes par numéro de facture
    const factures = {};
    data.forEach(row => {
        // S'assurer que le numéro de facture existe et le normaliser
        const nf = row.numero_facture && row.numero_facture.trim();
        if (nf) {
            if (!factures[nf]) {
                factures[nf] = [];
            }
            factures[nf].push(row);
        }
    });

    // Pour chaque facture
    Object.keys(factures).forEach(nf => {
        const rows = factures[nf];
        let totalDebit = 0, totalCredit = 0;
        rows.forEach(row => {
            totalDebit += parseFloat(row.debit) || 0;
            totalCredit += parseFloat(row.credit) || 0;
        });

        // Seulement si la facture est équilibrée
        if (totalDebit === totalCredit && totalDebit !== 0) {
            // On prend la date de la première ligne pour extraire le mois
            let dt = luxon.DateTime.fromFormat(rows[0].date, "yyyy-MM-dd HH:mm:ss");
            if (!dt.isValid) {
                dt = luxon.DateTime.fromISO(rows[0].date);
            }
            if (!dt.isValid) {
                console.warn("Date invalide pour la facture " + nf);
                return;
            }
            const moisFormatted = dt.toFormat("MM");

            // Récupérer le code journal à partir de la ligne ou du filtre
            const codeJournal = rows[0].type_journal || document.querySelector("#journal-achats").value || "CJ";

            // Rechercher dans l'ensemble des données les pièces existantes pour ce mois et ce code journal
            let existingNumbers = [];
            data.forEach(row => {
                if (row.piece_justificative) {
                    // On attend le format : P{MM}{codeJournal}{NNNN}
                    const prefix = `P${moisFormatted}${codeJournal}`;
                    if (row.piece_justificative.startsWith(prefix)) {
                        const numStr = row.piece_justificative.substring(prefix.length);
                        const num = parseInt(numStr, 10);
                        if (!isNaN(num)) {
                            existingNumbers.push(num);
                        }
                    }
                }
            });
            existingNumbers.sort((a, b) => a - b);
            const newIncrement = existingNumbers.length > 0 ? existingNumbers[existingNumbers.length - 1] + 1 : 1;
            const numeroFormate = String(newIncrement).padStart(4, "0");
            const newPiece = `P${moisFormatted}${codeJournal}${numeroFormate}`;

            // Mettre à jour le champ piece_justificative pour toutes les lignes de cette facture
            rows.forEach(row => {
                row.piece_justificative = newPiece;
            });
        }
    });

    return data;
}

// =====================================================================
// Fonction pour enregistrer les lignes
async function enregistrerLignesAch() {
    try {
      // Récupérer les données actuelles du tableau
      let lignes = tableAch.getData();
      console.log("📌 Données récupérées du tableau :", lignes);

      const codeJournal = document.querySelector("#journal-achats").value;
      if (!codeJournal) {
        alert("⚠️ Veuillez sélectionner un code journal.");
        return;
      }

      const selectedFilter = document.querySelector('input[name="filter-achats"]:checked')?.value || null;

      // Mettre à jour le champ piece_justificative pour chaque facture
      lignes = updatePieceJustificative(lignes);

      // Filtrer les lignes valides à envoyer et ajouter le champ "ordre" selon leur position actuelle
      const lignesAEnvoyer = lignes
        .filter(ligne => ligne.compte && (parseFloat(ligne.debit) > 0 || parseFloat(ligne.credit) > 0))
        .map((ligne, index) => ({
          id: ligne.id || null,
          ordre: index, // Indique la position dans le tableau
          date: ligne.date || new Date().toISOString().slice(0, 10),
          numero_facture: ligne.numero_facture || 'N/A',
          compte: ligne.compte || '',
          debit: ligne.debit ? parseFloat(ligne.debit) : 0,
          credit: ligne.credit ? parseFloat(ligne.credit) : 0,
          contre_partie: ligne.contre_partie || '',
          rubrique_tva: ligne.rubrique_tva || '',
          compte_tva: ligne.compte_tva || '',
          type_journal: codeJournal,
          prorat_de_deduction: ligne.prorat_de_deduction || '',
          piece_justificative: ligne.piece_justificative || '',
          libelle: ligne.libelle || '',
          filtre_selectionne: selectedFilter,
          value: typeof ligne.solde_cumule !== "undefined" ? ligne.solde_cumule : ""
        }));

      console.log("📤 Données envoyées :", lignesAEnvoyer);

      if (lignesAEnvoyer.length === 0) {
        alert("⚠️ Aucune ligne valide à enregistrer.");
        return;
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      const response = await fetch('/lignes', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ lignes: lignesAEnvoyer })
      });

      if (!response.ok) {
        console.error("❌ Erreur serveur :", response.status, response.statusText);
        alert(`Erreur lors de l'enregistrement : ${response.statusText}`);
        return;
      }

      const result = await response.json();
      console.log("📥 Réponse du serveur :", result);

      // Vérification et tri de la réponse selon le champ "ordre"
      if (Array.isArray(result)) {
        result.sort((a, b) => a.ordre - b.ordre);
        tableAch.setData(result);
        console.log("✅ Tableau mis à jour avec les nouvelles données dans l'ordre.");
      } else if (result && Array.isArray(result.data)) {
        result.data.sort((a, b) => a.ordre - b.ordre);
        tableAch.setData(result.data);
        console.log("✅ Tableau mis à jour avec les nouvelles données dans l'ordre.");
      } else {
        console.warn("⚠️ Format inattendu de la réponse :", result);
        alert("Aucune donnée valide reçue du serveur.");
        return;
      }

      // Recalculer le solde cumulé après la mise à jour des données
      calculerSoldeCumule();

      // Vérifier si la dernière ligne est vide avant d'ajouter une nouvelle ligne vide
      const dataActuelle = tableAch.getData();
      const derniereLigne = dataActuelle[dataActuelle.length - 1];

      if (!derniereLigne || derniereLigne.compte !== '') {
        // Ajout d'une ligne vide avec un ordre correspondant à la fin du tableau
        tableAch.addRow({
          id: null,
          ordre: dataActuelle.length,
          compte: '',
          contre_partie: '',
          compte_tva: '',
          debit: 0,
          credit: 0,
          piece_justificative: '',
          libelle: '',
          rubrique_tva: '',
          type_journal: codeJournal,
          value: ""
        });
      } else {
        console.log("Ligne vide déjà présente, pas besoin d'en ajouter une autre.");
      }

    } catch (error) {
      console.error("🚨 Erreur lors de l'enregistrement :", error);
      alert("❌ Une erreur s'est produite. Vérifiez la console pour plus de détails.");
    }
  }

  /**********************
   * Fonction d'écoute sur l'événement "Enter" du tableau
   **********************/
  async function ecouterEntrer(table) {
    table.element.addEventListener("keydown", async function (event) {
      if (event.key === "Enter") {
        event.preventDefault();

        const selectedRows = table.getSelectedRows();
        if (selectedRows.length === 0) {
          console.error("Aucune ligne active trouvée");
          return;
        }

        const ligneActive = selectedRows[0].getData();
        // Vous pouvez définir ici votre logique d'ajout de nouvelle ligne
        // Par exemple, copier la ligne active ou créer une nouvelle ligne par défaut
        const nouvelleLigne = {
          id: null,
          ordre: table.getData().length, // ordre à la fin
          compte: '',
          contre_partie: '',
          compte_tva: '',
          debit: 0,
          credit: 0,
          piece_justificative: '',
          libelle: '',
          rubrique_tva: '',
          type_journal: document.querySelector("#journal-achats").value,
          value: ""
        };

        // Ajouter la nouvelle ligne
        table.addRow(nouvelleLigne);
        console.log("Nouvelle ligne ajoutée :", nouvelleLigne);

        // Nettoyer : supprimer les lignes dont le champ "compte" est non vide (sauf la ligne vide)
        const dataActuelle = table.getData();
        table.setData(dataActuelle.filter(ligne => ligne.compte !== ""));

        // Vérifier à nouveau la dernière ligne pour ajouter une ligne vide si nécessaire
        const updatedData = table.getData();
        const derniereLigne = updatedData[updatedData.length - 1];
        if (!derniereLigne || derniereLigne.compte !== '') {
          table.addRow({
            id: null,
            ordre: updatedData.length,
            compte: '',
            contre_partie: '',
            compte_tva: '',
            debit: 0,
            credit: 0,
            piece_justificative: '',
            libelle: '',
            rubrique_tva: '',
            type_journal: document.querySelector("#journal-achats").value,
            value: ""
          });
        } else {
          console.log("Ligne vide déjà présente, pas besoin d'en ajouter une autre.");
        }

        // Enregistrer les lignes après l'ajout
        await enregistrerLignesAch();
      }
    });
  }



// Fonction pour calculer le solde cumulé et appliquer la vérification
function calculerSoldeCumule() {
    const rows = tableAch.getRows();
    const groupSums = {};
    const factures = {};

    rows.forEach((row, index) => {
        const data = row.getData();
        const key = `${data.numero_facture}`;

        // Initialisation du solde si non défini
        if (typeof groupSums[key] === "undefined") {
            groupSums[key] = 0;
        }

        // Calcul du solde cumulé
        const debit = parseFloat(data.debit) || 0;
        const credit = parseFloat(data.credit) || 0;
        const nouveauSolde = groupSums[key] + debit - credit;

        // Mise à jour du solde cumulé pour cette ligne
        data.value = nouveauSolde;
        groupSums[key] = nouveauSolde;

        // Mise à jour de la ligne dans Tabulator
        row.update({ value: nouveauSolde });

        // Vérification si c'est la dernière ligne pour cette facture
        if (!factures[data.numero_facture]) {
            factures[data.numero_facture] = { lastRow: row, lastSolde: nouveauSolde };
        } else {
            factures[data.numero_facture].lastRow = row;
            factures[data.numero_facture].lastSolde = nouveauSolde;
        }
    });

    // Vérification du solde cumulé pour la dernière ligne de chaque facture
    for (const numero_facture in factures) {
        const { lastRow, lastSolde } = factures[numero_facture];

        if (lastSolde !== 0) {
            // Appliquer la surbrillance clignotante si le solde cumulé n'est pas zéro
            lastRow.getElement().classList.add("highlight-error");
        }
    }

    // Redessiner le tableau après la mise à jour
    tableAch.redraw();
}

// Appeler la fonction de calcul après le chargement des données
tableAch.on("dataLoaded", function() {
    calculerSoldeCumule();
});



// Fonction de mise à jour des données du tableau en fonction des filtres
function updateTabulatorData() {
    const mois = document.getElementById("periode-achats").value;
    const annee = document.getElementById("annee-achats").value;
    const codeJournal = document.getElementById("journal-achats").value;

    let dataToSend = {};

    // Définir les filtres en fonction des valeurs renseignées
    if (codeJournal && (!mois || !annee || mois === 'selectionner un mois')) {
        dataToSend = { code_journal: codeJournal };
    } else if (mois && annee && !codeJournal) {
        dataToSend = { mois: mois, annee: annee };
    } else if (mois && annee && codeJournal) {
        dataToSend = { mois: mois, annee: annee, code_journal: codeJournal };
    }

    console.log("Filtrage appliqué :", dataToSend);

    fetch("/get-operations", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(dataToSend),
    })
        .then(response => response.json())
        .then(data => {
            console.log("Données reçues après filtrage :", data);
            // Remplacer les données du tableau
            tableAch.replaceData(data).then(() => {
                // Après remplacement, recalculer immédiatement le solde cumulé
                calculerSoldeCumule();
            });
        })
        .catch(error => {
            console.error("Erreur lors de la mise à jour :", error);
        });
}

// Ajouter des écouteurs d'événement pour appliquer automatiquement les filtres
document.getElementById("journal-achats").addEventListener("change", updateTabulatorData);
document.getElementById("periode-achats").addEventListener("change", updateTabulatorData);
document.getElementById("annee-achats").addEventListener("input", updateTabulatorData);

// Chargement initial des données (avec éventuellement une ligne vide selon votre logique)
updateTabulatorData();


// Vous pouvez aussi appeler la fonction dès le chargement complet de la page, si besoin
document.addEventListener("DOMContentLoaded", function() {
    calculerSoldeCumule();
});
// Initialiser l'écouteur d'événements pour chaque table
ecouterEntrer(tableVentes);
ecouterEntrer(tableAch);
ecouterEntrer(tableBanque);
ecouterEntrer(tableCaisse);
ecouterEntrer(tableOP);
tabulatorManager.applyToTabulator(tableAch);
tabulatorManager.applyToTabulator(tableVentes);



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
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let selectedRows = tableAch.getSelectedRows(); // Récupérer les lignes sélectionnées dans Tabulator

    if (selectedRows.length > 0) {
        // Tableau des identifiants des lignes sélectionnées
        let rowIds = selectedRows.map(row => row.getData().id); // Supposons que chaque ligne a un identifiant unique 'id'

        // Supprimer les lignes de l'interface utilisateur
        selectedRows.forEach(function (row) {
            row.delete(); // Supprimer chaque ligne sélectionnée du tableau Tabulator
        });

        // Envoyer une requête pour supprimer les lignes dans la base de données
        fetch('/delete-rows', { // Assurez-vous que cette route existe sur votre serveur
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken // Ajoutez le token CSRF ici
            },
            body: JSON.stringify({ rowIds: rowIds }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Les lignes sélectionnées ont été supprimées.");
            } else {
                alert("Erreur lors de la suppression des lignes.");
            }
        })
        .catch(error => {
            console.error("Erreur lors de la suppression des lignes :", error);
            alert("Erreur lors de la suppression des lignes.");
        });
    } else {
        alert("Veuillez sélectionner une ou plusieurs lignes à supprimer.");
    }
});

   // Gestionnaire pour supprimer une ligne sélectionnée
   document.getElementById("delete-row-btn").addEventListener("click", function () {
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let selectedRows = tableVentes.getSelectedRows(); // Récupérer les lignes sélectionnées dans Tabulator

    if (selectedRows.length > 0) {
        // Tableau des identifiants des lignes sélectionnées
        let rowIds = selectedRows.map(row => row.getData().id); // Supposons que chaque ligne a un identifiant unique 'id'

        // Supprimer les lignes de l'interface utilisateur
        selectedRows.forEach(function (row) {
            row.delete(); // Supprimer chaque ligne sélectionnée du tableau Tabulator
        });

        // Envoyer une requête pour supprimer les lignes dans la base de données
        fetch('/delete-rows', { // Assurez-vous que cette route existe sur votre serveur
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken // Ajoutez le token CSRF ici
            },
            body: JSON.stringify({ rowIds: rowIds }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Les lignes sélectionnées ont été supprimées.");
            } else {
                alert("Erreur lors de la suppression des lignes.");
            }
        })
        .catch(error => {
            console.error("Erreur lors de la suppression des lignes :", error);
            alert("Erreur lors de la suppression des lignes.");
        });
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
var tableBanque = new Tabulator("#table-Banque", {
    layout: "fitColumns",
    height: "500px",
    // rowHeader:{headerSort:false, resizable: true, frozen:true,width:50,minwidth:40, headerHozAlign:"center", hozAlign:"center", formatter:"rowSelection", titleFormatter:"rowSelection", cellClick:function(e, cell){
    //   cell.getRow().toggleSelect();
    // }},
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
        {
    title: "Mode de paiement",
    field: "Mode_pay",
    headerFilter: "input", // Permet de rechercher dans la colonne
    editor: "list", // Type d'éditeur pour une liste déroulante
    editorParams: {
        values: ["Espèces", "Chèques", "Virement", "Effet", "Prélèvements", "Compensations", "Autres"], // Options dans la liste
        clearable: true, // Permet de réinitialiser à une valeur vide
        verticalNavigation: "editor", // Navigation clavier pour ouvrir l'éditeur
    },
},
        { title: "Compte", field: "compte", headerFilter: "input", editor: "input" },

        { title: "Libellé", field: "libelle", headerFilter: "input", editor: "input" },
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
{ title: "Crédit", field: "credit", headerFilter: "input",  editor: "number", // Permet l'édition en tant que nombre
    bottomCalc: "sum", // Calcul du total dans le bas de la colonne
    formatter: function(cell) {
        // Formater pour afficher 0.00 si la cellule est vide ou nulle
        const value = cell.getValue();
        return value ? parseFloat(value).toFixed(2) : "0.00";
    },

},
        { title: "N° facture lettrée", field: "fact_lettrer", headerFilter: "input", editor: "input" },
        { title: "Taux RAS TVA", field: "taux_ras_tva", headerFilter: "input", editor: "input" },
        { title: "Nature de l'opération", field: "nature_op", headerFilter: "input" , editor: "input" },
        { title: "Date lettrage", field: "date_lettrage", headerFilter: "input", editor: "input" },
        { title: "Contre-Partie", field: "contre_partie", headerFilter: "input" , editor: "input" },
        { title: "Pièce justificative", field:"piece_justificative"
, headerFilter: "input" , editor: "input" },
{
            title: "Sélectionner",
            headerSort: false,
            resizable: true,
            frozen: true,
            width: 50,
            minWidth: 40,
            headerHozAlign: "center",
            hozAlign: "center",
            formatter: "rowSelection",
            titleFormatter: "rowSelection",
            cellClick: function(e, cell){
                cell.getRow().toggleSelect();
            }
        },


    ],

    footerElement: "<table style='width: 30%; margin-top: 10px; border-collapse: collapse;'>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Débit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-debit-Banque'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Crédit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-credit-Banque'></span></td>" +
                    "</tr>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Débiteur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-debit-Banque'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Créditeur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-credit-Banque'></span></td>" +
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
        document.getElementById('cumul-debit-Banque').innerText = formatCurrency(debitTotal);
        document.getElementById('cumul-credit-Banque').innerText = formatCurrency(creditTotal);
        document.getElementById('solde-debit-Banque').innerText = formatCurrency(soldeDebiteur);
        document.getElementById('solde-credit-Banque').innerText = formatCurrency(soldeCrediteur);
    }

});
tabulatorManager.applyToTabulator(tableBanque);


// Configuration du tableau Trésorerie
var tableCaisse = new Tabulator("#table-Caisse", {
    layout: "fitColumns",
    height: "500px",
    // rowHeader:{headerSort:false, resizable: true, frozen:true,width:50,minwidth:40, headerHozAlign:"center", hozAlign:"center", formatter:"rowSelection", titleFormatter:"rowSelection", cellClick:function(e, cell){
    //   cell.getRow().toggleSelect();
    // }},
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
        {
    title: "Mode de paiement",
    field: "Mode_pay",
    headerFilter: "input", // Permet de rechercher dans la colonne
    editor: "list", // Type d'éditeur pour une liste déroulante
    editorParams: {
        values: ["Espèces", "Chèques", "Virement", "Effet", "Prélèvements", "Compensations", "Autres"], // Options dans la liste
        clearable: true, // Permet de réinitialiser à une valeur vide
        verticalNavigation: "editor", // Navigation clavier pour ouvrir l'éditeur
    },
},
        { title: "Compte", field: "compte", headerFilter: "input", editor: "input" },

        { title: "Libellé", field: "libelle", headerFilter: "input", editor: "input" },
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
{ title: "Crédit", field: "credit", headerFilter: "input",  editor: "number", // Permet l'édition en tant que nombre
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
        { title: "N° facture lettrée", field: "fact_lettrer", headerFilter: "input", editor: "input" },
        { title: "Taux RAS TVA", field: "taux_ras_tva", headerFilter: "input", editor: "input" },
        { title: "Nature de l'opération", field: "nature_op", headerFilter: "input" , editor: "input" },
        { title: "Date lettrage", field: "date_lettrage", headerFilter: "input", editor: "input" },
        { title: "Contre-Partie", field: "contre_partie", headerFilter: "input" , editor: "input" },
        { title: "Pièce justificative", field:"piece_justificative"
, headerFilter: "input" , editor: "input" },
{
            title: "Sélectionner",
            headerSort: false,
            resizable: true,
            frozen: true,
            width: 50,
            minWidth: 40,
            headerHozAlign: "center",
            hozAlign: "center",
            formatter: "rowSelection",
            titleFormatter: "rowSelection",
            cellClick: function(e, cell){
                cell.getRow().toggleSelect();
            }
        },


    ],
    footerElement: "<table style='width: 30%; margin-top: 10px; border-collapse: collapse;'>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Débit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-debit-Caisse'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Crédit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-credit-Caisse'></span></td>" +
                    "</tr>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Débiteur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-debit-Caisse'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Créditeur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-credit-Caisse'></span></td>" +
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
        document.getElementById('cumul-debit-Caisse').innerText = formatCurrency(debitTotal);
        document.getElementById('cumul-credit-Caisse').innerText = formatCurrency(creditTotal);
        document.getElementById('solde-debit-Caisse').innerText = formatCurrency(soldeDebiteur);
        document.getElementById('solde-credit-Caisse').innerText = formatCurrency(soldeCrediteur);
    }
});
tabulatorManager.applyToTabulator(tableCaisse);


// Configuration du tableau Opérations Diverses
var tableOP = new Tabulator("#table-operations-diverses", {
    layout: "fitColumns",
    height: "500px",
    // rowHeader:{headerSort:false, resizable: true, frozen:true,width:50,minwidth:40, headerHozAlign:"center", hozAlign:"center", formatter:"rowSelection", titleFormatter:"rowSelection", cellClick:function(e, cell){
    //   cell.getRow().toggleSelect();
    // }},
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

        { title: "Compte", field: "compte" , headerFilter: "input", editor: "input"},
        { title: "Libellé", field: "libelle", editor: "input" , headerFilter: "input",},
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
      { title: "Crédit", field: "credit", headerFilter: "input", editor: "number", bottomCalc: "sum" },
        {
            title: "Sélectionner",
            headerSort: false,
            resizable: true,
            frozen: true,
            width: 50,
            minWidth: 40,
            headerHozAlign: "center",
            hozAlign: "center",
            formatter: "rowSelection",
            titleFormatter: "rowSelection",
            cellClick: function(e, cell){
                cell.getRow().toggleSelect();
            }
        },

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

tabulatorManager.applyToTabulator(tableOP);



            // Gestion des onglets
            $('.tab').on('click', function () {
                const tabId = $(this).data('tab');
                $('.tab').removeClass('active');
                $('.tab-content').removeClass('active');
                $(this).addClass('active');
                $('#' + tabId).addClass('active');
            });




        });
