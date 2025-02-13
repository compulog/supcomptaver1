@extends('layouts.user_type.auth')

@section('content')

<style>
    .input-group .btn-secondary {
    padding: 0.375rem 0.75rem; /* Ajuste le padding horizontal et vertical */
    font-size: 1rem; /* Taille du texte cohérente avec celle de l'input */
    font-weight: 400; /* Poids de police standard */
    color: #6c757d; /* Couleur par défaut du bouton secondaire (qui est la couleur d'origine de btn-secondary) */
    background-color: #e2e6ea; /* Couleur d'arrière-plan du bouton secondaire */
    border-color: #adb5bd; /* Bordure du bouton secondaire */
    border-radius: 0.25rem; /* Coins arrondis pour un look plus moderne */
   height:40px;
}
#compte{
    height:40px
}




</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">






<meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container mt-5">
        <h3>Liste des Clients</h3>

        <!-- Affichage du message de succès ou d'erreur -->
        <div id="message" class="alert d-none" role="alert"></div>

        <div class="mb-3 d-flex align-items-center gap-2 flex-wrap-nowrap">
    <!-- Bouton Créer -->
    <button type="button" id="create-button" class="btn btn-outline-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modal-saisie-manuel" style="color:#007bff; border-color: #007bff;">
        Créer
    </button> 

    <!-- Bouton Importer -->
    <button type="button" id="import-button" class="btn btn-outline-secondary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modal-import-excel">
        Importer
    </button>

    <!-- Formulaire pour exporter les clients -->
    <form action="{{ route('export.clients') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="societe_id" id="societe_id" value="{{ $societe->id }}">
        <button type="submit" class="btn btn-outline-success d-flex align-items-center gap-2">Exporter les clients</button>
    </form>

    <!-- Formulaire pour exporter en PDF -->
    <form action="{{ route('export.clients.pdf') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="societe_id" id="societe_id" value="{{ $societe->id }}">
        <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2">Exporter en PDF</button>
    </form>
</div>


<!-- Modal pour le formulaire d'ajout manuel -->
<div class="modal fade" id="modal-saisie-manuel" tabindex="-1" aria-labelledby="modalSaisieManuelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSaisieManuelLabel">Nouveau Client</h5>
                <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>

            </div>
            <div class="modal-body">
                <form action="{{ route('client.store') }}" method="POST" id="form-saisie-manuel">
                    @csrf

                    <!-- Compte -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="compte" class="form-label">Compte</label>
                                <!-- Options pour choisir entre saisie et auto-incrémentation -->
                                <div class="input-group">
                                    <input type="text" class="form-control" name="compte" id="compte" value="3421" required>
                                    <input type="hidden" name="societe_id" id="societe_id" value="{{ $societe->id }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-secondary" id="auto-increment">Auto</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Intitulé -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="intitule" class="form-label">Intitulé</label>
                            <input type="text" class="form-control" name="intitule" required>
                        </div>
                    </div>

                    <!-- Identifiant Fiscal -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" id="identifiant_fiscal" name="identifiant_fiscal" class="form-control"
                                   pattern="^\d{7,8}$" maxlength="8" title="L'identifiant fiscal doit comporter 7 ou 8 chiffres"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>

                    <!-- ICE -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="ICE" class="form-label">ICE</label>
                            <input type="text" id="ICE" name="ICE" class="form-control"
                                   pattern="^\d{15}$" maxlength="15" title="L'ICE doit comporter exactement 15 chiffres"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>

                    <!-- Type client -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="type_client" class="form-label">Type client</label>
                            <select class="form-control" name="type_client" required>
                                <option value="Null">Choisir une option</option>
                                <option value="5.Entreprise de droit privé">5.Entreprise de droit privé</option>
                                <option value="1.État">1.État</option>
                                <option value="2.Collectivités territoriales">2.Collectivités territoriales</option>
                                <option value="3.Entreprise publique">3.Entreprise publique</option>
                                <option value="4.Autre organisme public">4.Autre organisme public</option>
                            </select>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex justify-content-end">
                        <!-- Bouton Réinitialiser avec marge très grande à droite -->
                        <button type="reset" class="btn btn-secondary me-8">
                            <i class="fas fa-undo"></i>
                        </button>
                        <!-- Bouton Valider avec marge très grande à gauche -->
                        <button type="submit" class="btn btn-primary ms-8" style="background-color:#007bff;">
                            <i class="fas fa-check"></i> Valider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>





  <!-- Modal pour le formulaire d'importation Excel -->
  <div class="modal fade" id="modal-import-excel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalImportExcelLabel">Importer des Clients</h5>
                        <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>
                        </div>
                    <div class="modal-body">
                        <form action="{{ route('import.clients') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="societe_id" id="societe_id" value="{{ $societe->id }}">

                            <div class="mb-3">
                                <label for="file" class="form-label">Fichier Excel :</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                        
                            <div class="mb-3">
                                <label for="compte">Colonne Compte :</label>
                                <input type="number" name="mapping[compte]" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="intitule">Colonne Intitulé :</label>
                                <input type="number" name="mapping[intitule]" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="identifiant_fiscal">Colonne Identifiant Fiscal :</label>
                                <input type="number" name="mapping[identifiant_fiscal]" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="ICE">Colonne ICE :</label>
                                <input type="number" name="mapping[ICE]" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="type_client">Colonne Type Client :</label>
                                <input type="number" name="mapping[type_client]" class="form-control">
                            </div>
                               <!-- Bouton Réinitialiser avec marge très grande à droite -->
                        <button type="reset" class="btn btn-secondary me-8">
                            <i class="fas fa-undo"></i>
                        </button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-arrow-down"></i>
                            Importer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

 

