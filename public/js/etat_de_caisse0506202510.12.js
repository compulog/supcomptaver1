console.log(soldesMensuels);
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
    var selectedMonth = parseInt(this.value, 10);
    var selectedYear = parseInt(document.getElementById('year-select').value, 10);
    var selectedJournalCode = document.getElementById('journal-select').value;

    // Calcul du mois et année précédent
    var prevMonth = selectedMonth - 1;
    var prevYear = selectedYear;
    if (prevMonth === 0) {
        prevMonth = 12;
        prevYear -= 1;
    }
    var prevMonthStr = prevMonth.toString().padStart(2, '0');
    var prevYearStr = prevYear.toString();

    // Vérifier si le mois précédent est clôturé
    var prevClosed = isMonthClosed(prevMonthStr, prevYearStr, selectedJournalCode);

    if (!prevClosed && selectedMonth !== 1) {
        Swal.fire({
            title: 'Attention',
            text: 'Veuillez clôturer le mois précédent avant de continuer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Clôturer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                // Clôturer le mois précédent automatiquement
                $.ajax({
                    url: '/cloturer-solde',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        mois: prevMonthStr,
                        annee: prevYearStr,
                        journal_code: selectedJournalCode
                    },
                    success: function(response) {
                        Swal.fire('Succès', 'Le mois précédent a été clôturé.', 'success');
                        // Mettre à jour l'état local
                        var soldeMensuel = soldesMensuels.find(function(solde) {
                            return parseInt(solde.mois) === parseInt(prevMonthStr) &&
                                   parseInt(solde.annee) === parseInt(prevYearStr) &&
                                   solde.code_journal === selectedJournalCode;
                        });
                        if (soldeMensuel) {
                            soldeMensuel.cloturer = 1;
                        }
                        // Relancer la sélection du mois courant pour rafraîchir l'affichage
                        filterSoldeInitial(selectedMonth.toString().padStart(2, '0'), selectedYear, selectedJournalCode);
                        if (selectedMonth !== 1) {
                            document.getElementById('initial-balance').disabled = true;
                        } else {
                            document.getElementById('initial-balance').disabled = false;
                        }
                        // Désactiver le bouton "Clôturer" si le mois courant est clôturé
                        if (isMonthClosed(selectedMonth.toString().padStart(2, '0'), selectedYear.toString(), selectedJournalCode)) {
                            document.getElementById('cloturer-button').disabled = true;
                        } else {
                            document.getElementById('cloturer-button').disabled = false;
                        }
                        updateTotals($('#month-select').val(),  $('#year-select').val());
                        saveData();
                        updateTableData(selectedMonth.toString().padStart(2, '0'), selectedYear);
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Erreur', 'Erreur lors de la clôture du mois précédent.', 'error');
                    }
                });
            } else {
                // Annuler la sélection du mois
                // Optionnel : remettre l'ancien mois sélectionné si tu le stockes dans une variable
                // Par exemple : document.getElementById('month-select').value = previousSelectedMonth;
            }
        });
        return;
    }
    // Si tout va bien, continuer normalement
    filterSoldeInitial(this.value, selectedYear, selectedJournalCode);

    // Désactiver le bouton "Clôturer" si le mois courant est clôturé
    if (isMonthClosed(this.value, selectedYear.toString(), selectedJournalCode)) {
        document.getElementById('cloturer-button').disabled = true;
    } else {
        document.getElementById('cloturer-button').disabled = false;
    }

    if (this.value !== "01") {
        document.getElementById('initial-balance').disabled = true;
    } else {
        document.getElementById('initial-balance').disabled = false;
    }
    updateTotals($('#month-select').val(),  $('#year-select').val());
    saveData();
    updateTableData(this.value, selectedYear);
});

