console.log(soldesMensuels);
console.log(transactions);
function filterSoldeInitial(month, year, journalCode) {
    // Convertir le mois et l'année en entiers
    var monthInt = parseInt(month);
    var yearInt = parseInt(year);

    // Récupérer le solde du mois sélectionné
    var soldeMensuel = soldesMensuels.find(function(solde) {
        var moisComparaison = parseInt(solde.mois).toString().padStart(2, '0');
        var anneeComparaison = parseInt(solde.annee).toString();
        return moisComparaison === month && anneeComparaison === year && solde.code_journal === journalCode;
    });

    if (monthInt === 1) {
        // Si c'est janvier, utiliser le solde initial du mois de janvier
        if (soldeMensuel) {
            document.getElementById('initial-balance').value = soldeMensuel.solde_initial; // Utiliser le solde initial du mois de janvier

            // Vérifiez si le solde de janvier est clôturé
            if (soldeMensuel.cloturer === 1) {
                document.getElementById('initial-balance').disabled = true; // Désactiver le champ
                Swal.fire({
                    title: 'Alerte',
                    text: 'Le solde de janvier est déjà clôturé.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            } else {
                document.getElementById('initial-balance').disabled = false; // Activer le champ si ce n'est pas clôturé
            }
        } else {
            document.getElementById('initial-balance').value = 0; // Si aucun solde trouvé, mettre à 0
            document.getElementById('initial-balance').disabled = false; // Activer le champ
        }
    } else {
        // Pour les autres mois, récupérer le solde final du mois précédent
        var previousMonth = monthInt - 1;
        var previousYear = yearInt;

        // Si le mois précédent est décembre, ajuster l'année
        if (previousMonth === 0) {
            previousMonth = 12;
            previousYear -= 1;
        }

        // Trouver le solde du mois précédent
        var previousSoldeMensuel = soldesMensuels.find(function(solde) {
            return parseInt(solde.mois) === previousMonth && parseInt(solde.annee) === previousYear && solde.code_journal === journalCode;
        });

        // Si le solde du mois précédent existe, mettre à jour le solde initial
        if (previousSoldeMensuel) {
            document.getElementById('initial-balance').value = previousSoldeMensuel.solde_final; // Utiliser le solde final du mois précédent
        } else {
            document.getElementById('initial-balance').value = 0; // Si aucun solde trouvé, mettre à 0
        }
    }

    // Vérifiez si le solde est clôturé
    if (soldeMensuel) {
        if (soldeMensuel.cloturer === 1) {
            Swal.fire({
                title: 'Alerte',
                text: 'Le solde pour ce mois est déjà clôturé.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            // Désactiver le bouton "Clôturer"
            document.getElementById('cloturer-button').disabled = true;
        } else {
            // Réactiver le bouton "Clôturer" si le mois n'est pas clôturé
            document.getElementById('cloturer-button').disabled = false;
        }
    } else {
        // Réactiver le bouton "Clôturer" si aucun solde n'est trouvé
        document.getElementById('cloturer-button').disabled = false;
    }

    updateShareIconVisibility();
    document.getElementById('initial-balance').readOnly = false;
}
// Écoutez le changement de sélection du mois

document.getElementById('month-select').addEventListener('change', function() {
    var selectedMonth = this.value;
    var selectedYear = document.getElementById('year-select').value;
    var selectedJournalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné
    filterSoldeInitial(selectedMonth, selectedYear, selectedJournalCode); // Appeler la fonction avec le code journal
    if (selectedMonth !== "01") { // Si le mois n'est pas janvier
        document.getElementById('initial-balance').disabled = true; // Désactiver l'input
    } else {
        document.getElementById('initial-balance').disabled = false; // Activer l'input
    }
    updateTotals($('#month-select').val(),  $('#year-select').val());
        saveData();
});

document.getElementById('cloturer-button').addEventListener('click', function() {
    var mois = $('#month-select').val();
    // var annee = $('input[type="text"]').val();
    var annee = $('#year-select').val();

    var journalCode = document.getElementById('journal-select').value;

    // Créer un message de confirmation
    Swal.fire({
        title: 'Êtes-vous sûr de vouloir clôturer cet état de caisse pour la période ' + mois + '/' + annee + ' ?',
        text: "Attention, cette action est irréversible.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'OUI',
        cancelButtonText: 'NON'
    }).then((result) => {
        if (result.isConfirmed) {
            // Si l'utilisateur confirme, procéder à la clôture
            console.log("Clôturer le solde pour :", { mois, annee, journalCode });

            $.ajax({
                url: '/cloturer-solde',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    mois: mois,
                    annee: annee,
                    journal_code: journalCode
                },
                success: function(response) {
                    console.log("Réponse du serveur :", response);
                    alert('Le solde a été clôturé avec succès !');

                    // Mettez à jour l'interface utilisateur
                    document.getElementById('initial-balance').disabled = true; // Désactiver le champ de solde initial
                    document.getElementById('cloturer-button').disabled = true; // Désactiver le bouton de clôture

                    // Mettez à jour le tableau pour refléter que le mois est clôturé
                    updateTableData(mois, annee); // Mettre à jour les données du tableau
                    updateTotals(mois, annee); // Mettre à jour les totaux

                    // Mettre à jour la valeur cloturer dans soldesMensuels
                    var soldeMensuel = soldesMensuels.find(function(solde) {
                        return parseInt(solde.mois) === parseInt(mois) &&
                               parseInt(solde.annee) === parseInt(annee) &&
                               solde.code_journal === journalCode;
                    });

                    if (soldeMensuel) {
                        soldeMensuel.cloturer = 1; // Mettre à jour la valeur cloturer
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erreur lors de la clôture :", error);
                    alert('Erreur lors de la clôture du solde.');
                }
            });
        } else {
            // Si l'utilisateur annule, ne rien faire
            console.log("Clôture annulée.");
        }
    });
});

document.getElementById('export-excel-icon').addEventListener('click', exportToExcel);




    function filterTransactions(month, year, journalCode) {
    return transactions.filter(function(transaction) {
        var transactionDate = new Date(transaction.date);
        return transactionDate.getMonth() + 1 === parseInt(month) &&
               transactionDate.getFullYear() === parseInt(year) &&
               transaction.code_journal === journalCode; // Filtrer par code journal
    });
}

function updateTableData(month, year) {
    var journalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné
    var filteredTransactions = filterTransactions(month, year, journalCode); // Passer le code journal
    var tableData = filteredTransactions.map(function(transaction) {
        return {
            id: transaction.id,
            day: new Date(transaction.date).getDate(),
            ref: transaction.reference,
            libelle: transaction.libelle,
            recette: transaction.recette,
            depense: transaction.depense
        };
    });

    // Vérifiez si le mois est clôturé
    if (!isMonthClosed(month, year, journalCode)) {
        // Si le mois n'est pas clôturé, ajoutez une ligne vide
        var emptyRow = {day: "", ref: "", libelle: "", recette: "", depense: ""};
        tableData.push(emptyRow);
    }

    table.replaceData(tableData);
    updateTotals(month, year);

    // Vérifiez si le mois est clôturé
    if (isMonthClosed(month, year, journalCode)) {

        // Désactiver l'édition des cellules
        table.getColumns().forEach(function(column) {
            column.getDefinition().editor = false; // Désactiver l'éditeur
        });
        table.setEditable(false); // Désactiver l'édition du tableau
        alert('Le mois sélectionné est déjà clôturé. Vous ne pouvez pas modifier les données.');
    } else {
        // Réactiver l'édition si le mois n'est pas clôturé
        table.getColumns().forEach(function(column) {
            column.getDefinition().editor = "input"; // Réactiver l'éditeur
        });
        table.setEditable(true); // Réactiver l'édition du tableau
    }
}

document.getElementById('journal-select').addEventListener('change', function() {
    var selectedMonth = document.getElementById('month-select').value;
    var selectedYear = document.getElementById('year-select').value;
    var selectedJournalCode = this.value; // Récupérer le code journal sélectionné
    filterSoldeInitial(selectedMonth, selectedYear, selectedJournalCode); // Appeler la fonction avec le code journal
    updateTableData(selectedMonth, selectedYear); // Mettre à jour le tableau avec le nouveau code journal
    updateTotals($('#month-select').val(),  $('#year-select').val());    saveData();
});
document.addEventListener('DOMContentLoaded', function() {
    var selectedMonth = document.getElementById('month-select').value;
    var selectedYear = document.getElementById('year-select').value;
    var selectedJournalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné
    filterSoldeInitial(selectedMonth, selectedYear, selectedJournalCode); // Appeler la fonction avec le code journal
// Mettre le focus sur le sélecteur de code journal au chargement de la page
updateTableData(selectedMonth, selectedYear);

document.getElementById('journal-select').focus();




});

var table = new Tabulator("#example-table", {
    height: 500,
    layout: "fitColumns",
    columns: [
        {title: "Jour", field: "day", editor: customNumberEditor1 , editorPlaceholder: "jj", width: 65, headerFilter: "input", headerFilterParams: {
            elementAttributes: {
                style: "width: 55px; height: 20px;"
            }
        }
    },
        {title: "N° Référence", field: "ref",  editor: genericTextEditor , editorPlaceholder: "Entrez le N° Référence", width: 200, headerFilter: "input", headerFilterParams: {
            elementAttributes: {
                style: "width: 190px; height: 20px;"
            }
        }},
        {title: "Libellé", field: "libelle",   editor: genericTextEditor , editorPlaceholder: "Entrez le libellé", width: 417, headerFilter: "input", headerFilterParams: {
            elementAttributes: {
                style: "width: 400px; height: 20px;"
            }
        }},
        {title: "Recette", field: "recette", editor: customNumberEditor, editorPlaceholder: "Entrez la recette", width: 200, formatter: "money", bottomCalc: "sum", headerFilter: "input", headerFilterParams: {
            elementAttributes: {
                style: "width: 180px; height: 20px;"
            }
        }},
        {title: "Dépense", field: "depense", editor: customNumberEditor, editorPlaceholder: "Entrez la dépense", width: 200, formatter: "money", bottomCalc: "sum", headerFilter: "input", headerFilterParams: {
            elementAttributes: {
                style: "width: 180px; height: 20px;"
            }
        }},
        {
            title: `
        <input type="checkbox" id="selectAllCheckbox" title="Sélectionner tout" style="cursor: pointer;" onclick="selectAllRows()">
            `,
            field: "actions",
            width: 100,
            formatter: function(cell, formatterParams, onRendered) {
                var rowData = cell.getRow().getData();

                // Créer un conteneur pour les icônes et la case à cocher
                var actionContainer = document.createElement("div");
                actionContainer.style.display = "flex";
                actionContainer.style.alignItems = "center";

                // Créer une case à cocher pour la ligne
                var checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.style.cursor = "pointer";

                // Écouter l'événement de clic sur la case à cocher
                checkbox.addEventListener('change', function() {
                    if (checkbox.checked) {
                        // Sélectionner la ligne si la case est cochée
                        cell.getRow().select();
                    } else {
                        // Désélectionner la ligne si la case est décochée
                        cell.getRow().deselect();
                    }
                });

                // Vérifier l'état initial de la ligne (si elle est déjà sélectionnée)
                checkbox.checked = cell.getRow().isSelected();

                actionContainer.appendChild(checkbox);


                onRendered(function() {
                    cell.getElement().appendChild(actionContainer);
                });

                return ""; // Retourner une chaîne vide car les icônes ont été ajoutées manuellement
            }
        },
    ],
    data: [],
    selectable: true,  // Permet la sélection de lignes
     cellEdited: function(cell) {
        updateTotals($('#month-select').val(),  $('#year-select').val());
                saveData();
    }

});

function focusNextEditableCell(currentCell) {
    const row = currentCell.getRow();
    const cells = row.getCells();
    const currentIndex = cells.findIndex(c => c === currentCell);

    // Chercher dans la même ligne la prochaine cellule éditable
    for (let i = currentIndex + 1; i < cells.length; i++) {
        const colDef = cells[i].getColumn().getDefinition();
        if (colDef.editor) {
            cells[i].edit();
            return;
        }
    }

    // Sinon, passer à la première cellule éditable de la ligne suivante
    const table = currentCell.getTable();
    const rows = table.getRows();
    const currentRowIndex = rows.findIndex(r => r.getIndex() === row.getIndex());
    if (currentRowIndex < rows.length - 1) {
        const nextRow = rows[currentRowIndex + 1];
        for (let cell of nextRow.getCells()) {
            if (cell.getColumn().getDefinition().editor) {
                cell.edit();
                return;
            }
        }
    }
}
function genericTextEditor(cell, onRendered, success, cancel, editorParams) {
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.value = cell.getValue() || "";

    onRendered(() => {
        input.focus();
    });

    input.addEventListener("blur", () => {
        success(input.value);
    });

    input.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            success(input.value);
            setTimeout(() => {
                focusNextEditableCell(cell);
            }, 50);
        }
    });

    return input;
}


/**
 * Ajoute la navigation par la touche Enter à l'élément d'édition.
 * @param {HTMLElement} editorElement - L'élément de l'éditeur (input, textarea, etc.).
 * @param {Object} cell - La cellule Tabulator en cours d'édition.
 * @param {Function} successCallback - La fonction à appeler pour valider la saisie.
 * @param {Function} cancelCallback - (Optionnel) La fonction à appeler en cas d'annulation.
 * @param {Function} getValueCallback - (Optionnel) Fonction pour récupérer la valeur courante de l'éditeur.
 */
function addEnterNavigation(editorElement, cell, successCallback, cancelCallback, getValueCallback) {
    editorElement.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            // Récupérer la valeur courante (pour un input, editorElement.value suffit)
            const value = (getValueCallback && typeof getValueCallback === "function")
                ? getValueCallback(editorElement)
                : editorElement.value;
            // Valider la saisie en appelant le callback success
            successCallback(value);
            // Passer à la cellule éditable suivante
            setTimeout(() => {
                focusNextEditableCell(cell);
            }, 50);
        }
    });
}

