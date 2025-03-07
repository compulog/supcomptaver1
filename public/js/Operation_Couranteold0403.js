
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab');

    // Forcer l'activation de l'onglet "Achats" par défaut
    const defaultTab = document.querySelector('.tab[data-tab="achats"]');
    if (defaultTab) {
        defaultTab.classList.add('active');
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Enlever la classe 'active' de tous les onglets
            tabs.forEach(t => t.classList.remove('active'));

            // Ajouter la classe 'active' à l'onglet cliqué
            tab.classList.add('active');

            // Modifier la couleur de fond des onglets
            tabs.forEach(t => {
                if (t.classList.contains('active')) {
                    t.style.backgroundColor = '#007bff';
                    t.style.color = 'white';
                    t.style.borderColor = '#0056b3';
                } else {
                    t.style.backgroundColor = '#f9f9f9';
                    t.style.color = 'black';
                    t.style.borderColor = '#ccc';
                }
            });
        });
    });
});

function remplirContrePartie(selectId, selectedValue = null) {
    $.ajax({
        url: '/comptes',
        type: 'GET',
        success: function (data) {
            var select = $("#" + selectId);
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2("destroy");
            }
            select.empty();
            select.append(new Option("Sélectionnez une contre partie", ""));
            data.sort((a, b) => a.compte.localeCompare(b.compte));
            data.forEach(function (compte) {
                let option = new Option(`${compte.compte} - ${compte.intitule}`, compte.compte);
                select.append(option);
            });
            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true
            });
            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }
        }
    });
}
function remplirRubriquesTva(selectId, selectedValue = null) {
    $.ajax({
        url: '/get-rubriques-tva',
        type: 'GET',
        success: function (data) {
            var select = $("#" + selectId);

            // Réinitialisation de Select2 s'il est déjà initialisé
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2("destroy");
            }
            select.empty();
            select.append(new Option("Sélectionnez une Rubrique", ""));

            let categoriesArray = [];
            $.each(data.rubriques, function (categorie, rubriques) {
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

            $.each(categoriesArray, function (index, categoryObj) {
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

                categoryObj.rubriques.forEach(function (rubrique) {
                    if (!excludedNumRacines.includes(rubrique.Num_racines)) {
                        let option = new Option(`${rubrique.Num_racines}: ${rubrique.Nom_racines} : ${Math.round(rubrique.Taux)}%`, rubrique.Num_racines);
                        option.setAttribute('data-search-text', `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.mainCategory}`);
                        select.append(option);
                    }
                });
            });

            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true,
                templateResult: function (data) {
                    if (!data.id) return data.text;
                    if ($(data.element).hasClass('category')) {
                        return $('<span style="font-weight: bold;">' + data.text + '</span>');
                    } else if ($(data.element).hasClass('subcategory')) {
                        return $('<span style="font-weight: bold; padding-left: 10px;">' + data.text + '</span>');
                    }
                    return $('<span>' + data.text + '</span>');
                },
                matcher: function (params, data) {
                    if ($.trim(params.term) === '') return data;
                    var searchText = $(data.element).data('search-text');
                    return searchText && searchText.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
                }
            });

            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des rubriques :', textStatus, errorThrown);
        }
    });
}

/**********************************************/
/* Fonctions Utilitaires Globales             */
/**********************************************/

// Fonction permettant de passer à la cellule éditable suivante
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

/**********************************************/
/* Fonction de mise à jour du Libellé         */
/**********************************************/

function updateLibelle(row) {
    const rowData = row.getData();
    const numeroFacture = rowData.numero_facture || "Inconnu";
    const compteFournisseur = rowData.compte; // Ce champ doit contenir uniquement le numéro de compte

    if (!compteFournisseur) {
        row.update({ libelle: "" });
        return;
    }

    fetch(`/get-fournisseurs-avec-details?societe_id=${societeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error("Erreur lors de la récupération des détails :", data.error);
                return;
            }
            // On recherche par numéro de compte seulement
            const fournisseur = data.find(f => f.compte === compteFournisseur);
            if (fournisseur) {
                // Mise à jour du libellé avec le numéro de facture et l'intitulé du fournisseur
                row.update({
                    libelle: `F° ${numeroFacture} ${fournisseur.intitule}`
                });
                // Affichage des autres données du fournisseur dans la console (ou dans un autre composant si nécessaire)
                console.log("Détails fournisseur :", fournisseur);
                // Après mise à jour, on met le focus sur la cellule "credit"
                setTimeout(() => {
                    const creditCell = row.getCell("credit");
                    if (creditCell) {
                        creditCell.edit();
                    }
                }, 300); // délai de 300ms (ajustez si nécessaire)
            } else {
                console.warn("Aucun fournisseur correspondant trouvé pour le compte :", compteFournisseur);
            }
        })
        .catch(error => {
            console.error("Erreur réseau lors de la récupération des détails :", error);
            alert("Une erreur est survenue lors de la récupération des détails du fournisseur.");
        });
}

// Fonction pour mettre à jour la ligne avec le libellé et déplacer le focus
function updateLibelleAndFocus(row, compte) {
    // Première tentative via l'API des détails
    fetch(`/get-fournisseurs-avec-details?societe_id=${societeId}`)
        .then(response => response.json())
        .then(data => {
            let fournisseur = data.find(f => f.compte === compte);
            if (!fournisseur) {
                // Si aucun fournisseur trouvé, on tente via l'API /fournisseurs-comptes
                return fetch('/fournisseurs-comptes')
                    .then(response => response.json())
                    .then(data2 => {
                        fournisseur = data2.find(f => f.compte === compte);
                        if (fournisseur) {
                            updateRowWithFournisseur(row, fournisseur);
                        } else {
                            console.warn("Aucun fournisseur trouvé pour le compte :", compte);
                        }
                    });
            } else {
                updateRowWithFournisseur(row, fournisseur);
            }
        })
        .catch(error => {
            console.error("Erreur réseau lors de la récupération :", error);
            alert("Une erreur est survenue lors de la récupération du fournisseur.");
        });
}

// Fonction qui met à jour la ligne avec les données du fournisseur
function updateRowWithFournisseur(row, fournisseur) {
    const numeroFacture = row.getCell("numero_facture").getValue() || "Inconnu";
    const libelle = `F° ${numeroFacture} ${fournisseur.intitule || ""}`;
    const tauxTVA = parseFloat(fournisseur.taux_tva) || 0;
    window.tauxTVAGlobal = tauxTVA;  // mise à jour globale si nécessaire

    row.update({
        libelle: libelle,
        contre_partie: fournisseur.contre_partie || "",
        rubrique_tva: fournisseur.rubrique_tva || "",
        taux_tva: tauxTVA,
        compte_tva: (window.comptesVentes && window.comptesVentes.length > 0)
            ? `${window.comptesVentes[0].compte} - ${window.comptesVentes[0].intitule || ""}`
            : ""
    });

    // Déplacement du focus en fonction du préfixe du compte
    let trimmed = row.getCell("compte").getValue().trim();
    if (trimmed.startsWith("55") || trimmed.startsWith("1") || trimmed.startsWith("4") || trimmed.startsWith("7")) {
        setTimeout(() => {
            const creditCell = row.getCell("credit");
            if (creditCell) { creditCell.edit(); }
        }, 300);
    } else if (trimmed.startsWith("51") || trimmed.startsWith("2") || trimmed.startsWith("3") || trimmed.startsWith("6")) {
        setTimeout(() => {
            const debitCell = row.getCell("debit");
            if (debitCell) { debitCell.edit(); }
        }, 300);
    }
}


function getFormattedComptesFournisseurs() {
    var formatted = [];
    if (window.comptesFournisseurs && Array.isArray(window.comptesFournisseurs)) {
        for (let i = 0; i < window.comptesFournisseurs.length; i++){
            let f = window.comptesFournisseurs[i];
            if (f && f.compte) {
                formatted.push(`${f.compte} - ${f.intitule || ""}`);
            }
        }
    }
    return formatted;
}


/**********************************************/
/* Éditeurs Personnalisés                     */
/**********************************************/

// Éditeur générique pour les champs texte (utilisé pour "N° facture" et "Libellé")
function genericTextEditor(cell, onRendered, success, cancel, editorParams) {
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
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
            setTimeout(() => {
                focusNextEditableCell(cell);
            }, 50);
        }
    });

    return input;
}
// Éditeur personnalisé pour le champ "Libellé" qui, sur Enter, transfère le focus sur le champ "Compte"
function genericTextEditorForLibelle(cell, onRendered, success, cancel, editorParams) {
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.value = cell.getValue() || "";

    onRendered(() => {
        input.focus();
    });

    // Validation au blur
    input.addEventListener("blur", () => {
        success(input.value);
    });

    // Lorsqu'on appuie sur Enter, on valide et on place le focus sur la cellule "compte"
    input.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            success(input.value);
            setTimeout(() => {
                const creditCell = cell.getRow().getCell("credit");
                if (creditCell) {
                    creditCell.edit();  // Lance l'édition sur le champ "Compte"
                }
            }, 50);
        }
    });

    return input;
}


var societeId = $('#societe_id').val(); // ID de la société
var nombreChiffresCompte = parseInt($('#nombre_chiffre_compte').val()); // Nombre de chiffres du compte
// Déclaration globale de la liste des comptes fournisseurs
var comptesFournisseurs = []; // ou avec des valeurs initiales si vous en avez
function genererCompteAutoForPopupClt() {
    $.ajax({
        url: `/get-next-compte-client/${societeId}?nombre=${nombreChiffresCompte}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#swal-compte').val(response.nextCompte);
            } else {
                alert('Erreur lors de la génération du compte client.');
            }
        },
        error: function() {
            alert('Erreur lors de la génération du compte client.');
        }
    });
}

  function ouvrirPopupClient(compteClient, row, cell) {
    // Récupérer l'id de la société depuis la balise meta
    const societeId = document.querySelector('meta[name="societe_id"]').getAttribute("content");

    Swal.fire({
      title: 'Ajouter un nouveau client',
      width: '800px',
      html: `
        <div class="container">
          <!-- Ligne 1 : Compte et Intitulé -->
          <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
            <div style="flex: 1 1 45%;">
              <input id="swal-compte" class="swal2-input" placeholder="Compte" value="">
            </div>
            <div style="flex: 1 1 45%;">
              <input id="swal-intitule" class="swal2-input" placeholder="Intitulé" required value="${compteClient}">
            </div>
          </div>
          <!-- Ligne 2 : Identifiant Fiscal et ICE côte à côte -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="swal-identifiant" class="form-label">Identifiant Fiscal</label>
              <input type="text" id="swal-identifiant" class="swal2-input form-control" placeholder="Identifiant Fiscal"
                     pattern="^\\d{7,8}$" maxlength="8" title="L'identifiant fiscal doit comporter 7 ou 8 chiffres"
                     oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
            <div class="col-md-6">
              <label for="swal-ICE" class="form-label">ICE</label>
              <input type="text" id="swal-ICE" class="swal2-input form-control" placeholder="ICE"
                     pattern="^\\d{15}$" maxlength="15" title="L'ICE doit comporter exactement 15 chiffres"
                     oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
          </div>
          <!-- Ligne 3 : Type Client (occupant toute la largeur) -->
          <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <div style="flex: 1 1 45%;">
              <label for="swal-type_client" class="form-label">Type Client</label>
              <select id="swal-type_client" class="swal2-input">
                <option value="">Choisir une option</option>
                <option value="5.Entreprise de droit privé">5.Entreprise de droit privé</option>
                <option value="1.État">1.État</option>
                <option value="2.Collectivités territoriales">2.Collectivités territoriales</option>
                <option value="3.Entreprise publique">3.Entreprise publique</option>
                <option value="4.Autre organisme public">4.Autre organisme public</option>
              </select>
            </div>
          </div>
        </div>
      `,
      didOpen: () => {
        // Appel de la fonction d'auto-incrément pour générer le compte
        if (typeof genererCompteAutoForPopupClt === "function") {
          genererCompteAutoForPopupClt();
        }
      },
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: 'Ajouter',
      preConfirm: () => {
        return {
          compte: document.getElementById('swal-compte').value,
          intitule: document.getElementById('swal-intitule').value,
          identifiant_fiscal: document.getElementById('swal-identifiant').value,
          ICE: document.getElementById('swal-ICE').value,
          type_client: document.getElementById('swal-type_client').value,
          societe_id: societeId
        };
      }
    }).then((result) => {
      if (result.isConfirmed && result.value) {
        fetch("/clients", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(result.value)
        })
        .then(response => response.json())
        .then(newClient => {
          if (newClient.error) {
            Swal.fire('Erreur', newClient.error, 'error');
          } else {
            const clientCree = newClient.client;
            const newValue = `${clientCree.compte} - ${clientCree.intitule}`;
            // Mettez à jour la liste globale si elle existe
            if (typeof window.comptesClients !== "undefined") {
              window.comptesClients.push(clientCree);
            }
            cell.setValue(newValue);
            const numeroDossier = row.getCell("numero_dossier").getValue() || "";
            const numeroFacture = row.getCell("numero_facture").getValue() || "";
            row.update({
              libelle: `F°${numeroFacture} D°${numeroDossier} ${clientCree.intitule}`
            });
            Swal.fire('Succès', 'Client ajouté avec succès', 'success').then(() => {
              const debitCell = row.getCell("debit");
              if (debitCell) {
                setTimeout(() => { debitCell.edit(); }, 200);
              }
            });
          }
        })
        .catch(error => {
          console.error('Erreur lors de l’ajout du client:', error);
          Swal.fire('Erreur', 'Une erreur est survenue lors de l’ajout du client.', 'error');
        });
      }
    });
  }


function customListEditorClt(cell, onRendered, success, cancel, editorParams) {
    // Création du container principal pour l'éditeur
    const container = document.createElement("div");
    container.className = "custom-list-editor-container";
    container.style.position = "relative"; // Pour une bonne gestion du focus

    // Création de l'input
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.style.boxSizing = "border-box";
    input.placeholder = "Rechercher un client...";
    input.value = cell.getValue() || "";
    container.appendChild(input);

    // Préparation du tableau d'options à partir des paramètres
    let options = [];
    if (editorParams && editorParams.values) {
      options = Array.isArray(editorParams.values)
        ? editorParams.values
        : Object.values(editorParams.values);
    }

    // Création du dropdown personnalisé (ajouté dans le body pour éviter qu'il ne soit caché)
    const dropdown = document.createElement("div");
    dropdown.className = "custom-dropdown";
    dropdown.style.position = "absolute";
    dropdown.style.background = "#fff";
    dropdown.style.border = "1px solid #ccc";
    dropdown.style.maxHeight = "200px";
    dropdown.style.overflowY = "auto";
    dropdown.style.zIndex = "10000"; // Pour qu'il apparaisse au-dessus
    dropdown.style.display = "none"; // Caché par défaut
    document.body.appendChild(dropdown);

    // Fonction pour positionner le dropdown sous l'input
    function positionDropdown() {
      const rect = input.getBoundingClientRect();
      dropdown.style.top = (rect.bottom + window.scrollY) + "px";
      dropdown.style.left = (rect.left + window.scrollX) + "px";
      dropdown.style.width = rect.width + "px";
    }

    // Mise à jour du contenu du dropdown en fonction de la saisie
    function updateDropdown() {
      dropdown.innerHTML = "";
      const search = input.value.trim().toLowerCase();
      const filtered = options.filter(opt => opt.toLowerCase().indexOf(search) !== -1);

      if (filtered.length > 0) {
        filtered.forEach(opt => {
          const item = document.createElement("div");
          item.textContent = opt;
          item.style.padding = "5px";
          item.style.cursor = "pointer";
          item.style.borderBottom = "1px solid #eee";
          item.addEventListener("mousedown", function(e) {
            e.preventDefault();
            input.value = opt;
            dropdown.style.display = "none";
            success(opt);
          });
          dropdown.appendChild(item);
        });
      } else {
        // Aucun résultat : afficher un message et un bouton d'ajout de client
        const item = document.createElement("div");
        item.style.display = "flex";
        item.style.justifyContent = "space-between";
        item.style.alignItems = "center";
        item.style.padding = "5px";
        item.style.borderBottom = "1px solid #eee";

        const message = document.createElement("span");
        message.textContent = "Client non trouvé";
        message.style.color = "red";
        item.appendChild(message);

        const btn = document.createElement("button");
        btn.type = "button";
        btn.innerHTML = '<i class="fas fa-plus-circle" style="color:green;"></i>';
        btn.style.border = "none";
        btn.style.background = "none";
        btn.style.cursor = "pointer";
        btn.addEventListener("mousedown", function(e) {
          e.preventDefault();
          Swal.fire({
            title: "Client non trouvé",
            text: "Voulez-vous ajouter ce client ?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Oui, ajouter",
            cancelButtonText: "Non"
          }).then(result => {
            if (result.isConfirmed) {
              // Appel à une fonction pour ouvrir la pop-up d'ajout de client
              ouvrirPopupClient(input.value, cell.getRow(), cell);
            } else {
              input.focus();
            }
          });
        });
        item.appendChild(btn);
        dropdown.appendChild(item);
      }
      positionDropdown();
      dropdown.style.display = "block";
    }

    // Déclenche la mise à jour du dropdown lors de la saisie et du focus
    input.addEventListener("input", updateDropdown);
    input.addEventListener("focus", updateDropdown);

    // Masquer le dropdown lors du blur avec un léger délai pour permettre le clic
    input.addEventListener("blur", function() {
      setTimeout(() => {
        dropdown.style.display = "none";
        // Si la valeur correspond à une option existante, on valide
        if (options.indexOf(input.value) !== -1) {
          success(input.value);
        }
      }, 150);
    });

    // Au rendu, on met le focus sur l'input et on affiche le dropdown
    onRendered(function() {
      input.focus();
      updateDropdown();
    });

    return container;
  }

 /********** Éditeur pour les listes personnalisées **********/
 function customListEditor(cell, onRendered, success, cancel, editorParams) {
    var container = document.createElement("div");
    container.style.position = "relative";

    var input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.style.boxSizing = "border-box";
    input.placeholder = "Rechercher...";
    input.value = cell.getValue() || "";
    container.appendChild(input);

    var options = [];
    if(editorParams && editorParams.values){
      options = Array.isArray(editorParams.values)
        ? editorParams.values
        : Object.values(editorParams.values);
    }

    // Création d'un dropdown personnalisé pour la recherche
    var dropdown = document.createElement("div");
    dropdown.style.position = "absolute";
    dropdown.style.background = "#fff";
    dropdown.style.border = "1px solid #ccc";
    dropdown.style.maxHeight = "200px";
    dropdown.style.overflowY = "auto";
    dropdown.style.zIndex = "10000";
    dropdown.style.display = "none";
    document.body.appendChild(dropdown);

    function positionDropdown() {
      var rect = input.getBoundingClientRect();
      dropdown.style.top = (rect.bottom + window.scrollY) + "px";
      dropdown.style.left = (rect.left + window.scrollX) + "px";
      dropdown.style.width = rect.width + "px";
    }

    function updateDropdown() {
      dropdown.innerHTML = "";
      var search = input.value.trim().toLowerCase();
      var filtered = options.filter(function(opt){
        return opt.toLowerCase().indexOf(search) !== -1;
      });
      filtered.forEach(function(opt) {
        var item = document.createElement("div");
        item.textContent = opt;
        item.style.padding = "5px";
        item.style.cursor = "pointer";
        item.style.borderBottom = "1px solid #eee";
        item.addEventListener("mousedown", function(e) {
          e.preventDefault();
          input.value = opt;
          dropdown.style.display = "none";
          success(opt);
        });
        dropdown.appendChild(item);
      });
      if(filtered.length > 0){
        positionDropdown();
        dropdown.style.display = "block";
      } else {
        dropdown.style.display = "none";
      }
    }

    input.addEventListener("input", updateDropdown);
    input.addEventListener("focus", updateDropdown);

    // Lorsqu'on appuie sur Enter dans cet éditeur, on valide la saisie
    input.addEventListener("keydown", function(e) {
      if(e.key === "Enter"){
        e.preventDefault();
        dropdown.style.display = "none";
        success(input.value);
      } else if(e.key === "Escape"){
        cancel();
      }
    });

    input.addEventListener("blur", function() {
      setTimeout(function(){
        dropdown.style.display = "none";
        success(input.value);
      },150);
    });

    onRendered(function(){
      input.focus();
      updateDropdown();
    });

    return container;
  }