document.getElementById('cloturer-button').addEventListener('click', function() {
    var mois = $('#month-select').val();
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

// Fonction de mise à jour du tableau avec condition de clôture du mois
function updateTableData(month, year) {
    var journalCode = document.getElementById('journal-select').value;
    var filteredTransactions = filterTransactions(month, year, journalCode);

    var tableData = filteredTransactions.map(function(transaction) {
        return {
            id: transaction.id,
            day: new Date(transaction.date).getDate(),
            ref: transaction.reference,
            libelle: transaction.libelle,
            recette: transaction.recette,
            depense: transaction.depense,
            attachment_url: transaction.attachment_url,
            attachmentName: transaction.attachmentName,
                    updated_at: transaction.updated_at,
                            updated_by_name: transaction.updated_by // <-- Ajoute cette ligne


        };
    });

    table.setData(tableData); // recharge les données sans remplacer l'objet table

    // Ajoute une ligne vide si le mois n'est pas clôturé
    if (!isMonthClosed(month, year, journalCode)) {
        table.addRow({
            day: "",
            libelle: "",
            recette: "",
            depense: "",
            ref: "",
            attachment_url: "",
            attachmentName: "",
            updated_by:""
        });
    }

    updateTotals(month, year);
}


document.getElementById('journal-select').addEventListener('change', function() {
    var selectedMonth = document.getElementById('month-select').value;
    var selectedYear = document.getElementById('year-select').value;
    var selectedJournalCode = this.value; // Récupérer le code journal sélectionné
    filterSoldeInitial(selectedMonth, selectedYear, selectedJournalCode); // Appeler la fonction avec le code journal
    updateTableData(selectedMonth, selectedYear); // Mettre à jour le tableau avec le nouveau code journal
    updateTotals($('#month-select').val(),  $('#year-select').val());
    saveData();
});
document.addEventListener('DOMContentLoaded', function() {
    var selectedMonth = document.getElementById('month-select').value;
    var selectedYear = document.getElementById('year-select').value;
    var selectedJournalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné
    filterSoldeInitial(selectedMonth, selectedYear, selectedJournalCode); // Appeler la fonction avec le code journal
// Mettre le focus sur le sélecteur de code journal au chargement de la page
document.getElementById('journal-select').focus();


});



var table = new Tabulator("#example-table", {

    // ajaxURL: "/etat_de_caisse", // URL qui retourne les données JSON complètes
    height: 500,
    layout: "fitColumns",
    columns: [
        {title: "Jour", field: "day", editor: customNumberEditor1 , editorPlaceholder: "jj", width: 65, headerFilter: "input", headerFilterParams: {
            elementAttributes: {
                style: "width: 55px; height: 20px;"
            }
        }
    },

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
         {title: "N° Piéce", field: "ref",  editor: genericTextEditor , editorPlaceholder: "Entrez le N° Piéce", width: 200, headerFilter: "input", headerFilterParams: {
            elementAttributes: {
                style: "width: 190px; height: 20px;"
            }
        }},

  {
    title: `<input type="checkbox" id="selectAllCheckbox" title="Tout sélectionner" style="cursor:pointer;">`,
    field: "actions",
    hozAlign: "center",
    width: 120,
    formatter: function(cell) {
        const row = cell.getRow();

        const data = row.getData();

        const container = document.createElement("div");
        container.style.display = "flex";
        container.style.alignItems = "center";
        container.style.justifyContent = "space-around";

        // 📎 Upload
        const uploadLabel = document.createElement("label");
        uploadLabel.textContent = "📎";
        uploadLabel.title = "Joindre un fichier";
        uploadLabel.style.cursor = "pointer";

        const fileInput = document.createElement("input");
        fileInput.type = "file";
        fileInput.style.display = "none";
        fileInput.accept = "*/*";
        uploadLabel.appendChild(fileInput);
        container.appendChild(uploadLabel);

        fileInput.addEventListener("change", function () {
            const file = fileInput.files[0];
            if (!file) return;

            fileInput.disabled = true;

            const formData = new FormData();
            formData.append("file", file);
            formData.append("transaction_id", data.id);

            fetch("/upload-attachment", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                },
                body: formData,
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    row.update({
                        attachment_url: response.url,
                        attachmentName: response.filename
                    });
                    alert("📎 Fichier attaché !");
                } else {
                    alert("❌ Upload échoué");
                }
                fileInput.disabled = false;
            })
            .catch(() => {
                alert("❌ Erreur réseau");
                fileInput.disabled = false;
            });
        });

        // 👁️ Voir
        const viewBtn = document.createElement("span");
        viewBtn.innerHTML = "👁️";
        viewBtn.title = data.attachmentName || "Pas de fichier";

        if (data.attachment_url) {
            viewBtn.style.cursor = "pointer";
            viewBtn.style.color = "initial";
        } else {
            viewBtn.style.cursor = "not-allowed";
            viewBtn.style.color = "grey";
        }

        viewBtn.addEventListener("click", function () {
            viewAttachment(row);
        });

        container.appendChild(viewBtn);

        // ✅ Checkbox
        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.style.cursor = "pointer";
        checkbox.checked = row.isSelected();

        checkbox.addEventListener("change", () => {
            if (checkbox.checked) {
                row.select();
            } else {
                row.deselect();
            }
        });

        container.appendChild(checkbox);

        return container;
    }
},
{
    title: "Dernière modification",
    field: "updated_at",
    width: 170,
    formatter: function(cell) {
        const value = cell.getValue();
        return value ? new Date(value).toLocaleString('fr-FR') : "";
    }
},

{
    title: "Modifié par",
    field: "updated_by_name",
    width: 150,
    formatter: function(cell) {
        return cell.getValue() ? cell.getValue() : "";
    }
},
    ],

    // 🔄 Recharge les données existantes avec attachment_url et attachmentName
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
     input.style.height = "100%";
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

            // Si on est dans le champ N° Piéce (ref), enregistrer la ligne
            if (cell.getField() === "ref") {
                // Sélectionner la ligne courante
                table.deselectRow();
                cell.getRow().select();

                // Déclencher l'enregistrement comme dans l'événement global
                var rowData = cell.getRow().getData();
                var selectedMonth = $('#month-select').val();
                var selectedYear = $('#year-select').val();
                var formattedDate = selectedYear + '-' + selectedMonth + '-' + (rowData.day ? rowData.day.toString().padStart(2, '0') : '01');
                var journalCode = document.getElementById('journal-select').value;
                var userResponseToSend = userResponse ? userResponse : 0;

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
                    return;
                }

                $.ajax({
                    url: '/save-transaction',
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        date: formattedDate,
                        libelle: rowData.libelle,
                        recette: rowData.recette,
                        depense: rowData.depense,
                        ref: rowData.ref,
                        attachment_url: rowData.attachment_url,
                        attachmentName: rowData.attachmentName,
                        journal_code: journalCode,
                        user_response: userResponseToSend
                    },
                    success: function(response) {
                        updateTotals($('#month-select').val(),  $('#year-select').val());
                        table.addData([{
                            id: response.id,
                            day: rowData.day,
                            libelle: rowData.libelle,
                            recette: rowData.recette,
                            depense: rowData.depense,
                            ref: rowData.ref,
                            attachment_url: rowData.attachment_url,
                            attachmentName: rowData.attachmentName,
                            updated_by: rowData.updated_by_name
                        }], true);

                        // Réinitialiser la ligne
                        cell.getRow().update({
                            day: '',
                            libelle: '',
                            recette: '',
                            depense: '',
                            ref: '',
                            attachment_url: '',
                            attachmentName: '',
                            updated_by: '',
                        });

                        saveData();
                    },
                    error: function(xhr, status, error) {
                        console.error("Erreur lors de l'envoi des données :", error);
                        console.log(xhr.responseText);
                    }
                });
            } else {
                setTimeout(() => {
                    focusNextEditableCell(cell);
                }, 50);
            }
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

   // Fonction à exécuter une fois le DOM de l'en-tête est rendu
