
// Fonction pour gérer la navigation entre les champs avec la touche Entrée
function setupEnterNavigation(formId) {
    document.getElementById(formId).addEventListener('keypress', function(event) {
        // Vérifie si la touche appuyée est "Entrée"
        if (event.key === 'Enter') {
            event.preventDefault(); // Empêche le comportement par défaut du bouton Entrée
            
            // Récupère tous les éléments INPUT et SELECT du formulaire
            const inputs = Array.from(this.elements).filter(el => el.tagName === 'INPUT' || el.tagName === 'SELECT');
            
            // Trouve l'index de l'élément actuellement actif
            const currentIndex = inputs.indexOf(document.activeElement);
            
            // Si l'élément actuel n'est pas le dernier, passe au champ suivant
            if (currentIndex < inputs.length - 1) {
                inputs[currentIndex + 1].focus(); // Passe au champ suivant
            } else {
                // Si c'est le dernier champ, vous pouvez éventuellement soumettre le formulaire ici
                // this.submit(); // Décommentez cette ligne si vous voulez soumettre le formulaire après le dernier champ
            }
        }
    });
}


    // Initialiser le tableau
    var table = new Tabulator("#plan-comptable-table", {
        ajaxURL: "/plancomptable/data", // URL pour récupérer les données
        height: "600px", // Hauteur du tableau pour activer le défilement vertical
        layout: "fitColumns",
        columns: [
            {title: "Compte", field: "compte", editor: "input", headerFilter: "input"},
            {title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input"},
            {
                title: "Actions",
                field: "action-icons",
                formatter: function() {
                    return `
                        <i class='fas fa-edit text-primary edit-icon' style='cursor: pointer;'></i>
                        <i class='fas fa-trash-alt text-danger delete-icon' style='cursor: pointer;'></i>
                    `;
                },
                cellClick: function(e, cell) {
                    if (e.target.classList.contains('edit-icon')) {
                        var rowData = cell.getRow().getData();
                        editPlanComptable(rowData);
                    } else if (e.target.classList.contains('delete-icon')) {
                        var rowData = cell.getRow().getData();
                        deletePlanComptable(rowData.id);
                    }
                },
            }
        ],
    });

    
    // Fonction pour gérer l'ajout des plans comptables
    $("#planComptableFormAdd").on("submit", function(e) {
        e.preventDefault();

        $.ajax({
            url: "/plancomptable",
            type: "POST",
            data: {
                compte: $("#compte").val(),
                intitule: $("#intitule").val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                table.setData("/plancomptable/data");
                $("#planComptableModalAdd").modal("hide");
                $("#planComptableFormAdd")[0].reset();
              
            },
            error: function(xhr) {
                alert("Erreur lors de l'enregistrement des données !");
            }
        });
        
    });

    // Fonction pour gérer la modification des plans comptables
    $("#planComptableFormEdit").on("submit", function(e) {
        e.preventDefault();

        var planComptableId = $("#editPlanComptableId").val();
        $.ajax({
            url: "/plancomptable/" + planComptableId,
            type: "PUT",
            data: {
                compte: $("#editCompte").val(),
                intitule: $("#editIntitule").val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                table.setData("/plancomptable/data");
                $("#planComptableModalEdit").modal("hide");
                
                $("#planComptableFormEdit")[0].reset();
                $("#editPlanComptableId").val("");
            },
            error: function(xhr) {
                alert("Erreur lors de l'enregistrement des données !");
            }
        });
    });

    // Fonction pour remplir le formulaire pour la modification
    function editPlanComptable(data) {
        $("#editPlanComptableId").val(data.id);
        $("#editCompte").val(data.compte);
        $("#editIntitule").val(data.intitule);
        $("#planComptableModalEdit").modal("show");
    }


    // Fonction pour supprimer un plan comptable
function deletePlanComptable(id) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce plan comptable ?")) {
        $.ajax({
            url: "/plancomptable/" + id,
            type: "DELETE",
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Recharger les données pour refléter la suppression
                table.setData("/plancomptable/data"); 
                alert("Plan comptable supprimé avec succès !");
            },
            error: function(xhr) {
                alert("Erreur lors de la suppression du plan comptable. Veuillez réessayer.");
            }
        });
    }
}

$("#importForm").on("submit", function(e) {
    e.preventDefault();

    $.ajax({
        url: "{{ route('plancomptable.import') }}",
        type: "POST",
        data: new FormData(this), // Envoi du formulaire avec le fichier
        processData: false,
        contentType: false,
        success: function(response) {
            table.setData("/plancomptable/data"); // Recharger les données du tableau
            $("#importModal").modal("hide");
            alert(response.message); // Affiche le message de succès
        },
        error: function(xhr) {
            let errorMessage = 'Erreur lors de l\'importation des données !';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message; // Message d'erreur du backend
            }
            alert(errorMessage);
        }
    });
});


//excel
// Ouvrir le modal d'importation
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const reader = new FileReader();

    reader.onload = function(event) {
        const data = new Uint8Array(event.target.result);
        const workbook = XLSX.read(data, { type: 'array' });

        const sheetName = workbook.SheetNames[0]; // Prendre la première feuille
        const worksheet = workbook.Sheets[sheetName];
        const headers = XLSX.utils.sheet_to_json(worksheet, { header: 1 })[0]; // Obtenir les en-têtes de colonnes

        // Remplir les sélecteurs de colonnes
        const compteSelect = document.querySelector('select[name="colonne_compte"]');
        const intituleSelect = document.querySelector('select[name="colonne_intitule"]');
        
        compteSelect.innerHTML = ''; // Vider les options existantes
        intituleSelect.innerHTML = ''; // Vider les options existantes

        headers.forEach((header, index) => {
            // Ajouter l'option pour le champ "compte"
            if (header.toLowerCase().includes('compte')) { // Vérifier si le header contient "compte"
                const optionCompte = new Option(header, index);
                compteSelect.add(optionCompte);
            }

            // Ajouter l'option pour le champ "intitule"
            if (header.toLowerCase().includes('intitule')) { // Vérifier si le header contient "intitule"
                const optionIntitule = new Option(header, index);
                intituleSelect.add(optionIntitule);
            }
        });
    };

    reader.readAsArrayBuffer(file);
});