// Éditeur personnalisé pour le champ "Compte" (Fournisseurs)
function customListEditorFrs(cell, onRendered, success, cancel, editorParams) {
    // Création du container principal pour l'éditeur
    var container = document.createElement("div");
    container.className = "custom-list-editor-container";
    container.style.position = "relative";

    // Création de l'input
    var input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.style.boxSizing = "border-box";
    input.placeholder = "Rechercher...";
    input.value = cell.getValue() || "";
    container.appendChild(input);

    // Préparation du tableau d'options depuis editorParams.values
    var options = [];
    if (editorParams && editorParams.values) {
        // Ici, nous utilisons la fonction manuelle
        options = getFormattedComptesFournisseurs();
    }
    console.log("Options disponibles (contre-partie):", options);

    // Création du dropdown personnalisé
    var dropdown = document.createElement("div");
    dropdown.className = "custom-dropdown";
    dropdown.style.position = "absolute";
    dropdown.style.background = "#fff";
    dropdown.style.border = "1px solid #ccc";
    dropdown.style.maxHeight = "200px";
    dropdown.style.overflowY = "auto";
    dropdown.style.zIndex = "10000";
    dropdown.style.display = "none";
    document.body.appendChild(dropdown);

    function positionDropdown() {
        var rect = input.getBoundingClientRect();
        dropdown.style.top = (rect.bottom + window.scrollY) + "px";
        dropdown.style.left = (rect.left + window.scrollX) + "px";
        dropdown.style.width = rect.width + "px";
    }

    function updateDropdown() {
        dropdown.innerHTML = "";
        var search = input.value.trim().toLowerCase();
        var filtered = options.filter(function(opt) {
            return opt.toLowerCase().indexOf(search) !== -1;
        });
        if (filtered.length > 0) {
            filtered.forEach(function(opt) {
                var item = document.createElement("div");
                item.textContent = opt;
                item.style.padding = "5px";
                item.style.cursor = "pointer";
                item.style.borderBottom = "1px solid #eee";
                item.addEventListener("mousedown", function(e) {
                    e.preventDefault();
                    input.value = opt;
                    dropdown.style.display = "none";
                    success(opt.split(" - ")[0]); // Retourne uniquement le compte
                });
                dropdown.appendChild(item);
            });
        } else {
            var item = document.createElement("div");
            item.style.display = "flex";
            item.style.justifyContent = "space-between";
            item.style.alignItems = "center";
            item.style.padding = "5px";
            item.style.borderBottom = "1px solid #eee";
            var message = document.createElement("span");
            message.textContent = "Fournisseur non trouvé";
            message.style.color = "red";
            item.appendChild(message);
            var btn = document.createElement("button");
            btn.type = "button";
            btn.innerHTML = '<i class="fas fa-plus-circle" style="color:green;"></i>';
            btn.style.border = "none";
            btn.style.background = "none";
            btn.style.cursor = "pointer";
            btn.addEventListener("mousedown", function(e) {
                e.preventDefault();
                Swal.fire({
                    title: "Fournisseur non trouvé",
                    text: "Voulez-vous ajouter ce fournisseur ?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Oui, ajouter",
                    cancelButtonText: "Non"
                }).then((result) => {
                    if (result.isConfirmed) {
                        ouvrirPopupFournisseur(input.value, cell.getRow(), cell, 0);
                    } else {
                        input.focus();
                    }
                });
            });
            item.appendChild(btn);
            dropdown.appendChild(item);
        }
        positionDropdown();
        dropdown.style.display = "block";
    }

    input.addEventListener("input", updateDropdown);
    input.addEventListener("focus", updateDropdown);
    input.addEventListener("blur", function() {
        setTimeout(function(){
            dropdown.style.display = "none";
            if (options.indexOf(input.value) !== -1) {
                success(input.value);
            }
        }, 150);
    });

    onRendered(function() {
        input.focus();
        updateDropdown();
    });

    return container;
}

function openFileSelectionPopup(input) {
    console.log("Ouverture du popup de sélection de fichiers...");
    $.ajax({
      url: '/files', // Votre route qui renvoie la liste des fichiers
      method: 'GET',
      dataType: 'json',
      success: function(files) {
        console.log("Fichiers reçus :", files);
        // Si files est vide ou non défini, utiliser un fallback pour tester
        if (!files || files.length === 0) {
          console.warn("Aucun fichier reçu, utilisation d'un fallback de test.");
          files = [{"name": "TestFile1.pdf"}, {"name": "TestFile2.jpg"}];
        }
        let html = '<ul class="swal2-list-group">';
        files.forEach(function(file) {
          html += `<li class="swal2-list-group-item" data-filename="${file.name}">${file.name}</li>`;
        });
        html += '</ul>';
        Swal.fire({
          title: "Sélectionnez un fichier",
          html: html,
          showCancelButton: true,
          confirmButtonText: 'Valider',
          preConfirm: () => {
            let selected = $('.swal2-list-group-item.active').data('filename');
            if (!selected) {
              Swal.showValidationMessage("Veuillez sélectionner un fichier.");
            }
            return selected;
          },
          didOpen: () => {
            $('.swal2-list-group-item').on('click', function() {
              $('.swal2-list-group-item').removeClass('active');
              $(this).addClass('active');
            });
          }
        }).then((result) => {
          console.log("Résultat du popup :", result);
          if(result.isConfirmed) {
            input.value = result.value;
            // Déclenche le commit (validation) en simulant un blur sur l'input
            input.dispatchEvent(new Event("blur"));
          }
        });
      },
      error: function() {
        Swal.fire("Erreur", "Impossible de charger les fichiers.", "error");
      }
    });
  }

  /**
   * Éditeur personnalisé pour la colonne "Pièce".
   * Affiche un input group (champ texte + bouton "Charger Fichiers").
   * Le bouton ouvre le popup pour sélectionner un fichier.
   */
  function integratedPieceEditor(cell, onRendered, success, cancel, editorParams) {
    // Créer le conteneur input group
    var container = document.createElement("div");
    container.classList.add("input-group");

    // Créer le champ texte
    var textInput = document.createElement("input");
    textInput.type = "text";
    textInput.classList.add("form-control");
    textInput.value = cell.getValue() || "";
    container.appendChild(textInput);

    // Créer le bouton d'upload dans un div d'append
    var appendDiv = document.createElement("div");
    appendDiv.classList.add("input-group-append");

    var uploadButton = document.createElement("button");
    uploadButton.type = "button";
    uploadButton.classList.add("btn", "btn-secondary", "btn-sm");
    uploadButton.textContent = "Charger Fichiers";
    appendDiv.appendChild(uploadButton);
    container.appendChild(appendDiv);

    // Ajouter un log pour vérifier que le bouton est cliqué
    uploadButton.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      console.log("Bouton 'Charger Fichiers' cliqué");
      openFileSelectionPopup(textInput);
    });

    // Dès que l'éditeur est rendu, mettre le focus sur le champ texte
    onRendered(function() {
      textInput.focus();
    });

    // Valider la valeur lorsque le champ perd le focus ou lors de la touche Entrée
    textInput.addEventListener("blur", function() {
      success(textInput.value);
    });
    textInput.addEventListener("keydown", function(e) {
      if(e.key === "Enter") {
        e.preventDefault();
        success(textInput.value);
      } else if(e.key === "Escape") {
        cancel();
      }
    });

    return container;
  }




  /********** Éditeur pour la cellule "Pièce" **********/
  function pieceEditor(cell, onRendered, success, cancel, editorParams) {
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.value = cell.getValue() || "";

    onRendered(() => {
      input.focus();
    });

    // La fonction commit valide la saisie, sélectionne la ligne,
    // et déplace le focus sur la cellule "Sélectionner"
    function commit() {
      success(input.value);
      cell.getRow().select();
      setTimeout(() => {
        let selectCell = cell.getRow().getCell("select");
        if (selectCell) {
          selectCell.getElement().focus();
        }
      }, 50);
    }

    input.addEventListener("blur", commit);
    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        commit();
      } else if (e.key === "Escape") {
        cancel();
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
    function validateAndCommit() {
        // Vous pouvez ajouter des validations supplémentaires si besoin
        success(input.value);
    }

    // Lors du blur, valider la saisie
    input.addEventListener("blur", function() {
        validateAndCommit();
    });

    // Intercepter la touche Entrée pour valider et naviguer
    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            validateAndCommit();
            // Passer à la cellule éditable suivante
            setTimeout(function() {
                focusNextEditableCell(cell);
            }, 50);
        }
    });

    return input;
}


window.tauxTVAGlobal = 0;
// On suppose que ces variables sont définies au chargement de la page
var societeId = $('#societe_id').val(); // ID de la société
var nombreChiffresCompte = parseInt($('#nombre_chiffre_compte').val()); // Nombre de chiffres du compte
// Déclaration globale de la liste des comptes fournisseurs
var comptesFournisseurs = []; // ou avec des valeurs initiales si vous en avez
var comptesVentes = [];