setTimeout(() => {
    const selectAllCheckbox = document.getElementById("selectAllCheckbox");

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener("change", function (e) {
            const checked = e.target.checked;

            if (checked) {
                table.selectRow();
            } else {
                table.deselectRow();
            }

            // Mettre à jour tous les checkbox individuels
            table.getRows().forEach(row => {
                const cell = row.getCell("actions");
                const element = cell.getElement();
                const checkbox = element.querySelector("input[type='checkbox']");
                if (checkbox) checkbox.checked = checked;
            });
        });
    }
}, 500); // délai pour s'assurer que le DOM est prêt


// Fonction pour sélectionner toutes les lignes et cocher toutes les cases
function selectAllCheckbox() {
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
                title: `La Piéce N° "${newValue}" existe déjà dans la période ${period}`,
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
// $('#example-table').on('keydown', function(e) {
//     if (e.key === "Enter") {
//         var selectedRows = table.getSelectedRows();
//         if (selectedRows.length > 0) {
//             var rowData = selectedRows[0].getData();
//             var selectedMonth = $('#month-select').val();
//                         var selectedYear = $('#year-select').val();

//             var formattedDate = selectedYear + '-' + selectedMonth + '-' + rowData.day.padStart(2, '0');
//             var journalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné

//             // Vérifier si userResponse est vide et le remplacer par 0
//             var userResponseToSend = userResponse ? userResponse : 0;

//             // Effacer les messages d'erreur précédents
//             $('#error-message').text('');

//             // Vérification des valeurs vides
//             if (!rowData.day) {
//                 $('#error-message').text("Le jour ne peut pas être vide.");
//                 return;
//             }
//             if (!rowData.depense && !rowData.recette) {
//                 $('#error-message').text("Vous devez entrer soit une dépense soit une recette.");
//                 return;
//             }
//             if (isMonthClosed(selectedMonth, selectedYear, journalCode)) {
//                 $('#error-message').text("Le mois est déjà clôturé. Vous ne pouvez pas modifier des transactions.");
//                 return; // Sortir de la fonction si le mois est clôturé
//             } else {
//                 $.ajax({
//                     url: '/save-transaction',
//                     type: "POST",
//                     data: {
//                         _token: $('meta[name="csrf-token"]').attr('content'),
//                         date: formattedDate,
//                         libelle: rowData.libelle,
//                         recette: rowData.recette,
//                         depense: rowData.depense,
//                         ref: rowData.ref,
//                       attachment_url: rowData.attachment_url,
//                              attachmentName: rowData.attachmentName,
//                         journal_code: journalCode,
//                         user_response: userResponseToSend
//                     },
//                     success: function(response) {
//                         // Mettre à jour les totaux
//                          updateTotals($('#month-select').val(),  $('#year-select').val());

//                         // Ajouter la nouvelle transaction au tableau
//                         table.addData([{
//                             id: response.id, // Assurez-vous que l'ID est renvoyé par le serveur
//                             day: rowData.day,
//                             libelle: rowData.libelle,
//                             recette: rowData.recette,
//                             depense: rowData.depense,
//                             ref: rowData.ref,

//                             attachment_url: rowData.attachment_url,
//                              attachmentName: rowData.attachmentName,
//                              updated_by:rowData.updated_by_name


//                         }], true); // Le deuxième paramètre 'true' permet d'ajouter les données en haut du tableau

//                         // Réinitialiser les champs d'entrée
//                         selectedRows[0].update({
//                             day: '',
//                             libelle: '',
//                             recette: '',
//                             depense: '',
//                              ref: '',

//                             attachment_url:'',
//                             attachmentName:'',
//                             updated_by:'',
//                         });

//                         // Enregistrer les données
//                         saveData();

//                         // Mettre le focus sur le premier champ d'entrée de la nouvelle ligne
//                         // Remplacez '#first-input-field' par le sélecteur approprié pour votre champ
//                         $('#first-input-field').focus();
//                     },
//                     error: function(xhr, status, error) {
//                         console.error("Erreur lors de l'envoi des données :", error);
//                         console.log(xhr.responseText);
//                     }
//                 });
//             }
//         } else {
//             console.log("Aucune ligne sélectionnée !");
//         }
//     }
// });
function openFileUploadDialog(row) {
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "*/*";

    input.onchange = function(event) {
        const file = event.target.files[0];
        if (file) {
            const formData = new FormData();
            formData.append("file", file);
            formData.append("transaction_id", row.getData().id);

            fetch("/upload-attachment", {
                method: "POST",
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    row.update({ attachmentName: result.filename, attachment_url: result.url });
                    alert("📎 Fichier attaché avec succès.");
                } else {
                    alert("❌ Erreur lors de l'envoi du fichier.");
                }
            })
            .catch(() => {
                alert("❌ Erreur AJAX.");
            });
        }
    };

    input.click();
}