<!-- @foreach($clients as $client)

@endforeach -->
<!-- Modal pour la modification d'un client -->
<div class="modal fade" id="editClientModal" tabindex="-1" role="dialog" aria-labelledby="editClientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="clientForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel">Modifier le Client</h5>
                    <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>

                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="compte">Compte</label>
                        <input type="text" class="form-control" name="compte" required>
                    </div>
                    <div class="form-group">
                        <label for="intitule">Intitulé</label>
                        <input type="text" class="form-control" name="intitule" required>
                    </div>
                    <div class="form-group">
                        <label for="identifiant_fiscal">Identifiant Fiscal</label>
                        <input type="text" class="form-control" name="identifiant_fiscal" required>
                    </div>
                    <div class="form-group">
                        <label for="ICE">ICE</label>
                        <input type="text" class="form-control" name="ICE" required>
                    </div>
                    <div class="form-group">
                        <label for="type_client">Type Client</label>
                        <select class="form-control" name="type_client" required>
                            <option value="5.Entreprise de droit privé">Entreprise de droit privé</option>
                            <option value="1.État">État</option>
                            <option value="2.Collectivités territoriales">Collectivités territoriales</option>
                            <option value="3.Entreprise publique">Entreprise publique</option>
                            <option value="4.Autre organisme public">Autre organisme public</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                     <!-- Bouton Réinitialiser avec marge très grande à droite -->
                     <button type="reset" class="btn btn-secondary me-8">
                            <i class="fas fa-undo"></i>
                        </button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- CSS de Tabulator -->
<link href="https://unpkg.com/tabulator-tables@5.4.3/dist/css/tabulator.min.css" rel="stylesheet">

<!-- JavaScript de Tabulator -->
<script src="https://unpkg.com/tabulator-tables@5.4.3/dist/js/tabulator.min.js"></script>

    <!-- Conteneur Tabulator avec recherche -->
<div class="container mt-4">


    <!-- Conteneur pour Tabulator -->
    <div id="table-list"></div>
</div>

{{-- <!-- Table Tabulator -->
<div id="table-list"></div> --}}