// Fonction d'auto-incrémentation pour le compte fournisseur dans la pop-up
function genererCompteAutoForPopup() {
    $.ajax({
        url: `/get-next-compte/${societeId}?nombre=${nombreChiffresCompte}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                // Remplit le champ "swal-compte" dans la pop-up avec le compte généré
                $('#swal-compte').val(response.nextCompte);
            } else {
                alert('Erreur lors de la génération du compte.');
            }
        },
        error: function() {
            alert('Erreur lors de la génération du compte.');
        }
    });
}


// -------------------------------------------------------------------
// Fonction d'ouverture de la pop-up pour ajouter un fournisseur
// -------------------------------------------------------------------
function ouvrirPopupFournisseur(compteFournisseur, row, cell, tauxTVA) {
    // Assurez-vous que "societeId" est défini (par exemple via une balise meta)
    const societeId = document.querySelector('meta[name="societe_id"]').getAttribute("content");

    Swal.fire({
      title: 'Ajouter un nouveau fournisseur',
      width: '900px',
      html: `
<div style="max-width:800px; margin:auto; padding:25px; border:1px solid #ddd; border-radius:8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); box-sizing:border-box;">
  <div style="display:flex; flex-wrap:wrap; gap:10px; box-sizing:border-box;">
    <!-- Ligne 1 : Compte et Intitulé -->
    <div style="width:calc(50% - 5px); box-sizing:border-box;">
      <label for="swal-compte" style="font-weight:bold; font-size:0.9rem; display:block; margin-bottom:3px;">Compte:</label>
      <input id="swal-compte" class="swal2-input" placeholder="Compte" style="width:100%; height:35px; padding:5px; box-sizing:border-box;" value="">
    </div>
    <div style="width:calc(45% - 5px); box-sizing:border-box;">
      <label for="swal-intitule" style="font-weight:bold; font-size:0.9rem; display:block; margin-bottom:3px;">Intitulé:</label>
      <input id="swal-intitule" class="swal2-input" placeholder="Intitulé" style="width:100%; height:35px; padding:5px; box-sizing:border-box;" required value="${compteFournisseur}">
    </div>

    <!-- Ligne 2 : Identifiant Fiscal et ICE -->
    <div style="width:calc(50% - 5px); box-sizing:border-box;">
      <label for="swal-identifiant" style="font-weight:bold; font-size:0.9rem; display:block; margin-bottom:3px;">Identifiant Fiscal:</label>
      <input id="swal-identifiant" class="swal2-input" placeholder="Identifiant Fiscal" style="width:100%; height:35px; padding:5px; box-sizing:border-box;"
             pattern="^\\d{7,8}$" maxlength="8" title="L'identifiant fiscal doit comporter 7 ou 8 chiffres"
             oninput="this.value = this.value.replace(/[^0-9]/g, '')">
    </div>
    <div style="width:calc(45% - 5px); box-sizing:border-box;">
      <label for="swal-ICE" style="font-weight:bold; font-size:0.9rem; display:block; margin-bottom:3px;">ICE:</label>
      <input id="swal-ICE" class="swal2-input" placeholder="ICE" style="width:100%; height:35px; padding:5px; box-sizing:border-box;"
             pattern="^\\d{15}$" maxlength="15" title="L'ICE doit comporter exactement 15 chiffres"
             oninput="this.value = this.value.replace(/[^0-9]/g, '')">
    </div>

    <!-- Ligne 3 : Nature de l'opération et Contre‑partie -->
    <div style="width:calc(50% - 5px); box-sizing:border-box;">
      <label for="swal-nature_operation" style="font-weight:bold; font-size:0.9rem; display:block; margin-bottom:3px;">Nature de l'opération:</label>
      <select id="swal-nature_operation" class="swal2-input form-select" style="width:100%; height:35px; padding:5px; box-sizing:border-box;">
        <option value="">Sélectionner une option</option>
        <option value="1-Achat de biens d'équipement">1-Achat de biens d'équipement</option>
        <option value="2-Achat de travaux">2-Achat de travaux</option>
        <option value="3-Achat de services">3-Achat de services</option>
      </select>
    </div>
    <div style="width:calc(50% - 5px); box-sizing:border-box;">
      <label for="swal-contre-partie" style="font-weight:bold; font-size:0.9rem; display:block; margin-bottom:3px;">Contre‑partie:</label>
      <select id="swal-contre-partie" class="swal2-input" style="width:100%; height:35px; padding:5px; box-sizing:border-box;">
        <option value="">Sélectionner</option>
        <!-- Ajoutez ici vos options -->
      </select>
    </div>

    <!-- Ligne 4 : Rubrique TVA et Désignation -->
    <div style="width:calc(50% - 5px); box-sizing:border-box;">
      <label for="swal-rubrique" style="font-weight:bold; font-size:0.9rem; display:block; margin-bottom:3px;">Rubrique TVA:</label>
      <select id="swal-rubrique" class="swal2-input" style="width:100%; height:35px; padding:5px; box-sizing:border-box;">
        <option value="">Sélectionner</option>
        <!-- Ajoutez ici vos options -->
      </select>
    </div>
    <div style="width:calc(45% - 5px); box-sizing:border-box;">
      <input id="swal-designation" class="swal2-input" placeholder="Désignation" style="width:100%; height:35px; padding:5px; box-sizing:border-box;">
    </div>
  </div>
</div>

      `,
      didOpen: () => {
        // Auto-incrémente le compte dans le champ "swal-compte"
        if (typeof genererCompteAutoForPopup === "function") {
          genererCompteAutoForPopup();
        }

        // Remplir les listes si les fonctions correspondantes existent
        if (typeof remplirRubriquesTva === "function") {
          remplirRubriquesTva('swal-rubrique');
        }
        if (typeof remplirContrePartie === "function") {
          remplirContrePartie('swal-contre-partie');
        }

        // Initialiser Select2 sur les éléments <select> en forçant le dropdown à rester dans la pop-up
        var dropdownParent = $('.swal2-container');

        var selectRubrique = $('#swal-rubrique');
        if (selectRubrique.length) {
          selectRubrique.select2({ dropdownParent: dropdownParent });
        }

        var selectContrePartie = $('#swal-contre-partie');
        if (selectContrePartie.length) {
          selectContrePartie.select2({ dropdownParent: dropdownParent });
        }

        // Initialisation de Select2 pour le select "swal-nature_operation"
        var selectNature = $('#swal-nature_operation');
        if (selectNature.length) {
          selectNature.select2({
            dropdownParent: $('.swal2-container'),
            width: '100%'
          });
        }
      },
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: 'Ajouter',
      preConfirm: () => {
        return {
          compte: $('#swal-compte').val(),
          intitule: $('#swal-intitule').val(),
          identifiant_fiscal: $('#swal-identifiant').val(),
          ICE: $('#swal-ICE').val(),
          // Pour rubrique_tva, on récupère le texte de l'option sélectionnée
          rubrique_tva: $('#swal-rubrique option:selected').text(),
          contre_partie: $('#swal-contre-partie').val(),
          nature_operation: $('#swal-nature_operation').val(),
          designation: $('#swal-designation').val(),
          societe_id: societeId
        };
      }
    }).then((result) => {
      if (result.isConfirmed && result.value) {
        fetch('/fournisseurs', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          body: JSON.stringify(result.value)
        })
        .then(response => response.json())
        .then(newFournisseur => {
          if (newFournisseur.error) {
            Swal.fire('Erreur', newFournisseur.error, 'error');
          } else {
            const fournisseurCree = newFournisseur.fournisseur;
            let newTauxTVA = 0;
            if (fournisseurCree.rubrique_tva) {
              let match = fournisseurCree.rubrique_tva.match(/\(([\d\.]+)%\)/) ||
                          fournisseurCree.rubrique_tva.match(/([\d\.]+)%\s*$/);
              if (match && match[1]) {
                let valeurExtraite = parseFloat(match[1]);
                newTauxTVA = (valeurExtraite < 1) ? valeurExtraite : valeurExtraite / 100;
              }
            }
            window.tauxTVAGlobal = newTauxTVA;
            const newValue = `${fournisseurCree.compte} - ${fournisseurCree.intitule}`;
            // Ajout à la liste globale des fournisseurs
            if (typeof window.comptesFournisseurs !== "undefined") {
              window.comptesFournisseurs.push(newValue);
            }
            cell.setValue(newValue);
            const numeroFacture = row.getCell("numero_facture").getValue() || "Inconnu";
            row.update({
              contre_partie: fournisseurCree.contre_partie,
              rubrique_tva: fournisseurCree.rubrique_tva,
              taux_tva: newTauxTVA,
              libelle: `F° ${numeroFacture} ${fournisseurCree.intitule}`,
              compte_tva: (window.comptesVentes && window.comptesVentes.length > 0)
                ? `${window.comptesVentes[0].compte} - ${window.comptesVentes[0].intitule}`
                : ""
            });
            Swal.fire('Succès', 'Fournisseur ajouté avec succès', 'success').then(() => {
              const creditCell = row.getCell("credit");
              if (creditCell) {
                setTimeout(() => { creditCell.edit(); }, 200);
              }
            });
          }
        })
        .catch(error => {
          console.error('Erreur lors de l’ajout du fournisseur:', error);
          Swal.fire('Erreur', 'Une erreur est survenue lors de l’ajout du fournisseur.', 'error');
        });
      }
    });
  }


  // Fonction éditeur personnalisé pour le plan comptable
// Fonction éditeur personnalisé pour le plan comptable
function customListEditorPlanComptable(cell, onRendered, success, cancel, editorParams) {
    // Création du container principal
    var container = document.createElement("div");
    container.className = "custom-list-editor-container";
    container.style.position = "relative";

    // Création de l'input
    var input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.style.boxSizing = "border-box";
    input.placeholder = "Rechercher...";
    input.value = cell.getValue() || "";
    container.appendChild(input);

    // Initialisation du tableau d'options (sera rempli par l'appel async)
    var options = [];

    // Création du dropdown personnalisé (ajouté au body pour éviter les problèmes de overflow)
    var dropdown = document.createElement("div");
    dropdown.className = "custom-dropdown";
    dropdown.style.position = "absolute";
    dropdown.style.background = "#fff";
    dropdown.style.border = "1px solid #ccc";
    dropdown.style.maxHeight = "200px";
    dropdown.style.overflowY = "auto";
    dropdown.style.zIndex = "10000";
    dropdown.style.display = "none"; // caché par défaut
    document.body.appendChild(dropdown);

    // Positionnement du dropdown sous l'input
    function positionDropdown() {
        var rect = input.getBoundingClientRect();
        dropdown.style.top = (rect.bottom + window.scrollY) + "px";
        dropdown.style.left = (rect.left + window.scrollX) + "px";
        dropdown.style.width = rect.width + "px";
    }

    // Mise à jour du contenu du dropdown en fonction de la saisie
    function updateDropdown() {
        dropdown.innerHTML = "";
        var search = input.value.trim().toLowerCase();
        var filtered = options.filter(function(opt) {
            return opt.toLowerCase().indexOf(search) !== -1;
        });

        if (filtered.length > 0) {
            filtered.forEach(function(opt) {
                var item = document.createElement("div");
                item.textContent = opt;
                item.style.padding = "5px";
                item.style.cursor = "pointer";
                item.style.borderBottom = "1px solid #eee";
                item.addEventListener("mousedown", function(e) {
                    e.preventDefault();
                    input.value = opt;
                    dropdown.style.display = "none";
                    success(opt);
                });
                dropdown.appendChild(item);
            });
        } else {
            // Aucun résultat : affichage du message et du bouton pour ajouter un compte
            var item = document.createElement("div");
            item.style.display = "flex";
            item.style.justifyContent = "space-between";
            item.style.alignItems = "center";
            item.style.padding = "5px";
            item.style.borderBottom = "1px solid #eee";

            var message = document.createElement("span");
            message.textContent = "Compte non trouvé";
            message.style.color = "red";
            item.appendChild(message);

            var btn = document.createElement("button");
            btn.type = "button";
            btn.innerHTML = '<i class="fas fa-plus-circle" style="color:green;"></i>';
            btn.style.border = "none";
            btn.style.background = "none";
            btn.style.cursor = "pointer";
            btn.addEventListener("mousedown", function(e) {
                e.preventDefault();
                Swal.fire({
                    title: "Compte non trouvé",
                    text: "Voulez-vous ajouter ce compte ?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Oui, ajouter",
                    cancelButtonText: "Non"
                }).then((result) => {
                    if (result.isConfirmed) {
                        ouvrirPopupPlanComptable(input.value, cell.getRow(), cell);
                    } else {
                        input.focus();
                    }
                });
            });
            item.appendChild(btn);
            dropdown.appendChild(item);
        }
        positionDropdown();
        dropdown.style.display = "block";
    }

    // Événements sur l'input
    input.addEventListener("input", function() {
        updateDropdown();
    });
    input.addEventListener("focus", function() {
        updateDropdown();
    });
    input.addEventListener("blur", function() {
        setTimeout(function(){
            dropdown.style.display = "none";
            if (options.indexOf(input.value) !== -1) {
                success(input.value);
            }
        }, 150);
    });

    // Au rendu, on récupère les options via la fonction asynchrone valuesLookup
    onRendered(function() {
        input.focus();
        if (editorParams && typeof editorParams.valuesLookup === "function") {
            editorParams.valuesLookup(cell)
                .then(function(result) {
                    options = result;
                    console.log("Options récupérées :", options);
                    updateDropdown();
                })
                .catch(function(err) {
                    console.error("Erreur lors de la récupération des options :", err);
                    updateDropdown();
                });
        } else {
            updateDropdown();
        }
    });

    return container;
}

// Définition de la colonne "Contre-Partie"
var contrePartieColumn = {
    title: "Contre-Partie",
    field: "contre_partie",
    headerFilter: "input",
    headerFilterParams: {
        elementAttributes: {
            style: "width: 85px; height: 25px;"
        }
    },
    editor: customListEditorPlanComptable,
    editorParams: {
        autocomplete: true,
        listOnEmpty: true,
        // Fonction asynchrone qui récupère et fusionne les données
        valuesLookup: async function(cell) {
            const societeId = $('#societe_id').val();
            if (!societeId) {
                alert("Veuillez sélectionner une société avant de modifier la Contre-Partie.");
                return [];
            }
            try {
                let fournisseurData = [];
                let codeJournalData = [];
                let planComptableData = [];

                // Récupération des contre-parties fournisseurs
                const fournisseurResponse = await fetch(`/get-all-contre-parties?societe_id=${societeId}`);
                if (fournisseurResponse.ok) {
                    const data = await fournisseurResponse.json();
                    fournisseurData = data.map(item => item.contre_partie);
                } else {
                    console.error("Erreur lors de la récupération des contre-parties fournisseurs.");
                }

                // Récupération des contre-parties liées au code journal (si défini)
                if (typeof selectedCodeJournal !== "undefined" && selectedCodeJournal) {
                    const codeJournalResponse = await fetch(`/get-contre-parties?code_journal=${selectedCodeJournal}`);
                    if (codeJournalResponse.ok) {
                        const data = await codeJournalResponse.json();
                        codeJournalData = data.map(item => item.contre_partie);
                    } else {
                        console.error("Erreur lors de la récupération des contre-parties du code journal.");
                    }
                }

                // Récupération de la liste des comptes du plan comptable
                const planComptableResponse = await fetch(`/get-plan-comptable?societe_id=${societeId}`);
                if (planComptableResponse.ok) {
                    const data = await planComptableResponse.json();
                    planComptableData = data.map(item => item.compte);
                } else {
                    console.error("Erreur lors de la récupération des comptes du plan comptable.");
                }

                // Fusion des listes en supprimant les doublons
                let mergedData = [...new Set([...fournisseurData, ...codeJournalData, ...planComptableData])];

                // Priorisation : si un code journal est sélectionné et renvoie des données, placer sa contre-partie en premier
                if (typeof selectedCodeJournal !== "undefined" && selectedCodeJournal && codeJournalData.length > 0) {
                    let cpJournal = codeJournalData[0]; // on prend la première valeur
                    const index = mergedData.indexOf(cpJournal);
                    if (index > -1) {
                        mergedData.splice(index, 1);
                    }
                    mergedData.unshift(cpJournal);
                }
                // Sinon, si un compte fournisseur est sélectionné, le placer en tête
                else if (typeof selectedFournisseur !== "undefined" && selectedFournisseur) {
                    const index = mergedData.indexOf(selectedFournisseur);
                    if (index > -1) {
                        mergedData.splice(index, 1);
                    }
                    mergedData.unshift(selectedFournisseur);
                }

                console.log("Liste fusionnée des contre-parties :", mergedData);
                return mergedData;
            } catch (error) {
                console.error("Erreur lors de la récupération des contre-parties :", error);
                alert("Impossible de récupérer les contre-parties.");
                return [];
            }
        }
    },
    cellEdited: function(cell) {
        console.log("Contre-Partie mise à jour :", cell.getValue());
    }
};







  function ouvrirPopupPlanComptable(compteInitial, row, cell) {
    // Récupérer l'identifiant de la société via une balise meta
    const societeId = document.querySelector('meta[name="societe_id"]').getAttribute("content");

    Swal.fire({
      title: 'Ajouter un compte',
      width: '800px',
      html: `
           <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
        <div style="display: flex; align-items: center; gap: 5px;">
          <label for="swal-compte" style="font-weight: bold; font-size: 0.9rem; white-space: nowrap;">Compte:</label>
          <input id="swal-compte" class="swal2-input" placeholder="Compte" style="width: 200px; height: 35px;" value="${compteInitial || ''}">
        </div>
        <div style="display: flex; align-items: center; gap: 5px;">
          <label for="swal-intitule" style="font-weight: bold; font-size: 0.9rem; white-space: nowrap;">Intitulé:</label>
          <input id="swal-intitule" class="swal2-input" placeholder="Intitulé" style="width: 200px; height: 35px;" required>
        </div>
      </div>
      `,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: 'Ajouter',
      preConfirm: () => {
        return {
          compte: document.getElementById('swal-compte').value,
          intitule: document.getElementById('swal-intitule').value,
          societe_id: societeId
        };
      }
    }).then((result) => {
      if (result.isConfirmed && result.value) {
        fetch('/plancomptable', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(result.value)
        })
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            Swal.fire('Erreur', data.error, 'error');
          } else {
            // Mise à jour de la cellule avec le compte seulement
            cell.setValue(data.data.compte);
            Swal.fire('Succès', data.message, 'success');
          }
        })
        .catch(error => {
          console.error('Erreur:', error);
          Swal.fire('Erreur', "Une erreur est survenue lors de l'enregistrement.", 'error');
        });
      }
    });
  }



document.addEventListener("DOMContentLoaded", function () {
    // Liste des sections
    const sections = ["achats", "ventes", "Caisse", "Banque","operations-diverses"];

    // Fonction pour initialiser une section
    function initializeSection(section) {
        const radioMois = document.getElementById(`filter-mois-${section}`);
        const radioExercice = document.getElementById(`filter-exercice-${section}`);
        const periodeContainer = document.getElementById(`periode-${section}`);
        const anneeInput = document.getElementById(`annee-${section}`);

        // Vérification des éléments requis
        if (!radioMois || !radioExercice || !periodeContainer || !anneeInput) {
            console.warn(`Certains éléments de la section "${section}" sont manquants.`);
            return; // Sortir si des éléments sont introuvables
        }

        // Fonction pour mettre à jour l'affichage
        function updateDisplay() {
            if (radioMois.checked) {
                periodeContainer.style.display = "inline-block";
                anneeInput.style.display = "none";
            } else if (radioExercice.checked) {
                periodeContainer.style.display = "none";
                anneeInput.style.display = "inline-block";
            }
        }

        // Ajouter des écouteurs d'événements pour les boutons radio
        radioMois.addEventListener("change", updateDisplay);
        radioExercice.addEventListener("change", updateDisplay);

        // Initialiser l'affichage au chargement
        updateDisplay();
    }

    // Initialiser toutes les sections
    sections.forEach(initializeSection);
});

        // Déclaration des tables Tabulator
var tableAch, tableVentes, tableBanque, tableCaisse,tableOP;
// Liste des mois en anglais et en français
const moisAnglais = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
const moisFrancais = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

// Fonction pour charger les exercices sociaux et les périodes
function loadExerciceSocialAndPeriodes() {
    const sessionSocialRequest = $.get('/session-social');
    const periodesRequest = $.get('/periodes');

    $.when(sessionSocialRequest, periodesRequest)
        .done(function (sessionSocialResponse, periodesResponse) {
            const sessionSocialData = sessionSocialResponse[0];
            const periodesData = periodesResponse[0];

            if (!sessionSocialData || !sessionSocialData.exercice_social_debut) {
                console.error('Données de l\'exercice social invalides.');
                return;
            }

            if (!Array.isArray(periodesData) || periodesData.length === 0) {
                console.error('Les périodes reçues sont invalides ou vides.');
                return;
            }

            const anneeDebut = new Date(sessionSocialData.exercice_social_debut).getFullYear();
            $('#annee-achats').val(anneeDebut);
            $('#annee-ventes').val(anneeDebut);
            $('#annee-Caisse').val(anneeDebut);
            $('#annee-Banque').val(anneeDebut);
          $('#annee-operations-diverses').val(anneeDebut);

            // Peupler les périodes pour tous les onglets
            populateMonths('achats', periodesData);
            populateMonths('ventes', periodesData);
            populateMonths('Banque', periodesData);
            populateMonths('Caisse', periodesData);
            populateMonths('operations-diverses', periodesData);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors du chargement des données :', textStatus, errorThrown);
        });
}

// Fonction pour peupler les périodes dans le select de chaque onglet
function populateMonths(onglet, periodes) {
    const periodeSelect = $(`#periode-${onglet}`);
    const previousSelection = periodeSelect.data('selected');

    periodeSelect.empty();

    // Ajouter l'option "Sélectionner un mois" comme option par défaut
    periodeSelect.append('<option value="selectionner un mois">Sélectionner un mois</option>');

    // Ajouter les options de mois et années
    periodes.forEach(function (periode) {
        const [moisEnAnglais, annee] = periode.split(' ');

        if (moisEnAnglais && annee) {
            const moisIndex = moisAnglais.indexOf(moisEnAnglais);
            if (moisIndex !== -1) {
                const moisNom = moisFrancais[moisIndex];
                const optionValue = `${(moisIndex + 1).toString().padStart(2, '0')}-${annee}`;
                const optionText = `${moisNom} ${annee}`;

                periodeSelect.append(`<option value="${optionValue}">${optionText}</option>`);
            } else {
                console.error('Mois anglais inconnu:', moisEnAnglais);
            }
        } else {
            console.error('Format de la période incorrect:', periode);
        }
    });

    // Si aucune sélection précédente n'existe, laisser "Sélectionner un mois" sélectionné
    if (previousSelection) {
        periodeSelect.val(previousSelection);
    } else {
        // S'assurer que l'option "Sélectionner un mois" reste sélectionnée
        periodeSelect.val('selectionner un mois');
    }

    console.log("Options ajoutées dans #" + onglet + ":", periodeSelect.html());
}

// Fonction pour mettre à jour la date dans toutes les tables Tabulator
function updateTabulatorDate(year, month) {
    const formattedDate = `${year}-${month.padStart(2, '0')}-01`;

    // Met à jour la date dans toutes les tables Tabulator
    [tableAch, tableVentes, tableBanque,tableCaisse,tableOP].forEach(function(table) {
        table.updateData(table.getData().map(row => ({
            ...row,
            date: formattedDate,
        })));
    });
}

// Fonction de gestion des changements dans le select
function setupPeriodChangeHandler(onglet) {
    $(`#periode-${onglet}`).on('change', function () {
        const selectedValue = $(this).val();
        const selectedText = $(this).find("option:selected").text();

        console.log('Valeur sélectionnée pour ' + onglet + ':', selectedValue);
        console.log('Texte sélectionné pour ' + onglet + ':', selectedText);

        $(this).data('selected', selectedValue);

        if (selectedValue) {
            const selectedYear = selectedValue.split('-')[1];
            const selectedMonth = selectedValue.split('-')[0];
            $(`#annee-${onglet}`).val(selectedYear);
            updateTabulatorDate(selectedYear, selectedMonth);
        }
    });
}

// Fonction de gestion des changements dans les boutons radio
function setupFilterEventHandlers() {
    // Liste des onglets à gérer
    const onglets = ['Achats', 'Ventes', 'Banque','Caisse','Operations-diverses'];

    // Parcourir chaque onglet et mettre en place un gestionnaire pour chaque
    onglet => {
        // Sélectionner les boutons radio associés à chaque onglet
        $(`input[name="filter-period-${onglet}"]`).on('change', function () {
            if ($(this).val() === 'mois') {
                $(`#periode-${onglet}`).show();
                $(`#annee-${onglet}`).hide();
            } else if ($(this).val() === 'exercice') {
                $(`#periode-${onglet}`).hide();
                $(`#annee-${onglet}`).show();
            }
        });
    };
}

// Initialisation de la fonction
$(document).ready(function () {
    loadExerciceSocialAndPeriodes();
    setupFilterEventHandlers();
    ['achats', 'ventes', 'Banque','Caisse','operations'].forEach(onglet => {
        setupPeriodChangeHandler(onglet);
    });
});




var tableAch, tableVentes, tableBanque,tableCaisse,tableOP;

       $(document).ready(function () {

// Fonction pour charger les journaux dans le select
function loadJournaux(typeJournal, selectId) {
    $.ajax({
        url: '/journaux-' + typeJournal,
        method: 'GET',
        success: function (data) {
            if (data.error) {
                console.error(data.error);
                return;
            }
            $(selectId).empty();
            $(selectId).append('<option value="">Sélectionner un journal</option>');
            data.forEach(function (journal) {
                $(selectId).append(
                    '<option value="' +
                        journal.code_journal +
                        '" data-type="' +
                        journal.type_journal +
                        '" data-intitule="' +
                        journal.intitule +
                        '">' +
                        journal.code_journal + // Affiche le code_journal avec le intitule
                        '</option>'
                );
            });
        },
        error: function (err) {
            console.error('Erreur lors du chargement des journaux', err);
        },
    });
}

// Vérifier si un journal est sélectionné
function checkJournalSelection() {
    const selectedJournal = $('#journal-achats').val() || $('#journal-ventes').val() || $('#journal-Banque').val() || $('#journal-Caisse').val() || $('#journal-operations-diverses').val();

    if (!selectedJournal) {
        // Afficher l'alerte si aucun journal n'est sélectionné
        alert('Veuillez renseigner le code journal avant de continuer.');
        return false; // Empêcher l'action suivante
    }
    return true; // Permet de continuer si un journal est sélectionné
}

// Gestion des changements d'input (autre que le code_journal)
$('input, select').on('change', function () {
    if (!checkJournalSelection()) {
        // Empêcher l'action de changement si le code journal n'est pas sélectionné
        // Vous pouvez aussi ajouter ici un focus sur le select journal si nécessaire
    }
});

// Charger les journaux pour chaque onglet
loadJournaux('achats', '#journal-achats');
loadJournaux('ventes', '#journal-ventes');
loadJournaux('Banque', '#journal-Banque');
loadJournaux('Caisse', '#journal-Caisse');
loadJournaux('operations-diverses', '#journal-operations-diverses');

// Gestion des changements de journal
$('select').on('change', function () {
    const selectedOption = $(this).find(':selected');
    const intituleJournal = selectedOption.data('intitule');
    const tabId = $(this).attr('id').replace('journal-', 'filter-intitule-');
    $('#' + tabId).val(intituleJournal ? 'journal - ' + intituleJournal : '');
});

class TabulatorManager {
    constructor(journalSelectors) {
        this.journalSelectors = journalSelectors;
        this.initEventListeners();
    }

    // Vérifie si un journal est sélectionné
    isJournalSelected() {
        return this.journalSelectors.some(selector => $(selector).val());
    }

    // Affiche une alerte et empêche l'action si aucun journal n'est sélectionné
    validateJournalSelection() {
        if (!this.isJournalSelected()) {
            alert("Veuillez renseigner le code journal avant de continuer.");
            return false;
        }
        return true;
    }

    // Empêche l'édition d'une cellule si aucun journal n'est sélectionné
    preventEditingIfNoJournal(cell) {
        if (!this.validateJournalSelection()) {
            cell.cancelEdit();
            return false;
        }
    }

    // Applique la validation à une instance Tabulator donnée
    applyToTabulator(tableInstance) {
        tableInstance.on("cellEditing", (cell) => this.preventEditingIfNoJournal(cell));
    }

    // Ajoute un écouteur global pour empêcher les actions si aucun journal n'est sélectionné
    initEventListeners() {
        $("input, select").on("change", () => this.validateJournalSelection());
    }
}
// Liste des sélecteurs des journaux (à adapter selon votre projet)
const journalSelectors = [
    '#journal-achats',
    '#journal-ventes',
    '#journal-Banque',
    '#journal-Caisse',
    '#journal-operations-diverses'
];
// Initialiser le gestionnaire de Tabulator avec les sélecteurs des journaux
const tabulatorManager = new TabulatorManager(journalSelectors);



const { DateTime } = luxon;

document.addEventListener("DOMContentLoaded", function(){





    // Mettre à jour window.filterAchats lors du changement de sélection
    const radios = document.querySelectorAll('input[name="filter-achats"]');
    radios.forEach(radio => {
        radio.addEventListener("change", function(){
            window.filterAchats = this.value;
            console.log("Nouveau filtre sélectionné :", window.filterAchats);
        });
    });

    // Initialiser la variable globale avec la valeur du radio checked au chargement
    window.filterAchats = document.querySelector('input[name="filter-achats"]:checked')?.value || "";
});





// Fonction pour récupérer les rubriques TVA
async function fetchRubriquesTva() {
    const [ventesResponse, achatsResponse] = await Promise.all([
        fetch('/get-rubriques-tva-vente').then(res => res.json()),
        fetch('/get-rubriques-tva').then(res => res.json())
    ]);

    const ventes = ventesResponse.rubriques ? Object.values(ventesResponse.rubriques).flatMap(r => r.rubriques.map(rubrique => `${rubrique.Num_racines} - ${rubrique.Nom_racines} (${rubrique.Taux}%)`)) : [];
    const achats = achatsResponse.rubriques ? Object.values(achatsResponse.rubriques).flatMap(r => r.rubriques.map(rubrique => `${rubrique.Num_racines} - ${rubrique.Nom_racines} (${rubrique.Taux}%)`)) : [];

    return { ventes, achats };
}

// Fonction pour récupérer les comptes TVA
async function fetchComptesTva() {
    const [ventes, achats] = await Promise.all([
        fetch('/get-compte-tva-vente').then(res => res.json()),
        fetch('/get-compte-tva-ach').then(res => res.json())
    ]);

    return { ventes, achats };
}

// Initialisation des tables après récupération des données
// Fonction d'initialisation de la table et des données
    (async function initTables() {
        try {
            // Récupération d'autres données (rubriques, comptes TVA, etc.)
            const { ventes: rubriquesVentes, achats: rubriquesAchats } = await fetchRubriquesTva();
            const { ventes: comptesVentes, achats: comptesAchats } = await fetchComptesTva();

            // Récupération des clients (si nécessaire)
            const clients = await fetch(`/get-clients?societe_id=${societeId}`).then(res => res.json());
            window.clients = clients; // Pour y accéder globalement si besoin
            window.comptesClients = clients.map(client => `${client.compte} - ${client.intitule}`);

            // Récupération des fournisseurs avec détails (les données brutes attendues)
            const fournisseurs = await fetch(`/get-fournisseurs-avec-details?societe_id=${societeId}`)
                .then(res => res.json());

            // Stocker globalement les données brutes pour utilisation ultérieure
            window.comptesFournisseurs = fournisseurs;

            // Créer une version formatée pour l'éditeur (tableau de chaînes "compte - intitule")
            window.formattedComptesFournisseurs = getFormattedComptesFournisseurs();

            console.log("comptesFournisseurs:", window.comptesFournisseurs);
            console.log("formattedComptesFournisseurs:", window.formattedComptesFournisseurs);

                // Fonction pour formater les valeurs en monnaie
                function formatCurrency(value) {
                    return parseFloat(value).toFixed(2);
                  }


                let numeroIncrementGlobal = 1; // Compteur global pour les pièces justificatives

                let selectedCodeJournal = null; // Stocker le code journal sélectionné

                // Récupérer le code journal lorsqu'il change dans le dropdown
                document.getElementById("journal-achats").addEventListener("change", function () {
                    selectedCodeJournal = this.value; // Mettre à jour la variable globale
                    console.log("Code journal sélectionné :", selectedCodeJournal);
                });


// Variables globales pour le suivi de la valeur précédente et du mode couleur
let lastPiece = null;
let toggle = false;
        // Table des achats
        var tableAch = new Tabulator("#table-achats", {
            height: "500px",
            layout: "fitColumns",
            // reactiveData: true,
            rowHeight: 30, // définit la hauteur de ligne à 30px

           clipboard:true,
           clipboardPasteAction:"replace",
           placeholder: "Aucune donnée disponible",
            // Regrouper les lignes par numero_facture

    ajaxResponse: function(url, params, response) {
        console.log("Données reçues :", response);

        // Ajouter une ligne vide si elle n'est pas déjà présente
        if (response.length === 0 || response[0].id !== "") {
            response.unshift({
                id: "",
                date: "",
                debit: "",
                credit: "",
            });
        }

        return response; // Passe les données modifiées à Tabulator
    },
    ajaxError: function(xhr, textStatus, errorThrown) {
        console.error("Erreur AJAX :", textStatus, errorThrown);
    },
            printAsHtml:true,
            printHeader:"<h1>Table Achats<h1>",
            printFooter:"<h2>Example Table Footer<h2>",
            selectable: true,
            footerElement: "<table style='width: 15%; margin-top: 6px; border-collapse: collapse;'>" +
    "<tr>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px;'>Cumul Débit :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 10px;'><span id='cumul-debit-achats'></span></td>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px;'>Cumul Crédit :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 10px;'><span id='cumul-credit-achats'></span></td>" +
    "</tr>" +
    "<tr>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px;'>Solde Débiteur :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 10px;'><span id='solde-debit-achats'></span></td>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px;'>Solde Créditeur :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 10px;'><span id='solde-credit-achats'></span></td>" +
    "</tr>" +
"</table>",  // Footer sous forme de tableau avec des styles inline
            // data: Array(1).fill({}),

            columns: [
                { title: "ID", field: "id", visible: false },

                {
                    title: "Date",
                    field: "date",
                    hozAlign: "center",
                    headerFilter: "input",
                    headerFilterParams: {
                        elementAttributes: {
                            style: "width: 80px; height: 25px;" // 80 pixels de large
                        }
                    },
                    sorter: "date",
                    editor: function (cell, onRendered, success, cancel) {
                        // Création d'un conteneur pour l'éditeur
                        const container = document.createElement("div");
                        container.style.display = "flex";
                        container.style.alignItems = "center";

                        // Création de l'input pour saisir la date (jour seulement, le mois et l'année étant gérés via des filtres externes)
                        const input = document.createElement("input");
                        input.type = "text";
                        input.style.flex = "1";
                        input.placeholder = "jj/";

                        // Si une date existe déjà, on l'affiche au format "dd/MM/yyyy"
                        const currentValue = cell.getValue();
                        if (currentValue) {
                            let dt = luxon.DateTime.fromFormat(currentValue, "yyyy-MM-dd HH:mm:ss");
                            if (!dt.isValid) {
                                dt = luxon.DateTime.fromISO(currentValue);
                            }
                            if (dt.isValid) {
                                input.value = dt.toFormat("dd/MM/yyyy");
                            }
                        }

                        // Fonction de validation et de commit de la valeur saisie
                        function validateAndCommit() {
                            // Récupération des filtres ou champs externes pour le mois, l'année et le code journal
                            const moisSelect = document.getElementById("periode-achats");
                            const anneeInput = document.getElementById("annee-achats");
                            const codeJournalInput = document.getElementById("journal-achats");

                            // Extraction des valeurs saisies
                            const dayStr = input.value.slice(0, 2);
                            const day = parseInt(dayStr, 10);
                            const month = parseInt(moisSelect.value, 10);
                            const year = parseInt(anneeInput.value, 10);
                            const codeJournal = (codeJournalInput.value || "CJ").trim();

                            if (!isNaN(day) && !isNaN(month) && !isNaN(year)) {
                                const dt = luxon.DateTime.local(year, month, day);
                                if (dt.isValid) {
                                    // Récupération de la table et de ses lignes
                                    const table = cell.getTable();
                                    const rows = table.getRows();
                                    const currentRow = cell.getRow();
                                    const rowData = currentRow.getData();
                                    const numeroFacture = rowData.numero_facture;

                                    // Calculer les totaux débit et crédit pour toutes les lignes de la même facture
                                    let totalDebit = 0, totalCredit = 0;
                                    const lignesFacture = rows.filter(r => {
                                        const data = r.getData();
                                        return data.numero_facture === numeroFacture;
                                    });
                                    lignesFacture.forEach(r => {
                                        const data = r.getData();
                                        totalDebit += parseFloat(data.debit) || 0;
                                        totalCredit += parseFloat(data.credit) || 0;
                                    });

                                    // Si la facture est équilibrée (et non nulle)
                                    if (totalDebit === totalCredit && totalDebit !== 0) {
                                        const moisFormatted = dt.toFormat("MM");
                                        const prefix = `P${moisFormatted}${codeJournal}`;

                                        // Rechercher les numéros déjà attribués pour ce mois et ce code journal
                                        const existingNumbers = [];
                                        rows.forEach(r => {
                                            const data = r.getData();
                                            if (data.piece_justificative && data.piece_justificative.startsWith(prefix)) {
                                                const numStr = data.piece_justificative.substring(prefix.length);
                                                const num = parseInt(numStr, 10);
                                                if (!isNaN(num)) {
                                                    existingNumbers.push(num);
                                                }
                                            }
                                        });
                                        existingNumbers.sort((a, b) => a - b);
                                        const newIncrement = existingNumbers.length > 0 ? existingNumbers[existingNumbers.length - 1] + 1 : 1;
                                        const numeroFormate = String(newIncrement).padStart(4, "0");
                                        const pieceJustificative = `P${moisFormatted}${codeJournal}${numeroFormate}`;

                                        // Mettre à jour la pièce justificative pour la ligne en cours
                                        currentRow.update({ piece_justificative: pieceJustificative });
                                        console.log("Nouvelle pièce générée :", pieceJustificative);
                                    }

                                    // Retourner la date au format stocké (par exemple "yyyy-MM-dd HH:mm:ss")
                                    success(dt.toFormat("yyyy-MM-dd HH:mm:ss"));
                                    return true;
                                }
                            }

                            // En cas de saisie invalide, annuler la modification
                            alert("Veuillez saisir une date valide.");
                            cancel();
                            return false;
                        }

                        // Validation lors du blur (perte de focus)
                        input.addEventListener("blur", validateAndCommit);

                        // Gestion de la touche Entrée pour valider et passer à la cellule suivante
                        input.addEventListener("keydown", function (e) {
                            if (e.key === "Enter") {
                                e.preventDefault();
                                if (validateAndCommit()) {
                                    // Après validation, passer à la cellule éditable suivante
                                    setTimeout(() => {
                                        focusNextEditableCell(cell);
                                    }, 50);
                                }
                            }
                        });

                        container.appendChild(input);
                        onRendered(() => input.focus());
                        return container;
                    },
                    formatter: function (cell) {
                        const dateValue = cell.getValue();
                        if (dateValue) {
                            let dt = luxon.DateTime.fromFormat(dateValue, "yyyy-MM-dd HH:mm:ss");
                            if (!dt.isValid) {
                                dt = luxon.DateTime.fromISO(dateValue);
                            }
                            return dt.isValid ? dt.toFormat("dd/MM/yyyy") : dateValue;
                        }
                        return "";
                    },
                },


                    {
                        title: "N° facture",
                        field: "numero_facture",
                        headerFilter: "input",
                        headerFilterParams: {
                            elementAttributes: {
                                style: "width: 80px; height: 25px;" // 80 pixels de large
                            }
                        },
                        editor: genericTextEditor
                    },

// Configuration de la colonne "Compte" dans Tabulator
{
    title: "Compte",
    field: "compte",
    headerFilter: "input",
    headerFilterParams: {
        elementAttributes: { style: "width: 85px; height: 25px;" }
    },
    editor: function(cell, onRendered, success, cancel) {
        let currentFilter = document.querySelector('input[name="filter-achats"]:checked')?.value;
        console.log("Editor - filter-achats =", currentFilter);

        if (currentFilter === 'libre') {
            // Mode libre : éditeur <select> affichant "compte - intitule"
            let editor = document.createElement("select");
            editor.style.width = "100%";
            editor.style.height = "100%";
            fetch('/fournisseurs-comptes')
                .then(response => response.json())
                .then(data => {
                    data.forEach(compteObj => {
                        let option = document.createElement("option");
                        option.textContent = `${compteObj.compte} - ${compteObj.intitule || ""}`;
                        option.value = compteObj.compte;
                        editor.appendChild(option);
                    });
                    editor.value = cell.getValue() || "";
                    onRendered(() => editor.focus());
                })
                .catch(error => {
                    console.error("Erreur lors du chargement des comptes :", error);
                    cancel();
                });
            editor.addEventListener("change", function(){
                success(editor.value);
            });
            editor.addEventListener("blur", function(){
                success(editor.value);
            });
            return editor;
        } else if (currentFilter === 'contre-partie') {
            // Mode contre-partie : utilisation de l'éditeur personnalisé customListEditorFrs
            return customListEditorFrs(cell, onRendered, success, cancel, {
                values: window.formattedComptesFournisseurs
            });
        } else {
            let input = document.createElement("input");
            input.type = "text";
            input.value = cell.getValue() || "";
            onRendered(() => input.focus());
            input.addEventListener("blur", function(){
                success(input.value);
            });
            return input;
        }
    },
    editorParams: {
        autocomplete: true,
        listOnEmpty: true,
        values: window.formattedComptesFournisseurs || []
    },
    formatter: function(cell) {
        // Affiche uniquement le numéro de compte (avant " - ")
        let value = cell.getValue();
        if (value && typeof value === "string") {
            let parts = value.split(" - ");
            return parts[0];
        }
        return value;
    },
    cellEdited: function(cell) {
        let currentFilter = document.querySelector('input[name="filter-achats"]:checked')?.value;
        const row = cell.getRow();
        const compte = cell.getValue();
        if (!compte) return;

        // Appeler updateLibelleAndFocus pour les deux modes, libre ou contre-partie
        updateLibelleAndFocus(row, compte);

        console.log("Valeur Compte mise à jour :", cell.getValue());
    }
},

                    {
    title: "Libellé",
    field: "libelle",
    headerFilter: "input",
    headerFilterParams: {
        elementAttributes: {
            style: "width: 85px; height: 25px;" // 80 pixels de large
        }
    },
    editor: genericTextEditorForLibelle
},

                {
                    title: "Débit",
                    field: "debit",
                    headerFilter: "input",
                    headerFilterParams: {
                        elementAttributes: {
                            style: "width: 85px; height: 25px;" // 80 pixels de large
                        }
                    },
                    editor: customNumberEditor, // Utilisation de notre éditeur personnalisé
                    bottomCalc: "sum",
                    formatter: function(cell) {
                        const value = cell.getValue();
                        return value ? parseFloat(value).toFixed(2) : "0.00";
                    },
                },
                {
                    title: "Crédit",
                    field: "credit",
                    headerFilter: "input",
                    headerFilterParams: {
                        elementAttributes: {
                            style: "width: 85px; height: 25px;" // 80 pixels de large
                        }
                    },
                    editor: customNumberEditor, // Utilisation de l'éditeur personnalisé
                    bottomCalc: "sum",
                    formatter: function(cell) {
                        const value = cell.getValue();
                        return value ? parseFloat(value).toFixed(2) : "0.00";
                    },
                    mutatorEdit: function(value) {
                        return value || "0.00";
                    },
                    cellEdited: function(cell) {
                        console.log("Valeur Crédit mise à jour :", cell.getValue());
                    }
                },
                contrePartieColumn,

                {
                    title: "Rubrique TVA",
                    field: "rubrique_tva",
                    headerFilter: "input",
                    editor: customListEditor,
                    headerFilterParams: {
                        elementAttributes: {
                            style: "width: 85px; height: 25px;" // 80 pixels de large
                        }
                    },
                    editorParams: {
                        autocomplete: true,
                        listOnEmpty: true,
                        values: rubriquesAchats
                    }
                },

                {
                    title: "Compte TVA",
                    field: "compte_tva",
                    headerFilter: "input",
                    editor: customListEditor,
                    headerFilterParams: {
                      elementAttributes: { style: "width: 85px; height: 25px;" }
                    },
                    editorParams: {
                      autocomplete: true,
                      listOnEmpty: true,
                      values: comptesVentes.map(function(compte) {
                        return compte.compte + " - " + compte.intitule;
                      })
                    }
                  },
                  {
                    title: "Prorat de deduction",
                    field: "prorat_de_deduction",
                    headerFilter: "input",
                    editor: customListEditor,
                    headerFilterParams: {
                      elementAttributes: { style: "width: 85px; height: 25px;" }
                    },
                    editorParams: {
                      autocomplete: true,
                      listOnEmpty: true,
                      values: ["Oui", "Non"]
                    }
                  },
                  {
                    title: "Solde Cumulé",
                    field: "value",
                    headerFilter: "input",
                    headerFilterParams: {
                      elementAttributes: { style: "width: 85px; height: 25px;" }
                    },
                    formatter: function(cell, formatterParams, onRendered) {
                        let val = cell.getValue();

                        // Vérifier si c'est un nombre
                        if (val !== "" && !isNaN(val)) {
                          let numericVal = parseFloat(val);

                          // Si c'est -0, on le force à 0
                          if (Object.is(numericVal, -0)) {
                            numericVal = 0;
                          }

                          // Retourne la valeur formatée sur 2 décimales
                          return numericVal.toFixed(2);
                        }

                        return val;
                      }
                  },
                  {
                    title: "Pièce",
                    field: "piece_justificative",
                    editor: pieceEditor,
                    formatter: function(cell) {
                      return cell.getValue() || "Aucun fichier";
                    },
                    headerFilter:"input",
                    headerFilterParams: {
                      elementAttributes: { style: "width: 85px; height: 25px;" }
                    }
                  },
                  {
                    title: "Sélectionner",
                    field: "select",
                    headerSort: false,
                    resizable: true,
                    frozen: true,
                    width: 50,
                    minWidth: 40,
                    headerHozAlign: "center",
                    hozAlign: "center",
                    formatter: "rowSelection",
                    titleFormatter: "rowSelection",
                    cellEdited: function(cell) {
                      var el = cell.getElement();
                      el.setAttribute("tabindex", "0");
                      el.addEventListener("keydown", function(e) {
                        if(e.key === "Enter"){
                          e.preventDefault();
                          cell.getRow().toggleSelect();
                        }
                      });
                    }
                  },

                  { title: "Code_journal", field: "type_Journal", visible: false },
                  { title: "categorie", field: "categorie", visible: false }
                ],

                // Lorsque la cellule "Prorat de deduction" est éditée et validée (Enter)
                cellEdited: function(cell) {
                  if(cell.getField() === "prorat_de_deduction") {
                    var row = cell.getRow();
                    var rowData = row.getData();
                    // Mise à jour du numéro de pièce
                    var updatedData = updatePieceJustificative(table.getData());
                    var updatedRow = updatedData.find(function(r) {
                      return r.id === rowData.id;
                    });
                    if(updatedRow) {
                      row.update({ piece_justificative: updatedRow.piece_justificative });
                    }
                    // Après validation, lancer l'éditeur de la cellule "Pièce" pour afficher le numéro calculé
                    setTimeout(function(){
                      row.getCell("piece_justificative").edit();
                      // Ensuite, sélectionner la ligne et déplacer le focus sur la cellule "Sélectionner"
                      setTimeout(function(){
                        row.select();
                        var selectCell = row.getCell("select");
                        if(selectCell) {
                          selectCell.getElement().focus();
                        }
                      }, 200);
                    }, 100);
                  }
                },
                    // Calcul et mise à jour des totaux dans le footer pour chaque rendu de ligne
                    rowFormatter: function(row) {
                        let data = row.getData();

                        // Appliquer le zebra striping en fonction de piece_justificative
                        if (data.piece_justificative !== lastPiece) {
                            toggle = !toggle;
                            lastPiece = data.piece_justificative;
                        }
                        row.getElement().style.backgroundColor = toggle ? "#f2f2f2" : "#ffffff";

                        // Calculs cumulés (pour chaque row, ce qui peut être optimisé si nécessaire)
                        let debitTotal = 0;
                        let creditTotal = 0;
                        row.getTable().getRows().forEach(function(r) {
                            debitTotal += parseFloat(r.getData().debit || 0);
                            creditTotal += parseFloat(r.getData().credit || 0);
                        });
                        let soldeDebiteur = debitTotal > creditTotal ? debitTotal - creditTotal : 0.00;
                        let soldeCrediteur = creditTotal > debitTotal ? creditTotal - debitTotal : 0.00;
                        // Mise à jour du footer (assurez-vous que ces éléments existent dans votre HTML)
                        document.getElementById('cumul-debit-achats').innerText = formatCurrency(debitTotal);
                        document.getElementById('cumul-credit-achats').innerText = formatCurrency(creditTotal);

                        document.getElementById('solde-debit-achats').innerText = formatCurrency(soldeDebiteur);
                        document.getElementById('solde-credit-achats').innerText = formatCurrency(soldeCrediteur);
                         // Diminuer la taille de la police pour ces éléments

                    },

                });


// Événement de mise à jour des champs
// Récupérer le token CSRF du meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Empêcher l'édition d'une ligne sans ID
tableAch.on("cellEditing", function (cell) {
    const row = cell.getRow();
    const data = row.getData();

    if (!data.id) {
        console.log("Édition ignorée : nouvelle ligne sans ID.");
        return false; // Annuler l'édition sans alerte
    }

    return true; // Autoriser l'édition si la ligne a un ID
});

tableAch.on("cellEdited", function (cell) {
    const row = cell.getRow();
    const data = row.getData();

    // Vérifier si la ligne a un ID (éviter les erreurs)
    if (!data.id) {
        console.log("Modification ignorée : ligne sans ID.");
        return;
    }

    // Vérifier si la ligne est vide
    const isEmpty = !data.numero_facture && !data.compte && !data.credit;
    if (isEmpty) {
        console.log("Mise à jour ignorée : ligne existante mais vide.");
        return; // Ne rien envoyer si la ligne est vide
    }

    // Vérifier si tous les champs obligatoires sont remplis
    if (!data.numero_facture || !data.compte || !data.credit) {
        console.log("Mise à jour bloquée : certains champs obligatoires sont vides.");
        return; // Bloquer la mise à jour sans message d'alerte
    }

    // Si on arrive ici, la mise à jour est autorisée
    const field = cell.getField();
    const value = cell.getValue();
    const numeroFacture = data.numero_facture;

    // Préparer les données à envoyer sans `debit` et `credit`
    const updateData = {
        field: field,
        value: value,
        numero_facture: numeroFacture
    };

    // Si la modification concerne le champ `debit` ou `credit`, ne pas l'inclure dans la mise à jour
    if (field === "debit" || field === "credit") {
        console.log(`Modification de "${field}" ignorée : champ exclu de la mise à jour.`);
        return; // Ne pas envoyer ces champs
    }

    // Préparer la requête PUT pour la ligne concernée
    fetch(`/operations/${data.id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify(updateData),
    })
    .then(response => {
        if (!response.ok) {
            console.error(`Erreur HTTP ${response.status}`);
            return;
        }
        return response.json();
    })
    .then(result => {
        console.log("Mise à jour réussie :", result);
    })
    .catch(error => {
        console.error("Erreur de mise à jour :", error);
    });
});



// Ajouter l'écouteur pour mettre à jour "type_Journal"
document.querySelector("#journal-achats").addEventListener("change", function (e) {
    const selectedCode = e.target.value;

    let ligneSelectionnee = tableAch.getSelectedRows()[0];
    if (ligneSelectionnee) {
        ligneSelectionnee.update({ type_Journal: selectedCode });
    }
});





document.getElementById("print-table").addEventListener("click", function () {
    if (tableAch) {
      tableAch.print(false, true); // Utilise la méthode d'impression de Tabulator
    } else {
        console.error("Le Tabulator n'est pas initialisée.");
    }
});

document.getElementById("download-xlsx").addEventListener("click", function () {
    if (tableAch) {
       tableAch.download("xlsx", "data.xlsx", { sheetName: "My Data" });
    } else {
        console.error("La table Tabulator n'est pas initialisée.");
    }
});

document.getElementById("download-pdf").addEventListener("click", function () {
    if (tableAch) {
       tableAch.download("pdf", "data.pdf", {
            orientation: "portrait",
            title: "Rapport des achats",
        });
    } else {
        console.error("La table Tabulator n'est pas initialisée.");
    }




});
// Fonction pour mettre le focus sur la cellule "date" du Tabulator
function focusTabulatorDate() {
    const rows = tableAch.getRows();
    if (rows.length > 0) {
        const firstRow = rows[0];
        const dateCell = firstRow.getCell("date");
        if (dateCell) {
            dateCell.edit();  // Lance le mode édition sur la cellule "date"
        }
    }
}

// Tableau des sélecteurs dans l'ordre de navigation
const controlSelectors = [
    "#journal-achats",
    "#filter-intitule-achats",
    "#filter-contre-partie-achats",
    "#filter-libre-achats",
    "#filter-exercice-achats",
    "#periode-achats"

    // Ajoutez ici d'autres sélecteurs si nécessaire
];

// Attache un écouteur "keydown" sur chaque contrôle pour la navigation avec la touche Entrée
controlSelectors.forEach((selector, index) => {
    const element = document.querySelector(selector);
    if (element) {
        element.addEventListener("keydown", function(e) {
            if(e.key === "Enter") {
                e.preventDefault();
                // Si le contrôle actuel est "periode-achats", passer directement au champ "date" du Tabulator
                if(selector === "#periode-achats") {
                    focusTabulatorDate();
                } else {
                    // Sinon, passer au contrôle suivant dans le tableau (en boucle si besoin)
                    const nextIndex = (index + 1) % controlSelectors.length;
                    const nextElement = document.querySelector(controlSelectors[nextIndex]);
                    if (nextElement) {
                        nextElement.focus();
                    }
                }
            }
        });
    }
});



let selectedCodeJournal2 = "";
document.querySelector("#journal-ventes").addEventListener("change", function() {
    selectedCodeJournal2 = this.value ? this.value.trim() : "";
    console.log("Code journal sélectionné (Ventes):", selectedCodeJournal2);
});

// Table des ventes
var tableVentes = new Tabulator("#table-ventes", {
    height: "500px",
    layout: "fitColumns",
    rowHeight: 30, // définit la hauteur de ligne à 30px

    clipboard: true,
    clipboardPasteAction: "replace",
    placeholder: "Aucune donnée disponible",
    ajaxResponse: function(url, params, response) {
        console.log("Données reçues (ventes) :", response);

        // Ajouter une ligne vide si elle n'est pas déjà présente
        if (response.length === 0 || response[0].id !== "") {
            response.unshift({
                id: "",
                date: "",
                debit: "",
                credit: "",
            });
        }
        return response; // Passe les données modifiées à Tabulator
    },
    ajaxError: function(xhr, textStatus, errorThrown) {
        console.error("Erreur AJAX (ventes) :", textStatus, errorThrown);
    },
    printAsHtml: true,
    printHeader: "<h1>Table Ventes</h1>",
    printFooter: "<h2>Table Footer</h2>",
    selectable: true,
    footerElement:"<table style='width: 30%; margin-top: 6px; border-collapse: collapse;'>" +
    "<tr>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 12px;'>Cumul Débit :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='cumul-debit-ventes'></span></td>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 12px;'>Cumul Crédit :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='cumul-credit-ventes'></span></td>" +
    "</tr>" +
    "<tr>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 12px;'>Solde Débiteur :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='solde-debit-ventes'></span></td>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 12px;'>Solde Créditeur :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='solde-credit-ventes'></span></td>" +
    "</tr>" +
"</table>",
    columns: [
        { title: "ID", field: "id", visible: false },
        {
            title: "Date",
            field: "date",
            hozAlign: "center",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 85px; height: 25px;" // 80 pixels de large
                }
            },
            sorter: "date",
            editor: function(cell, onRendered, success, cancel) {
                // Création d'un conteneur pour l'éditeur de date
                const container = document.createElement("div");
                container.style.display = "flex";
                container.style.alignItems = "center";

                // Création de l'input pour saisir la date
                const input = document.createElement("input");
                input.type = "text";
                input.style.flex = "1";
                input.placeholder = "jj/";

                // Pré-remplissage de l'input si une date existe déjà
                const currentValue = cell.getValue();
                if (currentValue) {
                    let dt = luxon.DateTime.fromFormat(currentValue, "yyyy-MM-dd HH:mm:ss");
                    if (!dt.isValid) {
                        dt = luxon.DateTime.fromISO(currentValue);
                    }
                    if (dt.isValid) {
                        input.value = dt.toFormat("dd/MM/yyyy");
                    }
                }

                // Fonction de validation commune pour l'input
                function validateAndCommit() {
                    const moisSelect = document.getElementById("periode-ventes");
                    const anneeInput = document.getElementById("annee-ventes");
                    const codeJournalInput = document.getElementById("journal-ventes");

                    const day = parseInt(input.value.slice(0, 2), 10);
                    const month = parseInt(moisSelect.value, 10);
                    const year = parseInt(anneeInput.value, 10);

                    if (!isNaN(day) && !isNaN(month) && !isNaN(year)) {
                        const dt = luxon.DateTime.local(year, month, day);
                        if (dt.isValid) {
                            // Validation de la cellule avec le format souhaité
                            success(dt.toFormat("yyyy-MM-dd HH:mm:ss"));
                            return true;
                        }
                    }
                    alert("Veuillez saisir une date valide (jour, et vérifier les filtres pour le mois et l'année).");
                    cancel();
                    return false;
                }

                // Validation sur blur
                input.addEventListener("blur", validateAndCommit);

                // Intercepter la touche Entrée
                input.addEventListener("keydown", function(e) {
                    if (e.key === "Enter") {
                        e.preventDefault(); // Empêcher le comportement par défaut
                        if (validateAndCommit()) {
                            // Après validation, passer à la cellule éditable suivante
                            setTimeout(() => {
                                focusNextEditableCell(cell);
                            }, 50);
                        }
                    }
                });

                container.appendChild(input);
                onRendered(() => input.focus());
                return container;
            },
            formatter: function(cell) {
                const dateValue = cell.getValue();
                if (dateValue) {
                    let dt = luxon.DateTime.fromFormat(dateValue, "yyyy-MM-dd HH:mm:ss");
                    if (!dt.isValid) {
                        dt = luxon.DateTime.fromISO(dateValue);
                    }
                    return dt.isValid ? dt.toFormat("dd/MM/yyyy") : dateValue;
                }
                return "";
            },
        },


        { title: "N° dossier", field: "numero_dossier",headerFilter: "input",headerFilterParams: {
            elementAttributes: {
                style: "width: 85px; height: 25px;" // 80 pixels de large
            }
        },
        editor: "input"
     },
        { title: "N° Facture", field: "numero_facture",headerFilter: "input",headerFilterParams: {
            elementAttributes: {
                style: "width: 85px; height: 25px;" // 80 pixels de large
            }
        }, editor: "input" },

        {
            title: "Compte",
            field: "compte",
            headerFilter: "input",
            headerFilterParams: {
              elementAttributes: { style: "width: 85px; height: 25px;" }
            },
            // Utilisation de l'éditeur personnalisé pour les clients
            editor: customListEditorClt,
            editorParams: {
              autocomplete: true,
              listOnEmpty: true,
              values: window.comptesClients // On passe la liste formatée
            },
            cellEdited: function(cell) {
              const compteClient = cell.getValue();
              const row = cell.getRow();
              if (!compteClient) return;

              // Recherche du client dans le tableau global
              const client = window.clients.find(c => `${c.compte} - ${c.intitule}` === compteClient);
              const numeroDossier = row.getCell("numero_dossier").getValue() || "";
              const numeroFacture = row.getCell("numero_facture").getValue() || "";

              if (client) {
                row.update({
                  libelle: `F°${numeroFacture} D°${numeroDossier} ${client.intitule}`
                });
              } else {
                // Affichage d'un message et d'un bouton pour ajouter un client
                let editorEl = cell.getElement();
                if (!editorEl.querySelector('.btn-ajouter-client')) {
                  editorEl.innerHTML = `
                    <div style="display: flex; flex-direction: column; padding: 5px;">
                      <span style="color:red; font-size:0.9em;">Client non trouvé</span>
                      <div style="display: flex; align-items: center; margin-top: 3px;">
                        <span>${compteClient}</span>
                        <button type="button" class="btn-ajouter-client" title="Ajouter client"
                          style="margin-left:5px; padding:0 5px; border:none; background:none; cursor:pointer;">
                          <i class="fas fa-plus-circle" style="color:green;"></i>
                        </button>
                      </div>
                    </div>
                  `;
                  editorEl.querySelector('.btn-ajouter-client').addEventListener('click', () => {
                    Swal.fire({
                      title: 'Client non trouvé',
                      text: "Voulez-vous ajouter ce client ?",
                      icon: 'question',
                      showCancelButton: true,
                      confirmButtonText: 'Oui, ajouter',
                      cancelButtonText: 'Non'
                    }).then((resultConfirmation) => {
                      if (resultConfirmation.isConfirmed) {
                        ouvrirPopupClient(compteClient, row, cell);
                      } else {
                        cell.edit();
                      }
                    });
                  });
                }
                if (!cell._reopened) {
                  cell._reopened = true;
                  setTimeout(() => { cell.edit(); }, 100);
                }
              }

              // Focus sur la cellule "Débit"
              const debitCell = row.getCell("debit");
              if (debitCell) {
                debitCell.getElement().focus();
              }
            }
          },


{
    title: "Libellé",
    field: "libelle",
    headerFilter: "input",
    headerFilterParams: {
        elementAttributes: {
            style: "width: 85px; height: 25px;" // 80 pixels de large
        }
    },
    editor: "input", // Optionnel, si modification manuelle est permise
    editable: false, // Non éditable automatiquement
},
{
title: "Débit",
field: "debit",
headerFilter: "input",
headerFilterParams: {
    elementAttributes: {
        style: "width: 85px; height: 25px;" // 80 pixels de large
    }
},
editor: "number", // Permet l'édition en tant que nombre
bottomCalc: "sum", // Calcul du total dans le bas de la colonne
formatter: function(cell) {
// Formater pour afficher 0.00 si la cellule est vide ou nulle
const value = cell.getValue();
return value ? parseFloat(value).toFixed(2) : "0.00";
},

},
{ title: "Crédit", field: "credit", headerFilter: "input", headerFilterParams: {
    elementAttributes: {
        style: "width: 85px; height: 25px;" // 80 pixels de large
    }
},
 editor: "number", // Permet l'édition en tant que nombre
bottomCalc: "sum", // Calcul du total dans le bas de la colonne
formatter: function(cell) {
// Formater pour afficher 0.00 si la cellule est vide ou nulle
const value = cell.getValue();
return value ? parseFloat(value).toFixed(2) : "0.00";
},
 },
 {
    title: "Contre-Partie",
    field: "contre_partie",
    headerFilter: "input",
    headerFilterParams: {
        elementAttributes: {
            style: "width: 85px; height: 25px;" // 80 pixels de large
        }
    },
    editor: customListEditorPlanComptable,
    editorParams: {
        autocomplete: true,
        listOnEmpty: true,
        verticalNavigation: "editor", // Ouvre la liste au focus
        // Fonction pour récupérer la liste des contre-parties depuis le backend pour Ventes
        valuesLookup: async function (cell) {
            if (!selectedCodeJournal2 || selectedCodeJournal2.trim() === "") {
                // Retourne une liste vide si aucun code journal n'est sélectionné
                return [];
            }
            try {
                const response = await fetch(`/get-contre-parties-ventes?code_journal=${selectedCodeJournal2}`);
                if (!response.ok) {
                    throw new Error("Erreur réseau ou code journal non valide.");
                }
                const datav = await response.json();
                if (datav.error) {
                    console.error("Erreur serveur :", datav.error);
                    return [];
                }
                console.log("Contre-Parties récupérées :", datav);
                return datav; // Retourne la liste des valeurs récupérées
            } catch (error) {
                console.error("Erreur réseau :", error);
                alert("Impossible de récupérer les contre-parties.");
                return [];
            }
        },
        // Une fois l'éditeur rendu, si la cellule est vide, on sélectionne automatiquement la première valeur
        onRendered: function (editor, cell) {
            if (!cell.getValue() && editor.options && editor.options.length > 0) {
                // Affecte la première valeur comme valeur par défaut
                editor.value = editor.options[0].value;
            }
        }
    },
    cellEdited: function (cell) {
        console.log("Contre-Partie mise à jour :", cell.getValue());
    },
},

        {
            title: "Compte TVA",
            field: "compte_tva",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 85px; height: 25px;" // 80 pixels de large
                }
            },
            editor: customListEditor,
            editorParams: {
                autocomplete: true,
                listOnEmpty: true,
                values: comptesAchats.map(compte => `${compte.compte} - ${compte.intitule}`)
            }
        },
        {
            title: "Rubrique TVA",
            field: "rubrique_tva",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 85px; height: 25px;"
                }
            },
            // Éditeur personnalisé qui va remplir l'input via une requête AJAX
            editor: function(cell, onRendered, success, cancel, editorParams) {
                // Création de l'élément input
                var input = document.createElement("input");
                input.type = "text";
                input.style.width = "100%";
                input.style.boxSizing = "border-box";

                // Affecte la valeur actuelle de la cellule (si présente)
                input.value = cell.getValue();

                // Requête AJAX pour récupérer la rubrique et les informations associées
                $.ajax({
                    url: '/getRubriqueSociete', // Assurez-vous que cette URL correspond bien à votre route
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        // Vérifier que la réponse contient la clé "rubrique"
                        if (response && response.rubrique) {
                            // Formater la valeur sous la forme "103-Nom_racine (taux)"
                            var formattedValue = response.rubrique + '-' + response.nom_racines + ' (' + response.taux +'%)';
                            input.value = formattedValue;

                            // Optionnel : mettre à jour d'autres colonnes de la ligne
                            cell.getRow().update({
                                nom_racines: response.nom_racines,
                                taux: response.taux
                            });
                        }
                        // Une fois l'input rendu, lui donner le focus
                        onRendered(function(){
                            input.focus();
                            input.style.height = "100%";
                        });
                    },
                    error: function() {
                        onRendered(function(){
                            input.focus();
                            input.style.height = "100%";
                        });
                    }
                });

                // Validation lors de la perte de focus
                input.addEventListener("blur", function(){
                    success(input.value);
                });
                // Gestion des touches Entrée (pour valider) et Échap (pour annuler)
                input.addEventListener("keydown", function(e){
                    if (e.keyCode === 13) {
                        success(input.value);
                    }
                    if (e.keyCode === 27) {
                        cancel();
                    }
                });

                return input;
            }
        },



        {
            title: "Solde Cumulé",
            field: "value", // Ce champ contient le solde cumulé calculé (issu de ton mapping: value: ligne.solde_cumule)
            // editor: "input", // Permet l'édition manuelle si besoin (tu peux le supprimer si le solde doit être uniquement calculé)
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 85px; height: 25px;" // 80 pixels de large
                }
            },
            formatter: function(cell, formatterParams, onRendered) {
                let val = cell.getValue();

                // Vérifier si c'est un nombre
                if (val !== "" && !isNaN(val)) {
                  let numericVal = parseFloat(val);

                  // Si c'est -0, on le force à 0
                  if (Object.is(numericVal, -0)) {
                    numericVal = 0;
                  }

                  // Retourne la valeur formatée sur 2 décimales
                  return numericVal.toFixed(2);
                }

                return val;
              }
          },

        {
            title: "Pièce",
        field: "piece_justificative",
        editor: "input", // Éditeur pour permettre la modification manuelle
        headerFilter: "input",
        headerFilterParams: {
            elementAttributes: {
                style: "width: 85px; height: 25px;" // 80 pixels de large
            }
        },

        },
        {
    title: "Sélectionner",
    headerSort: false,
    resizable: true,
    frozen: true,
    width: 50,
    minWidth: 40,
    headerHozAlign: "center",
    hozAlign: "center",
    formatter: "rowSelection",
    titleFormatter: "rowSelection",
    cellClick: function(e, cell){
        cell.getRow().toggleSelect();
    }
},


{ title: "Code_journal", field: "type_Journal", visible: false },
{ title: "categorie", field: "categorie", visible: false },

        ],
        rowFormatter: function(row) {
            let data = row.getData();

            // Appliquer le zebra striping en fonction de piece_justificative
            if (data.piece_justificative !== lastPiece) {
                toggle = !toggle;
                lastPiece = data.piece_justificative;
            }
            row.getElement().style.backgroundColor = toggle ? "#f2f2f2" : "#ffffff";

            // Calcul cumulés des totaux
            let debitTotal = 0;
            let creditTotal = 0;
            row.getTable().getRows().forEach(function(r) {
                debitTotal += parseFloat(r.getData().debit || 0);
                creditTotal += parseFloat(r.getData().credit || 0);
            });

            // Calcul des soldes en appliquant la logique conditionnelle
            let soldeDebiteur = debitTotal > creditTotal ? debitTotal - creditTotal : 0.00;
            let soldeCrediteur = creditTotal > debitTotal ? creditTotal - debitTotal : 0.00;

            // Mise à jour du footer avec les totaux et soldes pour les ventes
            document.getElementById('cumul-debit-ventes').innerText = formatCurrency(debitTotal);
            document.getElementById('cumul-credit-ventes').innerText = formatCurrency(creditTotal);
            document.getElementById('solde-debit-ventes').innerText = formatCurrency(soldeDebiteur);
            document.getElementById('solde-credit-ventes').innerText = formatCurrency(soldeCrediteur);
        }


});

////////////Mise a jour ventes////////////////////////:

// Récupérer le token CSRF du meta tag
// const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Empêcher l'édition d'une ligne sans ID pour tableVentes
tableVentes.on("cellEditing", function (cell) {
    const row = cell.getRow();
    const data = row.getData();

    if (!data.id) {
        console.log("Édition ignorée (Ventes) : nouvelle ligne sans ID.");
        return false; // Annuler l'édition sans alerte
    }
    return true; // Autoriser l'édition si la ligne a un ID
});

tableVentes.on("cellEdited", function (cell) {
    const row = cell.getRow();
    const data = row.getData();

    // Vérifier si la ligne a un ID (éviter les erreurs)
    if (!data.id) {
        console.log("Modification ignorée (Ventes) : ligne sans ID.");
        return;
    }

    // Vérifier si la ligne est vide
    const isEmpty = !data.numero_facture && !data.compte && !data.credit;
    if (isEmpty) {
        console.log("Mise à jour ignorée (Ventes) : ligne existante mais vide.");
        return; // Ne rien envoyer si la ligne est vide
    }

    // Vérifier si tous les champs obligatoires sont remplis
    if (!data.numero_facture || !data.compte || !data.credit) {
        console.log("Mise à jour bloquée (Ventes) : certains champs obligatoires sont vides.");
        return; // Bloquer la mise à jour sans message d'alerte
    }

    // Si on arrive ici, la mise à jour est autorisée
    const field = cell.getField();
    const value = cell.getValue();
    const numeroFacture = data.numero_facture;

    // Préparer les données à envoyer sans `debit` et `credit`
    const updateData = {
        field: field,
        value: value,
        numero_facture: numeroFacture
    };

    // Si la modification concerne le champ `debit` ou `credit`, ne pas l'inclure dans la mise à jour
    if (field === "debit" || field === "credit") {
        console.log(`Modification de "${field}" ignorée (Ventes) : champ exclu de la mise à jour.`);
        return; // Ne pas envoyer ces champs
    }

    // Préparer la requête PUT pour la ligne concernée
    fetch(`/operations/${data.id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify(updateData),
    })
    .then(response => {
        if (!response.ok) {
            console.error(`Erreur HTTP (Ventes) ${response.status}`);
            return;
        }
        return response.json();
    })
    .then(result => {
        console.log("Mise à jour réussie (Ventes) :", result);
    })
    .catch(error => {
        console.error("Erreur de mise à jour (Ventes) :", error);
    });
});


document.querySelector("#journal-ventes").addEventListener("change", function (e) {
    const selectedCode = e.target.value;

    let ligneSelectionnee = tableVentes.getSelectedRows()[0];
    if (ligneSelectionnee) {
        ligneSelectionnee.update({ type_Journal: selectedCode });
    }
});
// Fonction pour supprimer les doublons en fonction de l'id
function supprimerDoublonsLignes(lignes) {
    const lignesUniquement = []; // Tableau pour stocker les lignes sans doublons
    const idsDejaAjoutes = new Set(); // Un Set pour suivre les ids déjà rencontrés

    lignes.forEach(ligne => {
        if (!idsDejaAjoutes.has(ligne.id)) {
            lignesUniquement.push(ligne); // Ajouter la ligne si son id n'a pas encore été rencontré
            idsDejaAjoutes.add(ligne.id); // Ajouter l'id au Set
        }
    });

    return lignesUniquement; // Retourner le tableau sans doublons
}

// =====================================================================
// Fonction pour ajouter une ligne au tableau
// =====================================================================
// Fonction pour ajouter une ligne dans Tabulator
// =====================================================================
// 1. Fonction pour ajouter une ligne (vide ou préremplie)
async function ajouterLigne(table, preRemplir = false, ligneActive = null) {
    let nouvellesLignes = [];
    let idCounter = table.getData().length + 1;
    let codeJournal = document.querySelector("#journal-achats").value;
    let moisActuel = new Date().getMonth() + 1;
    let filterAchats = document.querySelector('input[name="filter-achats"]:checked')?.value;

    if (!filterAchats) {
      alert("Veuillez sélectionner un filtre.");
      return;
    }

    if (preRemplir && ligneActive) {
      // Ajout de la ligne pré-remplie en fonction du filtre
      nouvellesLignes = await ajouterLignePreRemplie(idCounter, ligneActive, codeJournal, moisActuel, filterAchats);
      console.log("Lignes pré-remplies générées:", nouvellesLignes);
    } else {
      // Création d'une ligne vide
      let ligneVide = ajouterLigneVide(idCounter, ligneActive, codeJournal, moisActuel);
      nouvellesLignes.push(ligneVide);
    }

    if (Array.isArray(nouvellesLignes)) {
      nouvellesLignes.forEach(ligne => {
        table.addRow(ligne, false);
      });
    } else {
      console.error("Erreur: nouvellesLignes n'est pas un tableau.");
    }
    return nouvellesLignes;
}

// =====================================================================
// 2. Fonction pour ajouter une ligne pré-remplie selon le filtre sélectionné
async function ajouterLignePreRemplie(idCounter, ligneActive, codeJournal, moisActuel, filterAchats) {
    let lignes = [];
    let creditPremierLigne = parseFloat(ligneActive.credit) || 0;

    if (filterAchats === 'contre-partie') {
      // Pour le filtre contre-partie : création de deux lignes
      let ligne1 = { ...ligneActive, id: idCounter++ };
      ligne1.compte = ligneActive.contre_partie || '';
      ligne1.contre_partie = ligneActive.compte || '';
      ligne1.debit = 0;
      ligne1.credit = 0;
      ligne1.piece = ligneActive.piece;
      ligne1.type_journal = codeJournal || '';
      lignes.push(ligne1);

      let ligne2 = { ...ligneActive, id: idCounter++ };
      ligne2.compte = ligneActive.compte_tva || '';
      ligne2.contre_partie = ligne1.compte || '';
      ligne2.debit = 0;
      ligne2.credit = 0;
      ligne2.piece = ligneActive.piece;
      ligne2.type_journal = codeJournal || '';
      lignes.push(ligne2);

    } else if (filterAchats === 'libre') {
      // Pour le filtre libre : création d'une seule ligne préremplie (initialement)
      let ligne1 = { ...ligneActive, id: idCounter++ };
      ligne1.compte = ''; // à renseigner via sélection ultérieure
      ligne1.contre_partie = ''; // sera rempli automatiquement
      ligne1.debit = 0;
      ligne1.credit = 0;
      ligne1.piece = ligneActive.piece || '';
      ligne1.numero_facture = ligneActive.numero_facture || '';
      ligne1.libelle = ligneActive.libelle || '';
      ligne1.rubrique_tva = ligneActive.rubrique_tva || '';
      ligne1.piece_justificative = ligneActive.piece_justificative || '';
      ligne1.prorat = 'Oui'; // par défaut
      ligne1.type_journal = codeJournal || '';
      lignes.push(ligne1);
    }

    // Calcul du débit (adaptable selon votre logique)
    for (let i = 0; i < lignes.length; i++) {
      const typeLigne = (i === 0) ? "ligne1" : "ligne2";
      await calculerDebit(lignes[i], typeLigne, creditPremierLigne);
      console.log(`Débit calculé pour ${typeLigne}:`, lignes[i].debit);
    }

    // Pour le filtre contre-partie, incrémenter la pièce si la facture est équilibrée
    if (filterAchats === 'contre-partie') {
      lignes.forEach((ligne, index) => {
        if (parseFloat(ligne.debit) === parseFloat(ligne.credit)) {
          let piece = parseInt(ligne.piece) || 0;
          ligne.piece = piece + 1;
          console.log(`Nouvelle pièce pour la ligne ${index}: ${ligne.piece}`);
        }
      });
    }
    return lignes;
}

// =====================================================================
// 3. Fonction pour créer une ligne vide
function ajouterLigneVide(idCounter, ligneActive, codeJournal, moisActuel) {
    return {
      id: idCounter,
      compte: '',
      contre_partie: '',
      compte_tva: '',
      debit: 0,
      credit: 0,
      piece: '',
      piece_justificative: '',
      numero_facture: '',
      libelle: '',
      rubrique_tva: '',
      prorat: '',
      type_journal: codeJournal,
      date: '' // Doit être rendu en input avec la classe .date-ligne
    };
}

// =====================================================================
// 4. Fonction pour calculer dynamiquement le débit
async function calculerDebit(rowData, typeLigne, credit) {
    const tauxTVA = parseFloat(rowData.taux_tva || 0) / 100;
    console.log(`Calcul du débit pour ${typeLigne}: Crédit = ${credit}, Taux TVA = ${tauxTVA}`);

    if (isNaN(credit) || isNaN(tauxTVA)) {
        console.error("Crédit ou Taux TVA invalides !");
        rowData.debit = 0;
        return;
    }

    const prorataDeDeduction = (rowData.prorat_de_deduction || "Non").trim().toLowerCase();
    const isProrataOui = prorataDeDeduction === "oui";
    let prorata = 0;
    if (isProrataOui) {
        try {
            const response = await fetch('/get-session-prorata');
            if (!response.ok) throw new Error(`Erreur réseau : ${response.statusText}`);
            const data = await response.json();
            prorata = parseFloat(data.prorata_de_deduction) || 0;
        } catch (error) {
            console.error('Erreur lors de la récupération du prorata de déduction :', error);
        }
    }
    let debit = 0;
    if (typeLigne === "ligne1") {
        debit = isProrataOui
            ? (credit / (1 + tauxTVA)) + (((credit / (1 + tauxTVA)) * tauxTVA) * (1 - prorata / 100))
            : credit / (1 + tauxTVA);
    } else if (typeLigne === "ligne2") {
        debit = isProrataOui
            ? ((credit / (1 + tauxTVA)) * tauxTVA) * (prorata / 100)
            : (credit / (1 + tauxTVA)) * tauxTVA;
    }
    rowData.debit = parseFloat(debit.toFixed(2));
    console.log(`Débit final pour ${typeLigne}: ${rowData.debit}`);
}

// =====================================================================
// 5. Mise à jour du champ piece_justificative selon certaines règles
function getSelectedCodeJournal() {
    const selectors = [
        "#journal-achats",
        "#journal-ventes",
        "#journal-caisse",
        "#journal-banque",
        "#journal-operation-diverses"
    ];
    for (let sel of selectors) {
        const el = document.querySelector(sel);
        if (el && el.value && el.value.trim() !== "") {
            return el.value.trim();
        }
    }
    return "CJ";
}

function updatePieceJustificative(data) {
    // Regrouper les lignes par numéro de facture
    const factures = {};
    data.forEach(row => {
        const nf = row.numero_facture && row.numero_facture.trim();
        if (nf) {
            if (!factures[nf]) {
                factures[nf] = [];
            }
            factures[nf].push(row);
        }
    });
    Object.keys(factures).forEach(nf => {
        const rows = factures[nf];
        let totalDebit = 0, totalCredit = 0;
        rows.forEach(row => {
            totalDebit += parseFloat(row.debit) || 0;
            totalCredit += parseFloat(row.credit) || 0;
        });
        // Création de la pièce justificative : si la facture est équilibrée ET non nulle,
        // on incrémente, sinon on conserve la valeur existante.
        let dt = luxon.DateTime.fromFormat(rows[0].date, "yyyy-MM-dd HH:mm:ss");
        if (!dt.isValid) {
            dt = luxon.DateTime.fromISO(rows[0].date);
        }
        if (!dt.isValid) {
            console.warn("Date invalide pour la facture " + nf);
            return;
        }
        const moisFormatted = dt.toFormat("MM");
        const codeJournal = rows[0].type_journal || getSelectedCodeJournal();
        let existingNumbers = [];
        data.forEach(row => {
            if (row.piece_justificative) {
                const prefix = `P${moisFormatted}${codeJournal}`;
                if (row.piece_justificative.startsWith(prefix)) {
                    const numStr = row.piece_justificative.substring(prefix.length);
                    const num = parseInt(numStr, 10);
                    if (!isNaN(num)) {
                        existingNumbers.push(num);
                    }
                }
            }
        });
        existingNumbers.sort((a, b) => a - b);
        let newIncrement = 1;
        if (totalDebit === totalCredit && totalDebit !== 0) {
            newIncrement = existingNumbers.length > 0 ? existingNumbers[existingNumbers.length - 1] + 1 : 1;
        }
        const numeroFormate = String(newIncrement).padStart(4, "0");
        const newPiece = `P${moisFormatted}${codeJournal}${numeroFormate}`;
        rows.forEach(row => {
            row.piece_justificative = newPiece;
        });
    });
    return data;
}

// =====================================================================
// 6. Fonction pour enregistrer les lignes sans quitter le tableau
async function enregistrerLignesAch() {
    try {
      let lignes = tableAch.getData();
      console.log("📌 Données récupérées du tableau :", lignes);

      const journalSelect = document.querySelector("#journal-achats");
      const codeJournal = journalSelect.value;
      if (!codeJournal) {
        alert("⚠️ Veuillez sélectionner un journal.");
        return;
      }
      const selectedOption = journalSelect.options[journalSelect.selectedIndex];
      const categorie = selectedOption ? selectedOption.getAttribute("data-type") : "";
      console.log("Catégorie extraite :", categorie);

      const selectedFilter = document.querySelector('input[name="filter-achats"]:checked')?.value || null;
      // Mise à jour de la pièce justificative selon les règles
      lignes = updatePieceJustificative(lignes);

      const lignesAEnvoyer = lignes
        .filter(ligne => ligne.compte && (ligne.debit > 0 || ligne.credit > 0))
        .map(ligne => ({
          id: ligne.id || null,
          date: ligne.date || new Date().toISOString().slice(0, 10),
          numero_facture: ligne.numero_facture || 'N/A',
          compte: ligne.compte || '',
          debit: ligne.debit ? parseFloat(ligne.debit) : 0,
          credit: ligne.credit ? parseFloat(ligne.credit) : 0,
          contre_partie: ligne.contre_partie || '',
          rubrique_tva: ligne.rubrique_tva || '',
          compte_tva: ligne.compte_tva || '',
          type_journal: codeJournal,
          categorie: categorie,
          prorat_de_deduction: ligne.prorat_de_deduction || '',
          piece_justificative: ligne.piece_justificative || '',
          libelle: ligne.libelle || '',
          filtre_selectionne: selectedFilter,
          value: typeof ligne.solde_cumule !== "undefined" ? ligne.solde_cumule : ""
        }));

      console.log("📤 Données envoyées :", lignesAEnvoyer);
      if (lignesAEnvoyer.length === 0) {
        alert("⚠️ Aucune ligne valide à enregistrer.");
        return;
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const response = await fetch('/lignes', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ lignes: lignesAEnvoyer })
      });
      if (!response.ok) {
        console.error("❌ Erreur serveur :", response.status, response.statusText);
        alert(`Erreur lors de l'enregistrement : ${response.statusText}`);
        return;
      }
      const result = await response.json();
      console.log("📥 Réponse du serveur :", result);

      // Actualiser le tableau sans quitter la page
      if (Array.isArray(result)) {
        tableAch.setData(result);
        console.log("✅ Tableau mis à jour avec les nouvelles données.");
      } else if (result && Array.isArray(result.data)) {
        tableAch.setData(result.data);
        console.log("✅ Tableau mis à jour avec les nouvelles données.");
      } else {
        console.warn("⚠️ Format inattendu de la réponse :", result);
        alert("Aucune donnée valide reçue du serveur.");
        return;
      }
      calculerSoldeCumule();

      // Vérifier si la dernière ligne est vide, sinon l'ajouter
      const dataActuelle = tableAch.getData();
      let derniereLigne = dataActuelle[dataActuelle.length - 1];
      if (!derniereLigne || derniereLigne.compte !== '') {
        tableAch.addRow({
          id: null,
          compte: '',
          contre_partie: '',
          compte_tva: '',
          debit: 0,
          credit: 0,
          piece: '',
          piece_justificative: '',
          libelle: '',
          rubrique_tva: '',
          type_journal: codeJournal,
          value: ""
        });
      }
    } catch (error) {
      console.error("🚨 Erreur lors de l'enregistrement :", error);
      alert("❌ Une erreur s'est produite. Vérifiez la console pour plus de détails.");
    }
}

// =====================================================================
// 7. Fonction d'écoute sur l'événement "Enter" du tableau
async function ecouterEntrer(table) {
  table.element.addEventListener("keydown", async function (event) {
    if (event.key === "Enter") {
      event.preventDefault();

      // 1. Récupérer la ligne active (sélectionnée)
      const selectedRows = table.getSelectedRows();
      if (!selectedRows.length) {
        console.error("Aucune ligne active trouvée");
        return;
      }
      const activeRowData = selectedRows[0].getData();

      // 2. Générer la nouvelle ligne via la fonction existante
      let addedRows = await ajouterLigne(table, true, activeRowData);
      if (!Array.isArray(addedRows)) {
        addedRows = [addedRows];
      }
      console.log("Lignes ajoutées :", addedRows);

      // 3. Enregistrer les lignes sans quitter la page
      await enregistrerLignesAch();

      // 4. Récupérer les données mises à jour du tableau
      let dataAfter = table.getData();

      // 5. Identifier la dernière ligne enregistrée (celle avec un champ compte non vide)
      let nonEmptyRows = dataAfter.filter(row => row.compte && row.compte.trim() !== "");
      let lastRecorded = nonEmptyRows[nonEmptyRows.length - 1];
      console.log("Dernière ligne enregistrée :", lastRecorded);

      // 6. Rechercher une ligne vide dans le tableau
      let emptyRow = dataAfter.find(row => !row.compte || row.compte.trim() === "");
      if (!emptyRow) {
        // S'il n'y a pas de ligne vide, en ajouter une
        const newEmpty = {
          id: dataAfter.length + 1,
          compte: '',
          contre_partie: '',
          compte_tva: '',
          debit: 0,
          credit: 0,
          piece: '',
          piece_justificative: '',
          numero_facture: '',
          libelle: '',
          rubrique_tva: '',
          prorat: '',
          type_journal: document.querySelector("#journal-achats").value,
          date: ''
        };
        let newEmptyComponent = await table.addRow(newEmpty);
        emptyRow = newEmptyComponent.getData();
      }
      console.log("Ligne vide :", emptyRow);

      // 7. Pour le filtre "libre" : si le solde cumulé est différent de 0,
      // recopier exactement les données de la dernière ligne (sans incrémenter la pièce)
      const selectedFilter = document.querySelector('input[name="filter-achats"]:checked')?.value;
      if (selectedFilter === "libre" && lastRecorded) {
        let cumBalance = parseFloat(lastRecorded.value);
        if (isNaN(cumBalance)) { cumBalance = 0; }
        console.log("Solde cumulé :", cumBalance);
        if (cumBalance !== 0) {
          // Construction de la nouvelle ligne en recopiant toutes les valeurs de la précédente
          const newRowData = {
            id: dataAfter.length + 1,
            compte: lastRecorded.contre_partie,
            contre_partie: lastRecorded.compte,
            compte_tva: lastRecorded.compte_tva,
            debit: lastRecorded.debit,
            credit: lastRecorded.credit,
            piece_justificative: lastRecorded.piece_justificative, // Recopie exacte
            numero_facture: lastRecorded.numero_facture,
            libelle: lastRecorded.libelle,
            rubrique_tva: lastRecorded.rubrique_tva,
            prorat: lastRecorded.prorat,
            type_journal: lastRecorded.type_journal,
            date: lastRecorded.date,
          };
          console.log("Nouvelle ligne préremplie (libre) :", newRowData);
          // Ajouter la nouvelle ligne et placer le focus sur le champ date
          let newRowComponent = await table.addRow(newRowData);
          if (newRowComponent) {
            let newRowEl = newRowComponent.getElement();
            if (newRowEl) {
              let newDateInput = newRowEl.querySelector('.date-ligne');
              if (newDateInput) {
                newDateInput.focus();
              }
            }
          }
        } else {
          // Si le solde cumulé est 0, placer le focus sur la ligne vide
          if (emptyRow && emptyRow.id) {
            let emptyRowComponent = table.getRow(emptyRow.id);
            if (emptyRowComponent) {
              let emptyEl = emptyRowComponent.getElement();
              let dateInput = emptyEl.querySelector('.date-ligne');
              if (dateInput) {
                dateInput.focus();
              }
            }
          }
        }
      } else {
        // Pour les autres filtres, placer le focus sur la cellule date de la ligne vide
        if (emptyRow && emptyRow.id) {
          let emptyRowComponent = table.getRow(emptyRow.id);
          if (emptyRowComponent) {
            let emptyEl = emptyRowComponent.getElement();
            let dateInput = emptyEl.querySelector('.date-ligne');
            if (dateInput) {
              dateInput.focus();
            }
          }
        }
      }
    }
  });
}

// =====================================================================
// 8. Lancer l'écoute sur le tableau
ecouterEntrer(tableAch);

// =====================================================================
// 9. Fonction d'aide pour traiter la ligne suivante en mode "libre"
function traiterLigneLibre(rowElement) {
    let lignesDOM = document.querySelectorAll('.ligne');
    let index = Array.from(lignesDOM).indexOf(rowElement);
    if (index > 0) {
      let lignePrecedente = lignesDOM[index - 1];
      let datePrecedente = lignePrecedente.querySelector('.date-ligne')?.value;
      if (datePrecedente) {
        let dateInput = rowElement.querySelector('.date-ligne');
        if(dateInput) dateInput.value = datePrecedente;
      }
      ['numero_facture', 'libelle', 'piece_justificative', 'rubrique_tva'].forEach(champ => {
        let champPrecedent = lignePrecedente.querySelector(`.${champ}`);
        let champCourant = rowElement.querySelector(`.${champ}`);
        if (champPrecedent && champCourant) {
          champCourant.value = champPrecedent.value;
        }
      });
      let comptePrecedent = lignePrecedente.querySelector('.compte')?.value;
      let contrePartie = rowElement.querySelector('.contre_partie');
      if (comptePrecedent && contrePartie) {
        contrePartie.value = comptePrecedent;
      }
      let proratField = rowElement.querySelector('.prorat');
      if (proratField) {
        proratField.value = "Oui";
      }
    }
}

// =====================================================================
// Fonction pour calculer le solde cumulé et appliquer la vérification
function calculerSoldeCumule() {
    const rows = tableAch.getRows();
    const groupSums = {};
    const factures = {};

    rows.forEach((row) => {
      const data = row.getData();
      const key = `${data.numero_facture}`;

      if (typeof groupSums[key] === "undefined") {
        groupSums[key] = 0;
      }

      const debit = parseFloat(data.debit) || 0;
      const credit = parseFloat(data.credit) || 0;
      let nouveauSolde = groupSums[key] + debit - credit;

      // Correction pour ne pas afficher -0.00
      if (Math.abs(nouveauSolde) < Number.EPSILON) {
        nouveauSolde = 0;
      }

      // Format d'affichage à deux décimales
      const displayValue = nouveauSolde.toFixed(2);

      data.value = displayValue;
      groupSums[key] = nouveauSolde;
      row.update({ value: displayValue });

      if (!factures[data.numero_facture]) {
        factures[data.numero_facture] = { lastRow: row, lastSolde: nouveauSolde };
      } else {
        factures[data.numero_facture].lastRow = row;
        factures[data.numero_facture].lastSolde = nouveauSolde;
      }
    });

    // Appliquer la surbrillance uniquement si le solde est différent de 0.00
    for (const numero_facture in factures) {
      const { lastRow, lastSolde } = factures[numero_facture];
      if (Math.abs(lastSolde) > 0.00) { // Si lastSolde est différent de 0
        lastRow.getCell("value").getElement().classList.add("highlight-error");
      } else {
        // Optionnel : retirer la classe si elle a été ajoutée auparavant
        lastRow.getCell("value").getElement().classList.remove("highlight-error");
      }
    }

    tableAch.redraw();
  }

  tableAch.on("dataLoaded", function() {
    calculerSoldeCumule();
  });



// Fonction de mise à jour des données du tableau Achats en fonction des filtres
function updateTabulatorDataAchats() {
    const mois = document.getElementById("periode-achats").value;
    const annee = document.getElementById("annee-achats").value;
    const codeJournal = document.getElementById("journal-achats").value;

    let dataToSend = {};

    // Définir les filtres en fonction des valeurs renseignées
    if (codeJournal && (!mois || !annee || mois === 'selectionner un mois')) {
        dataToSend = { code_journal: codeJournal };
    } else if (mois && annee && !codeJournal) {
        dataToSend = { mois: mois, annee: annee };
    } else if (mois && annee && codeJournal) {
        dataToSend = { mois: mois, annee: annee, code_journal: codeJournal };
    }

    // Ajouter le filtre indiquant que l'on souhaite récupérer uniquement les achats
    dataToSend.operation_type = "Achats";

    console.log("Filtrage Achats appliqué :", dataToSend);

    fetch("/get-operations", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(dataToSend),
    })
    .then(response => response.json())
    .then(data => {
        console.log("Données reçues après filtrage Achats :", data);
        // Remplacer les données du tableau Achats
        tableAch.replaceData(data).then(() => {
            // Après remplacement, recalculer immédiatement le solde cumulé
            calculerSoldeCumule();
        });
    })
    .catch(error => {
        console.error("Erreur lors de la mise à jour Achats :", error);
    });
}

// Ajout des écouteurs pour les filtres Achats
document.getElementById("journal-achats").addEventListener("change", updateTabulatorDataAchats);
document.getElementById("periode-achats").addEventListener("change", updateTabulatorDataAchats);
document.getElementById("annee-achats").addEventListener("input", updateTabulatorDataAchats);

// Chargement initial des données Achats
updateTabulatorDataAchats();



//////////////////gestion ventes//////////////////////////////////////////////////////////////////////////////////////
function supprimerDoublonsLignes(lignes) {
    const lignesUniquement = []; // Tableau pour stocker les lignes sans doublons
    const idsDejaAjoutes = new Set(); // Un Set pour suivre les IDs déjà rencontrés

    lignes.forEach(ligne => {
        if (!idsDejaAjoutes.has(ligne.id)) {
            lignesUniquement.push(ligne);
            idsDejaAjoutes.add(ligne.id);
        }
    });

    return lignesUniquement;
}

const lignesSansDoublons = supprimerDoublonsLignes(tableVentes.getData());
console.log("Lignes après suppression des doublons (Ventes):", lignesSansDoublons);

async function ajouterLigneVentes(table, preRemplir = false, ligneActive = null) {
    let nouvellesLignes = [];
    let idCounter = table.getData().length + 1;

    // Récupérer les valeurs spécifiques aux ventes
    let codeJournal = document.querySelector("#journal-ventes").value;
    let moisActuel = new Date().getMonth() + 1;
    let filterVentes = document.querySelector('input[name="filter-ventes"]:checked')?.value;
    if (!filterVentes) {
        alert("Veuillez sélectionner un filtre.");
        return;
    }

    if (preRemplir && ligneActive) {
        nouvellesLignes = await ajouterLignePreRemplieVentes(idCounter, ligneActive, codeJournal, moisActuel, filterVentes);
        console.log("Lignes pré-remplies générées (Ventes):", nouvellesLignes);
    } else {
        let ligneVide = ajouterLigneVide(idCounter, ligneActive, codeJournal, moisActuel);
        nouvellesLignes.push(ligneVide);
    }

    if (Array.isArray(nouvellesLignes)) {
        nouvellesLignes.forEach(ligne => {
            table.addRow(ligne, false);
        });
    } else {
        console.error("Erreur: nouvellesLignes n'est pas un tableau.");
    }

    console.log("Toutes les lignes du tableau Ventes après ajout:", table.getData());

    // Supprimer les doublons si nécessaire (si vous souhaitez utiliser la même fonction pour Achats et Ventes)
    const lignesSansDoublons = supprimerDoublonsLignes(table.getData());
    console.log("Lignes après suppression des doublons (Ventes):", lignesSansDoublons);

    return nouvellesLignes;
}

async function ajouterLignePreRemplieVentes(idCounter, ligneActive, codeJournal, moisActuel, filterVentes) {
    let lignes = [];
    let ligne1 = { ...ligneActive, id: idCounter++ };
    let ligne2 = { ...ligneActive, id: idCounter++ };

    console.log("Ajout des lignes pré-remplies avec filterVentes:", filterVentes);

    // Pour les ventes, on considère le montant net saisi dans le champ 'debit'
    const netAmount = parseFloat(ligneActive.debit) || 0;
    console.log("Montant net de vente :", netAmount);

    if (filterVentes === 'contre-partie') {
        // Création de deux lignes pré-remplies
        // Ligne 1
        ligne1.compte = ligneActive.contre_partie || '';
        ligne1.contre_partie = ligneActive.compte || '';
        ligne1.debit = 0;
        ligne1.credit = 0;
        ligne1.piece = ligneActive.piece;
        ligne1.type_journal = codeJournal || '';
        lignes.push(ligne1);

        // Ligne 2
        ligne2.compte = ligneActive.compte_tva || '';
        ligne2.contre_partie = ligne1.compte || '';
        ligne2.debit = 0;
        ligne2.credit = 0;
        ligne2.piece = ligneActive.piece;
        ligne2.type_journal = codeJournal || '';
        lignes.push(ligne2);
    } else if (filterVentes === 'libre') {
        // Création d'une seule ligne vide pré-remplie
        ligne1.compte = '';
        ligne1.contre_partie = '';
        ligne1.debit = 0;
        ligne1.credit = 0;
        ligne1.piece = '';
        ligne1.type_journal = codeJournal || '';
        lignes.push(ligne1);
    }

    console.log("Lignes pré-remplies générées (Ventes):", lignes);

    // Calcul du crédit pour chaque ligne pré-remplie
    if (Array.isArray(lignes)) {
        for (let i = 0; i < lignes.length; i++) {
            const typeLigne = (i === 0) ? "ligne1" : "ligne2";
            console.log(`Calcul du crédit pour ${typeLigne} (Ventes):`, lignes[i]);
            await calculerCredit(lignes[i], typeLigne, netAmount);
            console.log(`Crédit calculé pour ${typeLigne} (Ventes):`, lignes[i].credit);
        }
    } else {
        console.error("Erreur: 'lignes' n'est pas un tableau:", lignes);
    }

    return lignes;
}

async function calculerCredit(rowData, typeLigne, debit) {
    // Extraire le taux de TVA depuis le champ rubrique_tva
    let tauxTVA = 0;
    if (rowData.rubrique_tva) {
        // Cette regex recherche une valeur numérique (avec éventuellement un point décimal) entre parenthèses suivie de '%'
        const match = rowData.rubrique_tva.match(/\(([\d\.]+)%\)/);
        if (match && match[1]) {
            tauxTVA = parseFloat(match[1]) / 100;
        }
    }
    console.log(`Calcul du crédit pour ${typeLigne}: Débit = ${debit}, Taux TVA = ${tauxTVA}`);

    if (isNaN(debit) || isNaN(tauxTVA)) {
        console.error("Débit ou Taux TVA invalides !");
        rowData.credit = 0;
        return;
    }

    // Pour les opérations de vente, on force le débit à 0
    rowData.debit = 0;

    // Calcul du montant net (crédit de la ligne1)
    const montantNet = debit / (1 + tauxTVA);

    let credit = 0;
    if (typeLigne === "ligne1") {
        // Ligne 1 : montant net
        credit = montantNet;
    } else if (typeLigne === "ligne2") {
        // Ligne 2 : crédit de la ligne1 * tauxTVA (soit la TVA seule)
        credit = montantNet * tauxTVA;
    }

    // Arrondir le résultat à deux décimales
    rowData.credit = parseFloat(credit.toFixed(2));
    console.log(`Crédit final pour ${typeLigne}: ${rowData.credit}`);
}

async function enregistrerLignesVentes() {
    try {
        // Récupérer les données actuelles du tableau Ventes
        let lignes = tableVentes.getData();
        console.log("📌 Données récupérées du tableau Ventes :", lignes);

        // Récupérer l'élément select et extraire le code du journal ainsi que la catégorie
        const journalSelect = document.querySelector("#journal-ventes");
        const codeJournal = journalSelect.value;
        if (!codeJournal) {
            alert("⚠️ Veuillez sélectionner un journal.");
            return;
        }
        const selectedOption = journalSelect.options[journalSelect.selectedIndex];
        const categorie = selectedOption ? selectedOption.getAttribute("data-type") : "";
        console.log("Catégorie extraite (Ventes) :", categorie);

        const selectedFilter = document.querySelector('input[name="filter-ventes"]:checked')?.value || null;

        // Mettre à jour le champ piece_justificative pour chaque facture
        lignes = updatePieceJustificative(lignes);

        // Filtrer les lignes valides à envoyer :
        // Pour Ventes, on n'exige pas que "compte" soit non vide, on envoie toutes les lignes qui ont un montant positif
        const lignesAEnvoyer = lignes.filter(ligne => (ligne.debit > 0 || ligne.credit > 0))
            .map(ligne => ({
                id: ligne.id || null,
                date: ligne.date || new Date().toISOString().slice(0, 10),
                numero_dossier: ligne.numero_dossier || 'N/A',

                numero_facture: ligne.numero_facture || 'N/A',

                compte: ligne.compte || '',
                debit: ligne.debit ? parseFloat(ligne.debit) : 0,
                credit: ligne.credit ? parseFloat(ligne.credit) : 0,
                contre_partie: ligne.contre_partie || '',
                rubrique_tva: ligne.rubrique_tva || '',
                compte_tva: ligne.compte_tva || '',
                type_journal: codeJournal,
                categorie: categorie, // Ajout du champ catégorie
                piece_justificative: ligne.piece_justificative || '',
                libelle: ligne.libelle || '',
                filtre_selectionne: selectedFilter,
                value: typeof ligne.solde_cumule !== "undefined" ? ligne.solde_cumule : ""
            }));

        console.log("📤 Données envoyées (Ventes) :", lignesAEnvoyer);

        if (lignesAEnvoyer.length === 0) {
            alert("⚠️ Aucune ligne valide à enregistrer.");
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch('/lignes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ lignes: lignesAEnvoyer })
        });

        if (!response.ok) {
            console.error("❌ Erreur serveur (Ventes) :", response.status, response.statusText);
            alert(`Erreur lors de l'enregistrement (Ventes) : ${response.statusText}`);
            return;
        }

        const result = await response.json();
        console.log("📥 Réponse du serveur (Ventes) :", result);

        if (Array.isArray(result)) {
            tableVentes.setData(result);
            console.log("✅ Tableau Ventes mis à jour avec les nouvelles données.");
        } else if (result && Array.isArray(result.data)) {
            tableVentes.setData(result.data);
            console.log("✅ Tableau Ventes mis à jour avec les nouvelles données.");
        } else {
            console.warn("⚠️ Format inattendu de la réponse (Ventes) :", result);
            alert("Aucune donnée valide reçue du serveur (Ventes).");
            return;
        }

        calculerSoldeCumuleVentes();

        // Ajouter une ligne vide si nécessaire
        const dataActuelle = tableVentes.getData();
        const derniereLigne = dataActuelle[dataActuelle.length - 1];
        if (!derniereLigne || derniereLigne.compte !== '') {
            tableVentes.addRow({
                id: null,
                compte: '',
                contre_partie: '',
                compte_tva: '',
                debit: 0,
                credit: 0,
                piece_justificative: '',
                libelle: '',
                rubrique_tva: '',
                type_journal: codeJournal,
                value: ""
            });
        }
    } catch (error) {
        console.error("🚨 Erreur lors de l'enregistrement (Ventes) :", error);
        alert("❌ Une erreur s'est produite lors de l'enregistrement (Ventes). Vérifiez la console pour plus de détails.");
    }
}

async function ecouterEntrerVentes(table) {
    table.element.addEventListener("keydown", async function (event) {
        if (event.key === "Enter") {
            event.preventDefault();

            const selectedRows = table.getSelectedRows();
            if (selectedRows.length === 0) {
                console.error("Aucune ligne active trouvée (Ventes)");
                return;
            }

            const ligneActive = selectedRows[0].getData();
            let nouvellesLignes = await ajouterLigneVentes(table, true, ligneActive);

            if (!Array.isArray(nouvellesLignes)) {
                nouvellesLignes = [nouvellesLignes];
            }

            console.log("Lignes ajoutées (Ventes) :", nouvellesLignes);

            // Récupérer les données actuelles du tableau Ventes
            let dataActuelle = table.getData();
            // Pour Ventes, nous ne filtrons pas sur le champ "compte" afin de conserver
            // les lignes pré-remplies même si le champ compte est renseigné.
            table.setData(dataActuelle);

            // Vérifier si la dernière ligne est vide ; si ce n'est pas le cas, ajouter une ligne vide
            const derniereLigne = dataActuelle[dataActuelle.length - 1];
            if (!derniereLigne || derniereLigne.compte !== '') {
                const nouvelleLigneVide = {
                    id: dataActuelle.length + 1,
                    compte: '',
                    contre_partie: '',
                    compte_tva: '',
                    debit: 0,
                    credit: 0,
                    piece_justificative: '', // La pièce sera générée lors de l'enregistrement
                    type_journal: document.querySelector("#journal-ventes").value,
                    value: ""
                };
                table.addRow(nouvelleLigneVide);
            } else {
                console.log("Ligne vide déjà présente (Ventes), pas besoin d'en ajouter une autre.");
            }

            // Enregistrer les lignes après l'ajout
            await enregistrerLignesVentes();
        }
    });
}




// Fonction de mise à jour des données du tableau Ventes en fonction des filtres
function updateTabulatorDataVentes() {
    const mois = document.getElementById("periode-ventes").value;
    const annee = document.getElementById("annee-ventes").value;
    const codeJournal = document.getElementById("journal-ventes").value;

    let dataToSend = {};

    // Définir les filtres en fonction des valeurs renseignées
    if (codeJournal && (!mois || !annee || mois === 'selectionner un mois')) {
        dataToSend = { code_journal: codeJournal };
    } else if (mois && annee && !codeJournal) {
        dataToSend = { mois: mois, annee: annee };
    } else if (mois && annee && codeJournal) {
        dataToSend = { mois: mois, annee: annee, code_journal: codeJournal };
    }

    // Ajouter le filtre indiquant que l'on souhaite récupérer uniquement les ventes
    dataToSend.operation_type = "Ventes";

    console.log("Filtrage Ventes appliqué :", dataToSend);

    fetch("/get-operations", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(dataToSend),
    })
    .then(response => response.json())
    .then(data => {
        console.log("Données reçues après filtrage Ventes :", data);
        // Remplacer les données du tableau Ventes
        tableVentes.replaceData(data).then(() => {
            // Après remplacement, recalculer immédiatement le solde cumulé
            calculerSoldeCumuleVentes();
        });
    })
    .catch(error => {
        console.error("Erreur lors de la mise à jour Ventes :", error);
    });
}

// Ajout des écouteurs pour les filtres Ventes
document.getElementById("journal-ventes").addEventListener("change", updateTabulatorDataVentes);
document.getElementById("periode-ventes").addEventListener("change", updateTabulatorDataVentes);
document.getElementById("annee-ventes").addEventListener("input", updateTabulatorDataVentes);

// Chargement initial des données Ventes
updateTabulatorDataVentes();


function calculerSoldeCumuleVentes() {
    const rows = tableVentes.getRows();
    const groupSums = {};
    const factures = {};

    rows.forEach((row) => {
      const data = row.getData();
      const key = `${data.numero_facture}`;

      if (typeof groupSums[key] === "undefined") {
        groupSums[key] = 0;
      }

      const debit = parseFloat(data.debit) || 0;
      const credit = parseFloat(data.credit) || 0;
      let nouveauSolde = groupSums[key] + debit - credit;

      // Correction pour ne pas afficher -0.00
      if (Math.abs(nouveauSolde) < Number.EPSILON) {
        nouveauSolde = 0;
      }

      // Format d'affichage à deux décimales
      const displayValue = nouveauSolde.toFixed(2);

      data.value = displayValue;
      groupSums[key] = nouveauSolde;
      row.update({ value: displayValue });

      if (!factures[data.numero_facture]) {
        factures[data.numero_facture] = { lastRow: row, lastSolde: nouveauSolde };
      } else {
        factures[data.numero_facture].lastRow = row;
        factures[data.numero_facture].lastSolde = nouveauSolde;
      }
    });

    // Appliquer la surbrillance uniquement si le solde est différent de 0.00
    for (const numero_facture in factures) {
      const { lastRow, lastSolde } = factures[numero_facture];
      if (Math.abs(lastSolde) > 0.00) {  // Si lastSolde est différent de 0
        lastRow.getCell("value").getElement().classList.add("highlight-error");
      } else {
        // Optionnel : supprimer la classe si elle a été appliquée auparavant
        lastRow.getCell("value").getElement().classList.remove("highlight-error");
      }
    }

    tableVentes.redraw();
  }

  tableVentes.on("dataLoaded", function() {
    calculerSoldeCumuleVentes();
  });



// Vous pouvez aussi appeler la fonction dès le chargement complet de la page, si besoin
document.addEventListener("DOMContentLoaded", function() {
    calculerSoldeCumuleVentes();
});
// Vous pouvez aussi appeler la fonction dès le chargement complet de la page, si besoin
document.addEventListener("DOMContentLoaded", function() {
    calculerSoldeCumule();
});
// Initialiser l'écouteur d'événements pour chaque table
ecouterEntrerVentes(tableVentes);
ecouterEntrer(tableAch);
ecouterEntrer(tableBanque);
ecouterEntrer(tableCaisse);
ecouterEntrer(tableOP);
tabulatorManager.applyToTabulator(tableAch);
tabulatorManager.applyToTabulator(tableVentes);

                 // Gestionnaire pour importer les données
                 document.getElementById("import-ventes").addEventListener("click", function () {
                alert("Fonction d'import non implémentée !");
                // Ajoutez ici votre logique pour l'importation (par ex. ouvrir un modal ou lire un fichier)
            });

            // Gestionnaire pour exporter vers Excel
            document.getElementById("export-ventesExcel").addEventListener("click", function () {
                tableVentes .download("xlsx", "ventes.xlsx", { sheetName: "Ventes" });
            });

            // Gestionnaire pour exporter vers PDF
            document.getElementById("export-ventesPDF").addEventListener("click", function () {
                tableVentes .download("pdf", "ventes.pdf", {
                    orientation: "portrait", // Orientation de la page
                    title: "Rapport des Ventes", // Titre du rapport
                });
            });

          // Gestionnaire pour supprimer une ligne sélectionnée
document.getElementById("delete-row-btn").addEventListener("click", function () {
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let selectedRows = tableAch.getSelectedRows(); // Récupérer les lignes sélectionnées dans Tabulator

    if (selectedRows.length > 0) {
        // Tableau des identifiants des lignes sélectionnées
        let rowIds = selectedRows.map(row => row.getData().id); // Supposons que chaque ligne a un identifiant unique 'id'

        // Supprimer les lignes de l'interface utilisateur
        selectedRows.forEach(function (row) {
            row.delete(); // Supprimer chaque ligne sélectionnée du tableau Tabulator
        });

        // Envoyer une requête pour supprimer les lignes dans la base de données
        fetch('/delete-rows', { // Assurez-vous que cette route existe sur votre serveur
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken // Ajoutez le token CSRF ici
            },
            body: JSON.stringify({ rowIds: rowIds }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Les lignes sélectionnées ont été supprimées.");
            } else {
                alert("Erreur lors de la suppression des lignes.");
            }
        })
        .catch(error => {
            console.error("Erreur lors de la suppression des lignes :", error);
            alert("Erreur lors de la suppression des lignes.");
        });
    } else {
        alert("Veuillez sélectionner une ou plusieurs lignes à supprimer.");
    }
});

   // Gestionnaire pour supprimer une ligne sélectionnée
   document.getElementById("delete-row-btn").addEventListener("click", function () {
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let selectedRows = tableVentes.getSelectedRows(); // Récupérer les lignes sélectionnées dans Tabulator

    if (selectedRows.length > 0) {
        // Tableau des identifiants des lignes sélectionnées
        let rowIds = selectedRows.map(row => row.getData().id); // Supposons que chaque ligne a un identifiant unique 'id'

        // Supprimer les lignes de l'interface utilisateur
        selectedRows.forEach(function (row) {
            row.delete(); // Supprimer chaque ligne sélectionnée du tableau Tabulator
        });

        // Envoyer une requête pour supprimer les lignes dans la base de données
        fetch('/delete-rows', { // Assurez-vous que cette route existe sur votre serveur
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken // Ajoutez le token CSRF ici
            },
            body: JSON.stringify({ rowIds: rowIds }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Les lignes sélectionnées ont été supprimées.");
            } else {
                alert("Erreur lors de la suppression des lignes.");
            }
        })
        .catch(error => {
            console.error("Erreur lors de la suppression des lignes :", error);
            alert("Erreur lors de la suppression des lignes.");
        });
    } else {
        alert("Veuillez sélectionner une ou plusieurs lignes à supprimer.");
    }
});


            document.getElementById("print-tableV").addEventListener("click", function () {
    if (tableVentes ) {
        tableVentes .print(false, true); // Utilise la méthode d'impression de Tabulator
    } else {
        console.error("La table Tabulator n'est pas initialisée.");
    }
});


    } catch (error) {
        console.error("Erreur lors de l'initialisation des tables :", error);
    }
})();