function viewAttachment(row) {
    const data = row.getData();
    if (data.attachment_url) {
        window.open(data.attachment_url, '_blank');
    } else {
        alert("👁️ Aucun fichier attaché.");
    }
}
document.getElementById("export-pdf-icon").addEventListener("click", exporterPDF);

/**
 * Helper to get user name by user ID from users array.
 */
function getUserNameById(userId) {
    if (!userId) return "N/A";
    const user = Users.find(u => u.id === userId);

    return user ? user.name : "N/A";
}

/**
 * Checks if the month/year/journalCode is closed in soldeMensuels.
 * Returns closure info and dates.
 */
function isMonthClosedpdf(month, year, journalCode) {
    var soldeMensuel = soldesMensuels.find(function(solde) {
        var moisComparaison = parseInt(solde.mois).toString().padStart(2, '0');
        var anneeComparaison = parseInt(solde.annee).toString();
        return moisComparaison === month && anneeComparaison === year && solde.code_journal === journalCode;
    });

    if (soldeMensuel && soldeMensuel.cloturer === 1) {
        return {
            closed: true,
            closureDate: soldeMensuel.updated_at || null,
            created_at: soldeMensuel.created_at || null
        };
    }

    return {
        closed: false,
        closureDate: null,
        created_at: null
    };
}