<p style="margin-left:30px;">compte erroné </p>
<div style="background-color:rgba(228, 20, 20, 0.453);width:15px;height:15px;margin-top:-35px;border:1px solid #333;">

</div>

<p style="margin-left:30px;">information obligatoire manquante </p>
<div style="background-color: rgba(233, 233, 13, 0.838);width:15px;height:15px;margin-top:-35px;border:1px solid #333;">

</div>

<script>
  
const societeId = '{{ session('societeId') }}';

 
    document.getElementById('export-clients-button').addEventListener('click', function() {
    window.location.href = '/export-clients';
});
document.getElementById("export-pdf").addEventListener("click", function() {
        // Soumettre le formulaire d'export PDF
        document.querySelector('form[action="{{ route('export.clients.pdf') }}"]').submit();
    });

</script>
<script>
// Initialisation de Tabulator
var table = new Tabulator("#table-list", {
    layout: "fitColumns",
    data: @json($clients), // Chargement initial des données
    selectable: true, // Permet de sélectionner les lignes
    rowSelection: true,
    initialSort: [ // Tri initial par colonne 'Compte'
        { column: "compte", dir: "asc" }
    ],
    columns: [
        {
            title: `
<i class="fas fa-square" id="selectAllIcon" title="Sélectionner tout" style="cursor: pointer;" onclick="toggleSelectAll()"></i>
                <i class="fas fa-trash-alt" id="deleteAllIcon" title="Supprimer toutes les lignes sélectionnées" style="cursor: pointer;"></i>
            `,
            field: "select",
            formatter: "rowSelection", // Active la sélection de ligne
            headerSort: false,
            hozAlign: "center",
            width: 60, // Fixe la largeur de la colonne de sélection
            cellClick: function(e, cell) {
                cell.getRow().toggleSelect();  // Basculer la sélection de ligne
            }
        },
        { title: "Compte", field: "compte", headerFilter: "input" },
        { title: "Intitulé", field: "intitule", headerFilter: "input" },
        { title: "Identifiant fiscal", field: "identifiant_fiscal", headerFilter: "input" },
        { title: "ICE", field: "ICE", headerFilter: "input" },
        { title: "Type client", field: "type_client", headerFilter: "input" },
        {
            title: "Actions",
            field: "id",
            formatter: function(cell, formatterParams, onRendered) {
                var id = cell.getValue();
                return `
                   <span class="text-warning edit-client" title="Modifier" style="cursor: pointer;" data-id="${id}">
                        <i class="fas fa-edit" style="color:#82d616;"></i>
                    </span>
                    <span class="text-danger" title="Supprimer" style="cursor: pointer;" onclick="deleteClient(${id})">
                        <i class="fas fa-trash" style="color:#82d616;"></i>
                    </span>
                `;
            }
        }
    ],
    rowFormatter: function(row) {
        // Récupérer les valeurs du compte et de l'intitulé de la ligne
        var compte = row.getData().compte;
        var intitule = row.getData().intitule;

        // Récupérer le nombre de chiffres du compte défini dans la variable PHP
        var nombreChiffresCompte = {{ $societe->nombre_chiffre_compte }};

        // Vérifier si la valeur de 'compte' ou 'intitule' est égale à 0 ou null
        if (compte == 0 || compte == null || intitule == 0 || intitule == null) {
            row.getElement().style.backgroundColor = " rgba(233, 233, 13, 0.838)"; // Appliquer la couleur rouge à la ligne
        }
        // Vérifier si le nombre de chiffres du compte ne correspond pas à nombreChiffresCompte
        else if (compte.toString().length !== nombreChiffresCompte) {
            row.getElement().style.backgroundColor = "rgba(228, 20, 20, 0.453)"; // Appliquer la couleur jaune à la ligne
            row.getElement().style.color = "white";
        }
    }
});

    // Fonction pour basculer entre les icônes
    function toggleSelectAll() {
        var icon = document.getElementById('selectAllIcon');

        // Si l'icône est fa-square (non sélectionnée), la changer en fa-check-square (sélectionnée)
        if (icon.classList.contains('fa-square')) {
            icon.classList.remove('fa-square');
            icon.classList.add('fa-check-square');
        } else {
            // Si l'icône est fa-check-square (sélectionnée), la changer en fa-square (non sélectionnée)
            icon.classList.remove('fa-check-square');
            icon.classList.add('fa-square');
        }

        // Ici, vous pouvez ajouter d'autres actions pour gérer la sélection/désélection des éléments associés
        // Par exemple, vous pouvez cocher ou décocher des cases à cocher en fonction de l'état de l'icône.
    }

