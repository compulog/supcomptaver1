


function openEditFolderModal(dossierId, dossierName) {
    // Mettre à jour le champ du formulaire avec le nom du dossier
    document.getElementById('folderName').value = dossierName;

    // Mettre à jour l'ID du dossier dans le formulaire
    document.getElementById('dossier_id').value = dossierId;

    // Mettre à jour l'action du formulaire pour qu'il pointe vers la bonne URL (route pour PUT)
    document.getElementById('edit-folder-form').action = `/dossier/${dossierId}`;

    // Afficher le modal de modification
    var myModal = new bootstrap.Modal(document.getElementById('editFolderModal'));
    myModal.show();
}










    // Fonction pour générer une couleur hexadécimale aléatoire
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    // Appliquer la couleur aléatoire à chaque "dossier-box" et bouton à l'intérieur
    document.querySelectorAll('.dossier-box').forEach(function(div) {
        const color = getRandomColor();
        div.style.backgroundColor = color;

        // Appliquer la même couleur au bouton
        const button = div.querySelector('.dossier-button');
        if (button) {
            button.style.backgroundColor = color;

        }
    });

    function handleFileSelect(event, dossierId) {
    const fileInput = document.getElementById(`file-${dossierId}`);
    const formId = `form-${dossierId}`;  // Générer l'ID du formulaire

    if (!fileInput.files.length) {
        alert("Veuillez sélectionner un fichier.");
        return;
    }

    // Soumettre le formulaire si un fichier est sélectionné
    document.getElementById(formId).submit();
}



