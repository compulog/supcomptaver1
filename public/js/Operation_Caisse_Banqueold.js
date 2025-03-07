(function () {
    "use strict";


    if (typeof tabulatorManager === "undefined") {
        console.error("tabulatorManager n'est pas défini ! Vérifiez l'ordre des fichiers JS.");
    } else {
        console.log("tabulatorManager est bien défini !");
    }
    // =======================================================
    // INITIALISATION & GESTION DES SECTIONS ET PÉRIODE
    // =======================================================
    document.addEventListener("DOMContentLoaded", function () {
        // Liste des sections à gérer
        const sections = ["Caisse", "Banque"];

        // Initialisation des filtres (mois/exercice) pour chaque section
        function initializeSection(section) {
            const radioMois = document.getElementById(`filter-mois-${section}`);
            const radioExercice = document.getElementById(`filter-exercice-${section}`);
            const periodeContainer = document.getElementById(`periode-${section}`);
            const anneeInput = document.getElementById(`annee-${section}`);

            if (!radioMois || !radioExercice || !periodeContainer || !anneeInput) {
                console.warn(`Certains éléments de la section "${section}" sont manquants.`);
                return;
            }

            function updateDisplay() {
                if (radioMois.checked) {
                    periodeContainer.style.display = "inline-block";
                    anneeInput.style.display = "none";
                } else if (radioExercice.checked) {
                    periodeContainer.style.display = "none";
                    anneeInput.style.display = "inline-block";
                }
            }

            radioMois.addEventListener("change", updateDisplay);
            radioExercice.addEventListener("change", updateDisplay);
            updateDisplay();
        }
        sections.forEach(initializeSection);

        // Liste des mois pour le peuplement des périodes
        const moisAnglais = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        const moisFrancais = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

        // Fonction pour peupler les périodes dans le select de chaque onglet
        function populateMonths(onglet, periodes) {
            const periodeSelect = $(`#periode-${onglet}`);
            const previousSelection = periodeSelect.data('selected');
            periodeSelect.empty();
            periodeSelect.append('<option value="selectionner un mois">Sélectionner un mois</option>');

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
            } else {
                periodeSelect.val('selectionner un mois');
            }
        }

        // Fonction pour charger l'exercice social et les périodes
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
                    $('#annee-Caisse').val(anneeDebut);
                    $('#annee-Banque').val(anneeDebut);

                    populateMonths('Banque', periodesData);
                    populateMonths('Caisse', periodesData);
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    console.error('Erreur lors du chargement des données :', textStatus, errorThrown);
                });
        }

        // Gestion des changements dans le select de période pour chaque onglet
        function setupPeriodChangeHandler(onglet) {
            $(`#periode-${onglet}`).on('change', function () {
                const selectedValue = $(this).val();
                const selectedText = $(this).find("option:selected").text();
                console.log('Valeur sélectionnée pour ' + onglet + ':', selectedValue);
                console.log('Texte sélectionné pour ' + onglet + ':', selectedText);
                $(this).data('selected', selectedValue);

                if (selectedValue && selectedValue !== "selectionner un mois") {
                    const parts = selectedValue.split('-');
                    if (parts.length === 2) {
                        const selectedMonth = parts[0];
                        const selectedYear = parts[1];
                        $(`#annee-${onglet}`).val(selectedYear);
                        updateTabulatorDate(selectedYear, selectedMonth);
                    }
                }
            });
        }

        // Mise à jour de la date dans tous les tableaux Tabulator
        function updateTabulatorDate(year, month) {
            const formattedDate = `${year}-${month.padStart(2, '0')}-01`;
            [tableBanque, tableCaisse].forEach(function (table) {
                if (table) {
                    table.updateData(
                        table.getData().map(row => ({
                            ...row,
                            date: formattedDate,
                        }))
                    );
                }
            });
        }

        // Gestion des changements des boutons radio pour filtrer entre mois et exercice
        function setupFilterEventHandlers() {
            const onglets = ['Banque', 'Caisse'];
            onglets.forEach((onglet) => {
                $(`input[name="filter-period-${onglet}"]`).on('change', function () {
                    if ($(this).val() === 'mois') {
                        $(`#periode-${onglet}`).show();
                        $(`#annee-${onglet}`).hide();
                    } else if ($(this).val() === 'exercice') {
                        $(`#periode-${onglet}`).hide();
                        $(`#annee-${onglet}`).show();
                    }
                });
            });
        }

        // Charger les données et initialiser les filtres
        loadExerciceSocialAndPeriodes();
        setupFilterEventHandlers();
        sections.forEach(onglet => setupPeriodChangeHandler(onglet));

        // Gestionnaire pour charger les journaux
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
                            `<option value="${journal.code_journal}" data-type="${journal.type_journal}" data-intitule="${journal.intitule}">
                                ${journal.code_journal}
                            </option>`
                        );
                    });
                },
                error: function (err) {
                    console.error('Erreur lors du chargement des journaux', err);
                },
            });
        }
        loadJournaux('Banque', '#journal-Banque');
        loadJournaux('Caisse', '#journal-Caisse');

        $('select').on('change', function () {
            const selectedOption = $(this).find(':selected');
            const intituleJournal = selectedOption.data('intitule');
            const tabId = $(this).attr('id').replace('journal-', 'filter-intitule-');
            $('#' + tabId).val(intituleJournal ? 'journal - ' + intituleJournal : '');
        });


        // =======================================================
        // CONFIGURATION DES TABLEAUX TABULATOR
        // =======================================================

        // Pour Banque
        var tableBanque = new Tabulator("#table-Banque", {
            layout: "fitColumns",
            height: "500px",
            selectable: true,
            data: [{}],
            columns: [
                { title: "ID", field: "id", visible: false },
                {
                    title: "Date",
                    field: "date",
                    hozAlign: "center",
                    headerFilter: "input",
                    sorter: "date",
                    editor: function (cell, onRendered, success) {
                        const input = document.createElement("input");
                        input.type = "text";
                        const currentValue = cell.getValue();
                        input.value = currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '';
                        input.placeholder = "jj/mm/aaaa";
                        flatpickr(input, {
                            dateFormat: "d/m/Y",
                            defaultDate: currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '',
                            onChange: function (selectedDates) {
                                success(luxon.DateTime.fromJSDate(selectedDates[0]).toISODate());
                            },
                            allowInput: true,
                        });
                        onRendered(() => input.focus());
                        return input;
                    },
                    formatter: function (cell) {
                        const dateValue = cell.getValue();
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
                    headerFilter: "input",
                    editor: "list",
                    editorParams: {
                        values: ["Espèces", "Chèques", "Virement", "Effet", "Prélèvements", "Compensations", "Autres"],
                        clearable: true,
                        verticalNavigation: "editor",
                    },
                },
                { title: "Compte", field: "compte", headerFilter: "input", editor: "input" },
                { title: "Libellé", field: "libelle", headerFilter: "input", editor: "input" },
                {
                    title: "Débit",
                    field: "debit",
                    headerFilter: "input",
                    editor: "number",
                    bottomCalc: "sum",
                    formatter: function (cell) {
                        const value = cell.getValue();
                        return value ? parseFloat(value).toFixed(2) : "0.00";
                    },
                    mutatorEdit: function (value) {
                        return value || "0.00";
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
                    }
                },
                { title: "N° facture lettrée", field: "fact_lettrer", headerFilter: "input", editor: "input" },
                { title: "Taux RAS TVA", field: "taux_ras_tva", headerFilter: "input", editor: "input" },
                { title: "Nature de l'opération", field: "nature_op", headerFilter: "input", editor: "input" },
                { title: "Date lettrage", field: "date_lettrage", headerFilter: "input", editor: "input" },
                { title: "Contre-Partie", field: "contre_partie", headerFilter: "input", editor: "input" },
                { title: "Pièce justificative", field: "piece_justificative", headerFilter: "input", editor: "input" },
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
                    cellClick: function (e, cell) {
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
                "</table>",
            rowFormatter: function (row) {
                let debitTotal = 0;
                let creditTotal = 0;
                row.getTable().getRows().forEach(function (r) {
                    const data = r.getData();
                    debitTotal += parseFloat(data.debit || 0);
                    creditTotal += parseFloat(data.credit || 0);
                });
                let soldeDebiteur = debitTotal - creditTotal;
                let soldeCrediteur = creditTotal - debitTotal;
                document.getElementById('cumul-debit-Banque').innerText = debitTotal.toFixed(2);
                document.getElementById('cumul-credit-Banque').innerText = creditTotal.toFixed(2);
                document.getElementById('solde-debit-Banque').innerText = soldeDebiteur.toFixed(2);
                document.getElementById('solde-credit-Banque').innerText = soldeCrediteur.toFixed(2);
            }
        });

        // Pour Caisse
       var tableCaisse = new Tabulator("#table-Caisse", {
            layout: "fitColumns",
            height: "500px",
            selectable: true,
            data: [{}],
            columns: [
                { title: "ID", field: "id", visible: false },
                {
                    title: "Date",
                    field: "date",
                    hozAlign: "center",
                    headerFilter: "input",
                    sorter: "date",
                    editor: function (cell, onRendered, success) {
                        const input = document.createElement("input");
                        input.type = "text";
                        const currentValue = cell.getValue();
                        input.value = currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '';
                        input.placeholder = "jj/mm/aaaa";
                        flatpickr(input, {
                            dateFormat: "d/m/Y",
                            defaultDate: currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '',
                            onChange: function (selectedDates) {
                                success(luxon.DateTime.fromJSDate(selectedDates[0]).toISODate());
                            },
                            allowInput: true,
                        });
                        onRendered(() => input.focus());
                        return input;
                    },
                    formatter: function (cell) {
                        const dateValue = cell.getValue();
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
                    headerFilter: "input",
                    editor: "list",
                    editorParams: {
                        values: ["Espèces", "Chèques", "Virement", "Effet", "Prélèvements", "Compensations", "Autres"],
                        clearable: true,
                        verticalNavigation: "editor",
                    },
                },
                { title: "Compte", field: "compte", headerFilter: "input", editor: "input" },
                { title: "Libellé", field: "libelle", headerFilter: "input", editor: "input" },
                {
                    title: "Débit",
                    field: "debit",
                    headerFilter: "input",
                    editor: "number",
                    bottomCalc: "sum",
                    formatter: function (cell) {
                        const value = cell.getValue();
                        return value ? parseFloat(value).toFixed(2) : "0.00";
                    },
                    mutatorEdit: function (value) {
                        return value || "0.00";
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
                    }
                },
                { title: "N° facture lettrée", field: "fact_lettrer", headerFilter: "input", editor: "input" },
                { title: "Taux RAS TVA", field: "taux_ras_tva", headerFilter: "input", editor: "input" },
                { title: "Nature de l'opération", field: "nature_op", headerFilter: "input", editor: "input" },
                { title: "Date lettrage", field: "date_lettrage", headerFilter: "input", editor: "input" },
                { title: "Contre-Partie", field: "contre_partie", headerFilter: "input", editor: "input" },
                { title: "Pièce justificative", field: "piece_justificative", headerFilter: "input", editor: "input" },
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
                    cellClick: function (e, cell) {
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
                "</table>",
            rowFormatter: function (row) {
                let debitTotal = 0;
                let creditTotal = 0;
                row.getTable().getRows().forEach(function (r) {
                    const data = r.getData();
                    debitTotal += parseFloat(data.debit || 0);
                    creditTotal += parseFloat(data.credit || 0);
                });
                let soldeDebiteur = debitTotal - creditTotal;
                let soldeCrediteur = creditTotal - debitTotal;
                document.getElementById('cumul-debit-Caisse').innerText = debitTotal.toFixed(2);
                document.getElementById('cumul-credit-Caisse').innerText = creditTotal.toFixed(2);
                document.getElementById('solde-debit-Caisse').innerText = soldeDebiteur.toFixed(2);
                document.getElementById('solde-credit-Caisse').innerText = soldeCrediteur.toFixed(2);
            }
        });


        // Appliquer les contrôles d'édition avec TabulatorManager (assurez-vous que cet objet est défini)
        tabulatorManager.applyToTabulator(tableBanque);
        tabulatorManager.applyToTabulator(tableCaisse);

        // =======================================================
        // GESTION DES ACTIONS (IMPORT / EXPORT / SUPPRESSION / IMPRESSION)
        // =======================================================


        // --------------------
        // Actions pour la table Caisse
        // --------------------
        document.getElementById("import-Caisse").addEventListener("click", function () {
            alert("Fonction d'import non implémentée pour Caisse !");
        });
        document.getElementById("export-CaisseExcel").addEventListener("click", function () {
            tableCaisse.download("xlsx", "caisse.xlsx", { sheetName: "Caisse" });
        });
        document.getElementById("export-CaissePDF").addEventListener("click", function () {
            tableCaisse.download("pdf", "caisse.pdf", {
                orientation: "portrait",
                title: "Rapport de la Caisse"
            });
        });
        document.getElementById("delete-row-btn-caisse").addEventListener("click", function () {
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let selectedRows = tableCaisse.getSelectedRows();
            if (selectedRows.length > 0) {
                let rowIds = selectedRows.map(row => row.getData().id);
                selectedRows.forEach(function (row) {
                    row.delete();
                });
                fetch('/delete-rows', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ rowIds: rowIds }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Les lignes sélectionnées ont été supprimées pour Caisse.");
                        } else {
                            alert("Erreur lors de la suppression des lignes pour Caisse.");
                        }
                    })
                    .catch(error => {
                        console.error("Erreur lors de la suppression des lignes pour Caisse :", error);
                        alert("Erreur lors de la suppression des lignes pour Caisse.");
                    });
            } else {
                alert("Veuillez sélectionner une ou plusieurs lignes à supprimer pour Caisse.");
            }
        });
        document.getElementById("print-tableC").addEventListener("click", function () {
            if (tableCaisse) {
                tableCaisse.print(false, true);
            } else {
                console.error("La table Tabulator Caisse n'est pas initialisée.");
            }
        });

        // --------------------
        // Actions pour la table Banque
        // --------------------
        document.getElementById("import-Banque").addEventListener("click", function () {
            alert("Fonction d'import non implémentée pour Banque !");
        });
        document.getElementById("export-BanqueExcel").addEventListener("click", function () {
            tableBanque.download("xlsx", "banque.xlsx", { sheetName: "Banque" });
        });
        document.getElementById("export-BanquePDF").addEventListener("click", function () {
            tableBanque.download("pdf", "banque.pdf", {
                orientation: "portrait",
                title: "Rapport de la Banque"
            });
        });
        document.getElementById("delete-row-btn-banque").addEventListener("click", function () {
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let selectedRows = tableBanque.getSelectedRows();
            if (selectedRows.length > 0) {
                let rowIds = selectedRows.map(row => row.getData().id);
                selectedRows.forEach(function (row) {
                    row.delete();
                });
                fetch('/delete-rows', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ rowIds: rowIds }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Les lignes sélectionnées ont été supprimées pour Banque.");
                        } else {
                            alert("Erreur lors de la suppression des lignes pour Banque.");
                        }
                    })
                    .catch(error => {
                        console.error("Erreur lors de la suppression des lignes pour Banque :", error);
                        alert("Erreur lors de la suppression des lignes pour Banque.");
                    });
            } else {
                alert("Veuillez sélectionner une ou plusieurs lignes à supprimer pour Banque.");
            }
        });
        document.getElementById("print-tableB").addEventListener("click", function () {
            if (tableBanque) {
                tableBanque.print(false, true);
            } else {
                console.error("La table Tabulator Banque n'est pas initialisée.");
            }
        });



                // =======================================================
                // GESTION DES ONGLETS
                // =======================================================
                $('.tab').on('click', function () {
                    const tabId = $(this).data('tab');
                    $('.tab').removeClass('active');
                    $('.tab-content').removeClass('active');
                    $(this).addClass('active');
                    $('#' + tabId).addClass('active');
                });
            });
        })();

