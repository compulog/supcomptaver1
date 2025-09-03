document.addEventListener('DOMContentLoaded', function () {
    // Sélectionner tous les onglets et les rendre focusables
    const tabs = document.querySelectorAll('.tab');

    tabs.forEach(tab => {
      tab.setAttribute('tabindex', '0'); // Permettre le focus au clavier
    });

    // Activer l'onglet "Achats" par défaut et afficher son contenu
    const defaultTab = document.querySelector('.tab[data-tab="achats"]');
    if (defaultTab) {
      activerOnglet(defaultTab);
    }

    // Fonction pour activer un onglet et afficher son contenu
    function activerOnglet(tab) {
      // Désactiver tous les onglets et masquer leur contenu
      tabs.forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

      // Activer l'onglet sélectionné
      tab.classList.add('active');

      // Afficher le contenu correspondant
      const tabId = tab.getAttribute('data-tab');
      const tabContent = document.getElementById(tabId);
      if (tabContent) {
        tabContent.classList.add('active');
      }

      // Mettre à jour les styles des onglets
      updateTabsStyles();
    }

    // Mettre à jour les styles des onglets
    function updateTabsStyles() {
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
    }

    // Ajouter les écouteurs pour chaque onglet
    tabs.forEach((tab, index) => {
      tab.addEventListener('click', function () {
        activerOnglet(tab);
      });

      tab.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          activerOnglet(tab);
        } else if (e.key === 'ArrowRight') {
          // Aller à l'onglet suivant
          const nextIndex = index + 1;
          if (nextIndex < tabs.length) {
            activerOnglet(tabs[nextIndex]);
            tabs[nextIndex].focus();
          }
        } else if (e.key === 'ArrowLeft') {
          // Aller à l'onglet précédent
          const prevIndex = index - 1;
          if (prevIndex >= 0) {
            activerOnglet(tabs[prevIndex]);
            tabs[prevIndex].focus();
          }
        }
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
function remplirRubriquesTvaVente(selectId, selectedValue = null) {
    $.ajax({
        url: '/get-rubriques-tva-vente',  // URL mise à jour
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            var select = $("#" + selectId);

            // Réinitialisation de Select2 s'il est déjà initialisé
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2("destroy");
            }
            select.empty();
            select.append(new Option("Sélectionnez une Rubrique", ""));

            let categoriesArray = [];
            $.each(data.rubriques, function (categorie, rubriquesObj) {
                let categories = categorie.split('/').map(cat => cat.trim());
                let mainCategory = categories[0];
                let subCategory = categories[1] ? categories[1].trim() : '';
                categoriesArray.push({
                    mainCategory: mainCategory,
                    subCategory: subCategory,
                    rubriques: rubriquesObj.rubriques
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
                        let displayValue = `${rubrique.Num_racines}: ${rubrique.Nom_racines} (${parseFloat(rubrique.Taux).toFixed(2)}%)`;
                        let option = new Option(displayValue, displayValue);
                        option.setAttribute('data-search-text', `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.mainCategory}`);
                        select.append(option);
                    }
                });

            });

            // Initialisation de Select2 sur le select
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
                },
                placeholder: "Rechercher une rubrique TVA...",
                allowClear: true
            });

            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des rubriques TVA :', textStatus, errorThrown);
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

    const rowIndex = cell.getRow().getPosition();
    const field = cell.getField();
    const storageKey = "tabulator_edit_focus";

    let validated = false;

    onRendered(() => {
        input.focus();

        // Sauvegarder la position de l'édition dans localStorage
        localStorage.setItem(storageKey, JSON.stringify({ rowIndex, field }));
    });

    input.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === "Tab") {
            validated = true;
            localStorage.removeItem(storageKey);
            success(input.value);
        }
    });

    input.addEventListener("blur", (e) => {
        if (!validated) {
            e.preventDefault();
            e.stopImmediatePropagation();
            // Re-focus si le blur ne vient pas d’un Enter/Tab
            setTimeout(() => input.focus(), 10);
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
    if (editorParams && editorParams.values) {
        options = Array.isArray(editorParams.values)
            ? editorParams.values
            : Object.values(editorParams.values);
    }

    // Variables pour la gestion du focus et de la validation
    var rowIndex = cell.getRow().getPosition();
    var field = cell.getField();
    var storageKey = "tabulator_edit_focus";
    var validated = false;

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

    var selectedIndex = -1; // Suivi de l'élément sélectionné dans la liste avec les flèches

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

        filtered.forEach(function(opt, index) {
            // On découpe l'option en deux parties : le compte et l'intitulé
            var [compte, intitule] = opt.split(" - ");

            var item = document.createElement("div");
            item.textContent = `${compte} - ${intitule}`;  // Affiche "compte - intitulé"
            item.style.padding = "5px";
            item.style.cursor = "pointer";
            item.style.borderBottom = "1px solid #eee";

            // Highlighting selected item
            if (index === selectedIndex) {
                item.style.backgroundColor = "#ddd";
            }

            item.addEventListener("mousedown", function(e) {
                e.preventDefault();
                validated = true;
                localStorage.removeItem(storageKey);
                input.value = compte;  // Seul le compte est affiché dans l'input
                dropdown.style.display = "none";
                success(compte);  // Renvoi du compte seulement
            });
            dropdown.appendChild(item);
        });

        if (filtered.length > 0) {
            positionDropdown();
            dropdown.style.display = "block";
        } else {
            dropdown.style.display = "none";
        }
    }

    input.addEventListener("input", updateDropdown);
    input.addEventListener("focus", updateDropdown);

    // Navigation avec les flèches
    input.addEventListener("keydown", function(e) {
        if (e.key === "ArrowDown") {
            e.preventDefault();
            if (selectedIndex < options.length - 1) {
                selectedIndex++;
                updateDropdown();
            }
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            if (selectedIndex > 0) {
                selectedIndex--;
                updateDropdown();
            }
        } else if (e.key === "Enter" || e.key === "Tab") {
            e.preventDefault();
            if (selectedIndex !== -1) {
                // On récupère le compte uniquement
                var selectedOption = options[selectedIndex];
                var [compte] = selectedOption.split(" - ");
                input.value = compte;  // Affichage du compte seul
                dropdown.style.display = "none";
                success(compte);
            } else {
                dropdown.style.display = "none";
            }
        } else if (e.key === "Escape") {
            cancel();
            dropdown.style.display = "none";
        }
    });

    // Gestion du blur pour éviter la perte de focus non validée
    input.addEventListener("blur", function(e) {
        if (!validated) {
            e.preventDefault();
            e.stopImmediatePropagation();
            setTimeout(function() {
                input.focus();
            }, 10);
        } else {
            dropdown.style.display = "none";
        }
    });

    onRendered(function() {
        input.focus();
        // Sauvegarder la position de l'édition dans localStorage
        localStorage.setItem(storageKey, JSON.stringify({ rowIndex: rowIndex, field: field }));
        updateDropdown();
    });

    return container;
}

 function customListEditorRub(cell, onRendered, success, cancel, editorParams) {
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
    if (editorParams && editorParams.values) {
        options = Array.isArray(editorParams.values)
            ? editorParams.values
            : Object.values(editorParams.values);
    }

    // Variables pour la gestion du focus et de la validation
    var rowIndex = cell.getRow().getPosition();
    var field = cell.getField();
    var storageKey = "tabulator_edit_focus";
    var validated = false;

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
        var filtered = options.filter(function(opt) {
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
                validated = true;
                localStorage.removeItem(storageKey);
                input.value = opt;
                dropdown.style.display = "none";
                success(opt);
            });
            dropdown.appendChild(item);
        });
        if (filtered.length > 0) {
            positionDropdown();
            dropdown.style.display = "block";
        } else {
            dropdown.style.display = "none";
        }
    }

    input.addEventListener("input", updateDropdown);
    input.addEventListener("focus", updateDropdown);

    // Validation sur "Enter" ou "Tab"
    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter" || e.key === "Tab") {
            e.preventDefault();
            validated = true;
            localStorage.removeItem(storageKey);
            dropdown.style.display = "none";
            success(input.value);
        } else if (e.key === "Escape") {
            cancel();
        }
    });

    // Gestion du blur pour éviter la perte de focus non validée
    input.addEventListener("blur", function(e) {
        if (!validated) {
            e.preventDefault();
            e.stopImmediatePropagation();
            setTimeout(function() {
                input.focus();
            }, 10);
        } else {
            dropdown.style.display = "none";
        }
    });

    onRendered(function() {
        input.focus();
        // Sauvegarder la position de l'édition dans localStorage
        localStorage.setItem(storageKey, JSON.stringify({ rowIndex: rowIndex, field: field }));
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




  /********** Éditeur pour la cellule "Pièce" **********/
  function pieceEditor(cell, onRendered, success, cancel, editorParams) {
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.value = cell.getValue() || "";

    // Récupération de la position (ligne et champ) pour le stockage
    const rowIndex = cell.getRow().getPosition();
    const field = cell.getField();
    const storageKey = "tabulator_edit_focus";
    let validated = false;

    onRendered(() => {
      input.focus();
      // Sauvegarde de la position dans le localStorage
      localStorage.setItem(storageKey, JSON.stringify({ rowIndex, field }));
    });

    // La fonction commit valide la saisie, sélectionne la ligne,
    // et déplace le focus sur la cellule "select"
    function commit() {
      validated = true;
      localStorage.removeItem(storageKey);
      success(input.value);
      cell.getRow().select();
      setTimeout(() => {
        let selectCell = cell.getRow().getCell("select");
        if (selectCell) {
          selectCell.getElement().focus();
        }
      }, 50);
    }

    // Valider la saisie dès que l'utilisateur appuie sur "Enter" (ou "Tab")
    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === "Tab") {
        e.preventDefault();
        commit();
      } else if (e.key === "Escape") {
        cancel();
      }
    });

    // Prévenir un blur non voulu si la saisie n'est pas validée
    input.addEventListener("blur", (e) => {
      if (!validated) {
        e.preventDefault();
        e.stopImmediatePropagation();
        setTimeout(() => input.focus(), 10);
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


function customNumberEditor(cell, onRendered, success, cancel, editorParams) {
    const input = document.createElement("input");
    input.type = "number";
    input.style.width = "100%";
    input.value = cell.getValue() || "";

    const rowIndex = cell.getRow().getPosition();
    const field = cell.getField();
    const storageKey = "tabulator_edit_focus";

    let validated = false;

    onRendered(() => {
        input.focus();
        // Enregistrement de la position actuelle de l'édition dans localStorage
        localStorage.setItem(storageKey, JSON.stringify({ rowIndex, field }));
    });

    input.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === "Tab") {
            validated = true;
            localStorage.removeItem(storageKey);
            success(input.value);
        }
    });

    input.addEventListener("blur", (e) => {
        if (!validated) {
            e.preventDefault();
            e.stopImmediatePropagation();
            // Re-focus si le blur survient sans validation (ni Enter ni Tab)
            setTimeout(() => input.focus(), 10);
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

    // Variables pour la gestion du focus et de la validation
    const rowIndex = cell.getRow().getPosition();
    const field = cell.getField();
    const storageKey = "tabulator_edit_focus";
    let validated = false;

    // Création du dropdown personnalisé (ajouté au body pour éviter les problèmes d'overflow)
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

    // Initialisation du tableau d'options (sera rempli par l'appel asynchrone)
    var options = [];

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
                    // Valider et marquer la saisie comme confirmée
                    validated = true;
                    localStorage.removeItem(storageKey);
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
                // Validation en cas d'ajout
                validated = true;
                localStorage.removeItem(storageKey);
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

    // Gestion de la saisie et des touches "Enter" ou "Tab"
    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter" || e.key === "Tab") {
            e.preventDefault();
            validated = true;
            localStorage.removeItem(storageKey);
            dropdown.style.display = "none";
            // On peut vérifier ici si la valeur correspond à une option, ou lancer success directement
            success(input.value);
        }
    });

    // Intercepter le blur pour empêcher la perte de focus si la validation n'a pas lieu
    input.addEventListener("blur", function(e) {
        if (!validated) {
            e.preventDefault();
            e.stopImmediatePropagation();
            setTimeout(function(){
                input.focus();
            }, 10);
        } else {
            dropdown.style.display = "none";
        }
    });

    // Actualisation du dropdown lors des événements
    input.addEventListener("input", function() {
        updateDropdown();
    });
    input.addEventListener("focus", function() {
        updateDropdown();
    });

    // Au rendu, placer le focus sur l'input et récupérer les options
    onRendered(function() {
        input.focus();
        // Sauvegarder la position de l'édition dans localStorage
        localStorage.setItem(storageKey, JSON.stringify({ rowIndex, field }));

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
    const sections = ["achats", "ventes",'operations-diverses'];

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
                $('#annee-operations-diverses').val(anneeDebut);

                // Peupler les périodes pour tous les onglets
                populateMonths('achats', periodesData);
                populateMonths('ventes', periodesData);
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

        // Vérifier les options ajoutées
        console.log("Options ajoutées dans #" + onglet + ":", periodeSelect.html());

        // Si aucune sélection précédente n'existe, laisser "Sélectionner un mois" sélectionné
        if (previousSelection) {
            periodeSelect.val(previousSelection);
        } else {
            periodeSelect.val('selectionner un mois');
        }
    }

    // Fonction pour mettre à jour la date dans toutes les tables Tabulator
    function updateTabulatorDate(year, month) {
        const formattedDate = `${year}-${month.padStart(2, '0')}-01`;

        // Met à jour la date dans toutes les tables Tabulator
        [tableAch, tableVentes].forEach(function (table) {
            table.updateData(table.getData().map(row => ({
                ...row,
                date: formattedDate,
            })));
        });
    }

    window.addEventListener("focus", () => {
        const key = "tabulator_current_edit";
        const saved = localStorage.getItem(key);
        if (saved) {
            const { rowIndex, field } = JSON.parse(saved);
            const row = tableAch.getRows()[rowIndex];
            if (row) {
                const cell = row.getCell(field);
                if (cell) {
                    setTimeout(() => cell.edit(), 100);
                }
            }
        }
    });

    // Fonction de gestion des changements dans le select
    function setupPeriodChangeHandler(onglet) {
        $(`#periode-${onglet}`).on('change', function () {
            const selectedValue = $(this).val();
            const selectedText = $(this).find("option:selected").text();

            console.log('Valeur sélectionnée pour ' + onglet + ':', selectedValue);
            console.log('Texte sélectionné pour ' + onglet + ':', selectedText);

            if (selectedValue === "selectionner un mois" || !selectedValue) {
                console.warn("⚠️ Aucune période valide sélectionnée !");
                return;
            }

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
        const onglets = ['achats', 'ventes','operations-diverses'];

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
    loadExerciceSocialAndPeriodes();
    setupFilterEventHandlers();
    ['achats', 'ventes','operations-diverses'].forEach(onglet => {
        setupPeriodChangeHandler(onglet);
    });
});


// Liste des mois en anglais et en français
const moisAnglais = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
const moisFrancais = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];



var tableAch, tableVentes,tableOP;

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
    const selectedJournal = $('#journal-achats').val() || $('#journal-ventes').val()|| $('#journal-operations-diverses').val()|| $('#journal-Banque').val()|| $('#journal-Caisse').val();

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
loadJournaux('operations-diverses', '#journal-operations-diverses');
//  loadJournaux('Banque', '#journal-Banque');
//  loadJournaux('Caisse', '#journal-Caisse');



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
     '#journal-operations-diverses',
     '#journal-Banque',
     '#journal-Caisse',

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
async function fetchComptesTva(societeId) {
    const [ventes, achats] = await Promise.all([
        fetch(`/get-compte-tva-vente?societe_id=${societeId}`).then(res => res.json()),
        fetch(`/get-compte-tva-ach?societe_id=${societeId}`).then(res => res.json())
    ]);

    return { ventes, achats };
}

// Initialisation des tables après récupération des données
// Fonction d'initialisation de la table et des données
(async function initTables() {
    try {
        // Récupération des données
        const { ventes: rubriquesVentes, achats: rubriquesAchats } = await fetchRubriquesTva();
        const { ventes: comptesVentes, achats: comptesAchats } = await fetchComptesTva(societeId);

        // Récupération des clients et définition des variables globales
        const clients = await fetch(`/get-clients?societe_id=${societeId}`).then(res => res.json());
        window.clients = clients; // Pour y accéder globalement
        window.comptesClients = clients.map(client => `${client.compte} - ${client.intitule}`);

        // Récupération des fournisseurs et autres données
        const fournisseurs = await fetch(`/get-fournisseurs-avec-details?societe_id=${societeId}`)
            .then(res => res.json());
        window.comptesFournisseurs = fournisseurs;
        window.formattedComptesFournisseurs = getFormattedComptesFournisseurs();

        console.log("comptesClients:", window.comptesClients);
        console.log("comptesFournisseurs:", window.comptesFournisseurs);
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

        var showProrat = (assujettiePartielleTVA == 1); // Vérifie si la valeur est 1
        var tableAch = new Tabulator("#table-achats", {
            height: "500px",
            layout: "fitDataFill",   // ← change pour adapter chaque colonne à son contenu
            columnDefaults: {        // ← appliqué à TOUTES les colonnes
                formatter: "textarea",  // wrap du texte
                variableHeight: true,   // hauteur de ligne automatique
                cellStyled: function(cell) {
                    cell.getElement().style.whiteSpace = "normal"; // autorise le wrapping
                }
            },

            // —————— vos options existantes ——————
            clipboard: true,
            clipboardPasteAction: "replace",
            placeholder: "Aucune donnée disponible",

            ajaxResponse: function(url, params, response) {
                console.log("Données reçues :", response);

                if (response.length === 0 || response[0].id !== "") {
                    response.unshift({ id: "", date: "", debit: "", credit: "" });
                }
                return response;
            },
            ajaxError: function(xhr, textStatus, errorThrown) {
                console.error("Erreur AJAX :", textStatus, errorThrown);
            },

            printAsHtml: true,
            printHeader: "<h1>Table Achats</h1>",
            printFooter: "<h2>Example Table Footer</h2>",

            selectable: true,
            footerElement:
                "<table style='width:15%; margin-top:6px; border-collapse:collapse;'>" +
                  "<tr>" +
                    "<td style='padding:8px; text-align:left; font-weight:bold; font-size:11px;'>Cumul Débit :</td>" +
                    "<td style='padding:8px; text-align:center; font-size:10px;'><span id='cumul-debit-achats'></span></td>" +
                    "<td style='padding:8px; text-align:left; font-weight:bold; font-size:11px;'>Cumul Crédit :</td>" +
                    "<td style='padding:8px; text-align:center; font-size:10px;'><span id='cumul-credit-achats'></span></td>" +
                  "</tr>" +
                  "<tr>" +
                    "<td style='padding:8px; text-align:left; font-weight:bold; font-size:11px;'>Solde Débiteur :</td>" +
                    "<td style='padding:8px; text-align:center; font-size:10px;'><span id='solde-debit-achats'></span></td>" +
                    "<td style='padding:8px; text-align:left; font-weight:bold; font-size:11px;'>Solde Créditeur :</td>" +
                    "<td style='padding:8px; text-align:center; font-size:10px;'><span id='solde-credit-achats'></span></td>" +
                  "</tr>" +
                "</table>",
          // Footer sous forme de tableau avec des styles inline
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
                            style: "width: 95px; height: 25px;" // 80 pixels de large
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
                            style: "width: 95px; height: 25px;"
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
        elementAttributes: { style: "width: 95px; height: 25px;" }
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
            style: "width: 95px; height: 25px;" // 80 pixels de large
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
                            style: "width: 95px; height: 25px;" // 80 pixels de large
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
                            style: "width: 95px; height: 25px;" // 80 pixels de large
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
                    width: 95,
                    minWidth: 95,
                    widthGrow: 0,
                    editor: customListEditorRub,
                    headerFilterParams: {
                        elementAttributes: {
                            style: "width: 95px; height: 25px;"
                        }
                    },
                    editorParams: {
                        autocomplete: true,
                        listOnEmpty: true,
                        values: rubriquesAchats
                    },
                    formatter: function(cell) {
                        const value = cell.getValue();
                        return `<div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${value}</div>`;
                    }
                },



                {
                    title: "Compte TVA",
                    field: "compte_tva",
                    headerFilter: "input",
                    editor: customListEditor,
                    headerFilterParams: {
                      elementAttributes: { style: "width: 95px; height: 25px;" }
                    },
                    editorParams: {
                      autocomplete: true,
                      listOnEmpty: true,
                      values: comptesVentes.reduce((obj, compte) => {
                        obj[compte.compte] = `${compte.compte} - ${compte.intitule}`;
                        return obj;
                      }, {})
                    }
                  },

                  {
                    title: "Prorat de deduction",
                    field: "prorat_de_deduction",
                    visible: showProrat, // Affiché si "Oui", masqué sinon
                    headerFilter: "input",
                    editor: customListEditor,
                    headerFilterParams: {
                        elementAttributes: { style: "width: 95px; height: 25px;" }
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
                      elementAttributes: { style: "width: 95px; height: 25px;" }
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
                    title: "Pièce justificative",
                    field: "piece_justificative",
                    headerFilter: "input",
                    headerFilterParams: {
                      elementAttributes: { style: "width: 150px; height: 25px;" }
                    },
                    width: 200,
                    formatter: function(cell) {
                      const rowData = cell.getRow().getData();
                      const affichage = rowData.piece_justificative || "";
                      const icon = `<i class='fas fa-paperclip upload-icon' title='Choisir un fichier' style='cursor: pointer; margin-right: 5px;'></i>`;
                      const inputHTML = `<input type='text' class='selected-file-input' placeholder='${affichage}' value='${affichage}'>`;
                      return icon + inputHTML;
                    },
                    cellRendered: function(cell) {
                      const cellEl = cell.getElement();
                      const inputEl = cellEl.querySelector('.selected-file-input');

                      if (inputEl) {
                        inputEl.addEventListener("keydown", function(e) {
                          if (e.key === "Enter" || e.key === "Tab") {
                            e.preventDefault();
                            e.stopPropagation();

                            const row = cell.getRow();

                            // Sélection de la ligne
                            if (typeof row.select === "function") {
                              row.select();
                            } else if (typeof row.toggleSelect === "function") {
                              row.toggleSelect();
                            }

                            // Déplacement du focus vers la cellule "select"
                            setTimeout(() => {
                              const selectCell = row.getCell("select");
                              if (selectCell) {
                                const el = selectCell.getElement();
                                el.setAttribute("tabindex", "0"); // nécessaire pour le focus clavier
                                el.focus();
                              }
                            }, 50);
                          }
                        });
                      }
                    },
                    cellClick: function(e, cell) {
                      if (!$(e.target).hasClass('upload-icon')) return;

                      $('#file_achat_Modal').show();
                      $('#confirmBtnAchat').data('cell', cell);
                      cell.getRow().select();
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
                    var updatedData = updatePieceJustificative(tableAch.getData());
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


                console.log("Valeur assujettiePartielleTVA:", assujettiePartielleTVA);
                console.log("Colonne Prorat affichée ?", showProrat);

// Événement de mise à jour des champs
// Récupérer le token CSRF du meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
// Empêcher l'édition d'une ligne sans ID et maintenir le focus sur l'éditeur actif
// Lorsqu'une cellule passe en mode édition, on enregistre son identifiant dans le localStorage
tableAch.on("cellEditing", function(cell) {
    const row = cell.getRow();
    const data = row.getData();

    // Si la ligne n'a pas d'ID, on annule l'édition
    if (!data.id) {
        console.log("Édition ignorée : nouvelle ligne sans ID.");
        return false;
    }

    // Enregistrer l'information sur la cellule en cours d'édition
    const editingIdentifier = {
        rowId: data.id,
        field: cell.getField()
    };
    localStorage.setItem("currentEditingCell", JSON.stringify(editingIdentifier));

    // Pour tenter de maintenir le focus en surveillant la visibilité du document
    function reFocusIfNeeded() {
        if (document.visibilityState === "visible") {
            const stored = localStorage.getItem("currentEditingCell");
            if (stored) {
                try {
                    const editing = JSON.parse(stored);
                    // Récupération de la ligne par son ID
                    const row = tableAch.getRow(editing.rowId);
                    if (row) {
                        // Récupération de la cellule en fonction du champ
                        const cellToFocus = row.getCell(editing.field);
                        if (cellToFocus) {
                            // Recherche de l'éditeur (input, select, textarea)
                            let editorElement = cellToFocus.getElement().querySelector("input, select, textarea");
                            if (editorElement) {
                                editorElement.focus();
                                console.log("Focus réaffecté à l'éditeur via localStorage.");
                            }
                        }
                    }
                } catch (e) {
                    console.error("Erreur lors du parse de currentEditingCell :", e);
                }
            }
        }
    }

    // Écoute de l'événement visibilitychange pour détecter le retour sur la page
    window.addEventListener("visibilitychange", reFocusIfNeeded);

    // Optionnel : vous pouvez ajouter un intervalle en complément si nécessaire
    const focusInterval = setInterval(() => {
        // Vérifie si le document est visible et si un éditeur est en cours d'édition puis tente de forcer le focus
        if (document.visibilityState === "visible") {
            reFocusIfNeeded();
        }
    }, 500);

    // À la fin de l'édition, on nettoie le localStorage et les écouteurs
    cell.getRow().getTable().once("cellEdited", function() {
        localStorage.removeItem("currentEditingCell");
        window.removeEventListener("visibilitychange", reFocusIfNeeded);
        clearInterval(focusInterval);
    });

    return true; // Autoriser l'édition
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

const headerMap = {
    "Date":                "date",
    "N° facture":          "numero_facture",
    "Compte":              "compte",
    "Libellé":             "libelle",
    "Débit":               "debit",
    "Crédit":              "credit",
    "Contre partie":       "contre_partie",
    "Rubrique TVA":        "rubrique_tva",
    "Compte TVA":          "compte_tva",
    "Prorat de déduction": "prorat_de_deduction",
    "Solde Cumulé":        "value",
    "Pièce justificative": "piece_justificative",
    "Code journal":        "type_Journal",
    "Catégorie":           "categorie",
    // Ajoutez ici toute autre colonne de votre Excel...
  };

  document
  .getElementById("import-achats")
  .addEventListener("click", () => {
    document.getElementById("excel-file-achats").click();
  });

// 4) Lecture & parsing du fichier Excel, puis import + save automatique
document
  .getElementById("excel-file-achats")
  .addEventListener("change", async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    try {
      // a) lecture du buffer
      const arrayBuffer = await file.arrayBuffer();
      const data        = new Uint8Array(arrayBuffer);
      // b) parse avec SheetJS
      const wb = XLSX.read(data, { type: "array" });
      const ws = wb.Sheets[wb.SheetNames[0]];
      const raw = XLSX.utils.sheet_to_json(ws, { header: 1, defval: "" });
      const [headers, ...rows] = raw;

      // c) mapping en objets { field: value }
      const dataObjects = rows.map((row) => {
        const obj = {};
        headers.forEach((h, idx) => {
          const field = headerMap[h] || h;
          obj[field] = row[idx];
        });
        return obj;
      });

      // d) injecter dans Tabulator
      await tableAch.setData(dataObjects);
      console.log("✅ Import Excel terminé");

      // e) puis lancer l'enregistrement
      await enregistrerLignesAch();
      console.log("✅ Enregistrement post‑import terminé");
    } catch (err) {
      console.error("Erreur import/enregistrement :", err);
      alert("📛 Une erreur est survenue pendant l'import ou l'enregistrement.");
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

    // ← chaque colonne s’adapte à la largeur de son contenu
    layout: "fitDataFill",
    // ← appliqué à TOUTES les colonnes pour wrap + hauteur auto
    columnDefaults: {
        formatter: "textarea",
        variableHeight: true,
        cellStyled: function(cell) {
            cell.getElement().style.whiteSpace = "normal";
        }
    },

    rowHeight: 30, // définit la hauteur de ligne à 30px

    clipboard: true,
    clipboardPasteAction: "replace",
    placeholder: "Aucune donnée disponible",

    ajaxResponse: function(url, params, response) {
        console.log("Données reçues (ventes) :", response);

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
        console.error("Erreur AJAX (ventes) :", textStatus, errorThrown);
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
                    style: "width: 95px; height: 25px;" // 80 pixels de large
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
                    alert("Veuillez saisir une date valide");
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
                style: "width: 95px; height: 25px;" // 80 pixels de large
            }
        },
        editor: "input"
     },
        { title: "N° Facture", field: "numero_facture",headerFilter: "input",headerFilterParams: {
            elementAttributes: {
                style: "width: 95px; height: 25px;" // 80 pixels de large
            }
        }, editor: "input" },

        {
            title: "Compte",
            field: "compte",
            headerFilter: "input",
            headerFilterParams: {
              elementAttributes: { style: "width: 95px; height: 25px;" }
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
            style: "width: 95px; height: 25px;" // 80 pixels de large
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
        style: "width: 95px; height: 25px;" // 80 pixels de large
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
        style: "width: 95px; height: 25px;" // 80 pixels de large
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
            style: "width: 95px; height: 25px;" // 80 pixels de large
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
                    style: "width: 95px; height: 25px;" // 80 pixels de large
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
                    style: "width: 95px; height: 25px;"
                }
            },
          width: 95,       // largeur fixe
         minWidth: 95,    // ne descend pas en dessous
         widthGrow: 0,    // n’attrape pas d’espace supplémentaire
         formatter: function(cell) {
            const value = cell.getValue() || "";
            return `<div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${value}">${value}</div>`;
        },

            editor: function(cell, onRendered, success, cancel) {
                const select = document.createElement("select");
                const uniqueId = "select_tva_" + cell.getRow().getData().id;
                select.id = uniqueId;
                select.style.width = "100%";
                select.style.boxSizing = "border-box";

                onRendered(() => {
                    cell.getElement().appendChild(select);
                });
                // Récupérer la rubrique suggérée par la société
                $.ajax({
                    url: '/getRubriqueSociete',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Réponse API:", response);

                        let suggestedValue = null;
                        let formattedSuggestedValue = "";
                        if (response && response.rubrique && response.nom_racines && response.taux) {
                            // Formater la suggestion sous forme "102: Autres (20.00%)"
                            suggestedValue = response.rubrique;
                            formattedSuggestedValue = `${response.rubrique}: ${response.nom_racines} (${response.taux}%)`;
                        }

                        console.log("Valeur suggérée formatée:", formattedSuggestedValue);

                        // Appeler ta fonction de remplissage des rubriques TVA
                        remplirRubriquesTvaVente(uniqueId, null); // Pas de sélection automatique ici

                        // Après un petit délai pour attendre le remplissage
                        setTimeout(() => {
                            const $select = $("#" + uniqueId);

                            // Ajouter une option suggérée au tout début
                            if (formattedSuggestedValue) {
                                const suggestionOption = new Option(formattedSuggestedValue, suggestedValue);
                                suggestionOption.className = "suggestion";  // Class pour styliser
                                suggestionOption.disabled = false;  // Sélectionnable
                                suggestionOption.hidden = false;  // Visible
                                $select.prepend(suggestionOption);  // Ajouter en haut

                                // Sélectionner la suggestion automatiquement
                                $select.val(suggestedValue).trigger('change');
                            }

                            // Initialiser Select2
                            $select.select2({
                                dropdownParent: document.body,
                                width: '100%',
                                placeholder: "Choisir une rubrique",
                                allowClear: true
                            });

                            // Lors de la sélection d'une option
                            $select.on("select2:select", function () {
                                const selectedValue = $select.val();
                                const selectedText = $select.find("option:selected").text(); // Le texte complet de l'option sélectionnée

                                // Mettre à jour la cellule avec le texte complet formaté
                                cell.setValue(selectedText);

                                // Validation du champ et transmission de la valeur sélectionnée
                                success(selectedText);

                                // Appel de la fonction de calcul du solde cumulé
                                calculerSoldeCumuleVentes();

                                // Sélectionner la ligne entière une fois la sélection validée
                                cell.getRow().select();

                                // Passer à la cellule suivante
                                setTimeout(() => {
                                    const nextCell = cell.getRow().getNextCell(cell);
                                    if (nextCell) {
                                        nextCell.edit();
                                    }
                                }, 10);
                            });

                            // Si la fenêtre Select2 est fermée sans choix explicite
                            $select.on("select2:close", function () {
                                // Appel de la fonction de calcul du solde cumulé lors de la fermeture
                                calculerSoldeCumuleVentes();

                                // Valider et renvoyer la valeur sélectionnée actuelle, qui peut être null si aucun choix n'a été fait
                                success($select.val());
                            });
                        }, 250);
                    },
                    error: function (err) {
                        console.error("Erreur récupération rubrique société :", err);
                    }
                });

                return select;
            }
        },






        {
            title: "Solde Cumulé",
            field: "value", // Ce champ contient le solde cumulé calculé (issu de ton mapping: value: ligne.solde_cumule)
            // editor: "input", // Permet l'édition manuelle si besoin (tu peux le supprimer si le solde doit être uniquement calculé)
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 95px; height: 25px;" // 80 pixels de large
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
            title: "Pièce justificative",
            field: "piece_justificative",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: { style: "width: 150px; height: 25px;" }
            },
            width: 200,
            formatter: function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();
                const affichage = rowData.piece_justificative || "";

                // Icône cliquable pour ouvrir la modale
                const icon = `<i class='fas fa-paperclip upload-icon' title='Choisir un fichier' style='cursor: pointer; margin-right: 5px;'></i>`;
                const input = `<input type='text' class='selected-file-input' placeholder='${affichage}' readonly value='${affichage}'>`;

                return icon + input;
            },
            cellClick: function(e, cell) {
                const table = cell.getTable();
                const row = cell.getRow();
                const rowData = row.getData();

                // Vérifier si l'utilisateur a cliqué sur l'icône (et non ailleurs dans la cellule)
                if (!$(e.target).hasClass('upload-icon')) {
                    return; // Empêche l'ouverture si on clique en dehors de l'icône
                }

                // Si aucun numéro de pièce n'est défini, essayer de le générer si possible
                if (!rowData.piece_justificative) {
                    const numeroFacture = rowData.numero_facture ? rowData.numero_facture.trim() : null;
                    // Si le numéro de facture n'est pas défini, on peut choisir de ne pas générer de pièce mais d'ouvrir quand même le modal
                    if (numeroFacture) {
                        let dt = luxon.DateTime.fromFormat(rowData.date, "yyyy-MM-dd HH:mm:ss");
                        if (!dt.isValid) {
                            dt = luxon.DateTime.fromISO(rowData.date);
                        }
                        if (dt.isValid) {
                            const moisFormatted = dt.toFormat("MM");
                            const codeJournal = rowData.type_journal || getSelectedCodeJournal();
                            const prefix = `P${moisFormatted}${codeJournal}`;

                            const allData = table.getData();
                            let existingNumbers = allData
                                .filter(r =>
                                    r.numero_facture === numeroFacture &&
                                    r.piece_justificative &&
                                    r.piece_justificative.startsWith(prefix) &&
                                    luxon.DateTime.fromISO(r.date).toFormat("MM") === moisFormatted
                                )
                                .map(r => {
                                    const numStr = r.piece_justificative.substring(prefix.length);
                                    return parseInt(numStr, 10);
                                })
                                .filter(num => !isNaN(num));

                            existingNumbers.sort((a, b) => a - b);
                            let newIncrement = existingNumbers.length > 0 ? existingNumbers[existingNumbers.length - 1] + 1 : 1;
                            const numeroFormate = String(newIncrement).padStart(4, "0");
                            const newPiece = `${prefix}${numeroFormate}`;

                            row.update({ piece_justificative: newPiece });
                            console.log(`Nouvelle pièce justificative attribuée : ${newPiece}`);

                            $('#confirmBtnVente').data('piece', newPiece);
                        } else {
                            console.warn("Date invalide pour la facture " + numeroFacture);
                        }
                    } else {
                        console.warn("Numéro de facture non défini, génération de la pièce impossible.");
                    }
                }

                // Ouvrir le modal file_vente_Modal, même si la ligne est vide
                $('#file_vente_Modal').show();
                $('#confirmBtnVente').data('cell', cell);

                // Sélectionner la ligne associée
                row.select();
            }
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
async function recupererTauxTVAFromAPI(rubriqueTva, compteFournisseur) {
    console.log(`📌 Début récupération taux TVA : rubrique_tva = "${rubriqueTva}", compte_fournisseur = "${compteFournisseur}"`);

    let tauxTVA = 0;

    // 🔹 **1️⃣ Extraire le taux TVA s'il est mentionné dans la `rubrique_tva` (ex: "140: prestations de services : 20%")**
    if (rubriqueTva) {
        const match = rubriqueTva.match(/(\d+(\.\d+)?)\s*%$/); // Capture le dernier nombre avant `%`
        if (match && match[1]) {
            tauxTVA = parseFloat(match[1]);
            console.log(`✅ Taux TVA extrait de la rubrique : ${tauxTVA}%`);
            return tauxTVA; // Si trouvé, retourner directement
        }
    }

    try {
        console.log(`📡 Requête API en cours : /get-fournisseurs-avec-details`);
        const response = await fetch(`/get-fournisseurs-avec-details`);
        if (!response.ok) throw new Error(`Erreur serveur : ${response.statusText}`);

        const data = await response.json();
        console.log(`📊 Données reçues de l'API :`, data);

        // 🔹 **2️⃣ Vérifier si `rubrique_tva` est renseignée et chercher le taux TVA**
        if (rubriqueTva) {
            let rubrique = data.find(f => f.rubrique_tva === rubriqueTva);
            if (rubrique) {
                console.log(`✅ Taux TVA trouvé via rubrique TVA : ${rubrique.taux_tva}%`);
                return parseFloat(rubrique.taux_tva) || 0;
            }
            console.warn(`⚠️ Aucun taux TVA trouvé pour la rubrique TVA : ${rubriqueTva}`);
        }

        // 🔹 **3️⃣ Si `rubrique_tva` ne donne rien, essayer via le fournisseur**
        if (compteFournisseur) {
            let fournisseur = data.find(f => f.compte === compteFournisseur);
            if (fournisseur) {
                console.log(`✅ Fournisseur trouvé :`, fournisseur);
                if (fournisseur.rubrique_tva) {
                    let tauxTVA = parseFloat(fournisseur.taux_tva) || 0;
                    console.log(`✅ Taux TVA trouvé via fournisseur : ${tauxTVA}%`);
                    return tauxTVA;
                } else {
                    console.warn(`⚠️ Le fournisseur ${compteFournisseur} n'a pas de rubrique TVA définie.`);
                }
            } else {
                console.warn(`⚠️ Aucun fournisseur trouvé avec le compte : ${compteFournisseur}`);
            }
        }

        console.warn(`❌ Aucun taux TVA trouvé ni via rubrique, ni via fournisseur. TVA = 0%`);
        return 0;

    } catch (error) {
        console.error("❌ Erreur récupération taux TVA :", error);
        return 0;
    }
}

// =====================================================================
// 4. Fonction pour calculer dynamiquement le débit
async function calculerDebit(rowData, typeLigne, credit, useAPIMethod = false) {
    console.log(`📌 Début du calcul du débit :`, rowData);

    let tauxTVA = 0;

    // Méthode 1 : Récupérer le taux via l'API si demandée
    if (useAPIMethod) {
        tauxTVA = await recupererTauxTVAFromAPI(rowData.rubrique_tva, rowData.compte_fournisseur);
        console.log(`📝 Taux TVA récupéré via API = ${tauxTVA}%`);
    } else {
        // Méthode 2 : Extraire le taux depuis le champ rubrique_tva
        if (rowData.rubrique_tva) {
            // Exemple attendu : "140 - prestations de services (20.00%)"
            const match = rowData.rubrique_tva.match(/\(([\d\.]+)%\)/);
            if (match && match[1]) {
                tauxTVA = parseFloat(match[1]);
            } else {
                console.warn("Le format de rubrique_tva n'est pas conforme pour extraire le taux de TVA.");
            }
        } else {
            console.warn("Aucune rubrique_tva sélectionnée.");
        }
        console.log(`📝 Taux TVA extrait = ${tauxTVA}%`);
    }

    if (isNaN(credit) || isNaN(tauxTVA)) {
        console.error("❌ Crédit ou Taux TVA invalides !");
        rowData.debit = 0;
        return;
    }

    // Gestion du prorata de déduction si applicable
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
            console.error('❌ Erreur lors de la récupération du prorata de déduction :', error);
        }
    }

    let debit = 0;
    // Calcul du débit selon le type de ligne
    if (typeLigne === "ligne1") {
        debit = isProrataOui
            ? (credit / (1 + tauxTVA / 100)) + (((credit / (1 + tauxTVA / 100)) * tauxTVA / 100) * (1 - prorata / 100))
            : credit / (1 + tauxTVA / 100);
    } else if (typeLigne === "ligne2") {
        debit = isProrataOui
            ? ((credit / (1 + tauxTVA / 100)) * tauxTVA / 100) * (prorata / 100)
            : (credit / (1 + tauxTVA / 100)) * tauxTVA / 100;
    }

    rowData.debit = parseFloat(debit.toFixed(2));
    console.log(`✅ Débit final pour ${typeLigne}: ${rowData.debit}`);
}


// =====================================================================
// 5. Mise à jour du champ piece_justificative selon certaines règles
function getSelectedCodeJournal() {
    const selectors = [
        "#journal-achats",
        "#journal-ventes",
        "#journal-operations-diverses",

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
        // Conversion de la date
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
        // Mise à jour de toutes les lignes de cette facture
        rows.forEach(row => {
            row.piece_justificative = newPiece;
        });
    });
    return data;
}


// =====================================================================
// 5. Mise à jour du champ piece_justificative selon certaines règles
function getSelectedCodeJournal() {
    const selectors = [
        "#journal-achats",
        "#journal-ventes",
        "#journal-operations-diverses",

    ];
    for (let sel of selectors) {
        const el = document.querySelector(sel);
        if (el && el.value && el.value.trim() !== "") {
            return el.value.trim();
        }
    }
    return "CJ";
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

tabulatorManager.applyToTabulator(tableAch);
tabulatorManager.applyToTabulator(tableVentes);

        // Gestionnaire pour importer les données
        document.getElementById("import-ventes").addEventListener("click", function () {
            alert("Fonction d'import non implémentée !");
            // Ajoutez ici votre logique pour l'importation (par ex. ouvrir un modal ou lire un fichier)
        });

        // Gestionnaire pour exporter vers Excel
        document.getElementById("export-ventesExcel").addEventListener("click", function () {
            tableVentes.download("xlsx", "ventes.xlsx", { sheetName: "Ventes" });
        });

        // Gestionnaire pour exporter vers PDF
        document.getElementById("export-ventesPDF").addEventListener("click", function () {
            tableVentes.download("pdf", "ventes.pdf", {
                orientation: "portrait", // Orientation de la page
                title: "Rapport des Ventes", // Titre du rapport
            });
        });

        // Gestionnaire pour supprimer une ligne sélectionnée dans tableAch
        document.getElementById("delete-row-btnAch").addEventListener("click", function () {
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let selectedRows = tableAch.getSelectedRows(); // Récupérer les lignes sélectionnées dans Tabulator (tableAch)

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
                    body: JSON.stringify({ rowIds: rowIds })
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

        // Gestionnaire pour supprimer une ligne sélectionnée dans tableVentes
        document.getElementById("delete-row-btnVte").addEventListener("click", function () {
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let selectedRows = tableVentes.getSelectedRows(); // Récupérer les lignes sélectionnées dans Tabulator (tableVentes)

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
                    body: JSON.stringify({ rowIds: rowIds })
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

    } catch (error) {
        console.error("Erreur lors de l'initialisation des tables :", error);
    }


// Gestionnaire pour l'impression de tableVentes
document.getElementById("print-tableV").addEventListener("click", function () {
    if (tableVentes) {
        tableVentes.print(false, true); // Utilise la méthode d'impression de Tabulator
    } else {
        console.error("La table Tabulator n'est pas initialisée.");
    }
});

    // Fonction de formatage de la monnaie
function formatCurrency(value) {
    return parseFloat(value).toFixed(2);
  }

  // Variables pour le zebra striping
  let lastPiece = null;
  let toggle = false;
    function updateFooterCalculs() {
        // Récupère les totaux calculés par Tabulator pour les colonnes "debit" et "credit"
        let calcResults = tableOP.getCalcResults();
        let totalDebit = calcResults.debit ? parseFloat(calcResults.debit) : 0;
        let totalCredit = calcResults.credit ? parseFloat(calcResults.credit) : 0;

        // Calcul du solde débiteur et créditeur global
        let soldeDebiteur = totalDebit > totalCredit ? totalDebit - totalCredit : 0;
        let soldeCrediteur = totalCredit > totalDebit ? totalCredit - totalDebit : 0;

        // Mise à jour des éléments du footer
        document.getElementById("cumul-debit-operations-diverses").innerText = formatCurrency(totalDebit);
        document.getElementById("cumul-credit-operations-diverses").innerText = formatCurrency(totalCredit);
        document.getElementById("solde-debit-operations-diverses").innerText = formatCurrency(soldeDebiteur);
        document.getElementById("solde-credit-operations-diverses").innerText = formatCurrency(soldeCrediteur);
      }

      /**
       * Fonction rowFormatter()
       * Applique le zebra striping selon "piece_justificative" et recalcule
       * les totaux cumulés sur toutes les lignes pour mettre à jour le footer.
       */
      function rowFormatter(row) {
        let data = row.getData();

        // Appliquer le zebra striping en fonction du champ piece_justificative
        if (data.piece_justificative !== lastPiece) {
          toggle = !toggle;
          lastPiece = data.piece_justificative;
        }
        row.getElement().style.backgroundColor = toggle ? "#f2f2f2" : "#ffffff";

        // Calculer les totaux cumulés sur toutes les lignes
        let debitTotal = 0;
        let creditTotal = 0;
        row.getTable().getRows().forEach(function(r) {
          let d = parseFloat(r.getData().debit || 0);
          let c = parseFloat(r.getData().credit || 0);
          debitTotal += d;
          creditTotal += c;
        });
        let soldeDebiteur = debitTotal > creditTotal ? debitTotal - creditTotal : 0;
        let soldeCrediteur = creditTotal > debitTotal ? creditTotal - debitTotal : 0;

        // Mise à jour du footer pour refléter les totaux recalculés
        document.getElementById('cumul-debit-operations-diverses').innerText = formatCurrency(debitTotal);
        document.getElementById('cumul-credit-operations-diverses').innerText = formatCurrency(creditTotal);
        document.getElementById('solde-debit-operations-diverses').innerText = formatCurrency(soldeDebiteur);
        document.getElementById('solde-credit-operations-diverses').innerText = formatCurrency(soldeCrediteur);
      }

// Initialisation de la table des opérations diverses
var tableOP = new Tabulator("#table-operations-diverses", {
    clipboard: true,
    clipboardPasteAction: "replace",
    placeholder: "Aucune donnée disponible",
    ajaxResponse: function(url, params, response) {
        console.log("Données reçues (operations-diverses) :", response);
        if (response.length === 0 || response[0].id !== "") {
            response.unshift({ id: "", date: "", debit: "", credit: "" });
        }
        return response;
    },
    ajaxError: function(xhr, textStatus, errorThrown) {
        console.error("Erreur AJAX (operations-diverses) :", textStatus, errorThrown);
    },
    printAsHtml: true,
    printHeader: "<h1>Table Opérations Diverses</h1>",
    printFooter: "<h2>Table Footer</h2>",
    selectable: true,
    rowFormatter: rowFormatter, // Applique la fonction rowFormatter à chaque ligne

    footerElement:
        "<table style='width: 30%; margin-top: 6px; border-collapse: collapse;'>" +
            "<tr>" +
                "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 12px;'>Cumul Débit :</td>" +
                "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='cumul-debit-operations-diverses'></span></td>" +
                "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 12px;'>Cumul Crédit :</td>" +
                "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='cumul-credit-operations-diverses'></span></td>" +
            "</tr>" +
            "<tr>" +
                "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 12px;'>Solde Débiteur :</td>" +
                "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='solde-debit-operations-diverses'></span></td>" +
                "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 12px;'>Solde Créditeur :</td>" +
                "<td style='padding: 8px; text-align: center; font-size: 12px;'><span id='solde-credit-operations-diverses'></span></td>" +
            "</tr>" +
        "</table>",
    layout: "fitColumns",
    height: "500px",
    rowHeight: 30,
    columns: [
        { title: "ID", field: "id", visible: false },
        {
            title: "Date",
            field: "date",
            hozAlign: "center",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: { style: "width: 95px; height: 25px;" }
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

                // Pré-remplissage si une date existe déjà
                const currentValue = cell.getValue();
                if (currentValue) {
                    let dt = luxon.DateTime.fromFormat(currentValue, "yyyy-MM-dd HH:mm:ss");
                    if (!dt.isValid) { dt = luxon.DateTime.fromISO(currentValue); }
                    if (dt.isValid) { input.value = dt.toFormat("dd/MM/yyyy"); }
                }

                // Fonction de validation et commit
                function validateAndCommit() {
                    // On adapte ces éléments si vos IDs diffèrent pour operations-diverses
                    const moisSelect = document.getElementById("periode-operations-diverses");
                    const anneeInput = document.getElementById("annee-operations-diverses");
                    const day = parseInt(input.value.slice(0, 2), 10);
                    const month = moisSelect ? parseInt(moisSelect.value, 10) : 1;
                    const year = anneeInput ? parseInt(anneeInput.value, 10) : new Date().getFullYear();

                    if (!isNaN(day) && !isNaN(month) && !isNaN(year)) {
                        const dt = luxon.DateTime.local(year, month, day);
                        if (dt.isValid) {
                            success(dt.toFormat("yyyy-MM-dd HH:mm:ss"));
                            return true;
                        }
                    }
                    alert("Veuillez saisir une date valide");
                    cancel();
                    return false;
                }

                input.addEventListener("blur", validateAndCommit);
                input.addEventListener("keydown", function(e) {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        if (validateAndCommit()) {
                            setTimeout(() => { focusNextEditableCell(cell); }, 50);
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
                    if (!dt.isValid) { dt = luxon.DateTime.fromISO(dateValue); }
                    return dt.isValid ? dt.toFormat("dd/MM/yyyy") : dateValue;
                }
                return "";
            }
        },

        {
            title: "N° Facture",
            field: "numero_facture",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: { style: "width: 95px; height: 25px;" }
            },
            editor: "input"
        },
        {
            title: "Compte",
            field: "compte",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: { style: "width: 95px; height: 25px;" }
            },
            // On encapsule l'éditeur personnalisé pour intercepter le callback de validation
            editor: function(cell, onRendered, success, cancel, editorParams) {
                function newSuccess(value) {
                    // Si la valeur est au format "compte - intitule", on extrait le numéro
                    let compteNumber = value;
                    if (typeof value === "string" && value.indexOf(" - ") !== -1) {
                        compteNumber = value.split(" - ")[0].trim();
                    }
                    // Valide la valeur dans Tabulator
                    success(value);
                    // Appelle updateLibelleAndFocus en lui passant uniquement le numéro de compte
                    updateLibelleAndFocus(cell.getRow(), compteNumber);
                }
                return customListEditorPlanComptable(cell, onRendered, newSuccess, cancel, editorParams);
            },
            // Passage d'une fonction de lookup pour récupérer la liste des comptes
            editorParams: {
                valuesLookup: function(cell) {
                    return fetch('/fournisseurs-comptes')
                        .then(response => response.json())
                        .then(data => {
                            // Transformer chaque objet en une chaîne "compte - intitule"
                            return data.map(compteObj => `${compteObj.compte} - ${compteObj.intitule || ""}`);
                        });
                }
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
                const row = cell.getRow();
                const compte = cell.getValue();
                console.log("Valeur Compte mise à jour :", compte);
            }
        },

        {
            title: "Libellé",
            field: "libelle",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: { style: "width: 95px; height: 25px;" }
            },
            editor: "input",
            editable: false
        },
        {
            title: "Débit",
            field: "debit",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: { style: "width: 95px; height: 25px;" }
            },
            editor: "number",
            bottomCalc: "sum",
            formatter: function(cell) {
                const value = cell.getValue();
                return value ? parseFloat(value).toFixed(2) : "0.00";
            }
        },
        {
            title: "Crédit",
            field: "credit",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: { style: "width: 95px; height: 25px;" }
            },
            editor: "number",
            bottomCalc: "sum",
            formatter: function(cell) {
                const value = cell.getValue();
                return value ? parseFloat(value).toFixed(2) : "0.00";
            }
        },
        {
            title: "Contre-partie",
            field: "contre_partie",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: { style: "width: 95px; height: 25px;" }
            },
            editor: customListEditorPlanComptable,
            editorParams: {
                valuesLookup: function(cell) {
                    return fetch('/fournisseurs-comptes')
                        .then(response => response.json())
                        .then(data => {
                            // Transformation de chaque objet en chaîne "compte - intitule"
                            return data.map(compteObj => `${compteObj.compte} - ${compteObj.intitule || ""}`);
                        });
                }
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
                const row = cell.getRow();
                const contrePartieValue = cell.getValue();
                if (!contrePartieValue) return;
                console.log("Valeur Contre-partie mise à jour :", contrePartieValue);

                // Mettre le focus sur le champ "piece_justificative" dans la même ligne
                const pieceCell = row.getCell("piece_justificative");
                if (pieceCell) {
                    pieceCell.edit();
                }
                row.select();
            }

        },


        {
            title: "Solde Cumulé",
            field: "value",
            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: { style: "width: 90px; height: 25px;" }
            },
            formatter: function(cell) {
                let val = cell.getValue();
                if (val !== "" && !isNaN(val)) {
                    let numericVal = parseFloat(val);
                    if (Object.is(numericVal, -0)) { numericVal = 0; }
                    return numericVal.toFixed(2);
                }
                return val;

            }
        },
        {
            title: "Pièce justificative",
            field: "piece_justificative",
            headerFilter: "input",
            headerFilterParams: {
              elementAttributes: { style: "width: 150px; height: 25px;" }
            },
            width: 200,
            formatter: function(cell, formatterParams, onRendered) {
              const rowData = cell.getRow().getData();
              const affichage = rowData.piece_justificative || "";

              // Icône cliquable pour ouvrir la modale
              const icon = `<i class='fas fa-paperclip upload-icon' title='Choisir un fichier' style='cursor: pointer; margin-right: 5px;'></i>`;
              // Champ input modifiable (attribut readonly retiré) avec classe pour identification
              const input = `<input type='text' class='selected-file-input' placeholder='${affichage}' value='${affichage}'>`;

              // Attacher un écouteur d'événement pour mettre à jour la donnée de la ligne
              onRendered(function(){
                $(cell.getElement()).find('.selected-file-input').on('change', function(){
                  const newValue = $(this).val();
                  cell.getRow().update({ piece_justificative: newValue });
                });
              });

              return icon + input;
            },
            cellClick: function(e, cell) {
              const table = cell.getTable();
              const row = cell.getRow();
              const rowData = row.getData();

              // Si l'utilisateur clique sur l'icône de téléchargement, exécuter la logique d'ouverture de la modale
              if ($(e.target).hasClass('upload-icon')) {
                // Générer le numéro de pièce justificative s'il n'existe pas déjà
                if (!rowData.piece_justificative) {
                  const numeroFacture = rowData.numero_facture ? rowData.numero_facture.trim() : null;
                  if (!numeroFacture) return;

                  let dt = luxon.DateTime.fromFormat(rowData.date, "yyyy-MM-dd HH:mm:ss");
                  if (!dt.isValid) {
                    dt = luxon.DateTime.fromISO(rowData.date);
                  }
                  if (!dt.isValid) {
                    console.warn("Date invalide pour la facture " + numeroFacture);
                    return;
                  }
                  const moisFormatted = dt.toFormat("MM");
                  const codeJournal = rowData.type_journal || getSelectedCodeJournal();
                  const prefix = `P${moisFormatted}${codeJournal}`;

                  const allData = table.getData();
                  let existingNumbers = allData
                    .filter(r =>
                      r.numero_facture === numeroFacture &&
                      r.piece_justificative &&
                      r.piece_justificative.startsWith(prefix) &&
                      luxon.DateTime.fromISO(r.date).toFormat("MM") === moisFormatted
                    )
                    .map(r => {
                      const numStr = r.piece_justificative.substring(prefix.length);
                      return parseInt(numStr, 10);
                    })
                    .filter(num => !isNaN(num));

                  existingNumbers.sort((a, b) => a - b);
                  let newIncrement = existingNumbers.length > 0 ? existingNumbers[existingNumbers.length - 1] + 1 : 1;
                  const numeroFormate = String(newIncrement).padStart(4, "0");
                  const newPiece = `${prefix}${numeroFormate}`;

                  row.update({ piece_justificative: newPiece });
                  console.log(`Nouvelle pièce justificative attribuée : ${newPiece}`);

                  $('#confirmBtn').data('piece', newPiece);
                }

                // Ouvrir la modale UNIQUEMENT si l'utilisateur a cliqué sur l'icône
                $('#file_op_Modal').show();
                $('#confirmBtn').data('cell', cell);
              }

              // Dans tous les cas, forcer la sélection de la ligne,
              // même lorsque l'utilisateur clique sur l'input (même vide)
              row.select();
            }
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
            cellClick: function(e, cell) {
                cell.getRow().toggleSelect();
            }
        },
        { title: "Code_journal", field: "type_Journal", visible: false },
        { title: "categorie", field: "categorie", visible: false }
    ]



});
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

tableOP.on("cellEditing", function (cell) {
    const row = cell.getRow();
    const data = row.getData();

    // Empêcher l'édition d'une ligne sans ID
    if (!data.id) {
        console.log("Édition ignorée : nouvelle ligne sans ID.");
        return false; // Annuler l'édition sans alerte
    }

    return true; // Autoriser l'édition si la ligne a un ID
});

tableOP.on("cellEdited", function (cell) {
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


// Événements pour mettre à jour le footer globalement
tableOP.on("dataLoaded", updateFooterCalculs);
tableOP.on("dataChanged", updateFooterCalculs);

// Gestionnaire pour importer les données
document.getElementById("import-operations-diverses").addEventListener("click", function () {
    alert("Fonction d'import non implémentée !");
    // Ajoutez ici la logique pour l'importation (par exemple, ouvrir un modal ou lire un fichier)
});

// Gestionnaire pour exporter vers Excel
document.getElementById("export-operations-diversesExcel").addEventListener("click", function () {
    tableOP.download("xlsx", "operations_diverses.xlsx", { sheetName: "OperationsDiverses" });
});

// Gestionnaire pour exporter vers PDF
document.getElementById("export-operations-diversesPDF").addEventListener("click", function () {
    tableOP.download("pdf", "operations_diverses.pdf", {
        orientation: "portrait",
        title: "Rapport des Opérations Diverses"
    });
});

// Gestionnaire pour supprimer une ligne sélectionnée
document.getElementById("delete-row-btnOD").addEventListener("click", function () {
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let selectedRows = tableOP.getSelectedRows();

    if (selectedRows.length > 0) {
        let rowIds = selectedRows.map(row => row.getData().id);
        selectedRows.forEach(function (row) {
            row.delete();
        });
        fetch('/delete-rows', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ rowIds: rowIds })
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

// Gestionnaire pour l'impression de la table
document.getElementById("print-tableOp").addEventListener("click", function () {
    if (tableOP) {
        tableOP.print(false, true);
    } else {
        console.error("La table Tabulator n'est pas initialisée.");
    }
});


// Configuration du tableau Opérations Diverses
function updateTabulatorDataOp() {
    const mois = document.getElementById("periode-operations-diverses").value;
    const annee = document.getElementById("annee-operations-diverses").value;
    const codeJournal = document.getElementById("journal-operations-diverses").value;

    let dataToSend = {};

    // Définir les filtres en fonction des valeurs renseignées
    if (codeJournal && (!mois || !annee || mois === 'selectionner un mois')) {
        dataToSend = { code_journal: codeJournal };
    } else if (mois && annee && !codeJournal) {
        dataToSend = { mois: mois, annee: annee };
    } else if (mois && annee && codeJournal) {
        dataToSend = { mois: mois, annee: annee, code_journal: codeJournal };
    }

    // Ajouter le filtre indiquant que l'on souhaite récupérer uniquement les opérations dont la catégorie est "Opérations diverses"
    dataToSend.categorie = "Opérations Diverses";

    console.log("Filtrage operations-diverses appliqué :", dataToSend);

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
        console.log("Données reçues après filtrage operations-diverses :", data);
        // Remplacer les données du tableau operations-diverses
        tableOP.replaceData(data).then(() => {
            // Après remplacement, recalculer immédiatement le solde cumulé
            calculerSoldeCumuleOperationsDiverses();
        });
    })
    .catch(error => {
        console.error("Erreur lors de la mise à jour operations-diverses :", error);
    });
}

// Ajout des écouteurs pour les filtres operations-diverses
document.getElementById("journal-operations-diverses").addEventListener("change", updateTabulatorDataOp);
document.getElementById("periode-operations-diverses").addEventListener("change", updateTabulatorDataOp);
document.getElementById("annee-operations-diverses").addEventListener("input", updateTabulatorDataOp);

// Chargement initial des données operations-diverses
updateTabulatorDataOp();

/////////////////////////gestion OD /////////////////////////////////

function calculerSoldeCumuleOperationsDiverses() {
    const rows = tableOP.getRows();
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

    tableOP.redraw();
}

// Mise à jour automatique lors du chargement des données du tableau
tableOP.on("dataLoaded", function() {
    calculerSoldeCumuleOperationsDiverses();
});

// Appel dès le chargement complet de la page
document.addEventListener("DOMContentLoaded", function() {
    calculerSoldeCumuleOperationsDiverses();
});

// 1. Fonction principale pour ajouter une ligne pour Opérations Diverses
async function ajouterLigneOperationsDiverses(table, preRemplir = false, ligneActive = null) {
    let nouvellesLignes = [];
    let idCounter = table.getData().length + 1;
    let codeJournal = document.querySelector("#journal-operations-diverses").value;
    let moisActuel = new Date().getMonth() + 1;
    let filterOP = document.querySelector('input[name="filter-operations-diverses"]:checked')?.value;

    if (!filterOP) {
        alert("Veuillez sélectionner un filtre.");
        return;
    }

    if (preRemplir && ligneActive) {
        // Générer deux lignes : la ligne saisie et la ligne avec les champs débit/crédit inversés
        nouvellesLignes = await ajouterLignePreRemplieOP(idCounter, ligneActive, codeJournal, moisActuel, filterOP);
        console.log("Lignes pré-remplies générées :", nouvellesLignes);
    } else {
        // Création d'une ligne vide
        let ligneVide = ajouterLigneVideOP(idCounter, ligneActive, codeJournal, moisActuel);
        nouvellesLignes.push(ligneVide);
    }

    if (Array.isArray(nouvellesLignes)) {
        nouvellesLignes.forEach(ligne => {
            table.addRow(ligne, false);
        });
    } else {
        console.error("Erreur : nouvellesLignes n'est pas un tableau.");
    }
    return nouvellesLignes;
}

// 2. Fonction pour ajouter une ligne pré-remplie avec inversion des champs débit et crédit
async function ajouterLignePreRemplieOP(idCounter, ligneActive, codeJournal, moisActuel, filterOP) {
    let lignes = [];

    // Première ligne : identique à la saisie active
    let ligne1 = { ...ligneActive, id: idCounter++ };
    ligne1.type_journal = codeJournal || '';
    lignes.push(ligne1);

    // Deuxième ligne : copie de la ligne active avec inversion du débit et du crédit
    let ligne2 = { ...ligneActive, id: idCounter++ };
    const originalDebit = parseFloat(ligneActive.debit) || 0;
    const originalCredit = parseFloat(ligneActive.credit) || 0;

    // Inversion : si l'utilisateur a saisi un crédit, la ligne générée aura ce montant en débit, et inversement
    ligne2.debit = originalCredit;
    ligne2.credit = originalDebit;
    ligne2.type_journal = codeJournal || '';

    lignes.push(ligne2);

    return lignes;
}

// 3. Fonction pour créer une ligne vide pour Opérations Diverses
function ajouterLigneVideOP(idCounter, ligneActive, codeJournal, moisActuel) {
    return {
        id: idCounter,
        compte: '',
        contre_partie: '',
        debit: 0,
        credit: 0,
        piece: '',
        piece_justificative: '',
        numero_facture: '',
        libelle: '',
        type_journal: codeJournal,
        date: '' // Champ date à afficher en input (avec la classe .date-ligne par exemple)
    };
}

async function enregistrerLignesOperationsDiverses() {
    try {
        let lignes = tableOP.getData();
        console.log("📌 Données récupérées :", lignes);

        const journalSelect = document.querySelector("#journal-operations-diverses");
        const codeJournal = journalSelect.value;
        if (!codeJournal) {
            alert("⚠️ Veuillez sélectionner un journal.");
            return;
        }
        const selectedOption = journalSelect.options[journalSelect.selectedIndex];
        const categorie = selectedOption ? selectedOption.getAttribute("data-type") : "";
        console.log("Catégorie :", categorie);

        const selectedFilter = document.querySelector('input[name="filter-operations-diverses"]:checked')?.value || null;

        // Mise à jour éventuelle des champs (exemple pour piece_justificative)
        lignes = updatePieceJustificative(lignes);

        const lignesAEnvoyer = lignes.map(ligne => ({
            id: ligne.id || null,
            date: ligne.date || new Date().toISOString().slice(0, 10),
            numero_dossier: ligne.numero_dossier || null,
            numero_facture: ligne.numero_facture || 'N/A',
            compte: ligne.compte || '',
            debit: ligne.debit ? parseFloat(ligne.debit) : 0,
            credit: ligne.credit ? parseFloat(ligne.credit) : 0,
            contre_partie: ligne.contre_partie || '',
            type_journal: codeJournal,
            categorie: categorie,
            // On force ces champs à être null s'ils ne sont pas définis
            rubrique_tva: (typeof ligne.rubrique_tva !== 'undefined') ? ligne.rubrique_tva : null,
            compte_tva: (typeof ligne.compte_tva !== 'undefined') ? ligne.compte_tva : null,
            prorat_de_deduction: (typeof ligne.prorat_de_deduction !== 'undefined') ? ligne.prorat_de_deduction : null,
            piece_justificative: ligne.piece_justificative || '',
            libelle: ligne.libelle || '',
            filtre_selectionne: selectedFilter,
            value: (typeof ligne.solde_cumule !== "undefined") ? ligne.solde_cumule : ""
        }));

        console.log("📤 Données envoyées :", lignesAEnvoyer);

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch('/lignes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ lignes: lignesAEnvoyer })
        });

        console.log("Réponse HTTP:", response.status, response.statusText);
        if (!response.ok) {
            console.error("❌ Erreur serveur :", response.status, response.statusText);
            alert(`Erreur lors de l'enregistrement : ${response.statusText}`);
            return;
        }

        const result = await response.json();
        console.log("📥 Réponse du serveur :", result);

        if (Array.isArray(result)) {
            tableOP.setData(result);
            console.log("✅ Tableau mis à jour.");
        } else if (result && Array.isArray(result.data)) {
            tableOP.setData(result.data);
            console.log("✅ Tableau mis à jour.");
        } else {
            console.warn("⚠️ Format inattendu de la réponse :", result);
            alert("Aucune donnée valide reçue du serveur.");
            return;
        }

        calculerSoldeCumuleOperationsDiverses();

        // Forcer l'ajout d'une nouvelle ligne vide pour continuer la saisie
        tableOP.addRow({
            id: null,
            compte: '',
            contre_partie: '',
            debit: 0,
            credit: 0,
            piece_justificative: '',
            libelle: '',
            type_journal: codeJournal,
            value: ""
        });
    } catch (error) {
        console.error("🚨 Erreur lors de l'enregistrement :", error);
        alert("❌ Une erreur s'est produite lors de l'enregistrement. Consultez la console pour plus de détails.");
    }
}

async function ecouterEntrerOP(table) {
    table.element.addEventListener("keydown", async function (event) {
        if (event.key === "Enter") {
            event.preventDefault();

            const selectedRows = table.getSelectedRows();
            if (selectedRows.length === 0) {
                console.error("❌ Aucune ligne active trouvée");
                return;
            }

            const ligneActive = selectedRows[0].getData();
            const nouvelleLigne = {
                id: null,
                date: ligneActive.date || new Date().toISOString().slice(0, 10),
                compte: ligneActive.compte || '',
                numero_dossier: ligneActive.numero_dossier || null,
                numero_facture: ligneActive.numero_facture || '',
                contre_partie: ligneActive.contre_partie || '',
                debit: ligneActive.credit, // inversion
                credit: ligneActive.debit, // inversion
                piece_justificative: ligneActive.piece_justificative || '',
                libelle: ligneActive.libelle || '',
                type_journal: document.querySelector("#journal-operations-diverses").value,
                value: ""
            };

            const newRow = await table.addRow(nouvelleLigne);
            const dataActuelle = table.getData();
            updatePieceJustificative(dataActuelle);

            setTimeout(() => {
                const newRowCells = newRow.getCells();
                if (newRowCells && newRowCells.length > 0) {
                    if (typeof newRowCells[0].edit === 'function') {
                        try {
                            newRowCells[0].edit();
                        } catch (e) {
                            console.warn("Erreur lors de l'édition de la première cellule :", e);
                        }
                    } else {
                        console.warn("La méthode edit n'est pas définie pour la première cellule.");
                    }
                } else {
                    console.warn("La nouvelle ligne ne contient aucune cellule.");
                }
            }, 100);

            selectedRows[0].deselect();
            await enregistrerLignesOperationsDiverses();
            calculerSoldeCumuleOperationsDiverses();
        }
    });
}

ecouterEntrerOP(tableOP);




///////////////////////////////////////////////////////////////////////////
// Fonction pour lire un fichier Excel
function readExcelFile(file) {
    const fileReader = new FileReader();
    fileReader.onload = function() {
        const data = new Uint8Array(this.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const sheet = workbook.Sheets[workbook.SheetNames[0]];
        const sheetData = XLSX.utils.sheet_to_json(sheet, { header: 1 });
        displayExcelData(sheetData);
    };
    fileReader.readAsArrayBuffer(file);
}

// Fonction pour afficher les données Excel dans Tabulator
function displayExcelData(data) {
    const fields = [
        'Date', 'N°facture', 'Compte', 'Libellé',
        'Débit', 'Crédit', 'Contre-partie', 'Rubrique TVA','Compte TVA',
        'Prorat de deduction', 'Solde cumulé','pièce_justificative'
    ];

    const rows = [];
    for (let i = 1; i < data.length; i++) {
        const row = {};
        fields.forEach((field, index) => {
            let value = data[i][index] !== undefined ? data[i][index] : ''; // Gérer les valeurs undefined

            // Vérifier si la valeur est un nombre et correspond à une date Excel
            if (field === 'Date' && typeof value === 'number') {
                // Convertir le nombre Excel en date JavaScript
                const excelDate = new Date((value - 25569) * 86400 * 1000);
                value = excelDate.toLocaleDateString(); // Formater la date selon vos besoins
            }

            row[field.toLowerCase().replace(/ /g, "_")] = value;
        });
        console.log(row); // Afficher chaque ligne pour déboguer
        rows.push(row);
    }

    tableAch.setData(rows); // Mettre à jour les données de Tabulator
}

// Fonction pour lire un fichier PDF
function readPdfFile(file) {
    const reader = new FileReader();
    reader.onload = function() {
        const pdfData = new Uint8Array(this.result);
        pdfjsLib.getDocument(pdfData).promise.then(pdf => {
            const numPages = pdf.numPages;
            let pdfText = '';
            let textPromises = [];
            for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                textPromises.push(pdf.getPage(pageNum).then(page => {
                    return page.getTextContent().then(textContent => {
                        let pageText = '';
                        textContent.items.forEach(item => {
                            pageText += item.str + ' ';
                        });
                        return pageText;
                    });
                }));
            }
            Promise.all(textPromises).then(pagesText => {
                pdfText = pagesText.join(' ');
                displayPdfData(pdfText);
            });
        });
    };
    reader.readAsArrayBuffer(file);
}

// Fonction pour afficher le contenu PDF dans Tabulator
function displayPdfData(text) {
    const lines = text.split('\n');
    const rows = [];
    lines.forEach(line => {
        const columns = line.trim().split(/\s{2,}/);
        const row = {
            date: columns[0] || '',
            numero_facture: columns[1] || '',

            compte: columns[2] || '',
            libelle: columns[3] || '',
            debit: columns[4] || '',
            credit: columns[5] || '',
            contre_partie: columns[6] || '',
            rubrique_tva: columns[7] || '',
            compte_tva: columns[8] || '',
            prorat_de_deduction: columns[9] || '',
            piece_justificative: columns[10] || '',

        };
        rows.push(row);
    });

    tableAch.setData(rows); // Mettre à jour les données de Tabulator
}

$(document).ready(function() {
    // Fermer la modale lorsqu'on clique sur la croix
    $('.close-btn').on('click', function() {
        $('#file_achat_Modal').hide();
    });

    // Fermer la modale si on clique en dehors de celle-ci
    $(window).on('click', function(event) {
        if ($(event.target).is('#file_achat_Modal')) {
            $('#file_achat_Modal').hide();
        }
    });

    // Gestion de la sélection des fichiers dans la modale
    $('.file-button').on('click', function() {
        $('.file-button').removeClass('selected');
        $(this).addClass('selected');
    });

    // Lorsque l'utilisateur clique sur "Confirmer" dans la modale
    $('#confirmBtnAchat').on('click', function() {
        var selectedFileName = $('.file-button.selected').data('filename');
        var cell = $(this).data('cell');
        var pieceGeneree = $(this).data('piece');
        var nouvelleValeur = selectedFileName || pieceGeneree;

        var cellElement = cell.getElement();
        $(cellElement).find('.selected-file-input').val(nouvelleValeur);
        cell.getRow().update({ piece_justificative: nouvelleValeur });
        console.log("Mise à jour de la pièce justificative :", nouvelleValeur);

        $('#file_achat_Modal').hide();

        document.getElementById('file-input').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || file.type === 'application/vnd.ms-excel') {
                    readExcelFile(file);
                } else if (file.type === 'application/pdf') {
                    readPdfFile(file);
                } else {
                    alert('Veuillez sélectionner un fichier Excel ou PDF valide.');
                }
            }
        });
    });
});

 // --- Gestion de la modale et des événements d'upload ---
 $(document).ready(function() {
    // Fermer la modale lorsqu'on clique sur la croix
    $('.close-btn').on('click', function() {
        $('#file_vente_Modal').hide();
    });

    // Fermer la modale si on clique en dehors de celle-ci
    $(window).on('click', function(event) {
        if ($(event.target).is('#file_vente_Modal')) {
            $('#file_vente_Modal').hide();
        }
    });

    // Gestion de la sélection des fichiers dans la modale
    $('.file-button').on('click', function() {
        // Retirer la classe 'selected' de tous les boutons et ajouter à celui cliqué
        $('.file-button').removeClass('selected');
        $(this).addClass('selected');
    });

    // Lorsque l'utilisateur clique sur "Confirmer" dans la modale
    $('#confirmBtnVente').on('click', function() {
        // Récupérer le nom du fichier sélectionné (à partir d'un attribut data-filename sur le bouton)
        var selectedFileName = $('.file-button.selected').data('filename');

        // Récupérer la cellule et le numéro de pièce généré stocké lors du clic
        var cell = $(this).data('cell');
        var pieceGeneree = $(this).data('piece');

        // Si aucun fichier n'a été choisi, on garde le numéro de pièce généré
        var nouvelleValeur = selectedFileName || pieceGeneree;

        // Mise à jour de l'input affiché dans la cellule
        var cellElement = cell.getElement();
        $(cellElement).find('.selected-file-input').val(nouvelleValeur);

        // Mettre à jour la donnée dans Tabulator
        cell.getRow().update({ piece_justificative: nouvelleValeur });
        console.log("Mise à jour de la pièce justificative :", nouvelleValeur);

        // Fermer la modale
        $('#file_vente_Modal').hide();
    });

});

})();

tabulatorManager.applyToTabulator(tableOP);






       });
// Gestion des onglets
$('.tab').on('click', function () {
    const tabId = $(this).data('tab');
    $('.tab').removeClass('active');
    $('.tab-content').removeClass('active');
    $(this).addClass('active');
    $('#' + tabId).addClass('active');
});

