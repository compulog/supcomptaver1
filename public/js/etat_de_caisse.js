console.log(soldesMensuels);

let userResponse = null;
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
document.addEventListener('DOMContentLoaded', function() {
    var selectedMonth = document.getElementById('month-select').value;
    var selectedYear = document.getElementById('year-select').value;
    var selectedJournalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné
    filterSoldeInitial(selectedMonth, selectedYear, selectedJournalCode); // Appeler la fonction avec le code journal
// Mettre le focus sur le sélecteur de code journal au chargement de la page
document.getElementById('journal-select').focus();


});
$(document).ready(function() {
        var currentMonth = $('#month-select').val();
        var currentYear = $('#year-select').val();
        updateTableData(currentMonth, currentYear);
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
        {title: "Recette", field: "recette", editor: customNumberEditor, editorPlaceholder: "Entrez la recette", width: 200, formatter: formatNumber, bottomCalc: "sum", headerFilter: "input", headerFilterParams: {
            elementAttributes: {
                style: "width: 180px; height: 20px;"
            }
        }},
        {title: "Dépense", field: "depense", editor: customNumberEditor, editorPlaceholder: "Entrez la dépense", width: 200, formatter: formatNumber, bottomCalc: "sum", headerFilter: "input", headerFilterParams: {
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
    title: `Action  <input type="checkbox" id="selectAllCheckbox" title="Tout sélectionner" style="cursor:pointer;">     <span id="select-stats" class="text-muted">0</span>`,
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

    // Stocker le fichier temporairement dans la ligne
    row.update({ selectedFile: file, attachmentName: file.name });

    uploadLabel.title = file.name;
    // alert("📎 Fichier prêt à être envoyé avec la transaction.");
        viewBtn.focus();

});


        // 👁️ Voir
       const viewBtn = document.createElement("span");
viewBtn.innerHTML = "👁️";
viewBtn.title = data.attachmentName || "Pas de fichier";
viewBtn.tabIndex = 0; // ← rendre focusable

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

// Ajout : déclencher saveRow(row) si Entrée sur la checkbox cochée
checkbox.addEventListener("keydown", function(e) {
    if (e.key === "Enter" && checkbox.checked) {
        e.preventDefault();
        saveRow(row);
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
        }, headerFilter: "input", headerFilterParams: {
                elementAttributes: {
                    style: "width: 55px; height: 20px;"
                }
            }
    },

    {
        title: "Modifié par",
        field: "updated_by_name",
        width: 150,
        formatter: function(cell) {
            return cell.getValue() ? cell.getValue() : "";
        }, headerFilter: "input", headerFilterParams: {
                elementAttributes: {
                    style: "width: 55px; height: 20px;"
                }
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

table.on("cellEditing", function(cell) {
    const rowIndex = cell.getRow().getPosition(true); // index absolu
    const field = cell.getField();
    localStorage.setItem("tabulatorFocus", JSON.stringify({ rowIndex, field }));
});
table.on("rowSelectionChanged", function(data, rows) {
    document.getElementById("select-stats").innerHTML = rows.length; // Afficher le nombre de lignes sélectionnées
});
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
                text: "Voulez-vous continuer ?",
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

$('#initial-balance').on('input', function() {
    // Update background color when the input changes
    const initialBalanceValue = parseFloat($(this).val());
    if (initialBalanceValue >= 0) {
        $(this).css('background-color', '#52b438'); // Green
    } else {
        $(this).css('background-color', 'red'); // Red
    }
    updateTotals($('#month-select').val(),  $('#year-select').val());
    saveData();
});
 document.addEventListener("visibilitychange", function() {
    if (!document.hidden) {
        setTimeout(restoreTabulatorFocus, 100); // petit délai pour que Tabulator soit prêt
    }
});

 window.addEventListener("focus", function() {
    setTimeout(restoreTabulatorFocus, 100);
});

$(document).on('keydown', function(e) {
    if (e.key === "Enter") {
        const selectedRows = table.getSelectedRows();
        if (selectedRows.length > 0) {
            selectedRows.forEach(function(row) {
                const checkbox = row.getCell("actions").getElement().querySelector("input[type='checkbox']");
                if (checkbox && checkbox.checked) {
                    // Vérifiez si la ligne existe déjà dans les données du tableau
                    const existingRow = table.getData().find(function(data) {
                        return data.id === row.getData().id;
                    });
                    if (!existingRow) {
                        saveRow(row);
                    } else {
                        console.log("La ligne existe déjà dans le tableau.");
                    }
                } else {
                    console.log("La case à cocher n'est pas cochée pour la ligne :", row.getData());
                }
            });
        } else {
            console.log("Aucune ligne sélectionnée !");
        }
    }
});

document.getElementById('month-select').addEventListener('change', function() {
    var selectedMonth = parseInt(this.value, 10);
    var selectedYear = parseInt(document.getElementById('year-select').value, 10);
    var selectedJournalCode = document.getElementById('journal-select').value;
console.log(selectedMonth, selectedYear, selectedJournalCode);

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

    // if (!prevClosed && selectedMonth !== 1) {
    //     Swal.fire({
    //         title: 'Attention',
    //         text: 'Veuillez clôturer le mois précédent avant de continuer.',
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonText: 'Clôturer',
    //         cancelButtonText: 'Annuler'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             // Clôturer le mois précédent automatiquement
    //             $.ajax({
    //                 url: '/cloturer-solde',
    //                 type: 'POST',
    //                 data: {
    //                     _token: $('meta[name="csrf-token"]').attr('content'),
    //                     mois: prevMonthStr,
    //                     annee: prevYearStr,
    //                     journal_code: selectedJournalCode
    //                 },
    //                 success: function(response) {
    //                     Swal.fire('Succès', 'Le mois précédent a été clôturé.', 'success');
    //                     // Mettre à jour l'état local
    //                     var soldeMensuel = soldesMensuels.find(function(solde) {
    //                         return parseInt(solde.mois) === parseInt(prevMonthStr) &&
    //                                parseInt(solde.annee) === parseInt(prevYearStr) &&
    //                                solde.code_journal === selectedJournalCode;
    //                     });
    //                     if (soldeMensuel) {
    //                         soldeMensuel.cloturer = 1;
    //                     }
    //                     // Relancer la sélection du mois courant pour rafraîchir l'affichage
    //                     filterSoldeInitial(selectedMonth.toString().padStart(2, '0'), selectedYear, selectedJournalCode);
    //                     if (selectedMonth !== 1) {
    //                         document.getElementById('initial-balance').disabled = true;
    //                     } else {
    //                         document.getElementById('initial-balance').disabled = false;
    //                     }
    //                     // Désactiver le bouton "Clôturer" si le mois courant est clôturé
    //                     if (isMonthClosed(selectedMonth.toString().padStart(2, '0'), selectedYear.toString(), selectedJournalCode)) {
    //                         document.getElementById('cloturer-button').disabled = true;
    //                     } else {
    //                         document.getElementById('cloturer-button').disabled = false;
    //                     }
    //                     updateTotals($('#month-select').val(),  $('#year-select').val());
    //                     saveData();
    //                     updateTableData(selectedMonth.toString().padStart(2, '0'), selectedYear);
    //                 },
    //                 error: function(xhr, status, error) {
    //                     Swal.fire('Erreur', 'Erreur lors de la clôture du mois précédent.', 'error');
    //                 }
    //             });
    //         } else {
    //             // Annuler la sélection du mois
    //             // Optionnel : remettre l'ancien mois sélectionné si tu le stockes dans une variable
    //             // Par exemple : document.getElementById('month-select').value = previousSelectedMonth;
    //         }
    //     });
    //     return;
    // }else{ 
    // // Si tout va bien, continuer normalement
    // filterSoldeInitial(this.value, selectedYear, selectedJournalCode);
    // }
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
const moisNoms = [
  "janvier", "février", "mars", "avril", "mai", "juin",
  "juillet", "août", "septembre", "octobre", "novembre", "décembre"
];

const title = `Êtes-vous sûr de vouloir clôturer la période ${moisNoms[mois - 1]} ${annee} ?`;
    // Créer un message de confirmation
    Swal.fire({
        title: title,
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
  const messageDiv = document.createElement('div');
        messageDiv.textContent = "Le solde a été clôturé avec succès !";
        Object.assign(messageDiv.style, {
            position: 'fixed',
            top: '150px',
            left: '70%',
            transform: 'translateX(-50%)',
            backgroundColor: '#4CAF50',
            color: 'white',
            padding: '10px 20px',
            borderRadius: '5px',
            zIndex: 9999
        });
        document.body.appendChild(messageDiv);
        setTimeout(() => location.reload(), 2000);
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

document.getElementById('cloturer-button').addEventListener('click', function() {

     updateShareIconVisibility();
});


document.getElementById('journal-select').addEventListener('keydown', function(e) {
    if (e.key === "Enter") {
        e.preventDefault(); // Empêche le comportement par défaut
        document.getElementById('month-select').focus(); // Focaliser le champ "Période"
    }
});

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

$('#initial-balance').on('input', function() {
        updateTotals($('#month-select').val(),  $('#year-select').val());
        saveData();
});



$('#month-select, #year-select').on('change', function() {
        var currentMonth = $('#month-select').val();
        var currentYear =   $('#year-select').val();
        updateTableData(currentMonth, currentYear);

});

function filterTransactions(month, year, journalCode) {
    return transactions.filter(function(transaction) {
        var transactionDate = new Date(transaction.date);
        return transactionDate.getMonth() + 1 === parseInt(month) &&
               transactionDate.getFullYear() === parseInt(year) &&
               transaction.code_journal === journalCode; // Filtrer par code journal
    });
}
function filterSoldeInitial(month, year, journalCode) {
    console.log(month, year, journalCode);
    // Convertir le mois et l'année en entiers
    var monthInt = parseInt(month);
    var yearInt = parseInt(year);
console.log("montint" + monthInt);
    // Récupérer le solde du mois sélectionné
    var soldeMensuel = soldesMensuels.find(function(solde) {
        var moisComparaison = parseInt(solde.mois).toString().padStart(2, '0');
        var anneeComparaison = parseInt(solde.annee).toString();
        return moisComparaison === month && anneeComparaison === year && solde.code_journal === journalCode;
    });

    let initialBalanceValue = 0; // Default value
     if (monthInt === 1) {
        // Si c'est janvier, utiliser le solde initial du mois de janvier
        if (soldeMensuel) {
            console.log("soldmenseil" + soldeMensuel)
            initialBalanceValue = soldeMensuel.solde_initial;
            document.getElementById('initial-balance').value = initialBalanceValue; // Utiliser le solde initial du mois de janvier

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
            initialBalanceValue = previousSoldeMensuel.solde_final;
            document.getElementById('initial-balance').value = initialBalanceValue; // Utiliser le solde final du mois précédent
        } else {
            document.getElementById('initial-balance').value = 0; // Si aucun solde trouvé, mettre à 0
        }
    }

    // Set background color based on initial balance value
    if (parseFloat(initialBalanceValue) >= 0) {
        document.getElementById('initial-balance').style.backgroundColor = '#52b438'; // Green
    } else {
        document.getElementById('initial-balance').style.backgroundColor = 'red'; // Red
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
function updateTableData(month, year) {
    var journalCode = document.getElementById('journal-select').value;
    var filteredTransactions = filterTransactions(month, year, journalCode);
  updateTotals(month, year);
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
            updated_by_name: transaction.updated_by
        };
    });

    // Ajoute une ligne vide au début si le mois n'est pas clôturé
    if (!isMonthClosed(month, year, journalCode)) {
        tableData.unshift({
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

    table.setData(tableData); // recharge les données sans remplacer l'objet table

  
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

function saveRow(row) {
    const rowData = row.getData();
    const selectedMonth = $('#month-select').val();
    const selectedYear = $('#year-select').val();
    const journalCode = document.getElementById('journal-select').value;
    const formattedDate = `${selectedYear}-${selectedMonth}-${rowData.day ? rowData.day.toString().padStart(2, '0') : '01'}`;
    const userResponseToSend = userResponse ? userResponse : 0;

    const userName = window.currentUserName || 'Utilisateur inconnu';

    const userNameElement = document.getElementById('user-name-display');
    if (userNameElement) {
        userNameElement.textContent = userName;
    }

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

    // Construction du FormData pour inclure un fichier s'il existe
    const formData = new FormData();
    formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
    formData.append("date", formattedDate);
    formData.append("libelle", rowData.libelle);
    formData.append("recette", rowData.recette);
    formData.append("depense", rowData.depense);
    formData.append("ref", rowData.ref);
    formData.append("journal_code", journalCode);
    formData.append("user_response", userResponseToSend);
    formData.append("updated_by", userName);

    if (rowData.selectedFile) {
        formData.append("file", rowData.selectedFile);
    }

    $.ajax({
        url: '/save-transaction',
        type: "POST",
        processData: false,
        contentType: false,
        data: formData,
        success: function(response) {
            updateTotals(selectedMonth, selectedYear);
            row.delete();

            table.addData([{
                id: response.id,
                day: rowData.day,
                libelle: rowData.libelle,
                recette: rowData.recette,
                depense: rowData.depense,
                ref: rowData.ref,
                attachment_url: response.attachment_url || '',
                attachmentName: response.attachmentName || '',
                updated_by_name: userName,
                updated_at: new Date().toISOString()
            }]);

            updateBalancesAfterTransaction(selectedMonth, selectedYear, rowData.recette, rowData.depense);

            const emptyRow = {
                day: '',
                libelle: '',
                recette: '',
                depense: '',
                ref: '',
                attachment_url: '',
                attachmentName: '',
                updated_by_name: ''
            };

            table.addData([emptyRow], true);
updateTotals(selectedMonth, selectedYear);

            saveData();

            setTimeout(() => {
                const firstRow = table.getRows()[0];
                if (firstRow && !firstRow.getData().id) {
                    const dayCell = firstRow.getCell("day");
                    if (dayCell) {
                        dayCell.edit();
                    }
                }
            }, 50);
        },
        error: function(xhr, status, error) {
            console.error("Erreur lors de l'envoi des données :", error);
            console.log(xhr.responseText);
        }
    });
}

function updateBalancesAfterTransaction(month, year, recette, depense) {
    // Récupérer le solde initial actuel
    const initialBalance = parseFloat($('#initial-balance').val()) || 0;
    const totalRecette = parseFloat(recette) || 0;
    const totalDepense = parseFloat(depense) || 0;

    // Calculer le nouveau solde final
    const newFinalBalance = initialBalance + totalRecette - totalDepense;

    // Mettre à jour les champs de solde
    $('#final-balance').val(newFinalBalance.toFixed(2));

    // Mettre à jour la couleur de fond en fonction du solde final
    if (newFinalBalance < 0) {
        $('#final-balance').css('background-color', 'red');
    } else {
        $('#final-balance').css('background-color', '#52b438');
    }

    // Enregistrer les nouveaux soldes
    saveData();
}

function restoreTabulatorFocus() {
    const focusData = localStorage.getItem("tabulatorFocus");
    if (focusData) {
        const { rowIndex, field } = JSON.parse(focusData);
        const rows = table.getRows();
        if (rows[rowIndex]) {
            const cell = rows[rowIndex].getCell(field);
            if (cell) cell.edit();
        }
    }
}

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

            if (cell.getField() === "ref") {
                table.deselectRow();
                cell.getRow().select();

                const actionsCell = cell.getRow().getCell("actions");
                if (actionsCell) {
                    const actionsCellElement = actionsCell.getElement();
                    const attachLabel = actionsCellElement.querySelector('label[title="Joindre un fichier"]');
                    const eyeIcon = actionsCellElement.querySelector('span[title="Pas de fichier"]');
                    const checkbox = actionsCellElement.querySelector('input[type="checkbox"]');

                    if (attachLabel) {
                        attachLabel.setAttribute("tabindex", "0");
                        attachLabel.focus();

                        const keyHandler = (e) => {
                            if (e.key === "Enter") {
                                attachLabel.click();
                                attachLabel.removeEventListener("keydown", keyHandler);
                            } else if (e.key === "Tab") {
                                e.preventDefault();

                                // Focus sur l'icône 
                                if (eyeIcon) {
                                    eyeIcon.setAttribute("tabindex", "0");
                                    eyeIcon.focus();

                                    eyeIcon.style.outline = "2px dashed #999";
                                    setTimeout(() => {
                                        eyeIcon.style.outline = "";
                                    }, 1500);

                                    const eyeKeyHandler = (e) => {
                                        if (e.key === "Enter") {
                                            e.preventDefault();
                                            viewAttachment(cell.getRow());
                                            eyeIcon.removeEventListener("keydown", eyeKeyHandler);
                                        }
                                    };

                                    eyeIcon.addEventListener("keydown", eyeKeyHandler, { once: true });
                                }

                                // Focus ensuite sur la checkbox (si présente)
                                if (checkbox) {
                                    checkbox.setAttribute("tabindex", "0");

                                    // Petit délai pour éviter conflit de focus
                                    setTimeout(() => {
                                        checkbox.focus();

                                        const checkboxKeyHandler = (e) => {
                                            if (e.key === "Enter") {
                                                e.preventDefault();
                                                checkbox.checked = !checkbox.checked;

                                                // Déclenche l'événement "change" si nécessaire
                                                checkbox.dispatchEvent(new Event("change", { bubbles: true }));
                                                checkbox.removeEventListener("keydown", checkboxKeyHandler);
                                            }
                                        };

                                        checkbox.addEventListener("keydown", checkboxKeyHandler, { once: true });
                                    }, 100);
                                }

                                attachLabel.removeEventListener("keydown", keyHandler);
                            }
                        };

                        attachLabel.addEventListener("keydown", keyHandler, { once: true });

                        attachLabel.style.outline = "2px solid #007bff";
                        setTimeout(() => {
                            attachLabel.style.outline = "";
                        }, 1500);

                        // Ajout de l'événement onchange pour déplacer le focus vers l'icône après sélection de fichier
                        const input = document.querySelector('input[type="file"]');
                        input.onchange = function(event) {
                            const file = event.target.files[0];
                            if (file) {
                                // ... (code existant pour uploader le fichier)

                                // Déplacer le focus vers l'icône après un délai
                                setTimeout(() => {
                                    if (eyeIcon) {
                                        eyeIcon.setAttribute("tabindex", "0");
                                        eyeIcon.focus();
                                    }
                                }, 100); // ajout d'un délai de 100ms
                            }
                        };

                        // Ajout de l'événement keydown pour cocher la checkbox si le focus est sur celle-ci et que l'on appuie sur Entrée
                        checkbox.addEventListener("keydown", (e) => {
                            if (e.key === "Enter") {
                                e.preventDefault();
                                checkbox.checked = true;
                            }
                        });

                        return;
                    }
                }

                setTimeout(() => {
                    focusNextEditableCell(cell);
                }, 50);
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

    // input.addEventListener("blur", function() {
    //     validateAndCommit();
    // });

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
    function validateAndCommit1() {
        // Vous pouvez ajouter des validations supplémentaires si besoin
        success(input.value);
    }

    // Lors du blur, valider la saisie
    input.addEventListener("blur", function() {
        validateAndCommit1();
    });

    // Intercepter la touche Entrée pour valider et naviguer
    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            validateAndCommit1();
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
    var annee = $('#year-select').val(); // Utilise le bon champ pour l’année
    var journalCode = document.getElementById('journal-select').value;

    // Si un objet soldeMensuel est disponible pour le mois/année en cours
    if (typeof soldeMensuel !== 'undefined' && soldeMensuel.cloturer === 1) {
        Swal.fire({
            title: 'Mois clôturé',
            text: 'Vous ne pouvez pas supprimer une transaction car le mois est clôturé.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Vérifie via la fonction aussi (double sécurité)
    if (isMonthClosed(mois, annee, journalCode)) {
        Swal.fire({
            title: 'Alerte',
            text: 'Le mois est déjà clôturé. Vous ne pouvez pas supprimer des transactions.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    // AJAX pour supprimer
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
                updateTotals(mois, annee);
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
 function deleteSelectedRows() {
    var selectedRows = table.getSelectedRows();

    if (selectedRows.length === 0) {
        alert("Aucune ligne sélectionnée !");
        return;
    }

    // Vérification du statut de clôture avant même de confirmer
    if (typeof soldeMensuel !== 'undefined' && soldeMensuel.cloturer === 1) {
        Swal.fire({
            title: 'Mois clôturé',
            text: 'Vous ne pouvez pas supprimer des transactions sur un mois clôturé.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (confirm("Êtes-vous sûr de vouloir supprimer les lignes sélectionnées ?")) {
        selectedRows.forEach(function(row) {
            var rowData = row.getData();
            deleteTransaction(rowData.id);
        });
    }
}

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

                     const messageDiv = document.createElement('div');
        messageDiv.textContent = "📎 Fichier attaché avec succès.";
        Object.assign(messageDiv.style, {
            position: 'fixed',
            top: '150px',
            left: '70%',
            transform: 'translateX(-50%)',
            backgroundColor: '#4CAF50',
            color: 'white',
            padding: '10px 20px',
            borderRadius: '5px',
            zIndex: 9999
        });
        document.body.appendChild(messageDiv);
        setTimeout(() => location.reload(), 2000);
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


function getUserNameById(userId) {
    if (!userId) return "N/A";
    const user = Users.find(u => u.id === userId);

    return user ? user.name : "N/A";
}

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

function exporterPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // ─── 1) Récupération du formulaire ────────────────────────────────────────────
    const codeJournal = document.getElementById("journal-select").value;
    const intitule    = document.getElementById("intitule-input").value;
    const month       = document.getElementById("month-select").value;
    const year        = document.getElementById("year-select").value;
    const moisText    = document.querySelector("#month-select option:checked").textContent;
    const periodeText = `${moisText}`; // Mois + Année 
    // ─── Infos société ───────────────────────────────────────────────────────────────
const societeRaisonSociale     = document.getElementById('session-data')?.getAttribute('data-societe-raison_sociale') || "";
const societeFormeJuridique    = document.getElementById('session-data1')?.getAttribute('data-societe-forme_juridique') || "";
const societeIdentifiantFiscal = document.getElementById('session-data2')?.getAttribute('data-societe-identifiant_fiscal') || "";

    // ─── 2) Vérification de clôture ───────────────────────────────────────────────
    const closureInfo = isMonthClosedpdf(month, year, codeJournal);
    if (!closureInfo.closed) {
    const messageDiv = document.createElement('div');
    messageDiv.textContent = "Veuillez clôturer la période " + periodeText + " avant de l'exporter.";
    Object.assign(messageDiv.style, {
        position: 'fixed',
        top: '150px',
        left: '50%',
        transform: 'translateX(-50%)',
        backgroundColor: '#f44336', // rouge pour l'avertissement
        color: 'white',
        padding: '10px 20px',
        borderRadius: '5px',
        zIndex: 9999,
        fontFamily: 'Arial, sans-serif',
        boxShadow: '0 2px 6px rgba(0,0,0,0.3)'
    });
    document.body.appendChild(messageDiv);

    // Le message disparaît au bout de 2 secondes
    setTimeout(() => {
        messageDiv.remove();
    }, 2000);
            return;
        }

    // ─── 3) Solde initial & final ─────────────────────────────────────────────────
    const soldeInitial = Number(document.getElementById("initial-balance").value || 0).toFixed(2);
    const soldeFinal   = Number(document.getElementById("final-balance").value || 0).toFixed(2);

    // ─── 4) Récupération des transactions & calcul des totaux ────────────────────
    const transactionRows = table.getData();
    let totalRecette = 0;
    let totalDepense = 0;
    let lastUpdatedBy = "";

    const rows = transactionRows.map(row => {
        const rc = row.recette ? parseFloat(row.recette) : 0;
        const dp = row.depense ? parseFloat(row.depense) : 0;
        totalRecette += rc;
        totalDepense += dp;

        const updatedByName = row.updated_by_name || '';
        lastUpdatedBy = updatedByName;

        return [
            row.day || '',
            row.libelle || '',
            rc ? rc.toFixed(2) : '',
            dp ? dp.toFixed(2) : '',
            row.ref || ''
        ];
    });

    // Ligne TOTAL (span sur Jour+Libellé)
    rows.push([
        { content: "TOTAL", colSpan: 2, styles: { halign: "right", fontStyle: "bold" } },
        { content: totalRecette.toFixed(2), styles: { fontStyle: "bold" } },
        { content: totalDepense.toFixed(2), styles: { fontStyle: "bold" } },
        ""
    ]);
// ─── 5) EN-TÊTE BLEU ──────────────────────────────────────────────────────────
const pageWidth  = doc.internal.pageSize.getWidth();
const pageHeight = doc.internal.pageSize.getHeight();
doc.setFillColor(41, 128, 185);
doc.rect(0, 0, pageWidth, 30, 'F');

// 5.1) Ligne 1 : Raison sociale + Forme juridique
const societeHeaderLine = `${societeRaisonSociale}   ${societeFormeJuridique}`;
doc.setFontSize(9).setTextColor(255).setFont("helvetica", "bold");
doc.text(societeHeaderLine, 10, 10); // y = 10

// 5.2) Ligne 2 : IF à gauche, Titre centré
// IF à gauche
doc.setFontSize(9).setTextColor(255).setFont("helvetica", "normal");
doc.text(`IF : ${societeIdentifiantFiscal}`, 10, 16); // gauche

// Titre centré
const title = "État de Caisse Mensuel";
doc.setFontSize(14).setFont("helvetica", "bold");
const titleWidth = doc.getTextWidth(title);
doc.text(title, (pageWidth - titleWidth) / 2, 16); // même y que IF, centré

// 5.3) Ligne 3 : Code / Intitulé / Période
const yHeader = 26;
const third   = pageWidth / 3;
doc.setFontSize(10).setTextColor(255);
doc.text(`Code : ${codeJournal}`, 10, yHeader);
doc.text(`Intitulé : ${intitule}`, third + 10, yHeader);
doc.text(`Période : ${periodeText}`, 2 * third + 10, yHeader);


    // ─── 6) Affichage du Solde initial juste SOUS l’en-tête (à droite, aligné)
    const ySoldeInitial =  thirtyBelowHeader();
    doc.setFontSize(11).setTextColor(0);
    doc.text(`Solde initial : ${soldeInitial} MAD`, pageWidth -69, ySoldeInitial, { align: "right" });

    // ─── 7) Dessin du tableau (autoTable) à y = 40 ───────────────────────────────
    // startYTable = 40 fixe pour laisser 10 pts après l’en-tête
    const startYTable = 40;
    doc.autoTable({
        startY: startYTable,
        head: [[ "Jour", "Libellé", "Recette", "Dépense", "N° Pièce" ]],
        body: rows,
        styles: {
            fontSize: 10,
            halign: "center",
            cellPadding: 2
        },
        headStyles: {
            fillColor: [41, 128, 185],
            textColor: 255,
            fontStyle: "bold"
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245]
        },
        columnStyles: {
            0: { cellWidth: 14 },   // “Jour” réduit à 10 pts
            1: { cellWidth: 80 },   // “Libellé” = 80 pts
            2: { cellWidth: 25 },   // “Recette” = 25 pts
            3: { cellWidth: 25 },   // “Dépense” = 25 pts
            4: { cellWidth: 35 }    // “N° Pièce” = 35 pts
        },
        didDrawPage: function(data) {
            // Numérotation de la page en bas à droite
            const pageNumber = doc.internal.getCurrentPageInfo().pageNumber;
            doc.setFontSize(9).setTextColor(150);
            doc.text(`Page ${pageNumber}`, pageWidth - 20, pageHeight - 10);
        }
    });

    // ─── 8) Positionnement du “Solde final” juste SOUS le tableau (à droite)
    const soldeFinalY = doc.lastAutoTable.finalY + 6; // 6 pts sous la dernière ligne du tableau
    doc.setFontSize(11).setTextColor(0);
    doc.text(`Solde final : ${soldeFinal} MAD`, pageWidth -69, soldeFinalY, { align: "right" });

    // ─── 9) “Clôturé le” et “Fait par” à l’extrême droite, UNE SEULE LIGNE ENTRE EUX ──
    const xFooter      = pageWidth - 14;              // aligné à 14 pts de la marge droite
    const yFooterClos  = soldeFinalY + 14;            // 14 pts sous “Solde final”
    const yFooterFait  = yFooterClos + 6;             // 6 pts sous “Clôturé le”
    if (closureInfo.closureDate) {
        const closureDateFormatted = new Date(closureInfo.closureDate).toLocaleDateString('fr-FR');
        doc.setTextColor(0);
        doc.text(`Clôturé le : ${closureDateFormatted}`, xFooter, yFooterClos, { align: "right" });
    }
    doc.setFontSize(10).setTextColor(0);
    doc.text(`Fait par : ${lastUpdatedBy}`, xFooter, yFooterFait, { align: "right" });

    // ─── 10) Sauvegarde du PDF ────────────────────────────────────────────────────
    doc.save(`etat_caisse_${month}_${year}.pdf`);


    // ─── Fonction utilitaire pour calculer la position Y du Solde initial ───────
    function thirtyBelowHeader() {
        // On veut 10 pts sous l’en-tête bleu (yHeader = 18, rectangle bleu = 30).
        // Donc on peut fixer à y = 36 (30 + 6) pour un petit espace.
        return 36;
    }
}

function exportToExcel() {
    const codeJournal = document.getElementById("journal-select").value;
    const intitule    = document.getElementById("intitule-input").value;
    const month       = document.getElementById("month-select").value;
    const year        = document.getElementById("year-select").value;
    const moisText    = document.querySelector("#month-select option:checked").textContent;
    const periodeText = `${moisText}`; // Mois + Année

    // ─── 2) Vérification de clôture ───────────────────────────────────────────────
    const closureInfo = isMonthClosedpdf(month, year, codeJournal);
    if (!closureInfo.closed) {
const messageDiv = document.createElement('div');
messageDiv.textContent = "Veuillez clôturer la période " + periodeText + " avant de l'exporter.";
Object.assign(messageDiv.style, {
    position: 'fixed',
    top: '150px',
    left: '50%',
    transform: 'translateX(-50%)',
    backgroundColor: '#f44336', // rouge pour l'avertissement
    color: 'white',
    padding: '10px 20px',
    borderRadius: '5px',
    zIndex: 9999,
    fontFamily: 'Arial, sans-serif',
    boxShadow: '0 2px 6px rgba(0,0,0,0.3)'
});
document.body.appendChild(messageDiv);

// Le message disparaît au bout de 2 secondes
setTimeout(() => {
    messageDiv.remove();
}, 2000);
        return;
    }
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

function updateShareIconVisibility() {
    var cloturerButton = document.getElementById('cloturer-button');
    var shareIcon = document.querySelector('.fa-share');

    if (cloturerButton.disabled) {
        shareIcon.classList.remove('hidden');
     } else {
        shareIcon.classList.add('hidden');     }
}

function formatNumber(cell) {
    const value = cell.getValue();
    if (value === null || value === undefined) return '';

    const number = parseFloat(value);
    if (isNaN(number)) return '';

    const fixed = number.toFixed(2);
    const parts = fixed.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    return parts.join(',');
}




