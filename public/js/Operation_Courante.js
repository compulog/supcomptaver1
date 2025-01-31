
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

// Lors de l'édition ou de l'ajout de ligne
tableAch.on("cellEdited", function (cell) {
    const row = cell.getRow();
    const data = row.getData();

    // Vérifier si c'est une nouvelle ligne (par l'absence de l'ID)
    if (!data.id) {
        // Si c'est une nouvelle ligne, ne pas afficher le message d'erreur de mise à jour
        console.log("Nouvelle ligne ajoutée, pas de validation nécessaire.");
        return; // Ignorer la mise à jour pour les nouvelles lignes
    }

    // // Si c'est une ligne existante (elle a un ID), effectuer la validation
    // if (!data.numero_facture || !data.compte || !data.credit) {
    //     alert("La ligne est invalide. Veuillez remplir tous les champs obligatoires.");
    //     return; // Ne pas envoyer la requête si un champ est manquant
    // }

    // Si la ligne est valide, procéder à la mise à jour
    const field = cell.getField();
    const value = cell.getValue();
    const numeroFacture = data.numero_facture;

    // Préparer l'URL et la méthode (mise à jour)
    const url = `/operations/${data.id}`; // Pour une ligne existante, on met à jour cette ligne avec son ID
    const method = "PUT"; // Utiliser la méthode PUT pour la mise à jour

    // Créer le corps de la requête
    const requestBody = {
        field: field,
        value: value,
        numero_facture: numeroFacture
    };

    // Envoi de la requête PUT uniquement si c'est une ligne existante
    fetch(url, {
        method: method,
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify(requestBody),
    })
    .then((response) => {
        if (!response.ok) {
            if (response.status === 422) {
                throw new Error("Données invalides. Assurez-vous que tous les champs sont remplis correctement.");
            }
            throw new Error(`Erreur HTTP ${response.status}`);
        }
        return response.json();
    })
    .then((result) => {
        console.log("Opération réussie : ", result);
        alert("Mise à jour réussie !"); // Alerte indiquant que la mise à jour a réussi
    })
    .catch((error) => {
        console.error("Erreur de mise à jour :", error);
        alert(`Erreur de mise à jour : ${error.message}`); // Alerte indiquant qu'il y a eu une erreur
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

// Fonction pour ajouter une ligne au tableau
async function ajouterLigne(table, preRemplir = false, ligneActive = null) {
    let nouvellesLignes = []; // S'assurer que c'est un tableau vide
    let idCounter = table.getData().length + 1; // Générer un ID unique pour chaque ligne ajoutée

    let codeJournal = document.querySelector("#journal-achats").value;
    let moisActuel = new Date().getMonth() + 1; // Récupère le mois courant (1-12)

    // Récupérer la valeur du filtre sélectionné
    let filterAchats = document.querySelector('input[name="filter-achats"]:checked')?.value;

    if (!filterAchats) {
        alert("Veuillez sélectionner un filtre.");
        return;
    }

    if (preRemplir && ligneActive) {
        // Vérification que la fonction `ajouterLignePreRemplie` retourne bien un tableau
        nouvellesLignes = await ajouterLignePreRemplie(idCounter, ligneActive, codeJournal, moisActuel, filterAchats);
        console.log("Lignes pré-remplies générées:", nouvellesLignes); // Debug
    } else {
        let ligneVide = ajouterLigneVide(idCounter, ligneActive, codeJournal, moisActuel);
        nouvellesLignes.push(ligneVide);
    }

    // S'assurer que `nouvellesLignes` est bien un tableau avant d'utiliser `map`
    if (Array.isArray(nouvellesLignes)) {
        nouvellesLignes.forEach(ligne => {
            table.addRow(ligne, false);
        });
    } else {
        console.error("Erreur: nouvellesLignes n'est pas un tableau.");
    }

    console.log("Toutes les lignes du tableau après ajout:", table.getData()); // Vérifie que les lignes sont bien ajoutées

    // Supprimer les doublons dans les lignes ajoutées
    const lignesSansDoublons = supprimerDoublonsLignes(table.getData());

    console.log("Lignes après suppression des doublons:", lignesSansDoublons);

    return nouvellesLignes;
}

// Fonction pour ajouter une ligne pré-remplie
async function ajouterLignePreRemplie(idCounter, ligneActive, codeJournal, moisActuel, filterAchats) {
    let lignes = [];
    let ligne1 = { ...ligneActive, id: idCounter++ };
    let ligne2 = { ...ligneActive, id: idCounter++ };

    console.log("Ajout des lignes pré-remplies avec filterAchats:", filterAchats); // Debug

    const creditPremierLigne = parseFloat(ligneActive.credit) || 0; // Récupérer le crédit de la ligne active
    console.log("Crédit de la première ligne:", creditPremierLigne); // Debug

    if (filterAchats === 'contre-partie') {
        ligne1.compte = ligneActive.contre_partie || '';
        ligne1.contre_partie = ligneActive.compte || '';
        ligne1.debit = 0;  // Va être calculé
        ligne1.credit = 0;  // FORCE LE CRÉDIT À 0
        ligne1.piece = ligneActive.piece;
        ligne1.type_journal = codeJournal || '';
        lignes.push(ligne1);

        ligne2.compte = ligneActive.compte_tva || '';
        ligne2.contre_partie = ligne1.compte || '';
        ligne2.debit = 0;  // Va être calculé
        ligne2.credit = 0;  // FORCE LE CRÉDIT À 0
        ligne2.piece = incrementPiece(ligneActive.piece, codeJournal, moisActuel);
        ligne2.type_journal = codeJournal || '';
        lignes.push(ligne2);
    }

    console.log("Lignes pré-remplies générées:", lignes); // Debug

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

// Fonction pour incrémenter le numéro de la pièce justificative
function incrementPiece(piece, codeJournal, mois) {
    if (!piece) return generatePiece(codeJournal, mois, 1);

    let match = piece.match(/^P(\d{2})([A-Za-z]+)(\d{4})$/);
    if (match) {
        let moisPiece = match[1];
        let journalPiece = match[2];
        let numero = parseInt(match[3], 10) + 1;

        if (moisPiece !== mois.toString().padStart(2, '0') || journalPiece !== codeJournal) {
            numero = 1;
        }

        return generatePiece(codeJournal, mois, numero);
    }

    return piece;
}

// Fonction pour générer une pièce justificative
function generatePiece(codeJournal, mois, numeroIncrement) {
    const moisFormat = mois.toString().padStart(2, '0');
    const numeroFormat = numeroIncrement.toString().padStart(4, '0');
    return `P${moisFormat}${codeJournal}${numeroFormat}`;
}

async function enregistrerLignesAch() {
    const lignes = tableAch.getData();
    const lignesSansDoublons = supprimerDoublonsLignes(lignes);
    console.log("Lignes sans doublons avant l'enregistrement:", lignesSansDoublons);

    const codeJournal = document.querySelector("#journal-achats").value;
    if (!codeJournal) {
        alert("Veuillez sélectionner un code journal.");
        return;
    }

    const lignesValides = lignesSansDoublons.filter(ligne => ligne.date && ligne.numero_facture && ligne.compte);
    if (lignesValides.length === 0) {
        console.warn("Aucune ligne valide à enregistrer.");
        return;
    }

    try {
        const formData = lignesValides.map(ligne => ({
            id: ligne.id,
            date: ligne.date,
            numero_facture: ligne.numero_facture,
            compte: ligne.compte,
            debit: parseFloat(ligne.debit) || 0,
            credit: parseFloat(ligne.credit) || 0,
            contre_partie: ligne.contre_partie || '',
            rubrique_tva: ligne.rubrique_tva || '',
            compte_tva: ligne.compte_tva || '',
            type_journal: codeJournal,
            prorat_de_deduction: ligne.prorat_de_deduction || '',
            piece_justificative: ligne.piece_justificative || '',
            libelle: ligne.libelle || ''
        }));

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch('/lignes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ lignes: formData })
        });

        if (!response.ok) {
            console.error("Erreur lors de l'enregistrement des lignes. Status:", response.status);
            return;
        }

        console.log("Lignes enregistrées avec succès !");

        // 🔥 Ajouter une seule ligne vide pour la saisie continue
        const nouvelleLigneVide = {
            id: tableAch.getData().length + 1,
            compte: '',
            contre_partie: '',
            compte_tva: '',
            debit: 0,
            credit: 0,
            piece: generatePiece(document.querySelector("#journal-achats").value, new Date().getMonth() + 1, 1),
            type_journal: document.querySelector("#journal-achats").value
        };

        tableAch.addRow(nouvelleLigneVide);

    } catch (error) {
        console.error("Erreur lors de l'enregistrement des lignes :", error);
        alert("Une erreur est survenue lors de l'enregistrement.");
    }
}



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
            let nouvellesLignes = await ajouterLigne(table, true, ligneActive);

            if (!Array.isArray(nouvellesLignes)) {
                nouvellesLignes = [nouvellesLignes];
            }

            console.log("Lignes ajoutées :", nouvellesLignes);

            // Vérifier si la dernière ligne est déjà vide
            const dataActuelle = table.getData();
            const derniereLigne = dataActuelle[dataActuelle.length - 1];

            // Supprimer toutes les nouvelles lignes ajoutées sauf une vide
            table.setData(dataActuelle.filter(ligne => ligne.compte !== ""));

            // Ajouter une seule ligne vide si nécessaire
            if (!derniereLigne || derniereLigne.compte !== '') {
                const nouvelleLigneVide = {
                    id: dataActuelle.length + 1,
                    compte: '',
                    contre_partie: '',
                    compte_tva: '',
                    debit: 0,
                    credit: 0,
                    piece: generatePiece(document.querySelector("#journal-achats").value, new Date().getMonth() + 1, 1),
                    type_journal: document.querySelector("#journal-achats").value
                };
                table.addRow(nouvelleLigneVide);
            } else {
                console.log("Ligne vide déjà présente, pas besoin d'en ajouter une autre.");
            }

            // 🔥 Enregistrer les lignes après l'ajout
            await enregistrerLignesAch();
        }
    });
}


