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

  // Ajout d'un gestionnaire d'événements pour les sélections de listes déroulantes
  const selects = document.querySelectorAll(`#${formId} select`);
  selects.forEach(select => {
      select.addEventListener('keydown', function(event) {
          if (event.key === 'ArrowDown') {
              // Sélectionne l'option suivante
              if (this.selectedIndex < this.options.length - 1) {
                  this.selectedIndex++;
              }
              event.preventDefault(); // Empêche le défilement de la page
          } else if (event.key === 'ArrowUp') {
              // Sélectionne l'option précédente
              if (this.selectedIndex > 0) {
                  this.selectedIndex--;
              }
              event.preventDefault(); // Empêche le défilement de la page
          } else if (event.key === 'Enter') {
              // Si la touche "Entrée" est pressée après la sélection, passe au champ suivant
              event.preventDefault(); // Empêche le comportement par défaut
              const currentInput = document.activeElement; // Récupère l'élément actif
              const inputs = Array.from(document.getElementById(formId).elements).filter(el => el.tagName === 'INPUT' || el.tagName === 'SELECT');
              const currentIndex = inputs.indexOf(currentInput);
              
              // Vérifie si l'élément actif est un <select>
              if (currentInput.tagName === 'SELECT') {
                  if (currentIndex < inputs.length - 1) {
                      inputs[currentIndex + 1].focus(); // Passe au champ suivant
                  }
              }
          }
      });
  });
}


// Initialiser la navigation pour les deux formulaires
setupEnterNavigation('fournisseurFormAdd');
setupEnterNavigation('fournisseurFormEdit');
setupEnterNavigation('importModal');


    // Événement pour garantir que le champ compte commence par 4411
    // Initialiser le champ compte avec '4411' et déplacer le curseur à la fin
  const compteInput = document.getElementById('compte');
  compteInput.value = '4411';

  // Déplacer le curseur à la fin du texte
  compteInput.setSelectionRange(compteInput.value.length, compteInput.value.length);

// Validation pour le champ ICE
$("#ICE").on("input", function() {
  // Remplacer le contenu du champ par uniquement les chiffres
  this.value = this.value.replace(/[^0-9]/g, '');

  // Limiter la longueur à 15 caractères
  if (this.value.length > 15) {
      this.value = this.value.slice(0, 15);
  }
});

// Validation pour le champ identifiant_fiscal
$("#identifiant_fiscal").on("input", function() {
  // Remplacer le contenu du champ par uniquement les chiffres
  this.value = this.value.replace(/[^0-9]/g, '');

  // Limiter la longueur à 15 caractères
  if (this.value.length > 15) {
      this.value = this.value.slice(0, 15);
  }
});



