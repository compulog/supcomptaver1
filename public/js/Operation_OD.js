(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {

        // =======================================================
        // Définition de tabulatorManager (optionnel)
        // =======================================================
        var tabulatorManager = {
            applyToTabulator: function(table) {
                table.on("cellEditing", function(cell) {
                    // Exemple : empêcher l'édition si la cellule n'a pas de valeur
                    // if (!cell.getValue()) { cell.cancelEdit(); }
                });
            }
        };

        // =======================================================
        // CONFIGURATION DU TABLEAU TABULATOR - Opérations Diverses (tableOP)
        // =======================================================
        var tableOP = new Tabulator("#table-operations-diverses", {
            layout: "fitColumns",
            height: "500px",
            selectable: true,
            rowHeight: 30, // Hauteur de ligne définie à 30px
            // data: Array(1).fill({}), // Décommentez si vous souhaitez initialiser avec une ligne vide
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
                        input.type = "text"; // Utilisation d'un champ de texte pour Flatpickr
                        const currentValue = cell.getValue();
                        input.value = currentValue
                            ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy')
                            : '';
                        input.placeholder = "jj/mm/aaaa";
                        flatpickr(input, {
                            dateFormat: "d/m/Y",
                            defaultDate: currentValue
                                ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy')
                                : '',
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
                    }
                },
                { title: "N°Facture", field: "numero_facture", headerFilter: "input", editor: "input" },
                { title: "Compte", field: "compte", headerFilter: "input", editor: "input" },
                { title: "Libellé", field: "libelle", headerFilter: "input", editor: "input" },
                {
                    title: "Débit",
                    field: "debit",
                    headerFilter: "input",
                    editor: "number",
                    bottomCalc: "sum",
                    formatter: function(cell) {
                        const value = cell.getValue();
                        return value ? parseFloat(value).toFixed(2) : "0.00";
                    },
                    mutatorEdit: function(value) {
                        return value || "0.00";
                    }
                },
                {
                    title: "Crédit",
                    field: "credit",
                    headerFilter: "input",
                    editor: "number",
                    bottomCalc: "sum",
                    formatter: function(cell) {
                        const value = cell.getValue();
                        return value ? parseFloat(value).toFixed(2) : "0.00";
                    },
                    mutatorEdit: function(value) {
                        return value || "0.00";
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
                    cellClick: function(e, cell) {
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
            "</table>",
            rowFormatter: function(row) {
                let debitTotal = 0;
                let creditTotal = 0;
                row.getTable().getRows().forEach(function(r) {
                    // Ici, on suppose que le champ "montant" est utilisé pour le calcul des totaux.
                    // Adaptez cette partie selon votre logique de calcul.
                    debitTotal += parseFloat(r.getData().montant || 0);
                    creditTotal += parseFloat(r.getData().montant || 0);
                });
                let soldeDebiteur = debitTotal - creditTotal;
                let soldeCrediteur = creditTotal - debitTotal;
                document.getElementById('cumul-debit-operations-diverses').innerText = formatCurrency(debitTotal);
                document.getElementById('cumul-credit-operations-diverses').innerText = formatCurrency(creditTotal);
                document.getElementById('solde-debit-operations-diverses').innerText = formatCurrency(soldeDebiteur);
                document.getElementById('solde-credit-operations-diverses').innerText = formatCurrency(soldeCrediteur);
            }
        });

        // Appliquer le gestionnaire d'édition si tabulatorManager est défini
        if (typeof tabulatorManager !== "undefined" && tabulatorManager.applyToTabulator) {
            tabulatorManager.applyToTabulator(tableOP);
        }

        // =======================================================
        // GESTION DES ACTIONS POUR OPÉRATIONS DIVERSES (tableOP)
        // =======================================================
        document.getElementById("import-operations-diverses").addEventListener("click", function () {
            alert("Fonction d'import non implémentée pour Opérations Diverses !");
        });

        document.getElementById("export-operations-diversesExcel").addEventListener("click", function () {
            tableOP.download("xlsx", "operations-diverses.xlsx", { sheetName: "Opérations Diverses" });
        });

        document.getElementById("export-operations-diversesPDF").addEventListener("click", function () {
            tableOP.download("pdf", "operations-diverses.pdf", {
                orientation: "portrait",
                title: "Rapport des Opérations Diverses"
            });
        });

        document.getElementById("delete-row-btn-operations").addEventListener("click", function () {
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let selectedRows = tableOP.getSelectedRows();
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
                        alert("Les lignes sélectionnées ont été supprimées pour Opérations Diverses.");
                    } else {
                        alert("Erreur lors de la suppression des lignes pour Opérations Diverses.");
                    }
                })
                .catch(error => {
                    console.error("Erreur lors de la suppression des lignes pour Opérations Diverses :", error);
                    alert("Erreur lors de la suppression des lignes pour Opérations Diverses.");
                });
            } else {
                alert("Veuillez sélectionner une ou plusieurs lignes à supprimer pour Opérations Diverses.");
            }
        });

        document.getElementById("print-tableOp").addEventListener("click", function () {
            if (tableOP) {
                tableOP.print(false, true);
            } else {
                console.error("La table Tabulator Opérations Diverses n'est pas initialisée.");
            }
        });

        // =======================================================
        // Fonction auxiliaire pour le formatage des devises
        // =======================================================
        function formatCurrency(value) {
            return parseFloat(value).toFixed(2);
        }

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