// Fonction pour supprimer un client sans demande de mot de passe
function deleteClient(clientId) {
    // Demander confirmation avant de supprimer le client
    if (confirm("Êtes-vous sûr de vouloir supprimer ce client ?")) {
        fetch(`/clients/${clientId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Supprimer le client du tableau (par exemple, avec Tabulator)
                table.deleteRow(clientId);
                alert('Client supprimé avec succès.');
            } else {
                alert('Erreur lors de la suppression du client.');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    }
}

// Fonction pour supprimer les lignes sélectionnées côté serveur sans rafraîchir la page
function deleteSelectedRows() {
    var selectedRows = table.getSelectedRows(); // Récupère les lignes sélectionnées
    var idsToDelete = selectedRows.map(function(row) {
        return row.getData().id; // Récupère les IDs des lignes sélectionnées
    });

    // Si aucune ligne n'est sélectionnée
    if (idsToDelete.length === 0) {
        alert("Aucune ligne sélectionnée pour la suppression.");
        return;
    }

    // Demander confirmation avant de supprimer
    if (confirm("Êtes-vous sûr de vouloir supprimer ces clients ?")) {
        // Envoi de la requête AJAX pour supprimer les clients
        fetch("/clients/delete-selected", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids: idsToDelete })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Clients supprimés avec succès.');

                // Supprimer les lignes du tableau Tabulator sans rafraîchir la page
                selectedRows.forEach(function(row) {
                    table.deleteRow(row);  // Supprime les lignes sélectionnées visuellement
                });
            }
        })
        .catch(error => {
            console.error('Erreur de suppression:', error);
            alert('Une erreur est survenue lors de la suppression.');
        });
    }
}

// Gestionnaire d'événements pour sélectionner/désélectionner toutes les lignes et supprimer les lignes sélectionnées
document.getElementById("table-list").addEventListener("click", function(e) {
    if (e.target.id === "selectAllIcon") {
        if (table.getSelectedRows().length === table.getRows().length) {
            table.deselectRow(); // Désélectionner toutes les lignes
        } else {
            table.selectRow(); // Sélectionner toutes les lignes
        }
    }
    if (e.target.id === "deleteAllIcon") {
        deleteSelectedRows(); // Appelle la fonction de suppression pour les lignes sélectionnées
        // Recharger la page
location.reload();

    }
});

 
$(document).ready(function () {
    var initialValue = '3421'; // La valeur initiale à ne pas modifier
    var nombreChiffresCompte = {{ $societe->nombre_chiffre_compte }}; // Nombre de chiffres pour le compte

    // Récupérer l'ID de la société à partir du champ caché
    var societeId = $('#societe_id').val(); // Assurez-vous que ce champ caché existe

    // Limiter la longueur du champ "compte" en fonction de "nombre_chiffre_compte"
    $('#compte').attr('maxlength', nombreChiffresCompte);

    // Quand le champ "compte" est modifié
    $('#compte').on('input', function () {
        var currentValue = $(this).val();

        // Si l'utilisateur modifie la partie "3421", on la restaure à chaque fois
        if (currentValue.length < initialValue.length || currentValue.substring(0, initialValue.length) !== initialValue) {
            $(this).val(initialValue); // Remettre le préfixe "3421" si modifié
        }

        // Si la longueur du compte atteint la longueur maximale autorisée, on empêche l'utilisateur de rajouter plus de caractères
        if (currentValue.length > nombreChiffresCompte) {
            $(this).val(currentValue.substring(0, nombreChiffresCompte)); // Limiter la longueur
        }
    });

    // Lors de l'ouverture du modal de saisie manuelle
    $('#modal-saisie-manuel').on('shown.bs.modal', function () {
        // Positionner le curseur juste après le "3421" (index 4)
        var input = $('#compte')[0];
        input.setSelectionRange(4, 4); // Positionner le curseur après le "3421"

        // Focus sur le champ "compte" lorsque le modal s'ouvre
        $('#compte').focus();
    });

    // Quand le bouton "auto-increment" est cliqué
    $('#auto-increment').on('click', function () {
        // Récupérer toutes les valeurs du champ "compte" dans le tableau Tabulator
        const comptes = table.getData().map(row => row.compte);

        // Logique pour déterminer le préfixe et la longueur du compte en fonction de "nombre_chiffres_compte"
        let comptePrefix = initialValue;
        let compteLength = nombreChiffresCompte;

        // Si aucun compte n'existe dans le tableau
        if (comptes.length === 0) {
            // Si aucun compte n'est présent, initialiser à "34210001" ou "3421000001"
            let initialCompte = comptePrefix + '0'.repeat(compteLength - comptePrefix.length - 1) + '1';
            $('#compte').val(initialCompte); // Remplir automatiquement avec le compte de départ
        } else {
            // Filtrer et récupérer uniquement les comptes qui commencent par le préfixe
            const comptesFiltrés = comptes.filter(compte => compte.startsWith(comptePrefix));

            // Extraire les numéros des comptes après "3421" et les convertir en entiers
            const numeraux = comptesFiltrés.map(compte => parseInt(compte.substring(comptePrefix.length)));

            // Trier les numéros extraits par ordre croissant
            numeraux.sort((a, b) => a - b);

            // Recherche d'une valeur manquante (trou) dans la séquence
            let newCompte = null;

            // Chercher s'il y a des trous dans la séquence
            for (let i = 0; i < numeraux.length - 1; i++) {
                if (numeraux[i + 1] > numeraux[i] + 1) {
                    // Trouvé un trou, on prend la valeur suivante manquante
                    newCompte = numeraux[i] + 1;
                    break;
                }
            }

            // Si aucun trou n'est trouvé, on prend le numéro suivant après le plus grand
            if (newCompte === null) {
                newCompte = numeraux[numeraux.length - 1] + 1;
            }

            // Formater le nouveau compte avec le nombre de chiffres demandé
            // On ajoute des zéros à gauche pour que le compte soit de la longueur correcte
            let formattedCompte = comptePrefix + newCompte.toString().padStart(compteLength - comptePrefix.length, '0');

            // Vérifier que la longueur du compte est correcte et ajuster si nécessaire
            if (formattedCompte.length > compteLength) {
                // Si la longueur dépasse la longueur attendue, on tronque le compte
                formattedCompte = formattedCompte.slice(0, compteLength);
            }

            // Mettre à jour le champ "compte" avec la nouvelle valeur
            $('#compte').val(formattedCompte); // Ajouter le préfixe "3421" et formater le numéro avec le nombre de chiffres spécifié
        }

        // Si vous souhaitez associer également l'ID de la société dans le compte généré, vous pouvez faire ceci :
        console.log("ID de la société : ", societeId);  // Exemple de console log de l'ID de la société
    });

    // Vérification au focus si le champ 'compte' est vide, et initialiser si nécessaire
    $('#compte').on('focus', function() {
        if ($(this).val() === '') {
            let initialCompte = comptePrefix + '0'.repeat(compteLength - comptePrefix.length - 1) + '1'; // 34210001 ou 3421000001
            $(this).val(initialCompte); // Remplir automatiquement avec le compte de départ
        }
    });
});


</script>
<script src="{{ asset('js/client.js') }}"></script>

@endsection