// Fonction de mise à jour des données avec des filtres
function updateTabulatorData() {
    const mois = document.getElementById("periode-achats").value;
    const annee = document.getElementById("annee-achats").value;
    const codeJournal = document.getElementById("journal-achats").value;

    let dataToSend = {};

    // Vérifier quel filtre est utilisé
    if (codeJournal && (!mois || !annee || mois === 'selectionner un mois')) {
        // Filtrage uniquement par code_journal
        dataToSend = { code_journal: codeJournal };
    } else if (mois && annee && !codeJournal) {
        // Filtrage par mois et année
        dataToSend = { mois: mois, annee: annee };
    } else if (mois && annee && codeJournal) {
        // Filtrage combiné
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
        .then((response) => response.json())
        .then((data) => {
            console.log("Données reçues après filtrage :", data);
            tableAch.replaceData(data); // Met à jour les données du tableau
        })
        .catch((error) => {
            console.error("Erreur lors de la mise à jour :", error);
        });
}

// Event listener pour appliquer les filtres
document.getElementById("journal-achats").addEventListener("change", updateTabulatorData);
document.getElementById("periode-achats").addEventListener("change", updateTabulatorData);
document.getElementById("annee-achats").addEventListener("input", updateTabulatorData);

// Chargement initial avec une ligne vide
updateTabulatorData();


// Initialiser l'écouteur d'événements pour chaque table
ecouterEntrer(tableVentes);
ecouterEntrer(tableAch);
ecouterEntrer(tableBanque);
ecouterEntrer(tableCaisse);
ecouterEntrer(tableOP);


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




            // Gestion des onglets
            $('.tab').on('click', function () {
                const tabId = $(this).data('tab');
                $('.tab').removeClass('active');
                $('.tab-content').removeClass('active');
                $(this).addClass('active');
                $('#' + tabId).addClass('active');
            });




        });
