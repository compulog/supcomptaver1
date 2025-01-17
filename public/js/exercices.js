

      document.addEventListener('DOMContentLoaded', function () {
        // Ajout des événements de double-clic pour toutes les sections
        document.getElementById('achat-div').addEventListener('dblclick', function () {
            window.location.href = '{{ route("achat.view") }}';
        });

        document.getElementById('vente-div').addEventListener('dblclick', function () {
            window.location.href = '{{ route("vente.view") }}';
        });

        document.getElementById('banque-div').addEventListener('dblclick', function () {
            window.location.href = '{{ route("banque.view") }}';
        });

        document.getElementById('caisse-div').addEventListener('dblclick', function () {
            window.location.href = '{{ route("caisse.view") }}';
        });

        document.getElementById('impot-div').addEventListener('dblclick', function () {
            window.location.href = '{{ route("impot.view") }}';
        });

        document.getElementById('paie-div').addEventListener('dblclick', function () {
            window.location.href = '{{ route("paie.view") }}';
        });
        document.getElementById('Dossier_permanant-div').addEventListener('dblclick', function () {
            window.location.href = '{{ route("Dossier_permanant.view") }}';
        });
    });
    function handleFileSelect(event, type) {
    const fileInput = document.getElementById(`file-${type.toLowerCase()}`);
    const formId = `form-${type.toLowerCase()}`;  // Générer l'ID du formulaire
    
    if (!fileInput.files.length) {
        alert("Veuillez sélectionner un fichier.");
        return;
    }

    // Soumettre le formulaire si un fichier est sélectionné
    document.getElementById(formId).submit();
}


function openCreateFolderForm() {
    var myModal = new bootstrap.Modal(document.getElementById('createFolderModal'));
    myModal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    // Ajout des événements de double-clic pour chaque dossier dynamique
    document.querySelectorAll('.dossier-box').forEach(function(div) {
        div.addEventListener('dblclick', function () {
            // On récupère l'ID du dossier à partir de l'attribut data-id
            const dossierId = div.getAttribute('data-id');
            
            // On redirige vers la route avec l'ID du dossier
            window.location.href = `/Douvrir/${dossierId}`;  // Assurez-vous que la route correspond bien à celle définie dans les routes Laravel
        });
    });
});


 