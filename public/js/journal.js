// public/js/journal.js

// Initialiser le tableau
var table = new Tabulator("#journal-table", {
    ajaxURL: "/journaux/data", // URL pour récupérer les données
    height: "600px", // Hauteur du tableau pour activer le défilement vertical
    layout: "fitColumns",
    columns: [
        { title: "Code Journal", field: "code_journal", editor: "input", headerFilter: "input" },
        { title: "Type Journal", field: "type_journal", editor: "input", headerFilter: "input" },
        { title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input" },
        { title: "Contre Partie", field: "contre_partie", editor: "input", headerFilter: "input" },
        {
            title: "Actions",
            field: "action-icons",
            formatter: function () {
                return `
                    <i class='fas fa-edit text-primary edit-icon' style='cursor: pointer;'></i>
                    <i class='fas fa-trash-alt text-danger delete-icon' style='cursor: pointer;'></i>
                `;
            },
            cellClick: function (e, cell) {
                if (e.target.classList.contains('edit-icon')) {
                    var rowData = cell.getRow().getData();
                    editJournal(rowData);
                } else if (e.target.classList.contains('delete-icon')) {
                    var rowData = cell.getRow().getData();
                    deleteJournal(rowData.id);
                }
            },
        }
    ],
});

// Fonction d'édition de journal
function editJournal(rowData) {
    $('#journal_id').val(rowData.id);
    $('#code_journal').val(rowData.code_journal);
    $('#type_journal').val(rowData.type_journal);
    $('#intitule').val(rowData.intitule);
    $('#contre_partie').val(rowData.contre_partie);
    $('#modifierJournalModal').modal('show');
}

// Fonction de suppression de journal
function deleteJournal(journalId) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce journal ?")) {
        $.ajax({
            url: `/journaux/${journalId}`,
            type: 'DELETE',
            success: function () {
                table.replaceData('/journaux/data'); // Remplace les données de la table
            },
            error: function (xhr) {
                alert('Erreur lors de la suppression : ' + xhr.responseJSON.message);
            }
        });
    }
}

// Ajout d'un nouvel journal
$('#ajouterJournalForm').on('submit', function (e) {
    e.preventDefault(); // Empêche le rechargement de la page
    // URL pour l'ajout d'un journal
    var url = "/journaux";

    $.ajax({
        url: url,
        type: "POST",
        data: {
            code_journal: $("#code_journal").val(),
            type_journal: $("#type_journal").val(),
            intitule: $("#intitule").val(),
            contre_partie: $("#contre_partie").val(),
            _token: $('meta[name="csrf-token"]').attr('content') // Assurez-vous d'inclure votre CSRF token
        },
        success: function (response) {
            table.setData("/journaux/data"); // Recharger les données dans Tabulator
            $("#ajouterJournalModal").modal("hide"); // Fermer le modal d'ajout
            $("#ajouterJournalForm")[0].reset(); // Réinitialiser le formulaire d'ajout
            alert("Journal ajouté avec succès !"); // Afficher un message de succès
        },
        error: function (xhr) {
            console.error("Erreur lors de l'enregistrement des données :", xhr.responseText);
            alert("Erreur lors de l'enregistrement des données !");
        }
    });
});

// Modification d'un journal
$('#modifierJournalForm').on('submit', function (e) {
    e.preventDefault(); // Empêche le rechargement de la page
    var formData = $(this).serialize(); // Récupère les données du formulaire
    var journalId = $('#journal_id').val(); // Assurez-vous que cet ID est bien présent dans le formulaire

    $.ajax({
        url: `/journaux/${journalId}`, // URL pour la mise à jour du journal
        type: 'PUT', // Type de requête
        data: formData, // Données à envoyer
        success: function () {
            table.replaceData('/journaux/data'); // Remplace les données de la table
            $('#modifierJournalModal').modal('hide'); // Ferme le modal
            alert("Journal modifié avec succès !"); // Affiche un message de succès
        },
        error: function (jqXHR) {
            // Affiche un message d'erreur si la modification échoue
            alert("Erreur lors de la modification du journal : " + jqXHR.responseJSON.message);
        }
    });
});

// Importation de journaux
$('#importerJournalForm').on('submit', function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: '/journaux/import',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function () {
            table.reload();
            $('#importerJournalModal').modal('hide');
            alert("Journaux importés avec succès !");
        },
        error: function (xhr) {
            alert("Erreur lors de l'importation : " + xhr.responseJSON.message);
        }
    });
});
