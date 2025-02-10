 
 
 document.getElementById('export-button').addEventListener('click', function() {
 window.location.href = '/export-societes';
});

 
  $(document).ready(function() {
      // Réinitialiser le formulaire lors de l'ouverture du modal
      $('#nouvelleSocieteModal').on('show.bs.modal', function (event) {
          $('#societe-form')[0].reset();
      });
  
      // Lorsqu'on clique sur le bouton "Ajouter"
      $('#ajouter-societe').on('click', function(event) {
          // Sélectionner les éléments Rubrique TVA et Désignation
          const rubriqueTvaSelect = $('#rubrique_tva');
          const designationInput = $('input[name="designation"]');
  
          // Vérifier si Désignation est vide et si Rubrique TVA a une option sélectionnée
          if (designationInput.val().trim() === '' && rubriqueTvaSelect.val()) {
              // Extraire le texte de l'option sélectionnée dans Rubrique TVA
              const rubriqueTvaText = rubriqueTvaSelect.find('option:selected').text().trim();
  
              // Séparer le texte en mots
              const words = rubriqueTvaText.split(' ');
  
              // Exclure le premier et le dernier mot
              const middleWords = words.slice(1, words.length - 1);
  
              // Reconstituer la chaîne de texte sans le premier et dernier mot
              const racineNom = middleWords.join(' ');
  
              // Mettre à jour la valeur de Désignation avec le texte modifié
              designationInput.val(racineNom);
          };
          document.getElementById('societe-form').addEventListener('submit', function(event) {
          event.preventDefault();  // Empêcher l'envoi du formulaire pour que l'alerte s'affiche avant l'envoi réel
  
          // Récupérer les valeurs des champs
          const raisonSociale = document.querySelector('input[name="raison_sociale"]').value;
          const exerciceDebut = document.querySelector('input[name="exercice_social_debut"]').value;
          const exerciceFin = document.querySelector('input[name="exercice_social_fin"]').value;
  
          // Afficher l'alerte avec les données du formulaire
          alert('La société ' + raisonSociale + ', Exercice du ' + exerciceDebut + ' au ' + exerciceFin + ' créés avec succès');
          alert("Le compte d'accès interlocuteur généré avec succès !! Merci de consulter son profil sur la rubrique Interlocuteurs");
          // Si l'alerte est affichée, soumettre le formulaire
          this.submit();
      });
  
    
      });
    
  });
  
  
 
  
  // $(document).ready(function() {
  //     $('#nouvelleSocieteModal').on('show.bs.modal', function (event) {
  //         // Réinitialiser le formulaire lors de l'ouverture du modal
  //         $('#societe-form')[0].reset();
  //     });
  // });
  
  
  
  function remplirRubriquesTva(selectId, selectedValue = null) {
    $.ajax({
        url: '/rubriques-tva?type=Achat',
        type: 'GET',
        success: function(data) {
            var select = $("#" + selectId);
  
            select.empty();
  
            let categoriesArray = [];
            let caNonImposable = null;
  
            // Séparer CA non imposable des autres catégories
            $.each(data.rubriques, function(categorie, rubriques) {
                let categories = categorie.split('/').map(cat => cat.trim());
                let mainCategory = categories[0];
                let subCategory = categories[1] ? categories[1].trim() : '';
  
                if (mainCategory === 'CA non imposable') {
                    // Stocker la catégorie "CA non imposable" séparément
                    caNonImposable = {
                        mainCategory: mainCategory,
                        subCategory: subCategory,
                        rubriques: rubriques.rubriques
                    };
                } else {
                    // Ajouter les autres catégories
                    categoriesArray.push({
                        mainCategory: mainCategory,
                        subCategory: subCategory,
                        rubriques: rubriques.rubriques
                    });
                }
            });
  
            // Trier les autres catégories par ordre alphabétique
            categoriesArray.sort((a, b) => a.mainCategory.localeCompare(b.mainCategory));
  
            // Ajouter "CA non imposable" au début si elle existe
            if (caNonImposable) {
                categoriesArray.unshift(caNonImposable);
            }
  
            let categoryCounter = 1;
            const excludedNumRacines = [147, 151, 152, 148, 144];
  
            // Ajouter les catégories triées et leurs rubriques au select
            $.each(categoriesArray, function(index, categoryObj) {
                let mainCategoryOption = new Option(`${categoryCounter}. ${categoryObj.mainCategory}`, '', true, true);
                mainCategoryOption.className = 'category';
                mainCategoryOption.disabled = true;
                select.append(mainCategoryOption);
                categoryCounter++;
  
                if (categoryObj.subCategory) {
                    let subCategoryOption = new Option(` ${categoryObj.subCategory}`, '', true, true);
                    subCategoryOption.className = 'subcategory';
                    subCategoryOption.disabled = true;
                    select.append(subCategoryOption);
                }
  
                // Si la catégorie est "CA imposable", trier par taux descendant (desc)
                if (categoryObj.mainCategory === 'CA imposable') {
                    categoryObj.rubriques.sort((a, b) => b.Taux - a.Taux);  // Tri par taux descendant
                }
  
                // Ajouter les rubriques triées
                categoryObj.rubriques.forEach(function(rubrique) {
                    if (!excludedNumRacines.includes(rubrique.Num_racines)) {
                        let searchText = `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.mainCategory}`;
                        let option = new Option(`${rubrique.Num_racines}: ${rubrique.Nom_racines} : ${Math.round(rubrique.Taux)}%`, rubrique.Num_racines);
                        option.setAttribute('data-search-text', searchText);
                        option.setAttribute('data-nom-racine', rubrique.Nom_racines);
                        select.append(option);
                    }
                });
            });
  
            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true,
                templateResult: function(data) {
                    if (!data.id) return data.text;
                    if ($(data.element).hasClass('category')) {
                        return $('<span style="font-weight: bold;">' + data.text + '</span>');
                    } else if ($(data.element).hasClass('subcategory')) {
                        return $('<span style="font-weight: bold; padding-left: 10px;">' + data.text + '</span>');
                    }
                    return $('<span>' + data.text + '</span>');
                },
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') return data;
                    var searchText = $(data.element).data('search-text');
                    return searchText && searchText.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
                }
            });
  
            select.on("select2:open", function() {
                setTimeout(function() {
                    $('.select2-search__field').focus();
                }, 10);
            });
  
            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des rubriques :', textStatus, errorThrown);
        }
    });
  }
  
    
 document.addEventListener('DOMContentLoaded', function () {
     const inputs = document.querySelectorAll('#import-societe-form input:not([type="file"]):not([type="submit"])');

     inputs.forEach((input, index) => {
         input.addEventListener('keydown', function(event) {
             if (event.key === 'Enter') {
                 event.preventDefault(); // Empêche le formulaire de se soumettre
                 // Si c'est le dernier champ, soumettre le formulaire
                 if (!inputs[index + 1]) {
                     document.getElementById('import-societe-form').submit();
                 } else {
                     // Focus sur le champ suivant
                     inputs[index + 1].focus();
                 }
             }
         });
     });
 });
 
 $(document).ready(function() {
    remplirRubriquesTva('editRubriqueTVA');

    // Variable pour empêcher l'exécution multiple
    var modalOpened = false;

    // Fonction pour ouvrir le modal directement sans demander de mot de passe
    function openModal(societeId) {
        // Requête AJAX pour obtenir les données de la société
        var url = '/societes/' + societeId; // URL pour récupérer les données de la société

        $.get(url, function(data) {
            // Remplir le formulaire avec les données de la société
            $('#modification_id').val(data.id);
            $('#mod_raison_sociale').val(data.raison_sociale);
            $('#mod_siège_social').val(data.siege_social);
            $('#mod_ice').val(data.ice);
            $('#mod_rc').val(data.rc);
            $('#mod_identifiant_fiscal').val(data.identifiant_fiscal);
            $('#mod_patente').val(data.patente);
            $('#mod_centre_rc').val(data.centre_rc);
            $('#mod_forme_juridique').val(data.forme_juridique);
            $('#mod_exercice_social_debut').val(data.exercice_social_debut);
            $('#mod_exercice_social_fin').val(data.exercice_social_fin);
            $('#mod_date_creation').val(data.date_creation);
            $('#mod_assujettie_partielle_tva').val(data.assujettie_partielle_tva);
            $('#mod_prorata_de_deduction').val(data.prorata_de_deduction);
            $('#mod_nature_activite').val(data.nature_activite);
            $('#mod_activite').val(data.activite);
            $('#mod_regime_declaration').val(data.regime_declaration);
            $('#mod_fait_generateur').val(data.fait_generateur);
            $('#editRubriqueTVA').val(data.rubrique_tva);
            $('#mod_designation').val(data.designation);
            $('#mod_nombre_chiffre_compte').val(data.nombre_chiffre_compte);
            $('#mod_model_comptable').val(data.modele_comptable);

            // Ouvrir le modal après avoir rempli les données
            $('#modifierSocieteModal').modal('show');
        });
    }

    // Événement lors de l'ouverture du modal de modification
    $('#modifierSocieteModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // bouton qui a déclenché le modal
        var societeId = button.data('id'); // récupère l'ID de la société

        // Si le modal a déjà été ouvert, on ne continue pas
        if (modalOpened) {
            return;  // Si le modal est déjà ouvert, on arrête tout
        }

        // Marquer le modal comme ouvert pour éviter les appels multiples
        modalOpened = true;

        // Appeler la fonction pour ouvrir le modal directement sans mot de passe
        openModal(societeId);
    });

    // Événement lors de la soumission du formulaire
    $('#societe-modification-form').on('submit', function(e) {
        e.preventDefault(); // Empêche le rechargement de la page
        var formData = $(this).serialize(); // Sérialiser les données du formulaire
        var societeId = $('#modification_id').val(); // ID de la société

        // Requête AJAX pour mettre à jour la société
        $.ajax({
            url: '/societes/' + societeId,
            type: 'PUT',
            data: formData,
            success: function(response) {
                // Fermer le modal
                $('#modifierSocieteModal').modal('hide');
                // Recharger la page après la mise à jour
                location.reload(); // Recharger la page après la mise à jour
            },
            error: function(xhr) {
                // Gérer les erreurs
                alert('Une erreur s\'est produite lors de la mise à jour de la société.');
            }
        });
    });
});

    // Initialiser Tabulator avec les données
    var table = new Tabulator("#societes-table", {
        data: societes, // Charger les données passées depuis le contrôleur
        layout: "fitColumns", // Ajuster les colonnes à la largeur du tableau
        columns: [
            {
                title: `
                    <i class="fas fa-square" id="selectAllIcon" title="Sélectionner tout" style="cursor: pointer;" onclick="toggleSelectAll()"></i>
                    <i class="fas fa-trash-alt" id="deleteAllIcon" title="Supprimer toutes les lignes sélectionnées" style="cursor: pointer;" onclick="deleteSelectedRows()"></i>
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
            {title: "Raison Sociale", field: "raison_sociale", formatter: function(cell) {
                var nomEntreprise = cell.getData()["raison_sociale"];
                var formeJuridique = cell.getData().forme_juridique;
                return nomEntreprise + " " + formeJuridique;
            }, headerFilter: true},
            {title: "ICE", field: "ice", headerFilter: true},
            {title: "RC", field: "rc", headerFilter: true},
            {title: "Identifiant Fiscal", field: "identifiant_fiscal", headerFilter: true},
            {
                title: "Exercice en cours",
                field: "exercice_social", // Nom du champ dans vos données

                formatter: function(cell) {
                    const rowData = cell.getRow().getData(); // Obtenir les données de la ligne
                    return `Du <input type="date" value="${rowData.exercice_social_debut}"> au <input type="date" value="${rowData.exercice_social_fin}">`; // Formater les dates
                },
            },
            {
                title: "Actions",
                formatter: function(cell) {
                    var rowData = cell.getRow().getData();
                    return "<div class='action-icons'>" +
                    "<a href='/exercices/" + rowData.id + "' class='text-info mx-1'>" +
                    "<i class='fas fa-door-open'></i></a>" +
                    "<a href='#' class='text-primary mx-1' data-bs-toggle='modal' data-bs-target='#modifierSocieteModal' " +
                    "data-id='" + rowData.id + "' " +
                    "data-nom-entreprise='" + rowData.raison_sociale + "' " +
                    "data-ice='" + rowData.ice + "' " +
                    "data-rc='" + rowData.rc + "' " +
                    "data-identifiant-fiscal='" + rowData.identifiant_fiscal + "'>" +
                    "<i class='fas fa-edit'></i></a>" +
                    "<a href='#' class='text-danger mx-1 delete-icon' data-id='" + rowData.id + "'>" +
                    "<i class='fas fa-trash'></i></a>" +
                    "</div>";
                },
                width: 150,
                hozAlign: "center"
            }
        ],
        rowFormatter: function(row) {
        // Récupérer les valeurs du compte et de l'intitulé de la ligne
        var ice = row.getData().ice;

        // Vérifier si la valeur de 'compte' ou 'intitule' est égale à 0 ou null
        if (ice == 0 || ice == null) {
            row.getElement().style.backgroundColor = " rgba(233, 233, 13, 0.838)"; // Appliquer la couleur rouge à la ligne
        }
    }
    });


    function toggleSelectAll() {
    var icon = document.getElementById('selectAllIcon');

    // Vérifier l'état actuel (sélectionné ou non)
    var isAllSelected = table.getSelectedRows().length === table.getRows().length;

    console.log("isAllSelected: " + isAllSelected); // Debug

    if (isAllSelected) {
        // Si toutes les lignes sont sélectionnées, désélectionner toutes les lignes
        icon.classList.remove('fa-check-square');
        icon.classList.add('fa-square');
        table.deselectRows();  // Désélectionner toutes les lignes
    } else {
        // Si toutes les lignes ne sont pas sélectionnées, les sélectionner
        icon.classList.remove('fa-square');
        icon.classList.add('fa-check-square');
        table.selectRows();  // Sélectionner toutes les lignes
    }
}


    function deleteSelectedRows() {
    var selectedRows = table.getSelectedRows(); // Obtenez les lignes sélectionnées
    var selectedIds = selectedRows.map(function(row) {
        return row.getData().id;  // Récupérer les ID des lignes sélectionnées
    });

    // Si aucune ligne n'est sélectionnée
    if (selectedIds.length === 0) {
        alert("Aucune société sélectionnée.");
        return;
    }
    fetch("{{ route('societes.deleteSelected') }}", {
    method: "DELETE",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ ids: selectedIds })
})
.then(response => {
    // Affichez le texte de la réponse brute dans la console
    return response.text();  // Récupère la réponse en texte brut
})
.then(data => {
    console.log("Réponse brute du serveur:", data);  // Afficher la réponse brute pour vérifier ce qui est renvoyé

    try {
        const jsonData = JSON.parse(data);  // Essayer de parser la réponse en JSON
        if (jsonData.message) {
            alert(jsonData.message);  // Afficher le message de succès
            location.reload();  // Recharger la page ou mettre à jour le tableau
        } else {
            alert("Une erreur s'est produite lors de la suppression: " + jsonData.error);
        }
    } catch (error) {
        alert("Erreur de parsing JSON: " + error.message);  // Afficher l'erreur de parsing JSON
    }
})
.catch(error => {
    console.error("Erreur:", error);
    alert("Une erreur s'est produite lors de la suppression: " + error.message);  // Afficher l'erreur
});




}
// Gestionnaire d'événements pour sélectionner/désélectionner toutes les lignes et supprimer les lignes sélectionnées
// Gestionnaire d'événements pour sélectionner/désélectionner toutes les lignes et supprimer les lignes sélectionnées
document.getElementById("societes-table").addEventListener("click", function(e) {
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


  // Ajouter un gestionnaire d'événements pour le double clic sur une ligne
// table.on("rowDblClick", function(row) {
//     var rowData = row.getData(); // Obtenir les données de la ligne
//     window.location.href = "{{ route('exercices.show', '') }}/" + rowData.id; // Rediriger vers la vue 'exercices'
// });

//    // Écouteur d'événement pour le double clic sur une ligne du tableau
//    table.on("rowDblClick", function(row) {
//         var societeId = row.getData().id; // Récupérer l'ID de la société
//         window.location.href = `/exercice/${societeId}`; // Rediriger vers la vue "exercice"
//     });

	$(document).ready(function() {
  remplirRubriquesTva('rubrique_tva');
});


    // Ouvrir le modal au clic sur le bouton
    document.getElementById('open-modal-btn').addEventListener('click', function() {
        var myModal = new bootstrap.Modal(document.getElementById('nouvelleSocieteModal'));
        myModal.show();
        remplirRubriquesTva();
    });

   



// Gestion du changement de la valeur "Assujettie partielle à la TVA"
document.getElementById('assujettie_partielle_tva').addEventListener('change', function() {
    var prorataField = document.getElementById('prorata_de_deduction');

    if (this.value === "Null") {
        // Si "choisir un option" est sélectionné, désactiver 'Prorata de Déduction' et le réinitialiser
        prorataField.value = "0";  // Réinitialiser la valeur
        prorataField.setAttribute("readonly", true); // Rendre le champ non modifiable
    } else if (this.value === "0") {
        // Si l'option "Non" est sélectionnée, mettre la valeur à "100" et rendre le champ non modifiable
        prorataField.value = "100"; // Mettre la valeur à 100
        prorataField.setAttribute("readonly", true); // Rendre le champ non modifiable
    } else {
        // Si une autre option est sélectionnée, rendre le champ modifiable
        prorataField.removeAttribute("readonly"); // Rendre le champ modifiable
        prorataField.value = ""; // Réinitialiser le champ si nécessaire
    }
});



    document.getElementById("identifiant_fiscal").addEventListener("input", function() {
    // Remplace tous les caractères non numériques par une chaîne vide
    this.value = this.value.replace(/\D/g, '');

    // Limite la longueur à 8 chiffres
    if (this.value.length > 8) {
        this.value = this.value.slice(0, 8);
    }
});


    document.getElementById("ice").addEventListener("input", function() {
    // Remplace tous les caractères non numériques par une chaîne vide
    this.value = this.value.replace(/\D/g, '');

    // Limite la longueur à 15 chiffres
    if (this.value.length > 15) {
        this.value = this.value.slice(0, 15);
    }
});


$(function() {
  $('#exercice_social').daterangepicker({
    opens: 'left',
    startDate: moment('2018-01-01'), // Date de début par défaut
    endDate: moment('2019-01-15'), // Date de fin par défaut
    locale: {
      format: 'YYYY-MM-DD'
    },
    // Permet de choisir une plage de dates
    singleDatePicker: false,
    showDropdowns: true,
    autoUpdateInput: true
  }, function(start, end) {
    // Met à jour le champ d'entrée avec les dates sélectionnées
    $('#exercice_social').val(start.format('YYYY-MM-DD') + ' au ' + end.format('YYYY-MM-DD'));
  });
});





    document.getElementById('import-societes').addEventListener('click', function() {
    // Logique d'importation, par exemple, ouvrir un modal
    openImportModal();
});

function openImportModal() {
    // Code pour afficher le modal d'importation
    $('#importModal').modal('show'); // Utiliser Bootstrap modal si vous l'avez
}

 