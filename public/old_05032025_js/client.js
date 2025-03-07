    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-saisie-manuel'); // Sélectionner le formulaire
        const inputs = form.querySelectorAll('input, select'); // Sélectionner tous les champs de formulaire (input et select)

        // Écouter l'événement 'keydown' pour chaque champ du formulaire
        inputs.forEach((input, index) => {
            input.addEventListener('keydown', function(event) {
                // Si la touche appuyée est 'Enter' (code 13)
                if (event.key === 'Enter') {
                    // Si c'est le dernier champ, soumettre le formulaire
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus(); // Passer au champ suivant
                    } else {
                        form.submit(); // Soumettre le formulaire si c'est le dernier champ
                    }
                    event.preventDefault(); // Empêcher le comportement par défaut de la touche 'Enter'
                }
            });
        });
    });
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('form input:not([type="file"]), form textarea');

    inputs.forEach((input, index) => {
        input.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Empêche le formulaire de se soumettre
                // Si c'est le dernier champ, soumettre le formulaire
                if (!inputs[index + 1]) {
                    this.form.submit();
                } else {
                    // Focus sur le champ suivant
                    inputs[index + 1].focus();
                }
            }
        });
    });
});
$(document).ready(function() {
    // Événement pour le clic sur le bouton d'édition
    $(document).on('click', '.edit-client', function() {
        var clientId = $(this).data('id');

        // Requête AJAX pour récupérer les données du client
        $.ajax({
            url: '/clients/' + clientId + '/edit',
            method: 'GET',
            success: function(data) {
                // Remplir le formulaire dans le pop-up avec les données
                $('#clientForm [name="compte"]').val(data.compte);
                $('#clientForm [name="intitule"]').val(data.intitule);
                $('#clientForm [name="identifiant_fiscal"]').val(data.identifiant_fiscal);
                $('#clientForm [name="ICE"]').val(data.ICE);
                // Remplir d'autres champs si nécessaire

                // Mettre à jour l'URL d'action du formulaire pour la modification
                $('#clientForm').attr('action', '/clients/' + clientId);

                // Afficher le pop-up
                $('#editClientModal').modal('show');
            },
            error: function(xhr) {
                console.error('Erreur lors de la récupération des données :', xhr);
                alert('Erreur lors de la récupération des données du client.');
            }
        });
    });

    // Événement pour la soumission du formulaire de modification
    $('#clientForm').on('submit', function(event) {
        event.preventDefault(); // Empêche le comportement par défaut du formulaire

        // Appel AJAX pour modifier le client
        $.ajax({
            url: $(this).attr('action'), // Utiliser l'URL définie précédemment
            method: 'PUT', // Assurez-vous que votre méthode est correcte (PUT pour modification)
            data: $(this).serialize(), // Sérialiser les données du formulaire
            success: function(data) {
                // Afficher un message de succès
                alert("Client modifié avec succès !");

                // Mettre à jour la ligne correspondante dans le tableau Tabulator
                var updatedClient = {
                    id: data.client.id, // ID du client
                    compte: $('#clientForm [name="compte"]').val(), // Nouveau compte
                    intitule: $('#clientForm [name="intitule"]').val(), // Nouveau intitulé
                    identifiant_fiscal: $('#clientForm [name="identifiant_fiscal"]').val(), // Nouvel identifiant fiscal
                    ICE: $('#clientForm [name="ICE"]').val(), // Nouvel ICE
                    type_client: data.client.type_client // Garder le type client de la réponse
                };

                // Supposons que votre tableau Tabulator est stocké dans une variable appelée "table"
                table.updateOrAddData([updatedClient]); // Mettre à jour la ligne correspondante

                // Fermer le modal
                $('#editClientModal').modal('hide');
            },
            error: function(xhr) {
                console.error('Erreur lors de la modification du client :', xhr);
                alert("Erreur lors de la modification du client !");
            }
        });
    });
});


 

document.getElementById('form-import-excel').addEventListener('submit', function(e) {
    e.preventDefault();

    let formData = new FormData(this); // FormData va automatiquement inclure tous les champs du formulaire, y compris 'societe_id' et 'mapping'

    fetch("{{ route('import.clients') }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Fichier importé avec succès!');
            // Actions supplémentaires si nécessaire
        } else {
            alert('Erreur lors de l\'importation.');
        }
    })
    .catch(error => console.log(error));
});

    $.ajax({
    url: '/clients/get', // Assure-toi que cette route est correcte
    method: 'GET',
    success: function(data) {
        // Met à jour Tabulator avec les nouvelles données
        table.setData(data);
    },
    error: function(err) {
        console.error('Erreur lors de la récupération des données:', err);
    }
});
console.log("Societe ID: ", societeId); // Ajoutez un log pour vérifier si l'ID est bien récupéré