var table = new Tabulator("#fournisseur-table", {
  ajaxURL: "/fournisseurs/data", // URL pour récupérer les données
  layout: "fitColumns",
  Height:  "500px", // Hauteur minimale du tableau
  columns: [
      {title: "Compte", field: "compte", editor: "input", headerFilter: "input", minWidth: 80},
      {title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input", minWidth: 120},
      {title: "Identifiant Fiscal", field: "identifiant_fiscal", editor: "input", headerFilter: "input", minWidth: 50},
      {title: "ICE", field: "ICE", editor: "input", headerFilter: "input", minWidth: 80},
      {title: "Nature de l'opération", field: "nature_operation",editor: "input", headerFilter: "input", minWidth: 50},
      {
          title: "Rubrique TVA",
          field: "rubrique_tva",
          editor: "input",
          headerFilter: "input", 
          minWidth: 50,
      },
      {title: "Désignation", field: "designation", editor: "input", headerFilter: "input", minWidth: 80},
      {title: "Contre Partie", field: "contre_partie", editor: "input", headerFilter: "input", minWidth: 80},
      {
          title: "Actions", 
          field: "action-icons", 
          formatter: function() {
              return `
               
            <i class='fas fa-edit text-primary edit-icon' style='font-size: 0.9em; line-height:0.8; style='cursor: pointer;'></i>
  <i class='fas fa-trash-alt text-danger delete-icon' style='font-size: 0.9em; line-height:0.8; style='cursor: pointer;'></i>
`;
          },
          cellClick: function(e, cell) {
              if (e.target.classList.contains('edit-icon')) {
                  var rowData = cell.getRow().getData();
                  editFournisseur(rowData);
                 
              } else if (e.target.classList.contains('delete-icon')) {
                  var rowData = cell.getRow().getData();
                  deleteFournisseur(rowData.id);
              }
          },
          minWidth: 50,
      }
  ],
  
});


$("#fournisseurFormAdd").on("submit", function(e) {
  e.preventDefault(); // Empêche la soumission par défaut du formulaire

  // Récupérer la valeur du champ rubrique_tva, designation et nom_racines
  var contrePartieVal = $("#contre_partie").val();
  var designationVal = designationValue; // Utiliser la variable globale pour l'intitulé

  // Vérifier si le champ designation est vide
  if (!$("#designation").val()) {
      
      $("#designation").val(designationVal); // Remplir le champ designation avec designationValue
 
  }

  // Appeler la fonction d'envoi des données
  envoyerDonnees(designationVal); // Passer designationVal à la fonction d'envoi
});

// Fonction pour envoyer les données via AJAX
function envoyerDonnees(designationVal) {
  var url = "/fournisseurs"; // URL pour l'ajout

  $.ajax({
      url: url,
      type: "POST",
      data: {
          compte: $("#compte").val(),
          intitule: $("#intitule").val(),
          identifiant_fiscal: $("#identifiant_fiscal").val(),
          ICE: $("#ICE").val(),
          nature_operation: $("#nature_operation").val(),
          rubrique_tva: $("#rubrique_tva").val(),
          designation:designationValue, // Utiliser la designation remplie sans l'afficher
          contre_partie: $("#contre_partie").val(),
          _token: '{{ csrf_token() }}' // Assurez-vous d'inclure votre CSRF token
      },
      success: function(response) {
          $("#designation").val(designationValue); // Remplir le champ designation avec designationValue
          table.setData("/fournisseurs/data"); // Recharger les données
          $("#fournisseurModaladd").modal("hide");
          $("#fournisseurFormAdd")[0].reset(); // Réinitialiser le formulaire d'ajout
      },
      error: function(xhr) {
          console.error("Erreur lors de l'enregistrement des données :", xhr.responseText);
          alert("Erreur lors de l'enregistrement des données !");
      }
  });
}

// Appel pour remplir les options de contrepartie lors du chargement
$(document).ready(function() {
  remplirContrePartie('contre_partie');
});



var designationValue = ''; // Variable globale pour stocker l'intitulé

function remplirContrePartie(selectId, selectedValue = null, callback = null) {
  $.ajax({
      url: '/comptes', // La route pour récupérer les comptes
      type: 'GET',
      success: function(data) {
          var select = $("#" + selectId);

          // Détruire Select2 s'il est déjà initialisé
          if (select.hasClass("select2-hidden-accessible")) {
              select.select2("destroy");
          }

          // Vider le sélecteur et ajouter les nouvelles options
          select.empty();

          // Tri des comptes par numéro
          data.sort((a, b) => a.compte.localeCompare(b.compte));

          // Ajout des comptes au sélecteur
          data.forEach(function(compte) {
              let option = new Option(`${compte.compte} - ${compte.intitule}`, compte.compte);
              select.append(option);
          });

          // Réinitialiser Select2 avec les paramètres de configuration
          select.select2({
              width: '100%',
              minimumResultsForSearch: 0, // Afficher la barre de recherche
              dropdownAutoWidth: true
          });

          // Mettre le focus sur la recherche lorsqu'on ouvre le sélecteur
          select.on("select2:open", function() {
              setTimeout(function() {
                  $('.select2-search__field').focus();
              }, 10);
          });

          // Sélectionner la valeur si fournie
          if (selectedValue) {
              select.val(selectedValue).trigger('change');
          }

          // Gestionnaire d'événement pour la sélection d'un compte
          select.on('select2:select', function(e) {
              var data = e.params.data;
              var designationValue = data.text.split(' - ')[1];
              console.log("Intitulé sélectionné (sans l'afficher dans le champ) :", designationValue);
          });

          // Appeler le callback si fourni
          if (callback) callback();
      },
      error: function(jqXHR, textStatus, errorThrown) {
          console.error('Erreur lors de la récupération des comptes :', textStatus, errorThrown);
      }
  });
}

$(document).ready(function() {
  // Appel de la fonction pour remplir le sélecteur à l'ouverture du modal
  $('#fournisseurModaladd').on('shown.bs.modal', function() {
      remplirContrePartie('contre_partie'); // ID du sélecteur
  });

});


$(document).ready(function() {
  remplirRubriquesTva('rubrique_tva');
});


// Soumission du formulaire de modification de fournisseur
$("#fournisseurFormEdit").on("submit", function(e) {
  e.preventDefault();

  var fournisseurId = $("#editFournisseurId").val();
  var url = "/fournisseurs/" + fournisseurId; // URL pour la modification

  $.ajax({
      url: url,
      type: "PUT",
      data: {
          compte: $("#editCompte").val(),
          intitule: $("#editIntitule").val(),
          identifiant_fiscal: $("#editIdentifiantFiscal").val(),
          ICE: $("#editICE").val(),
          nature_operation: $("#editNatureOperation").val(),
          rubrique_tva: $("#editRubriqueTVA").val(), // Inclure la valeur sélectionnée dans les données
          designation: $("#editDesignation").val(),
          contre_partie: $("#editContrePartie").val(),
          _token: '{{ csrf_token() }}' // Assurez-vous que le token CSRF est inclus
      },
      success: function(response) {
          table.setData("/fournisseurs/data"); // Recharger les données
          $("#fournisseurModaledit").modal("hide");
          $("#fournisseurFormEdit")[0].reset(); // Réinitialiser le formulaire de modification
          $("#editFournisseurId").val(""); // Réinitialiser l'ID
          // Remplir de nouveau les rubriques TVA pour le prochain affichage
          remplirRubriquesTva('rubrique_tva'); 
          remplirContrePartie('contre_partie');
      },
      error: function(xhr) {
          alert("Erreur lors de l'enregistrement des données !");
      }
  });
});

// Fonction pour remplir le formulaire pour la modification
function editFournisseur(data) {
  $("#editFournisseurId").val(data.id);
  $("#editCompte").val(data.compte);
  $("#editIntitule").val(data.intitule);
  $("#editIdentifiantFiscal").val(data.identifiant_fiscal);
  $("#editICE").val(data.ICE);
  $("#editNatureOperation").val(data.nature_operation);
  remplirRubriquesTva('rubrique_tva'); 
  remplirContrePartie('contre_partie');
  // Remplir la liste déroulante de rubrique TVA avec la valeur actuelle
  remplirRubriquesTva("editRubriqueTVA", data.rubrique_tva);

  $("#editDesignation").val(data.designation);
  $("#editContrePartie").val(data.contre_partie);
  remplirContrePartie("editContrePartie", data.contre_partie);
  
  $("#fournisseurModaledit").modal("show");
}

// Fonction pour remplir les options de rubrique TVA dans le select
function remplirRubriquesTva(selectId, selectedValue = null) {
  $.ajax({
      url: '/rubriques-tva?type=Achat',
      type: 'GET',
      success: function(data) {
          var select = $("#" + selectId);
          
          // Détruire Select2 s'il est déjà initialisé
          if (select.hasClass("select2-hidden-accessible")) {
              select.select2("destroy");
          }
          
          select.empty();

          let categoriesArray = [];
          $.each(data.rubriques, function(categorie, rubriques) {
              let categories = categorie.split('/').map(cat => cat.trim());
              let mainCategory = categories[0];
              let subCategory = categories[1] ? categories[1].trim() : '';
              categoriesArray.push({
                  mainCategory: mainCategory,
                  subCategory: subCategory,
                  rubriques: rubriques.rubriques
              });
          });

          categoriesArray.sort((a, b) => a.mainCategory.localeCompare(b.mainCategory));
          let categoryCounter = 1;
          const excludedNumRacines = [147, 151, 152, 148, 144];

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





// excel

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
          
          compteSelect.innerHTML = '';
          intituleSelect.innerHTML = '';
          
          headers.forEach((header, index) => {
              const optionCompte = new Option(header, index);
              const optionIntitule = new Option(header, index);
              compteSelect.add(optionCompte);
              intituleSelect.add(optionIntitule);
          });
      };

      reader.readAsArrayBuffer(file);
  });


  // Fonction pour supprimer un fournisseur
  function deleteFournisseur(id) {
      if (confirm("Êtes-vous sûr de vouloir supprimer ce fournisseur ?")) {
          $.ajax({
              url: "/fournisseurs/" + id,
              type: "DELETE",
              data: {
                  _token: '{{ csrf_token() }}'
              },
              success: function(response) {
                  table.setData("/fournisseurs/data"); // Recharger les données
              },
              error: function(xhr) {
                  alert("Erreur lors de la suppression des données !");
              }
          });
      }
  }
// Fonction pour sauvegarder les valeurs dans localStorage
function saveInputValues() {
      const inputs = [
          'colonne_compte',
          'colonne_intitule',
          'colonne_identifiant_fiscal',
          'colonne_ICE',
          'colonne_nature_operation',
          'colonne_rubrique_tva',
          'colonne_designation',
          'colonne_contre_partie',
      ];

      inputs.forEach(inputId => {
          const input = document.getElementById(inputId);
          localStorage.setItem(inputId, input.value);
          input.addEventListener('input', () => {
              localStorage.setItem(inputId, input.value);
          });
      });
  }

  // Fonction pour restaurer les valeurs de localStorage
  function restoreInputValues() {
      const inputs = [
          'colonne_compte',
          'colonne_intitule',
          'colonne_identifiant_fiscal',
          'colonne_ICE',
          'colonne_nature_operation',
          'colonne_rubrique_tva',
          'colonne_designation',
          'colonne_contre_partie',
      ];

      inputs.forEach(inputId => {
          const input = document.getElementById(inputId);
          if (localStorage.getItem(inputId)) {
              input.value = localStorage.getItem(inputId);
          }
      });
  }

  // Appeler les fonctions lors de l'ouverture du modal
  document.addEventListener('DOMContentLoaded', () => {
      restoreInputValues(); // Restaurer les valeurs lorsque la page est chargée
      saveInputValues();    // Sauvegarder les valeurs lors de la saisie
  });