function customNumberEditor1(cell, onRendered, success, cancel) {
    const input = document.createElement("input");
    input.type = "number";
    input.style.width = "100%";
    input.placeholder = "jj";

    input.value = cell.getValue() || "";

    onRendered(function() {
        input.focus();
        input.style.height = "100%";
    });

    let isValidating = false; // Drapeau pour éviter les appels multiples

    function validateAndCommit() {
        if (isValidating) return; // Éviter les appels multiples
        isValidating = true; // Définir le drapeau

        const value = parseInt(input.value, 10); // Convertir la valeur en entier

        // Vérifier si la valeur est un nombre valide et entre 1 et 31
        if (isNaN(value) || value < 1 || value > 31) {
            alert("La valeur doit être un nombre entre 1 et 31.");
            isValidating = true; // Réinitialiser le drapeau
            return;
        }

        // Vérifier la longueur de la valeur
        if (input.value.length > 2) {
            alert("La valeur ne peut pas dépasser 2 chiffres.");
            isValidating = true; // Réinitialiser le drapeau
            return;
        }

        success(input.value);
        isValidating = false; // Réinitialiser le drapeau
    }

    input.addEventListener("blur", function() {
        validateAndCommit();
    });

    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            validateAndCommit();
            setTimeout(function() {
                // Vérifiez si la cellule suivante est différente avant de la focaliser
                const nextCell = focusNextEditableCell(cell);
                if (nextCell && nextCell !== cell) {
                    nextCell.focus(); // Focaliser la cellule suivante
                }
            }, 50);
        }
    });

    return input;
}
function customNumberEditor(cell, onRendered, success, cancel) {
    // Crée un input de type number
    const input = document.createElement("input");
    input.type = "number";
    input.style.width = "100%";
    // Initialiser la valeur avec la valeur actuelle de la cellule ou une chaîne vide
    input.value = cell.getValue() || "";

    // Focus sur l'input une fois rendu
    onRendered(function() {
        input.focus();
        input.style.height = "100%";
    });

    // Fonction de validation : ici, nous validons simplement en retournant la valeur de l'input
    function validateAndCommit() {
        // Vous pouvez ajouter des validations supplémentaires si besoin
        success(input.value);
    }

    // Lors du blur, valider la saisie
    input.addEventListener("blur", function() {
        validateAndCommit();
    });

    // Intercepter la touche Entrée pour valider et naviguer
    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            validateAndCommit();
            // Passer à la cellule éditable suivante
            setTimeout(function() {
                focusNextEditableCell(cell);
            }, 50);
        }
    });

    return input;
}