document.getElementById('form-saisie-manuel').onsubmit = function(event) {
    event.preventDefault(); // Empêche le rechargement de la page

    const data = new FormData(this); // Récupère les données du formulaire

    // Ajouter l'ID de la société aux données
    if (societeId) {
        console.log("Ajout de societe_id aux données");
        data.append('societe_id', societeId); // Ajouter societe_id dans les données du formulaire
    } else {
        console.log("Pas de societeId dans la session");
    }

    // Vérifier si les champs "identifiant_fiscal" et "ICE" sont vides
    const identifiantFiscal = data.get('identifiant_fiscal');
    const ICE = data.get('ICE');

    // Si vide, attribuer la valeur 'Null'
    if (!identifiantFiscal) {
        console.log("Identifiant Fiscal vide, valeur par défaut : Null");
        data.set('identifiant_fiscal', 'Null');
    }

    if (!ICE) {
        console.log("ICE vide, valeur par défaut : Null");
        data.set('ICE', 'Null');
    }

    // Récupérer tous les comptes existants dans Tabulator
    const comptesExistants = table.getData().map(row => row.compte); // Suppose que "table" est votre instance Tabulator

    // Vérifier si le compte existe déjà dans Tabulator
    const compteEntree = data.get('compte'); // Récupère le compte entré par l'utilisateur
    if (comptesExistants.includes(compteEntree)) {
        // Si le compte existe déjà, afficher un message d'erreur sous forme d'alerte
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger';  // Utilisation de la classe d'alerte Bootstrap
        alertDiv.textContent = 'Le compte ' + compteEntree + ' existe déjà dans le système. Veuillez choisir un autre compte.';

        // Ajouter l'alerte dans la page, vous pouvez ajuster l'endroit où l'alerte est affichée
        const formContainer = document.getElementById('form-saisie-manuel');
        formContainer.insertBefore(alertDiv, formContainer.firstChild); // Affiche l'alerte avant le formulaire

        // Retourner pour empêcher la soumission du formulaire
        return;
    }

    // Si le compte n'existe pas dans Tabulator, soumettre le formulaire
    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: data
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('message');
        if (data.success) {
            messageDiv.classList.remove('d-none');
            table.addRow(data.client); // Ajoute la nouvelle ligne au tableau Tabulator
        } else {
            messageDiv.className = 'alert alert-danger';
            messageDiv.classList.remove('d-none');
        }

        this.reset();
    })
    .catch(error => {
        const messageDiv = document.getElementById('message');
        messageDiv.className = 'alert alert-danger';
        messageDiv.textContent = 'Erreur de connexion : ' + error.message;
        messageDiv.classList.remove('d-none');
        console.error('Erreur:', error);
    });
}



 

   function deleteclients(id) {
    // Demander le mot de passe via un prompt
    var password = prompt("Veuillez entrer votre mot de passe pour confirmer la suppression du client :");

    // Vérifier si un mot de passe a été saisi
    if (password === null || password === "") {
        alert("Mot de passe requis pour confirmer la suppression.");
        return;  // Arrêter le processus si le mot de passe est vide ou annulé
    }

    // Requête AJAX pour vérifier le mot de passe
    fetch('/check-client-password', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ password: password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Si le mot de passe est correct, procéder à la suppression du client
            if (confirm("Êtes-vous sûr de vouloir supprimer ce client ?")) {
                fetch(`{{ url('clients') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const messageDiv = document.getElementById('message');
                    if (data.success) {
                        // Afficher un message de succès
                        messageDiv.className = 'alert alert-success';
                        messageDiv.textContent = 'Client supprimé avec succès !';
                        messageDiv.classList.remove('d-none');

                        // Supprimer le client du tableau Tabulator
                        table.deleteRow(id);
                    } else {
                        // Afficher un message d'erreur
                        messageDiv.className = 'alert alert-danger';
                        messageDiv.textContent = 'Erreur lors de la suppression.';
                        messageDiv.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
            }
        } else {
            // Si le mot de passe est incorrect, afficher un message d'erreur
            alert("Mot de passe incorrect. Vous ne pouvez pas supprimer ce client.");
        }
    })
    .catch(error => {
        console.error("Erreur de vérification du mot de passe :", error);
        alert("Une erreur s'est produite lors de la vérification du mot de passe.");
    });
}



 