/**
 * Main function to export the PDF report.
 */
function exporterPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    const codeJournal = document.getElementById("journal-select").value;
    const intitule    = document.getElementById("intitule-input").value;
    const month       = document.getElementById("month-select").value;
    const year        = document.getElementById("year-select").value;
    const moisText    = document.querySelector("#month-select option:checked").textContent;
    const periodeText = `${moisText}`;

    const closureInfo = isMonthClosedpdf(month, year, codeJournal);
    if (!closureInfo.closed) {
        alert("Impossible d’exporter. La période sélectionnée n’est pas clôturée.");
        return;
    }

    const soldeInitial = Number(document.getElementById("initial-balance").value || 0).toFixed(2);
    const soldeFinal   = Number(document.getElementById("final-balance").value || 0).toFixed(2);
    const transactionRows = table.getData();

    let totalRecette = 0;
    let totalDepense = 0;
    let lastUpdatedBy = "";

    const rows = transactionRows.map(row => {
        const recette = row.recette ? parseFloat(row.recette) : 0;
        const depense = row.depense ? parseFloat(row.depense) : 0;
        totalRecette += recette;
        totalDepense += depense;

        const updatedByName = row.updated_by_name || '';
        lastUpdatedBy = updatedByName; // Retenir le dernier

        return [
            row.day || '',
            row.libelle || '',
            recette ? recette.toFixed(2) : '',
            depense ? depense.toFixed(2) : '',
            row.ref || ''
        ];
    });

    rows.push([
        { content: "TOTAL", colSpan: 2, styles: { halign: "right", fontStyle: "bold" } },
        { content: totalRecette.toFixed(2), styles: { fontStyle: "bold" } },
        { content: totalDepense.toFixed(2), styles: { fontStyle: "bold" } },
        ""
    ]);

    const pageWidth = doc.internal.pageSize.getWidth();
    doc.setFillColor(41, 128, 185);
    doc.rect(0, 0, pageWidth, 30, 'F');

    const title = "État de Caisse Mensuel";
    doc.setFontSize(16).setTextColor(255).setFont("helvetica", "bold");
    doc.text(title, (pageWidth - doc.getTextWidth(title)) / 2, 10);

    const yHeader = 16;
    const x1 = 10;
    const x2 = pageWidth / 3;
    const x3 = (2 * pageWidth) / 3;

    doc.setFontSize(10).setFont("helvetica", "normal").setTextColor(255);
    doc.text(`Code : ${codeJournal}`, x1, yHeader);
    doc.text(`Intitulé : ${intitule}`, x2, yHeader);
    doc.text(`Période : ${periodeText}`, x3, yHeader);

    // const yCreatedSolde = yHeader + 5;
    // doc.text(
    //     `Créé le : ${closureInfo.created_at ? new Date(closureInfo.created_at).toLocaleDateString('fr-FR') : "N/A"}`,
    //     x1,
    //     yCreatedSolde
    // );

    doc.setTextColor(0);
    const startYTable = 45;
    doc.setFontSize(12).text(`Solde initial : ${soldeInitial} MAD`, pageWidth - 80, startYTable - 10);

    doc.autoTable({
        startY: startYTable,
        head: [[ "Jour", "Libellé", "Recette", "Dépense", "N° Pièce" ]],
        body: rows,
        styles: { fontSize: 10, halign: "center" },
        headStyles: { fillColor: [41, 128, 185], textColor: 255, fontStyle: "bold" },
        alternateRowStyles: { fillColor: [245, 245, 245] },
        didDrawPage: function(data) {
            const pageNumber = doc.internal.getCurrentPageInfo().pageNumber;
            doc.setFontSize(9).setTextColor(150);
            doc.text(`Page ${pageNumber}`, pageWidth - 20, doc.internal.pageSize.getHeight() - 10);
        }
    });

    const finalY = doc.lastAutoTable.finalY + 10;

    // Solde final et clôture à droite
    doc.setFontSize(12).setTextColor(0);
    doc.text(`Solde final : ${soldeFinal} MAD`, pageWidth - 80, finalY);

    if (closureInfo.closureDate) {
        const closureDateFormatted = new Date(closureInfo.closureDate).toLocaleDateString('fr-FR');
        doc.setFontSize(11).setTextColor(255, 0, 0);
        doc.text(`Clôturé le : ${closureDateFormatted}`, pageWidth - 80, finalY + 6);
    }

    // Signature
    const sigY = finalY + 25;
    doc.setFontSize(11).setTextColor(0);
    doc.text(`Fait par : ${lastUpdatedBy}`, 14, sigY);

    doc.save(`etat_caisse_${month}_${year}.pdf`);
}







function exportToExcel() {
    // Récupérer les données du tableau
    const tableData = table.getData();

    // Créer un tableau pour les en-têtes et les données
    const headers = ["Jour", "Libellé", "Recette", "Dépense", "N° Piéce"];
    const data = [headers];

    // Ajouter les données du tableau
    tableData.forEach(row => {
        data.push([row.day,row.libelle, row.recette, row.depense, row.ref]);
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
        return moisComparaison === month && anneeComparaison === year && solde.code_journal === journalCode;
    });

    return (soldeMensuel && soldeMensuel.cloturer === 1)

}
function isMonthClosedpdf(month, year, journalCode) {
    var soldeMensuel = soldesMensuels.find(function(solde) {
        var moisComparaison = parseInt(solde.mois).toString().padStart(2, '0');
        var anneeComparaison = parseInt(solde.annee).toString();
        return moisComparaison === month && anneeComparaison === year && solde.code_journal === journalCode;
    });

    if (soldeMensuel && soldeMensuel.cloturer === 1)
        return {
            closed: true,
            closureDate: soldeMensuel.updated_at || null
        };


    return {
        closed: false,
        closureDate: null
    }
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
