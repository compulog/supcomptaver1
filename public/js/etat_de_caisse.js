// Fonction pour sélectionner toutes les lignes et cocher toutes les cases
function selectAllRows() {
    // Sélectionner toutes les lignes
    table.selectRow();
    
    // Cocher toutes les cases à cocher
    table.getRows().forEach(function(row) {
        var checkbox = row.getCell("actions").getElement().querySelector("input[type='checkbox']");
        if (checkbox) {
            checkbox.checked = true; // Cocher la case
        }
    });
}


    $(document).on('click', '.edit-etat-caisse', function() {
        var caisseId = $(this).data('id');
        console.log("ID de la transaction:", caisseId);

        if (!caisseId) {
            console.error("L'ID de la transaction est manquant");
            return;
        }

        $.ajax({
            url: '/etat-caisse/' + caisseId + '/edit',
            method: 'GET',
            success: function(data) {
                $('#etat_de_caisse [name="day"]').val(new Date(data.date).getDate());
                $('#etat_de_caisse [name="Nreference"]').val(data.reference);
                $('#etat_de_caisse [name="Libellé"]').val(data.libelle);
                $('#etat_de_caisse [name="Recette"]').val(data.recette);
                $('#etat_de_caisse [name="Depense"]').val(data.depense);
                $('#etat_de_caisse').attr('action', '/update-transaction/' + caisseId);
                $('#editcaisseModal').modal('show');
            },
            error: function(xhr) {
                console.error('Erreur lors de la récupération des données :', xhr);
                alert('Erreur lors de la récupération des données de l\'état de caisse.');
            }
        });
    });

    function deleteTransaction(transactionId) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cette transaction ?")) {
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
                        updateTotals($('#month-select').val(), $('input[type="text"]').val());
                    } else {
                        console.error("Erreur lors de la suppression : " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erreur lors de la suppression :", error);
                }
            });
        } else {
            console.log("Suppression annulée.");
        }
        location.reload();
    }

    // Fonction pour sélectionner ou désélectionner toutes les lignes
    function toggleSelectAll() {
        var allRows = table.getRows();
        var allSelected = allRows.every(row => row.getSelected());

        if (allSelected) {
            table.deselectRow();
        } else {
            table.selectRow();
        }
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


    function saveData() {
        var mois = $('#month-select').val();
        var soldeInitial = parseFloat($('#initial-balance').val() || 0);
        var totalRecette = parseFloat($('#total-revenue').val() || 0);
        var totalDepense = parseFloat($('#total-expense').val() || 0);
        var soldeFinal = parseFloat($('#final-balance').val() || 0);
        var year = $('input[type="text"]').val();
        var date = new Date(year + '-' + mois + '-01');
        var journalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné
        console.log(journalCode);
        $.ajax({
            url: '/save-solde',
            method: 'POST',
            data: {
                mois: mois,
                annee: year,
                solde_initial: soldeInitial,
                total_recette: totalRecette,
                total_depense: totalDepense,
                solde_final: soldeFinal,
                journal_code: journalCode ,

                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
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
}

    $('#initial-balance').on('input', function() {
        updateTotals($('#month-select').val(), $('input[type="text"]').val());
        saveData();
    });

    $(document).ready(function() {
        var currentMonth = $('#month-select').val();
        var currentYear = $('input[type="text"]').val();
        updateTableData(currentMonth, currentYear);
    });

    $('#month-select, #year-select').on('change', function() {
        var currentMonth = $('#month-select').val();
        var currentYear = $('input[type="text"]').val();
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
                }
            });
        }
    }

    // Mettre à jour les totaux après modification
    updateTotals($('#month-select').val(), $('input[type="text"]').val());
});
// Événement pour enregistrer les données lors de l'appui sur "Entrée"
$('#example-table').on('keydown', function(e) {
    if (e.key === "Enter") {
        var selectedRows = table.getSelectedRows();
        if (selectedRows.length > 0) {
            var rowData = selectedRows[0].getData();
            var selectedMonth = $('#month-select').val();
            var selectedYear = $('input[type="text"]').val();
            var formattedDate = selectedYear + '-' + selectedMonth + '-' + rowData.day.padStart(2, '0');
            var journalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné

            // Vérifier si userResponse est vide et le remplacer par 0
            var userResponseToSend = userResponse ? userResponse : 0;
            if (isMonthClosed(selectedMonth, selectedYear, journalCode)) {
        alert("Le mois est déjà clôturé. Vous ne pouvez pas modifier des transactions.");
        return; // Sortir de la fonction si le mois est clôturé
    }else{
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
                    user_response: userResponseToSend // Envoyer la réponse de l'utilisateur ou 0
                },
                success: function(response) {
                    console.log("Données envoyées avec succès :", response);
                    // location.reload();
                },
                error: function(xhr, status, error) {
                    console.error("Erreur lors de l'envoi des données :", error);
                    console.log(xhr.responseText);
                }
            });}
        } else {
            console.log("Aucune ligne sélectionnée !");
        }
        location.reload();
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
});
document.addEventListener('DOMContentLoaded', function() {
    var selectedMonth = document.getElementById('month-select').value;
    var selectedYear = document.getElementById('year-select').value;
    var selectedJournalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné
    filterSoldeInitial(selectedMonth, selectedYear, selectedJournalCode); // Appeler la fonction avec le code journal
});



document.getElementById('cloturer-button').addEventListener('click', function() {
    var mois = $('#month-select').val();
    var annee = $('input[type="text"]').val();
    var journalCode = document.getElementById('journal-select').value;

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
        },
        error: function(xhr, status, error) {
            console.error("Erreur lors de la clôture :", error);
            alert('Erreur lors de la clôture du solde.');
        }
    });
});


document.getElementById('export-excel-icon').addEventListener('click', exportToExcel);
function filterSoldeInitial(month, year, journalCode) {
    var soldeMensuel = soldesMensuels.find(function(solde) {
        var moisComparaison = parseInt(solde.mois).toString().padStart(2, '0');
        var anneeComparaison = parseInt(solde.annee).toString();
        return moisComparaison === month && anneeComparaison === year && solde.code_journal === journalCode; // Filtrer par code journal
    });

    if (soldeMensuel) {
        // Vérifiez si le solde est clôturé
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

        // Afficher le solde initial
        document.getElementById('initial-balance').value = soldeMensuel.solde_initial;
    } else {
        document.getElementById('initial-balance').value = 0;
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
});