// function formatDate(cell) {
//     let dateValue = cell.getValue();
//     if (dateValue) {
//         const dt = DateTime.fromISO(dateValue);
//         return dt.isValid ? dt.toFormat('dd/MM/yyyy') : "Date invalide";
//     }
//     return "";
// }





// Configuration du tableau Trésorerie
var tableBanque = new Tabulator("#table-Banque", {
    layout: "fitColumns",
    height: "500px",
    // rowHeader:{headerSort:false, resizable: true, frozen:true,width:50,minwidth:40, headerHozAlign:"center", hozAlign:"center", formatter:"rowSelection", titleFormatter:"rowSelection", cellClick:function(e, cell){
    //   cell.getRow().toggleSelect();
    // }},
    selectable: true,
    data: Array(1).fill({}),
    columns: [
        { title: "ID", field: "id", visible: false },
        {
            title: "Date",
            field: "date",
            hozAlign: "center",
            headerFilter: "input",

            sorter: "date",
            editor: function(cell, onRendered, success, cancel) {
                const input = document.createElement("input");
                input.type = "text"; // Utilisation d'un champ de texte pour flatpickr
                const currentValue = cell.getValue();
                input.value = currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '';
                input.placeholder = "jj/mm/aaaa";
                flatpickr(input, {
                    dateFormat: "d/m/Y",
                    defaultDate: currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '',
                    onChange: function(selectedDates) {
                        success(luxon.DateTime.fromJSDate(selectedDates[0]).toISODate());
                    },
                    allowInput: true,
                });
                onRendered(function() {
                    input.focus();
                });
                return input;
            },
            formatter: function(cell) {
                let dateValue = cell.getValue();
                if (dateValue) {
                    const dt = luxon.DateTime.fromISO(dateValue);
                    return dt.isValid ? dt.toFormat('dd/MM/yyyy') : "Date invalide";
                }
                return "";
            },
        },
        {
    title: "Mode de paiement",
    field: "Mode_pay",
    headerFilter: "input", // Permet de rechercher dans la colonne
    editor: "list", // Type d'éditeur pour une liste déroulante
    editorParams: {
        values: ["Espèces", "Chèques", "Virement", "Effet", "Prélèvements", "Compensations", "Autres"], // Options dans la liste
        clearable: true, // Permet de réinitialiser à une valeur vide
        verticalNavigation: "editor", // Navigation clavier pour ouvrir l'éditeur
    },
},
        { title: "Compte", field: "compte", headerFilter: "input", editor: "input" },

        { title: "Libellé", field: "libelle", headerFilter: "input", editor: "input" },
        {
    title: "Débit",
    field: "debit",
    headerFilter: "input",
    editor: "number", // Permet l'édition en tant que nombre
    bottomCalc: "sum", // Calcul du total dans le bas de la colonne
    formatter: function(cell) {
        // Formater pour afficher 0.00 si la cellule est vide ou nulle
        const value = cell.getValue();
        return value ? parseFloat(value).toFixed(2) : "0.00";
    },
    mutatorEdit: function(value) {
        // Retourner "0.00" comme valeur par défaut si vide lors de l'édition
        return value || "0.00";

        // Mettre à jour la valeur du champ "Débit"
        cell.setValue(debit.toFixed(2)); // Format en 2 décimales
    }
},
{ title: "Crédit", field: "credit", headerFilter: "input",  editor: "number", // Permet l'édition en tant que nombre
    bottomCalc: "sum", // Calcul du total dans le bas de la colonne
    formatter: function(cell) {
        // Formater pour afficher 0.00 si la cellule est vide ou nulle
        const value = cell.getValue();
        return value ? parseFloat(value).toFixed(2) : "0.00";
    },

},
        { title: "N° facture lettrée", field: "fact_lettrer", headerFilter: "input", editor: "input" },
        { title: "Taux RAS TVA", field: "taux_ras_tva", headerFilter: "input", editor: "input" },
        { title: "Nature de l'opération", field: "nature_op", headerFilter: "input" , editor: "input" },
        { title: "Date lettrage", field: "date_lettrage", headerFilter: "input", editor: "input" },
        { title: "Contre-Partie", field: "contre_partie", headerFilter: "input" , editor: "input" },
        { title: "Pièce justificative", field:"piece_justificative"
, headerFilter: "input" , editor: "input" },
{
            title: "Sélectionner",
            headerSort: false,
            resizable: true,
            frozen: true,
            width: 50,
            minWidth: 40,
            headerHozAlign: "center",
            hozAlign: "center",
            formatter: "rowSelection",
            titleFormatter: "rowSelection",
            cellClick: function(e, cell){
                cell.getRow().toggleSelect();
            }
        },


    ],
    footerElement: "<table style='width: 30%; margin-top: 10px; border-collapse: collapse;'>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Débit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-debit-Banque'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Crédit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-credit-Banque'></span></td>" +
                    "</tr>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Débiteur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-debit-Banque'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Créditeur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-credit-Banque'></span></td>" +
                    "</tr>" +
                    "</table>", // Footer sous forme de tableau
    rowFormatter: function(row) {
        let debitTotal = 0;
        let creditTotal = 0;

        row.getTable().getRows().forEach(function(r) {
            debitTotal += parseFloat(r.getData().montant || 0);
            creditTotal += parseFloat(r.getData().montant || 0);
        });

        let soldeDebiteur = debitTotal - creditTotal;
        let soldeCrediteur = creditTotal - debitTotal;

        // Mise à jour des éléments dans le footer
        document.getElementById('cumul-debit-Banque').innerText = formatCurrency(debitTotal);
        document.getElementById('cumul-credit-Banque').innerText = formatCurrency(creditTotal);
        document.getElementById('solde-debit-Banque').innerText = formatCurrency(soldeDebiteur);
        document.getElementById('solde-credit-Banque').innerText = formatCurrency(soldeCrediteur);
    }
});
tabulatorManager.applyToTabulator(tableBanque);
// Configuration du tableau Trésorerie
var tableCaisse = new Tabulator("#table-Caisse", {
    layout: "fitColumns",
    height: "500px",
    // rowHeader:{headerSort:false, resizable: true, frozen:true,width:50,minwidth:40, headerHozAlign:"center", hozAlign:"center", formatter:"rowSelection", titleFormatter:"rowSelection", cellClick:function(e, cell){
    //   cell.getRow().toggleSelect();
    // }},
    selectable: true,
    data: Array(1).fill({}),
    columns: [
        { title: "ID", field: "id", visible: false },
        {
            title: "Date",
            field: "date",
            hozAlign: "center",
            headerFilter: "input",

            sorter: "date",
            editor: function(cell, onRendered, success, cancel) {
                const input = document.createElement("input");
                input.type = "text"; // Utilisation d'un champ de texte pour flatpickr
                const currentValue = cell.getValue();
                input.value = currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '';
                input.placeholder = "jj/mm/aaaa";
                flatpickr(input, {
                    dateFormat: "d/m/Y",
                    defaultDate: currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '',
                    onChange: function(selectedDates) {
                        success(luxon.DateTime.fromJSDate(selectedDates[0]).toISODate());
                    },
                    allowInput: true,
                });
                onRendered(function() {
                    input.focus();
                });
                return input;
            },
            formatter: function(cell) {
                let dateValue = cell.getValue();
                if (dateValue) {
                    const dt = luxon.DateTime.fromISO(dateValue);
                    return dt.isValid ? dt.toFormat('dd/MM/yyyy') : "Date invalide";
                }
                return "";
            },
        },
        {
    title: "Mode de paiement",
    field: "Mode_pay",
    headerFilter: "input", // Permet de rechercher dans la colonne
    editor: "list", // Type d'éditeur pour une liste déroulante
    editorParams: {
        values: ["Espèces", "Chèques", "Virement", "Effet", "Prélèvements", "Compensations", "Autres"], // Options dans la liste
        clearable: true, // Permet de réinitialiser à une valeur vide
        verticalNavigation: "editor", // Navigation clavier pour ouvrir l'éditeur
    },
},
        { title: "Compte", field: "compte", headerFilter: "input", editor: "input" },

        { title: "Libellé", field: "libelle", headerFilter: "input", editor: "input" },
        {
    title: "Débit",
    field: "debit",
    headerFilter: "input",
    editor: "number", // Permet l'édition en tant que nombre
    bottomCalc: "sum", // Calcul du total dans le bas de la colonne
    formatter: function(cell) {
        // Formater pour afficher 0.00 si la cellule est vide ou nulle
        const value = cell.getValue();
        return value ? parseFloat(value).toFixed(2) : "0.00";
    },
    mutatorEdit: function(value) {
        // Retourner "0.00" comme valeur par défaut si vide lors de l'édition
        return value || "0.00";

        // Mettre à jour la valeur du champ "Débit"
        cell.setValue(debit.toFixed(2)); // Format en 2 décimales
    }
},
{ title: "Crédit", field: "credit", headerFilter: "input",  editor: "number", // Permet l'édition en tant que nombre
    bottomCalc: "sum", // Calcul du total dans le bas de la colonne
    formatter: function(cell) {
        // Formater pour afficher 0.00 si la cellule est vide ou nulle
        const value = cell.getValue();
        return value ? parseFloat(value).toFixed(2) : "0.00";
    },
    mutatorEdit: function(value) {
        // Retourner "0.00" comme valeur par défaut si vide lors de l'édition
        return value || "0.00";
      // Mettre à jour la valeur du champ "Débit"
        cell.setValue(debit.toFixed(2)); // Format en 2 décimales
    }
},
        { title: "N° facture lettrée", field: "fact_lettrer", headerFilter: "input", editor: "input" },
        { title: "Taux RAS TVA", field: "taux_ras_tva", headerFilter: "input", editor: "input" },
        { title: "Nature de l'opération", field: "nature_op", headerFilter: "input" , editor: "input" },
        { title: "Date lettrage", field: "date_lettrage", headerFilter: "input", editor: "input" },
        { title: "Contre-Partie", field: "contre_partie", headerFilter: "input" , editor: "input" },
        { title: "Pièce justificative", field:"piece_justificative"
, headerFilter: "input" , editor: "input" },
{
            title: "Sélectionner",
            headerSort: false,
            resizable: true,
            frozen: true,
            width: 50,
            minWidth: 40,
            headerHozAlign: "center",
            hozAlign: "center",
            formatter: "rowSelection",
            titleFormatter: "rowSelection",
            cellClick: function(e, cell){
                cell.getRow().toggleSelect();
            }
        },


    ],
    footerElement: "<table style='width: 30%; margin-top: 10px; border-collapse: collapse;'>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Débit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-debit-Caisse'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Crédit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-credit-Caisse'></span></td>" +
                    "</tr>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Débiteur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-debit-Caisse'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Créditeur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-credit-Caisse'></span></td>" +
                    "</tr>" +
                    "</table>", // Footer sous forme de tableau
    rowFormatter: function(row) {
        let debitTotal = 0;
        let creditTotal = 0;

        row.getTable().getRows().forEach(function(r) {
            debitTotal += parseFloat(r.getData().montant || 0);
            creditTotal += parseFloat(r.getData().montant || 0);
        });

        let soldeDebiteur = debitTotal - creditTotal;
        let soldeCrediteur = creditTotal - debitTotal;

        // Mise à jour des éléments dans le footer
        document.getElementById('cumul-debit-Caisse').innerText = formatCurrency(debitTotal);
        document.getElementById('cumul-credit-Caisse').innerText = formatCurrency(creditTotal);
        document.getElementById('solde-debit-Caisse').innerText = formatCurrency(soldeDebiteur);
        document.getElementById('solde-credit-Caisse').innerText = formatCurrency(soldeCrediteur);
    }
});
tabulatorManager.applyToTabulator(tableCaisse);