// Fonction pour sélectionner toutes les lignes et cocher toutes les cases
function selectAllRows() {
    // Vérifier si toutes les lignes sont déjà sélectionnées
    var allRowsSelected = table.getRows().every(function(row) {
        return row.isSelected();
    });

    // Si toutes les lignes sont sélectionnées, les désélectionner
    if (allRowsSelected) {
        table.deselectRow(); // Désélectionner toutes les lignes
        table.getRows().forEach(function(row) {
            var checkbox = row.getCell("actions").getElement().querySelector("input[type='checkbox']");
            if (checkbox) {
                checkbox.checked = false; // Décocher toutes les cases
            }
        });
    } else {
        // Si ce n'est pas le cas, sélectionner toutes les lignes
        table.selectRow(); // Sélectionner toutes les lignes
        table.getRows().forEach(function(row) {
            var checkbox = row.getCell("actions").getElement().querySelector("input[type='checkbox']");
            if (checkbox) {
                checkbox.checked = true; // Cocher toutes les cases
            }
        });
    }
}



    function deleteTransaction(transactionId) {
    var mois = $('#month-select').val();
    var annee = $('input[type="text"]').val();
    var journalCode = document.getElementById('journal-select').value;

    // Vérifiez si le mois est clôturé
    if (isMonthClosed(mois, annee, journalCode)) {
        Swal.fire({
            title: 'Alerte',
            text: 'Le mois est déjà clôturé. Vous ne pouvez pas supprimer des transactions.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return; // Sortir de la fonction si le mois est clôturé
    }

      $.ajax({
            url: '/delete-transaction',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: transactionId
            },
            success: function(response) {
                if (response.success) {
                    console.log("Transaction supprimée avec succès");
                    table.deleteRow(transactionId);
                    updateTotals($('#month-select').val(),  $('#year-select').val());
                saveData();
                } else {
                    console.error("Erreur lors de la suppression : " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Erreur lors de la suppression :", error);
            }
        });

}



    // Fonction pour sélectionner ou désélectionner toutes les lignes
    // function toggleSelectAll() {
    //     var allRows = table.getRows();
    //     var allSelected = allRows.every(row => row.getSelected());

    //     if (allSelected) {
    //         table.deselectRow();
    //     } else {
    //         table.selectRow();
    //     }
    // }

    // Fonction pour supprimer les lignes sélectionnées
    function deleteSelectedRows() {
        var selectedRows = table.getSelectedRows();

        if (selectedRows.length === 0) {
            alert("Aucune ligne sélectionnée !");
            return;
        }


        if (confirm("Êtes-vous sûr de vouloir supprimer les lignes sélectionnées ?")) {
            selectedRows.forEach(function(row) {
                var rowData = row.getData();
                deleteTransaction(rowData.id);
            });
        }
    }

    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function saveData() {
        var mois = $('#month-select').val();
        var year = $('#year-select').val();
        // var dateStr = "{{ $societe->exercice_social_debut }}"; // Récupère la date depuis Laravel
        // var year = new Date(dateStr).getFullYear();
        var journalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné

        // Enregistrer le solde actuel
        $.ajax({
            url: '/save-solde',
            method: 'POST',
            data: {
                mois: mois,
                annee: year,
                solde_initial: $('#initial-balance').val(),
                total_recette: $('#total-revenue').val(),
                total_depense: $('#total-expense').val(),
                solde_final: $('#final-balance').val(),
                journal_code: journalCode,
                _token: csrfToken
            },
            success: function(response) {
                console.log(response);
                // Mettre à jour les soldes des mois suivants
                updateSubsequentBalances(mois, year, journalCode, parseFloat($('#final-balance').val()));
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }
    function updateSubsequentBalances(month, year, journalCode, newBalance) {
        var monthInt = parseInt(month);
        var yearInt = parseInt(year);

        // Parcourir les mois suivants
        for (var i = monthInt + 1; i <= 12; i++) {
            // Si on atteint décembre, on passe à l'année suivante
            if (i > 12) {
                i = 1;
                yearInt++;
            }

            // Récupérer le solde du mois suivant
            var soldeMensuel = soldesMensuels.find(function(solde) {
                return parseInt(solde.mois) === i && parseInt(solde.annee) === yearInt && solde.code_journal === journalCode;
            });

            if (soldeMensuel) {
                // Calculer le nouveau solde final
                var newFinalBalance = newBalance + parseFloat(soldeMensuel.total_recette || 0) - parseFloat(soldeMensuel.total_depense || 0);
                newBalance = newFinalBalance; // Mettre à jour le solde précédent

                // Envoyer la mise à jour au serveur
                $.ajax({
                    url: '/save-solde',
                    method: 'POST',
                    data: {
                        mois: i,
                        annee: yearInt,
                        solde_initial: soldeMensuel.solde_initial,
                        total_recette: soldeMensuel.total_recette,
                        total_depense: soldeMensuel.total_depense,
                        solde_final: newFinalBalance,
                        journal_code: journalCode,
                        _token: csrfToken
                    },
                    success: function(response) {
                        console.log("Mise à jour du solde pour " + i + "/" + yearInt + " réussie.");
                    },
                    error: function(xhr, status, error) {
                        console.error("Erreur lors de la mise à jour du solde pour " + i + "/" + yearInt + " :", error);
                    }
                });
            }
        }
    }

    function updateTotals(month, year) {
    var totalRecette = 0;
    var totalDepense = 0;
    var filteredTransactions = filterTransactions(month, year, document.getElementById('journal-select').value);

    filteredTransactions.forEach(function(row) {
        totalRecette += parseFloat(row.recette || 0);
        totalDepense += parseFloat(row.depense || 0);
    });

    console.log("Total Recette:", totalRecette); // Vérifiez le total des recettes
    console.log("Total Dépense:", totalDepense); // Vérifiez le total des dépenses

    // Mettre à jour les éléments du pied de page pour afficher les totaux
    $('#total-revenue-footer').text(totalRecette.toFixed(2));
    $('#total-expense-footer').text(totalDepense.toFixed(2));

    // Mettre à jour les champs d'entrée pour les totaux
    $('#total-revenue').val(totalRecette.toFixed(2));
    $('#total-expense').val(totalDepense.toFixed(2));

    var soldeInitial = parseFloat($('#initial-balance').val() || 0);
    var soldeFinal = soldeInitial + totalRecette - totalDepense;
    $('#final-balance').val(soldeFinal.toFixed(2));

    console.log("Solde Final:", soldeFinal); // Vérifiez le solde final

    // Changer la couleur de fond en fonction du solde final
    if (soldeFinal < 0) {
        $('#final-balance').css('background-color', 'red');
    } else {
        $('#final-balance').css('background-color', '#52b438');
    }
    saveData();

}

    $('#initial-balance').on('input', function() {
        updateTotals($('#month-select').val(),  $('#year-select').val());
        saveData();
    });

    $(document).ready(function() {
        var currentMonth = $('#month-select').val();
        var currentYear = $('#year-select').val();
        updateTableData(currentMonth, currentYear);
    });

    $('#month-select, #year-select').on('change', function() {
        var currentMonth = $('#month-select').val();
        var currentYear =   $('#year-select').val();
        updateTableData(currentMonth, currentYear);

    });
// Variable pour stocker la réponse de l'utilisateur
let userResponse = null;

// Fonction pour vérifier si la référence existe déjà dans le tableau
function checkReferenceExists(reference, currentRowId) {
    const existingTransaction = transactions.find(function(transaction) {
        return transaction.reference === reference && transaction.id !== currentRowId;
    });
    return existingTransaction ? {
        exists: true,
        month: new Date(existingTransaction.date).getMonth() + 1, // Récupérer le mois (1-12)
        year: new Date(existingTransaction.date).getFullYear() // Récupérer l'année
    } : { exists: false };
}

// Événement cellEdited pour vérifier la référence
table.on("cellEdited", function(cell) {
    var field = cell.getField();
    var newValue = cell.getValue();
    var rowData = cell.getRow().getData();

    // Vérifiez si le champ modifié est la référence
    if (field === "ref") {
        var referenceCheck = checkReferenceExists(newValue, rowData.id);
        if (referenceCheck.exists) {
            // Afficher une alerte avec SweetAlert2
            var period = referenceCheck.month + '/' + referenceCheck.year; // Format de la période
            Swal.fire({
                title: `La référence N° "${newValue}" existe déjà dans la période ${period}`,
                text: "Voulez-vous continuer ou annuler ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui',
                cancelButtonText: 'Non'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si l'utilisateur choisit "Oui", accepter la modification et conserver la nouvelle valeur
                    userResponse = 'continue'; // Stocker la réponse
                } else {
                    // Si l'utilisateur choisit "Non", réinitialiser la valeur de la cellule
                    cell.setValue(rowData.ref); // Réinitialiser à l'ancienne valeur
                    userResponse = 'cancel'; // Stocker la réponse
                    location.reload();
                }
            });
        }
    }

    // Sélectionner la ligne en cours après modification
    table.deselectRow(); // Désélectionner toutes les lignes
    cell.getRow().select(); // Sélectionner la ligne en cours
    updateTotals($('#month-select').val(),  $('#year-select').val());

    // Appeler saveData() après que les totaux ont été mis à jour
    saveData();
});
// Événement pour enregistrer les données lors de l'appui sur "Entrée"
$('#example-table').on('keydown', function(e) {
    if (e.key === "Enter") {
        var selectedRows = table.getSelectedRows();
        if (selectedRows.length > 0) {
            var rowData = selectedRows[0].getData();
            var selectedMonth = $('#month-select').val();
            var selectedYear = $('#year-select').val();

            var formattedDate = selectedYear + '-' + selectedMonth + '-' + rowData.day.padStart(2, '0');
            var journalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné

            // Vérifier si userResponse est vide et le remplacer par 0
            var userResponseToSend = userResponse ? userResponse : 0;

            // Effacer les messages d'erreur précédents
            $('#error-message').text('');

            // Vérification des valeurs vides
            if (!rowData.day) {
                $('#error-message').text("Le jour ne peut pas être vide.");
                return;
            }
            if (!rowData.depense && !rowData.recette) {
                $('#error-message').text("Vous devez entrer soit une dépense soit une recette.");
                return;
            }
            if (isMonthClosed(selectedMonth, selectedYear, journalCode)) {
                $('#error-message').text("Le mois est déjà clôturé. Vous ne pouvez pas modifier des transactions.");
                return; // Sortir de la fonction si le mois est clôturé
            } else {
                $.ajax({
                    url: '/save-transaction',
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        date: formattedDate,
                        ref: rowData.ref,
                        libelle: rowData.libelle,
                        recette: rowData.recette,
                        depense: rowData.depense,
                        journal_code: journalCode,
                        user_response: userResponseToSend
                    },
                    success: function(response) {
                        // Mettre à jour les totaux
                        updateTotals($('#month-select').val(),  $('#year-select').val());
                        // Ajouter la nouvelle transaction au tableau
                        table.addData([{
                            id: response.id, // Assurez-vous que l'ID est renvoyé par le serveur
                            day: rowData.day,
                            ref: rowData.ref,
                            libelle: rowData.libelle,
                            recette: rowData.recette,
                            depense: rowData.depense
                        }], true); // Le deuxième paramètre 'true' permet d'ajouter les données en haut du tableau

                        // Réinitialiser les champs d'entrée
                        selectedRows[0].update({
                            day: '',
                            ref: '',
                            libelle: '',
                            recette: '',
                            depense: ''
                        });

                        // Enregistrer les données
                        saveData();

                        // Mettre le focus sur le premier champ d'entrée de la nouvelle ligne
                        // Remplacez '#first-input-field' par le sélecteur approprié pour votre champ
                        $('#first-input-field').focus();
                    },
                    error: function(xhr, status, error) {
                        console.error("Erreur lors de l'envoi des données :", error);
                        console.log(xhr.responseText);
                    }
                });
            }
        } else {
            console.log("Aucune ligne sélectionnée !");
        }
    }
});


function exportToExcel() {
    // Récupérer les données du tableau
    const tableData = table.getData();

    // Créer un tableau pour les en-têtes et les données
    const headers = ["Jour", "N° Référence", "Libellé", "Recette", "Dépense"];
    const data = [headers];

    // Ajouter les données du tableau
    tableData.forEach(row => {
        data.push([row.day, row.ref, row.libelle, row.recette, row.depense]);
    });

    // Créer un nouveau classeur
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(data);

    // Ajouter la feuille au classeur
    XLSX.utils.book_append_sheet(wb, ws, "État de Caisse");

    // Exporter le fichier Excel
    XLSX.writeFile(wb, "etat_caisse_mensuelle.xlsx");
}





function isMonthClosed(month, year, journalCode) {
    var soldeMensuel = soldesMensuels.find(function(solde) {
        var moisComparaison = parseInt(solde.mois).toString().padStart(2, '0');
        var anneeComparaison = parseInt(solde.annee).toString();
        return moisComparaison === month && anneeComparaison === year && solde.code_journal === journalCode; // Inclure le code journal
    });
    return soldeMensuel && soldeMensuel.cloturer === 1;
}


// Ajoutez cette fonction pour mettre à jour la visibilité de l'icône
function updateShareIconVisibility() {
    var cloturerButton = document.getElementById('cloturer-button');
    var shareIcon = document.querySelector('.fa-share');

    if (cloturerButton.disabled) {
        shareIcon.classList.remove('hidden');
     } else {
        shareIcon.classList.add('hidden');     }
}

// Appelez cette fonction chaque fois que vous modifiez l'état du bouton "Clôturer"
document.getElementById('cloturer-button').addEventListener('click', function() {

     updateShareIconVisibility();
});

// Écoutez l'événement keydown pour le champ "Code Journal"
// Écoutez l'événement keydown pour le champ "Code Journal"
document.getElementById('journal-select').addEventListener('keydown', function(e) {
    if (e.key === "Enter") {
        e.preventDefault(); // Empêche le comportement par défaut
        document.getElementById('month-select').focus(); // Focaliser le champ "Période"
    }
});

// Écoutez l'événement keydown pour le champ "Période"
document.getElementById('month-select').addEventListener('keydown', function(e) {
    if (e.key === "Enter") {
        e.preventDefault(); // Empêche le comportement par défaut
        var selectedMonth = this.value; // Récupérer le mois sélectionné
        if (selectedMonth !== "01") {
            // Si le mois n'est pas janvier, passer directement au tableau
            const firstRow = table.getRows()[0];
            if (firstRow) {
                const firstCell = firstRow.getCells()[0];
                firstCell.edit(); // Éditer la première cellule
            }
        } else {
            // Si c'est janvier, focaliser le champ "Solde initial"
            document.getElementById('initial-balance').focus(); // Focaliser le champ "Solde initial"
        }
    }
});


// Écoutez l'événement keydown pour le champ "Solde initial"
document.getElementById('initial-balance').addEventListener('keydown', function(e) {
    if (e.key === "Enter") {
        e.preventDefault(); // Empêche le comportement par défaut
        // Focaliser la première cellule du tableau
        const firstRow = table.getRows()[0];
        if (firstRow) {
            const firstCell = firstRow.getCells()[0];
            firstCell.edit(); // Éditer la première cellule
        }
    }
});