// Configuration du tableau Opérations Diverses
var tableOP = new Tabulator("#table-operations-diverses", {
    layout: "fitColumns",
    height: "500px",
    // rowHeader:{headerSort:false, resizable: true, frozen:true,width:50,minwidth:40, headerHozAlign:"center", hozAlign:"center", formatter:"rowSelection", titleFormatter:"rowSelection", cellClick:function(e, cell){
    //   cell.getRow().toggleSelect();
    // }},
    selectable: true,
    data: Array(1).fill({}),
    columns: [
        { title: "ID", field: "id", visible: false },
        {
            title: "Date",
            field: "date",
            hozAlign: "center",
            headerFilter: "input",

            sorter: "date",
            editor: function(cell, onRendered, success, cancel) {
                const input = document.createElement("input");
                input.type = "text"; // Utilisation d'un champ de texte pour flatpickr
                const currentValue = cell.getValue();
                input.value = currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '';
                input.placeholder = "jj/mm/aaaa";
                flatpickr(input, {
                    dateFormat: "d/m/Y",
                    defaultDate: currentValue ? luxon.DateTime.fromISO(currentValue).toFormat('dd/MM/yyyy') : '',
                    onChange: function(selectedDates) {
                        success(luxon.DateTime.fromJSDate(selectedDates[0]).toISODate());
                    },
                    allowInput: true,
                });
                onRendered(function() {
                    input.focus();
                });
                return input;
            },
            formatter: function(cell) {
                let dateValue = cell.getValue();
                if (dateValue) {
                    const dt = luxon.DateTime.fromISO(dateValue);
                    return dt.isValid ? dt.toFormat('dd/MM/yyyy') : "Date invalide";
                }
                return "";
            },
        },
        { title: "N°Facture", field: "numero_facture", headerFilter: "input", editor: "input" },

        { title: "Compte", field: "compte" , headerFilter: "input", editor: "input"},
        { title: "Libellé", field: "libelle", editor: "input" , headerFilter: "input",},
        {
    title: "Débit",
    field: "debit",
    headerFilter: "input",
    editor: "number", // Permet l'édition en tant que nombre
    bottomCalc: "sum", // Calcul du total dans le bas de la colonne
    formatter: function(cell) {
        // Formater pour afficher 0.00 si la cellule est vide ou nulle
        const value = cell.getValue();
        return value ? parseFloat(value).toFixed(2) : "0.00";
    },
    mutatorEdit: function(value) {
        // Retourner "0.00" comme valeur par défaut si vide lors de l'édition
        return value || "0.00";



        // Mettre à jour la valeur du champ "Débit"
        cell.setValue(debit.toFixed(2)); // Format en 2 décimales
    }
},
      { title: "Crédit", field: "credit", headerFilter: "input", editor: "number", bottomCalc: "sum" },
        {
            title: "Sélectionner",
            headerSort: false,
            resizable: true,
            frozen: true,
            width: 50,
            minWidth: 40,
            headerHozAlign: "center",
            hozAlign: "center",
            formatter: "rowSelection",
            titleFormatter: "rowSelection",
            cellClick: function(e, cell){
                cell.getRow().toggleSelect();
            }
        },

     ],
    footerElement: "<table style='width: 30%; margin-top: 10px; border-collapse: collapse;'>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Débit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-debit-operations-diverses'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Cumul Crédit :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='cumul-credit-operations-diverses'></span></td>" +
                    "</tr>" +
                    "<tr>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Débiteur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-debit-operations-diverses'></span></td>" +
                        "<td style='padding: 10px; text-align: left; font-weight: bold;'>Solde Créditeur :</td>" +
                        "<td style='padding: 10px; text-align: right; font-size: 14px;'><span id='solde-credit-operations-diverses'></span></td>" +
                    "</tr>" +
                    "</table>", // Footer sous forme de tableau
    rowFormatter: function(row) {
        let debitTotal = 0;
        let creditTotal = 0;

        row.getTable().getRows().forEach(function(r) {
            debitTotal += parseFloat(r.getData().montant || 0);
            creditTotal += parseFloat(r.getData().montant || 0);
        });

        let soldeDebiteur = debitTotal - creditTotal;
        let soldeCrediteur = creditTotal - debitTotal;

        // Mise à jour des éléments dans le footer
        document.getElementById('cumul-debit-operations-diverses').innerText = formatCurrency(debitTotal);
        document.getElementById('cumul-credit-operations-diverses').innerText = formatCurrency(creditTotal);
        document.getElementById('solde-debit-operations-diverses').innerText = formatCurrency(soldeDebiteur);
        document.getElementById('solde-credit-operations-diverses').innerText = formatCurrency(soldeCrediteur);
    }



});

tabulatorManager.applyToTabulator(tableOP);



            // Gestion des onglets
            $('.tab').on('click', function () {
                const tabId = $(this).data('tab');
                $('.tab').removeClass('active');
                $('.tab-content').removeClass('active');
                $(this).addClass('active');
                $('#' + tabId).addClass('active');
            });




        });
