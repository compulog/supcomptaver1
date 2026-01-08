// ...existing code...
(function (global) {
  "use strict";



  // ---------- Définitions défensives globales ----------
  // Safe-read helper for select option data-contre_partie
  function _readSelectContrePartie(sel) {
    try {
      if (!sel) return "";
      const opt = sel.options && sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex] : null;
      if (!opt) return "";
      return String(opt.dataset?.contre_partie ?? opt.getAttribute('data-contre_partie') ?? opt.dataset?.contrePartie ?? "").trim();
    } catch (e) {
      return "";
    }
  }

  // getJournalContrePartie safe: essaye plusieurs selects
  function getJournalContrePartieSafe(codeJournal) {
    try {
      const selectors = ['#journal-achats', '#journal-ventes', '#journal-operations-diverses', 'select[name="journal"]'];
      if (codeJournal) {
        for (const s of selectors) {
          const el = document.querySelector(s);
          if (!el || !el.options) continue;
          const byVal = Array.from(el.options).find(o => String(o.value).trim() === String(codeJournal).trim());
          if (byVal) return String(byVal.dataset?.contre_partie ?? byVal.getAttribute('data-contre_partie') ?? byVal.dataset?.contrePartie ?? "").trim();
        }
      }
      for (const s of selectors) {
        const el = document.querySelector(s);
        if (!el) continue;
        const cp = _readSelectContrePartie(el);
        if (cp) return cp;
      }
    } catch (e) { /* silent */ }
    return "";
  }

  // Défensive: applique une contre_partie par défaut aux lignes d'une instance Tabulator
  function applyDefaultContrePartieToTable(tabulatorInstance, codeJournal) {
    try {
      if (!tabulatorInstance) return;
      const defaultCp = String(getJournalContrePartieSafe(codeJournal) || "").trim();
      if (!defaultCp) return;

      const getRows = typeof tabulatorInstance.getRows === "function" ? tabulatorInstance.getRows() : [];
      if (!getRows || !getRows.length) return;

      getRows.forEach(row => {
        try {
          if (!row || typeof row.getData !== "function") return;
          const data = row.getData() || {};
          const cur = String(data.contre_partie ?? data.contrePartie ?? "").trim();
          if (cur !== "") return;
          if (typeof row.update === "function") {
            row.update({ contre_partie: defaultCp });
          } else if (typeof tabulatorInstance.updateOrAddData === "function") {
            const merged = Object.assign({}, data, { contre_partie: defaultCp });
            try { tabulatorInstance.updateOrAddData([merged]); } catch (e) { /* ignore */ }
          }
        } catch (rowErr) {
          console.warn("applyDefaultContrePartieToTable per-row error", rowErr && (rowErr.message || rowErr));
        }
      });
    } catch (e) {
      console.warn("applyDefaultContrePartieToTable error", e && (e.message || e));
    }
  }

  // Expose si absent (n'écrase pas volontairement s'il existe et est fonctionnel)
  try {
    if (typeof global.applyDefaultContrePartieToTable !== "function") {
      global.applyDefaultContrePartieToTable = applyDefaultContrePartieToTable;
    } else {
      // si existe mais n'est pas une fonction -> remplacer
      if (!(global.applyDefaultContrePartieToTable instanceof Function)) {
        global.applyDefaultContrePartieToTable = applyDefaultContrePartieToTable;
      }
    }
  } catch (e) { global.applyDefaultContrePartieToTable = applyDefaultContrePartieToTable; }

  // expose helper
  try { if (typeof global.getJournalContrePartie !== "function") global.getJournalContrePartie = getJournalContrePartieSafe; } catch (e) {}

  // ---------- Wrapper defensif pour fetch (normalise certaines réponses backend bancales) ----------
  // garde référence originale
  const _origFetch = global.fetch.bind(global);

  global.fetch = async function(url, init) {
    // appel natif
    try {
      const resp = await _origFetch(url, init);

      // si c'est la route get-clients, normaliser la payload JSON en tableau
      try {
        if (typeof url === 'string' && url.indexOf('/get-clients') !== -1) {
          // clone response body as text, parse and normalise to array if needed
          const txt = await resp.clone().text().catch(()=>null);
          if (!txt) return resp;
          let parsed;
          try { parsed = JSON.parse(txt); } catch(e){ parsed = null; }
          if (Array.isArray(parsed)) {
            return new Response(JSON.stringify(parsed), { status: resp.status, statusText: resp.statusText, headers: resp.headers });
          }
          // si backend renvoie { data: [...] } ou { clients: [...] } -> extraire
          const arr = (parsed && Array.isArray(parsed.data)) ? parsed.data
                    : (parsed && Array.isArray(parsed.clients)) ? parsed.clients
                    : (parsed && Array.isArray(parsed.result)) ? parsed.result
                    : null;
          if (arr) {
            return new Response(JSON.stringify(arr), { status: resp.status, statusText: resp.statusText, headers: resp.headers });
          }
          // sinon, si parsed est object non-array, tenter d'extraire mapped values to empty array fallback
          if (parsed && typeof parsed === 'object') {
            // try to find array-valued property
            for (const k of Object.keys(parsed)) {
              if (Array.isArray(parsed[k])) {
                return new Response(JSON.stringify(parsed[k]), { status: resp.status, statusText: resp.statusText, headers: resp.headers });
              }
            }
            // fallback : return empty array to avoid .map crash
            return new Response(JSON.stringify([]), { status: resp.status, statusText: resp.statusText, headers: resp.headers });
          }
        }

        // normaliser get-contre-parties & get-rubriques-tva responses (si nécessaire)
        if (typeof url === 'string' && (url.indexOf('/get-contre-parties') !== -1 || url.indexOf('/get-rubriques-tva') !== -1 || url.indexOf('/get-rubriques-tva-vente') !== -1)) {
          const txt2 = await resp.clone().text().catch(()=>null);
          if (!txt2) return resp;
          try {
            const parsed2 = JSON.parse(txt2);
            if (Array.isArray(parsed2)) return new Response(JSON.stringify(parsed2), { status: resp.status, statusText: resp.statusText, headers: resp.headers });
            if (parsed2 && Array.isArray(parsed2.data)) return new Response(JSON.stringify(parsed2.data), { status: resp.status, statusText: resp.statusText, headers: resp.headers });
          } catch(e) { /* ignore parse error -> return original resp */ }
        }
      } catch (normErr) { /* ignore normaliser errors */ }

      return resp;
    } catch (err) {
      // en cas d'erreur réseau, rejeter proprement
      throw err;
    }
  };

  // ---------- Fin IIFE ----------
})(window);
// ...existing code...

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
// $('#Achats-modal-file').on('click', function () {
//   console.log("Ouverture de la modale des fichiers d'achat");
//     $('#files_achat_Modal').show();
// });
function afficherfileachatpop(){
  // console.log('aaa')
  $('#achatModal_main').show();
}
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
            const select = $('#' + selectId);

            // Réinitialisation de Select2 si déjà initialisé
            if (select.hasClass('select2-hidden-accessible')) {
                select.select2('destroy');
            }
            select.empty();
            select.append(new Option('Sélectionnez une Rubrique', ''));

            const excludedNumRacines = [147, 151, 152, 148, 144];

            // Parcours des catégories reçues
            data.categories.forEach(categoryObj => {
                // Afficher le nom de la catégorie (numérotée) une seule fois
                const catOption = new Option(categoryObj.categoryName, '', false, false);
                $(catOption).addClass('category').prop('disabled', true);
                select.append(catOption);

                // Sous-catégories (indentées)
                categoryObj.subCategories.forEach(sub => {
                    const subOption = new Option(`  ${sub}`, '', false, false);
                    $(subOption).addClass('subcategory').prop('disabled', true);
                    select.append(subOption);
                });

                // Rubriques associées
                categoryObj.rubriques.forEach(rubrique => {
                    if (!excludedNumRacines.includes(rubrique.Num_racines)) {
                        const text = `    ${rubrique.Num_racines}: ${rubrique.Nom_racines} : ${Math.round(rubrique.Taux)}%`;
                        const opt = new Option(text, rubrique.Num_racines);
                        $(opt).attr('data-search-text', `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.categoryName}`);
                        select.append(opt);
                    }
                });
            });

            // Initialisation de Select2
            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true,
                templateResult: function (data) {
                    if (!data.id) return data.text;
                    const el = $(data.element);
                    if (el.hasClass('category')) {
                        return $('<span style="font-weight:bold; padding-left:0;">' + data.text + '</span>');
                    }
                    if (el.hasClass('subcategory')) {
                        return $('<span style="font-weight:bold; padding-left:20px;">' + data.text + '</span>');
                    }
                    return $('<span>' + data.text + '</span>');
                },
                matcher: function (params, data) {
                    if ($.trim(params.term) === '') return data;
                    const searchText = $(data.element).data('search-text');
                    return searchText && searchText.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
                }
            });

            // Sélection initiale
            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des rubriques TVA :', textStatus, errorThrown);
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
    const numeroDossier = row.getCell("numero_dossier").getValue() || "Inconnu";
    const libelle = `F° ${numeroDossier} ${numeroFacture} ${fournisseur.intitule || ""}`;
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

function evaluateMathExpression(expr) {
  if (expr === null || expr === undefined) return NaN;
  let s = String(expr).trim();
  if (s === "") return NaN;

  // normaliser les virgules en points
  s = s.replace(/,/g, ".");

  // remplacer les cas de pourcentage "10%" -> "(10/100)"
  s = s.replace(/(\d+(\.\d+)?)\s*%/g, "($1/100)");

  // sécurité: n'autoriser que chiffres, opérateurs, parentheses, points et espaces
  if (!/^[0-9+\-*/().\s]+$/.test(s)) {
    throw new Error("Expression non autorisée");
  }

  // limite longueur pour éviter abus
  if (s.length > 200) throw new Error("Expression trop longue");

  // évaluer de façon contrôlée
  try {
    // eslint-disable-next-line no-new-func
    const res = Function(`"use strict"; return (${s});`)();
    if (typeof res !== "number" || !isFinite(res)) throw new Error("Résultat invalide");
    return +res; // garantir number
  } catch (err) {
    throw new Error("Impossible d'évaluer l'expression");
  }
}

/* ===========================
   Editor de type "calculatrice" pour Tabulator
   - accepte expressions
   - valide sur Enter / blur
   - appelle navigateAfterCommitRobust après commit
   =========================== */
function calcNumberEditorFactory() {
  return function(cell, onRendered, success, cancel) {
    const field = cell.getColumn().getField();
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.autocomplete = "off";

    // valeur initiale : si champ vide, laisser vide, sinon afficher valeur avec 2 décimales
    const raw = cell.getValue();
    if (raw !== null && raw !== undefined && raw !== "") {
      // si la valeur est numérique, afficher en 2 décimales ; sinon afficher tel quel
      const asNum = Number(raw);
      input.value = !isNaN(asNum) ? asNum.toFixed(2) : String(raw);
    } else {
      input.value = "";
    }

    // focus quand rendu
    onRendered(() => {
      try { input.focus(); input.select && input.select(); } catch (e) {}
    });

    // commit function (direction null -> normal, or 'next'/'prev' for navigation)
    function commit(direction = null) {
      const expr = input.value.trim();
      if (expr === "") {
        // vide -> store null (ou 0 si tu préfères)
        success(null);
        // navigation
        const row = cell.getRow();
        if (direction) navigateAfterCommitRobust(row, field, direction);
        return;
      }
      let value;
      try {
        value = evaluateMathExpression(expr);
      } catch (err) {
        // message utile pour l'utilisateur
        alert("Expression invalide pour le montant : " + err.message);
        cancel();
        return;
      }
      // enregistrer la valeur numérique (nombre)
      success(value);
      // navigation
      const row = cell.getRow();
      if (direction) navigateAfterCommitRobust(row, field, direction);
    }

    // handlers
    input.addEventListener("blur", () => commit(null));
    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        if (e.shiftKey) commit("prev"); else commit("next");
      } else if (e.key === "Escape") {
        e.preventDefault();
        cancel();
      }
    });

    // formatage permissif à la volée : permet chiffres, opérateurs et % et espaces
    input.addEventListener("input", () => {
      // autoriser tout ici (on validera à commit); toutefois on peut filtrer caractères interdits
      // remplacer les caractères non autorisés par rien (optionnel)
      input.value = input.value.replace(/[^0-9+\-*/().,%\s]/g, "");
    });

    return input;
  };
}

/* ===========================
   Formatters pour affichage monétaire simple
   =========================== */
function moneyFormatter(cell) {
  const v = cell.getValue();
  if (v === null || v === undefined || v === "") return "0.00";
  const n = Number(v);
  if (isNaN(n)) return String(v);
  // afficher avec séparateur décimal point et 2 décimales
  return n.toFixed(2);
}


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

// ---------- Editeur Crédit -> navigation vers contrepartie ----------
const creditEditor = (nextField = 'contre_partie') => {
  return function(cell, onRendered, success, cancel) {
    const input = document.createElement("input");
    input.type = "text"; // permet expressions
    input.placeholder = "0.00";
    input.style.width = "100%";
    input.value = cell.getValue() != null ? cell.getValue().toString() : "";

    onRendered(() => {
      input.focus();
      input.select();
    });

    function commit(direction = null) {
      let raw = (input.value || "").toString().trim();
      if (raw === "") raw = "0";

      // Évaluer l'expression avec la calculatrice
      let value;
      try {
        value = evaluateMathExpression(raw); // utilise la fonction calculatrice sécurisée
      } catch (err) {
        alert("Expression invalide : " + err.message);
        cancel();
        return;
      }

      // Arrondir à 2 décimales et mettre à jour Tabulator
      const out = parseFloat(value.toFixed(2));
      success(out);

      // appeler le calcul des soldes après la mise à jour
      try { setTimeout(() => { if (typeof calculerSoldeCumule === "function") calculerSoldeCumule(); }, 0); } catch (e) { /* safe-fail */ }

      // navigation vers la cellule suivante si demandé
      if (direction === "next") {
        const row = cell.getRow();
        row.scrollTo()
          .then(() => {
            const nextCell = row.getCell(nextField);
            if (nextCell) {
              setTimeout(() => { try { nextCell.edit(true); } catch (err) { /* safe-fail */ } }, 50);
            }
          })
          .catch(() => {
            const nextCell = row.getCell && row.getCell(nextField);
            if (nextCell) {
              setTimeout(() => { try { nextCell.edit(true); } catch (err) { /* safe-fail */ } }, 50);
            }
          });
      }
    }

    input.addEventListener("blur", () => commit(null));
    input.addEventListener("keydown", e => {
      if (e.key === "Enter") {
        e.preventDefault();
        commit("next"); // navigation vers nextField
      } else if (e.key === "Escape") {
        e.preventDefault();
        cancel();
      } else if (e.key === "Tab") {
        e.preventDefault();
        commit("next");
      }
    });

    return input;
  };
};


function guaranteedInputEditor(fieldName, nextField = null, prevField = null) {
  return function(cell, onRendered, success, cancel) {
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.value = cell.getValue() || "";

    let validated = false;

    onRendered(() => {
      try { input.focus(); input.select && input.select(); } catch(e){}
    });

    // ---- Helpers réseau / fallback (utilisés seulement si propagation activée) ----
    async function sendUpdateToServerWithFallbackLocal(id, field, value, numero_facture) {
      const token = (typeof csrfToken !== 'undefined' && csrfToken) ? csrfToken
                    : (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');
      // try POST update-field
      const postUrl = `/operation-courante/${encodeURIComponent(id)}/update-field`;
      try {
        const resp = await fetch(postUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin',
          body: JSON.stringify({ field, value })
        });
        if (resp.ok) return await resp.json().catch(()=>null);
      } catch (e) {
        console.warn('POST update-field failed, will fallback to PUT', e);
      }

      // fallback PUT (existing endpoint)
      const putUrl = `/operations/${encodeURIComponent(id)}`;
      const resp2 = await fetch(putUrl, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ field, value, numero_facture: numero_facture || '' })
      });
      if (!resp2.ok) {
        const t = await resp2.text().catch(()=> '');
        throw new Error(`PUT -> HTTP ${resp2.status} : ${t.slice(0,200)}`);
      }
      return await resp2.json().catch(()=>null);
    }

    // propagate only for numero_dossier by default (keeps backward compatibility)
    const shouldPropagate = String(fieldName || '').toLowerCase() === 'numero_dossier';

    // main propagation routine (async)
    async function propagateFieldValue(targetRow, tableRef, newValue) {
      if (!shouldPropagate) return;
      const data = targetRow.getData() || {};
      const numero = data.numero_facture || '';
      // collect backups for rollback
      const backups = [];
      try {
        const tbl = tableRef || (targetRow && typeof targetRow.getTable === 'function' ? targetRow.getTable() : window.tableAch);
        if (tbl && typeof tbl.getRows === 'function') {
          tbl.getRows().forEach(r => {
            try {
              const rd = r.getData();
              if (rd && String(rd.numero_facture || '') === String(numero)) {
                backups.push({ id: rd.id || null, rowRef: r, oldValue: rd[fieldName] });
              }
            } catch(e){}
          });
        }
      } catch(e) {
        console.warn('collect backups failed', e);
      }

      // optimistic apply to all matching rows
      try {
        if (backups.length) {
          backups.forEach(b => { try { if (b.rowRef) b.rowRef.update({ [fieldName]: newValue }); } catch(e){} });
        } else {
          try { targetRow.update({ [fieldName]: newValue }); } catch(e){ console.warn(e); }
        }
      } catch(e) { console.warn('optimistic apply failed', e); }

      // do not call server for temp rows
      const isTemp = !data.id || String(data.id).startsWith('temp_') || data.__isTemp === true;
      if (isTemp) return { ok: true, message: 'temp row client-only' };

      // send to server using global helper if available, otherwise local fallback
      try {
        let resp;
        if (typeof window.sendUpdateToServerWithFallback === 'function') {
          resp = await window.sendUpdateToServerWithFallback(data.id, fieldName, newValue, numero);
        } else {
          resp = await sendUpdateToServerWithFallbackLocal(data.id, fieldName, newValue, numero);
        }

        // normalise server response and apply returned server object where appropriate
        if (resp) {
          let returned = null;
          if (Array.isArray(resp) && resp.length) returned = resp[0];
          else if (resp.data && Array.isArray(resp.data) && resp.data.length) returned = resp.data[0];
          else if (resp.ligne) returned = resp.ligne;
          else returned = resp;

          if (returned && typeof returned === 'object') {
            try { targetRow.update(returned); } catch(e){ console.warn('update targetRow with server returned object failed', e); }

            // propagate server value to other rows if they didn't change since backup
            const serverVal = returned[fieldName] !== undefined ? returned[fieldName] : newValue;
            try {
              const tbl = tableRef || (targetRow && typeof targetRow.getTable === 'function' ? targetRow.getTable() : window.tableAch);
              if (tbl && typeof tbl.getRows === 'function') {
                tbl.getRows().forEach(r => {
                  try {
                    const rd = r.getData();
                    if (rd && String(rd.numero_facture || '') === String(numero)) {
                      const b = backups.find(x => (x.id && x.id === rd.id) || (x.rowRef && x.rowRef === r));
                      const changedAfterSend = b ? (String(rd[fieldName] || '') !== String(b.oldValue || '')) : false;
                      if (!changedAfterSend) {
                        try { r.update({ [fieldName]: serverVal }); } catch(e){ console.warn('propagate after server failed', e); }
                      }
                    }
                  } catch(e){}
                });
              }
            } catch(e){ console.warn('propagate server val failed', e); }
          }
        }
        return { ok: true };
      } catch (err) {
        // rollback to backups
        try {
          backups.forEach(b => { try { if (b.rowRef) b.rowRef.update({ [fieldName]: b.oldValue }); } catch(e){} });
        } catch(e) { console.warn('rollback failed', e); }

        if (window.Swal) Swal.fire({ icon:'error', title:'Erreur', text: String(err.message || err) });
        else console.error(err);

        throw err;
      }
    }

    // commit with direction (next/prev)
    function commit(direction = null) {
      const value = input.value.trim();
      success(value);
      validated = true;

      // trigger propagation in background (do not block navigation)
      try {
        const row = cell.getRow();
        const tableRef = (cell.getTable && cell.getTable()) || (row && row.getTable && row.getTable()) || window.tableAch;
        // run async but don't await here to keep UI snappy
        propagateFieldValue(row, tableRef, value).catch(err => {
          // already handled inside propagateFieldValue (swal/log), but log here too
          console.error('propagation error (background):', err);
        });
      } catch(e){ console.warn('failed to start propagation', e); }

      // navigation vers la cellule suivante/précédente
      if (direction) {
        const row = cell.getRow();
        const targetField = direction === "next" ? nextField : prevField;
        if (!targetField) return;
        row.scrollTo()
          .then(() => {
            const nextCell = row.getCell(targetField);
            if (nextCell) setTimeout(() => { try { nextCell.edit(true); } catch(e){} }, 50);
          })
          .catch(() => {
            const nextCell = row.getCell && row.getCell(targetField);
            if (nextCell) setTimeout(() => { try { nextCell.edit(true); } catch(e){} }, 50);
          });
      }
    }

    input.addEventListener("keydown", e => {
      if (e.key === "Enter") {
        e.preventDefault();
        commit("next"); // navigation vers nextField
      } else if (e.key === "Escape") {
        e.preventDefault();
        cancel();
      } else if (e.key === "Tab") {
        e.preventDefault();
        commit("next");
      }
    });

    input.addEventListener("blur", e => {
      if (!validated) {
        // keep focus if user didn't validate (keeps behaviour précédent)
        e.preventDefault();
        e.stopImmediatePropagation();
        setTimeout(() => { try { input.focus(); } catch(e){} }, 10);
      }
    });

    return input;
  };
}


/* ---------- 2) Navigation robuste : essayer edit() et retry si nécessaire ---------- */
function findNextEditableFieldRobust(table, currentField, direction = "next") {
  const cols = table.getColumns();
  const fields = cols.map(c => c.getField());
  const idx = fields.indexOf(currentField);
  if (idx === -1) return null;
  const step = direction === "next" ? 1 : -1;
  for (let i = idx + step; direction === "next" ? i < cols.length : i >= 0; i += step) {
    const col = cols[i];
    if (!col) continue;
    const def = col.getDefinition ? col.getDefinition() : {};
    const f = col.getField();
    if (!f) continue;
    // accepter si editor défini ou en dernier recours si field présent
    if (typeof def.editor !== "undefined" && def.editor !== false) return f;
    if (typeof def.editor === "undefined") return f;
  }
  return null;
}

function navigateAfterCommitRobust(row, currentField, direction = "next") {
  try {
    const table = row.getTable();
    const cols = table.getColumns();
    const fields = cols.map(c => c.getField());
    // trouver index de départ
    const startField = findNextEditableFieldRobust(table, currentField, direction);
    if (!startField) return;

    let startIdx = fields.indexOf(startField);
    if (startIdx === -1) return;

    // tenter éditer successivement ; si échec (pas de focus) on retry sur la suivante
    const maxRetriesPerCol = 3;
    for (let i = startIdx; i >= 0 && i < fields.length; i += (direction === "next" ? 1 : -1)) {
      const f = fields[i];
      if (!f) continue;
      try {
        const nextCell = row.getCell(f);
        if (!nextCell) continue;

        // essayer éditer avec quelques délais pour laisser le DOM se stabiliser
        let attempts = 0;
        const tryEdit = () => {
          attempts++;
          try {
            nextCell.edit(true);
            // si edit ne focus pas, on laisse une courte attente puis vérifie si l'éditeur a le focus
            setTimeout(() => {
              // vérifier si un élément dans le document a le focus et s'il appartient à la cellule
              const active = document.activeElement;
              if (active && nextCell.getElement && nextCell.getElement().contains(active)) {
                // tout ok, on s'arrête
                return;
              } else if (attempts < maxRetriesPerCol) {
                // réessayer same column
                tryEdit();
              } else {
                // abandonner cette colonne et passer à la suivante
                // console.warn(`No focus on field ${f}, moving to next`);
              }
            }, 60);
          } catch (err) {
            // si edit lance exception, on passe à la colonne suivante immédiatement
          }
        };

        tryEdit();
        // on a déclenché l'édition (même si asynchrone) : sortir de la boucle
        return;
      } catch (err) {
        // ignore et passer à la suivante
      }
    }
  } catch (e) {
    console.warn("navigateAfterCommitRobust error", e);
  }
}

/* === genericDateEditor réutilisable (pour 'ventes') === */
// genericDateEditorVte : commit + navigation automatique Enter -> next editable cell
const genericDateEditorVte = () => {

  function isString(v) {
    return typeof v === "string" || v instanceof String;
  }

  function safeParseDate(value) {
    if (value == null) return null;
    if (value instanceof Date && !isNaN(value)) {
      return luxon.DateTime.fromJSDate(value);
    }
    if (!isString(value)) return null;

    let dt = luxon.DateTime.fromISO(value);
    if (!dt.isValid) dt = luxon.DateTime.fromFormat(value, "yyyy-MM-dd HH:mm:ss");
    if (!dt.isValid) dt = luxon.DateTime.fromFormat(value, "yyyy-MM-dd");
    return dt.isValid ? dt : null;
  }

  return function(cell, onRendered, success, cancel) {

    const field = cell.getColumn().getField();
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.autocomplete = "off";

    const raw = cell.getValue();
    const dtInit = safeParseDate(raw);

    const moisRadio   = document.getElementById("filter-mois-ventes");
    const periodeSel  = document.getElementById("periode-ventes");

    // Définir placeholder selon le mode
    if (moisRadio && moisRadio.checked) {
      input.placeholder = "JJ"; // uniquement jour
      if (dtInit) input.value = dtInit.toFormat("dd"); // afficher seulement le jour si déjà rempli
    } else {
      input.placeholder = "JJ/MM";
      if (dtInit) input.value = dtInit.toFormat("dd/MM"); // jour/mois si mode normal
    }

    /* =========================
       COMMIT
       ========================= */
    function doCommit() {

      if (field === "date") {

        const anneeSel = document.getElementById("annee-ventes");
        const digits = (input.value || "").replace(/\D/g, "");

        let day, month, year;

        // 🔹 MODE EXERCICE : SAISIE JOUR SEULEMENT
        if (moisRadio && moisRadio.checked) {
          if (digits.length < 1) { cancel(); return; }
          day = parseInt(digits.slice(0, 2), 10) || 1;

          const [mm, yyyy] = periodeSel?.value?.split("-") || [];
          month = parseInt(mm, 10) || 1;
          year  = parseInt(yyyy, 10) || parseInt(anneeSel?.value, 10) || luxon.DateTime.local().year;

        }
        // 🔹 MODE NORMAL : JJ/MM
        else {
          if (digits.length < 4) { cancel(); return; }
          day   = parseInt(digits.slice(0, 2), 10);
          month = parseInt(digits.slice(2, 4), 10);
          year  = parseInt(anneeSel?.value, 10) || luxon.DateTime.local().year;
        }

        const dt = luxon.DateTime.local(year, month, day);
        if (!dt.isValid) { cancel(); return; }

        const iso = dt.toFormat("yyyy-MM-dd HH:mm:ss");
        success(iso);

        // copie vers date_livr si présent
        try {
          cell.getRow().update({ date_livr: iso });
        } catch (_) {}

        return;
      }

      cancel();
    }

    /* =========================
       FORMAT SAISIE
       ========================= */
    input.addEventListener("input", () => {
      const digits = (input.value || "").replace(/\D/g, "");

      // MODE EXERCICE → JOUR seul
      if (moisRadio && moisRadio.checked) {
        input.value = digits.slice(0, 2);
        return;
      }

      // MODE NORMAL → JJ/MM
      if (digits.length > 2) {
        input.value = digits.slice(0, 2) + "/" + digits.slice(2, 4);
      } else {
        input.value = digits;
      }
    });

    /* =========================
       EVENTS
       ========================= */
    input.addEventListener("blur", doCommit);

    input.addEventListener("keydown", e => {
      if (e.key === "Enter") {
        e.preventDefault();
        doCommit();
      }
      if (e.key === "Escape") {
        e.preventDefault();
        cancel();
      }
    });

    onRendered(() => input.focus());

    return input;
  };
};


const genericDateEditorOP = () => {

    const safeParseDate = (value) => {
        if (!value) return null;
        let dt = luxon.DateTime.fromISO(value);
        if (!dt.isValid) dt = luxon.DateTime.fromFormat(value, "yyyy-MM-dd HH:mm:ss");
        if (!dt.isValid) dt = luxon.DateTime.fromFormat(value, "yyyy-MM-dd");
        return dt.isValid ? dt : null;
    };

    const parseInputDDMMYYYY = (str) => {
        if (!str) return null;
        const digits = str.replace(/\D/g, "");
        if (digits.length < 6) return null;
        const day = parseInt(digits.slice(0, 2), 10) || 1;
        const month = parseInt(digits.slice(2, 4), 10) || 1;
        const yearPart = digits.slice(4);
        const year = yearPart.length === 2 ? (2000 + parseInt(yearPart, 10)) : parseInt(yearPart, 10) || luxon.DateTime.local().year;
        const dt = luxon.DateTime.local(year, month, day);
        return dt.isValid ? dt : null;
    };

    return function(cell, onRendered, success, cancel) {
        const input = document.createElement("input");
        input.type = "text";
        input.placeholder = "jj/MM/aaaa";
        input.style.width = "100%";
        input.autocomplete = "off";

        const raw = cell.getValue();
        const dtInit = safeParseDate(raw);
        if (dtInit) input.value = dtInit.toFormat("dd/MM/yyyy");

        const commit = () => {
            const dt = parseInputDDMMYYYY(input.value);
            if (!dt) { cancel(); return; }
            const iso = dt.toFormat("yyyy-MM-dd HH:mm:ss");
            success(iso);
        };

        input.addEventListener("blur", commit);
        input.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                commit();
            } else if (e.key === "Escape") {
                e.preventDefault();
                cancel();
            }
        });

        onRendered(() => input.focus());
        return input;
    };
};

// ---------- Editeur date réutilisable (Enter => next, Shift+Enter => prev, blur => commit) ----------
const genericDateEditor = (nextField = null, prevField = null) => {
  return function(cell, onRendered, success, cancel) {
    const field = cell.getColumn().getField();
    const input = document.createElement("input");
    input.type = "text";
    input.placeholder = "jj/MM/aaaa";
    input.style.width = "100%";

    // valeur initiale (luxon parsing robuste)
    const raw = cell.getValue();
    let dt = luxon.DateTime.fromISO(raw);
    if (!dt.isValid) dt = luxon.DateTime.fromFormat(raw, "yyyy-MM-dd HH:mm:ss");
    if (dt.isValid) input.value = dt.toFormat("dd/MM/yyyy");

    // helper commit qui accepte direction: 'next' | 'prev' | null
    function doCommit(direction = null) {
      // logique spécifique pour la colonne 'date' (copie vers date_livr si besoin / gestion periode)
      if (field === "date") {
        // reprise de ta logique existante pour gérer "jj/" et moisRadio etc.
        const moisRadio     = document.getElementById("filter-mois-achats");
        const periodeSelect = document.getElementById("periode-achats");
        const anneeSelect   = document.getElementById("annee-achats");

        const partsRaw = (input.value || "").replace(/\D/g, "");
        // normalization basique + format dd/MM/yyyy si possible
        let formatted = input.value;
        if (partsRaw.length >= 8) formatted = partsRaw.slice(0,2) + "/" + partsRaw.slice(2,4) + "/" + partsRaw.slice(4,8);
        else if (partsRaw.length > 4) formatted = partsRaw.slice(0,2) + "/" + partsRaw.slice(2,4) + "/" + partsRaw.slice(4);
        else if (partsRaw.length > 2) formatted = partsRaw.slice(0,2) + "/" + partsRaw.slice(2);
        input.value = formatted;

        const parts = input.value.split("/");
        if (parts.length < 1) return cancel();

        // pad day if needed
        if (parts[0] && parts[0].length < 2) parts[0] = parts[0].padStart(2,'0');

        const day = parseInt(parts[0], 10) || 1;
        let month, year;
        if (moisRadio?.checked) {
          const periode = periodeSelect?.value ?? "";
          const [mm, yyyy] = periode.split("-");
          month = parseInt(mm, 10);
          year  = parseInt(yyyy, 10);
        } else {
          month = parseInt(parts[1], 10) || 1;
          year  = parts[2] ? parseInt(parts[2], 10) : parseInt(anneeSelect?.value, 10);
        }

        const nDT = luxon.DateTime.local(year, month, day);
        if (!nDT.isValid) { cancel(); return; }

        const iso = nDT.toFormat("yyyy-MM-dd HH:mm:ss");
        success(iso);

        // si on édite 'date', on copie aussi vers date_livr (comme dans ton code initial)
        try {
          const row = cell.getRow();
          row.update({ date_livr: iso });
          // navigation vers suivant si demandé
          if (direction) navigateAfterCommitRobust(row, direction);
        } catch (err) {
          // safe-fail
          if (direction) {
            const row = cell.getRow();
            navigateAfterCommitRobust(row, direction);
          }
        }

        return;
      }

      // logique générale (ex: date_livr)
      const parts = (input.value || "").split("/").map(n => parseInt(n,10));
      const d = parts[0] || 1;
      const m = parts[1] || 1;
      const y = parts[2] || luxon.DateTime.local().year;
      const nDT = luxon.DateTime.local(y, m, d);
      if (!nDT.isValid) { cancel(); return; }

      const iso = nDT.toFormat("yyyy-MM-dd HH:mm:ss");
      success(iso);

      // navigation si demandé
      const row = cell.getRow();
      if (direction) navigateAfterCommitRobust(row, direction);
    }

    // navigation helper


    // flatpickr only for date_livr (conserve ton comportement)
    onRendered(() => {
      input.focus();
      if (field === "date_livr") {
        flatpickr(input, {
          dateFormat: "d/m/Y",
          allowInput: true,
          defaultDate: input.value,
          locale: "fr",
        });
      }
    });

    // input helpers (formatage live léger similaire à ton code)
    input.addEventListener("input", () => {
      // formatage dd/MM/yyyy à la volée (permissif)
      let parts = input.value.replace(/\D/g, "");
      if (parts.length >= 8) input.value = parts.slice(0,2) + "/" + parts.slice(2,4) + "/" + parts.slice(4,8);
      else if (parts.length > 4) input.value = parts.slice(0,2) + "/" + parts.slice(2,4) + "/" + parts.slice(4);
      else if (parts.length > 2) input.value = parts.slice(0,2) + "/" + parts.slice(2);
      else input.value = parts;
    });

    // events
    input.addEventListener("blur", () => doCommit(null));
    input.addEventListener("keydown", e => {
      if (e.key === "Enter") {
        e.preventDefault();
        if (e.shiftKey) doCommit("prev"); else doCommit("next");
      } else if (e.key === "Escape") {
        e.preventDefault();
        cancel();
      }
    });

    return input;
  };
};



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
function customListEditortva(cell, onRendered, success, cancel, editorParams) {
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
            item.textContent = `${compte}`;  // Affiche "compte - intitulé"
            // item.textContent = `${compte} - ${intitule}`;  // Affiche "compte - intitulé"
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

function customListEditorRub(cell, onRendered, success, cancel) {
    const row = cell.getRow();
    const value = cell.getValue();

    const selectId = "rubrique-tva-select-" + Date.now();
    const select = document.createElement("select");
    select.id = selectId;
    select.style.width = "100%";

    const container = document.createElement("div");
    container.appendChild(select);

    remplirRubriquesTva(selectId, value);

    $(select).on("select2:select", function (e) {
        const selectedId = e.params.data.id;
        const selectedText = e.params.data.text;

        row.update({
            rubrique_tva: selectedId,
            rubrique_tva_label: selectedText,
        });

        success(selectedId);
    });

    $(select).on("select2:close", function () {
        if (!select.value) {
            cancel();
        } else {
            success(select.value);
        }
    });

    container.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            if (select.value) {
                success(select.value);
            } else {
                cancel();
            }
        }
        if (e.key === "Escape") {
            e.preventDefault();
            cancel();
        }
    });

    onRendered(() => {
        $(select).select2("open");
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
                // Swal.fire({
                //     title: "Fournisseur non trouvé",
                //     text: "Voulez-vous ajouter ce fournisseur ?",
                //     icon: "question",
                //     showCancelButton: true,
                //     confirmButtonText: "Oui, ajouter",
                //     cancelButtonText: "Non"
                // }).then((result) => {
                //     if (result.isConfirmed) {
                        ouvrirPopupFournisseur(input.value, cell.getRow(), cell, 0);
                    // } else {
                    //     input.focus();
                    // }
                // });
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


function customNumberEditor(cell, onRendered, success, cancel, editorParams, nextField = null) {
  const input = document.createElement("input");
  input.type = "text"; // pour permettre des expressions
  input.style.width = "100%";
  input.value = cell.getValue() != null ? cell.getValue().toString() : "";

  let validated = false;

  onRendered(() => {
    try {
      input.focus();
      input.select && input.select();
    } catch (e) {}
  });

  function commit(direction = null) {
    let raw = (input.value || "").trim();
    if (raw === "") raw = "0";

    let value;
    try {
      value = evaluateMathExpression(raw);
    } catch (err) {
      alert("Expression invalide : " + err.message);
      validated = false;
      return;
    }

    const out = parseFloat(value.toFixed(2));
    success(out);
    validated = true;

    // appel du calcul des soldes après commit
    try { setTimeout(() => { if (typeof calculerSoldeCumule === "function") calculerSoldeCumule(); }, 0); } catch (e) {}

    // navigation vers la cellule suivante si demandé
    if (direction === "next" && nextField) {
      const row = cell.getRow();
      row.scrollTo()
        .then(() => {
          const nextCell = row.getCell(nextField);
          if (nextCell) setTimeout(() => { try { nextCell.edit(true); } catch(e){} }, 50);
        })
        .catch(() => {
          const nextCell = row.getCell && row.getCell(nextField);
          if (nextCell) setTimeout(() => { try { nextCell.edit(true); } catch(e){} }, 50);
        });
    }
  }

  input.addEventListener("keydown", e => {
    if (e.key === "Enter" || e.key === "Tab") {
      e.preventDefault();
      commit("next");
    } else if (e.key === "Escape") {
      e.preventDefault();
      cancel();
    }
  });

  input.addEventListener("blur", e => {
    if (!validated) {
      e.preventDefault();
      e.stopImmediatePropagation();
      setTimeout(() => { try { input.focus(); } catch(e){} }, 10);
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
const compteurMap = {};

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


function openPlanComptablePopup() {

    const societeId = document.querySelector('meta[name="societe_id"]').content;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    Swal.fire({
        title: "<h5><i class='bi bi-plus-circle'></i> Ajouter un compte</h5>",
        width: "600px",
        showCancelButton: true,
        confirmButtonText: "Ajouter",
        customClass: { popup: "p-0" },

        html: `
            <form id="planComptableFormAdd">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Compte</label>
                        <input type="text" id="compte_add" class="form-control form-control-sm shadow-sm" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Intitulé</label>
                        <input type="text" id="intitule_add" class="form-control form-control-sm shadow-sm" required>
                    </div>
                </div>
            </form>
        `,

        preConfirm: () => {

            const data = {
                compte: document.getElementById("compte_add").value,
                intitule: document.getElementById("intitule_add").value,
                societe_id: societeId
            };

            return fetch("/plancomptable", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf,
                },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(response => {
                if (response.error) {
                    // 🔥 garde le popup ouvert et affiche le message
                    Swal.showValidationMessage(response.error);
                    return false; // empêche la fermeture
                }
                return response; // succès
            })
            .catch(() => {
                Swal.showValidationMessage("Impossible d'ajouter le compte");
            });
        }

    }).then(result => {

        // si l'utilisateur annule ou il y a une erreur, rien ne se passe
        if (!result.isConfirmed || !result.value) return;

        Swal.fire("Succès", "Plan comptable ajouté avec succès !", "success");

        // Mise à jour du select Contre Partie
        if (result.value.data) {
            ajouterNouveauCompteDansSelect(result.value.data.compte, result.value.data.intitule);
        }
    });

}

function ouvrirPopupFournisseur(compteFournisseur, row, cell, tauxTVA) {

    const societeId = document.querySelector('meta[name="societe_id"]').getAttribute("content");

    Swal.fire({
        title: '<h5><i class="fas fa-plus-circle"></i> Créer Fournisseur</h5>',
        width: '900px',
        showCancelButton: true,
        confirmButtonText: 'Ajouter',
        focusConfirm: false,
        customClass: { popup: 'p-0' },

        html: `
<div class="container-fluid bg-light p-3" style="border-radius:8px;">
<form id="fournisseurFormAdd" autocomplete="off">

    <!-- Ligne 1 -->
    <div class="row mb-2">
        <div class="col-md-6">
            <label class="form-label">Compte</label>
            <input type="text" class="form-control form-control-sm shadow-sm" id="swal-compte">
        </div>

        <div class="col-md-6">
            <label class="form-label">Intitulé</label>
            <input type="text" class="form-control form-control-sm shadow-sm" id="swal-intitule" value="${compteFournisseur}">
        </div>
    </div>

    <!-- Ligne 2 -->
    <div class="row mb-2">
        <div class="col-md-6">
            <label class="form-label">Identifiant Fiscal</label>
            <input type="text" class="form-control form-control-sm shadow-sm" id="swal-identifiant" maxlength="8"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>

        <div class="col-md-6">
            <label class="form-label">ICE</label>
            <input type="text" class="form-control form-control-sm shadow-sm" id="swal-ICE" maxlength="15"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>
    </div>

    <!-- Ligne 3 -->
    <div class="row mb-2">
        <div class="col-md-6">
            <label class="form-label">RC</label>
            <input type="text" class="form-control form-control-sm shadow-sm" id="swal-RC">
        </div>
        <div class="col-md-6">
            <label class="form-label">Ville RC</label>
            <input type="text" class="form-control form-control-sm shadow-sm" id="swal-ville">
        </div>
    </div>

    <!-- Ligne 4 -->
    <div class="row mb-2">
        <div class="col-md-6">
            <label class="form-label">Adresse</label>
            <input type="text" class="form-control form-control-sm shadow-sm" id="swal-adresse">
        </div>
        <div class="col-md-6">
            <label class="form-label">Délai de paiement</label>
            <input type="text" class="form-control form-control-sm shadow-sm" id="swal-delai" value="60 jours">
        </div>
    </div>

    <!-- Rubrique TVA + Contre Partie -->
    <div class="row mb-2">
        <div class="col-md-6">
            <label class="form-label">Rubrique TVA</label>
            <div id="swal-rubrique-container">
                <div class="d-flex gap-2 mb-2 rubrique-tva-row">
                    <select id="swal-rubrique-1" class="form-select form-select-sm shadow-sm swal-rubrique">
                        <option value="">Sélectionner</option>
                    </select>
                    <button type="button" class="btn btn-outline-primary btn-sm addRubriqueTvaBtn">
                        <i class="fas fa-plus"></i></button>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">Contre Partie</label>
            <select id="swal-contre-partie" class="form-select form-select-sm shadow-sm">
                <option value="">Sélectionner</option>
                <option value="add_new">+ Ajouter un nouveau compte</option>
            </select>

            <small class="text-muted">
                <a href="#" id="ajouterCompteLink">+Ajouter</a>
            </small>
        </div>
    </div>

</form>
</div>
        `,

        /* ----------------------------------------------------------------------
           DID OPEN : ICI on ajoute l’event pour ouvrir le Plan Comptable
        ---------------------------------------------------------------------- */
        didOpen: () => {

            /** Auto génération compte */
            if (typeof genererCompteAutoForPopup === "function") genererCompteAutoForPopup();

            /** Rubrique TVA */
            if (typeof remplirRubriquesTva === "function") remplirRubriquesTva("swal-rubrique-1");

            /** Remplissage Contre Partie */
            if (typeof remplirContrePartie === "function") remplirContrePartie("swal-contre-partie");

            /** Activation Select2 */
            const dropdownParent = $('.swal2-container');
            $('#swal-rubrique-1').select2({ dropdownParent });
            $('#swal-contre-partie').select2({ dropdownParent });

            // ⚡⚡⚡ AJOUT DU CLIC POUR OUVRIR LE POPUP PLAN COMPTABLE
            document.getElementById("ajouterCompteLink").addEventListener("click", function (e) {
                e.preventDefault();
                openPlanComptablePopup();
            });

            /* Ajout rubrique TVA */
            $(document).on('click', '.addRubriqueTvaBtn', function () {

                const id = "swal-rubrique-" + Date.now();

                let newRow = `
                    <div class="d-flex gap-2 mb-2 rubrique-tva-row">
                        <select id="${id}" class="form-select form-select-sm shadow-sm swal-rubrique">
                            <option value="">Sélectionner</option>
                        </select>
                        <button type="button" class="btn btn-outline-danger btn-sm removeRubriqueTvaBtn">
                            <i class="fas fa-minus"></i></button>
                    </div>
                `;

                $('#swal-rubrique-container').append(newRow);

                remplirRubriquesTva(id);
                $("#" + id).select2({ dropdownParent });
            });

            /* Suppression rubrique TVA */
            $(document).on('click', '.removeRubriqueTvaBtn', function () {
                $(this).closest(".rubrique-tva-row").remove();
            });
        },

        /* ----------------------------------------------------------------------
           PRE CONFIRM : ON ENVOIE LES DONNÉES
        ---------------------------------------------------------------------- */
        preConfirm: () => {
            let rubriquesTVA = [];
            $('.swal-rubrique').each(function () {
                if ($(this).val()) rubriquesTVA.push($(this).val());
            });

            return {
                compte: $('#swal-compte').val(),
                intitule: $('#swal-intitule').val(),
                identifiant_fiscal: $('#swal-identifiant').val(),
                ICE: $('#swal-ICE').val(),
                RC: $('#swal-RC').val(),
                ville: $('#swal-ville').val(),
                adresse: $('#swal-adresse').val(),
                delai_p: $('#swal-delai').val(),
                rubrique_tva: rubriquesTVA.join(' / '),
                contre_partie: $('#swal-contre-partie').val(),
                societe_id: societeId
            };
        }

    }).then((result) => {

        if (!result.isConfirmed) return;

        fetch('/fournisseurs', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(result.value)
        })
        .then(r => r.json())
        .then(newFournisseur => {

            if (newFournisseur.error)
                return Swal.fire('Erreur', newFournisseur.error, 'error');

            const f = newFournisseur.fournisseur;

            /** Extraction du taux TVA */
            let newTauxTVA = 0;
            if (f.rubrique_tva) {
                let r = f.rubrique_tva.split(' / ')[0];
                let match = r.match(/([\d\.]+)%/);
                if (match) newTauxTVA = parseFloat(match[1]) / 100;
            }

            window.tauxTVAGlobal = newTauxTVA;

            const display = `${f.compte} - ${f.intitule}`;
            if (window.comptesFournisseurs) window.comptesFournisseurs.push(display);
            cell.setValue(display);

            const numeroFacture = row.getCell("numero_facture").getValue() || "Inconnu";

            row.update({
                contre_partie: f.contre_partie,
                rubrique_tva: f.rubrique_tva,
                taux_tva: newTauxTVA,
                libelle: `F° ${numeroFacture} ${f.intitule}`,
                compte_tva:
                    (window.comptesVentes && window.comptesVentes[0])
                        ? `${window.comptesVentes[0].compte} - ${window.comptesVentes[0].intitule}`
                        : ""
            });

            Swal.fire('Succès', 'Fournisseur ajouté', 'success')
            .then(() => {
                const creditCell = row.getCell("credit");
                if (creditCell) setTimeout(() => creditCell.edit(), 200);
            });

        })
        .catch(() => Swal.fire('Erreur', 'Impossible d’ajouter le fournisseur', 'error'));

    });
}






// ---------------------------
// fetchMergedContreParties (inchangé logiquement, garde la fusion normalisée)
// ---------------------------
async function fetchMergedContreParties(cell) {
  try {
    const societeId = $('#societe_id').val();
    if (!societeId) {
      console.warn("Aucune société sélectionnée");
      return [];
    }

    // récupère selectedCodeJournal au moment de l'appel
    const selectedCodeJournal = (function() {
      const sel = $('#journal-achats, #journal-ventes, #journal-operations-diverses').filter(':visible').first();
      return sel.length ? sel.val() : (typeof window.selectedCodeJournal !== "undefined" ? window.selectedCodeJournal : null);
    })();

    // 1) Fournisseurs
    let fournisseurData = [];
    try {
      const resp = await fetch(`/get-all-contre-parties?societe_id=${encodeURIComponent(societeId)}`);
      if (resp.ok) {
        const data = await resp.json();
        fournisseurData = (Array.isArray(data) ? data : []).map(item => {
          if (!item) return null;
          const cp = item.contre_partie ?? item.contrePartie ?? null;
          const compte = item.value ?? item.code ?? cp ?? "";
          const intitule = item.libelle ?? item.label ?? item.name ?? compte;
          return { 
            value: String(compte), 
            label: `${compte} - ${intitule}`, 
            contre_partie: cp ? String(cp) : null, 
            _source: 'fournisseur' 
          };
        }).filter(Boolean);
      } else console.warn("fetch fournisseur non ok", resp.status);
    } catch (e) { console.error("err fetch fournisseurs", e); }

    // 2) Code journal (prioritaire si existe)
    let codeJournalData = [];
    if (selectedCodeJournal) {
      try {
        const resp = await fetch(`/get-contre-parties?code_journal=${encodeURIComponent(selectedCodeJournal)}`);
        if (resp.ok) {
          const data = await resp.json();
          codeJournalData = (Array.isArray(data) ? data : []).map(item => {
            if (!item) return null;
            const cp = item.contre_partie ?? item.contrePartie ?? item.value ?? item.code ?? null;
            const compte = item.value ?? item.code ?? cp ?? "";
            const intitule = item.libelle ?? item.label ?? cp ?? compte;
            return { 
              value: String(compte), 
              label: `${compte} - ${intitule}`, 
              contre_partie: cp ? String(cp) : null, 
              _source: 'code_journal' 
            };
          }).filter(Boolean);
        } else console.warn("fetch codeJournal non ok", resp.status);
      } catch (e) { console.error("err fetch codeJournal", e); }
    }

    // 3) Plan comptable
    let planComptableData = [];
    try {
      const resp = await fetch(`/get-plan-comptable?societe_id=${encodeURIComponent(societeId)}`);
      if (resp.ok) {
        const data = await resp.json();

        // Préfixes autorisés
        const allowedPrefixes = ['21', '22', '23', '24', '25', '611', '612', '613', '614', '618', '631'];

        planComptableData = (Array.isArray(data) ? data : [])
          .map(item => {
            const compte = item.compte ?? null;
            const intitule = item.intitule ?? compte ?? "";
            if (!compte) return null;

            // Filtre sur les préfixes
            if (!allowedPrefixes.some(prefix => compte.startsWith(prefix))) return null;

            return { 
              value: String(compte), 
              label: `${compte} - ${intitule}`, 
              contre_partie: String(compte), 
              _source: 'plan' 
            };
          })
          .filter(Boolean);
      } else {
        console.warn("fetch planComptable non ok", resp.status);
      }
    } catch (error) {
      console.error("Erreur fetch planComptable:", error);
    }

    // Fusion (Map pour éviter doublons) : ordre insertion = codeJournal (si exist), fournisseur, plan
    const map = new Map();
    if (codeJournalData && codeJournalData.length) codeJournalData.forEach(it => map.set(it.value, it));
    if (fournisseurData && fournisseurData.length) fournisseurData.forEach(it => { if (!map.has(it.value)) map.set(it.value, it); });
    if (planComptableData && planComptableData.length) planComptableData.forEach(it => { if (!map.has(it.value)) map.set(it.value, it); });

    let merged = Array.from(map.values());

    // assure que la contre_partie du code_journal (si fournie) soit en tête
    if (selectedCodeJournal && codeJournalData.length) {
      const cpJournal = codeJournalData[0].contre_partie ?? codeJournalData[0].value;
      if (cpJournal) {
        merged = merged.filter(x => x.value !== String(cpJournal));
        merged.unshift({ value: String(cpJournal), label: `${cpJournal} - ${cpJournal}`, contre_partie: String(cpJournal), _source: 'code_journal_fallback' });
      }
    }

    console.log("fetchMergedContreParties -> merged:", merged);
    return merged;
  } catch (err) {
    console.error("fetchMergedContreParties error:", err);
    return [];
  }
}



// ---------------------------
// customListEditorPlanComptable (mis à jour : affichage liste + fallback cp + navigation auto)
// ---------------------------
function customListEditorPlanComptable(cell, onRendered, success, cancel, editorParams) {
    // container + input
    const container = document.createElement("div");
    container.className = "custom-list-editor-container";
    container.style.position = "relative";

    const input = document.createElement("input");
    input.type = "text";
    input.placeholder = "Rechercher...";
    input.style.width = "100%";
    input.style.boxSizing = "border-box";
    input.autocomplete = "off";
    input.value = cell.getValue() ?? "";
    container.appendChild(input);

    // dropdown (attach to body)
    const dropdown = document.createElement("div");
    dropdown.className = "custom-dropdown";
    Object.assign(dropdown.style, {
        position: "absolute",
        background: "#fff",
        border: "1px solid #ccc",
        maxHeight: "220px",
        overflowY: "auto",
        zIndex: "99999",
        display: "none",
        boxShadow: "0 2px 6px rgba(0,0,0,0.08)"
    });
    document.body.appendChild(dropdown);

    // state
    let options = []; // [{value,label,contre_partie,_source}]
    let filteredOptions = [];
    let highlightedIndex = -1;
    let validated = false;
    const storageKey = "tabulator_edit_focus";
    const triggerOnPrefill = editorParams && editorParams.triggerOnPrefill === true;
    const listOnEmpty = !!(editorParams && editorParams.listOnEmpty);

    // helpers
    function positionDropdown() {
        try {
            const rect = input.getBoundingClientRect();
            dropdown.style.top = (rect.bottom + window.scrollY) + "px";
            dropdown.style.left = (rect.left + window.scrollX) + "px";
            dropdown.style.width = rect.width + "px";
        } catch (e) { /* ignore */ }
    }
    function cleanupDropdown() {
        try {
            dropdown.style.display = "none";
            dropdown.innerHTML = "";
            window.removeEventListener('scroll', positionDropdown, true);
            window.removeEventListener('resize', positionDropdown);
            if (dropdown.parentNode) dropdown.parentNode.removeChild(dropdown);
        } catch (e) {}
    }

    // récupère la contre_partie par défaut du code_journal (si exist)
    function getJournalDefault(tableName) {
        try {
            let $sel;
            if (tableName === 'tableAch') $sel = $('#journal-achats');
            else if (tableName === 'tableVentes') $sel = $('#journal-ventes');
            else if (tableName === 'tableOP') $sel = $('#journal-operations-diverses');

            if (!$sel || $sel.length === 0) {
              $sel = $('#journal-achats:visible, #journal-ventes:visible, #journal-operations-diverses:visible').first();
            }

            if ($sel && $sel.length) {
                const $opt = $sel.find('option:selected');
                if ($opt && $opt.length) {
                    const val = ($opt.data('contre_partie') ?? $opt.data('contrePartie') ?? $opt.attr('data-contre_partie') ?? $opt.attr('data-contre-partie')) || null;
                    return val ? String(val).trim() : null;
                }
            }
        } catch (e) { /* ignore */ }
        return null;
    }

    // navigation automatique : next/prev editable via NavigationUtils if present, fallback to openNextAfterEditorClosed
    function navigateToAdjacent(row, direction) {
        try {
            // prefer NavigationUtils if available
            if (window.NavigationUtils && typeof window.NavigationUtils.findAdjacentEditableField === "function" && typeof window.NavigationUtils.navigateAfterCommitRobust === "function") {
                // use navigateAfterCommitRobust to scroll and edit
                window.NavigationUtils.navigateAfterCommitRobust(row, direction);
                return;
            }
        } catch (e) { /* ignore */ }

        // fallback: try user-provided openNextAfterEditorClosed (keeps compatibility)
        try {
            if (direction === "next") openNextAfterEditorClosed(cell, "piece_justificative");
            else if (direction === "prev") {
                // best-effort: try previous column (not guaranteed)
                try {
                    const table = cell.getTable ? cell.getTable() : row.getTable();
                    if (table) {
                        const curField = cell.getField();
                        const prevField = (window.NavigationUtils && typeof window.NavigationUtils.findAdjacentEditableField === "function")
                          ? window.NavigationUtils.findAdjacentEditableField(table, curField, 'prev')
                          : null;
                        if (prevField) {
                            // open prev
                            const prevCell = row.getCell(prevField);
                            if (prevCell && typeof prevCell.edit === "function") prevCell.edit(true);
                        }
                    }
                } catch (err) { /* ignore */ }
            }
        } catch (e) { /* ignore */ }
    }

    // render dropdown
    function renderDropdown(list, highlightFirst = false) {
        dropdown.innerHTML = "";
        highlightedIndex = -1;
        if (!list || list.length === 0) {
            const item = document.createElement("div");
            item.style.padding = "8px";
            item.style.borderBottom = "1px solid #eee";
            item.textContent = "Compte non trouvé";
            dropdown.appendChild(item);
            return;
        }

        list.forEach((opt, idx) => {
            const item = document.createElement("div");
            item.textContent = opt.label;
            item.dataset.idx = idx;
            item.style.padding = "6px 8px";
            item.style.cursor = "pointer";
            item.style.borderBottom = "1px solid #f0f0f0";

            const inputVal = (input.value ?? "").toString();
            const shouldHighlight =
                (opt.value === inputVal) ||
                (opt.label === inputVal) ||
                (opt.contre_partie && opt.contre_partie === inputVal) ||
                (highlightFirst && idx === 0 && !inputVal);

            if (shouldHighlight) {
                item.classList.add("highlight");
                highlightedIndex = idx;
                item.style.background = "#f7fbff";
            }

            item.addEventListener("mousedown", function(e) {
                e.preventDefault();
                validated = true;
                localStorage.removeItem(storageKey);
                const chosen = opt;
                input.value = chosen.label;
                dropdown.style.display = "none";

                // commit chosen value
                try { success(chosen.value); } catch (err) { console.warn(err); }

                // assign contre_partie: chosen.contre_partie OR fallback to journalDefault
                try {
                    const row = cell.getRow();
                    const journalDefault = getJournalDefault(editorParams && editorParams.tableName ? editorParams.tableName : null);
                    const cpVal = chosen.contre_partie ?? (journalDefault ? journalDefault : null);
                    if (cpVal !== null) row.update({ contre_partie: cpVal });
                    console.log("Assign cp from selection:", cpVal, " (source:", chosen._source, ")");
                } catch (e) { console.warn("update contre_partie failed", e); }

                // navigate next
                try { navigateToAdjacent(cell.getRow(), "next"); } catch(e){/*ignore*/}
            });

            dropdown.appendChild(item);
        });

        positionDropdown();
        dropdown.style.display = "block";
    }

    function updateDropdown(defaultValue = null) {
        const search = (defaultValue ?? input.value ?? "").toLowerCase();
        if ((!search || search.trim() === "") && listOnEmpty) {
            filteredOptions = options.slice();
        } else {
            filteredOptions = options.filter(o =>
                (o.label || "").toLowerCase().includes(search) ||
                (o.value || "").toLowerCase().includes(search) ||
                (o.contre_partie || "").toLowerCase().includes(search)
            );
        }
        renderDropdown(filteredOptions, !!defaultValue);
    }

    // keyboard nav
    function moveHighlight(delta) {
        const items = Array.from(dropdown.querySelectorAll('div[data-idx]'));
        if (items.length === 0) return;
        let idx = highlightedIndex;
        if (idx === -1) idx = (delta > 0 ? -1 : items.length);
        idx = (idx + delta + items.length) % items.length;
        items.forEach(it => { it.classList.remove('highlight'); it.style.background = ''; });
        const it = items[idx];
        if (it) {
            it.classList.add('highlight');
            it.style.background = '#f7fbff';
            highlightedIndex = parseInt(it.dataset.idx, 10);
            it.scrollIntoView({ block: 'nearest' });
        }
    }

    // commit flow
    function commitSelected(direction = "next") {
        if (filteredOptions && filteredOptions.length > 0 && highlightedIndex >= 0 && highlightedIndex < filteredOptions.length) {
            const chosen = filteredOptions[highlightedIndex];
            validated = true;
            localStorage.removeItem(storageKey);
            input.value = chosen.label;
            dropdown.style.display = "none";
            try { success(chosen.value); } catch (err) {}

            try {
                const row = cell.getRow();
                const journalDefault = getJournalDefault(editorParams && editorParams.tableName ? editorParams.tableName : null);
                const cpVal = chosen.contre_partie ?? (journalDefault ? journalDefault : null);
                if (cpVal !== null) row.update({ contre_partie: cpVal });
                console.log("Assign cp from commitSelected:", cpVal, " (source:", chosen._source, ")");
            } catch (e) {}

            try { navigateToAdjacent(cell.getRow(), direction); } catch (e) {}
            return;
        }

        // free text fallback
        validated = true;
        localStorage.removeItem(storageKey);
        const v = input.value;
        dropdown.style.display = "none";
        try { success(v); } catch (err) {}
        try {
            const row = cell.getRow();
            const journalDefault = getJournalDefault(editorParams && editorParams.tableName ? editorParams.tableName : null);
            if (journalDefault) row.update({ contre_partie: journalDefault });
            console.log("Assign cp from free-text (journal default):", journalDefault);
        } catch (e) {}
        try { navigateToAdjacent(cell.getRow(), direction); } catch (e) {}
    }

    // input handlers (Enter/Tab/Arrows)
    input.addEventListener("keydown", function(e) {
        const dropdownVisible = (dropdown && dropdown.style && dropdown.style.display === "block" && filteredOptions && filteredOptions.length > 0);

        if (e.key === "Enter") {
            e.preventDefault();
            commitSelected("next");
        } else if (e.key === "Tab") {
            e.preventDefault();
            commitSelected("next");
        } else if (e.key === "Escape") {
            e.preventDefault();
            try { cancel(); } catch (err) {}
        } else if (e.key === "ArrowDown") {
            e.preventDefault();
            if (dropdownVisible) moveHighlight(1);
            else updateDropdown();
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            if (dropdownVisible) moveHighlight(-1);
            else updateDropdown();
        } else if (e.key === "ArrowRight") {
            const cursorAtEnd = typeof input.selectionStart === "number" && input.selectionStart === (input.value ?? "").length;
            if (!dropdownVisible || cursorAtEnd) {
                e.preventDefault();
                commitSelected("next");
            }
        } else if (e.key === "ArrowLeft") {
            const cursorAtStart = typeof input.selectionStart === "number" && input.selectionStart === 0;
            if (!dropdownVisible || cursorAtStart) {
                e.preventDefault();
                commitSelected("prev");
            }
        }
    });

    input.addEventListener("blur", function(e) {
        if (!validated) {
            // keep focus so user doesn't accidentally close editor
            setTimeout(function() { input.focus(); }, 10);
        } else {
            cleanupDropdown();
        }
    });

    input.addEventListener("input", function() { updateDropdown(); });
    input.addEventListener("focus", function() { updateDropdown(); });

    window.addEventListener('scroll', positionDropdown, true);
    window.addEventListener('resize', positionDropdown);

    // onRendered: load options & prefill logic
    onRendered(async function() {
        input.focus();
        try { localStorage.setItem(storageKey, JSON.stringify({ rowIndex: cell.getRow().getPosition(), field: cell.getField() })); } catch (e) {}

        const lookupFn = (editorParams && typeof editorParams.valuesLookup === "function") ? editorParams.valuesLookup : fetchMergedContreParties;
        try {
            const result = await lookupFn(cell);
            // normalize array<string> or array<object>
            options = (Array.isArray(result) ? result : []).map(item => {
                if (!item) return null;
                if (typeof item === "string") return { value: item, label: item, contre_partie: item, _source: 'string' };
                return {
                    value: String(item.value ?? item.code ?? item.id ?? item.label ?? ""),
                    label: String(item.label ?? item.libelle ?? item.name ?? item.value ?? ""),
                    contre_partie: item.contre_partie ?? item.contrePartie ?? null,
                    _source: item._source ?? null
                };
            }).filter(Boolean);
        } catch (err) {
            console.error("valuesLookup error:", err);
            options = [];
        }

        // journalDefault (contre_partie du code_journal)
        const journalDefault = getJournalDefault(editorParams && editorParams.tableName ? editorParams.tableName : null);

        // ensure journalDefault present in options (at top) to allow preselection / fallback
        if (journalDefault) {
            const exists = options.some(o => o.value === journalDefault || o.label === journalDefault || o.contre_partie === journalDefault);
            if (!exists) options.unshift({ value: String(journalDefault), label: String(journalDefault), contre_partie: String(journalDefault), _source: 'code_journal_injected' });
        }

        // ALWAYS display the merged list (even if input empty) when listOnEmpty === true
        // prefill logic : if cell empty => prefill with journalDefault (priority) OR first option if listOnEmpty
        const curVal = (cell.getValue() ?? "").toString().trim();
        if (!curVal) {
            if (journalDefault) {
                input.value = journalDefault;
                try { cell.setValue(journalDefault, !!triggerOnPrefill); } catch (e) {}
                try { cell.getRow().update({ contre_partie: journalDefault }); } catch (e) {}
                // mark highlight on matching option
                updateDropdown(journalDefault);
                setTimeout(() => {
                    const idxInFiltered = filteredOptions.findIndex(o => o.contre_partie === journalDefault || o.value === journalDefault || o.label === journalDefault);
                    if (idxInFiltered !== -1) {
                        highlightedIndex = idxInFiltered;
                        const el = dropdown.querySelector('div[data-idx="' + idxInFiltered + '"]');
                        if (el) {
                            Array.from(dropdown.querySelectorAll('div[data-idx]')).forEach(it=>{ it.classList.remove('highlight'); it.style.background = ''; });
                            el.classList.add('highlight'); el.style.background = '#f7fbff';
                            el.scrollIntoView({ block: 'nearest' });
                        }
                    }
                }, 20);
            } else {
                // no journalDefault -> show merged list (listOnEmpty must be true to show all)
                if (options.length > 0 && listOnEmpty) {
                    updateDropdown(null);
                } else {
                    updateDropdown(null);
                }
            }
        } else {
            // ensure current value shown in list
            if (!options.some(o => o.value === curVal || o.label === curVal)) {
                options.unshift({ value: curVal, label: curVal, contre_partie: null, _source: 'currentValue' });
            }
            updateDropdown(null);
        }
    });

    // cleanup observer removal
    try {
        const editorEl = cell.getElement && cell.getElement();
        if (editorEl) {
            const mo = new MutationObserver(function(mutations) {
                mutations.forEach(function(m) {
                    m.removedNodes && m.removedNodes.forEach(function(node) {
                        if (node === editorEl) {
                            cleanupDropdown();
                            mo.disconnect();
                        }
                    });
                });
            });
            if (editorEl.parentNode) mo.observe(editorEl.parentNode, { childList: true });
        }
    } catch (e) { /* ignore */ }

    return container;
}


// ---------------------------
// Exemple d'utilisation dans la définition de colonne
// ---------------------------
var contrePartieColumn = {
    title: "Contre-Partie",
    field: "contre_partie",
    headerFilter: "input",
    headerFilterParams: { elementAttributes: { style: "width:85px; height:25px;" } },

    editor: function(cell, onRendered, success, cancel) {
        // Création d'un <select> pour la liste déroulante
        const select = document.createElement("select");
        select.style.width = "100%";
        select.style.height = "25px";

        // Fonction pour remplir les options, peut être async
        function populateOptions(values) {
            select.innerHTML = ""; // Vide avant de remplir
            values.forEach(val => {
                const option = document.createElement("option");
                if (typeof val === "object") {
                    option.value = val.value;
                    option.textContent = val.label;
                } else {
                    option.value = val;
                    option.textContent = val;
                }
                select.appendChild(option);
            });
            select.value = cell.getValue() || "";
        }

        // Support pour valeurs asynchrones
        const values = fetchMergedContreParties(); // peut retourner Promise ou tableau
        if (values instanceof Promise) {
            values.then(res => populateOptions(res));
        } else {
            populateOptions(values);
        }

        // Gestion des événements
        onRendered(() => {
            select.focus();
            select.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    success(select.value);
                    moveFocus(cell); // Déplacement automatique du focus
                }
                if (e.key === "Escape") {
                    cancel();
                }
            });
        });

        // Valide la sélection au changement et déplace le focus
        select.addEventListener("change", () => {
            success(select.value);
            moveFocus(cell);
        });

        // Fonction pour déplacer le focus après validation
        function moveFocus(cell) {
            const rowEl = cell.getRow().getElement();
            if (!rowEl) return;

            // Essayer le champ "Prorat de déduction"
            const proratInput = rowEl.querySelector('input[placeholder="Rechercher..."]');
            const proratCell = cell.getTable().getColumns().find(col => col.getField() === "prorat_de_deduction");

            if (proratCell && proratCell.getDefinition().visible && proratInput) {
                proratInput.focus();
            } else {
                // Sinon, focus sur "Pièce justificative"
                const pieceInput = rowEl.querySelector('.pj-input');
                if (pieceInput) pieceInput.focus();
            }
        }

        return select;
    },

    editorParams: {
        tableName: 'tableAch',
        listOnEmpty: true,
        triggerOnPrefill: false,
        valuesLookup: fetchMergedContreParties
    },

    cellEdited: function(cell) {
        console.log("Contre-Partie mise à jour:", cell.getValue());
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

    // Option par défaut : valeur vide
    periodeSelect.append('<option value="">Sélectionner un mois</option>');

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

    // restore previous selection si existant, sinon garder la valeur vide (default)
    if (previousSelection) {
        periodeSelect.val(previousSelection);
    } else {
        periodeSelect.val('');
    }
}


    // Fonction pour mettre à jour la date dans toutes les tables Tabulator
   function updateTabulatorDate(year, month) {
    const formattedDate = `${year}-${month.toString().padStart(2,'0')}-01`;

    [tableAch, tableVentes, tableOP].forEach(function (table) {
        if (!table) return;
        try {
            table.getRows().forEach(r => {
                r.update({ date: formattedDate });
            });
        } catch (e) {
            console.warn("updateTabulatorDate failed for a table:", e);
        }
    });
}


   window.addEventListener("focus", () => {
    const key = "tabulator_current_edit";
    const saved = localStorage.getItem(key);
    if (!saved) return;
    try {
        const { rowIndex, field } = JSON.parse(saved);
        if (typeof rowIndex !== 'number' || !field) return;
        if (!window.tableAch || typeof window.tableAch.getRows !== 'function') return;
        const rows = window.tableAch.getRows();
        if (!rows || rows.length <= rowIndex) return;
        const row = rows[rowIndex];
        if (!row) return;
        const cell = row.getCell(field);
        if (cell && typeof cell.edit === 'function') {
            setTimeout(() => cell.edit(), 100);
        }
    } catch (e) {
        console.warn("Cannot restore edit focus:", e);
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
  const onglets = ['achats', 'ventes', 'operations-diverses'];
  onglets.forEach(onglet => {
    $(`input[name="filter-period-${onglet}"]`).on('change', function () {
      if ($(this).val() === 'mois') {
        $(`#periode-${onglet}`).show();
        $(`#annee-${onglet}`).hide();
      } else if ($(this).val() === 'exercice') {
        $(`#periode-${onglet}`).hide();
        $(`#annee-${onglet}`).show();
      }
    });
  });
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

var tableAch, tableVentes, tableOP;

$(document).ready(function () {
function updateTabulatorDataAchats() {
  const mois            = document.getElementById("periode-achats").value;
  const annee           = document.getElementById("annee-achats").value;
  const codeJournal     = document.getElementById("journal-achats").value;
  const filtreExercice  = document.getElementById("filter-exercice-achats").checked;

  // --- Alerte douce avec SweetAlert2 ---
  const showSwal = (message) => {
    Swal.fire({
      icon: 'info',
      title: 'Filtrage Achats',
      text: message,
      timer: 2800,
      showConfirmButton: false,
      toast: true,
      position: 'top-end',
      background: '#f0f8ff',
      color: '#004085'
    });
  };

  // --- Conditions ---
  if (!codeJournal || codeJournal === "" || codeJournal === "selectionner") {
    // showSwal("Veuillez sélectionner un journal .");
    if (typeof tableAch !== "undefined" && tableAch) tableAch.clearData();
    return;
  }

  if ((!mois || mois === "selectionner un mois") && !filtreExercice) {
    // showSwal("Veuillez sélectionner un mois ou cocher « Exercice entier » ");
    if (typeof tableAch !== "undefined" && tableAch) tableAch.clearData();
    return;
  }

  if (!annee || isNaN(annee)) {
    showSwal("Veuillez saisir une année valide.");
    if (typeof tableAch !== "undefined" && tableAch) tableAch.clearData();
    return;
  }

  // --- Construction des paramètres ---
  let dataToSend = { categorie: "Achats" };

  if (filtreExercice && annee) {
    dataToSend.annee = annee;
    if (codeJournal) dataToSend.code_journal = codeJournal;
  } else {
    if (mois && annee) dataToSend.mois = mois, dataToSend.annee = annee;
    if (codeJournal) dataToSend.code_journal = codeJournal;
  }

  console.log("📤 Filtrage Achats appliqué :", dataToSend);

  // --- Fetch ---
  fetch("/get-operations", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify(dataToSend),
  })
  .then(response => response.json())
  .then(payload => {
    const data = Array.isArray(payload)
      ? payload
      : (payload && Array.isArray(payload.data) ? payload.data : []);
// console.log(data);
    if (typeof tableAch === 'undefined' || !tableAch) {
      console.warn("⚠️ tableAch non initialisée.");
      return;
    }

    tableAch.replaceData(data).then(async () => {
    try { applyDefaultContrePartieToTable(tableAch, codeJournal); } catch {}
    try { tableAch.redraw(true); } catch {}
    try { calculerSoldeCumule(); } catch {}

    if (data.length === 0) {
        // Récupère la contre-partie correspondant au codeJournal
        const contrePartieDefault = await getContrePartieByCodeJournal(codeJournal);

        tableAch.addRow({
            id: null,
            compte: '',
            contre_partie: contrePartieDefault,  // <-- valeur par défaut
            debit: 0,
            credit: 0,
            piece_justificative: '',
            type_journal: codeJournal,
            value: ''
        });
    }
  });

  })
  .catch(error => {
    console.error("Erreur lors de la mise à jour Achats :", error);
  });
}
async function getContrePartieByCodeJournal(codeJournal) {
    try {
        const response = await fetch('/api/journauxACH');
        if (!response.ok) throw new Error('Erreur API Journaux');
        const journaux = await response.json();

        const journal = journaux.find(j => j.code_journal === codeJournal);
        return journal ? journal.contre_partie : '';
    } catch (err) {
        console.error(err);
        return '';
    }
}

    // =========================
    // UTILITAIRES
    // =========================
    function showError(msg) {
        if (window.Swal) {
            Swal.fire({ icon: 'warning', title: 'Attention', text: msg, confirmButtonText: 'OK' });
        } else {
            alert(msg);
        }
    }

    // Normalize value check
    function hasValue(val) {
        return val !== undefined && val !== null && val !== '' && val !== '0';
    }

    // =========================
    // LOAD JOURNAUX (inchangé)
    // =========================
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
      // $(selectId).append('<option value="">Sélectionner un journal</option>');

      data.forEach(function (journal) {
        // sécuriser les valeurs pour éviter les undefined
        const code = journal.code_journal || '';
        const type  = journal.type_journal || '';
        const intit = journal.intitule || '';
        const rub   = journal.rubrique_tva || '';
        const cp    = journal.contre_partie || journal.contrePartie || '';
// console.log('a ' + rub);
        // utilisation de template literal pour plus de lisibilité
        const optionHtml = `<option value="${code}"
                                  data-type="${type}"
                                  data-intitule="${intit}"
                                  data-rubrique_tva="${rub}"
                                  data-contre_partie="${cp}">
                              ${code}
                            </option>`;

        $(selectId).append(optionHtml);
      });
    },
    error: function (err) {
      console.error('Erreur lors du chargement des journaux', err);
    },
  });
}


    // Charger les journaux initiaux
    loadJournaux('achats', '#journal-achats');
    loadJournaux('ventes', '#journal-ventes');
    loadJournaux('operations-diverses', '#journal-operations-diverses');

    // =========================
    // CHECKS SPÉCIFIQUES PAR ONGLET / TYPE
    // =========================
    function checkJournalAchats() {
        if (!hasValue($('#journal-achats').val())) {
            showError('Veuillez renseigner le code journal pour les ACHATS avant de continuer.');
            $('#journal-achats').focus();
            return false;
        }
        return true;
    }

    function checkJournalVentes() {
        if (!hasValue($('#journal-ventes').val())) {
            showError('Veuillez renseigner le code journal pour les VENTES avant de continuer.');
            $('#journal-ventes').focus();
            return false;
        }
        return true;
    }

    function checkJournalOperationsDiverses() {
        if (!hasValue($('#journal-operations-diverses').val())) {
            showError('Veuillez renseigner le code journal pour les OPERATIONS DIVERSES avant de continuer.');
            $('#journal-operations-diverses').focus();
            return false;
        }
        return true;
    }

    // =========================
    // Gestion des changements sur les selects de journal (remplit le champ intitule associé)
    // =========================
    $('#journal-achats, #journal-ventes, #journal-operations-diverses').on('change', function () {
        const selectedOption = $(this).find(':selected');
        const intituleJournal = selectedOption.data('intitule');
        const tabId = $(this).attr('id').replace('journal-', 'filter-intitule-');
        $('#' + tabId).val(intituleJournal ? 'journal - ' + intituleJournal : '');
    });

    // =========================
    // Bindings scoping : empêcher actions/changes si journal du contexte non renseigné
    // =========================
    // Adapt these container selectors to match ton HTML (ex: '#tab-achats', '.panel-ventes', etc.)
    const ACHATS_CONTAINER = '#tab-achats';
    const VENTES_CONTAINER = '#tab-ventes';
    const OPS_CONTAINER    = '#tab-operations-diverses';

    // Empêcher changements d'inputs/selects dans l'onglet Achats si pas de journal Achats
    $(document).on('change', ACHATS_CONTAINER + ' input, ' + ACHATS_CONTAINER + ' select', function (e) {
        // Autoriser uniquement le changement du select journal lui-même
        if ($(this).is('#journal-achats')) return;
        if (!checkJournalAchats()) {
            e.preventDefault();
            return false;
        }
    });

    // Idem pour Ventes
    $(document).on('change', VENTES_CONTAINER + ' input, ' + VENTES_CONTAINER + ' select', function (e) {
        if ($(this).is('#journal-ventes')) return;
        if (!checkJournalVentes()) {
            e.preventDefault();
            return false;
        }
    });

    // Idem pour Opérations Diverses
    $(document).on('change', OPS_CONTAINER + ' input, ' + OPS_CONTAINER + ' select', function (e) {
        if ($(this).is('#journal-operations-diverses')) return;
        if (!checkJournalOperationsDiverses()) {
            e.preventDefault();
            return false;
        }
    });

    // =========================
    // Tabulator : gestion séparée par table
    // =========================
    // Manager simple pour appliquer la validation lors de cellEditing
    function applyTabulatorJournalGuard(tableInstance, checkFn) {
        if (!tableInstance) return;
        tableInstance.on('cellEditing', function (cell) {
            // si check échoue, annule l'édition
            if (!checkFn()) {
                cell.cancelEdit();
            }
        });
    }


    // Appliquer le guard par table
    applyTabulatorJournalGuard(tableAch, checkJournalAchats);
    applyTabulatorJournalGuard(tableVentes, checkJournalVentes);
    applyTabulatorJournalGuard(tableOP,  checkJournalOperationsDiverses);

    // =========================
    // Validation à la soumission de formulaires par onglet (si tu as des forms)
    // =========================
    $('#form-achats').on('submit', function (e) {
        if (!checkJournalAchats()) {
            e.preventDefault();
            return false;
        }
    });
    $('#form-ventes').on('submit', function (e) {
        if (!checkJournalVentes()) {
            e.preventDefault();
            return false;
        }
    });
    $('#form-operations-diverses').on('submit', function (e) {
        if (!checkJournalOperationsDiverses()) {
            e.preventDefault();
            return false;
        }
    });


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


                // Indicateur de mode exercice entier
let modeExerciceComplet = false;

// Ecoute du radio 'Exercice entier'
document.getElementById("filter-exercice-achats").addEventListener("click", () => {
    modeExerciceComplet = true;
});

// Désactivation du mode pour autres radios
document.querySelectorAll("input[name='filter-period-achats']").forEach(input => {
    if (input.id !== "filter-exercice-achats") {
        input.addEventListener("click", () => {
            modeExerciceComplet = false;
        });
    }
});
document.addEventListener('change', function (e) {
  if (e.target && e.target.id === 'master-select') {
    const checked = e.target.checked;
    if (checked) {
      tableAch.selectRow();
    } else {
      tableAch.deselectRow();
    }
  }
});

// ...existing code transfert journaux ...
(function(){
  /* ---------- Helpers ---------- */
  function q(id) { return document.getElementById(id); }
  function getCSRF() {
    const m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }
  function uniqArray(arr) {
    return Array.from(new Set(arr));
  }
  function sleep(ms){ return new Promise(r=>setTimeout(r,ms)); }

  /* ---------- Small UI highlight helper (scroll + flash) ---------- */
  function highlightElementOnce(el){
    if(!el) return;
    try{
      el.scrollIntoView({behavior:'smooth', block:'center'});
      el.classList.add('transfer-highlight');
      setTimeout(()=>el.classList.remove('transfer-highlight'), 1800);
    }catch(e){}
  }

  /* ---------- Map buttons / modals / selects ---------- */
  const config = {
    achats: {
      btnId: 'transfer-achats-btn',
      modalId: 'transferJournalModal-ach',
      selectId: 'transfer-target-ach',
      cancelId: 'transfer-cancel-ach',
      confirmId: 'transfer-confirm-ach',
      feedbackId: 'transfer-feedback-ach',
      endpoint: '/operation-courante/transfer-journal-ach',
      tableVar: () => window.tableAch || (typeof tableAch !== 'undefined' ? tableAch : null),
      journalSelect: '#journal-achats',
      tableContainerSelector: '#table-achats-container'
    },
    ventes: {
      btnId: 'transfer-ventes-btn',
      modalId: 'transferJournalModal-vte',
      selectId: 'transfer-target-vte',
      cancelId: 'transfer-cancel-vte',
      confirmId: 'transfer-confirm-vte',
      feedbackId: 'transfer-feedback-vte',
      endpoint: '/operation-courante/transfer-journal-vte',
      tableVar: () => window.tableVentes || (typeof tableVentes !== 'undefined' ? tableVentes : null),
      journalSelect: '#journal-ventes',
      tableContainerSelector: '#table-ventes-container'
    },
    operations: {
      btnId: 'transfer-operations-btn',
      modalId: 'transferJournalModal-op',
      selectId: 'transfer-target-op',
      cancelId: 'transfer-cancel-op',
      confirmId: 'transfer-confirm-op',
      feedbackId: 'transfer-feedback-op',
      endpoint: '/operation-courante/transfer-journal-op',
      tableVar: () => window.tableOP || (typeof tableOP !== 'undefined' ? tableOP : (window.tableOP || null)),
      journalSelect: '#journal-operations-diverses',
      tableContainerSelector: '#table-operations-container'
    }
  };

  /* ---------- piece_justificative generator ----------
     Format used: P + MM + YY + CODE + 4-digit-zero-padded-increment
     This function scans the table data for the target code and returns next piece.
  */
  function generateNextPieceForTable(table, codeJournal, dateHint){
    // determine MMYY from dateHint or today
    let date = dateHint ? new Date(dateHint) : new Date();
    if (isNaN(date.getTime())) date = new Date();
    const mm = String(date.getMonth() + 1).padStart(2,'0');
    const yy = String(date.getFullYear()).slice(-2);
    const prefix = `P${mm}${yy}${String(codeJournal)}`;

    let max = 0;
    try {
      const all = (typeof table.getData === 'function') ? (table.getData()||[]) : (Array.isArray(table) ? table : []);
      all.forEach(d => {
        const p = String(d.piece_justificative ?? d.piece ?? '');
        if (!p) return;
        if (p.indexOf(prefix) === 0) {
          const suffix = p.slice(prefix.length);
          const n = parseInt(suffix, 10);
          if (!isNaN(n) && n > max) max = n;
        }
      });
    } catch(e){
      console.warn('generateNextPieceForTable error', e);
    }

    const next = max + 1;
    const suf = String(next).padStart(4, '0');
    return prefix + suf;
  }

  /* ---------- Populate select with options from the corresponding global journal select,
       excluding the code_journal(s) of the selected rows ---------- */
  async function populateTargetSelectFor(key) {
    const c = config[key];
    const sel = q(c.selectId);
    const feedback = q(c.feedbackId);
    if (!sel) return;
    sel.innerHTML = '';
    if (feedback) feedback.style.display = 'none';

    const table = c.tableVar();

    // 1) collect codes of selected rows (to exclude)
    let selectedCurrentCodes = [];
    try {
      if (table && typeof table.getSelectedRows === 'function') {
        const selRows = table.getSelectedRows();
        if (selRows && selRows.length) {
          selRows.forEach(r => {
            try {
              const d = (typeof r.getData === 'function') ? r.getData() : r;
              const code = String(d.code_journal ?? d.type_journal ?? d.journal ?? '').trim();
              if (code) selectedCurrentCodes.push(code);
            } catch(e){}
          });
        }
      }
      if (selectedCurrentCodes.length === 0 && table && typeof table.getSelectedData === 'function') {
        const sd = table.getSelectedData();
        (sd||[]).forEach(d => {
          const code = String(d.code_journal ?? d.type_journal ?? d.journal ?? '').trim();
          if (code) selectedCurrentCodes.push(code);
        });
      }
    } catch (e) {
      console.warn('populateTargetSelectFor: error reading selected rows', e);
      selectedCurrentCodes = [];
    }
    selectedCurrentCodes = uniqArray(selectedCurrentCodes);

    // 2) Collect options from the dedicated global journal select for this key
    const journalSelector = c.journalSelect;
    const journalElement = journalSelector ? document.querySelector(journalSelector) : null;
    const options = [];

    if (journalElement) {
      Array.from(journalElement.options || []).forEach(o => {
        const v = String(o.value ?? '').trim();
        if (!v) return;
        const text = (o.textContent || o.innerText || v).trim();
        options.push({ value: v, text });
      });
    } else {
      // fallback: if no global select found, attempt to build from table data (less preferred)
      if (table) {
        try {
          if (typeof table.getData === 'function') {
            const all = table.getData() || [];
            all.forEach(d => {
              const v = String(d.code_journal ?? d.type_journal ?? d.journal ?? '').trim();
              if (v) options.push({ value: v, text: v });
            });
          } else if (typeof table.getRows === 'function') {
            const rows = table.getRows();
            rows.forEach(r => {
              try {
                const d = r.getData ? r.getData() : r;
                const v = String(d.code_journal ?? d.type_journal ?? d.journal ?? '').trim();
                if (v) options.push({ value: v, text: v });
              } catch(e){}
            });
          }
        } catch (e) {
          console.warn('populateTargetSelectFor: fallback read table failed', e);
        }
      }
    }

    // dedupe options by value and sort
    const map = new Map();
    options.forEach(o => { if (!map.has(o.value)) map.set(o.value, o.text); });
    let codes = Array.from(map.keys()).sort();

    // 3) exclude selectedCurrentCodes
    let filtered = codes.filter(code => !selectedCurrentCodes.includes(code));

    // 4) build options in select
    if (!filtered.length) {
      const msg = selectedCurrentCodes.length ? 'Aucun journal disponible (hors journaux sélectionnés)' : 'Aucun journal disponible';
      const opt = document.createElement('option'); opt.value = ''; opt.textContent = msg; sel.appendChild(opt);
      return;
    }

    const empty = document.createElement('option'); empty.value = ''; empty.textContent = '-- Choisir un journal cible --'; sel.appendChild(empty);
    filtered.forEach(code => {
      const o = document.createElement('option'); o.value = code; o.textContent = map.get(code) || code; sel.appendChild(o);
    });
  }

  /* ---------- Show / Hide modal helpers ---------- */
  function showModal(key) {
    const m = q(config[key].modalId);
    if (!m) return;
    m.style.display = 'block';
    m.classList.add('open');
    populateTargetSelectFor(key);
  }
  function hideModal(key) {
    const m = q(config[key].modalId);
    if (!m) return;
    m.style.display = 'none';
    m.classList.remove('open');
    const fb = q(config[key].feedbackId);
    if (fb) { fb.style.display = 'none'; fb.textContent = ''; }
  }

  /* ---------- Transfer function for a given key ---------- */
  async function transferForKey(key) {
    const c = config[key];
    const sel = q(c.selectId);
    const feedback = q(c.feedbackId);
    const table = c.tableVar();
    if (!sel) return;
    const to = sel.value;
    if (!to) {
      if (feedback) { feedback.style.display = 'block'; feedback.style.color = '#c00'; feedback.textContent = 'Choisissez un journal cible.'; }
      return;
    }
    if (!table) {
      if (feedback) { feedback.style.display = 'block'; feedback.style.color = '#c00'; feedback.textContent = 'Table non initialisée.'; }
      return;
    }

    // collect selected rows (only transfer selected rows)
    let selectedRows = [];
    try {
      if (typeof table.getSelectedRows === 'function') {
        selectedRows = table.getSelectedRows();
      } else if (typeof table.getSelectedData === 'function') {
        const data = table.getSelectedData();
        selectedRows = (data||[]).map(d => ({ getData: () => d, update: ()=>{} }));
      } else {
        selectedRows = [];
      }
    } catch (e) { selectedRows = []; }

    if (!selectedRows || selectedRows.length === 0) {
      if (feedback) { feedback.style.display = 'block'; feedback.style.color = '#666'; feedback.textContent = 'Aucune ligne sélectionnée.'; }
      return;
    }

    // Ensure 'to' is not equal to the journal of the selected rows (safety check)
    const selectedCurrentCodes = uniqArray(selectedRows.map(r => {
      try { const d = (typeof r.getData === 'function') ? r.getData() : r; return String(d.code_journal ?? d.type_journal ?? d.journal ?? '').trim(); } catch(e){ return ''; }
    }).filter(Boolean));
    if (selectedCurrentCodes.includes(String(to))) {
      if (feedback) { feedback.style.display = 'block'; feedback.style.color = '#c00'; feedback.textContent = 'La cible choisie correspond au journal actuel des lignes sélectionnées — choisissez un autre journal.'; }
      return;
    }

    // build assignments and compute new pieces
    const toTransfer = [];
    const assignments = [];
    try {
      selectedRows.forEach(r => {
        const d = (typeof r.getData === 'function') ? r.getData() : r;
        toTransfer.push({ rowComponent: r, data: d });

        // compute new piece if we will move a piece or create a new one
        const hasPiece = Boolean(d.piece_justificative || d.piece);
        const dateHint = d.date || d.date_livr || d.created_at || d.dateEcriture || null;
        const newPiece = generateNextPieceForTable(table, to, dateHint);

        if (d.id) {
          const asg = { id: d.id, new_piece_justificative: newPiece };
          if (d.file_id) asg.file_id = d.file_id;
          assignments.push(asg);
        } else if (d.piece_justificative) {
          // update by piece_justificative (server uses this to find rows)
          assignments.push({ piece_justificative: d.piece_justificative, new_piece_justificative: newPiece, file_id: d.file_id ?? null });
        } else {
          // temp_row / raw -> include raw and request new piece
          assignments.push({ temp_row: true, raw: d, new_piece_justificative: newPiece, file_id: d.file_id ?? null });
        }
      });
    } catch(e){
      console.warn('build assignments error', e);
    }

    if (toTransfer.length === 0) {
      if (feedback) { feedback.style.display = 'block'; feedback.style.color = '#666'; feedback.textContent = 'Aucune ligne valide à transférer.'; }
      return;
    }

    if (!window.confirm(`Transférer ${toTransfer.length} ligne(s) vers "${to}" ?`)) return;

    if (window.Swal) Swal.fire({ title: 'Transfert en cours...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

    try {
      const resp = await fetch(c.endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCSRF()
        },
        body: JSON.stringify({ to, assignments })
      });

      const text = await resp.text();
      let json = null;
      try { json = text ? JSON.parse(text) : null; } catch(e){ json = null; }

      if (!resp.ok) {
        const err = (json && json.message) ? json.message : `Erreur serveur (${resp.status})`;
        throw new Error(err);
      }

      // update UI rows: apply type/code journal + piece_justificative
      toTransfer.forEach((t, idx) => {
        try {
          const r = t.rowComponent;
          const d = t.data || {};
          const upd = {};
          // set journal fields
          if ('code_journal' in d || 'type_journal' in d || 'journal' in d) {
            upd.code_journal = to;
            upd.type_journal = to;
          } else {
            // still set a type_journal to reflect change
            upd.type_journal = to;
          }

          // find the new piece we computed in assignments array (matching by id or original piece)
          const asg = assignments[idx] || {};
          if (asg.new_piece_justificative) {
            upd.piece_justificative = asg.new_piece_justificative;
          }

          const selLocal = q(c.selectId);
          if (selLocal) {
            const opt = Array.from(selLocal.options).find(o => String(o.value) === String(to));
            if (opt) upd.filter_intitule = opt.textContent.trim();
          }
          if (typeof r.update === 'function') {
            r.update(upd);
          } else if (typeof r.setData === 'function') {
            const cur = r.getData ? r.getData() : {};
            r.setData(Object.assign({}, cur, upd));
          }
        } catch(e){ console.warn('update row after transfer failed', e); }
      });

      if (window.Swal) Swal.fire({ icon:'success', title:'Transfert réussi', text: `${toTransfer.length} ligne(s) transférée(s)`, timer:1200, showConfirmButton:false });
      // hide modal for this key
      const modalEl = q(c.modalId);
      if (modalEl) { modalEl.style.display = 'none'; modalEl.classList.remove('open'); }
    } catch (err) {
      console.error('transfer error', err);
      if (window.Swal) Swal.fire({ icon:'error', title:'Erreur', text: err.message || 'Erreur lors du transfert' });
      if (feedback) { feedback.style.display = 'block'; feedback.style.color = '#c00'; feedback.textContent = `Erreur: ${err.message || 'inconnue'}`; }
    } finally {
      try { Swal.close && Swal.close(); } catch(e){}
    }
  }

  /* ---------- Wire events for each config key ---------- */
  Object.keys(config).forEach(key => {
    const c = config[key];
    const btn = q(c.btnId);
    const modal = q(c.modalId);
    const cancel = q(c.cancelId);
    const confirm = q(c.confirmId);

    // CLICK on transfer button: first check there are selected rows, else bring user back to the table visually
    if (btn) btn.addEventListener('click', function(){
      const table = c.tableVar();
      let selectedCount = 0;
      try {
        if (table && typeof table.getSelectedRows === 'function') {
          const selRows = table.getSelectedRows();
          selectedCount = selRows ? selRows.length : 0;
        } else if (table && typeof table.getSelectedData === 'function') {
          const sel = table.getSelectedData();
          selectedCount = sel ? sel.length : 0;
        }
      } catch(e){ selectedCount = 0; }

      if (!selectedCount) {
        // show nice swal and focus table
        if (window.Swal) {
          Swal.fire({
            icon: 'info',
            title: 'Sélectionnez des lignes',
            text: 'Aucune ligne sélectionnée. Retour au tableau pour sélectionner les lignes à transférer.',
            confirmButtonText: 'Aller au tableau'
          }).then(() => {
            // scroll to table container & highlight
            const container = c.tableContainerSelector ? document.querySelector(c.tableContainerSelector) : null;
            highlightElementOnce(container || (typeof table.getElement === 'function' ? table.getElement() : null));
          });
        } else {
          alert('Aucune ligne sélectionnée. Retour au tableau pour sélectionner les lignes à transférer.');
          const container = c.tableContainerSelector ? document.querySelector(c.tableContainerSelector) : null;
          highlightElementOnce(container || (typeof table.getElement === 'function' ? table.getElement() : null));
        }
        return;
      }

      // open modal normally
      showModal(key);
    });

    if (cancel) cancel.addEventListener('click', function(){ hideModal(key); });

    if (confirm) confirm.addEventListener('click', function(){ transferForKey(key); });

    // close by clicking outside modal-content
    if (modal) {
      modal.addEventListener('click', function(e){
        if (e.target === modal) hideModal(key);
      });
    }

    // close on ESC
    window.addEventListener('keydown', function(e){
      if (e.key === 'Escape') {
        if (modal && modal.classList.contains('open')) hideModal(key);
      }
    });
  });

})();



// Variables globales pour le suivi de la valeur précédente et du mode couleur
let lastPiece = null;
let toggle = false;

        var showProrat = (assujettiePartielleTVA == 1); // Vérifie si la valeur est 1
      window.tableAch = new Tabulator("#table-achats", {
            height: "600px",
            layout: "fitDataFill",   // ← change pour adapter chaque colonne à son contenu
            index: "id",             // meilleur choix qu'indexer sur "piece_justificative"
            selectable: true,

            columnDefaults: {        // ← appliqué à TOUTES les colonnes
                formatter: "textarea",  // wrap du texte
                variableHeight: true,   // hauteur de ligne automatique
                cellStyle: function(cell) {
                    cell.getElement().style.whiteSpace = "normal"; // autorise le wrapping
                }
            },

            // —————— vos options existantes ——————
            clipboard: true,
            clipboardPasteAction: "replace",
            placeholder: "Aucune donnée disponible",


            // 2️⃣ À la réception des données : clone date → date_livraison
            ajaxResponse: function(url, params, response) {
              if (response.length === 0 || response[0].id !== "") {
                response.unshift({ id: "", date: "", debit: "", credit: "" });
              }
              response.forEach(row => {
                row.date_livr = row.date || "";
              });
              return response;
            },


            ajaxError: function(xhr, textStatus, errorThrown) {
                console.error("Erreur AJAX :", textStatus, errorThrown);
            },



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

 
                { title: "Date Facture",
                  field: "date",
                  hozAlign: "center",
                  headerFilter: "input",
                  headerFilterParams: {
                    elementAttributes: { style: "width:95px; height:25px;" }
                  },
                  sorter: "date",
                editor: function(cell, onRendered, success, cancel) {
                    // On utilise ton éditeur existant
                    const editor = genericDateEditor("date_livr", null)(cell, onRendered, success, cancel);

                    // Après rendu, on ajoute la gestion de la touche Enter
                    onRendered(function() {
                        const input = editor.tagName ? editor : editor.querySelector("input");
                        if(input){
                            // Placer le curseur à la fin du texte
                            const len = input.value.length;
                            input.setSelectionRange(len, len);
                            input.focus();

                            input.addEventListener("keydown", function(e) {
                                if(e.key === "Enter"){
                                    e.preventDefault();       // Empêche le comportement par défaut
                                    success(input.value);     // Valide la cellule
                                    const nextCell = cell.getRow().getCell("date_livr");
                                    if(nextCell) nextCell.edit(); // Passe au prochain éditeur
                                }
                                if(e.key === "Escape"){
                                    cancel();                // Annule l'édition si Escape
                                }
                            });
                        }
                    });

                    return editor; // Retourne ton éditeur original
                },

                  formatter: function(cell) {
                    const raw = cell.getValue();
                    if (!raw) return "";
                    let dt = luxon.DateTime.fromISO(raw);
                    if (!dt.isValid) dt = luxon.DateTime.fromFormat(raw, "yyyy-MM-dd HH:mm:ss");
                    return dt.isValid ? dt.toFormat("dd/MM/yyyy") : raw;
                  }
                }
                ,

                 { title: "Date livraison",
                  field: "date_livr",
                  hozAlign: "center",
                  headerFilter: "input",
                  headerFilterParams: {
                    elementAttributes: { style: "width:95px; height:25px;" }
                  },
                  sorter: "date",
                  editor: function(cell, onRendered, success, cancel) {
                    // On utilise ton éditeur existant
                    const editor = genericDateEditor("numero_dossier", "date")(cell, onRendered, success, cancel);

                    // Après rendu, on ajoute la gestion de la touche Enter
                    onRendered(function() {
                        const input = editor.tagName ? editor : editor.querySelector("input");
                        if(input){
                            // Placer le curseur à la fin du texte
                            const len = input.value.length;
                            input.setSelectionRange(len, len);
                            input.focus();

                            input.addEventListener("keydown", function(e) {
                                if(e.key === "Enter"){
                                    e.preventDefault();             // Empêche le comportement par défaut
                                    success(input.value);           // Valide la cellule
                                    const nextCell = cell.getRow().getCell("numero_dossier");
                                    if(nextCell) nextCell.edit();  // Passe au prochain éditeur
                                }
                                if(e.key === "Escape"){
                                    cancel();                       // Annule l'édition si Escape
                                }
                            });
                        }
                    });

                    return editor; // Retourne ton éditeur original
                }
                ,
                  formatter: function(cell) {
                    const raw = cell.getValue();
                    if (!raw) return "";
                    let dt = luxon.DateTime.fromISO(raw);
                    if (!dt.isValid) dt = luxon.DateTime.fromFormat(raw, "yyyy-MM-dd HH:mm:ss");
                    return dt.isValid ? dt.toFormat("dd/MM/yyyy") : raw;
                  }
                },
                { title: "N° dossier",
                  field: "numero_dossier",
                  headerFilter: "input",
                  headerFilterParams: {
                                        elementAttributes: {
                                            style: "width: 95px; height: 25px;"
                                        }
                                    },
                  editor: genericTextEditor
                },

                { title: "N° facture",
                    field: "numero_facture",
                    headerFilter: "input",
                    headerFilterParams: {
                        elementAttributes: {
                            style: "width: 95px; height: 25px;"
                        }
                    },
                    editor: genericTextEditor
                },


                { title: "Compte",
                    field: "compte",
                    headerFilter: "input",
                    headerFilterParams: {
                        elementAttributes: { style: "width: 95px; height: 25px;" }
                    },

                    editor: function(cell, onRendered, success, cancel) {
                        let currentFilter = document.querySelector('input[name="filter-achats"]:checked')?.value;
                        const row = cell.getRow();

                        if (currentFilter === 'libre') {
                            const select = document.createElement("select");
                            select.style.width = "100%";
                            select.appendChild(new Option("", "")); // option vide

                            const container = document.createElement("div");
                            container.style.width = "100%";
                            container.appendChild(select);

                            setTimeout(() => {
                                $(select).select2({
                                    placeholder: "Sélectionner un compte...",
                                    allowClear: true,
                                    width: 'resolve',
                                    minimumInputLength: 0,
                                    ajax: {
                                        url: '/fournisseurs-comptes',
                                        dataType: 'json',
                                        delay: 250,
                                        cache: true,
                                        data: function(params) {
                                            return { q: params.term || '', page: params.page || 1 };
                                        },
                                        processResults: function(data) {
                                            return {
                                                results: (data || []).map(item => ({
                                                    id: item.compte,
                                                    text: item.compte + (item.intitule ? ' - ' + item.intitule : ''),
                                                    raw: item
                                                }))
                                            };
                                        }
                                    },
                                    templateResult: entry => entry?.text || '',
                                    templateSelection: entry => entry?.text || '',
                                    escapeMarkup: m => m
                                });

                                // préselection si valeur existante
                                const currentVal = cell.getValue();
                                if (currentVal) {
                                    fetch(`/fournisseurs-comptes?compte=${encodeURIComponent(currentVal)}`)
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.length) {
                                                const item = data[0];
                                                const option = new Option(item.compte + (item.intitule ? ' - ' + item.intitule : ''), item.compte, true, true);
                                                $(option).data('raw', item);
                                                $(select).append(option).trigger('change');

                                                // si contre_partie vide → tenter auto-remplissage
                                                try {
                                                    const contreCell = row.getCell("contre_partie");
                                                    if (contreCell && (!contreCell.getValue() || String(contreCell.getValue()).trim() === "")) {
                                                        // priorité fournisseur
                                                        const fournisseurContre = item.contre_partie ?? item.contrePartie ?? item.default_contre_partie ?? item.compte_default ?? null;
                                                        if (fournisseurContre && String(fournisseurContre).trim() !== "") {
                                                            contreCell.setValue(String(fournisseurContre).trim(), true);
                                                        } else {
                                                            // fallback uniquement pour journal-achats
                                                            const tableEl = cell.getElement().closest('#table-achats');
                                                            if (tableEl) {
                                                                const fallback = $('#journal-achats option:selected').data('contre_partie')
                                                                              ?? $('#journal-achats option:selected').attr('data-contre_partie')
                                                                              ?? null;
                                                                if (fallback && String(fallback).trim() !== "") {
                                                                    contreCell.setValue(String(fallback).trim(), true);
                                                                }
                                                            }
                                                        }
                                                    }
                                                } catch (e) { }
                                            }
                                        })
                                        .catch(err => console.error('Erreur fetch fournisseur pour préselection', err));
                                }

                                $(select).on('select2:select', function(e) {
                                    const data = e.params.data;
                                    success(data.id);

                                    try {
                                        const currentRow = cell.getRow();
                                        const contreCell = currentRow.getCell("contre_partie");
                                        if (!contreCell) return;

                                        const currentCp = (contreCell.getValue() ?? "").toString().trim();
                                        if (currentCp !== "") return; // ne pas écraser

                                        // 1) priorité fournisseur
                                        const fournisseurContre = (data.raw && (data.raw.contre_partie ?? data.raw.contrePartie ?? data.raw.default_contre_partie ?? data.raw.compte_default)) ?? "";
                                        if (fournisseurContre && String(fournisseurContre).trim() !== "") {
                                            contreCell.setValue(String(fournisseurContre).trim(), true);
                                            return;
                                        }

                                        // 2) fallback → UNIQUEMENT si table achats
                                        const tableEl = cell.getElement().closest('#table-achats');
                                        if (tableEl) {
                                            const $opt = $('#journal-achats option:selected');
                                            const journalContre = $opt.data('contre_partie')
                                                                ?? $opt.attr('data-contre_partie')
                                                                ?? "";
                                            if (journalContre && String(journalContre).trim() !== "") {
                                                contreCell.setValue(String(journalContre).trim(), true);
                                            }
                                        }
                                    } catch (err) {
                                        console.error('Erreur lors de l\'application de la contre_partie :', err);
                                    }
                                });

                                $(select).on('select2:unselect', function() {
                                    success('');
                                });

                                $(select).on('keydown', function(e) {
                                    if (e.key === "Escape") cancel();
                                });

                            }, 10);

                            onRendered(() => setTimeout(() => $('.select2-search__field').focus(), 100));
                            return container;

                        } else if (currentFilter === 'contre-partie') {
                            return customListEditorFrs(cell, onRendered, success, cancel, {
                                values: window.getFormattedComptesFournisseurs
                            });
                        } else {
                            const input = document.createElement("input");
                            input.type = "text";
                            input.value = cell.getValue() || "";
                            onRendered(() => input.focus());
                            input.addEventListener("blur", () => success(input.value));
                            return input;
                        }
                    },

                    formatter: function(cell) {
                        const value = cell.getValue();
                        if (value && typeof value === "string") {
                            const parts = value.split(" - ");
                            return parts[0];
                        }
                        return value;
                    },

                    cellEdited: function(cell) {
                        const row = cell.getRow();
                        const compte = cell.getValue();
                        if (!compte) return;
                        if (typeof updateLibelleAndFocus === "function") updateLibelleAndFocus(row, compte);
                        console.log("Valeur Compte mise à jour :", compte);
                    }
                },

                { title: "Libellé",
    field: "libelle",
    headerFilter: "input",
    headerFilterParams: {
        elementAttributes: {
            style: "width: 95px; height: 25px;" // 80 pixels de large
        }
    },
    editor: genericTextEditor
                },

                { title: "Débit",
                    field: "debit",
                    headerFilter: "input",
                    headerFilterParams: {
                        elementAttributes: {
                            style: "width: 95px; height: 25px;"
                        }
                    },
                    editor: customNumberEditor,
                    bottomCalc: "sum",
                    formatter: function(cell) {
                        const value = cell.getValue();
                        return value ? parseFloat(value).toFixed(2) : "0.00";
                    },
                    cellEdited: function(cell) {
                        const row = cell.getRow();
                        const creditCell = row.getCell("credit");

                        // Si Débit a une valeur, mettre Crédit à 0
                        if (cell.getValue() && parseFloat(cell.getValue()) !== 0) {
                            creditCell.setValue("0.00");
                        }

                        if (typeof calculerSoldeCumule === "function") calculerSoldeCumule();
                    }
                },
                { title: "Crédit",
                    field: "credit",
                    headerFilter: "input",
                    headerFilterParams: {
                        elementAttributes: { style: "width: 95px; height: 25px;" }
                    },
                    editor: function(cell, onRendered, success, cancel) {
                        const editor = creditEditor('contte_partie')(cell, onRendered, success, cancel);

                        onRendered(function() {
                            const input = editor.tagName ? editor : editor.querySelector("input");
                            if(input){
                                const len = input.value.length;
                                input.setSelectionRange(len, len);
                                input.focus();

                                input.addEventListener("keydown", function(e) {
                                    if(e.key === "Enter"){
                                        e.preventDefault();
                                        success(input.value);
                                        const nextCell = cell.getRow().getCell("contre_partie");
                                        if(nextCell) nextCell.edit();
                                    }
                                    if(e.key === "Escape"){
                                        cancel();
                                    }
                                });
                            }
                        });

                        return editor;
                    },
                    bottomCalc: "sum",
                    formatter: function(cell) {
                        const value = cell.getValue();
                        return value ? parseFloat(value).toFixed(2) : "0.00";
                    },
                    mutatorEdit: function(value) {
                        return value || "0.00";
                    },
                    cellEdited: function(cell) {
                        const row = cell.getRow();
                        const debitCell = row.getCell("debit");

                        // Si Crédit a une valeur, mettre Débit à 0
                        if (cell.getValue() && parseFloat(cell.getValue()) !== 0) {
                            debitCell.setValue("0.00");
                        }

                        if (typeof calculerSoldeCumule === "function") calculerSoldeCumule();
                    }
                },

                                { title: "N° facture lettrée",
    field: "fact_lettrer",
    width: 200,
    headerFilter: "input",

    // =========================
    // FORMATTER : afficher seulement les numéros de facture
    // =========================
    formatter: function(cell) {
        const value = cell.getValue();
        if (!value) return "";

        // Normaliser en tableau (séparateur : & ou tableau)
        const values = typeof value === "string"
            ? value.split(/\s*&\s*/).filter(Boolean)
            : Array.isArray(value) ? value : [];

        // Retourner uniquement les numéros de facture
        return values
            .map(v => {
                const parts = v.split("|");
                return parts[1] || ""; // numero_facture
            })
            .filter(Boolean)
            .join(" | ");
    },

    // =========================
    // EDITOR : modal avec checkboxes
    // =========================
    editor: function(cell, onRendered, success, cancel) {
        const row = cell.getRow();
        const compte = row.getCell("compte").getValue();
        const debit = row.getCell("debit").getValue();
        const credit = row.getCell("credit").getValue();

        if (!debit && !credit) {
            alert("Veuillez remplir une valeur de débit ou crédit.");
            cancel();
            return document.createElement("div");
        }

        // Création overlay/modal
        const overlay = document.createElement("div");
        overlay.style = "position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);display:flex;justify-content:center;align-items:center;z-index:10002;";

        const modal = document.createElement("div");
        modal.style = "background:#fff;padding:15px;border-radius:6px;min-width:360px;box-shadow:0 6px 20px rgba(0,0,0,0.2);";
        modal.innerHTML = "<h4>Sélection des factures lettrées</h4>";

        const checkboxContainer = document.createElement("div");
        checkboxContainer.style = "max-height:260px;overflow-y:auto;border:1px solid #ddd;padding:6px;margin-top:6px;";
        modal.appendChild(checkboxContainer);

        const btnRow = document.createElement("div");
        btnRow.style = "margin-top:10px;display:flex;justify-content:flex-end;gap:8px;";

        const cancelBtn = document.createElement("button"); 
        cancelBtn.textContent = "Annuler"; 
        cancelBtn.className = "btn btn-secondary";

        const saveBtn = document.createElement("button"); 
        saveBtn.textContent = "Valider"; 
        saveBtn.className = "btn btn-primary";

        btnRow.append(cancelBtn, saveBtn); 
        modal.appendChild(btnRow);
        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Valeurs existantes
        const existingRaw = cell.getValue() ?? [];
        let existingValues = typeof existingRaw === "string" 
            ? existingRaw.split(/\s*&\s*/).filter(Boolean)
            : existingRaw;

        // Récupération via AJAX
        $.ajax({
            url: `/get-nfacturelettree?debit=${encodeURIComponent(debit)}&credit=${encodeURIComponent(credit)}&compte=${encodeURIComponent(compte)}`,
            method: 'GET',
            success: function(response) {
                if (!response) response = [];
                const dispoMap = {};
                response.forEach(item => {
                    const montantVal = item.debit ?? item.credit;
                    const valeur = `${item.id}|${item.numero_facture}|${montantVal}|${item.date}`;
                    dispoMap[valeur] = `${item.numero_facture} / ${montantVal} / ${item.date}`;
                });

                // Pré-cocher les valeurs existantes
                existingValues.forEach(v => {
                    const parts = v.split('|');
                    const label = `${parts[1] || ''} / ${parts[2] || ''} / ${parts[3] || ''}`;
                    const cb = document.createElement('div');
                    cb.innerHTML = `<label><input type="checkbox" value="${v}" checked> ${label}</label>`;
                    checkboxContainer.appendChild(cb);
                    delete dispoMap[v];
                });

                // Ajouter les autres options
                Object.keys(dispoMap).forEach(val => {
                    const cb = document.createElement('div');
                    cb.innerHTML = `<label><input type="checkbox" value="${val}"> ${dispoMap[val]}</label>`;
                    checkboxContainer.appendChild(cb);
                });

                // Boutons
                saveBtn.onclick = function() {
                    const checked = [];
                    checkboxContainer.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => checked.push(cb.value));
                    const joined = checked.join(' & ');
                    cell.setValue(joined);
                    success(joined);

                    // Focus automatique sur date_lettrage si nécessaire
                    const dateCell = row.getCell("date_lettrage");
                    if (dateCell) dateCell.edit();
                    
                    document.body.removeChild(overlay);
                };

                cancelBtn.onclick = function() {
                    document.body.removeChild(overlay);
                    cancel();
                };
            },
            error: function(err) {
                console.error("Erreur AJAX :", err);
                document.body.removeChild(overlay);
                cancel();
            }
        });

        return document.createElement("div"); // nécessaire pour Tabulator
    }
}
,

                contrePartieColumn,

                { title: "Rubrique TVA",
    field: "rubrique_tva",
    visible:false,
    headerFilter: "input",

    width: 120,
    editor: customListEditorRub,
    formatter: function (cell) {
        const row = cell.getRow().getData();
        return row.rubrique_tva_label || cell.getValue() || "";
    },
    headerFilterParams: {
        elementAttributes: {
            style: "width: 100px; height: 25px;"
        }
    }
                },

                { title: "Compte TVA",
                    field: "compte_tva",
                        visible:false,

                    headerFilter: "input",
                    editor: customListEditortva,
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

                { title: "Prorat de deduction",
                    field: "prorat_de_deduction",
                    visible: showProrat, // Affiché si "Oui", masqué sinon
                    headerFilter: "input",
                    editor: customListEditortva,
                    headerFilterParams: {
                        elementAttributes: { style: "width: 95px; height: 25px;" }
                    },
                    editorParams: {
                        autocomplete: true,
                        listOnEmpty: true,
                        values: ["Oui", "Non"]
                    }
                },
                { title: "Solde Cumulé",
                    field: "value",
                    headerFilter: "input",
                    headerFilterParams: {
                        elementAttributes: { style: "width: 95px; height: 25px;" }
                    },
                    mutator: function(value, data, type, params, component) {
                        return value; // garde la valeur brute pour le formatter
                    },
                    formatter: function(cell) {
                        const table = cell.getTable();
                        const rows = table.getRows();

                        let dernierSoldeParPiece = {};
                        let soldeCumul = 0;
                        const currentRow = cell.getRow();
                        const currentData = currentRow.getData();
                        const currentPiece = currentData.piece_justificative || "__default__";

                        // Vérifier si la ligne est vide (nouvelle saisie) → ne pas colorer
                        const isEmptyRow = !currentData.debit && !currentData.credit && !currentData.piece_justificative;

                        for (let i = 0; i < rows.length; i++) {
                            const row = rows[i];
                            const d = row.getData();
                            const piece = d.piece_justificative || "__default__";
                            const debit = parseFloat(d.debit) || 0;
                            const credit = parseFloat(d.credit) || 0;

                            if (!(piece in dernierSoldeParPiece)) {
                                soldeCumul = debit - credit;
                            } else {
                                soldeCumul = dernierSoldeParPiece[piece] + debit - credit;
                            }

                            dernierSoldeParPiece[piece] = soldeCumul;

                            if (row === currentRow) {
                                // Vérifier si c'est la dernière ligne de la pièce
                                const isDerniereLigne = rows.slice(i + 1).every(r => {
                                    return (r.getData().piece_justificative || "__default__") !== piece;
                                });

                                // Appliquer fond jaune seulement si dernière ligne, solde ≠ 0, et ligne non vide
                                if (isDerniereLigne && soldeCumul !== 0 && !isEmptyRow) {
                                    cell.getElement().style.backgroundColor = "yellow";
                                } else {
                                    cell.getElement().style.backgroundColor = "";  
                                }

                                // Retourne le solde calculé
                                return soldeCumul.toFixed(2);
                            }
                        }

                        return "0.00"; // valeur par défaut si jamais
                    }
                },   


                { title: "Pièce justificative",
                  field: "piece_justificative",
                  headerFilter: "input",
                  headerFilterParams: { elementAttributes: { style: "width: 150px; height: 25px;" } },
                  width: 260,

                  formatter: function(cell) {
                    const d = cell.getRow().getData();
                    const piece = d.piece_justificative || "";

                    const esc = s => String(s || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;');

                    const wrapper = document.createElement("div");
                    wrapper.className = "pj-cell";
                    wrapper.style.display = "flex";
                    wrapper.style.alignItems = "center";
                    wrapper.style.gap = "6px";

                    const input = document.createElement("input");
                    input.type = "text";
                    input.className = "pj-input";
                    input.value = esc(piece);
                    input.style.flex = "1";
                    input.style.border = "0";
                    input.style.background = "transparent";
                    input.style.padding = "4px 6px";

                    input.addEventListener("keydown", (ev) => {
                        if (ev.key === "Enter") {
                            ev.stopPropagation();
                            cell.setValue(input.value, true);
                            input.blur();
                        }
                    });

                    input.addEventListener("blur", () => {
                        if (cell.getValue() !== input.value) {
                            cell.setValue(input.value, true);
                        }
                    });

                    wrapper.appendChild(input);
                    return wrapper;
                  },

                  cellClick: function() {} // ❗ Aucun bouton, donc aucune action
                },
                { title: "<input type='checkbox' id='master-select' title='Tout sélectionner / Tout désélectionner'>",
                  field: "selected",
                  width: 140, // largeur légèrement augmentée pour garder le design propre
                  hozAlign: "center",
                  headerSort: false,
                  headerFilter: false,

                  formatter: function (cell) {
                    const row = cell.getRow();
                    const data = row.getData();
                    const checked = row.isSelected ? (row.isSelected() ? "checked" : "") : "";

                    const hasFile = !!(data.file_id || data.file_url || data.filepath || data.path);

                    // -------------------------------
                    // Ordre : Trombone → Eye → Checkbox
                    // -------------------------------
                    let html = `
                      <!-- 1️⃣ Icône Trombone (Upload) -->
                      <button class="icon-btn upload-icon"
                              style="border:0;background:none;padding:6px;cursor:pointer;">
                        <i class="fas fa-paperclip"></i>
                      </button>

                      <!-- 2️⃣ Icône Eye (View) -->
                      <button class="icon-btn view-icon-achat"
                              style="border:0;background:none;padding:6px;
                                    cursor:${hasFile ? 'pointer' : 'not-allowed'};
                                    opacity:${hasFile ? '1' : '0.3'};"
                              data-file-id="${data.file_id || ''}"
                              data-file-url="${data.file_url || data.filepath || data.path || ''}">
                        <i class="fas fa-eye"></i>
                      </button>

                      <!-- 3️⃣ Checkbox -->
                      <input type='checkbox' id="achat-checkbox" class='select-row' ${checked}>
                    `;

                    // -------------------------------
                    // Bouton Vider la ligne si saisie vide
                    // -------------------------------
                    const isSaisie = !data.date && !data.date_livr && !data.compte &&
                                    !data.libelle && !data.debit && !data.credit;

                    if (isSaisie) {
                      html += ` <span class='clear-row-btn'
                                    style='color:red;cursor:pointer;font-size:18px;'
                                    title='Vider la ligne'>&times;</span>`;
                    }

                    return html;
                  },

                  cellClick: function (e, cell) {
                    const row = cell.getRow();
                    const table = cell.getTable();

                    // -------------------------------
                    // ✔ UPLOAD (Trombone)
                    // -------------------------------
                    if (e.target.closest(".upload-icon") ||
                        (e.target.tagName === "I" && e.target.classList.contains("fa-paperclip"))) {

                      e.preventDefault();
                      window.currentPieceRowIndex = row.getIndex();
                      $("#achatModal_main").show();

                      const interval = setInterval(() => {
                        if (!window.selectedFileId && !window.selectedFilePath) return;

                        const fileId = window.selectedFileId;
                        const fileUrl = window.selectedFilePath;

                        row.update({
                          file_id: fileId,
                          file_url: fileUrl,
                          filepath: fileUrl,
                          path: fileUrl
                        });

                        const rowEl = row.getElement();
                        const eye = rowEl.querySelector(".view-icon-achat");

                        if (eye) {
                          eye.style.opacity = "1";
                          eye.style.cursor = "pointer";
                          eye.dataset.fileId = fileId;
                          eye.dataset.fileUrl = fileUrl;
                        }

                        window.selectedFileId = null;
                        window.selectedFilePath = null;
                        clearInterval(interval);

                      }, 300);

                      return;
                    }

                    // -------------------------------
                    // ✔ VIEW (Eye)
                    // -------------------------------
                    if (e.target.closest(".view-icon-achat") ||
                        (e.target.tagName === "I" && e.target.classList.contains("fa-eye"))) {

                      const btn = e.target.closest(".view-icon-achat");
                      const fileId = btn.dataset.fileId;
                      const fileUrl = btn.dataset.fileUrl;

                      const openFile = (url) => window.open(url, "_blank");

                      if (fileUrl && fileUrl.trim() !== "") return openFile(fileUrl);

                      if (fileId) {
                        fetch(`/api/file/${encodeURIComponent(fileId)}`)
                          .then(r => r.json())
                          .then(res => {
                            if (res.file_url) openFile(res.file_url);
                            else if (res.path) openFile(`/storage/${res.path}`);
                            else alert("❌ Impossible d’ouvrir le fichier");
                          });
                      } else {
                        alert("❌ Aucun fichier associé");
                      }

                      return;
                    }

                    // -------------------------------
                    // ✔ CHECKBOX
                    // -------------------------------
                    if (e.target.classList.contains('select-row')) {
                      const data = row.getData();
                      const isSaisie = !data.date && !data.date_livr && !data.compte &&
                                      !data.libelle && !data.debit && !data.credit;

                      if (isSaisie) {
                        const el = row.getElement();
                        if (el) {
                          el.classList.add('invalid-select-flash');
                          setTimeout(() => el.classList.remove('invalid-select-flash'), 500);
                        }
                        e.target.checked = false;
                        return;
                      }

                      if (row.isSelected()) {
                        row.deselect();
                        window.rowsWaitingForFileAchat =
                          (window.rowsWaitingForFileAchat || [])
                          .filter(r => r.getIndex() !== row.getIndex());
                      } else {
                        row.select();
                        if (!window.rowsWaitingForFileAchat) window.rowsWaitingForFileAchat = [];
                        if (!window.rowsWaitingForFileAchat.some(x => x.getIndex() === row.getIndex())) {
                          window.rowsWaitingForFileAchat.push(row);
                        }
                      }

                      return;
                    }

                    // -------------------------------
                    // ✔ CLEAR-ROW (Vider la ligne)
                    // -------------------------------
                    if (e.target.classList.contains('clear-row-btn')) {
                      row.update({
                        date: '',
                        date_livr: '',
                        compte: null,
                        libelle: null,
                        numero_facture: null,
                        debit: null,
                        contre_partie: null,
                        credit: null,
                        piece_justificative: null,
                        file_id: null,
                        file_url: null,
                        filepath: null,
                        path: null
                      });
                    }
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


// tableAch.on("cellEdited", function(cell){
//     const row = cell.getRow();
//     const rowData = row.getData();

//     console.log("🔄 Ligne modifiée, envoi au serveur :", rowData);

//     fetch("/achats/update-row", {
//         method: "POST",
//         headers: {
//             "Content-Type": "application/json",
//             "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
//         },
//         body: JSON.stringify(rowData)
//     })
//     .then(r => r.json())
//     .then(resp => {
//         console.log("Réponse du serveur :", resp);

        
//         if(resp.id){
//             row.update({ id: resp.id });
//         }
//     })
//     .catch(err => console.error("Erreur update ligne :", err));
// });

 tableAch.on("cellEdited", async function(cell) {
    const row = cell.getRow();
    const rowData = row.getData();
    const field = cell.getField();

    
    if (!rowData.id) {
        console.log("⚠️ Ligne sans id ignorée :", rowData);
        return;
    }

    console.log("🔄 Cellule modifiée, ligne :", rowData);

    try {
       
        const result = await Swal.fire({
            title: "Êtes-vous sûr ?",
            text: `Voulez-vous modifier le champ : "${field}" ?`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Oui",
            cancelButtonText: "Non",
            allowOutsideClick: false,
            allowEscapeKey: false
        });

        if (!result.isConfirmed) {
            console.log("❌ Modification annulée par l'utilisateur.");
            
            row.update({ [field]: cell.getOldValue() });
            return;
        }

        
        const response = await fetch("/achats/update-row", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(rowData)
        });

        const resp = await response.json();
        console.log("Réponse du serveur :", resp);

        if (resp.id) {
            row.update({ id: resp.id });
        }

        // await Swal.fire({
        //     icon: "success",
        //     title: "Modification enregistrée",
        //     text: `Le champ "${field}" a été mis à jour avec succès.`
            
        // });
    } catch (err) {
        console.error("Erreur update ligne :", err);
        await Swal.fire({
            icon: "error",
            title: "Erreur",
            text: "Impossible d'enregistrer la modification."
        });
    }
            updateTabulatorDataAchats();

});




// tableAch.on("cellEdited", function(cell){
//     const row = cell.getRow();
//     const rowData = row.getData();
//     const fieldName = cell.getField(); // Nom du champ modifié
//     const newValue = cell.getValue();  // Nouvelle valeur saisie

//     // Affichage de la confirmation avant l'envoi
//     Swal.fire({
//         title: 'Êtes-vous sûr ?',
//         text: `Voulez-vous vraiment modifier le champ "${fieldName}" en "${newValue}" ?`,
//         icon: 'warning',
//         showCancelButton: true,
//         confirmButtonText: 'Oui',
//         cancelButtonText: 'Non'
//     }).then((result) => {
//         if (result.isConfirmed) {
//             // Si l'utilisateur confirme, envoi de la modification au serveur
//             console.log("🔄 Ligne modifiée, envoi au serveur :", rowData);

//             fetch("/achats/update-row", {
//                 method: "POST",
//                 headers: {
//                     "Content-Type": "application/json",
//                     "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
//                 },
//                 body: JSON.stringify(rowData)
//             })
//             .then(r => r.json())
//             .then(resp => {
//                 console.log("Réponse du serveur :", resp);

//                 if(resp.id){
//                     row.update({ id: resp.id });
//                 }
//             })
//             .catch(err => console.error("Erreur update ligne :", err));
//         } else {
//             // Si l'utilisateur annule, on restaure l'ancienne valeur
//             cell.restoreOldValue();
//         }
//     });
// });



                console.log("Valeur assujettiePartielleTVA:", assujettiePartielleTVA);
                console.log("Colonne Prorat affichée ?", showProrat);

// Assure que csrfToken existe
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
// --- helper : récupérer un nom de fichier "friendly" depuis la ligne ---

// helper : considère si la ligne est vide (pas de NF, pas de compte, pas de montant)
function isRowEmpty(data) {
  const nf = String(data.numero_facture || "").trim();
  const compte = String(data.compte || "").trim();
  const debit = parseFloat(data.debit) || 0;
  const credit = parseFloat(data.credit) || 0;
  return !(nf || compte || debit !== 0 || credit !== 0);
}



// Ajouter l'écouteur pour mettre à jour "type_Journal"
document.querySelector("#journal-achats").addEventListener("change", function (e) {
    const selectedCode = e.target.value;

    let ligneSelectionnee = tableAch.getSelectedRows()[0];
    if (ligneSelectionnee) {
        ligneSelectionnee.update({ type_Journal: selectedCode });
    }
});



  document.getElementById('import-achats').addEventListener('click', () => {
    document.getElementById('mapping-modal').style.display = 'flex';
  });

  document.getElementById('mapping-cancel').addEventListener('click', () => {
    document.getElementById('mapping-modal').style.display = 'none';
  });

  // Facultatif : fermer le modal en cliquant à l’extérieur
  document.getElementById('mapping-modal').addEventListener('click', function (e) {
    if (e.target.id === 'mapping-modal') {
      this.style.display = 'none';
    }
  });

    // 3) Mapping modal setup
    const importBtn   = document.getElementById("import-achats");
    const modal       = document.getElementById("mapping-modal");
    const modalInput  = document.getElementById("modal-excel-input");
    const mappingForm = document.getElementById("mapping-form");
    const confirmBtn  = document.getElementById("mapping-confirm");
    const cancelBtn   = document.getElementById("mapping-cancel");

    const targetFields = [
      { key:"date",            label:"Date"           },
      { key:"numero_facture",  label:"N° facture"     },
      { key:"compte",          label:"Compte"         },
      { key:"intitule_compte", label:"INTITULE COMPTE"},
      { key:"libelle",         label:"Libellé"        },
      { key:"debit",           label:"Débit"          },
      { key:"credit",          label:"Crédit"         },
      { key:"contre_partie",   label:"Contre-Partie"  },
      { key:"rubrique_tva",    label:"Rubrique TVA"   },
      { key:"compte_tva",      label:"Compte TVA"     },
    ];

    importBtn.addEventListener("click", () => {
      mappingForm.innerHTML = "";
      confirmBtn.disabled   = true;
      modal.style.display   = "flex";
      modalInput.value      = null;
      modalInput.click();
    });

    modalInput.addEventListener("change", function(evt) {
      const file = evt.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.readAsBinaryString(file);
      reader.onload = e => {
        const wb     = XLSX.read(e.target.result,{type:"binary"});
        const ws     = wb.Sheets[wb.SheetNames[0]];
        const rows   = XLSX.utils.sheet_to_json(ws,{header:1,defval:""});
        if (!rows.length) { alert("Feuille vide ou format invalide."); return; }
        const headers = rows[0].map(h=>h.toString().trim());
        buildMappingForm(headers, rows.slice(1));
      };
      reader.onerror = () => alert("Erreur de lecture du fichier Excel.");
    });

    function buildMappingForm(headers, dataRows) {
      mappingForm.innerHTML = "";
      targetFields.forEach(f => {
        const div = document.createElement("div");
        div.style.display = "flex";
        div.style.justifyContent = "space-between";
        div.style.marginBottom = "8px";
        div.innerHTML = `
          <label>${f.label}</label>
          <select data-key="${f.key}">
            <option value="">— Sélectionnez —</option>
            ${headers.map((h,i)=>`<option value="${i}">Col ${i+1}: ${h}</option>`).join("")}
          </select>
        `;
        mappingForm.appendChild(div);
      });
      confirmBtn.disabled = false;

      confirmBtn.onclick = async () => {
        // mapping
        const selects = mappingForm.querySelectorAll("select[data-key]");
        const mapIdx = {};
        selects.forEach(sel => {
          if (sel.value !== "") mapIdx[sel.dataset.key] = parseInt(sel.value,10);
        });
        // build data
        const tableData = dataRows.map(cols => ({
          date:               mapIdx.date            != null ? cols[mapIdx.date]            : "",
          numero_facture:     mapIdx.numero_facture  != null ? cols[mapIdx.numero_facture]    : "",
          compte:             mapIdx.compte          != null ? cols[mapIdx.compte]            : "",
          intitule_compte:    mapIdx.intitule_compte != null ? cols[mapIdx.intitule_compte]   : "",
          libelle:            mapIdx.libelle         != null ? cols[mapIdx.libelle]           : "",
          debit:              mapIdx.debit           != null ? cols[mapIdx.debit]             : "",
          credit:             mapIdx.credit          != null ? cols[mapIdx.credit]            : "",
          contre_partie:      mapIdx.contre_partie   != null ? cols[mapIdx.contre_partie]     : "",
          rubrique_tva:       mapIdx.rubrique_tva    != null ? cols[mapIdx.rubrique_tva]      : "",
          compte_tva:         mapIdx.compte_tva      != null ? cols[mapIdx.compte_tva]        : "",
          piece_justificative:"",
          file_id:"",
        }));
        modal.style.display = "none";

        // update piece, inject, solde, save
        updatePieceJustificative(tableData);
        await tableAch.updateOrAddData(tableData);
        calculerSoldeCumule();
        const res = await enregistrerLignesAch();
        if (res.success) alert("Import + sauvegarde réussis !");
        else alert("Import OK, mais sauvegarde échouée.");
      };
    }

    cancelBtn.addEventListener("click", () => modal.style.display="none");
    modal.addEventListener("click", e => { if (e.target===modal) modal.style.display="none"; });



        
 

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
document.addEventListener('change', function (e) {
  if (e.target && e.target.id === 'master-select') {
    const checked = e.target.checked;
    if (checked) {
      tableVentes.selectRow();
    } else {
      tableVentes.deselectRow();
    }
  }
});
// après avoir initialisé tableAch et chargé les données :
tableAch.on("dataLoaded", function() {
  // on parcourt toutes les lignes et on coche si selected=true dans les data
  tableAch.getRows().forEach(row => {
    if (row.getData().selected) {
      row.select();
    } else {
      row.deselect();
    }
  });

  // mettre à jour la master checkbox
  const all = tableAch.getRows();
  const sel = tableAch.getSelectedRows();
  const master = document.getElementById("master-select");
  if (master) master.checked = all.length && sel.length === all.length;
});


// Table des ventes
window.tableVentes = new Tabulator("#table-ventes", {
    height: "500px",
    index:'id',

    // ← chaque colonne s’adapte à la largeur de son contenu
    layout: "fitDataFill",
    // ← appliqué à TOUTES les colonnes pour wrap + hauteur auto
    columnDefaults: {
        formatter: "textarea",
        variableHeight: true,
        cellStyle: function(cell) {
            cell.getElement().style.whiteSpace = "normal";
        }
    },

    rowHeight: 25, // définit la hauteur de ligne à 30px

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
                        elementAttributes: { style: "width: 95px; height: 25px;" }
                      },
                      sorter: "date",
                      editor: genericDateEditorVte("date_livr", null),

                      formatter: function(cell) {
                        const dateValue = cell.getValue();
                        if (!dateValue) return "";
                        let dt = luxon.DateTime.fromFormat(dateValue, "yyyy-MM-dd HH:mm:ss");
                        if (!dt.isValid) dt = luxon.DateTime.fromISO(dateValue);
                        return dt.isValid ? dt.toFormat("dd/MM/yyyy") : dateValue;
                      },

                      cellEdited: function(cell) {
                        const row = cell.getRow();
                        const nextCell = row.getCell("numero_dossier");

                        if (nextCell) {
                          nextCell.edit(); // ouvre l'éditeur automatiquement
                        }
                      }
                    },

                    // { title: "Date livraison",
                    //   field: "date_livr",
                    //   hozAlign: "center",
                    //   headerFilter: "input",
                    //   headerFilterParams: {
                    //     elementAttributes: { style: "width: 95px; height: 25px;" }
                    //   },
                    //   sorter: "date",
                    //   editor: genericDateEditorVte(null, "date"), // prevField = date (Shift+Enter pour revenir)
                    //   formatter: function(cell) {
                    //     const raw = cell.getValue();
                    //     if (!raw) return "";
                    //     let dt = luxon.DateTime.fromISO(raw);
                    //     if (!dt.isValid) dt = luxon.DateTime.fromFormat(raw, "yyyy-MM-dd HH:mm:ss");
                    //     return dt.isValid ? dt.toFormat("dd/MM/yyyy") : raw;
                    //   }
                    // },

                    { title: "N° dossier", field: "numero_dossier", headerFilter: "input",
                        headerFilterParams: { elementAttributes: { style: "width: 95px; height: 25px;" } },
                          editor: guaranteedInputEditor("numero_dossier", "numero_facture") 
                    },
                    { title: "N° Facture", field: "numero_facture",headerFilter: "input",headerFilterParams: {
                        elementAttributes: {
                            style: "width: 95px; height: 25px;" // 80 pixels de large
                        }
                    },
              editor: guaranteedInputEditor("numero_facture", "compte", "numero_dossier") // nextField = compte, prevField = numero_dossier
                    },
                    { title: "Compte",
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
                    { title: "Libellé",
                        field: "libelle",
                        headerFilter: "input",
                        headerFilterParams: {
                            elementAttributes: {
                                style: "width: 95px; height: 25px;" // 80 pixels de large
                            }
                        },
                        editor: guaranteedInputEditor("libelle"),
                        editable: true, // Non éditable automatiquement
                    },
                    { title: "Débit",
                    field: "debit",
                    headerFilter: "input",
                    headerFilterParams: {
                        elementAttributes: {
                            style: "width: 95px; height: 25px;" // 80 pixels de large
                        }
                    },
                      editor: calcNumberEditorFactory(),
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
                      editor: calcNumberEditorFactory(),
                    bottomCalc: "sum", // Calcul du total dans le bas de la colonne
                    formatter: function(cell) {
                    // Formater pour afficher 0.00 si la cellule est vide ou nulle
                    const value = cell.getValue();
                    return value ? parseFloat(value).toFixed(2) : "0.00";
                    },
                    },
                    { title: "N° facture lettrée",
                        field: "fact_lettrer",
                        width: 200,
                        headerFilter: "input",

                        // =========================
                        // FORMATTER : afficher seulement les numéros de facture
                        // =========================
                        formatter: function(cell) {
                            const value = cell.getValue();
                            if (!value) return "";

                            // Normaliser en tableau (séparateur : & ou tableau)
                            const values = typeof value === "string"
                                ? value.split(/\s*&\s*/).filter(Boolean)
                                : Array.isArray(value) ? value : [];

                            // Retourner uniquement les numéros de facture
                            return values
                                .map(v => {
                                    const parts = v.split("|");
                                    return parts[1] || ""; // numero_facture
                                })
                                .filter(Boolean)
                                .join(" | ");
                        },

                        // =========================
                        // EDITOR : modal avec checkboxes
                        // =========================
                        editor: function(cell, onRendered, success, cancel) {
                            const row = cell.getRow();
                            const compte = row.getCell("compte").getValue();
                            const debit = row.getCell("debit").getValue();
                            const credit = row.getCell("credit").getValue();

                            if (!debit && !credit) {
                                alert("Veuillez remplir une valeur de débit ou crédit.");
                                cancel();
                                return document.createElement("div");
                            }

                            // Création overlay/modal
                            const overlay = document.createElement("div");
                            overlay.style = "position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);display:flex;justify-content:center;align-items:center;z-index:10002;";

                            const modal = document.createElement("div");
                            modal.style = "background:#fff;padding:15px;border-radius:6px;min-width:360px;box-shadow:0 6px 20px rgba(0,0,0,0.2);";
                            modal.innerHTML = "<h4>Sélection des factures lettrées</h4>";

                            const checkboxContainer = document.createElement("div");
                            checkboxContainer.style = "max-height:260px;overflow-y:auto;border:1px solid #ddd;padding:6px;margin-top:6px;";
                            modal.appendChild(checkboxContainer);

                            const btnRow = document.createElement("div");
                            btnRow.style = "margin-top:10px;display:flex;justify-content:flex-end;gap:8px;";

                            const cancelBtn = document.createElement("button"); 
                            cancelBtn.textContent = "Annuler"; 
                            cancelBtn.className = "btn btn-secondary";

                            const saveBtn = document.createElement("button"); 
                            saveBtn.textContent = "Valider"; 
                            saveBtn.className = "btn btn-primary";

                            btnRow.append(cancelBtn, saveBtn); 
                            modal.appendChild(btnRow);
                            overlay.appendChild(modal);
                            document.body.appendChild(overlay);

                            // Valeurs existantes
                            const existingRaw = cell.getValue() ?? [];
                            let existingValues = typeof existingRaw === "string" 
                                ? existingRaw.split(/\s*&\s*/).filter(Boolean)
                                : existingRaw;

                            // Récupération via AJAX
                            $.ajax({
                                url: `/get-nfacturelettree?debit=${encodeURIComponent(debit)}&credit=${encodeURIComponent(credit)}&compte=${encodeURIComponent(compte)}`,
                                method: 'GET',
                                success: function(response) {
                                    if (!response) response = [];
                                    const dispoMap = {};
                                    response.forEach(item => {
                                        const montantVal = item.debit ?? item.credit;
                                        const valeur = `${item.id}|${item.numero_facture}|${montantVal}|${item.date}`;
                                        dispoMap[valeur] = `${item.numero_facture} / ${montantVal} / ${item.date}`;
                                    });

                                    // Pré-cocher les valeurs existantes
                                    existingValues.forEach(v => {
                                        const parts = v.split('|');
                                        const label = `${parts[1] || ''} / ${parts[2] || ''} / ${parts[3] || ''}`;
                                        const cb = document.createElement('div');
                                        cb.innerHTML = `<label><input type="checkbox" value="${v}" checked> ${label}</label>`;
                                        checkboxContainer.appendChild(cb);
                                        delete dispoMap[v];
                                    });

                                    // Ajouter les autres options
                                    Object.keys(dispoMap).forEach(val => {
                                        const cb = document.createElement('div');
                                        cb.innerHTML = `<label><input type="checkbox" value="${val}"> ${dispoMap[val]}</label>`;
                                        checkboxContainer.appendChild(cb);
                                    });

                                    // Boutons
                                    saveBtn.onclick = function() {
                                        const checked = [];
                                        checkboxContainer.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => checked.push(cb.value));
                                        const joined = checked.join(' & ');
                                        cell.setValue(joined);
                                        success(joined);

                                        // Focus automatique sur date_lettrage si nécessaire
                                        const dateCell = row.getCell("date_lettrage");
                                        if (dateCell) dateCell.edit();
                                        
                                        document.body.removeChild(overlay);
                                    };

                                    cancelBtn.onclick = function() {
                                        document.body.removeChild(overlay);
                                        cancel();
                                    };
                                },
                                error: function(err) {
                                    console.error("Erreur AJAX :", err);
                                    document.body.removeChild(overlay);
                                    cancel();
                                }
                            });

                            return document.createElement("div"); 
                        }
                    },
                    { title: "Contre-Partie",
                        field: "contre_partie",
                        headerFilter: "input",
                        headerFilterParams: { elementAttributes: { style: "width:95px; height:25px;" } },
                        triggerOnPrefill: false,
                        editor: customListEditorPlanComptable,
                        editorParams: {
                          autocomplete: true,
                          listOnEmpty: true,
                          verticalNavigation: "editor",
                          valuesLookup: async function(cell) {
                            if (!selectedCodeJournal2 || selectedCodeJournal2.trim() === "") return [];
                            try {
                              const resp = await fetch(`/get-contre-parties-ventes?code_journal=${selectedCodeJournal2}`);
                              if (!resp.ok) throw new Error("Erreur réseau");
                              const datav = await resp.json();
                              if (datav.error) return [];
                              return datav;
                            } catch (err) {
                              console.error(err);
                              alert("Impossible de récupérer les contre-parties.");
                              return [];
                            }
                          },
                          onRendered: function(editorEl, cell) {
                            // pré-fill
                            if (!cell.getValue()) {
                              const contrePartie = $('#journal-ventes option:selected').data('contre_partie');
                              if (contrePartie) {
                                cell.setValue(contrePartie, true);
                                editorEl.value = contrePartie;
                              } else if (editorEl.options && editorEl.options.length > 0) {
                                const first = editorEl.options[0].value || editorEl.options[0];
                                cell.setValue(first, true);
                                editorEl.value = first;
                              }
                            }

                            // Intercepter Enter => commit + ouvrir piece_justificative
                            editorEl.addEventListener("keydown", function(e) {
                              if (e.key === "Enter") {
                                e.preventDefault();
                                // commit dans la cellule
                                try { cell.setValue(editorEl.value, true); } catch (err) {}
                                // ouvrir piece_justificative
                                const nextCell = cell.getRow().getCell("piece_justificative");
                                if (nextCell) setTimeout(() => { try { nextCell.edit(true); } catch(e){} }, 50);
                              } else if (e.key === "Escape") {
                                e.preventDefault();
                                cell.edit(); // ré-ouvrir si besoin
                              }
                            });
                          }
                        },
                        cellEdited: function(cell) {
                          // hook après changement
                          console.log("Contre-Partie mise à jour :", cell.getValue());
                        }
                    },
                    { title: "Compte TVA",
                        field: "compte_tva",
                        headerFilter: "input",
                        visible:false,
                        headerFilterParams: {
                            elementAttributes: {
                                style: "width: 95px; height: 25px;" // 80 pixels de large
                            }
                        },
                        editor: customListEditortva,
                        editorParams: {
                            autocomplete: true,
                            listOnEmpty: true,
                            values: comptesAchats.map(compte => `${compte.compte} - ${compte.intitule}`)
                        }
                    },
                    { title: "Rubrique TVA",
                field: "rubrique_tva",
                headerFilter: "input",
                headerFilterParams: {
                    elementAttributes: { style: "width: 95px; height: 25px;" }
                },
                width: 95,
                visible:false,
                minWidth: 95,
                widthGrow: 0,
                formatter: function(cell) {
                    const value = cell.getValue() || "";
                    return `<div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${value}">${value}</div>`;
                },

                // Editor : input text (pas de select). Le traitement AJAX est fait ici.
              // --- Editor rubrique_tva adapté au JSON renvoyé par ton controller ---
            editor: function(cell, onRendered, success, cancel, editorParams) {
                const row = cell.getRow();
                const input = document.createElement("input");
                input.type = "text";
                input.style.width = "100%";
                input.style.boxSizing = "border-box";
                input.value = cell.getValue() || "";

                let lastServerResp = null;

                function getCsrfToken() {
                    const t = document.querySelector('meta[name="csrf-token"]');
                    return t ? t.getAttribute('content') : null;
                }
                function getCodeJournalFromDom() {
                    const sel = document.querySelector('#journal-ventes');
                    if (sel && sel.value && String(sel.value).trim() !== '') return String(sel.value).trim();
                    return null;
                }

                function fetchRubrique(codeJournal) {
                    return new Promise((resolve, reject) => {
                        const params = {};
                        if (codeJournal) params.code_journal = codeJournal;
                        const soc = document.querySelector('#societe_id');
                        if (soc && soc.value) params.societe_id = soc.value;

                        $.ajax({
                            url: '/getRubriqueSociete',
                            method: 'GET',
                            dataType: 'json',
                            data: params,
                            headers: { 'X-CSRF-TOKEN': getCsrfToken() ?? '' },
                            success(resp) {
                                if (resp && resp.error) { resolve({ ok: false, error: resp.error, raw: resp }); return; }
                                resolve({ ok: true, data: resp });
                            },
                            error(jqXHR) {
                                let parsed = null;
                                try { parsed = jqXHR.responseJSON ?? null; } catch(e) { parsed = null; }
                                reject({ status: jqXHR.status, parsed });
                            }
                        });
                    });
                }

                onRendered(() => {
                    const el = cell.getElement();
                    el.innerHTML = "";
                    el.appendChild(input);
                    input.focus();
                    input.select();
                });

                (function tryAutoFill() {
                    const codeJournal = getCodeJournalFromDom();
                    fetchRubrique(codeJournal)
                        .then(result => {
                            if (!result.ok) { console.warn('getRubriqueSociete:', result.error || result.raw); return; }
                            const resp = result.data;
                            lastServerResp = resp;

                            const serverText = resp.selected_text ?? (resp.nom_racines ? `${resp.rubrique}: ${resp.nom_racines} (${Number(resp.taux).toFixed(2)}%)` : (resp.rubrique ?? ""));
                            const serverCode = resp.selected ? String(resp.selected) : (resp.rubrique ? String(resp.rubrique) : null);
                            const serverCompteTva = resp.compte_tva ?? "";

                            // normaliser taux en fraction
                            const serverTaux = (typeof resp.taux !== 'undefined' && resp.taux !== null && resp.taux !== '') ? (Number(resp.taux) > 1 ? Number(resp.taux) / 100 : Number(resp.taux)) : null;

                            if (!input.value || String(input.value).trim() === "") {
                                input.value = serverText;
                                try {
                                    row.update({
                                        rubrique_tva_code: serverCode,
                                        rubrique_tva: serverText,
                                        compte_tva: serverCompteTva,
                                        rubrique_tva_taux: serverTaux
                                    });
                                    // recalcul si besoin (si débit déjà présent)
                                    try { calculerCredit(row.getData(), 'ligne1', parseFloat(row.getData().debit || 0)); } catch(e) {}
                                } catch (e) {
                                    console.warn("Impossible d'update row lors du préfill:", e);
                                }
                            } else {
                                // ne pas écraser texte, mais renseigner code/compte/taux si absent
                                const d = row.getData();
                                const updates = {};
                                if (!d.rubrique_tva_code && serverCode) updates.rubrique_tva_code = serverCode;
                                if (!d.compte_tva && serverCompteTva) updates.compte_tva = serverCompteTva;
                                if ((!d.rubrique_tva_taux || d.rubrique_tva_taux === null) && serverTaux !== null) updates.rubrique_tva_taux = serverTaux;
                                if (Object.keys(updates).length) {
                                    try { row.update(updates); } catch(e) {}
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Erreur AJAX getRubriqueSociete dans editor:', err);
                        });
                })();

                function commitValue() {
                    const v = input.value ?? "";
                    try {
                        if (!v || String(v).trim() === "") {
                            row.update({ rubrique_tva: "", rubrique_tva_code: null, rubrique_tva_taux: null, compte_tva: "" });
                        } else {
                            // si on a la liste 'rubriques' du serveur, on tente de matcher par 'value' ou 'text'
                            const updates = { rubrique_tva: v };
                            if (lastServerResp && Array.isArray(lastServerResp.rubriques)) {
                                const found = lastServerResp.rubriques.find(r => String(r.value) === String(v) || String((r.text||'').toLowerCase()) === String(v).toLowerCase() || (String(r.text||'').toLowerCase().indexOf(String(v).toLowerCase()) !== -1));
                                if (found) {
                                    updates.rubrique_tva_code = found.value ?? updates.rubrique_tva_code;
                                    updates.compte_tva = found.compte_tva ?? updates.compte_tva;
                                    // si taux exposé dans les items, essaye de l'utiliser (sinon top-level resp.taux peut être utilisé)
                                    if (typeof found.taux !== 'undefined' && found.taux !== null) {
                                        updates.rubrique_tva_taux = (Number(found.taux) > 1 ? Number(found.taux) / 100 : Number(found.taux));
                                    } else if (typeof lastServerResp.taux !== 'undefined') {
                                        updates.rubrique_tva_taux = (Number(lastServerResp.taux) > 1 ? Number(lastServerResp.taux) / 100 : Number(lastServerResp.taux));
                                    }
                                } else {
                                    // si pas trouvé, mais top-level selected existe => l'utiliser
                                    if (lastServerResp.selected) {
                                        updates.rubrique_tva_code = lastServerResp.selected;
                                        updates.compte_tva = lastServerResp.compte_tva ?? updates.compte_tva;
                                        if (typeof lastServerResp.taux !== 'undefined') updates.rubrique_tva_taux = (Number(lastServerResp.taux) > 1 ? Number(lastServerResp.taux) / 100 : Number(lastServerResp.taux));
                                    }
                                }
                            } else if (lastServerResp) {
                                if (lastServerResp.selected) updates.rubrique_tva_code = lastServerResp.selected;
                                if (lastServerResp.compte_tva) updates.compte_tva = lastServerResp.compte_tva;
                                if (typeof lastServerResp.taux !== 'undefined') updates.rubrique_tva_taux = (Number(lastServerResp.taux) > 1 ? Number(lastServerResp.taux) / 100 : Number(lastServerResp.taux));
                            }

                            row.update(updates);
                        }
                    } catch (e) {
                        console.error('Erreur update row lors du commit:', e);
                    }
                    success(input.value);

                    // recalcul immédiat et redraw
                    try {
                        const d = row.getData();
                        calculerCredit(d, 'ligne1', parseFloat(d.debit || 0));
                        row.getTable().redraw(true);
                    } catch (e) {}
                }

                function cancelEdit() { cancel(); }

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') { e.preventDefault(); cancelEdit(); }
                    else if (e.key === 'Enter') { e.preventDefault(); commitValue(); }
                    else if (e.key === 'Tab') {
                        commitValue();
                        setTimeout(() => {
                            try {
                                const nextCell = typeof row.getNextCell === 'function' ? row.getNextCell(cell) : null;
                                if (nextCell && typeof nextCell.edit === 'function') nextCell.edit();
                            } catch (e) {}
                        }, 10);
                    }
                });

                input.addEventListener('blur', function(e) {
                    setTimeout(() => {
                        if (document.activeElement !== input) commitValue();
                    }, 120);
                });

                cell.getElement().addEventListener('destroyEditor', function() {
                    try { input.removeEventListener('keydown', this); } catch (e) {}
                }, { once: true });

                return input;
            }
                    },
                    { title: "Solde Cumulé",
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
                    { title: "Pièce justificative",
  field: "piece_justificative",
  width: 200,
  headerFilter: "input",
  formatter: function(cell) {
    var rowData = cell.getRow().getData(); // Données de la ligne complète
    var justificatif = cell.getValue() || ''; // Le champ "piece_justificative"
    var filePath = rowData.file?.path || ''; // Le chemin réel du fichier (file.path)

    // Champ texte avec gestion de la touche Entrée, sans id pour éviter doublons
    var input = "<input type='text' class='selected-file-input' value='" + justificatif + 
      "' onkeydown='if(event.key === \"Enter\") { " +
      "var cellElement = this.closest(\".tabulator-cell\");" +
      "var uploadIcon = cellElement.querySelector(\".upload-icon\");" +
      "if(uploadIcon) uploadIcon.focus();" +
      "}'>";

    // Icône œil (vue fichier)
    var iconView = filePath
      ? "<i class='fas fa-eye view-icon' title='Voir le fichier' tabindex='0' onclick='viewFile(\"" + filePath + "\")'></i>"
      : '';

    // Icône upload (trombone) sans id pour éviter doublons
    var iconUpload = "<i class='fas fa-paperclip upload-icon' id='upload-icon-ventes' data-action='open-modal' title='Choisir un fichier' tabindex='0'></i>";

    // Icône "eye" si justificatif vide
    var iconEye = justificatif === ''
      ? "<i class='fas fa-eye view-icon' title='Voir le fichier' tabindex='0' onclick='viewFile(null)'></i>"
      : '';

    return input + iconUpload + iconEye + iconView;
  },

  // Optionnel : clic sur la cellule déclenche aussi l'ouverture du fichier
  cellClick: function(e, cell) {
    var filePath = cell.getRow().getData().file?.path;
    currentPieceCellBanque = cell;
    if (filePath) viewFile(filePath);
  },
                    },
                    { title: "<input type='checkbox' id='master-select' title='Tout sélectionner / Tout désélectionner'>",
                      field: "selected",
                      width: 70,
                      hozAlign: "center",
                      headerSort: false,
                      headerFilter: false,
                      formatter: function (cell) {
                        const row = cell.getRow();
                        const data = row.getData();
                        const checked = row.isSelected() ? "checked" : "";
                        let html = `<input type='checkbox' class='select-row' ${checked}>`;

                        // croix rouge si ligne de saisie (tous champs principaux vides)
                        const isSaisie = !data.date && !data.date_livr && !data.compte && !data.libelle && !data.debit && !data.credit;
                        if (isSaisie) {
                          html += ` <span class='clear-row-btn' style='color:red;cursor:pointer;font-size:18px;' title='Vider la ligne'>&times;</span>`;
                        }

                        return html;
                      },
                      cellClick: function (e, cell) {
                        const row = cell.getRow();
                        const table = cell.getTable && cell.getTable();

                        // Helper : mettre à jour état master checkbox
                        const updateMasterCheckbox = () => {
                          try {
                            const allRows = table.getRows();
                            const selRows = table.getSelectedRows();
                            const master  = document.getElementById("master-select");
                            if (!master) return;
                            master.checked = allRows.length > 0 && selRows.length === allRows.length;
                          } catch (err) { /* silent */ }
                        };

                        // --- clic sur la croix => vider la ligne ---
                        if (e.target.classList.contains('clear-row-btn')) {
                          const emptyData = {};
                          Object.keys(row.getData()).forEach(function (key) {
                            if (['id','selected'].includes(key)) return;
                            emptyData[key] = '';
                          });
                          row.update(emptyData);
                          try { row.deselect && row.deselect(); } catch(e){}
                          // mise à jour master checkbox
                          updateMasterCheckbox();
                          return;
                        }

                        // --- clic sur la case à cocher => toggle Tabulator sélection ---
                        if (e.target.classList.contains('select-row')) {
                          if (row.isSelected()) {
                            row.deselect();
                          } else {
                            row.select();
                          }
                          // mise à jour master checkbox
                          updateMasterCheckbox();
                          return;
                        }

                        // --- clic ailleurs dans la cellule => toggleSelect aussi ---
                        // (pour conserver le comportement précédent)
                        try {
                          row.toggleSelect();
                          updateMasterCheckbox();
                        } catch (err) {
                          console.warn('toggleSelect error', err);
                        }

                        // --- ATTACH : master checkbox behavior (only once) ---
                        try {
                          if (!document._masterSelectAttached) {
                            document._masterSelectAttached = true;
                            const master = document.getElementById("master-select");
                            if (master) {
                              master.addEventListener('change', function () {
                                try {
                                  const rows = table.getRows();
                                  if (master.checked) {
                                    rows.forEach(r => r.select && r.select());
                                  } else {
                                    rows.forEach(r => r.deselect && r.deselect());
                                  }
                                  // NE PAS appeler enregistrerLignesVentes() ici (tu as demandé de ne pas sauver sur sélection)
                                } catch (err) { console.warn('master-select handler', err); }
                              });
                            } else {
                              // Si élément pas encore dans DOM (par ex rendu tardif), tenter une attache périodique courte
                              let tries = 0;
                              const tId = setInterval(() => {
                                tries++;
                                const m = document.getElementById("master-select");
                                if (m) {
                                  clearInterval(tId);
                                  m.addEventListener('change', function () {
                                    try {
                                      const rows = table.getRows();
                                      if (m.checked) rows.forEach(r => r.select && r.select());
                                      else rows.forEach(r => r.deselect && r.deselect());
                                    } catch (err) {}
                                  });
                                } else if (tries > 20) clearInterval(tId);
                              }, 100);
                            }
                          }
                        } catch (err) { console.warn('attach master-select', err); }

                        // --- ATTACH : écoute Enter sur la table (one-time) ---
                        try {
                          const tblEl = (table && typeof table.getElement === 'function') ? table.getElement() : null;
                          const targetEl = tblEl || document;
                          if (targetEl && !targetEl._enterListenerVentesAttached) {
                            targetEl._enterListenerVentesAttached = true;
                            targetEl.addEventListener('keydown', function (evt) {
                              // Ignore si focus dans un input/textarea/select (sauf si c'est la colonne où on veut intercepter)
                              const tag = (evt.target && evt.target.tagName) ? evt.target.tagName.toLowerCase() : null;
                              if (tag === 'input' || tag === 'textarea' || tag === 'select' || evt.target && evt.target.isContentEditable) {
                                // si tu veux que Enter depuis la pj-input déclenche l'enregistrement, adapte ici
                                return;
                              }
                              if (evt.key === 'Enter') {
                                try {
                                  // appel ecouterEntrerVentes si défini (peut faire traitements avant save)
                                  if (typeof ecouterEntrerVentes === 'function') {
                                    try { ecouterEntrerVentes(table); } catch(e){ console.warn('ecouterEntrerVentes error', e); }
                                  }
                                  // appel enregistrement
                                  if (typeof enregistrerLignesVentes === 'function') {
                                    try { enregistrerLignesVentes(); } catch(e){ console.warn('enregistrerLignesVentes error', e); }
                                  }
                                } catch (err) {
                                  console.error('Enter handler ventes error', err);
                                }
                              }
                            }, true); // capture pour attraper Enter tôt
                          }
                        } catch (err) { console.warn('attach Enter handler ventes', err); }

                      } // fin cellClick
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
 tableVentes.on("cellEdited", async function(cell) {
    const row = cell.getRow();
    const rowData = row.getData();
    const field = cell.getField();

    
    if (!rowData.id) {
        console.log("⚠️ Ligne sans id ignorée :", rowData);
        return;
    }

    console.log("🔄 Cellule modifiée, ligne :", rowData);

    try {
       
        const result = await Swal.fire({
            title: "Êtes-vous sûr ?",
            text: `Voulez-vous modifier le champ : "${field}" ?`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Oui",
            cancelButtonText: "Non",
            allowOutsideClick: false,
            allowEscapeKey: false
        });

        if (!result.isConfirmed) {
            console.log("❌ Modification annulée par l'utilisateur.");
            
            row.update({ [field]: cell.getOldValue() });
            return;
        }

        
        const response = await fetch("/achats/update-row", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(rowData)
        });

        const resp = await response.json();
        console.log("Réponse du serveur :", resp);

        if (resp.id) {
            row.update({ id: resp.id });
        }

        // await Swal.fire({
        //     icon: "success",
        //     title: "Modification enregistrée",
        //     text: `Le champ "${field}" a été mis à jour avec succès.`
            
        // });
    } catch (err) {
        console.error("Erreur update ligne :", err);
        await Swal.fire({
            icon: "error",
            title: "Erreur",
            text: "Impossible d'enregistrer la modification."
        });
    }
            updateTabulatorDataAchats();

});
////////////Mise a jour ventes////////////////////////:

/* ===== Navigation Enter entre filtres (zone #ventes) et intégration avec tableVentes =====
   - Place ce script après l'init de tableVentes
   - Si ta Tabulator s'appelle différemment, change la variable 'tableVentes'
*/

/* === Comportement spécial : Enter sur #periode-ventes ouvre édition sur la cellule 'date' === */
/* === Enter sur #periode-ventes => focus édition sur la colonne 'date' de la ligne vide === */
(function() {
  const container = document.querySelector("#ventes");
  if (!container) return;

  const focusableSelector = [
    "input:not([type=hidden])",
    "select",
    "button",
    "textarea",
  ].join(",");

  function isVisibleAndEnabled(el) {
    if (!el) return false;
    if (el.disabled) return false;
    const style = window.getComputedStyle(el);
    if (style.display === "none" || style.visibility === "hidden" || style.opacity === "0") return false;
    if (el.type === "hidden") return false;
    return true;
  }

  function gatherFilterElements() {
    return Array.from(container.querySelectorAll(focusableSelector)).filter(isVisibleAndEnabled);
  }

  function getIndex(el, arr) { return arr.indexOf(el); }

  function focusFirstTableEditableCell() {
    try {
      if (typeof tableVentes === "undefined") return;
      const rows = tableVentes.getRows();
      if (!rows || rows.length === 0) {
        tableVentes.addRow({}, true).then(r => {
          setTimeout(() => { try { r.getCell("date").edit(true); } catch (e) {} }, 60);
        }).catch(() => {});
        return;
      }
      focusFirstEditableCellInRow(rows[0]);
    } catch (e) {}
  }

  function focusFirstEditableCellInRow(rowComponent) {
    try {
      if (!rowComponent) return;
      const table = rowComponent.getTable();
      const cols = table.getColumns();
      for (let i = 0; i < cols.length; i++) {
        const col = cols[i];
        const def = col.getDefinition ? col.getDefinition() : {};
        const field = col.getField();
        if (!field) continue;
        if (typeof def.editor !== "undefined" && def.editor !== false) {
          const cell = rowComponent.getCell(field);
          if (cell) {
            setTimeout(() => { try { cell.edit(true); } catch (e) {} }, 30);
            return;
          }
        }
      }
    } catch (e) {}
  }

  function attachHandlers() {
    const elems = gatherFilterElements();
    elems.forEach(el => {
      el.removeEventListener("keydown", filterKeydownHandler);
      el.addEventListener("keydown", filterKeydownHandler);
    });
  }

  function filterKeydownHandler(e) {
    if (e.key === "Enter") {
      e.preventDefault();
      const elems = gatherFilterElements();
      const idx = getIndex(e.target, elems);
      if (idx === -1) return;
      if (e.shiftKey) {
        const prev = elems[idx - 1];
        if (prev) { prev.focus(); if (prev.select) try { prev.select(); } catch (err) {} }
        else e.target.blur();
      } else {
        const next = elems[idx + 1];
        if (next) {
          next.focus();
          if (next.tagName.toLowerCase() === "input" && next.select) try { next.select(); } catch (err) {}
        } else {
          // dernier filtre -> focus table (première cellule éditable de la première ligne)
          focusFirstTableEditableCell();
        }
      }
    } else if (e.key === "Escape") {
      e.target.blur();
    }
  }

  const observer = new MutationObserver(() => { attachHandlers(); });
  observer.observe(container, { childList: true, subtree: true });
  attachHandlers();

  // Comportement spécial : Enter sur #periode-ventes -> édition sur colonne 'date' de la ligne vide
  const periodeEl = document.getElementById("periode-ventes");
  function focusEmptyRowDate() {
    try {
      const rows = tableVentes.getRows();
      if (!rows || rows.length === 0) {
        tableVentes.addRow({}, true).then(r => {
          setTimeout(() => { try { r.getCell("date").edit(true); } catch (e) {} }, 50);
        }).catch(() => {});
        return;
      }

      let targetRow = null;
      for (let i = rows.length - 1; i >= 0; i--) {
        const data = rows[i].getData();
        // critère de "ligne vide": tous les champs null/"" ou absents
        const values = Object.values(data || {});
        const isEmpty = values.length === 0 || values.every(v => v === null || v === "");
        if (isEmpty) { targetRow = rows[i]; break; }
      }
      if (!targetRow) targetRow = rows[rows.length - 1];

      targetRow.scrollTo().then(() => {
        try { targetRow.getCell("date").edit(true); } catch (e) {}
      }).catch(() => {
        try { targetRow.getCell("date").edit(true); } catch (e) {}
      });
    } catch (err) {
      console.warn("focusEmptyRowDate error", err);
    }
  }

  function periodeKeyHandler(e) {
    if (e.key === "Enter") {
      e.preventDefault();
      // option: lancer applyVentesFilter() ici si tu veux recharger avant
      focusEmptyRowDate();
    } else if (e.key === "Escape") {
      e.target.blur();
    }
  }

  if (periodeEl) {
    periodeEl.removeEventListener("keydown", periodeKeyHandler);
    periodeEl.addEventListener("keydown", periodeKeyHandler);
  }

  // expose helper si besoin
  window.ventesHelpers = {
    focusEmptyRowDate,
    focusFirstTableEditableCell,
    refreshFiltersNav: attachHandlers,
  };
})();
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
    const numeroDossier = data.numero_dossier;

    // Préparer les données à envoyer sans `debit` et `credit`
    const updateData = {
        field: field,
        value: value,
        numero_facture: numeroFacture,
         numero_dossier: numeroDossier

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


document.addEventListener('change', function(e){
  try {
    if (!e.target || e.target.id !== 'master-select') return;
    const masterChecked = !!e.target.checked;

    // choisir la table cible : adapte si tu as plusieurs tables (tableAch, tableVente, ...)
    const table = (typeof tableAch !== 'undefined') ? tableAch : (typeof tableVente !== 'undefined' ? tableVente : null);
    if (!table || typeof table.getRows !== 'function') return;

    const allRows = table.getRows();
    // on veut sélectionner uniquement les lignes non vides (exclure "saisie")
    allRows.forEach(r => {
      try {
        const d = r.getData();
        const isSaisie = !d || (!d.date && !d.date_livr && !d.compte && !d.libelle && !d.debit && !d.credit);
        if (isSaisie) {
          // s'assurer que la checkbox de la ligne est décochée
          try { r.deselect && r.deselect(); } catch(e){}
          return;
        }
        if (masterChecked) {
          // select row and add to waiting list if not present
          try { r.select && r.select(); } catch(e){}
          try {
            window.rowsWaitingForFileAchat = window.rowsWaitingForFileAchat || [];
            const already = window.rowsWaitingForFileAchat.some(x => { try { return x.getIndex && r.getIndex && x.getIndex() === r.getIndex(); } catch(e){ return false; } });
            if (!already) window.rowsWaitingForFileAchat.push(r);
          } catch(e){}
        } else {
          // deselect and remove from waiting list
          try { r.deselect && r.deselect(); } catch(e){}
          try {
            window.rowsWaitingForFileAchat = (window.rowsWaitingForFileAchat || []).filter(x => {
              try { return !(x.getIndex && r.getIndex && x.getIndex() === r.getIndex()); } catch(e){ return true; }
            });
          } catch(e){}
        }
      } catch(err){ console.warn('master select each row', err); }
    });

    // recalc master checked state just in case
    try {
      const selectableRows = allRows.filter(r => {
        try {
          const d = r.getData();
          return !!(d && (d.date || d.date_livr || d.compte || d.libelle || d.debit || d.credit));
        } catch(e){ return false; }
      });
      const selected = table.getSelectedRows ? table.getSelectedRows() : [];
      const master = document.getElementById('master-select');
      if (master) master.checked = (selectableRows.length > 0 && selected.length === selectableRows.length);
    } catch(e){}

    // recalcul du solde cumulé si présent
    try { if (typeof calculerSoldeCumule === 'function') calculerSoldeCumule(); } catch(e){ console.warn(e); }

  } catch(e){ console.error('master-select handler error', e); }
});

document.querySelector("#journal-ventes").addEventListener("change", function (e) {
    // console.log(e.target("#journal-ventes").value);
    const selectedCode = e.target.value;
    const rubriquetva = e.target.options[e.target.selectedIndex].getAttribute('data-rubrique_tva') || '';
    console.log("Journal ventes changé :", rubriquetva);
    let ligneSelectionnee = tableVentes.getSelectedRows()[0];
    if (ligneSelectionnee) {
        ligneSelectionnee.update({ type_Journal: selectedCode });
    }
});

function getJournalContrePartie(codeJournal) {
  if (!codeJournal) {
    // fallback : utiliser l'option sélectionnée dans #journal-achats si présente
    const selFallback = document.querySelector('#journal-achats');
    if (selFallback && selFallback.selectedIndex >= 0) {
      const opt = selFallback.options[selFallback.selectedIndex];
      return opt?.dataset?.contre_partie ?? opt?.getAttribute('data-contre_partie') ?? opt?.dataset?.contrePartie ?? '';
    }
    return '';
  }

  const journalSelectors = [
    '#journal-achats',
    '#journal-ventes',
    '#journal-operations',
    '#journal-operations-diverses',
    'select[name="journal"]'
  ];

  for (const sel of journalSelectors) {
    try {
      const el = document.querySelector(sel);
      if (!el) continue;
      const match = Array.from(el.options).find(o => String(o.value) === String(codeJournal));
      if (match) {
        return match.dataset?.contre_partie ?? match.getAttribute('data-contre_partie') ?? match.dataset?.contrePartie ?? '';
      }
    } catch (e) {
      // silent fail -> continue
    }
  }

  return '';
}


function applyDefaultContrePartieToRow(rowObj, codeJournal) {
  if (!rowObj) return rowObj;
  try {
    const current = rowObj.contre_partie ?? rowObj.contrePartie ?? '';
    if (String(current).trim() === '') {
      rowObj.contre_partie = getJournalContrePartie(codeJournal) || '';
    }
  } catch (e) {
    rowObj.contre_partie = getJournalContrePartie(codeJournal) || '';
  }
  return rowObj;
}

// ---------- Helper global : applique la contre_partie par défaut à toutes les lignes d'une table Tabulator ----------
function applyDefaultContrePartieToTable(tabulatorInstance, codeJournal) {
  if (!tabulatorInstance) return;
  try {
    const defaultCp = getJournalContrePartie(codeJournal);
    // parcourir les rows et mettre à jour celles qui n'ont pas de contre_partie
    const rows = tabulatorInstance.getRows ? tabulatorInstance.getRows() : [];
    rows.forEach(row => {
      const data = row.getData();
      if (!data) return;
      const cur = data.contre_partie ?? data.contrePartie ?? '';
      if (!cur || String(cur).trim() === '') {
        try {
          row.update({ contre_partie: defaultCp || '' });
        } catch (e) {
          // fallback : reconstruire l'objet et updater via updateOrAddData
          const updated = { ...data, contre_partie: defaultCp || '' };
          try { tabulatorInstance.updateOrAddData([updated]); } catch (_) {}
        }
      }
    });
  } catch (e) {
    console.warn('applyDefaultContrePartieToTable error', e);
  }
}




async function recupererDetailsTVA(rubriqueTva, compte, rowComponentOrElement = null) {
  console.groupCollapsed("🔎 recupererDetailsTVA", { rubriqueTva, compte });
  let data;
  try {
    const res = await fetch("/get-fournisseurs-avec-details");
    if (!res.ok) {
      console.error("❌ Erreur HTTP :", res.status, res.statusText);
      console.groupEnd();
      return { taux_tva: 0, compte_tva: null, contre_partie: null };
    }
    data = await res.json();
  } catch (e) {
    console.error("❌ Impossible de fetch '/get-fournisseurs-avec-details' :", e);
    console.groupEnd();
    return { taux_tva: 0, compte_tva: null, contre_partie: null };
  }

  console.log("📦 fournisseursAvecDetails (total:", Array.isArray(data) ? data.length : 0, ")");
  (Array.isArray(data) ? data : []).forEach((f, idx) => {
    const fCode = f.rubrique_tva
      ? f.rubrique_tva.split(":")[0].replace(/[^\d]/g, "").trim()
      : "";
    console.log(
      `  [${idx}] compte="${f.compte}", rubrique_tva="${f.rubrique_tva}", extrait="${fCode}", ` +
      `taux_tva="${f.taux_tva}", compte_tva="${f.compte_tva}", contre_partie="${f.contre_partie}"`
    );
  });

  // 1) match par rubriqueTva si fourni (prioritaire)
  if (rubriqueTva) {
    const codeWanted = rubriqueTva.split(":")[0].replace(/[^\d]/g, "").trim();
    console.log("🔍 codeWanted extrait de rubriqueTva:", codeWanted);
    if (codeWanted) {
      for (const f of data) {
        const fCode = f.rubrique_tva
          ? f.rubrique_tva.split(":")[0].replace(/[^\d]/g, "").trim()
          : "";
        if (fCode === codeWanted) {
          console.log("✅ match exact num_racines ->", f);
          console.groupEnd();
          return {
            taux_tva: parseFloat(f.taux_tva) || 0,
            compte_tva: f.compte_tva || null,
            contre_partie: f.contre_partie || null, // priorité fournisseur
          };
        }
      }
      console.warn(`⚠️ Aucun fournisseur trouvé avec num_racines="${codeWanted}"`);
    } else {
      console.warn("⚠️ Impossible d’extraire un code numérique de rubriqueTva:", rubriqueTva);
    }
  }

  // 2) fallback : match par compte fournisseur
  const matchByCompte = (Array.isArray(data) ? data : []).find((f) => String(f.compte) === String(compte));
  if (matchByCompte) {
    console.log("ℹ️ match par compte fournisseur ->", matchByCompte);
    // Si le fournisseur a une contre_partie définie -> PRIORITAIRE, on l'utilise et on renvoie directement.
    if (matchByCompte.contre_partie && String(matchByCompte.contre_partie).trim() !== "") {
      console.log("✅ fournisseur fournit une contre_partie prioritaire ->", matchByCompte.contre_partie);
      // injection locale si RowComponent fourni
      try {
        if (rowComponentOrElement && typeof rowComponentOrElement.getData === 'function') {
          const rc = rowComponentOrElement;
          const current = rc.getData ? rc.getData() : null;
          if (current) await rc.update({ ...current, contre_partie: matchByCompte.contre_partie });
        } else if (rowComponentOrElement instanceof Element) {
          const el = rowComponentOrElement;
          const sel = el.querySelector('select[name="contre_partie"], input[name="contre_partie"], select.contre_partie, .contre_partie');
          if (sel) { sel.value = matchByCompte.contre_partie; sel.dispatchEvent(new Event('change', { bubbles: true })); }
          else if ((el.tagName === 'SELECT' || el.tagName === 'INPUT') && (el.name === 'contre_partie' || el.id === 'contre_partie')) {
            el.value = matchByCompte.contre_partie; el.dispatchEvent(new Event('change', { bubbles: true }));
          }
        } else {
          const globalSel = document.querySelector('select[name="contre_partie"], input[name="contre_partie"], #contre_partie, select#contre_partie');
          if (globalSel) { globalSel.value = matchByCompte.contre_partie; globalSel.dispatchEvent(new Event('change', { bubbles: true })); }
        }
      } catch (injectErr) { console.warn("Erreur injection contre_partie fournisseur", injectErr); }

      console.groupEnd();
      return {
        taux_tva: parseFloat(matchByCompte.taux_tva) || 0,
        compte_tva: matchByCompte.compte_tva || null,
        contre_partie: matchByCompte.contre_partie || null,
      };
    }

    // Si matchByCompte trouvé mais sans contre_partie -> continuer fallback vers code_journal
    console.log("ℹ️ matchByCompte trouvé mais sans contre_partie -> fallback vers code_journal");
  }

  // 3) Aucun résultat prioritaire -> appeler backend /get-contre-parties?code_journal=...
  try {
    const journalSelect = document.querySelector('#journal-achats');
    const codeJournal = (journalSelect?.value || '').toString().trim();

    if (codeJournal) {
      const url = `/get-contre-parties?code_journal=${encodeURIComponent(codeJournal)}`;
      console.log("ℹ️ fetch fallback /get-contre-parties pour codeJournal:", codeJournal);
      try {
        const res2 = await fetch(url, { method: 'GET', credentials: 'same-origin' });
        if (res2.ok) {
          const contreArray = await res2.json(); // tableau attendu
          if (Array.isArray(contreArray) && contreArray.length > 0) {
            const firstNonEmpty = contreArray.find(v => v !== null && String(v).trim() !== '') || null;
            if (firstNonEmpty) {
              console.log("✅ contre_partie récupérée depuis /get-contre-parties ->", firstNonEmpty);

              // injection directe dans la ligne/select (si row fourni)
              try {
                if (rowComponentOrElement && typeof rowComponentOrElement.getData === 'function') {
                  const rc = rowComponentOrElement;
                  const current = rc.getData ? rc.getData() : null;
                  if (current) await rc.update({ ...current, contre_partie: firstNonEmpty });
                } else if (rowComponentOrElement instanceof Element) {
                  const el = rowComponentOrElement;
                  const sel = el.querySelector('select[name="contre_partie"], input[name="contre_partie"], select.contre_partie, .contre_partie');
                  if (sel) { sel.value = firstNonEmpty; sel.dispatchEvent(new Event('change', { bubbles: true })); }
                  else if ((el.tagName === 'SELECT' || el.tagName === 'INPUT') && (el.name === 'contre_partie' || el.id === 'contre_partie')) {
                    el.value = firstNonEmpty; el.dispatchEvent(new Event('change', { bubbles: true }));
                  }
                } else {
                  const globalSel = document.querySelector('select[name="contre_partie"], input[name="contre_partie"], #contre_partie, select#contre_partie');
                  if (globalSel) { globalSel.value = firstNonEmpty; globalSel.dispatchEvent(new Event('change', { bubbles: true })); }
                }
              } catch (injectErr) {
                console.warn("Erreur lors de l'injection de la contre_partie dans la ligne:", injectErr);
              }

              console.groupEnd();
              return { taux_tva: 0, compte_tva: null, contre_partie: firstNonEmpty };
            }
          } else {
            console.warn("ℹ️ /get-contre-parties a retourné un tableau vide ou invalide :", contreArray);
          }
        } else {
          console.warn("ℹ️ /get-contre-parties réponse non OK :", res2.status, res2.statusText);
        }
      } catch (e2) {
        console.warn("❌ Erreur fetch /get-contre-parties :", e2);
      }
    } else {
      console.warn("⚠️ Aucun code_journal sélectionné dans #journal-achats");
    }
  } catch (e) {
    console.warn("recover journal contre_partie error", e);
  }

  // 4) Aucun résultat final
  console.warn("❌ Aucun taux_tva/compte_tva/contre_partie trouvé pour rub/compte:", { rubriqueTva, compte });
  console.groupEnd();
  return { taux_tva: 0, compte_tva: null, contre_partie: null };
}



// async function calculerDebit(rowData, useAPIMethod = false) {
//   console.groupCollapsed("📌 calculerDebit début", {
//     numero_facture: rowData.numero_facture,
//     compte: rowData.compte,
//     rubrique_tva: rowData.rubrique_tva,
//   });
//   console.log("▶ rowData complet :", rowData);

//   // 1) Lecture du crédit TTC
//   const credit = parseFloat(rowData.credit);
//   if (!Number.isFinite(credit)) {
//     console.warn("❌ Crédit invalide :", rowData.credit);
//     console.groupEnd();
//     return;
//   }
//   console.log("🔢 Crédit =", credit);

//   // 2) Si prorata, récupérer la valeur depuis le back
//   const isProrata = (rowData.prorat_de_deduction || "").toString().toLowerCase() === "oui";
//   let prorata = 0;
//   if (isProrata) {
//     try {
//       const prResp = await fetch("/get-session-prorata", { credentials: 'same-origin' });
//       const pr = await prResp.json();
//       prorata = parseFloat(pr.prorata_de_deduction) || 0;
//       console.log("ℹ️ Prorata récupéré =", prorata, "%");
//     } catch (e) {
//       console.error("❌ Erreur récupération prorata :", e);
//       prorata = 0;
//     }
//   }

//   // 3) On découpe la chaîne rubrique_tva en sous-codes (au cas où plusieurs)
//   const items = (rowData.rubrique_tva || "")
//     .split(/[;,\/]/)
//     .map((s) => s.trim())
//     .filter(Boolean);
//   console.log("🔍 Rubriques extraites :", items);

//   // Helper arrondi (2 décimales)
//   const round2 = (v) => Math.round((Number(v) + Number.EPSILON) * 100) / 100;

//   // ── Cas 1 seule rubrique ─────────────────────────────────────────────
//   if (items.length === 1) {
//     const code = items[0];
//     console.log("➡️ Cas simple pour rubrique :", code);

//     const { taux_tva, compte_tva, contre_partie } = await recupererDetailsTVA(code, rowData.compte);
//     console.log("📊 Détails TVA récupérés :", { taux_tva, compte_tva, contre_partie });

//     // Calcul base HT & TVA
//     const tauxNum = parseFloat(taux_tva) || 0;
//     const baseHT = tauxNum === 0 ? credit : credit / (1 + tauxNum / 100);
//     const montantTVA_total = round2(baseHT * (tauxNum / 100));
//     // si prorata => TVA déductible
//     const montantTVA_deductible = isProrata ? round2(montantTVA_total * (prorata / 100)) : montantTVA_total;

//     // ligne TVA (même si compte_tva == null, on génère la ligne avec compte vide)
//     rowData.lignesTVA = [
//       {
//         compte_tva: compte_tva || "",
//         taux_tva: tauxNum,
//         debit_tva: montantTVA_deductible,
//         montant_tva_total: montantTVA_total, // info utile si tu veux tracer la TVA non-déductible
//       },
//     ];

//     // Ligne charge HT (débit = TTC - TVA déductible)
//     rowData.debit_contrepartie = round2(credit - montantTVA_deductible);
//     rowData.compte_debit_charge = contre_partie || null;

//     console.log("✅ Ligne chargeHT : compte =", rowData.compte_debit_charge, "débit =", rowData.debit_contrepartie);
//     console.log("✅ Ligne TVA      : compte =", compte_tva || "(vide)", "debit_tva =", montantTVA_deductible);
//     console.groupEnd();
//     return;
//   }

//   // ── Cas plusieurs rubriques ───────────────────────────────────────────
//   console.log("➡️ Cas multiple pour rubriques :", items);

//   // Si tu as une répartition définie (rowData.repartition = [0.6,0.4] ou montants),
//   // on l'utilisera. Sinon on répartit TTC également entre les rubriques.
//   let repartition = null;
//   if (Array.isArray(rowData.repartition) && rowData.repartition.length === items.length) {
//     // si totaux relatifs (somme != 1) on normalise pour obtenir proportions
//     const sumParts = rowData.repartition.reduce((s, v) => s + Number(v || 0), 0) || 0;
//     if (sumParts > 0) {
//       repartition = rowData.repartition.map(v => Number(v || 0) / sumParts);
//     }
//   }
//   if (!repartition) {
//     repartition = new Array(items.length).fill(1 / items.length); // distribution égale
//   }
//   console.log("ℹ️ Répartition utilisée :", repartition);

//   const lignesTVA = [];
//   let sumDebitTVA = 0;
//   // stocker contre_partie prioritaire si trouvée
//   let firstContrePartie = null;

//   for (let i = 0; i < items.length; i++) {
//     const rub = items[i];
//     const share = repartition[i];
//     // Montant TTC alloué à cette rubrique
//     const ttcShare = round2(credit * share);
//     console.log(`  • Rubrique ${rub} => TTC part = ${ttcShare} (share=${share})`);

//     const { taux_tva, compte_tva, contre_partie } = await recupererDetailsTVA(rub, rowData.compte);
//     const tauxNum = parseFloat(taux_tva) || 0;
//     console.log("    Détails reçus :", { taux_tva: tauxNum, compte_tva, contre_partie });

//     // garder première contre_partie non vide comme compte d'achat (si existant)
//     if (!firstContrePartie && contre_partie) firstContrePartie = contre_partie;

//     // calcul baseHT et TVA pour cette part
//     const baseHT = tauxNum === 0 ? ttcShare : ttcShare / (1 + tauxNum / 100);
//     const montantTVA_total = round2(baseHT * (tauxNum / 100));
//     const montantTVA_deductible = isProrata ? round2(montantTVA_total * (prorata / 100)) : montantTVA_total;

//     lignesTVA.push({
//       compte_tva: compte_tva || "",
//       taux_tva: tauxNum,
//       debit_tva: montantTVA_deductible,
//       montant_ttc_part: ttcShare,
//       montant_tva_total: montantTVA_total,
//     });

//     sumDebitTVA = round2(sumDebitTVA + montantTVA_deductible);
//   }

//   // Calcul du debit_contrepartie (HT total) = TTC total - somme TVA déductible
//   let debitContre = round2(credit - sumDebitTVA);

//   // Correction d'arrondi : s'il y a un écart minime, on l'applique sur la première ligne HT (non TVA)
//   // Ici on ne manipule pas explicitement les lignes HT dans rowData (car ajouterLigne() créera HT),
//   // mais on met à jour debit_contrepartie pour être cohérent.
//   // Si tu veux, on peut ajuster également la première ligneTVA ou créer des HT détaillées.
//   // Enregistrement des résultats
//   rowData.lignesTVA = lignesTVA;
//   rowData.debit_contrepartie = debitContre;
//   rowData.compte_debit_charge = firstContrePartie || null;

//   console.log("🔢 Somme TVA (déductible) =", sumDebitTVA.toFixed(2));
//   console.log("✅ Ligne chargeHT :", rowData.compte_debit_charge || "(vide)", "débit =", rowData.debit_contrepartie);
//   console.groupEnd();
// }





function getSelectedCodeJournal() {
  const selectors = [
    "#journal-achats",
    "#journal-ventes",
    "#journal-operations-diverses",
  ];
  for (const sel of selectors) {
    const el = document.querySelector(sel);
    if (el && el.value && el.value.trim() !== "") {
      return el.value.trim().toUpperCase();
    }
  }
  return "CJ";
}



/**
 * Parse une date de façon tolérante (Luxon) et retourne un DateTime valide.
 */
function parseDateSafe(dateStr) {
    let s = String(dateStr || "").trim();
    let dt = luxon.DateTime.fromISO(s);
    if (!dt.isValid) dt = luxon.DateTime.fromFormat(s, "yyyy-MM-dd HH:mm:ss");
    if (!dt.isValid) dt = luxon.DateTime.fromFormat(s, "yyyy-MM-dd");
    if (!dt.isValid) dt = luxon.DateTime.fromFormat(s, "dd/LL/yyyy");
    if (!dt.isValid) dt = luxon.DateTime.local(); // fallback : aujourd'hui
    return dt;
}



// ------------------------ Bloquer submit sur Enter ------------------------
const formEl = tableAch && tableAch.element ? tableAch.element.closest('form') : null;
if (formEl) {
    formEl.addEventListener("keydown", function(e) {
        if (e.key === "Enter" && e.target.closest && e.target.closest(".tabulator-cell")) {
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);
}

// ------------------------ Affiche la pièce sur Enter colonne date facture------------------------
(function () {
  const tab = typeof tableAch !== 'undefined' ? tableAch : null;
  if (!tab) return;

  tab.on("cellEdited", function (cell) {
    try {
      if (cell.getField() !== 'date') return;

      const row = cell.getRow();
      const rowData = row.getData();
      const dateValue = rowData.date;

      // récupère code journal
      const sel = document.querySelector("#journal-achats");
      const codeJournal = sel ? String(sel.value || '').trim().toLowerCase() : 'jr';

      // nettoie et parse les soldes (si tu veux garder la logique, sinon on ignore)
      const parseAmount = s => {
        if (s === null || s === undefined) return 0;
        let t = String(s).trim();
        t = t.replace(/\u00A0/g, '').replace(/\s/g, '').replace(/€/g, '').replace(/\u20AC/g, '');
        if (/[.,]/.test(t)) {
          const lastComma = t.lastIndexOf(',');
          const lastDot = t.lastIndexOf('.');
          if (lastComma > lastDot) t = t.replace(/\./g, '').replace(',', '.');
          else t = t.replace(/,/g, '');
        }
        const n = Number(t);
        return Number.isFinite(n) ? n : 0;
      };
      const Cachats = parseAmount((document.getElementById('solde-credit-achats') || { textContent: '0' }).textContent);
      const Dachats = parseAmount((document.getElementById('solde-debit-achats') || { textContent: '0' }).textContent);

      // parse date safely
      let MM = null, YY = null;
      if (typeof parseDateSafe === 'function') {
        try {
          const maybe = parseDateSafe(dateValue);
          if (maybe && typeof maybe.toFormat === 'function') {
            MM = maybe.toFormat('MM');
            YY = maybe.toFormat('yy');
          }
        } catch (e) { /* ignore */ }
      }
      if (MM === null || YY === null) {
        const jsDate = new Date(dateValue);
        if (isNaN(jsDate.getTime())) {
          console.debug('cellEdited: date invalide', dateValue);
          return;
        }
        MM = String(jsDate.getMonth() + 1).padStart(2,'0');
        YY = String(jsDate.getFullYear()).slice(-2);
      }

      // prefix basé sur la date + journal (ex P1025m5 -> P + MM + YY + codeJournal)
      const prefixFromDate = `P${MM}${YY}${codeJournal}`;

      // collecte toutes les pièces existantes visibles (table rows)
      const allRows = tab.getRows() || [];
      const pieces = allRows
        .map(r => {
          try { const v = r.getData() && r.getData().piece_justificative ? String(r.getData().piece_justificative).trim() : null; return v; }
          catch (e) { return null; }
        })
        .filter(Boolean);

      // calcule le max suffixe uniquement POUR CE prefixFromDate
      let maxForPrefix = 0;
      pieces.forEach(p => {
        if (!p.startsWith(prefixFromDate)) return;
        const suffix = p.slice(prefixFromDate.length);
        // suffix peut être "0001" ou "1" etc -> on garde uniquement digits
        const m = suffix.match(/^0*(\d+)$/);
        if (m) {
          const num = parseInt(m[1], 10);
          if (!Number.isNaN(num) && num > maxForPrefix) maxForPrefix = num;
        }
      });

      // decide next number
      const nextNum = maxForPrefix + 1; // si maxForPrefix == 0 -> nextNum = 1

      // si la ligne est nouvelle (pas d'id) ou piece vide -> on génère nouvelle pièce incrémentée
      const isNewRow = !rowData.id; // adapte si tu utilises un flag __isTemp : (!rowData.id || rowData.__isTemp)
      const currentPiece = rowData.piece_justificative ? String(rowData.piece_justificative).trim() : '';

      let newPiece = '';
      if (isNewRow || !currentPiece) {
        // toujours incrémenter pour une nouvelle ligne ou si pièce vide
        newPiece = `${prefixFromDate}${String(nextNum).padStart(4,'0')}`;
      } else {
        // ligne existante : on garde sa piece si elle ne doit pas changer
        // si toutefois tu veux forcer réassignation en fonction de date, décommente la ligne ci-dessous
        // newPiece = `${prefixFromDate}${String(nextNum).padStart(4,'0')}`;
        newPiece = currentPiece;
      }

      // Mise à jour de la ligne (seulement si la nouvelle piece diffère)
      if (newPiece && newPiece !== currentPiece) {
        row.update({ piece_justificative: newPiece });
      }

    } catch (err) {
      console.error('Erreur dans cellEdited handler (pièce justificative):', err);
    }
  });
})();




// id temporaire / client_ref

function genClientRef() {
  return 'client_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2,8);
}

// format date pour le serveur : 'YYYY-MM-DD HH:mm:ss'
function formatDateForServer(d) {
  if (!d) return null;
  const dt = (d instanceof Date) ? d : new Date(d);
  if (isNaN(dt)) return null;
  const pad = n => String(n).padStart(2, '0');
  return `${dt.getFullYear()}-${pad(dt.getMonth()+1)}-${pad(dt.getDate())} ${pad(dt.getHours())}:${pad(dt.getMinutes())}:${pad(dt.getSeconds())}`;
}

// util : vérifie s'il existe déjà une ligne "vide" ou temporaire
// --------- 1) hasEmptyOrTempRow (corrigée) ----------
function hasEmptyOrTempRow(table) {
  if (!table) return false;
  let rows = [];
  try {
    rows = (typeof table.getRows === 'function') ? table.getRows() : [];
  } catch (e) {
    console.warn("hasEmptyOrTempRow: impossible d'obtenir les rows", e);
    return false;
  }

  for (const r of rows) {
    try {
      const d = (typeof r.getData === 'function') ? r.getData() : (r || {});
      if (!d || typeof d !== 'object') continue;

      // Cas explicite : flag __isEmptyRow
      if (d.__isEmptyRow) return true;

      // Considère "vide" seulement si compte vide ET montants nuls ET pas de numéro_facture/libelle
      const compteVide = !d.compte || String(d.compte).trim() === '';
      const debitZero = Math.abs(Number(d.debit || 0)) < 0.0001;
      const creditZero = Math.abs(Number(d.credit || 0)) < 0.0001;
      const noMeta = !d.libelle && !d.numero_facture;

      if (compteVide && debitZero && creditZero && noMeta) return true;
    } catch (e) {
      // ignore row parse errors
    }
  }
  return false;
}

function makeTempId() {
  return `temp_${Date.now()}_${Math.random().toString(36).slice(2,6)}`;
}

// ----------------- AJOUTS : helper pour préfill et équilibrage -----------------

// Retourne la RowComponent précédente qui contient des données significatives
function getPreviousFilledRow(table, currentRow) {
  try {
    const all = table.getRows ? table.getRows() : [];
    if (!all || all.length === 0) return null;
    let idx = null;
    try { idx = currentRow.getIndex ? currentRow.getIndex() : null; } catch(e){ idx = null; }

    if (idx === null) {
      // fallback: trouver par référence
      for (let i = all.length - 1; i >= 0; i--) {
        if (all[i] === currentRow) return i > 0 ? all[i - 1] : null;
      }
      return null;
    }
    for (let i = idx - 1; i >= 0; i--) {
      const r = all[i];
      try {
        const d = r.getData();
        const hasValue = d && (String(d.compte || '').trim() !== '' || Math.abs(Number(d.debit || 0)) > 0 || Math.abs(Number(d.credit || 0)) > 0 || d.numero_facture || d.libelle);
        if (hasValue) return r;
      } catch (e) { /* ignore */ }
    }
    return null;
  } catch (e) {
    console.warn('getPreviousFilledRow error', e);
    return null;
  }
}

// Pré-remplissage depuis la ligne précédente sur triggers spécifiques
async function prefillFromPreviousIfWanted(table, rowToProcess, activeField) {
  const fieldsTrigger = ['date','date_livr','numero_facture','libelle'];
  if (!fieldsTrigger.includes(activeField)) return;

  const prev = getPreviousFilledRow(table, rowToProcess);
  if (!prev) return;
  const prevData = prev.getData();
  if (!prevData) return;

  const currentData = rowToProcess.getData();
  const newValues = {};
  if (prevData.date && (!currentData.date || String(currentData.date).trim() === '')) newValues.date = prevData.date;
  if (prevData.date_livr && (!currentData.date_livr || String(currentData.date_livr).trim() === '')) newValues.date_livr = prevData.date_livr;
  if (prevData.numero_facture && (!currentData.numero_facture || String(currentData.numero_facture).trim() === '')) newValues.numero_facture = prevData.numero_facture;
  if (prevData.libelle && (!currentData.libelle || String(currentData.libelle).trim() === '')) newValues.libelle = prevData.libelle;

  if ((!currentData.compte || String(currentData.compte).trim() === '') && prevData.compte) {
    newValues.compte = prevData.compte;
  }
  if ((!currentData.contre_partie || String(currentData.contre_partie).trim() === '') && prevData.contre_partie) {
    newValues.contre_partie = prevData.contre_partie;
  }

  if (Object.keys(newValues).length > 0) {
    try {
      await rowToProcess.update({ ...currentData, ...newValues });
    } catch (e) { console.warn('prefillFromPreviousIfWanted: update failed', e); }
  }
}

// calcule totaux debit/credit d'un tableau de RowComponent
function computeGroupTotals(rowComponents) {
  let totalDebit = 0, totalCredit = 0;
  for (const r of rowComponents) {
    try {
      const d = r.getData();
      if (!d) continue;
      totalDebit += Number(d.debit || 0);
      totalCredit += Number(d.credit || 0);
    } catch(e){}
  }
  return { totalDebit, totalCredit };
}

// Remplace la fonction addBalancingRow existante par celle-ci
async function addBalancingRow(table, groupRows, imbalance, codeJournal, referenceRowData) {
  const isPositive = imbalance > 0;
  const amount = Math.abs(Number(imbalance));
  if (amount < 0.0001) return null;

  const refCompte = referenceRowData?.contre_partie || referenceRowData?.compte || '';
  const refContre = referenceRowData?.compte || referenceRowData?.contre_partie || '';

  // --- AJOUT : utiliser le même libellé et la même pièce justificative que la ligne de référence (si présents) ---
  const libelleRef = (referenceRowData && referenceRowData.libelle)
    ? referenceRowData.libelle
    : (referenceRowData && referenceRowData.description)
      ? referenceRowData.description
      : `Ajustement automatique (${isPositive ? 'C' : 'D'})`;

  const pieceJustifRef = (referenceRowData && referenceRowData.piece_justificative)
    ? referenceRowData.piece_justificative
    : (referenceRowData && referenceRowData.pieceJustificative)
      ? referenceRowData.pieceJustificative
      : '';

  const row = {
    id: makeTempId(),
    __isTemp: true,
    __client_ref: referenceRowData?.__client_ref || genClientRef(),
    client_ref: referenceRowData?.__client_ref || genClientRef(),
    compte: isPositive ? refCompte : refContre,
    contre_partie: isPositive ? refContre : refCompte,
    debit: isPositive ? 0 : amount,
    credit: isPositive ? amount : 0,
    type_journal: codeJournal,
    libelle: libelleRef,
    piece_justificative: pieceJustifRef, // <-- copie la même pièce justificative
    date: referenceRowData?.date || formatDateForServer(new Date()),
    date_livr: referenceRowData?.date_livr || formatDateForServer(new Date()),
    numero_facture: referenceRowData?.numero_facture || '',
  };

  try {
    const comp = await table.addRow(row, false);
    return comp;
  } catch(e) {
    console.warn('addBalancingRow:addRow failed', e);
    return null;
  }
}



// Boucle d'équilibrage automatique (mode libre). Retourne true si équilibré.
async function balanceGroupWhileNeeded(table, groupRows, codeJournal) {
  const maxBalancingIterations = 12;
  let iter = 0;
  // on récupère directement les RowComponents actuels pour ce groupe à chaque itération
  while (iter < maxBalancingIterations) {
    const currentRows = groupRows.map(r => r); // shallow copy
    const totals = computeGroupTotals(currentRows);
    const imbalance = Number((totals.totalDebit || 0) - (totals.totalCredit || 0));
    if (Math.abs(imbalance) < 0.0001) return true;

    // choisir référence : dernière ligne non vide du groupe
    const ref = currentRows.slice().reverse().find(r => {
      const d = r.getData();
      return d && (d.compte || d.contre_partie || d.numero_facture || d.libelle);
    });
    const referenceData = ref ? ref.getData() : {};

    const added = await addBalancingRow(table, currentRows, imbalance, codeJournal, referenceData);
    if (added) {
      groupRows.push(added); // mettre à jour le tableau de référence
    } else {
      console.warn('balanceGroupWhileNeeded: impossible d ajouter ligne de contrepartie');
      return false;
    }
    iter++;
  }
  console.warn('balanceGroupWhileNeeded: max iterations atteinte, vérifie la logique');
  return false;
}


// --- Fin du fichier ---

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


// Écouteurs pour les filtres Achats (y compris la nouvelle radio)
document.getElementById("journal-achats").addEventListener("change", updateTabulatorDataAchats);
document.getElementById("periode-achats").addEventListener("change", updateTabulatorDataAchats);
document.getElementById("annee-achats").addEventListener("input", updateTabulatorDataAchats);
document.getElementById("filter-exercice-achats").addEventListener("change", updateTabulatorDataAchats);

// Chargement initial des données Achats
// updateTabulatorDataAchats();



// ----- Helper debounce (si tu n'en as pas déjà une) -----
function debounce(fn, wait) {
  let t;
  return function(...args) {
    clearTimeout(t);
    t = setTimeout(() => fn.apply(this, args), wait);
  };
}

// ----- Modal de conflit (Swal utilisé si présent) -----
async function showNumeroConflictModal(numero, resp) {
  const typeJournal = resp?.type_journal || '—';
  const periode = resp?.periode || '—';

  const html = `
    <div style="text-align:left">
      <p>Le numéro <strong>${numero}</strong> existe déjà dans le journal <strong>${typeJournal}</strong> (période ${periode}).</p>
      <p style="font-size: 12px; color: #666">Choisissez une action :</p>
    </div>
  `;

  if (window.Swal) {
    const result = await Swal.fire({
      title: 'Numéro facture en double',
      html,
      showCancelButton: true,
      confirmButtonText: 'Utiliser pour cette ligne',
      cancelButtonText: 'Ne pas l\'utiliser',
      icon: 'warning',
      focusConfirm: true,
      allowOutsideClick: false,
      toast: false,
      width: 520,
    });

    // Ici on gère le focus
    if (result.isConfirmed) {
      // FOCUS sur le champ "compte"
      document.getElementById('compte')?.focus();
      return { action: 'use' };
    } else {
      // FOCUS sur le champ "numero_facture"
      document.getElementById('numero_facture')?.focus();
      return { action: 'cancel' };
    }
  } else {
    const use = confirm(
      `Le numéro ${numero} existe déjà (type journal : ${typeJournal} / période : ${periode}).\nOK = Utiliser, Annuler = Ne pas utiliser.`
    );
    if (use) {
      document.getElementById('compte')?.focus();
      return { action: 'use' };
    } else {
      document.getElementById('numero_facture')?.focus();
      return { action: 'cancel' };
    }
  }
}

// ----- Fonction réutilisable d'attachement sur un Tabulator -----
(function attachNumeroFactureChecker() {
  function attachTo(tableInstance, opts = {}) {
    if (!tableInstance || !tableInstance.on) return console.warn('tableInstance non fourni');

    const oldValueByCell = new WeakMap();
    const filterGroupSelector = opts.filterGroupSelector || null; // ex '#filter-achats'
    const tableName = opts.name || 'table';

    function getFilterSelectionne() {
      let group = filterGroupSelector;
      if (!group) {
        if (tableName.toLowerCase().includes('ach')) group = '[name="filter-achats"]';
        else if (tableName.toLowerCase().includes('vente')) group = '[name="filter-ventes"]';
        else if (tableName.toLowerCase().includes('op')) group = '[name="filter-operations-diverses"]';
        else group = '[name="filter-achats"]';
      }
      const checked = document.querySelector(`${group}:checked`);
      return checked ? (checked.value || '') : '';
    }

    tableInstance.on('cellEditing', function(cell) {
      try {
        if (cell.getField && cell.getField() === 'numero_facture') {
          oldValueByCell.set(cell, cell.getValue());
        }
      } catch(e){}
    });

    const debouncedCheck = debounce(async function(cell) {
      try {
        if (!cell || (cell.getField && cell.getField() !== 'numero_facture')) return;

        const newNumero = (String(cell.getValue() || '')).trim();
        const oldNumero = oldValueByCell.get(cell);
        if (!newNumero || newNumero === String(oldNumero || '')) return;

        // récupérer infos UI
        const periodeEl = document.querySelector('#periode-achats') || document.querySelector('#periode') || document.querySelector('#periode-' + tableName);
        const periodeVal = periodeEl ? (periodeEl.value || periodeEl.dataset?.value || '') : '';
        const societeId = document.querySelector('#societe_id')?.value || null;

        const row = cell.getRow();
        const rowData = row ? row.getData() : {};
        const pieceJustificative = rowData?.piece_justificative || '';

        const cellEl = cell.getElement ? cell.getElement() : null;
        if (cellEl) cellEl.classList.add('checking-numero');

        // NOTE: on n'envoie PAS de paramètre "type_journal" / "codeJournal" :
        const params = {
          numero: newNumero,
          societe_id: societeId,
          exercice: (periodeVal.split('-')[1] || ''), // année si mm-yyyy
          piece_justificative: pieceJustificative,
          filtreSelectionne: getFilterSelectionne()
        };

        const q = new URLSearchParams(params).toString();
        const url = `/check-numero-facture?${q}`;

        const resp = await fetch(url, { method: 'GET', credentials: 'same-origin' })
                        .then(r => r.json())
                        .catch(err=>{
                          console.error('Erreur fetch check-numero-facture', err);
                          return { error: err.message || 'fetch error' };
                        });

        if (cellEl) cellEl.classList.remove('checking-numero');

        if (resp?.error) {
          console.warn('Erreur check numero:', resp.error);
          if (window.Swal)
            Swal.fire({ toast:true, position:'top-end', icon:'error', title:'Erreur vérification', text: resp.error, timer:2000, showConfirmButton:false });
          return;
        }

        if (resp.exists) {
          const result = await showNumeroConflictModal(newNumero, resp);

          if (result.action === 'cancel') {
            // rollback ancienne valeur et focus
            try { await cell.setValue(oldNumero); } catch(e){ try{ cell.edit(false); }catch(_){} }
            setTimeout(()=>{ try{ cell.edit(true); const el = cell.getElement?cell.getElement():null; if(el){ const inp = el.querySelector('input,textarea,select'); if(inp) inp.focus(); } }catch(e){} }, 30);
            return;
          }

          // si on choisit d'utiliser pour cette ligne uniquement
          try {
            await cell.setValue(newNumero);
            if (row) row.update({ __numero_conflict: false });
          } catch(e){ console.warn('Impossible setValue', e); }

          // remettre le focus sur la cellule pour continuer l'édition si besoin
          setTimeout(()=>{ try{ cell.edit(true); const el = cell.getElement?cell.getElement():null; if(el){ const inp = el.querySelector('input,textarea,select'); if(inp) inp.focus(); } }catch(e){} }, 30);
          return;
        } else {
          // pas de conflit
          if (row) row.update({ __numero_conflict: false });
        }

      } catch (err) {
        console.error('Erreur vérification numero_facture :', err);
      }
    }, 250);

    tableInstance.on('cellEdited', function(cell) {
      try {
        if (cell.getField && cell.getField() === 'numero_facture') {
          debouncedCheck(cell);
        }
      } catch(e){}
    });

    console.log(`✔ Vérification numéro facture activée pour ${tableName}`);
  }

  // Attacher automatiquement si ces variables existent :
  try { if (typeof tableAch !== 'undefined') attachTo(tableAch, { name:'tableAch', filterGroupSelector: '[name="filter-achats"]' }); } catch(e){}
  try { if (typeof tableVentes !== 'undefined') attachTo(tableVentes, { name:'tableVentes', filterGroupSelector: '[name="filter-ventes"]' }); } catch(e){}
  try { if (typeof tableOP !== 'undefined') attachTo(tableOP, { name:'tableOP', filterGroupSelector: '[name="filter-operations-diverses"]' }); } catch(e){}

  // Expose function si tu veux attacher manuellement
  window.attachNumeroFactureCheckerTo = attachTo;
})();


//////////////////gestion ventes//////////////////////////////////////////////////////////////////////////////////////

/* =======================
   Auto-fill contre_partie (remplit UNIQUEMENT si vide)
   - Pour tableVentes et tableAch
   - Coller APRES l'init Tabulator
   ======================= */

(function() {
  // options fixes : ne pas écraser (false)
  const OPTIONS = {
    overwriteExisting: false,   // IMPORTANT : ne pas écraser les valeurs déjà présentes
    applyTo: 'visible',         // 'visible' | 'all' | 'selected'
    matchJournalIfPresent: true // si la ligne contient un journal on vérifie la correspondance
  };

  // Lire data-contre_partie d'un select (support dataset & attribut)
  function readDataContrePartieFromSelect(selId, codeJournalFallback = null) {
    const sel = document.querySelector(selId);
    if (!sel) return null;
    let opt = null;

    if (codeJournalFallback && String(codeJournalFallback).trim() !== '') {
      opt = Array.from(sel.options).find(o => String(o.value).trim() === String(codeJournalFallback).trim()) || null;
    }
    if (!opt) opt = sel.options[sel.selectedIndex] || null;
    if (!opt) return null;

    const ds = opt.dataset ? (opt.dataset.contrePartie ?? opt.dataset.contre_partie) : undefined;
    const attr = opt.getAttribute ? (opt.getAttribute('data-contre_partie') ?? opt.getAttribute('data-contre-partie')) : undefined;
    const val = (ds !== undefined && ds !== null && String(ds).trim() !== '') ? ds
              : (attr !== undefined && attr !== null && String(attr).trim() !== '') ? attr
              : null;
    return val ? String(val).trim() : null;
  }

  // decide si on doit update : on ne touche pas si déjà rempli
  function shouldUpdateContreForRow(row, selectedJournalValue, opts = OPTIONS) {
    if (!row) return false;
    const data = row.getData ? row.getData() : {};
    const current = (data.contre_partie ?? data.contrePartie ?? "").toString().trim();

    if (!opts.overwriteExisting && current !== "") return false;

    if (opts.matchJournalIfPresent && selectedJournalValue) {
      const rowJournal = (data.type_journal ?? data.code_journal ?? data.journal ?? data.codejournal ?? "").toString().trim();
      if (rowJournal) {
        return rowJournal.toLowerCase() === String(selectedJournalValue).toLowerCase();
      }
    }

    return true;
  }

  function applyContreToRow(row, contrePartie) {
    if (!row || !contrePartie) return;
    try {
      // safe update (n'ouvre pas éditeur)
      row.update({ contre_partie: contrePartie });
    } catch (e) {
      console.error('applyContreToRow failed', e);
    }
  }

  function applyContreToTable(tableInstance, contrePartie, selectedJournalValue, opts = OPTIONS) {
    if (!tableInstance || !contrePartie) return;

    if (opts.applyTo === 'selected') {
      tableInstance.getSelectedRows().forEach(row => {
        if (shouldUpdateContreForRow(row, selectedJournalValue, opts)) applyContreToRow(row, contrePartie);
      });
      return;
    }

    if (opts.applyTo === 'all') {
      tableInstance.getRows().forEach(row => {
        if (shouldUpdateContreForRow(row, selectedJournalValue, opts)) applyContreToRow(row, contrePartie);
      });
      return;
    }

    // default: visible only
    tableInstance.getRows().forEach(row => {
      try {
        const el = row.getElement();
        const visible = !!(el && el.offsetParent !== null);
        if (!visible) return;
      } catch (e) {}
      if (shouldUpdateContreForRow(row, selectedJournalValue, opts)) applyContreToRow(row, contrePartie);
    });
  }

  // Main handler when a select changes
  function handleJournalSelectChange(selectId, tableInstance) {
    const select = document.querySelector(selectId);
    if (!select) return;
    const selectedVal = select.value ?? null;
    const contre = readDataContrePartieFromSelect(selectId, selectedVal);
    // debug
    console.log('[contre_partie] select', selectId, 'val=', selectedVal, 'contre=', contre);

    if (!contre) {
      // rien à appliquer
      return;
    }

    applyContreToTable(tableInstance, contre, selectedVal);
  }

  // Attach listeners (safe if tables are in global vars or window.*)
  function attachListeners() {
    // ventes
    const selV = document.querySelector('#journal-ventes');
    if (selV) {
      selV.addEventListener('change', function() {
        handleJournalSelectChange('#journal-ventes', window.tableVentes || tableVentes);
      });
      // trigger initial apply if value exists
      if (selV.value) handleJournalSelectChange('#journal-ventes', window.tableVentes || tableVentes);
    }

    // achats
    const selA = document.querySelector('#journal-achats');
    if (selA) {
      selA.addEventListener('change', function() {
        handleJournalSelectChange('#journal-achats', window.tableAch || tableAch);
      });
      if (selA.value) handleJournalSelectChange('#journal-achats', window.tableAch || tableAch);
    }

    // also prefill when user starts editing a cell or adds a row
    const tv = window.tableVentes || tableVentes;
    const ta = window.tableAch || tableAch;

    [tv, ta].forEach(t => {
      if (!t) return;
      t.on('cellEditing', function(cell) {
        if (!cell) return;
        if (cell.getField() === 'contre_partie') return;
        const selId = (t === tv) ? '#journal-ventes' : '#journal-achats';
        const select = document.querySelector(selId);
        const selectedVal = select ? select.value : null;
        const contre = readDataContrePartieFromSelect(selId, selectedVal);
        if (!contre) return;
        const row = cell.getRow();
        if (shouldUpdateContreForRow(row, selectedVal, OPTIONS)) applyContreToRow(row, contre);
      });

      t.on('rowAdded', function(row) {
        const selId = (t === tv) ? '#journal-ventes' : '#journal-achats';
        const select = document.querySelector(selId);
        const selectedVal = select ? select.value : null;
        const contre = readDataContrePartieFromSelect(selId, selectedVal);
        if (!contre) return;
        if (shouldUpdateContreForRow(row, selectedVal, OPTIONS)) applyContreToRow(row, contre);
      });

      t.on('dataLoaded', function() {
        const selId = (t === tv) ? '#journal-ventes' : '#journal-achats';
        const select = document.querySelector(selId);
        const selectedVal = select ? select.value : null;
        const contre = readDataContrePartieFromSelect(selId, selectedVal);
        if (!contre) return;
        applyContreToTable(t, contre, selectedVal);
      });
    });
  }

  // attach on DOM ready (if tables initialized after DOMContentLoaded this is still fine)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', attachListeners);
  } else {
    attachListeners();
  }

  // debug helpers
  window.applySelectedContreToVentes = function() { handleJournalSelectChange('#journal-ventes', window.tableVentes || tableVentes); };
  window.applySelectedContreToAchats = function() { handleJournalSelectChange('#journal-achats', window.tableAch || tableAch); };

})();



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
            tableVentes.addRow(ligne, false);
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

// ---------- Copier date -> date_livr et générer piece_justificative sur Enter (tableVentes) ----------
(function(){
  const tab = typeof tableVentes !== 'undefined' ? tableVentes : null;
  if (!tab) return;

  // utilitaire : parse une date en luxon.DateTime (retourne objet luxon)
  function parseDateSafe(value){
    if (!value) return luxon.DateTime.invalid("empty");
    let dt = luxon.DateTime.fromFormat(value, "yyyy-MM-dd HH:mm:ss");
    if (!dt.isValid) dt = luxon.DateTime.fromISO(value);
    if (!dt.isValid) dt = luxon.DateTime.fromFormat(value, "dd/LL/yyyy");
    return dt;
  }

  tab.on("cellEdited", function(cell){
    // On ne s'intéresse qu'à la colonne 'date'
    if (cell.getField() !== 'date') return;

    const row = cell.getRow();
    const rowData = row.getData();
    const dateValue = rowData.date; // valeur déjà normalisée par ton éditeur (yyyy-MM-dd HH:mm:ss)
    const dt = parseDateSafe(dateValue);

    // 1) Dupliquer la date dans date_livr si vide (laisser modifiable sinon)
    if (!rowData.date_livr || rowData.date_livr === "" || rowData.date_livr === null) {
      if (dt.isValid) {
        // stocke en même format que date (yyyy-MM-dd HH:mm:ss)
        row.update({ date_livr: dt.toFormat("yyyy-MM-dd HH:mm:ss") });
      }
    }
    // 2) Génération incrémentale de piece_justificative
    // Récupérer code journal (champ select ou input #journal-ventes)
    const sel = document.querySelector("#journal-ventes");
    const codeJournal = sel ? String(sel.value || '').trim().toLowerCase() : 'jr';

    // Totaux affichés (solde)
    let soldeCredit = 0;
    let soldeDebit = 0;
    const elCredit = document.getElementById('solde-credit-ventes');
    const elDebit  = document.getElementById('solde-debit-ventes');
    if (elCredit) soldeCredit = parseFloat(elCredit.textContent.replace(/[^0-9\-\.,]/g,'').replace(',','.')) || 0;
    if (elDebit)  soldeDebit  = parseFloat(elDebit.textContent.replace(/[^0-9\-\.,]/g,'').replace(',','.')) || 0;

    const allRows = tab.getRows();

    // Récupérer toutes les pièces existantes
    let maxNum = -1;
    let selectedPrefix = '';

    const pieces = allRows.map(r => r.getData().piece_justificative)
      .filter(p => typeof p === 'string' && p.trim() !== '');

    pieces.forEach(p => {
      // chercher suffixe de 4 chiffres à la fin, et capturer le préfixe
      const match = p.match(/^(.+?)(\d{4})$/);
      if (match) {
        const prefix = match[1];
        const num = parseInt(match[2], 10);
        if (num > maxNum) {
          maxNum = num;
          selectedPrefix = prefix;
        }
      }
    });

    // Si aucune pièce trouvée, selectedPrefix reste ''
    // Décider du nouveau numéro/prefix
    let newPiece = '';
    if (parseFloat(soldeCredit) === 0 && parseFloat(soldeDebit) === 0) {
      // cas où les soldes sont zéro -> comportement "normal"
      if (allRows.length === 1 || maxNum === -1) {
        // pas de pièce précédente: construire préfixe P + MM + YY + codeJournal
        if (dt.isValid) {
          const MM = dt.toFormat("MM");
          const YY = dt.toFormat("yy");
          const prefix = `P${MM}${YY}${codeJournal}`;
          newPiece = `${prefix}${String(1).padStart(4, '0')}`;
        } else {
          // fallback : prefix simple
          const prefix = `P${codeJournal}`;
          newPiece = `${prefix}${String(1).padStart(4, '0')}`;
        }
      } else {
        // incrémente le max existant
        const nextNum = (maxNum + 1).toString().padStart(4, '0');
        const prefixToUse = selectedPrefix || (dt.isValid ? `P${dt.toFormat("MM")}${dt.toFormat("yy")}${codeJournal}` : `P${codeJournal}`);
        newPiece = `${prefixToUse}${nextNum}`;
      }
    } else {
      // si soldes non nuls -> on reprend la dernière max (sans incrément ? on suit ta logique)
      if (maxNum <= 0) {
        // pas de max, fallback sur 0001
        const prefixToUse = selectedPrefix || (dt.isValid ? `P${dt.toFormat("MM")}${dt.toFormat("yy")}${codeJournal}` : `P${codeJournal}`);
        newPiece = `${prefixToUse}${String(1).padStart(4, '0')}`;
      } else {
        newPiece = `${selectedPrefix}${String(maxNum).padStart(4, '0')}`;
      }
    }

    // Mettre à jour la ligne (piece_justificative déjà peut exister donc on écrase)
    if (newPiece) {
      const upd = {};
      upd.piece_justificative = newPiece;
      row.update(upd);
    }
  });

})();

// --- utilitaire : récupérer la rubrique (taux + compte_tva) pour un code_journal donné ---
// renvoie { ok: true, data: resp } ou { ok:false, error: ... }
function fetchRubriqueForJournal(codeJournal) {
    return new Promise((resolve) => {
        if (!codeJournal) {
            resolve({ ok: false, error: 'codeJournal manquant' });
            return;
        }
        $.ajax({
            url: '/getRubriqueSociete',
            method: 'GET',
            dataType: 'json',
            data: { code_journal: codeJournal },
            success: function(resp) {
                // le controller renvoie le format que tu as partagé
                resolve({ ok: true, data: resp });
            },
            error: function(jqXHR) {
                let parsed = null;
                try { parsed = jqXHR.responseJSON ?? null; } catch(e) { parsed = null; }
                resolve({ ok: false, status: jqXHR.status, parsed });
            }
        });
    });
}


// --- remplacer ta fonction ajouterLignePreRemplieVentes par celle-ci ---
// conserve la logique existante, mais tente d'enrichir les lignes avec rubrique_tva_taux et compte_tva
async function ajouterLignePreRemplieVentes(idCounter, ligneActive, codeJournal, moisActuel, filterVentes) {
    // utilitaire local : retire " - Intitulé" si présent, retourne la partie compte (trim)
    function stripIntitule(val) {
        if (val === null || typeof val === 'undefined') return '';
        const s = String(val).trim();
        const parts = s.split(/\s*-\s*/, 2);
        return parts[0] ? parts[0].trim() : '';
    }

    let lignes = [];
    let ligne1 = { ...ligneActive, id: idCounter++ };
    let ligne2 = { ...ligneActive, id: idCounter++ };

    console.log("Ajout des lignes pré-remplies avec filterVentes:", filterVentes);

    // Pour les ventes, on considère le montant net saisi dans le champ 'debit'
    const netAmount = parseFloat(ligneActive.debit) || 0;
    console.log("Montant net de vente :", netAmount);

    // --- récupérer info rubrique (taux + compte_tva) si possible ---
    let rubriqueInfo = null;
    try {
        const resp = await fetchRubriqueForJournal(codeJournal);
        if (resp && resp.ok && resp.data) {
            rubriqueInfo = resp.data;
            // normalize taux en fraction (0.2 pour 20)
            if (typeof rubriqueInfo.taux !== 'undefined' && rubriqueInfo.taux !== null && rubriqueInfo.taux !== '') {
                let t = Number(rubriqueInfo.taux);
                if (!isNaN(t)) {
                    rubriqueInfo.__taux_normalise = (t > 1 ? t / 100 : t);
                }
            }
        } else {
            console.warn("fetchRubriqueForJournal a renvoyé une erreur ou pas de data :", resp);
        }
    } catch (e) {
        console.error("Erreur fetchRubriqueForJournal :", e);
    }

    if (filterVentes === 'contre-partie') {
        // Création de deux lignes pré-remplies
        // Ligne 1
        // CLEAN : garder seulement le numéro de compte (sans intitulé)
        ligne1.compte = stripIntitule(ligneActive.contre_partie || '');
        ligne1.contre_partie = stripIntitule(ligneActive.compte || '');
        ligne1.debit = 0;
        ligne1.credit = 0;
        ligne1.piece = ligneActive.piece;
        ligne1.type_journal = codeJournal || '';

        // Si on a des infos de rubrique, on les ajoute sur la ligne 1
        if (rubriqueInfo) {
            ligne1.rubrique_tva = rubriqueInfo.selected_text ?? (rubriqueInfo.nom_racines ? `${rubriqueInfo.rubrique}: ${rubriqueInfo.nom_racines}` : rubriqueInfo.rubrique ?? '');
            ligne1.rubrique_tva_code = String(rubriqueInfo.selected ?? rubriqueInfo.rubrique ?? '');
            ligne1.rubrique_tva_taux = typeof rubriqueInfo.__taux_normalise !== 'undefined' ? rubriqueInfo.__taux_normalise : (typeof rubriqueInfo.taux !== 'undefined' ? Number(rubriqueInfo.taux) : null);
            // CLEAN compte_tva aussi
            ligne1.compte_tva = stripIntitule(rubriqueInfo.compte_tva ?? (rubriqueInfo.compte_tva ?? ''));
        }

        lignes.push(ligne1);

        // Ligne 2 (TVA) - on met le compte_tva comme compte si disponible (nettoyé)
        ligne2.compte = (rubriqueInfo && rubriqueInfo.compte_tva) ? stripIntitule(rubriqueInfo.compte_tva) : stripIntitule(ligneActive.compte_tva || '');
        ligne2.contre_partie = ligne1.compte || '';
        ligne2.debit = 0;
        ligne2.credit = 0;
        ligne2.piece = ligneActive.piece;
        ligne2.type_journal = codeJournal || '';

        // Copier aussi les méta de rubrique sur la ligne TVA (utile pour envoi / affichage)
        if (rubriqueInfo) {
            ligne2.rubrique_tva = ligne1.rubrique_tva;
            ligne2.rubrique_tva_code = ligne1.rubrique_tva_code;
            ligne2.rubrique_tva_taux = ligne1.rubrique_tva_taux;
            ligne2.compte_tva = ligne1.compte_tva;
        }

        lignes.push(ligne2);
    } else if (filterVentes === 'libre') {
        // Création d'une seule ligne vide pré-remplie
        ligne1.compte = '';
        ligne1.contre_partie = '';
        ligne1.debit = 0;
        ligne1.credit = 0;
        ligne1.piece = '';
        ligne1.type_journal = codeJournal || '';

        // si on a la rubrique on peut la préremplir aussi (optionnel)
        if (rubriqueInfo) {
            ligne1.rubrique_tva = rubriqueInfo.selected_text ?? (rubriqueInfo.nom_racines ? `${rubriqueInfo.rubrique}: ${rubriqueInfo.nom_racines}` : rubriqueInfo.rubrique ?? '');
            ligne1.rubrique_tva_code = String(rubriqueInfo.selected ?? rubriqueInfo.rubrique ?? '');
            ligne1.rubrique_tva_taux = typeof rubriqueInfo.__taux_normalise !== 'undefined' ? rubriqueInfo.__taux_normalise : (typeof rubriqueInfo.taux !== 'undefined' ? Number(rubriqueInfo.taux) : null);
            ligne1.compte_tva = stripIntitule(rubriqueInfo.compte_tva ?? '');
        }

        lignes.push(ligne1);
    }

    console.log("Lignes pré-remplies générées (Ventes):", lignes);

    // Calcul du crédit pour chaque ligne pré-remplie
    if (Array.isArray(lignes)) {
        for (let i = 0; i < lignes.length; i++) {
            const typeLigne = (i === 0) ? "ligne1" : "ligne2";
            console.log(`Calcul du crédit pour ${typeLigne} (Ventes):`, lignes[i]);
            // Ici calculerCredit regardera rubrique_tva_taux / compte_tva injectés ci-dessus
            // await calculerCredit(lignes[i], typeLigne, netAmount);
            console.log(`Crédit calculé pour ${typeLigne} (Ventes):`, lignes[i].credit);
        }
    } else {
        console.error("Erreur: 'lignes' n'est pas un tableau:", lignes);
    }

    return lignes;
}


// --- calculerCredit : préfère rubrique_tva_taux stocké si présent ---
// --- calculerCredit : utilise rubrique_tva_taux et compte_tva si présents ---
// async function calculerCredit(rowData, typeLigne, debit) {
//     // priorise un taux explicite stocké (ex: renseigné par l'API depuis l'éditeur)
//     let tauxTVA = null;
//     if (typeof rowData.rubrique_tva_taux !== 'undefined' && rowData.rubrique_tva_taux !== null && rowData.rubrique_tva_taux !== '') {
//         tauxTVA = parseFloat(rowData.rubrique_tva_taux);
//         if (tauxTVA > 1) tauxTVA = tauxTVA / 100;
//     } else if (rowData.taux) {
//         tauxTVA = parseFloat(rowData.taux);
//         if (tauxTVA > 1) tauxTVA = tauxTVA / 100;
//     } else if (rowData.rubrique_tva) {
//         const m = String(rowData.rubrique_tva).match(/\(([\d\.]+)%\)/);
//         if (m && m[1]) tauxTVA = parseFloat(m[1]) / 100;
//         else {
//             const m2 = String(rowData.rubrique_tva).match(/([\d\.]+)/);
//             if (m2 && m2[1]) {
//                 let v = parseFloat(m2[1]);
//                 if (v > 1) v = v / 100;
//                 tauxTVA = v;
//             }
//         }
//     }

//     if (tauxTVA === null || isNaN(tauxTVA)) tauxTVA = 0;

//     console.log(`Calcul du crédit pour ${typeLigne}: Débit = ${debit}, Taux TVA = ${tauxTVA}`);

//     if (isNaN(debit) || isNaN(tauxTVA)) {
//         console.error("Débit ou Taux TVA invalides !");
//         rowData.credit = 0;
//         if (typeof calculerSoldeCumuleVentes === 'function') calculerSoldeCumuleVentes();
//         return;
//     }

//     rowData.debit = 0;
//     const montantNet = debit / (1 + tauxTVA);

//     let credit = 0;
//     if (typeLigne === "ligne1") credit = montantNet;
//     else if (typeLigne === "ligne2") credit = montantNet * tauxTVA;

//     rowData.credit = parseFloat(credit.toFixed(2));

//     // recalcul global si existe
//     if (typeof calculerSoldeCumuleVentes === 'function') {
//         try {
//             const res = calculerSoldeCumuleVentes();
//             if (res instanceof Promise) await res;
//         } catch (e) {
//             console.warn("Erreur lors de l'appel à calculerSoldeCumuleVentes:", e);
//         }
//     }


// }

// async function enregistrerLignesVentes() {
//   try {
//     const lignes = tableVentes.getData();
//     console.log("📌 [Ventes] Données récupérées :", lignes);

//     const journalSelect = document.querySelector("#journal-ventes");
//     const codeJournal = journalSelect?.value?.trim() || "";
//     if (!codeJournal) {
//       alert("⚠️ Veuillez sélectionner un journal.");
//       return;
//     }

//     const selectedOption = journalSelect.options[journalSelect.selectedIndex];
//     const categorie = selectedOption ? selectedOption.getAttribute("data-type") : "";
//     const selectedFilter = document.querySelector('input[name="filter-ventes"]:checked')?.value || null;

//     // Nettoyer les lignes avant envoi
//     const lignesAEnvoyer = lignes
//       .filter(ligne => (parseFloat(ligne.debit) > 0 || parseFloat(ligne.credit) > 0))
//       .map(ligne => ({
//         id: ligne.id || null,
//         date: ligne.date || new Date().toISOString().slice(0, 10),
//         numero_dossier: ligne.numero_dossier || ' ',
//         numero_facture: ligne.numero_facture || ' ',
//         compte: ligne.compte || '',
//         debit: parseFloat(ligne.debit) || 0,
//         credit: parseFloat(ligne.credit) || 0,
//         contre_partie: ligne.contre_partie || '',
//         rubrique_tva: ligne.rubrique_tva || '',
//         compte_tva: ligne.compte_tva || '',
//         type_journal: codeJournal,
//         categorie: categorie,
//         piece_justificative: ligne.piece_justificative || '',
//         file_id: ligne.file_id || '',
//         libelle: ligne.libelle || '',
//         filtre_selectionne: selectedFilter,
//         value: ligne.solde_cumule ?? ""
//       }));

//     if (lignesAEnvoyer.length === 0) {
//       alert("⚠️ Aucune ligne valide à enregistrer.");
//       return;
//     }

//     const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
//     const response = await fetch('/lignes', {
//       method: 'POST',
//       headers: {
//         'Content-Type': 'application/json',
//         'X-CSRF-TOKEN': csrfToken
//       },
//       body: JSON.stringify({ lignes: lignesAEnvoyer })
//     });

//     if (!response.ok) {
//       console.error("❌ [Ventes] Erreur serveur :", response.status, response.statusText);
//       alert(`Erreur lors de l'enregistrement : ${response.statusText}`);
//       return;
//     }

//     const result = await response.json();
//     console.log("📥 [Ventes] Réponse serveur :", result);

//     // ✅ Recharge proprement le tableau
//     const newData = Array.isArray(result)
//       ? result
//       : (result?.data && Array.isArray(result.data) ? result.data : []);

//     tableVentes.setData(newData);
//     calculerSoldeCumuleVentes();

//     // ✅ Ajoute UNE SEULE ligne vide à la fin (si aucune n'existe déjà)
//     const dataActuelle = tableVentes.getData();
//     const derniere = dataActuelle[dataActuelle.length - 1];

//     if (!derniere || (derniere.compte?.trim() || derniere.debit || derniere.credit)) {
//       tableVentes.addRow({
//         id: null,
//         compte: '',
//     contre_partie: contrePartie,              // valeur stockée (ex: code compte)
//         compte_tva: '',
//         debit: 0,
//         credit: 0,
//         piece_justificative: '',
//         file_id: '',
//         libelle: '',
//         rubrique_tva: '',
//         type_journal: codeJournal,
//         value: ''
//       });
//     }

//   } catch (error) {
//     console.error("🚨 [Ventes] Erreur :", error);
//     alert("❌ Une erreur s'est produite pendant l'enregistrement.");
//   }
// }

// async function ecouterEntrerVentes(table) {
//   table.element.addEventListener("keydown", async function (event) {
//     if (event.key !== "Enter") return;
//     event.preventDefault();

//     const selectedRows = table.getSelectedRows();
//     if (selectedRows.length === 0) {
//       console.warn("Aucune ligne sélectionnée (Ventes)");
//       return;
//     }

//     const ligneActive = selectedRows[0].getData();
//     let nouvellesLignes = await ajouterLigneVentes(table, true, ligneActive);

//     if (!Array.isArray(nouvellesLignes)) {
//       nouvellesLignes = [nouvellesLignes];
//     }

//     console.log("✅ Lignes ajoutées (Ventes) :", nouvellesLignes);

//     // Rafraîchir sans doublons
//     const dataActuelle = table.getData();
//     table.setData(dataActuelle);

//     // Vérifie proprement s'il y a déjà une ligne vide
//     const hasEmptyLine = dataActuelle.some(l =>
//       !l.compte && !parseFloat(l.debit) && !parseFloat(l.credit)
//     );

//     if (!hasEmptyLine) {
//       tableVentes.addRow({
//         id: null,
//         compte: '',
//     contre_partie: contrePartie,              // valeur stockée (ex: code compte)
//         compte_tva: '',
//         debit: 0,
//         credit: 0,
//         piece_justificative: '',
//         type_journal: document.querySelector("#journal-ventes")?.value || '',
//         value: ''
//       });
//     }

//     // Enregistrement automatique
//     await enregistrerLignesVentes();
//   });
// }

// Fonction de mise à jour des données du tableau Ventes en fonction des filtres
function updateTabulatorDataVentes() {
  const mois           = document.getElementById("periode-ventes").value;
  const annee          = document.getElementById("annee-ventes").value;
  const codeJournal    = document.getElementById("journal-ventes").value;
  const filtreExercice = document.getElementById("filter-exercice-ventes").checked;

  // --- Alerte douce avec SweetAlert2 ---
  const showSwal = (message) => {
    Swal.fire({
      icon: 'info',
      title: '',
      text: message,
      timer: 2500,
      showConfirmButton: false,
      toast: true,
      position: 'top-end',
      background: '#e6ffed',
      color: '#153257ff'
    });
  };

  // --- Conditions de filtrage ---
  if (!codeJournal || codeJournal === "" || codeJournal === "selectionner") {
    // showSwal("Veuillez sélectionner un journal .");
    if (typeof tableVentes !== "undefined" && tableVentes) tableVentes.clearData();
    return;
  }

  if ((!mois || mois === "selectionner un mois") && !filtreExercice) {
    // showSwal("Veuillez sélectionner un mois ou cocher « Exercice entier » ");
    if (typeof tableVentes !== "undefined" && tableVentes) tableVentes.clearData();
    return;
  }

  if (!annee || isNaN(annee)) {
    showSwal("Veuillez saisir une année valide.");
    if (typeof tableVentes !== "undefined" && tableVentes) tableVentes.clearData();
    return;
  }

  // --- Construction des paramètres ---
  let dataToSend = { categorie: "Ventes" };

  if (filtreExercice && annee) {
    dataToSend.annee = annee;
    if (codeJournal) dataToSend.code_journal = codeJournal;
  } else {
    if (mois && annee) dataToSend.mois = mois, dataToSend.annee = annee;
    if (codeJournal) dataToSend.code_journal = codeJournal;
  }

  console.log("📤 Filtrage Ventes appliqué :", dataToSend);

  // --- Requête fetch ---
  fetch("/get-operations", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify(dataToSend),
  })
  .then(response => response.json())
  .then(payload => {
    const data = Array.isArray(payload)
      ? payload
      : (payload && Array.isArray(payload.data) ? payload.data : []);

    if (typeof tableVentes === 'undefined' || !tableVentes) {
      console.warn("⚠️ tableVentes non initialisée.");
      return;
    }

    tableVentes.replaceData(data).then(() => {
      try { applyDefaultContrePartieToTable(tableVentes, codeJournal); } catch {}
      try { tableVentes.redraw(true); } catch {}
      try { calculerSoldeCumuleVentes(); } catch {}

      if (data.length === 0) {
        tableVentes.addRow({
          id: null,
          compte: '',
       contre_partie: contrePartie,              // valeur stockée (ex: code compte)
          debit: 0,
          credit: 0,
          piece_justificative: '',
          type_journal: codeJournal,
          value: ''
        });
      }
    });
  })
  .catch(error => {
    console.error("Erreur lors de la mise à jour Ventes :", error);
  });
}


// (les écouteurs que tu avais restent valides)
document.getElementById("journal-ventes").addEventListener("change", updateTabulatorDataVentes);
document.getElementById("periode-ventes").addEventListener("change", updateTabulatorDataVentes);
document.getElementById("annee-ventes").addEventListener("input", updateTabulatorDataVentes);
document.getElementById("filter-exercice-ventes").addEventListener("change", updateTabulatorDataVentes);

// Chargement initial
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
// ecouterEntrerVentes(tableVentes);
// ecouterEntrer(tableAch);

// tabulatorManager.applyToTabulator(tableAch);
// tabulatorManager.applyToTabulator(tableVentes);

         // --- Mapping Modal Ventes ---
   // mapping modal controls
   const importV = document.getElementById("import-ventes"),
   modalV = document.getElementById("mapping-modal-ventes"),
   inpV   = document.getElementById("excel-file-ventes"),
   modalInV = document.getElementById("modal-excel-input-ventes"),
   formV  = document.getElementById("mapping-form-ventes"),
   confV  = document.getElementById("mapping-confirm-ventes"),
   cancV  = document.getElementById("mapping-cancel-ventes");

const fieldsV=[
{key:"date",           label:"Date"},
{key:"numero_facture", label:"N° facture"},
{key:"compte",         label:"Compte"},
{key:"intitule_compte",label:"INTITULE COMPTE"},
{key:"libelle",        label:"Libellé"},
{key:"debit",          label:"Débit"},
{key:"credit",         label:"Crédit"},
{key:"contre_partie",  label:"Contre-Partie"},
{key:"rubrique_tva",   label:"Rubrique TVA"},
{key:"compte_tva",     label:"Compte TVA"},
];

// open modal + trigger file input
importV.addEventListener("click",()=>{
formV.innerHTML="";
confV.disabled=true;
modalV.style.display="flex";
modalInV.value=null;
modalInV.click();
});

// read excel
modalInV.addEventListener("change",evt=>{
const file=evt.target.files[0];
if(!file) return;
const reader=new FileReader();
reader.readAsBinaryString(file);
reader.onload=e=>{
 const wb=XLSX.read(e.target.result,{type:"binary"});
 const ws=wb.Sheets[wb.SheetNames[0]];
 const rows=XLSX.utils.sheet_to_json(ws,{header:1,defval:""});
 if(!rows.length){ alert("Feuille invalide"); return;}
 const headers=rows[0].map(h=>h.toString().trim());
 buildFormV(headers,rows.slice(1));
};
reader.onerror=()=>alert("Erreur lecture Excel");
});

function buildFormV(headers,dataRows){
formV.innerHTML="";
fieldsV.forEach(f=>{
 const div=document.createElement("div");
 div.style.display="flex";
 div.style.justifyContent="space-between";
 div.style.marginBottom="8px";
 div.innerHTML=`
   <label>${f.label}</label>
   <select data-key="${f.key}">
     <option value="">— Sélectionnez —</option>
     ${headers.map((h,i)=>`<option value="${i}">Col ${i+1}: ${h}</option>`).join("")}
   </select>`;
 formV.appendChild(div);
});
confV.disabled=false;
confV.onclick=async()=>{
 const sels=formV.querySelectorAll("select[data-key]"), mapIdx={};
 sels.forEach(s=>{ if(s.value!=="") mapIdx[s.dataset.key]=+s.value; });
 // build data
 const tableData=dataRows.map(cols=>({
   date:            mapIdx.date           !=null?cols[mapIdx.date]          :"",
   numero_facture:  mapIdx.numero_facture !=null?cols[mapIdx.numero_facture]  :"",
   compte:          mapIdx.compte         !=null?cols[mapIdx.compte]        :"",
   intitule_compte: mapIdx.intitule_compte!=null?cols[mapIdx.intitule_compte] :"",
   libelle:         mapIdx.libelle        !=null?cols[mapIdx.libelle]       :"",
   debit:           mapIdx.debit          !=null?cols[mapIdx.debit]         :"",
   credit:          mapIdx.credit         !=null?cols[mapIdx.credit]        :"",
   contre_partie:   mapIdx.contre_partie  !=null?cols[mapIdx.contre_partie] :"",
   rubrique_tva:    mapIdx.rubrique_tva   !=null?cols[mapIdx.rubrique_tva]  :"",
   compte_tva:      mapIdx.compte_tva     !=null?cols[mapIdx.compte_tva]    :"",
   piece_justificative:"",
 }));
 modalV.style.display="none";
 updatePieceJustificative(tableData);
 await tableVentes.setData(tableData);
 calculerSoldeCumuleVentes();
 const res=await enregistrerLignesVentes();
 if(res.success) alert("Ventes importées et sauvegardées !");
 else alert("Import OK, sauvegarde échouée.");
};
}

cancV.addEventListener("click",()=>modalV.style.display="none");
modalV.addEventListener("click",e=>{ if(e.target===modalV) modalV.style.display="none"; });


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
        // Demande de confirmation avant suppression
        Swal.fire({
            title: 'Confirmer la suppression',
            text: `Voulez-vous vraiment supprimer ${selectedRows.length} ligne(s) ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
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
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: "Les lignes sélectionnées ont été supprimées.",
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: "Erreur lors de la suppression des lignes.",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                })
                .catch(error => {
                    console.error("Erreur lors de la suppression des lignes :", error);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: "Erreur lors de la suppression des lignes.",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Suppression annulée
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: "Suppression annulée.",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
            }
        });
    } else {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: "Veuillez sélectionner une ou plusieurs lignes à supprimer.",
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    }
});

// Gestionnaire pour supprimer une ligne sélectionnée dans tableVentes
document.getElementById("delete-row-btnVte").addEventListener("click", function () {
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let selectedRows = tableVentes.getSelectedRows(); // Récupérer les lignes sélectionnées dans Tabulator (tableVentes)

    if (selectedRows.length > 0) {
        // Demande de confirmation avant suppression
        Swal.fire({
            title: 'Confirmer la suppression',
            text: `Voulez-vous vraiment supprimer ${selectedRows.length} ligne(s) ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
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
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: "Les lignes sélectionnées ont été supprimées.",
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: "Erreur lors de la suppression des lignes.",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                })
                .catch(error => {
                    console.error("Erreur lors de la suppression des lignes :", error);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: "Erreur lors de la suppression des lignes.",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Suppression annulée
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: "Suppression annulée.",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
            }
        });
    } else {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: "Veuillez sélectionner une ou plusieurs lignes à supprimer.",
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    }
});

} catch (error) {
    console.error("Erreur lors de l'initialisation des tables :", error);
}

    // Fonction de formatage de la monnaie
function formatCurrency(value) {
    return parseFloat(value).toFixed(2);
  }
    // -------------------- éditeur fact_lettrer (Select2) --------------------
function factLettrerEditorOP(cell, onRendered, success, cancel) {
  const row = cell.getRow();
  const data = row ? row.getData() : {};
  const compte = data.compte || '';
  const debit = parseFloat(data.debit) || 0;
  const credit = parseFloat(data.credit) || 0;

  const select = document.createElement("select");
  select.multiple = true;
  select.style.width = "420px";
  const uniq = 'fact_lettrer_' + Math.random().toString(36).slice(2,8);
  select.id = uniq;

  // --- lecture de la valeur précédente (compatibilité string/array)
  let prevValues = cell.getValue() || [];
  if (typeof prevValues === 'string') {
    // format attendu ancien: "id|num|montant|date & id2|..." ou unique "id|num|montant|date"
    if (prevValues.indexOf('&') !== -1) {
      prevValues = prevValues.split(/\s*&\s*/).map(s => s.trim()).filter(Boolean);
    } else {
      prevValues = prevValues.trim() ? [prevValues.trim()] : [];
    }
  }
  if (!Array.isArray(prevValues)) prevValues = [];

  // protège contre re-entrance quand on restaure la sélection
  let suppressChange = false;

  function safeDestroy(el) {
    try { if (window.$ && $(el).data('select2')) { $(el).select2('close'); $(el).select2('destroy'); } } catch(e){}
  }
  function closeOtherSelect2s(keepEl){
    try {
      if (!window.$) return;
      $('select').each(function(){ if (this !== keepEl && $(this).data('select2')) { try{ $(this).select2('close'); }catch(e){} } });
      $('.select2-dropdown').remove();
    } catch(e){ console.warn(e); }
  }

  function setOptionsFromItems(items, existingArr = []) {
    select.innerHTML = '';
    if (!items || !items.length) {
      select.appendChild(new Option("-- Aucune facture lettrable --", "", false, false));
      return;
    }
    items.forEach(item => {
      let id = '', numero = '', montant = '', date = '', reste = null;
      if (typeof item === 'string') {
        const p = item.split('|');
        id = p[0]||item;
        numero = p[1]||p[0]||item;
        montant = p[2]||'';
        date = p[3]||'';
      } else if (item && typeof item === 'object') {
        id = String(item.id ?? item.ID ?? '');
        numero = item.numero_facture ?? item.numero ?? id;
        montant = (item.reste_montant_lettre != null ? String(item.reste_montant_lettre) : ((item.debit != null && Number(item.debit)!==0) ? String(item.debit) : String(item.credit ?? '')));
        date = item.date ?? (item.created_at ? item.created_at.split(' ')[0] : '');
        reste = (item.reste_montant_lettre ?? item.reste ?? null);
      } else {
        id = String(item); numero = id;
      }

      // value: pipe-separated fields (id|numero|montant|date)
      const value = `${id}|${numero}|${montant}|${date}`;
      const label = `${numero} | ${montant}${date ? ' | ' + date : ''}`;
      const option = new Option(label, value, false, existingArr.indexOf(value) !== -1);
      option.setAttribute('data-numero', numero);
      if (reste !== null) option.setAttribute('data-reste', String(reste));
      if (date) option.setAttribute('data-date', date);
      select.appendChild(option);
    });
  }

  (async function loadAndInit(){
    try {
      const params = new URLSearchParams({ debit: debit || '', credit: credit || '', compte: (''+compte) || '' });
      const resp = await fetch(`/get-nfacturelettreeOP?${params.toString()}`, { method: 'GET', credentials: 'same-origin' });
      if (!resp.ok) throw new Error('Status '+resp.status);
      const json = await resp.json();
      const items = Array.isArray(json) ? json : (json && Array.isArray(json.data) ? json.data : []);
      setOptionsFromItems(items, prevValues);

      if (window.$ && window.$.fn.select2) {
        safeDestroy(select);
        const $containerModal = $(select).closest('.modal');
        const dropdownParent = $containerModal.length ? $containerModal : $(document.body);

        closeOtherSelect2s(select);

        $(select).select2({
          placeholder: "-- Sélectionnez une ou plusieurs factures --",
          closeOnSelect: false,
          width: 'resolve',
          dropdownAutoWidth: true,
          dropdownParent: dropdownParent,
          escapeMarkup: m => m,
          templateResult: function(data){
            if (!data.id) return data.text;
            const el = data.element;
            const num = el ? el.getAttribute('data-numero') || data.text : data.text;
            const date = el ? el.getAttribute('data-date') || '' : '';
            const reste = el ? el.getAttribute('data-reste') || '' : '';
            const checked = data.selected ? 'checked' : '';
            return `<div style="display:flex;align-items:center;">
                      <input type="checkbox" ${checked} style="margin-right:8px;pointer-events:none;"/>
                      <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${num}${reste? ' • ' + reste : ''}${date? ' • ' + date : ''}</div>
                    </div>`;
          },
          templateSelection: function(data){
            if (!data.id) return data.text;
            const raw = data.id || data.text || '';
            const parts = (''+raw).split('|');
            // affiche seulement le numero en sélection
            return parts[1] || parts[0] || raw;
          }
        });

        // restore selection WITHOUT triggering change handler
        try { suppressChange = true; $(select).val(prevValues).trigger('change.select2'); } catch(e){}
        setTimeout(()=>{ suppressChange = false; }, 40);

        // open the select2 safely (handle modal timing)
        const openSafely = () => {
          try {
            closeOtherSelect2s(select);
            $(select).select2('open');
            setTimeout(()=> {
              try {
                $('.select2-dropdown').last().removeClass('select2-dropdown--above').addClass('select2-dropdown--below');
                const search = document.querySelector(`#${uniq} + .select2-container .select2-search__field`);
                if (search) search.focus();
              } catch(e){}
            }, 40);
          } catch(e){ console.warn('openSafely', e); }
        };
        if ($containerModal.length) {
          if ($containerModal.is(':visible')) openSafely();
          else $containerModal.one('shown.bs.modal.factLettrerInit', openSafely);
        } else openSafely();
      }
    } catch(err){
      console.error('factLettrerEditorOP load error', err);
      select.innerHTML = '';
      select.appendChild(new Option("-- Erreur chargement --", "", false, false));
      try { if (window.$) $(select).select2({ width:'resolve' }); } catch(e){}
    }
  })();

  // change handler
  $(select).on('change', function(){
    if (suppressChange) return;
    const sel = $(this).val() || [];
    // joined string between records: "rec1 & rec2 & rec3"
    const joined = Array.isArray(sel) ? sel.join(' & ') : (sel || '');

    const acompte = (debit && Number(debit)!==0) ? Number(debit) : Number(credit||0);
    const optionNodes = Array.from(select.selectedOptions || []);
    const restValues = optionNodes.map(opt => {
      const r = opt.getAttribute('data-reste');
      return (r!==null && r!==undefined && r!=='') ? parseFloat(r) : null;
    }).filter(v=>v!==null);
    const totalRest = restValues.length ? restValues.reduce((a,b)=>a+b,0) : null;

    if (totalRest !== null && acompte > totalRest) {
      const acompteFmt = Number(acompte).toFixed(2);
      const totalFmt = Number(totalRest).toFixed(2);
      if (window.Swal) {
        suppressChange = true;
        Swal.fire({
          icon:'warning',
          title:'Avertissement',
          html: `Le montant à lettrer <strong>${acompteFmt}</strong> est supérieur au montant des factures sélectionnées <strong>${totalFmt}</strong>.<br><br>Vous devez lettrer encore plus de factures.`,
          confirmButtonText: 'OK'
        }).then(()=> {
          try { $(select).val(prevValues).trigger('change.select2'); } catch(e){}
          const prevJoined = Array.isArray(prevValues) ? prevValues.join(' & ') : (prevValues || '');
          try { cell.setValue(prevJoined); } catch(e){}
          try { if (cell.getRow) cell.getRow().update({ fact_lettrer: prevJoined }); } catch(e){}
          try { safeDestroy(select); } catch(e){}
          suppressChange = false;
          cancel();
        }).catch(()=> { suppressChange = false; cancel(); });
      } else {
        alert(`Le montant à lettrer ${acompte} est supérieur au total ${totalRest}.`);
        try { suppressChange = true; $(select).val(prevValues).trigger('change.select2'); } catch(e){}
        suppressChange = false;
        cancel();
      }
      return;
    }

    // OK: apply locally and finish
    try { cell.setValue(joined); } catch(e){}
    try { if (cell.getRow) cell.getRow().update({ fact_lettrer: joined }); } catch(e){}
    try { safeDestroy(select); } catch(e){}
    success(joined);
  });

  // ESC handler
  select.addEventListener('keydown', function(e){
    if (e.key === 'Escape') {
      try { suppressChange = true; $(select).val(prevValues).trigger('change.select2'); } catch(e){}
      const prevJoined = Array.isArray(prevValues) ? prevValues.join(' & ') : (prevValues || '');
      try { cell.setValue(prevJoined); } catch(e){}
      try { if (cell.getRow) cell.getRow().update({ fact_lettrer: prevJoined }); } catch(e){}
      try { safeDestroy(select); } catch(e){}
      suppressChange = false;
      cancel();
    }
  });

  onRendered(() => {
    setTimeout(() => {
      try { const search = document.querySelector(`#${uniq} + .select2-container .select2-search__field`); if (search) search.focus(); } catch(e){}
    }, 150);
  });

  return select;
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
window.tableOP = new Tabulator("#table-operations-diverses", {
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
    rowHeight: 25,
    columns: [
        { title: "ID", field: "id", visible: false },
     {
  title: "Date",
  field: "date",
  hozAlign: "center",
  headerFilter: "input",
               width: 95,

  headerFilterParams: {
    elementAttributes: { style: "width: 95px; height: 25px;" }
  },
  sorter: "date",
  editor: function(cell, onRendered, success, cancel) {
    // conteneur et input
    const container = document.createElement("div");
    container.style.display = "flex";
    container.style.alignItems = "center";

    const input = document.createElement("input");
    input.type = "text";
    input.style.flex = "1";
    input.placeholder = "jj/MM";

    // pré-remplissage (affiche dd/MM/yyyy si possible)
    const currentValue = cell.getValue();
    if (currentValue) {
      let dt = luxon.DateTime.fromFormat(currentValue, "yyyy-MM-dd HH:mm:ss");
      if (!dt.isValid) dt = luxon.DateTime.fromISO(currentValue);
      if (dt.isValid) input.value = dt.toFormat("dd/MM/yyyy");
    }

    function validateAndCommit(postEnter = false) {
      const moisSelect = document.getElementById("periode-operations-diverses");
      const anneeInput = document.getElementById("annee-operations-diverses");

      const raw = String(input.value || "").trim();
      const dayStr = raw.slice(0, 2);
      const day = parseInt(dayStr, 10);
      const month = moisSelect ? parseInt(moisSelect.value, 10) : (luxon.DateTime.local().month);
      const year = anneeInput ? parseInt(anneeInput.value, 10) : (new Date().getFullYear());

      if (!isNaN(day) && !isNaN(month) && !isNaN(year)) {
        const dt = luxon.DateTime.local(year, month, day);
        if (dt.isValid) {
          const iso = dt.toFormat("yyyy-MM-dd HH:mm:ss");

          try { success(iso); } catch (e) { console.warn('commit failed', e); }

          // If Enter pressed (postEnter === true) copy to date_lettrage
          if (postEnter) {
            try {
              const row = cell.getRow();
              if (row) {
                // copy date -> date_lettrage
                row.update({ date_lettrage: iso });
              }
            } catch (err) { console.warn('copy to date_lettrage failed', err); }

            // move focus to next editable cell
            setTimeout(() => { try { focusNextEditableCell(cell); } catch (e) { console.warn(e); } }, 50);
          }

          return true;
        }
      }

      alert("Veuillez saisir une date valide");
      try { cancel(); } catch (e) {}
      return false;
    }

    // blur => commit only (no copy)
    input.addEventListener("blur", function() {
      validateAndCommit(false);
    });

    // Enter => commit + copy into date_lettrage + focus next
    input.addEventListener("keydown", function(e) {
      if (e.key === "Enter") {
        e.preventDefault();
        validateAndCommit(true);
      } else if (e.key === "Escape") {
        try { cancel(); } catch(e){ }
      }
    });

    container.appendChild(input);
    onRendered(() => {
      input.focus();
      input.select();
    });
    return container;
  },
  formatter: function(cell) {
    const dateValue = cell.getValue();
    if (dateValue) {
      let dt = luxon.DateTime.fromFormat(dateValue, "yyyy-MM-dd HH:mm:ss");
      if (!dt.isValid) dt = luxon.DateTime.fromISO(dateValue);
      return dt.isValid ? dt.toFormat("dd/MM/yyyy") : dateValue;
    }
    return "";
  },
  // conserve le clic pour copier aussi si tu veux (optionnel)
  cellClick: function(e, cell) {
    const row = cell.getRow();
    const value = cell.getValue();
    if (row && value) {
      try { row.update({ date_lettrage: value }); } catch(e){ console.warn(e); }
    }
  }
},


        {
            title: "N° Facture",
            field: "numero_facture",
                         width: 150,
                                    headerFilter: "input",
            headerFilterParams: {
                elementAttributes: { style: "width: 95px; height: 25px;" }
            },
            editor: genericTextEditor
        },
        {
            title: "Compte",
            field: "compte",
             width: 150,

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
             width: 150,

            headerFilter: "input",
            headerFilterParams: {
                elementAttributes: { style: "width: 150px; height: 25px;" }
            },
            editor: genericTextEditor
            // editable: false
        },
        {
            title: "Débit",
            field: "debit",
              width: 95,
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
            width: 95,

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
              width: 100,

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
  title: "N° facture lettrée",
  field: "fact_lettrer",
  width: 240,
  headerFilter: "input",
  headerFilterParams: {
    elementAttributes: { style: "width: 120px; height: 26px;" }
  },

  // Formatter : n'affiche QUE le numero_facture pour chaque item sélectionné
  formatter: function(cell) {
    const value = cell.getValue();
    if (!value) return "";
    let items = [];
    if (Array.isArray(value)) items = value;
    else if (typeof value === "string") items = value.split(/\s*&\s*/).map(s => s.trim()).filter(Boolean);
    return items.map(it => {
      const parts = ("" + it).split('|');
      return parts[1] || parts[0] || it;
    }).join(", ");
  },

 editor: factLettrerEditorOP

},



{
    title: "Date Lettrage",
    field: "date_lettrage",
    width: 100,
    headerFilter: "input",
    headerFilterParams: {
        elementAttributes: { style: "width: 95px; height: 25px;" }
    },
    formatter: function(cell) {
        const value = cell.getValue();
        if (!value) return "";
        let dt = luxon.DateTime.fromISO(value);
        if (!dt.isValid) dt = luxon.DateTime.fromFormat(value, "yyyy-MM-dd HH:mm:ss");
        return dt.isValid ? dt.toFormat("dd/MM/yyyy") : value;
    },
    editor: genericDateEditorOP() // utilise l'éditeur date générique
},
   {
      title: "Solde Cumulé",
            field: "value",
            headerFilter: "input",
                     width: 100,

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
  // ---------- Colonne Pièce justificative (adaptée pour Opérations Diverses) ----------
{
  title: "Pièce justificative",
  field: "piece_justificative",
  headerFilter: "input",
  headerFilterParams: { elementAttributes: { style: "width: 150px; height: 25px;" } },
  width: 260,
  formatter: function(cell) {
    const d = cell.getRow().getData();
    const piece = d.piece_justificative || "";
    const esc = s => String(s || '')
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;');

    // decide if view should appear
    let showView = false;
    try {
      if (d && (d.file_id || d.file_id === 0 || d.file_url || d.filepath || d.path)) {
        showView = Boolean(d.file_id || d.file_url || d.filepath || d.path);
      } else if (d && d.piece_justificative) {
        const table = cell.getTable && cell.getTable();
        const all = (table && typeof table.getData === 'function') ? table.getData() : (table && typeof table.getRows === 'function' ? table.getRows().map(r=>r.getData()) : []);
        if (Array.isArray(all) && all.length) {
          const same = all.find(r => r && r.piece_justificative === d.piece_justificative &&
                                    (r.file_id || r.file_url || r.filepath || r.path));
          showView = Boolean(same && (same.file_id || same.file_url || same.filepath || same.path));
        }
      }
    } catch(e){ showView = false; }

    // compute tooltip/title: prefer explicit file_name, else try DOM lookup by file_id, else basename of path/url
    let titleName = '';
    try {
      if (d) {
        titleName = d.file_name || d.filename || d.name || '';
      }
      // if no name but have file_id -> try to read from modal DOM (cards have data-filename)
      if (!titleName && d && d.file_id) {
        const card = document.querySelector(`.card[data-file_id="${d.file_id}"]`);
        if (card) titleName = card.getAttribute('data-filename') || card.dataset.filename || '';
      }
      // fallback: if path/url present, use basename
      if (!titleName) {
        const path = d.file_url || d.filepath || d.path || '';
        if (path) titleName = decodeURIComponent(String(path).split('/').pop() || '');
      }
    } catch(e){ titleName = ''; }

    const inputHtml = `<input type="text" class="pj-input" tabindex="0" value="${esc(piece)}" placeholder="${esc(piece)}" style="flex:1;border:0;background:transparent;padding:4px 6px;">`;
    const viewStyle = showView ? '' : 'display:none;';
    const viewTitle = titleName ? esc(titleName) : 'Voir le fichier';
    const viewBtn = `<button class="icon-btn view-icon-achat" tabindex="0" title="${viewTitle}" aria-label="${viewTitle}" style="border:0;background:none;padding:6px;${viewStyle}"><i class="fas fa-eye" aria-hidden="true"></i></button>`;
    const uploadBtn = `<button class="icon-btn upload-icon" tabindex="0" title="Joindre un fichier" aria-label="Joindre un fichier" style="border:0;background:none;padding:6px;"><i class="fas fa-paperclip" aria-hidden="true"></i></button>`;

    return `<div class="pj-cell" style="display:flex;align-items:center;gap:6px;">${inputHtml}${viewBtn}${uploadBtn}</div>`;
  },

  cellClick: function(e, cell) {
    if (!e || !e.target) return;
    const el = e.target;
    const wrapper = cell.getElement ? cell.getElement() : null;
    const input = wrapper ? wrapper.querySelector('.pj-input') : null;
    const viewBtn = wrapper ? wrapper.querySelector('.view-icon-achat') : null;
    const uploadBtn = wrapper ? wrapper.querySelector('.upload-icon') : null;
    const row = cell.getRow();
    const d = row.getData();
    const table = cell.getTable && cell.getTable();

    // attach input handlers once
    try {
      if (input && !input._attached) {
        input._attached = true;
        input.addEventListener('blur', function() {
          const val = input.value == null ? '' : input.value.trim();
          try { row.update({ piece_justificative: val }); } catch(err){ console.warn(err); }
        });
        input.addEventListener('keydown', function(ev) {
          if (ev.key === 'Enter') {
            ev.preventDefault();
            const val = input.value == null ? '' : input.value.trim();
            try { row.update({ piece_justificative: val }); } catch(err){ console.warn(err); }
            // focus upload (trombone) after Enter
            if (uploadBtn) try { uploadBtn.focus(); } catch(e){}
          }
        });
      }
      if (viewBtn && !viewBtn._attached) { viewBtn._attached = true; viewBtn.addEventListener('keydown', function(ev){ if (ev.key === 'Enter') { ev.preventDefault(); viewBtn.click(); }}); }
      if (uploadBtn && !uploadBtn._attached) { uploadBtn._attached = true; uploadBtn.addEventListener('keydown', function(ev){ if (ev.key === 'Enter') { ev.preventDefault(); uploadBtn.click(); }}); }
    } catch(e){ console.warn('attach handlers achat', e); }

    // VIEW action (click) -> open via helper (but hover only shows title)
    if (el.closest && el.closest('.view-icon-achat') || (el.tagName && el.tagName.toLowerCase()==='i' && el.classList.contains('fa-eye'))) {
      (async function(){
        try {
          if (typeof openFileModalByField === 'function') {
            await openFileModalByField(d);
            return;
          }
          let fileId = d.file_id || null;
          if (!fileId && d.piece_justificative) {
            const all = (table && typeof table.getData === 'function') ? table.getData() : (table && typeof table.getRows === 'function' ? table.getRows().map(r=>r.getData()) : []);
            const same = all.find(r => r && r.piece_justificative === d.piece_justificative && (r.file_id || r.file_url || r.filepath || r.path));
            if (same && same.file_id) fileId = same.file_id;
          }
          if (fileId && typeof openFileModalByFileId === 'function') { openFileModalByFileId(fileId); return; }
          const path = d.file_url || d.filepath || d.path || null;
          if (path) {
            const vm = document.getElementById('viewFileModal');
            if (vm) {
              const ext = (String(path).split('.').pop() || '').toLowerCase();
              let html = '';
              if (['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext)) html = `<div style="text-align:center"><img src="${path}" alt="${d.piece_justificative||''}" style="max-width:100%; max-height:78vh;"></div>`;
              else if (ext === 'pdf') html = `<div style="height:80vh"><iframe src="${path}" frameborder="0" style="width:100%;height:100%;"></iframe></div>`;
              else html = `<div style="padding:18px"><div style="margin-bottom:12px;font-weight:600;">${d.piece_justificative || ''}</div><a href="${path}" target="_blank" rel="noopener">Ouvrir / Télécharger</a></div>`;

              // show modal
              vm.querySelector('.modal-body').innerHTML = html;
              vm.style.display = 'block'; vm.classList.add('open');

              // helper de fermeture
              function hideModalByIdLocal() {
                try { vm.style.display = 'none'; vm.classList.remove('open'); } catch(e){}
              }
              vm.querySelectorAll('.close').forEach(btn => { btn.removeEventListener && btn.removeEventListener('click', hideModalByIdLocal); btn.addEventListener('click', hideModalByIdLocal); });
              vm.addEventListener('click', function onModalClick(ev){ if (ev.target === vm) hideModalByIdLocal(); }, { once: true });
              return;
            } else {
              window.open(path, '_blank');
              return;
            }
          }
          // nothing to show; keep focus/selection
        } catch(err){ console.error('view achat error', err); }
      })();
      return;
    }

    // UPLOAD (trombone) - behavior: open files_achat_Modal and mark selected rows as waiting
    if (el.closest && el.closest('.upload-icon') || (el.tagName && el.tagName.toLowerCase()==='i' && el.classList.contains('fa-paperclip'))) {
      try {
        const hasFile = Boolean(d && (d.file_id || d.file_url || d.filepath || d.path));
        // remplace openModalForRows dans ton cellClick upload handling
const openModalForRows = function(rowsToOpen){
  window.currentPieceCellachat = cell;
  window.currentPieceCellAchat = cell;
  $('#confirmBtnAchat').data && $('#confirmBtnAchat').data('cell', cell);

  const selectedRows = typeof table.getSelectedRows === 'function' ? table.getSelectedRows() : [];
  const rowsToMark = (selectedRows && selectedRows.length > 0) ? selectedRows : (Array.isArray(rowsToOpen) && rowsToOpen.length ? rowsToOpen : [row]);

  window.rowsWaitingForFileAchat = window.rowsWaitingForFileAchat || [];

  rowsToMark.forEach(r => {
    try {
      const rd = r.getData();
      if (rd && rd.file_id) return; // si déjà lié, on laisse
      const already = window.rowsWaitingForFileAchat.some(x => { try { return x.getIndex && r.getIndex && x.getIndex() === r.getIndex(); } catch(e){ return false; } });
      if (!already) {
        window.rowsWaitingForFileAchat.push(r);
        try { r.getElement && r.getElement().classList.add('waiting-file'); } catch(e){}
      }
      // s'assurer que la row est sélectionnée (UI)
      try { r.select && r.select(); } catch(e){}
    } catch(e){ console.warn(e); }
  });

  // ouvre le modal (root behavior)
  if (typeof loadAndShowModal === 'function') {
    loadAndShowModal('files_achat_Modal', typeof urlAchatList !== 'undefined' ? urlAchatList : null, undefined);
  } else {
    const m = document.getElementById('files_achat_Modal');
    if (m) { m.style.display = 'block'; m.classList.add('open'); }
  }

  // Focus the first checkbox after a short delay (modal opened & table rendered)
  setTimeout(() => {
    try {
      const firstRow = (rowsToMark && rowsToMark.length) ? rowsToMark[0] : row;
      const el = firstRow && (firstRow.getElement ? firstRow.getElement() : (firstRow.element || null));
      if (el) {
        const chk = el.querySelector && el.querySelector('.select-row');
        if (chk) {
          try { chk.focus(); chk.checked = true; } catch(e){}
          // ensure the row is selected in Tabulator
          try { firstRow.select && firstRow.select(); } catch(e){}
        }
      }
    } catch(e){ console.warn('focus checkbox after openModal', e); }
  }, 250);

  try { row.select && row.select(); } catch(e){}
};

        if (hasFile) {
          if (window.Swal) {
            Swal.fire({
              title: 'Remplacer le fichier ?',
              text: 'Cette ligne contient déjà un fichier. Voulez-vous le remplacer ?',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Remplacer',
              cancelButtonText: 'Annuler'
            }).then(result => { if (result && result.isConfirmed) openModalForRows([row]); });
          } else {
            if (confirm('Cette ligne contient déjà un fichier. Voulez-vous le remplacer ?')) openModalForRows([row]);
          }
        } else {
          openModalForRows([row]);
        }
      } catch(err){ console.error('upload achat error', err); }
      return;
    }
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
      {
  title: "",
  field: "clear_action",
  width: 40,
  hozAlign: "center",
  headerSort: false,
  formatter: function (cell) {
    const data = cell.getRow().getData();

    // ✅ Ligne considérée "remplie" si un seul champ a une valeur non vide / non zéro
    const isFilled = [
      data.date,
      data.date_lettrage,
      data.compte,
      data.numero_facture,
      data.fact_lettrer,
      data.contre_partie,
      data.piece_justificative,
    ].some(v => v && String(v).trim() !== "") ||
    (Number(data.debit) !== 0 || Number(data.credit) !== 0 || Number(data.solde_cumule) !== 0);

    // ✅ Icône rouge active ou grisée désactivée
    return `
      <span class="clear-row-btn ${isFilled ? "active" : "disabled"}"
        style="color:${isFilled ? "red" : "#ccc"};
               cursor:${isFilled ? "pointer" : "not-allowed"};
               font-size:18px;"
        title="${isFilled ? "Vider la ligne" : "Rien à vider"}">
        &times;
      </span>
    `;
  },

  cellClick: function (e, cell) {
    const target = e.target;
    if (!target.classList.contains("clear-row-btn")) return;
    if (target.classList.contains("disabled")) return;

    const row = cell.getRow();

    // ✅ Données à vider
    const cleared = {
      date: "",
      date_lettrage: "",
      compte: "",
      numero_facture: "",
      fact_lettrer: "",
      debit: 0,
      credit: 0,
      contre_partie: "",
      solde_cumule: 0,
      piece_justificative: "",
    };

    // ✅ Mise à jour directe + feedback visuel
    row.update(cleared).then(() => {
      Swal.fire({
        toast: true,
        icon: "success",
        title: "Ligne vidée",
        position: "top-end",
        showConfirmButton: false,
        timer: 1200,
      });

      // 🔁 Re-dessiner la ligne uniquement (plus fluide que redraw complet)
      cell.getRow().reformat();
    });
  },
},

  { title: "Code_journal", field: "type_Journal", visible: false },
  { title: "categorie", field: "categorie", visible: false }
    ]


});
// Récupération du token CSRF (meta tag)
// Récupération du token CSRF (meta tag)
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// petites structures d'aide
const editingLocks = new Map(); // lock par row id pour éviter envoi concurrent
function normalizeAccountField(val) {
  if (!val && val !== 0) return null;
  if (typeof val !== 'string') val = String(val);
  if (val.includes(' - ')) return val.split(' - ')[0].trim();
  return val.trim();
}

// Helper simplified: only show Swal for success, otherwise use console
function notifySuccess(text = 'Opération réussie', title = 'Succès', timer = 1500) {
  if (window.Swal) {
    Swal.fire({
      icon: 'success',
      title,
      text,
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer
    });
  } else {
    console.log('SUCCESS:', title, text);
  }
}

// Empêche l'édition si la ligne n'a pas d'ID
tableOP.on("cellEditing", function (cell) {
  try {
    const row = cell.getRow();
    const data = row.getData();

    // Empêcher l'édition d'une ligne sans ID
    if (!data.id) {
      // silent log only
      console.info('Édition ignorée : nouvelle ligne sans ID — enregistrez-la d’abord.');
      return false; // Annuler l'édition sans alerte bloquante
    }

    return true; // Autoriser l'édition si la ligne a un ID
  } catch (err) {
    console.error('cellEditing handler error', err);
    return true;
  }
});

// Handler pour les edits (envoi PUT)
tableOP.on("cellEdited", function (cell) {
  (async () => {
    try {
      if (!cell) return;
      const row = cell.getRow();
      const data = row.getData();

      // Vérifier si la ligne a un ID (éviter les erreurs)
      if (!data.id) {
        console.info('Ignoré : ligne sans ID — aucune mise à jour envoyée.');
        return;
      }

      // Verrou simple pour éviter double requête simultanée
      if (editingLocks.get(data.id)) {
        console.info('Mise à jour déjà en cours pour cette ligne (id=' + data.id + ').');
        return;
      }

      // Vérifier si la ligne est vide => ignore
      const isEmpty = (!data.numero_facture || String(data.numero_facture).trim() === '') &&
                      (!data.compte || String(data.compte).trim() === '') &&
                      (!data.credit || Number(data.credit) === 0);
      if (isEmpty) {
        console.info('Ligne vide — aucune mise à jour envoyée.');
        return;
      }

      // Vérifier si tous les champs obligatoires sont remplis (logique fournie)
      if (!data.numero_facture || !data.compte || !data.credit) {
        // log warning only — user must complete fields
        console.warn('Mise à jour bloquée : les champs obligatoires (numero_facture, compte, credit) doivent être remplis.');
        return;
      }

      const field = cell.getField();
      const rawValue = cell.getValue();

      // Ne pas envoyer les champs debit/credit
      if (field === "debit" || field === "credit") {
        console.info(`Modification de "${field}" ignorée : champ exclu de la mise à jour.`);
        return;
      }

      // Préparer valeur normalisée selon champ
      let sendValue = rawValue;
      if (field === 'compte' || field === 'contre_partie' || field === 'compte_tva') {
        sendValue = normalizeAccountField(rawValue);
      } else if (field === 'rubrique_tva') {
        // forcer string ou null (évite erreur "must be a string")
        sendValue = (rawValue === null || typeof rawValue === 'undefined' || String(rawValue).trim() === '') ? null : String(rawValue);
      } else if (field === 'numero_facture') {
        sendValue = String(rawValue).trim();
      } else {
        if (sendValue === null || typeof sendValue === 'undefined') sendValue = null;
      }

      // Préparer payload – conforme à ton endpoint PUT /operations/{id}
      const updateData = {
        field: field,
        value: sendValue,
        numero_facture: data.numero_facture // utile côté serveur si tu relies aux factures
      };

      // lock
      editingLocks.set(data.id, true);

      const res = await fetch(`/operations/${encodeURIComponent(data.id)}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify(updateData),
        credentials: 'same-origin'
      });

      if (!res.ok) {
        const txt = await res.text().catch(()=>null);
        console.error(`Erreur HTTP ${res.status} lors de update operation ${data.id}`, txt);
        editingLocks.delete(data.id);
        return;
      }

      const json = await res.json().catch(()=>null);
      // Si le serveur renvoie la ligne mise à jour, on l'applique
      if (json && (json.data || json)) {
        const updated = Array.isArray(json.data) ? json.data[0] : (json.data ?? json);
        if (updated && typeof updated === 'object') {
          try {
            row.update(updated);
          } catch (err) {
            console.warn('row.update failed', err, updated);
          }
        }
      }

      // relancer calculs / rafraîchissements locaux si besoin
      if (typeof updateTabulatorDataOp === 'function') {
        try { updateTabulatorDataOp(); } catch(e){ console.warn('updateTabulatorDataOp failed', e); }
      }
      if (typeof calculerSoldeCumuleOperationsDiverses === 'function') {
        try { calculerSoldeCumuleOperationsDiverses(); } catch(e){ console.warn('calculerSoldeCumuleOperationsDiverses failed', e); }
      }

      // succès bref en toast uniquement
      notifySuccess('La ligne a été mise à jour avec succès.', 'Mis à jour');

      editingLocks.delete(data.id);
      console.log('Mise à jour réussie pour id=', data.id);
    } catch (error) {
      console.error("Erreur de mise à jour :", error);
      if (cell && cell.getRow) {
        const d = cell.getRow().getData();
        if (d && d.id) editingLocks.delete(d.id);
      }
    }
  })();
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


// Fonction de mise à jour des données du tableau Opérations Diverses en fonction des filtres
function updateTabulatorDataOp() {
  const mois           = document.getElementById("periode-operations-diverses").value;
  const annee          = document.getElementById("annee-operations-diverses").value;
  const codeJournal    = document.getElementById("journal-operations-diverses").value;
  const filtreExercice = document.getElementById("filter-exercice-operations-diverses").checked;

  // --- Alerte douce (SweetAlert2) ---
  const showSwal = (message) => {
    Swal.fire({
      icon: 'info',
      title: 'Filtrage Opérations Diverses',
      text: message,
      timer: 2500,
      showConfirmButton: false,
      toast: true,
      position: 'top-end',
      background: '#fff8e6',
      color: '#856404'
    });
  };

  // --- Si aucun code journal sélectionné ---
  if (!codeJournal || codeJournal === "" || codeJournal === "selectionner") {
    // showSwal("Veuillez sélectionner un journal pour afficher les opérations diverses.");
    if (typeof tableOP !== "undefined" && tableOP) tableOP.clearData();
    return;
  }

  // --- Si aucun mois sélectionné et pas d'exercice complet ---
  if ((!mois || mois === "selectionner un mois") && !filtreExercice) {
    // showSwal("Veuillez sélectionner un mois ou cocher « Exercice entier » pour afficher les opérations diverses.");
    if (typeof tableOP !== "undefined" && tableOP) tableOP.clearData();
    return;
  }

  // --- Si année non valide ---
  if (!annee || isNaN(annee)) {
    // showSwal("Veuillez saisir une année valide.");
    if (typeof tableOP !== "undefined" && tableOP) tableOP.clearData();
    return;
  }

  // --- Préparation des données à envoyer ---
  let dataToSend = { categorie: "Opérations Diverses" };

  if (filtreExercice && annee) {
    dataToSend.annee = annee;
    if (codeJournal) dataToSend.code_journal = codeJournal;
  } else {
    if (mois && annee) dataToSend.mois = mois, dataToSend.annee = annee;
    if (codeJournal) dataToSend.code_journal = codeJournal;
  }

  console.log("📤 Filtrage opérations-diverses appliqué :", dataToSend);

  // --- Envoi de la requête ---
  fetch("/get-operations", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]').content,
    },
    body: JSON.stringify(dataToSend),
  })
  .then(response => response.json())
  .then(payload => {
    const data = Array.isArray(payload)
      ? payload
      : (payload && Array.isArray(payload.data) ? payload.data : []);

    if (typeof tableOP === 'undefined' || !tableOP) {
      console.warn("⚠️ tableOP non initialisée — impossible de remplacer les données pour Opérations Diverses.");
      return;
    }

    tableOP.replaceData(data).then(() => {
      try { applyDefaultContrePartieToTable(tableOP, codeJournal); } catch {}
      try { tableOP.redraw(true); } catch {}
      try { calculerSoldeCumuleOperationsDiverses(); } catch {}

      // ✅ Si aucune donnée reçue → ajouter une ligne vide
      if (data.length === 0) {
        tableOP.addRow({
          id: null,
          compte: '',
          contre_partie: ContrePartie ,
          debit: 0,
          credit: 0,
          piece_justificative: '',
          type_journal: codeJournal,
          value: ''
        });
      }
    }).catch(err => {
      console.error("Erreur lors du replaceData sur tableOP :", err);
    });
  })
  .catch(error => {
    console.error("Erreur lors de la mise à jour Opérations Diverses :", error);
  });
}


// Écouteurs pour les filtres Opérations Diverses (y compris la nouvelle radio)
document.getElementById("journal-operations-diverses").addEventListener("change", updateTabulatorDataOp);
document.getElementById("periode-operations-diverses").addEventListener("change", updateTabulatorDataOp);
document.getElementById("annee-operations-diverses").addEventListener("input", updateTabulatorDataOp);
document.getElementById("filter-exercice-operations-diverses").addEventListener("change", updateTabulatorDataOp);

// Chargement initial des données Opérations Diverses
updateTabulatorDataOp();

/////////////////////////gestion OD /////////////////////////////////
// --------------------------- Fonctions exportées (globales) ---------------------------
/* ---------- Utilities & network helpers ---------- */

// function supprimerDoublonsLignes(lignes) {
//   const uniques = [];
//   const seen = new Set();
//   (lignes || []).forEach(l => {
//     const id = l && (l.id ?? null);
//     if (id === null || id === undefined) {
//       uniques.push(l);
//     } else if (!seen.has(id)) {
//       seen.add(id);
//       uniques.push(l);
//     }
//   });
//   return uniques;
// }

async function fetchRubriqueForJournal(codeJournal) {
  if (!codeJournal) return { ok: false, error: 'codeJournal manquant' };
  try {
    const res = await fetch(`/getRubriqueSociete?code_journal=${encodeURIComponent(codeJournal)}`, { credentials: 'same-origin' });
    if (!res.ok) return { ok: false, status: res.status };
    const json = await res.json();
    return { ok: true, data: json };
  } catch (err) {
    return { ok: false, error: err.message };
  }
}

/**
 * Fetch info journal (contre_partie default). Essaie plusieurs endpoints en fallback.
 */
async function fetchJournalInfo(codeJournal) {
  if (!codeJournal) return null;
  const tries = [
    `/getJournalInfo?code_journal=${encodeURIComponent(codeJournal)}`,
    `/getJournalByCode?code_journal=${encodeURIComponent(codeJournal)}`,
    `/getRubriqueSociete?code_journal=${encodeURIComponent(codeJournal)}`
  ];
  for (const url of tries) {
    try {
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) continue;
      const js = await res.json();
      // recherche la propriété qui contient la contre_partie
      const cp = js.contre_partie ?? js.compte_default ?? js.compte ?? js.compte_tva ?? null;
      if (cp) return js;
    } catch (e) { /* ignore and try next */ }
  }
  return null;
}

/* ---------- Date & piece helpers ---------- */

function parseDateSafe(value) {
  if (!value) return null;
  try {
    let dt = luxon.DateTime.fromISO(String(value));
    if (!dt.isValid) dt = luxon.DateTime.fromFormat(String(value), "yyyy-MM-dd HH:mm:ss");
    if (!dt.isValid) dt = luxon.DateTime.fromFormat(String(value), "yyyy-MM-dd");
    if (!dt.isValid) dt = luxon.DateTime.fromFormat(String(value), "dd/LL/yyyy");
    return dt.isValid ? dt : null;
  } catch (e) { return null; }
}

function buildNextPieceForRow(rowOrData) {
  try {
    const tab = (typeof tableOP !== 'undefined') ? tableOP : null;
    const d = (rowOrData && typeof rowOrData.getData === 'function') ? rowOrData.getData() : (rowOrData || {});
    const dt = parseDateSafe(d.date) || parseDateSafe(d.date_lettrage) || luxon.DateTime.local();
    const sel = document.querySelector('#journal-operations-diverses') || document.querySelector('#journal-ventes');
    const codeJournal = sel ? (String(sel.value || '').toUpperCase().slice(0,3) || 'JR') : 'JR';
    const prefixByDate = `P${dt.toFormat('MM')}${dt.toFormat('yy')}${codeJournal}`;

    const all = tab ? tab.getData() : [];
    const pieces = (all || []).map(r => r.piece_justificative).filter(p => p && typeof p === 'string' && p.trim() !== '');
    let maxNum = -1, selectedPrefix = '';
    pieces.forEach(p => {
      const m = String(p).match(/^(.+?)(\d{1,6})$/);
      if (m) {
        const pr = m[1], num = parseInt(m[2],10);
        if (!isNaN(num) && num > maxNum) { maxNum = num; selectedPrefix = pr; }
      }
    });
    const next = (maxNum === -1) ? 1 : (maxNum + 1);
    const padded = String(next).padStart(4,'0');
    if (selectedPrefix && selectedPrefix !== prefixByDate) return `${selectedPrefix}${padded}`;
    return `${prefixByDate}${padded}`;
  } catch (e) {
    console.error('buildNextPieceForRow error', e);
    return 'AUTO-0000';
  }
}

/* ---------- Misc helpers ---------- */

function generateTempId() {
  return `tmp-${Date.now()}-${Math.random().toString(36).slice(2,8)}`;
}

function isNumericId(val) {
  if (typeof val === 'number') return true;
  if (!val) return false;
  return /^[0-9]+$/.test(String(val));
}

/* ---------- UI helpers for fact_lettrer (formatter/editor) ---------- */

function factLettrerFormatter(cell, formatterParams, onRendered) {
  const v = cell.getValue();
  if (!v) return '';
  // v can be "numero|montant|date" or "numero|montant|date & numero2|..." or cleaned already
  const parts = String(v).split('&').map(s => s.trim()).filter(Boolean);
  const numeros = parts.map(p => {
    const sub = p.split('|').map(s => s.trim());
    return sub[0] || sub[0] === '' ? sub[0] : p; // if original had id removed and only numero|... then sub[0] is numero; if cleaned maybe only numero|montant|date -> we keep numero
  });
  return numeros.join(' & ');
}

// fournit un petit editor qui renvoie la même chaîne (l'édition quantitative est rare)
function factLettrerEditor(cell, onRendered, success, cancel, editorParams) {
  const v = cell.getValue() || '';
  const input = document.createElement('input');
  input.type = 'text';
  input.value = factLettrerFormatter({ getValue: () => v }, null);
  input.style.width = '100%';
  onRendered(() => input.focus());
  input.addEventListener('blur', () => success(String(input.value || '').trim()));
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { success(String(input.value || '').trim()); }
    if (e.key === 'Escape') cancel();
  });
  return input;
}

/* --------------------------- Generators (mirror / TVA) --------------------------- */

/**
 * Ajouter lignes pré-remplies (retourne seulement les lignes supplémentaires à ajouter sous l'origine)
 * - utilise date_lettrage (et date)
 * - gère cas TVA spécifique (3421/6147 + orig crédit) => retourne TVA + Imputation
 * - sinon retourne 1 mirror line (compte = contre_partie origine, swap debit/credit)
 * - Returned lines have id = null (or temp id) but when sending payload, JS ensures id numeric -> null
 */
async function ajouterLignePreRemplieDiverses(idCounter, ligneActive, codeJournal, filterDiverses) {
  function stripIntitule(val) {
    if (!val) return '';
    const parts = String(val).trim().split(/\s*-\s*/, 2);
    return parts[0] ? parts[0].trim() : '';
  }

  const base = Object.assign({}, ligneActive || {});
  const numeroFactureCommun = (base.numero_facture && String(base.numero_facture).trim() !== '') ? String(base.numero_facture).trim() : null;
  const factLettrerOrig = (typeof base.fact_lettrer !== 'undefined') ? base.fact_lettrer : null;
  base.piece_justificative = base.piece_justificative || buildNextPieceForRow(base);

  const compteClean = stripIntitule(base.compte || '');
  const cpClean = stripIntitule(base.contre_partie || '');
  const debitOrig = Number(base.debit || 0);
  const creditOrig = Number(base.credit || 0);

  // CAS TVA special: contre_partie 6147* && compte 3421* && origine credit > 0
  if (/^6147/.test(cpClean) && /^3421/.test(compteClean) && creditOrig > 0) {
    // récupérer compte TVA + taux (essayons plusieurs endpoints)
    let taux = null;
    let compteTVA = null;
    try {
      const r = await fetch(`/racine-tva/142`, { credentials: 'same-origin' });
      if (r.ok) {
        const js = await r.json();
        compteTVA = js.compte_tva ?? js.compte ?? null;
        if (typeof js.taux !== 'undefined' && js.taux !== null && js.taux !== '') {
          const t = Number(js.taux);
          if (!isNaN(t)) taux = (t > 1 ? t / 100 : t);
        }
      }
    } catch (e) { /* ignore */ }
    if (taux === null) {
      try {
        const r2 = await fetch(`/tva-config?racine=${encodeURIComponent(142)}`, { credentials: 'same-origin' });
        if (r2.ok) {
          const js2 = await r2.json();
          compteTVA = js2.compte || js2.compte_tva || null;
          taux = Number(js2.taux || js2.taux_tva || 0.20) || 0.20;
        }
      } catch (e2) { /* ignore */ }
    }
    if (taux === null || isNaN(taux)) taux = 0.20;
    if (!compteTVA) compteTVA = '44571';
    compteTVA = String(compteTVA).trim();

    const credit = creditOrig;
    const baseHT = credit / (1 + taux);
    let debitTVA = Number((baseHT * taux).toFixed(2));
    let debitImputation = Number((credit - debitTVA).toFixed(2));
    const somme = Number((debitTVA + debitImputation).toFixed(2));
    const diff = Number((credit - somme).toFixed(2));
    if (Math.abs(diff) >= 0.01) debitImputation = Number((debitImputation + diff).toFixed(2));

    const dateVal = base.date || new Date().toISOString().slice(0,10);

    const ligneTVA = {
      id: null,
      __generated: true,
      date: dateVal,
      date_lettrage: base.date_lettrage || dateVal,
      numero_dossier: base.numero_dossier || null,
      numero_facture: numeroFactureCommun,
      compte: String(compteTVA),
      debit: debitTVA,
      credit: 0,
      contre_partie: compteClean || base.compte || '',
      piece_justificative: base.piece_justificative || '',
      libelle: (base.libelle ? (base.libelle + ' ') : '') + `TVA (${Math.round(taux*100)}%)`,
      type_journal: codeJournal || '',
      categorie: 'Opérations Diverses',
      rubrique_tva: String(142),
      compte_tva: String(compteTVA),
      fact_lettrer: factLettrerOrig
    };

    const ligneImputation = {
      id: null,
      __generated: true,
      date: dateVal,
      date_lettrage: ligneTVA.date_lettrage,
      numero_dossier: base.numero_dossier || null,
      numero_facture: numeroFactureCommun,
      compte: cpClean || base.contre_partie || '',
      debit: debitImputation,
      credit: 0,
      contre_partie: compteClean || base.compte || '',
      piece_justificative: base.piece_justificative || '',
      libelle: base.libelle || '',
      type_journal: codeJournal || '',
      categorie: 'Opérations Diverses',
      fact_lettrer: factLettrerOrig
    };

    return [ligneTVA, ligneImputation];
  }

  // Mirror case (compte = contre_partie origine)
  const destCompte = cpClean || base.contre_partie || '';
  if (!destCompte || destCompte === '') return [];
  const mirror = {
    id: null,
    __generated: true,
    date: base.date || null,
    date_lettrage: base.date_lettrage || base.date || null,
    numero_dossier: base.numero_dossier || null,
    numero_facture: numeroFactureCommun,
    compte: destCompte,
    contre_partie: compteClean || base.compte || '',
    debit: creditOrig > 0 ? creditOrig : 0,
    credit: debitOrig > 0 ? debitOrig : 0,
    piece_justificative: base.piece_justificative || '',
    libelle: base.libelle || '',
    type_journal: codeJournal || '',
    categorie: 'Opérations Diverses',
    fact_lettrer: factLettrerOrig
  };

  return [mirror];
}

/* ---------- Main functions: add lines, save, expand ---------- */

/**
 * Ajouter lignes (si preRemplir=true, ajoute uniquement les lignes générées)
 * - blank line creation pre-fills contre_partie from selected journal when possible
 */
async function ajouterLigneDiverses(table, preRemplir = false, ligneActive = null) {
  if (!table) { console.error('ajouterLigneDiverses: table introuvable'); return []; }
  const idCounter = (table.getData() || []).length + 1;
  const codeJournal = document.querySelector('#journal-operations-diverses')?.value || '';
  const filterDiverses = document.querySelector('input[name="filter-operations-diverses"]:checked')?.value || 'libre';

  const source = (ligneActive && typeof ligneActive.getData === 'function') ? ligneActive.getData() : (ligneActive || null);
  if (preRemplir && source) {
    if (filterDiverses === 'libre') return []; // ne rien générer pour libre

    let nouvelles = await ajouterLignePreRemplieDiverses(idCounter, source, codeJournal, filterDiverses);
    if (!Array.isArray(nouvelles)) nouvelles = [nouvelles];

    // filter lines with a compte set
    const toAdd = nouvelles.filter(l => l && l.compte && String(l.compte).trim() !== '');
    const existing = table.getData() || [];
    const alreadyExists = (cand) => existing.some(e => {
      try {
        return String(e.compte || '').trim() === String(cand.compte || '').trim()
          && String(e.contre_partie || '').trim() === String(cand.contre_partie || '').trim()
          && Number(e.debit || 0) === Number(cand.debit || 0)
          && Number(e.credit || 0) === Number(cand.credit || 0)
          && String(e.piece_justificative || '').trim() === String(cand.piece_justificative || '').trim();
      } catch (err) { return false; }
    });

    const added = [];
    for (const l of toAdd) {
      if (alreadyExists(l)) { console.debug('Skipping duplicate generated line', l); continue; }
      try { table.addRow(l, false); added.push(l); } catch (e) { console.warn('addRow failed', e, l); }
    }

    // dedupe by id (still possible)
    const dataAfter = supprimerDoublonsLignes(table.getData());
    table.setData(dataAfter);

    return added;
  }

  // default: add a blank row — try prefill contre_partie from selected journal
  const blank = {
    id: generateTempId(),
    date: '',
    date_lettrage: '',
    numero_facture: '',
    compte: '',
        contre_partie: ContrePartie,
    debit: 0,
    credit: 0,
    piece_justificative: '',
    libelle: '',
    rubrique_tva: '',
    compte_tva: '',
    type_journal: codeJournal,
    categorie: 'Opérations Diverses',
    fact_lettrer: null
  };

  // attempt to fetch default contre_partie for the selected journal (async)
  try {
    const journalInfo = await fetchJournalInfo(codeJournal);
    const defaultCp = journalInfo ? (journalInfo.contre_partie ?? journalInfo.compte ?? journalInfo.compte_tva ?? null) : null;
    if (defaultCp) blank.contre_partie = String(defaultCp).trim();
  } catch (e) { /* ignore */ }

  try { table.addRow(blank, false); } catch (e) { console.warn('add blank row failed', e); }
  return [blank];
}

/**
 * Expand a single ligne client-side into TVA lines when applicable.
 * Ignore lines already generated (__generated)
 */
async function expandLigneWithTVA_Client(ligne) {
  if (!ligne || typeof ligne !== 'object') return [ligne];
  if (ligne.__generated) return [ligne];

  const compte = String(ligne.compte || '');
  const contre = String(ligne.contre_partie || '');
  const credit = parseFloat(ligne.credit || 0);
  if (!/^6147/.test(contre) || !/^3421/.test(compte) || !(credit > 0)) return [ligne];

  let tvaInfo = { compte: '44571', taux: 0.20 };
  try {
    const resp = await fetch(`/tva-config?racine=${encodeURIComponent(142)}`, { credentials: 'same-origin' });
    if (resp.ok) {
      const js = await resp.json();
      tvaInfo = { compte: String(js.compte || js.compte_tva || '44571').trim(), taux: parseFloat(js.taux || js.taux_tva || 0.20) || 0.20 };
    }
  } catch (e) { console.warn('getTVAForRacine failed', e); }
  const taux = Number.isFinite(tvaInfo.taux) ? tvaInfo.taux : 0.20;
  const compteTVA = String(tvaInfo.compte || '44571').trim();

  const baseHT = credit / (1 + taux);
  let debitL2 = Number((baseHT * taux).toFixed(2));
  let debitL3 = Number((credit - debitL2).toFixed(2));
  const somme = Number((debitL2 + debitL3).toFixed(2));
  const diff = Number((credit - somme).toFixed(2));
  if (Math.abs(diff) >= 0.01) debitL3 = Number((debitL3 + diff).toFixed(2));

  const dateValue = ligne.date || new Date().toISOString().slice(0,10);
  const typeJournal = ligne.type_journal || document.querySelector('#journal-operations-diverses')?.value || '';

  const ligne1 = Object.assign({}, ligne, { debit: 0, credit: credit, type_journal: typeJournal });
  const ligne2 = {
    id: null, __generated: true,
    date: dateValue, date_lettrage: ligne1.date_lettrage || dateValue, numero_dossier: ligne1.numero_dossier || null,
    numero_facture: ligne1.numero_facture || null, compte: compteTVA, debit: debitL2, credit: 0,
    contre_partie: ligne1.compte || '', piece_justificative: ligne1.piece_justificative || '', libelle: (ligne1.libelle ? ligne1.libelle + ' ' : '') + `TVA (${Math.round(taux*100)}%)`,
    type_journal: typeJournal, categorie: ligne1.categorie || 'Opérations Diverses', rubrique_tva: String(142), compte_tva: compteTVA, fact_lettrer: ligne1.fact_lettrer ?? null
  };
  const ligne3 = {
    id: null, __generated: true,
    date: dateValue, date_lettrage: ligne1.date_lettrage || dateValue, numero_dossier: ligne1.numero_dossier || null,
    numero_facture: ligne1.numero_facture || null, compte: ligne1.contre_partie || '', debit: debitL3, credit: 0,
    contre_partie: ligne1.compte || '', piece_justificative: ligne1.piece_justificative || '', libelle: ligne1.libelle || '',
    type_journal: typeJournal, categorie: ligne1.categorie || 'Opérations Diverses', fact_lettrer: ligne1.fact_lettrer ?? null
  };

  const totalDebit = Number((ligne1.debit || 0) + (ligne2.debit || 0) + (ligne3.debit || 0));
  const totalCredit = Number((ligne1.credit || 0) + (ligne2.credit || 0) + (ligne3.credit || 0));
  const delta = Number((totalCredit - totalDebit).toFixed(2));
  if (Math.abs(delta) >= 0.01) ligne3.debit = Number((ligne3.debit + delta).toFixed(2));

  return [ligne1, ligne2, ligne3];
}

/* ---------- Save (bulk) ---------- */

async function enregistrerLignesDiverses() {
  try {
    const table = (typeof tableOP !== 'undefined') ? tableOP : null;
    if (!table) throw new Error('tableOP introuvable');

    const all = table.getData() || [];

    // ensure piece present
    const ensured = all.map(d => Object.assign({}, d, {
      piece_justificative: (d.piece_justificative && String(d.piece_justificative).trim()) ? d.piece_justificative : buildNextPieceForRow(d)
    }));

    const selectedFilter = document.querySelector('input[name="filter-operations-diverses"]:checked')?.value || null;

    let lignesEtendues = [];
    for (const li of ensured) {
      if (li.__generated) {
        lignesEtendues.push(li);
        continue;
      }
      if (selectedFilter === 'contre-partie') {
        try {
          const expanded = await expandLigneWithTVA_Client(li).catch(()=>[li]);
          if (Array.isArray(expanded)) lignesEtendues.push(...expanded);
          else lignesEtendues.push(li);
        } catch (e) {
          lignesEtendues.push(li);
        }
      } else {
        lignesEtendues.push(li);
      }
    }

    // prepare payload: ensure id is integer or null; rubrique_tva string if present
    const payloadLines = lignesEtendues.map(l => {
      const normalizedId = isNumericId(l.id) ? (typeof l.id === 'number' ? l.id : parseInt(String(l.id),10)) : null;
      return {
        id: normalizedId,
        date: l.date || null,
        date_lettrage: l.date_lettrage || null,
        numero_dossier: l.numero_dossier || null,
        numero_facture: (typeof l.numero_facture !== 'undefined' && l.numero_facture !== '') ? l.numero_facture : null,
        compte: l.compte ? String(l.compte).trim() : '',
        debit: l.debit ? parseFloat(l.debit) : 0,
        credit: l.credit ? parseFloat(l.credit) : 0,
        contre_partie: l.contre_partie || '',
        type_journal: l.type_journal || document.querySelector('#journal-operations-diverses')?.value || '',
        categorie: l.categorie || 'Opérations Diverses',
        rubrique_tva: (typeof l.rubrique_tva === 'undefined' || l.rubrique_tva === null) ? null : String(l.rubrique_tva),
        compte_tva: l.compte_tva ? String(l.compte_tva).trim() : null,
        prorat_de_deduction: l.prorat_de_deduction || null,
        piece_justificative: l.piece_justificative || '',
        libelle: l.libelle || '',
        filtre_selectionne: selectedFilter,
        client_ref: l.client_ref || null,
        fact_lettrer: l.fact_lettrer || null,
        file_id: l.file_id || null
      };
    }).filter(l => (l.compte && (Number(l.debit) > 0 || Number(l.credit) > 0)));

    if (payloadLines.length === 0) {
      if (window.Swal) Swal.fire({ icon:'warning', title:'Aucune ligne', text:'Aucune ligne valide à enregistrer (compte manquant ou montants nuls).' });
      else alert('Aucune ligne valide à enregistrer (compte manquant ou montants nuls).');
      return false;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const res = await fetch('/operations/diverses', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' },
      body: JSON.stringify({ lignes: payloadLines })
    });

    const text = await res.text().catch(()=>null);
    let body = null;
    try { body = text ? JSON.parse(text) : null; } catch(e) { body = text; }

    if (!res.ok) {
      console.error('enregistrerLignesDiverses error', res.status, body);
      if (window.Swal) Swal.fire({ icon:'error', title:'Erreur', text: `Enregistrement: ${JSON.stringify(body)}` });
      throw new Error(JSON.stringify(body));
    }

    const data = (body && body.data) ? body.data : (Array.isArray(body) ? body : null);
    if (Array.isArray(data)) {
      table.setData(data);
      if (typeof updateTabulatorDataOp === 'function') updateTabulatorDataOp();
      calculerSoldeCumuleOperationsDiverses();

      // ensure last empty row exists
      const current = table.getData();
      const last = (current && current.length) ? current[current.length - 1] : null;
      if (!last || (last.compte && String(last.compte).trim() !== '')) {
        table.addRow({
          id: null, date: '', date_lettrage: '', numero_facture: '', compte: '',  contre_partie: ContrePartie || '',

          debit: 0, credit: 0, piece_justificative: '', libelle: '', rubrique_tva: '', compte_tva: '',
          type_journal: document.querySelector('#journal-operations-diverses')?.value || '', categorie: 'Opérations Diverses'
        }, false);
      }

      if (window.Swal) Swal.fire({ icon:'success', title:'Enregistré', text: 'Lignes enregistrées avec succès.' });
      return true;
    } else {
      if (window.Swal) Swal.fire({ icon:'warning', title:'Avertissement', text: 'Réponse serveur inattendue.' });
      return false;
    }

  } catch (err) {
    console.error('enregistrerLignesDiverses failed', err);
    if (window.Swal) Swal.fire({ icon:'error', title:'Erreur', text: err.message || 'Erreur lors de l\'enregistrement.' });
    throw err;
  }
}

/* ---------- Keyboard / Tabulator integration ---------- */

async function ecouterEntrerDiverses(table) {
  if (!table) table = (typeof tableOP !== 'undefined') ? tableOP : null;
  if (!table) { console.error('ecouterEntrerDiverses: table introuvable'); return; }

  if (table._enterListenerAttached) return;
  table._enterListenerAttached = true;

  table.element.addEventListener('keydown', async function (event) {
    if (event.key !== 'Enter') return;
    event.preventDefault();

    const selectedRows = table.getSelectedRows();
    if (!selectedRows || selectedRows.length === 0) {
      console.error('Aucune ligne active trouvée (Diverses)');
      return;
    }
    const ligneActive = selectedRows[0].getData();
    try {
      let nouvelles = await ajouterLigneDiverses(table, true, ligneActive);
      if (!Array.isArray(nouvelles)) nouvelles = [nouvelles];
      console.log('Lignes ajoutées (Diverses):', nouvelles);

      // ensure last empty row exists
      const dataActuelle = table.getData();
      const derniere = dataActuelle[dataActuelle.length - 1];
      if (!derniere || (derniere.compte && String(derniere.compte).trim() !== '')) {
        table.addRow({
          id: null, date: '', date_lettrage: '', numero_facture: '', compte: '',         contre_partie: ContrePartie ,

          debit: 0, credit: 0, piece_justificative: '', libelle: '', rubrique_tva: '',
          type_journal: document.querySelector('#journal-operations-diverses')?.value || '',
          categorie: 'Opérations Diverses'
        }, false);
      }

      // bulk save all lines
      await enregistrerLignesDiverses();
    } catch (err) {
      console.error('ecouterEntrerDiverses handler error', err);
    } finally {
      try { selectedRows[0].deselect(); } catch(e){}
    }
  }, true);
}

/* ---------- Solde cumulé ---------- */

function calculerSoldeCumuleOperationsDiverses() {
  try {
    const tab = (typeof tableOP !== 'undefined') ? tableOP : null;
    if (!tab) return;
    const rows = tab.getRows() || [];
    const groupSums = {};
    const factures = {};

    rows.forEach(row => {
      const data = row.getData();
      const key = `${data.numero_facture || ''}`;
      if (typeof groupSums[key] === 'undefined') groupSums[key] = 0;
      const debit = parseFloat(data.debit || 0) || 0;
      const credit = parseFloat(data.credit || 0) || 0;
      let nouveauSolde = groupSums[key] + debit - credit;
      if (Math.abs(nouveauSolde) < Number.EPSILON) nouveauSolde = 0;
      const displayValue = nouveauSolde.toFixed(2);
      groupSums[key] = nouveauSolde;
      try { row.update({ value: displayValue }); } catch(e){}
      if (!factures[key]) factures[key] = { lastRow: row, lastSolde: nouveauSolde };
      else { factures[key].lastRow = row; factures[key].lastSolde = nouveauSolde; }
    });

    for (const numero in factures) {
      const { lastRow, lastSolde } = factures[numero];
      if (!lastRow) continue;
      const cellEl = lastRow.getCell("value") ? lastRow.getCell("value").getElement() : null;
      if (!cellEl) continue;
      if (Math.abs(lastSolde) > 0.00) cellEl.classList.add("highlight-error");
      else cellEl.classList.remove("highlight-error");
    }
    tab.redraw();
  } catch (e) { console.error('calculerSoldeCumuleOperationsDiverses error', e); }
}

/* ---------- Exports & init ---------- */

window.supprimerDoublonsLignes = supprimerDoublonsLignes;
window.ajouterLignePreRemplieDiverses = ajouterLignePreRemplieDiverses;
window.ajouterLigneDiverses = ajouterLigneDiverses;
window.enregistrerLignesDiverses = enregistrerLignesDiverses;
window.ecouterEntrerDiverses = ecouterEntrerDiverses;
window.fetchRubriqueForJournal = fetchRubriqueForJournal;
window.fetchJournalInfo = fetchJournalInfo;
window.calculerSoldeCumuleOperationsDiverses = calculerSoldeCumuleOperationsDiverses;
window.factLettrerFormatter = factLettrerFormatter;
window.factLettrerEditor = factLettrerEditor;

(function initDiverses() {
  try {
    const table = (typeof tableOP !== 'undefined') ? tableOP : null;
    if (!table) { console.warn('initDiverses: tableOP introuvable'); return; }

    ecouterEntrerDiverses(table);

    // cellEdited logic (generate piece & duplicate date->date_lettrage)
    table.on && table.on('cellEdited', function(cell) {
      try {
        if (!cell) return;
        const field = cell.getField();
        if (field !== 'date') return;
        const row = cell.getRow();
        const data = row.getData();
        if ((!data.date_lettrage || data.date_lettrage === '') && data.date) {
          const dt = parseDateSafe(data.date || data.date_lettrage || null);
          if (dt) row.update({ date_lettrage: dt.toFormat('yyyy-MM-dd HH:mm:ss') });
        }
        if (!data.piece_justificative || String(data.piece_justificative||'').trim() === '') {
          const piece = buildNextPieceForRow(row);
          if (piece) row.update({ piece_justificative: piece });
        }
        calculerSoldeCumuleOperationsDiverses();
      } catch (e) { console.error('initDiverses cellEdited handler', e); }
    });

    table.on && table.on('dataLoaded', function(){ try { calculerSoldeCumuleOperationsDiverses(); } catch(e){} });

    console.info('Module Opérations Diverses initialisé.');
  } catch (e) { console.error('initDiverses error', e); }
})();







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
//  $(document).ready(function() {

//   // afficher modal Achat & charger racine
// document.getElementById('files_achat_Modal').style.display = 'flex';
// $.ajax({ type:'GET', url: '/select-folder-achat', data: { id: 0 }, success: updateFolderAndFileListsAchat });

// });

  // 0) Récupère la pièce depuis le fragment #piece-...
  function getPieceFromHash() {
    const hash = window.location.hash; // "#piece-P0125GEN0002"
    if (!hash.startsWith("#piece-")) return null;
    return decodeURIComponent(hash.replace("#piece-", ""));
  }
  const piece = getPieceFromHash();
  if (!piece) return console.warn("Pas de pièce dans l'URL");

  // 1) Charge les données et met à jour UI & tableau
async function chargerEcritures() {
   try {
    const res = await fetch(`/api/operation_courante/by-piece/${encodeURIComponent(piece)}`);
    if (!res.ok) throw new Error("Erreur serveur");
    const lignes = await res.json();

    if (!lignes.length) {
      console.warn("Aucune ligne pour cette pièce :", piece);
      return;
    }

    // 1️⃣ Calcul du solde cumulé et injection dans "value"
    let cumul = 0;
    lignes.forEach(l => {
      const debit  = parseFloat(l.debit)  || 0;
      const credit = parseFloat(l.credit) || 0;
      cumul += debit - credit;
      l.value = parseFloat(cumul.toFixed(2));

      // on force aussi la pièce justificative si besoin
      l.piece_justificative = piece;
    });

    // 2️⃣ Injection dans Tabulator
    tableAch.setData(lignes);

    // 3️⃣ Remplissage du <select> Journal
    const journaux = [...new Set(lignes.map(l => l.type_journal))];
    $('#journal-achats').empty().append(
      journaux.map(code => `<option>${code}</option>`)
    );
    if (journaux.length) {
      const code0 = journaux[0];
      $('#journal-achats').val(code0);
      $('#filter-intitule-achats').val(
        lignes.find(l => l.type_journal === code0)?.intitule_journal || ''
      );
      tableAch.setFilter("type_journal", "=", code0);
    }

    // 4️⃣ Remplissage du <select> Période (MM/YYYY)
    const periodes = [...new Set(lignes.map(l => l.date.slice(0,7)))];
    $('#periode-achats').empty().append(
      periodes.map(ym => {
        const [yyyy, mm] = ym.split("-");
        return `<option value="${ym}">${mm}/${yyyy}</option>`;
      })
    );
    if (periodes.length) {
      const p0 = periodes[0];
      $('#periode-achats').val(p0);
      $('#annee-achats').val(p0.split("-")[0]);
      // **ici**, on passe `data` à la fonction
      tableAch.setFilter(data => data.date.slice(0,7) === p0);
    }

    // 5️⃣ Surbrillance + édition automatique
    setTimeout(() => {
      // getRow attend la valeur d'index configuré : ici piece_justificative
      const row = tableAch.getRow(piece);
      if (row) {
        row.getElement().scrollIntoView({ behavior: "smooth", block: "center" });
        row.getElement().style.backgroundColor = "#ffeeba";
        row.getCells().forEach(cell => {
          if (cell.getColumn().getDefinition().editor) {
            cell.edit(true);
          }
        });
      }
    }, 300);

  } catch (e) {
    console.error("❌ Erreur lors du chargement des écritures :", e);
  }
}
chargerEcritures();


  // 2) Filtre par type_journal
  $('#journal-achats').on('change', function() {
    const code = $(this).val();
    const lib = tableAch.getData().find(l => l.type_journal === code)?.intitule_journal || '';
    $('#filter-intitule-achats').val(lib);
    tableAch.setFilter('type_journal','=',code);
  });

  // 3) Filtre par période
  $('#periode-achats').on('change', function() {
    const ym = $(this).val();
    const [yyyy, mm] = ym.split('-');
    $('#annee-achats').val(yyyy);
    if ($('#filter-mois-achats').is(':checked')) {
      tableAch.setFilter(row => row.getData().date.slice(0,7) === ym);
    } else {
      tableAch.clearFilter(true);
    }
  });

  // 4) Radio Mois / Exercice entier
  $('input[name="filter-period-achats"]').on('change', function(){
    if (this.value === 'exercice') {
      $('#periode-achats').val('');
      const firstDate = tableAch.getData()[0]?.date || '';
      $('#annee-achats').val(firstDate.split('-')[0] || '');
      tableAch.clearFilter(true);
    } else {
      $('#periode-achats').trigger('change');
    }
  });


})();


$(document).on('keydown', function(e) {
    if (e.key === "Enter" && $(e.target).is('input[type="checkbox"]')) {
        const checkboxElement = e.target;

        // Si tableBanque est définie, on l'utilise
        if (window.tableAch) {
            const rows = window.tableAch.getRows();

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const rowElement = row.getElement();

                if (rowElement.contains(checkboxElement)) {
                    const rowData = row.getData();
                    console.log("Donnée de la ligne active :", rowData);

                    sendDataToControllerAchat([rowData]); 
                    return; // stop boucle dès qu'on trouve la ligne
                }
            }
            console.log("Aucune ligne correspondante trouvée dans tableAch.");
        } else {
            // Si tableAch n'existe pas, on initialise tableAch si besoin
            if (!window.tableAch) {
                window.tableAch = new Tabulator("#table-Achat", {
                    // tes options Tabulator ici
                });
            }

            // Puis on récupère les lignes de tableAch
            const rows = window.tableAch.getRows();

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const rowElement = row.getElement();

                if (rowElement.contains(checkboxElement)) {
                    const rowData = row.getData();
                    console.log("Donnée de la ligne active (Achat) :", rowData);

                    sendDataToControllerAchat([rowData]); // Appel fonction achat
                    return; // stop boucle dès qu'on trouve la ligne
                }
            }

            console.log("Aucune ligne correspondante trouvée dans tableAch.");
        }
    }
});



$(document).on('keydown', function(e) {
    if (e.key === "Enter" && $(e.target).is('input[type="checkbox"]')) {
        const checkboxElement = e.target;

        // Si tableBanque est définie, on l'utilise
        if (window.tableVentes) {
            const rows = window.tableVentes.getRows();

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const rowElement = row.getElement();

                if (rowElement.contains(checkboxElement)) {
                    const rowData = row.getData();
                    console.log("Donnée de la ligne active :", rowData);

                    sendDataToControllerVente([rowData]); 
                    return; // stop boucle dès qu'on trouve la ligne
                }
            }
            console.log("Aucune ligne correspondante trouvée dans tableVente.");
        } else {
            // Si tableVentes n'existe pas, on initialise tableVentes si besoin
            if (!window.tableVentes) {
                window.tableVentes = new Tabulator("#table-Vente", {
                    // tes options Tabulator ici
                });
            }

            // Puis on récupère les lignes de tableVentes
            const rows = window.tableVentes.getRows();

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const rowElement = row.getElement();

                if (rowElement.contains(checkboxElement)) {
                    const rowData = row.getData();
                    console.log("Donnée de la ligne active (Vente) :", rowData);

                    sendDataToControllerVente([rowData]); // Appel fonction vente
                    return; // stop boucle dès qu'on trouve la ligne
                }
            }

            console.log("Aucune ligne correspondante trouvée dans tableVente.");
        }
    }
});


document.querySelectorAll('.achatModal_file-card').forEach(function(cardWrapper) {
  cardWrapper.addEventListener('dblclick', function () {
    console.log("✅ Double-click sur fichier détecté");

    const innerCard = this.querySelector('.card');

    const selectedFileId = innerCard?.dataset.fileid || null;
    const selectedFilePath = innerCard?.dataset.filepath || '';
    const fileName = innerCard?.dataset.filename || innerCard?.getAttribute('data-filename') || '';

    $('#achatModal_main').hide();

    if (window.currentPieceRowIndex == null) {
      console.warn("⚠️ Aucune ligne active trouvée");
      return;
    }

    const tableInstance = Tabulator.findTable(".tabulator")[0];
    if (!tableInstance) {
      console.error("❌ Impossible de trouver l’instance Tabulator.");
      return;
    }

    const row = tableInstance.getRow(window.currentPieceRowIndex);
    if (!row) {
      console.warn("⚠️ Ligne active introuvable");
      return;
    }

    // Mise à jour directe de la ligne avec le fichier sélectionné
    row.update({
      file_id: selectedFileId,
      file_url: selectedFilePath,
      filepath: selectedFilePath,
      path: selectedFilePath
    });

    // Mise à jour du bouton "eye"
    const cellElement = row.getCell("selected")?.getElement();
    if (cellElement) {
      const viewBtn = cellElement.querySelector('.icon-btn.view-icon-achat');
      if (viewBtn) {
        viewBtn.dataset.fileId = selectedFileId || '';
        viewBtn.dataset.fileUrl = selectedFilePath || '';
        viewBtn.style.cursor = selectedFilePath ? 'pointer' : 'not-allowed';
        viewBtn.style.opacity = selectedFilePath ? '1' : '0.3';
        viewBtn.title = fileName || 'Voir le fichier';

        // Focus temporaire avec style
        viewBtn.setAttribute('tabindex', '0');
        viewBtn.focus({ preventScroll: true });
        viewBtn.style.outline = '2px solid #0066cc';
        viewBtn.style.outlineOffset = '2px';
        viewBtn.style.backgroundColor = 'rgba(0, 102, 204, 0.1)';
        viewBtn.style.borderRadius = '4px';

        setTimeout(() => {
          viewBtn.removeAttribute('tabindex');
          viewBtn.style.outline = '';
          viewBtn.style.outlineOffset = '';
          viewBtn.style.backgroundColor = '';
          viewBtn.style.borderRadius = '';
        }, 3000);
      }
    }

    // Stockage des IDs pour suivi global si nécessaire
    if (selectedFileId && !fileIds.includes(selectedFileId)) {
      fileIds.push(selectedFileId);
      console.log("📁 Fichier ajouté :", selectedFileId);
    }
  });
});



function sendDataToControllerAchat(data) {
  const selectedJournalCodeAchat = $('#journal-achats').val();
  console.log("Code journal sélectionné :", selectedJournalCodeAchat);

  isSending = true;

  console.log("Données à envoyer :", data);

  // Formatage des dates
  function formatDate(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('/');
    if (parts.length !== 3) return dateStr;
    let [day, month, year] = parts;
    day = day.padStart(2, '0');
    month = month.padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  data.forEach(row => {
    const formattedDate = formatDate(row.date);
    const formattedDeliveryDate = formatDate(row.date_livraison || row.date);

    let compteValue = '';
    if (row.compte) {
      const compteObj = planComptable.find(c => c.id == row.compte);
      compteValue = compteObj ? compteObj.compte : row.compte;
    }
    console.log('Compte value :', compteValue);
    let factLettrerString = '';
    if (row.fact_lettrer) {
      if (Array.isArray(row.fact_lettrer) && row.fact_lettrer.length > 0) {
        factLettrerString = row.fact_lettrer
          .map(item => {
            const [id, numero, montant, date] = item.split('|');
            return `${id}|${numero}|${montant}|${date}`;
          })
          .join(' & ');
      } else if (typeof row.fact_lettrer === 'string') {
        // Normalize string (remove extra spaces around & and trim parts)
        factLettrerString = row.fact_lettrer
          .split(/\s*&\s*/)
          .map(s => s.trim())
          .filter(Boolean)
          .map(item => {
            const parts = item.split('|').map(p => p.trim());
            if (parts.length === 4) return `${parts[0]}|${parts[1]}|${parts[2]}|${parts[3]}`;
            return item; // if unexpected format, keep as-is
          })
          .join(' & ');
      }
    }
    $.ajax({
      url: '/operation-courante-achat-store',
      method: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        date: formattedDate,
        date_livraison: formattedDeliveryDate,
        numero_facture: row.numero_facture,
        numero_dossier: row.numero_dossier,
        compte: compteValue,
        libelle: row.libelle,
        debit: row.debit,
        credit: row.credit,
        fact_lettrer: factLettrerString,
        contre_partie: row.contre_partie,
        piece_justificative: row.piece_justificative,
        taux_ras_tva: row.taux_ras_tva,
        nature_op: row.nature_op,
        prorat_de_deduction: row.prorat_de_deduction ? row.prorat_de_deduction : "Non",
        mode_pay: row.mode_pay,
        code_journal: selectedJournalCodeAchat,
        saisie_choisie: getSaisieChoisieAchat(),
        file_id: row.file_id // <-- prend directement le file_id de la ligne
      },
      success: function(response) {
        updateTabulatorDataAchats();
      },
      error: function(xhr, status, error) {
        console.error("Erreur AJAX :", xhr, status, error);
        alert("Erreur lors de l'envoi des données : " + error);
      }
    });
  });
}
function sendDataToControllerVente(data) {
  const selectedJournalCodeVente = $('#journal-ventes').val();
  console.log("Code journal sélectionné :", selectedJournalCodeVente);

  isSending = true;

  console.log("Données à envoyer :", data);

  // Formatage des dates
  function formatDate(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('/');
    if (parts.length !== 3) return dateStr;
    let [day, month, year] = parts;
    day = day.padStart(2, '0');
    month = month.padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  data.forEach(row => {
   
    const formattedDate = formatDate(row.date);
    const formattedDeliveryDate = formatDate(row.date_livraison || row.date);

    let compteValue = '';
    if (row.compte) {
      const compteObj = planComptable.find(c => c.id == row.compte);
      compteValue = compteObj ? compteObj.compte : row.compte;
    }
    console.log('Compte value :', compteValue);
    let factLettrerString = '';
    if (row.fact_lettrer) {
      if (Array.isArray(row.fact_lettrer) && row.fact_lettrer.length > 0) {
        factLettrerString = row.fact_lettrer
          .map(item => {
            const [id, numero, montant, date] = item.split('|');
            return `${id}|${numero}|${montant}|${date}`;
          })
          .join(' & ');
      } else if (typeof row.fact_lettrer === 'string') {
        // Normalize string (remove extra spaces around & and trim parts)
        factLettrerString = row.fact_lettrer
          .split(/\s*&\s*/)
          .map(s => s.trim())
          .filter(Boolean)
          .map(item => {
            const parts = item.split('|').map(p => p.trim());
            if (parts.length === 4) return `${parts[0]}|${parts[1]}|${parts[2]}|${parts[3]}`;
            return item; // if unexpected format, keep as-is
          })
          .join(' & ');
      }
    }
    $.ajax({
      url: '/operation-courante-vente-store',
      method: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        date: formattedDate,
        date_livraison: formattedDeliveryDate,
        numero_facture: row.numero_facture,
        numero_dossier: row.numero_dossier,
        compte: compteValue,
        libelle: row.libelle,
        debit: row.debit,
        credit: row.credit,
        fact_lettrer: factLettrerString,
        contre_partie: row.contre_partie,
        piece_justificative: row.piece_justificative,
        taux_ras_tva: row.taux_ras_tva,
        nature_op: row.nature_op,
        prorat_de_deduction: row.prorat_de_deduction ? row.prorat_de_deduction : "Non",
        mode_pay: row.mode_pay,
        code_journal: selectedJournalCodeVente,
        saisie_choisie: getSaisieChoisieVente(),
        file_id: row.file_id,
      },
      success: function(response) {
        updateTabulatorDataAchats();
      },
      error: function(xhr, status, error) {
        console.error("Erreur AJAX :", xhr, status, error);
        alert("Erreur lors de l'envoi des données : " + error);
      }
    });
  });
}

function getSaisieChoisieAchat() {
    return $('input[name="filter-achats"]:checked').val(); // Récupérer la valeur du bouton radio sélectionné

}
function getSaisieChoisieVente() {
    return $('input[name="filter-ventes"]:checked').val(); // Récupérer la valeur du bouton radio sélectionné

}
// tabulatorManager.applyToTabulator(tableOP);

       });

$(document).on('keydown', '#upload-icon-ventes', function(e) {
    if (e.key === "Enter") {
        // Ouvre la popup
        $('#files_ventes_Modal').show();
        // Mémorise la cellule courante pour l'upload
        const $cell = $(this).closest('.tabulator-cell')[0];
        if ($cell && tableVentes) {
            // Trouve la cellule Tabulator correspondante
            const rowEl = $cell.closest('.tabulator-row');
            if (rowEl) {
                const row = tableVentes.getRow(rowEl.getAttribute('data-row-index'));
                if (row) {
                    currentPieceCellVentes = row.getCell("piece_justificative");
                }
            }
        }
    } else if (e.key === "ArrowRight") {
        // Focus sur la checkbox de la même ligne
        const $row = $(this).closest('.tabulator-row');
        const $checkbox = $row.find('.select-row[type="checkbox"]');
        if ($checkbox.length) {
            $checkbox.focus();
        }
    }
});


// Gestion des onglets
$('.tab').on('click', function () {
    const tabId = $(this).data('tab');
    $('.tab').removeClass('active');
    $('.tab-content').removeClass('active');
    $(this).addClass('active');
    $('#' + tabId).addClass('active');
});












var tableBanque;
let isSending = false;
let selectedFileId = null;
let currentPieceCellBanque = null;
const fileIds = [];

fetchOperations();
 
  const importFileInputCaisse = document.getElementById('importFileCaisse');
  const champsCaisse = [
    "dateCaisse",
    "modePaiementCaisse",
    "compteCaisse",
    "libelleCaisse",
    "debitCaisse",
    "creditCaisse",
    "nFactureLettreCaisse",
    "tauxRasTvaCaisse",
    "natureOperationCaisse",
    "dateLettrageCaisse",
    "contrePartieCaisse"
  ];

  function resetSelectsCaisse() {
    champsCaisse.forEach(id => {
      const select = document.getElementById(id);
      select.innerHTML = '<option>Importez un fichier pour voir les colonnes</option>';
      select.disabled = true;
    });
  }

  resetSelectsCaisse();

  importFileInputCaisse.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) {
      resetSelectsCaisse();
      return;
    }

    const ext = file.name.split('.').pop().toLowerCase();

    function remplirSelectsCaisse(columns) {
      champsCaisse.forEach(id => {
        const select = document.getElementById(id);
        select.innerHTML = '<option value="">-- Choisir la colonne --</option>';
        columns.forEach((col, i) => {
          const option = document.createElement('option');
          option.value = i;
          option.textContent = col || `Colonne ${i + 1}`;
          select.appendChild(option);
        });
        select.disabled = false;
      });
    }

    if (ext === 'csv') {
      const reader = new FileReader();
      reader.onload = function (e) {
        const text = e.target.result;
        const firstLine = text.split('\n')[0].trim();
        const columns = firstLine.split(',');
        remplirSelectsCaisse(columns);
      };
      reader.readAsText(file);
    } else if (ext === 'xls' || ext === 'xlsx') {
      const reader = new FileReader();
      reader.onload = function (e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });

        const firstSheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheetName];

        const sheetData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
        if (sheetData.length > 0) {
          const columns = sheetData[0];
          remplirSelectsCaisse(columns);
        } else {
          resetSelectsCaisse();
        }
      };
      reader.readAsArrayBuffer(file);
    } else {
      resetSelectsCaisse();
      alert('Type de fichier non supporté. Merci de choisir un .csv, .xls ou .xlsx');
    }
  });

  const importFormCaisse = document.getElementById('importFormCaisse');
  importFormCaisse.addEventListener('submit', async function (event) {
    event.preventDefault();

    const formData = new FormData(importFormCaisse);
   formData.append('typeJournal', $('#journal-Caisse').val());

    // Append journal type or other values here if needed
    // formData.append('typeJournal', $('#journal-Caisse').val());

    try {
      const response = await fetch('/importer-operation-courante-caisse', {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        const errorText = await response.text();
        alert('Erreur lors de l\'importation : ' + errorText);
        return;
      }

      const result = await response.json();
      alert('Importation réussie !');
    $('#importModalCaisse').hide();

    } catch (error) {
      alert('Erreur réseau ou serveur : ' + error.message);
    }
  });
 


  const importFileInput = document.getElementById('importFile');
  const champs = [
    "date",
    "modePaiement",
    "compte",
    "libelle",
    "debit",
    "credit",
    "nFactureLettre",
    "tauxRasTva",
    "natureOperation",
    "dateLettrage",
    "contrePartie"
  ];
  function resetSelects() {
    champs.forEach(id => {
      const select = document.getElementById(id);
      select.innerHTML = '<option>Importez un fichier pour voir les colonnes</option>';
      select.disabled = true;
    });
  }
  resetSelects();
  importFileInput.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) {
      resetSelects();
      return;
    }

    const ext = file.name.split('.').pop().toLowerCase();

    function remplirSelects(columns) {
      champs.forEach(id => {
        const select = document.getElementById(id);
        select.innerHTML = '<option value="">-- Choisir la colonne --</option>';
        columns.forEach((col, i) => {
          const option = document.createElement('option');
          option.value = i;
          option.textContent = col || `Colonne ${i + 1}`;
          select.appendChild(option);
        });
        select.disabled = false;
      });
    }

    if (ext === 'csv') {
      const reader = new FileReader();
      reader.onload = function(e) {
        const text = e.target.result;
        const firstLine = text.split('\n')[0].trim();
        const columns = firstLine.split(',');
        remplirSelects(columns);
      };
      reader.readAsText(file);
    } else if (ext === 'xls' || ext === 'xlsx') {
      const reader = new FileReader();
      reader.onload = function(e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, {type: 'array'});

        const firstSheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheetName];

        const sheetData = XLSX.utils.sheet_to_json(worksheet, {header: 1});
        if (sheetData.length > 0) {
          const columns = sheetData[0];
          remplirSelects(columns);
        } else {
          resetSelects();
        }
      };
      reader.readAsArrayBuffer(file);
    } else {
      resetSelects();
      alert('Type de fichier non supporté. Merci de choisir un .csv, .xls ou .xlsx');
    }
  });
  const importForm = document.getElementById('importForm');
importForm.addEventListener('submit', async function(event) {
  event.preventDefault(); 

  const formData = new FormData(importForm);

   formData.append('typeJournal', $('#journal-Banque').val());

  try {
    const response = await fetch('/importer-operation-courante-banque', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      const errorText = await response.text();
      alert('Erreur lors de l\'importation : ' + errorText);
      return;
    }

    const result = await response.json();  
    alert('Importation réussie !');
         $('#importModalBanque').hide();

  } catch (error) {
    alert('Erreur réseau ou serveur : ' + error.message);
  }
});
document.querySelectorAll('.file-card').forEach(function(cardWrapper) {
  cardWrapper.addEventListener('dblclick', function () {

    // Cibler le div .card à l’intérieur
    const innerCard = this.querySelector('.card');

    // Récupérer les attributs data- depuis la .card
    const selectedFileId = innerCard.dataset.fileid;
    const selectedFilePath = innerCard.dataset.filepath;

    console.log("ID du fichier sélectionné :", selectedFileId);
    console.log("Chemin du fichier sélectionné :", selectedFilePath);

    $('#files_banque_Modal').hide();
    $('#files_ventes_Modal').hide();
    
    
    if (currentPieceCellBanque) {
        const cellElement = currentPieceCellBanque.getElement();
        let viewIcon = cellElement.querySelector('.fas.fa-eye.view-icon');

        if (!viewIcon) {
            viewIcon = document.createElement('i');
            viewIcon.className = 'fas fa-eye view-icon';
            viewIcon.style.cursor = 'pointer';
            cellElement.appendChild(viewIcon);
        }

        viewIcon.setAttribute('tabindex', '0');
        viewIcon.focus();

        const openFileListener = function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                if (selectedFilePath) {
                    window.open(selectedFilePath, '_blank');
                } else {
                    console.error("Chemin du fichier non défini.");
                }
                viewIcon.removeEventListener('keydown', openFileListener);
            }
        };

        viewIcon.addEventListener('keydown', openFileListener);

        viewIcon.style.outline = '2px solid #333';
        viewIcon.style.outlineOffset = '2px';
        setTimeout(() => {
            viewIcon.removeAttribute('tabindex');
            viewIcon.style.outline = '';
            viewIcon.style.outlineOffset = '';
        }, 5000);

        fileIds.push(selectedFileId);
    } else {
        console.warn("Impossible de trouver la cellule Tabulator correspondante.");
    }
  });
});

document.addEventListener('keydown', function (event) {
    const activeElement = document.activeElement;

    if (
        activeElement.classList.contains('view-icon') &&
        event.key === 'ArrowRight'  
    ) {
        event.preventDefault();

        // Trouver la cellule Tabulator parente
        const cellDiv = activeElement.closest('.tabulator-cell');
        if (cellDiv) {
            // Trouver la ligne Tabulator parente
            const rowDiv = cellDiv.closest('.tabulator-row');
            if (rowDiv) {
                // Chercher la checkbox dans la même ligne
                const checkbox = rowDiv.querySelector('input.select-row[type="checkbox"]');
                if (checkbox) {
                    checkbox.focus();
                } else {
                    console.warn("Checkbox non trouvée dans la ligne.");
                }
            }
        }
    }
});
$(document).on('keydown', 'input#selectedFile', function(e) {
    if (e.key === "ArrowRight") {
        e.preventDefault();
        // Cherche l'icône dans la même cellule
        const $cell = $(this).closest('.tabulator-cell');
        const $icon = $cell.find('#upload-icone-banque');
        if ($icon.length) {
            // Rendre l'icône focusable si besoin
            if (!$icon.attr('tabindex')) $icon.attr('tabindex', '0');
            $icon.focus();
        }
    }
});
$(document).on('keydown', '#upload-icone-banque', function(e) {
    if (e.key === "Enter") {
        // Ouvre la popup
        $('#files_banque_Modal').show();
        // Mémorise la cellule courante pour l'upload
        const $cell = $(this).closest('.tabulator-cell')[0];
        if ($cell && tableBanque) {
            // Trouve la cellule Tabulator correspondante
            const rowEl = $cell.closest('.tabulator-row');
            if (rowEl) {
                const row = tableBanque.getRow(rowEl.getAttribute('data-row-index'));
                if (row) {
                    currentPieceCellBanque = row.getCell("piece_justificative");
                }
            }
        }
    } else if (e.key === "ArrowRight") {
        // Focus sur la checkbox de la même ligne
        const $row = $(this).closest('.tabulator-row');
        const $checkbox = $row.find('.select-row[type="checkbox"]');
        if ($checkbox.length) {
            $checkbox.focus();
        }
    }
});

// === tabulator Banque ===
$(document).ready(function() {
    // Récupérer la date de l'exercice depuis l'attribut data-exercice-date
    var exerciceDate = $('#exercice-date').data('exercice-date');
    var exerciceYear = new Date(exerciceDate).getFullYear(); // Extraire l'année de la date
    
    $('input[name="filter-period-Banque"]').on('change', function() {
        var selectedPeriod = $('input[name="filter-period-Banque"]:checked').val();

        if (selectedPeriod === 'mois') {
            // Afficher la liste des mois
            $('#periode-Banque').show();
            // Masquer le champ d'année
            $('#annee-Banque').hide();
        } else if (selectedPeriod === 'exercice') {
            // Masquer la liste des mois
            $('#periode-Banque').hide();
            // Afficher le champ d'année avec l'année extraite
            $('#annee-Banque').show().val(exerciceYear);
        }
        fetchOperations();

    });

    if ($('input[name="filter-period-Banque"]:checked').val() === 'mois') {
        $('#periode-Banque').show();
        $('#annee-Banque').hide();
    } else if ($('input[name="filter-period-Banque"]:checked').val() === 'exercice') {
        $('#periode-Banque').hide();
        $('#annee-Banque').show().val(exerciceYear);
    }
    
$.ajax({
    url: '/journaux-Banque', 
    method: 'GET',
    success: function(response) {
        $('#journal-Banque').empty();

        if (response && response.length > 0) {
            response.forEach(function(journal) {
                $('#journal-Banque').append(
                    $('<option>', {
                        value: journal.code_journal,
                        text: journal.code_journal,
                        'data-intitule': journal.intitule,
                        'data-contre-partie': journal.contre_partie
                    })
                );
            });

            // Mettre à jour #filter-intitule-Banque avec le premier journal sélectionné par défaut
            var firstOption = $('#journal-Banque option:first');
            if(firstOption.length) {
                $('#filter-intitule-Banque').val(firstOption.data('intitule'));
            }
        } else {
            console.log("Aucun journal trouvé.");
        }
    },
    error: function() {
        console.log("Erreur lors de la récupération des journaux.");
    }
});

// Écouter les changements du select pour mettre à jour l'intitulé
$('#journal-Banque').on('change', function() {
    var selectedIntitule = $(this).find('option:selected').data('intitule');
    $('#filter-intitule-Banque').val(selectedIntitule || '');
});


 
  
    $('.tab[data-tab="Banque"]').on('click', function() {
        $('.tab-content').removeClass('active');
        $('#Banque').addClass('active');

        tableBanque = new Tabulator("#tableBanque", {     
            data: [{ 
                    date: "",
                    mode_paiement: "",
                    compte: "",
                    libelle: "",
                    debit: "",
                    credit: "",
                    facture: "",
                    taux_ras_tva: "",
                    nature_operation: "",
                    date_lettrage: "",
                    contre_partie:  $('#journal-Banque option:selected').data('contre-partie'),
                    piece_justificative: ""
             }],
            height: "650px", 
            layout: "fitColumns", 
            columns: [
                { title: "Date paiement", 
                  field: "date", 
                  sorter: "date", 
                  width: 100, 
                  editor: customDateEditor, 
                  headerFilter: "input",
                  cellEdited: function(cell) {
                      var dateValue = cell.getValue(); 
                      var row = cell.getRow(); // Récupérer la ligne courante
                      var dateLettrageCell = row.getCell("date_lettrage"); // Récupérer la cellule du champ "Date lettrage"
                      dateLettrageCell.setValue(dateValue); // Mettre à jour la valeur du champ "Date lettrage"

                      // --- Déplacement du focus vers "mode_pay" ---
                      setTimeout(() => {
                          const modePayCell = row.getCell("mode_pay");
                          modePayCell.edit(); // Déclencher l'édition sur "Mode de paiement"
                      }, 20);
                  }
                },
                { title: "Mode de paiement",
  field: "mode_pay",
  editor: "list",
  headerFilter: "input",
  editorParams: {
    values: ["2.CHÈQUES", "3.PRÉLÈVEMENTS", "4.VIREMENT", "5.EFFET", "6.COMPENSATIONS", "7.AUTRES"],
    clearable: true,
    verticalNavigation: "editor",
  },
  cellEdited: function(cell) {
    // Après modification, déplacer le focus vers le champ "compte"
    setTimeout(function() {
      var compteCell = cell.getRow().getCell("compte");
      if (compteCell) compteCell.edit();
    }, 50);

    // Mise à jour du libellé si un compte est déjà sélectionné
    var row = cell.getRow();
    var data = row.getData();
    var compteCode = data.compte;

    if (compteCode) {
      var compte = planComptable.find(c => c.compte == compteCode);
      var modePaiement = cell.getValue();
      var intituleCompte = compte ? compte.intitule : '';
      let modePaiementSansNumero = modePaiement ? modePaiement.replace(/^\d+\.\s*/, '') : '';

      if (compte) {
        let libelle = '';
        if (compte.compte.startsWith('441')) {
          libelle = `PAIEMENT ${modePaiementSansNumero} ${intituleCompte}`;
        } else if (compte.compte.startsWith('342')) {
          libelle = `REGLEMENT ${modePaiementSansNumero} ${intituleCompte}`;
        } else if (compte.compte.startsWith('6147')) {
          libelle = `${modePaiementSansNumero} FRAIS BANCAIRE`;
        } else if (compte.compte.startsWith('61671')) {
          libelle = `${modePaiementSansNumero} FRAIS TIMBRE`;
        } else {
          libelle = `${modePaiementSansNumero} ${intituleCompte}`;
        }
        row.getCell("libelle").setValue(libelle);
      } else {
        row.getCell("libelle").setValue('');
      }
    }
  }
                },
                { title: "Compte",
    field: "compte",
    width: 100,
    editor: "list",
    editorParams: {
        autocomplete: true,
        listOnEmpty: true,
        values: planComptable.reduce((acc, c) => {
            acc[c.compte] = `${c.compte} - ${c.intitule}`;
            return acc;
        }, {}),
        clearable: true,
        verticalNavigation: "editor",
    },
    headerFilter: "input",
    formatter: function(cell) {
        return cell.getValue() || " ";
    },
    cellEdited: function(cell) {
        var compteCode = cell.getValue();
        var row = cell.getRow();
        var data = row.getData();
        var contrePartie = data.contre_partie;

        // --- Blocage uniquement si compte = contre-partie ---
        if (compteCode && contrePartie && String(compteCode).trim() === String(contrePartie).trim()) {
            alert("❌ Le compte sélectionné est identique à la contre-partie.");
            cell.setValue(""); // Vider la cellule
            disableEditor(cell); // Désactiver cette cellule uniquement
            return; // Ne rien faire d'autre
        }

        // --- Reste du traitement normal ---
        var compte = planComptable.find(c => c.compte == compteCode);
        var modePaiement = row.getCell("mode_pay").getValue();
        var intituleCompte = compte ? compte.intitule : '';
        let modePaiementSansNumero = modePaiement ? modePaiement.replace(/^\d+\.\s*/, '') : '';

        // Générer libellé
        if (compte) {
            let libelle = '';
            if (compte.compte.startsWith('441')) {
                libelle = `PAIEMENT ${modePaiementSansNumero} ${intituleCompte}`;
            } else if (compte.compte.startsWith('342')) {
                libelle = `REGLEMENT ${modePaiementSansNumero} ${intituleCompte}`;
            } else if (compte.compte.startsWith('6147')) {
                libelle = `${modePaiementSansNumero} FRAIS BANCAIRE`;
            } else if (compte.compte.startsWith('61671')) {
                libelle = `${modePaiementSansNumero} FRAIS TIMBRE`;
            } else {
                libelle = `${modePaiementSansNumero} ${intituleCompte}`;
            }
            row.getCell("libelle").setValue(libelle);
            row.getCell("libelle").edit();
        } else {
            row.getCell("libelle").setValue('');
        }

        // Gestion taux_ras_tva et nature_op
        if (!compte) return;

        const tauxCell = row.getCell("taux_ras_tva");
        const natureCell = row.getCell("nature_op");

        const is441 = compte.compte.startsWith('441');
        const is342 = compte.compte.startsWith('342');

        tauxCell.setValue('');
        natureCell.setValue('');

        if (!is441 && !is342) {
            disableEditor(tauxCell);
            disableEditor(natureCell);
        } else if (is342) {
            tauxCell.setValue('0%');
            enableEditor(tauxCell);
            disableEditor(natureCell);

            setTimeout(() => {
                let taux = tauxCell.getValue();
                if (taux !== '0%' && taux !== '0' && taux !== 0) {
                    enableEditor(natureCell);
                    const natureList = getNatureFromSociete().filter(n => n.toLowerCase().startsWith('vente'));
                    natureCell.setValue(getDefaultNatureFromSociete());
                    setCustomEditorOptions("nature_op", natureList);
                } else {
                    natureCell.setValue('');
                    disableEditor(natureCell);
                }
            }, 10);
        } else if (is441) {
            tauxCell.setValue('0%');
            enableEditor(tauxCell);
            disableEditor(natureCell);

            setTimeout(() => {
                let taux = tauxCell.getValue();
                if (taux !== '0%' && taux !== '0' && taux !== 0) {
                    enableEditor(natureCell);
                    const natureList = getNatureFromFournisseur().filter(n => n.toLowerCase().startsWith('achat'));
                    natureCell.setValue(getDefaultNatureFromFournisseur());
                    setCustomEditorOptions("nature_op", natureList);
                } else {
                    natureCell.setValue('');
                    disableEditor(natureCell);
                }
            }, 10);
        }

        // --- Fonctions utilitaires ---
        function disableEditor(cell) {
            if (cell) {
                let el = cell.getElement();
                el.style.pointerEvents = "none";
                el.style.backgroundColor = "#f2f2f2";
            }
        }

        function enableEditor(cell) {
            if (cell) {
                let el = cell.getElement();
                el.style.pointerEvents = "auto";
                el.style.backgroundColor = "";
            }
        }

        function getNatureFromFournisseur() {
            return ["achat matériel", "achat service", "achat divers"];
        }

        function getDefaultNatureFromFournisseur() {
            return "achat matériel";
        }

        function getNatureFromSociete() {
            return ["vente produit", "vente service"];
        }

        function getDefaultNatureFromSociete() {
            return "vente produit";
        }

        function setCustomEditorOptions(field, options) {
            console.log(`Mise à jour des options de "${field}" :`, options);
        }
    }
                },
                {title: "Libellé",
    field: "libelle",
    width: 100,
    editor: "input",
    headerFilter: "input",
    editorParams: {
        elementAttributes: {
            tabindex: "1"
        }
    },
    editor: function (cell, onRendered, success, cancel) {
        const input = document.createElement("input");
        input.type = "text";
        input.style.width = "100%";
        input.value = cell.getValue() || "";

        onRendered(() => {
            input.focus();
            // Place le curseur à la fin du texte
            input.setSelectionRange(input.value.length, input.value.length);
        });

        input.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                success(input.value);

                setTimeout(() => {
                    const row = cell.getRow();
                    const compteCode = row.getCell("compte").getValue();
                    const compte = planComptable.find(c => c.compte == compteCode);
                    if (!compte) return;

                    if (/^[642]/.test(compte.compte)) {
                        row.getCell("debit").edit();
                    } else {
                        row.getCell("credit").edit();
                    }
                }, 10);
            } else if (e.key === "Escape") {
                cancel();
            }
        });

        return input;
    }
                },
                {   title: "Débit",
  field: "debit",
  sorter: "number",
  width: 100,
  editor: customNumberEditor,
                      bottomCalc: "sum",

  headerFilter: "input",
  cellEdited: function(cell) {
    const row = cell.getRow();
    const debitValue = cell.getValue();
    const creditCell = row.getCell("credit");

    if (debitValue !== null && debitValue !== '' && debitValue !== 0) {
      creditCell.setValue('');
    }

    const factLettrerCell = row.getCell("fact_lettrer");

    // Laisse le focus sur fact_lettrer quand on appuie sur Enter dans débit
    cell.getElement().addEventListener("keydown", function(event) {
      if (event.key === "Enter") {
        setTimeout(function() {
          factLettrerCell.edit();
        }, 100);
      }
    });
  }
                },
                {   title: "Crédit",
                field: "credit", 
                sorter: "number", 
                width: 100, 
                editor: customNumberEditor, 
                                    bottomCalc: "sum",
                headerFilter: "input",
                cellEdited: function(cell) {
                    const row = cell.getRow();
                    const creditValue = cell.getValue();
                    const debitCell = row.getCell("debit");

                    if (creditValue !== null && creditValue !== '' && creditValue !== 0) {
                    debitCell.setValue('');
                    }
                }
                },
                // { title: "N° facture lettrée",
                //       field: "fact_lettrer",
                //       width: 200,
                //       headerFilter: "input",

                //       formatter: function(cell) {
                //           const value = cell.getValue();
                //           if (Array.isArray(value)) {
                //               return value.map(item => {
                //                   const [id, numero, montant, date] = item.split('|');
                //                   return `${numero} / ${montant} / ${date}`;
                //               }).join(", ");
                //           }
                //           return value || "";
                //       },

                    
                //      editor: function(cell, onRendered, success, cancel) {
                //       const select = document.createElement("select");
                //       select.style.width = "350px";
                //       select.multiple = true;

                //       const row = cell.getRow();
                //       const compte = row.getCell("compte").getValue();
                //       const debit = row.getCell("debit").getValue();
                //       const credit = row.getCell("credit").getValue();

                //       if (!debit && !credit) {
                //           alert("Veuillez remplir une valeur de débit ou crédit.");
                //           cancel();
                //           return select;
                //       }

                //       const existingValues = cell.getValue() || [];
                //       let committed = false;

                //       function commit(vals) {
                //           if (committed) return;
                //           committed = true;
                //           try { 
                //               success(vals); 
                //           } catch (e) {}

                //           // Focus sur la cellule "date_lettrage"
                //           const nextCell = row.getCell("date_lettrage");
                //           if(nextCell) nextCell.edit();
                //       }

                //       // Récupération des options via AJAX
                //       $.ajax({
                //           url: `/get-nfacturelettree?debit=${encodeURIComponent(debit)}&credit=${encodeURIComponent(credit)}&compte=${encodeURIComponent(compte)}`,
                //           method: 'GET',
                //           success: function(response) {
                //               if(response.length === 0){
                //                   // Si aucune option, on commit immédiatement pour aller à date_lettrage
                //                   commit([]);
                //               }

                //               response.forEach(item => {
                //                   const montant = item.debit != null ? item.debit : item.credit;
                //                   const valeur = `${item.id}|${item.numero_facture}|${montant}|${item.date}`;
                //                   const option = new Option(
                //                       `${item.numero_facture} / ${montant} / ${item.date}`,
                //                       valeur,
                //                       existingValues.includes(valeur),
                //                       existingValues.includes(valeur)
                //                   );
                //                   select.appendChild(option);
                //               });

                //               $(select).select2({
                //                   placeholder: "-- Sélectionnez une ou plusieurs factures --",
                //                   closeOnSelect: false,
                //                   width: '350px',
                //               });

                //               $(select).select2('open');

                //               setTimeout(() => {
                //                   const search = document.querySelector('.select2-container--open .select2-search__field');
                //                   if (search) {
                //                       search.addEventListener('keydown', function (e) {
                //                           if (e.key === 'Enter') {
                //                               e.preventDefault();
                //                               const vals = $(select).val() ?? [];
                //                               try { $(select).select2('close'); } catch (err) {}
                //                               commit(vals);
                //                           }
                //                       });
                //                   }
                //               }, 50);
                //           },
                //           error: function(error) {
                //               console.error("Erreur AJAX :", error);
                //               // Si erreur et pas d'options, commit pour aller à date_lettrage
                //               commit([]);
                //           }
                //       });

                //       // Commit quand on change la sélection
                //       $(select).on('change', function() {
                //           const vals = $(select).val() ?? [];
                //           try { cell.setValue(vals); } catch(e){}
                //           commit(vals);
                //       });

                //       // ESC => annuler
                //       select.addEventListener('keydown', function(e){
                //           if (e.key === 'Escape') {
                //               try { cancel(); } catch(ex) {}
                //           }
                //       });

                //       onRendered(() => {
                //           setTimeout(() => {
                //               try {
                //                   const search = document.querySelector('.select2-container--open .select2-search__field');
                //                   if (search) search.focus();
                //               } catch (err) {}
                //           }, 80);
                //       });

                //       return select;
                //   }


                // },
                                { title: "N° facture lettrée",
    field: "fact_lettrer",
    width: 200,
    headerFilter: "input",

    // =========================
    // FORMATTER : afficher seulement les numéros de facture
    // =========================
    formatter: function(cell) {
        const value = cell.getValue();
        if (!value) return "";

        // Normaliser en tableau (séparateur : & ou tableau)
        const values = typeof value === "string"
            ? value.split(/\s*&\s*/).filter(Boolean)
            : Array.isArray(value) ? value : [];

        // Retourner uniquement les numéros de facture
        return values
            .map(v => {
                const parts = v.split("|");
                return parts[1] || ""; // numero_facture
            })
            .filter(Boolean)
            .join(" | ");
    },

    // =========================
    // EDITOR : modal avec checkboxes
    // =========================
    editor: function(cell, onRendered, success, cancel) {
        const row = cell.getRow();
        const compte = row.getCell("compte").getValue();
        const debit = row.getCell("debit").getValue();
        const credit = row.getCell("credit").getValue();

        if (!debit && !credit) {
            alert("Veuillez remplir une valeur de débit ou crédit.");
            cancel();
            return document.createElement("div");
        }

        // Création overlay/modal
        const overlay = document.createElement("div");
        overlay.style = "position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);display:flex;justify-content:center;align-items:center;z-index:10002;";

        const modal = document.createElement("div");
        modal.style = "background:#fff;padding:15px;border-radius:6px;min-width:360px;box-shadow:0 6px 20px rgba(0,0,0,0.2);";
        modal.innerHTML = "<h4>Sélection des factures lettrées</h4>";

        const checkboxContainer = document.createElement("div");
        checkboxContainer.style = "max-height:260px;overflow-y:auto;border:1px solid #ddd;padding:6px;margin-top:6px;";
        modal.appendChild(checkboxContainer);

        const btnRow = document.createElement("div");
        btnRow.style = "margin-top:10px;display:flex;justify-content:flex-end;gap:8px;";

        const cancelBtn = document.createElement("button"); 
        cancelBtn.textContent = "Annuler"; 
        cancelBtn.className = "btn btn-secondary";

        const saveBtn = document.createElement("button"); 
        saveBtn.textContent = "Valider"; 
        saveBtn.className = "btn btn-primary";

        btnRow.append(cancelBtn, saveBtn); 
        modal.appendChild(btnRow);
        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Valeurs existantes
        const existingRaw = cell.getValue() ?? [];
        let existingValues = typeof existingRaw === "string" 
            ? existingRaw.split(/\s*&\s*/).filter(Boolean)
            : existingRaw;

        // Récupération via AJAX
        $.ajax({
            url: `/get-nfacturelettree?debit=${encodeURIComponent(debit)}&credit=${encodeURIComponent(credit)}&compte=${encodeURIComponent(compte)}`,
            method: 'GET',
            success: function(response) {
                if (!response) response = [];
                const dispoMap = {};
                response.forEach(item => {
                    const montantVal = item.debit ?? item.credit;
                    const valeur = `${item.id}|${item.numero_facture}|${montantVal}|${item.date}`;
                    dispoMap[valeur] = `${item.numero_facture} / ${montantVal} / ${item.date}`;
                });

                // Pré-cocher les valeurs existantes
                existingValues.forEach(v => {
                    const parts = v.split('|');
                    const label = `${parts[1] || ''} / ${parts[2] || ''} / ${parts[3] || ''}`;
                    const cb = document.createElement('div');
                    cb.innerHTML = `<label><input type="checkbox" value="${v}" checked> ${label}</label>`;
                    checkboxContainer.appendChild(cb);
                    delete dispoMap[v];
                });

                // Ajouter les autres options
                Object.keys(dispoMap).forEach(val => {
                    const cb = document.createElement('div');
                    cb.innerHTML = `<label><input type="checkbox" value="${val}"> ${dispoMap[val]}</label>`;
                    checkboxContainer.appendChild(cb);
                });

                // Boutons
                saveBtn.onclick = function() {
                    const checked = [];
                    checkboxContainer.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => checked.push(cb.value));
                    const joined = checked.join(' & ');
                    cell.setValue(joined);
                    success(joined);

                    // Focus automatique sur date_lettrage si nécessaire
                    const dateCell = row.getCell("date_lettrage");
                    if (dateCell) dateCell.edit();
                    
                    document.body.removeChild(overlay);
                };

                cancelBtn.onclick = function() {
                    document.body.removeChild(overlay);
                    cancel();
                };
            },
            error: function(err) {
                console.error("Erreur AJAX :", err);
                document.body.removeChild(overlay);
                cancel();
            }
        });

        return document.createElement("div"); // nécessaire pour Tabulator
    }
}
,
                { title: "Taux RAS TVA",
    field: "taux_ras_tva",
    width: 100,
    headerFilter: "input",
    editor: customListEditor1,
    cellEdited: function(cell) {
        const row = cell.getRow();
        const taux = (cell.getValue() || '').toString().trim();
        const natureCell = row.getCell("nature_op");

        // Fonctions pour activer/désactiver nature_op
        function disableEditor(cell) {
            if (cell) {
                let el = cell.getElement();
                if(el) {
                    el.style.pointerEvents = "none";
                    el.style.backgroundColor = "#f2f2f2";
                }
            }
        }
        function enableEditor(cell) {
            if (cell) {
                let el = cell.getElement();
                if(el) {
                    el.style.pointerEvents = "auto";
                    el.style.backgroundColor = "";
                }
            }
        }

        if (taux === '0%' || taux === '0' || taux === '0') {
            // Si taux 0%, on désactive nature_op
            disableEditor(natureCell);
            natureCell.setValue('');
        } else {
            // Sinon on active nature_op
            enableEditor(natureCell);
        }
    }
                },
                { title: "Nature de l'opération", 
                    field: "nature_op", 
                    width: 100, 
                    editor: customListEditor2, 
                    headerFilter: "input"
                },
                { title: "Date lettrage",
                    field: "date_lettrage",
                    sorter: "date",
                    width: 120,
                    editor: function(cell, onRendered, success, cancel){
                        var input = document.createElement("input");
                        input.type = "text";
                        input.value = cell.getValue() || "";

                        onRendered(function(){
                            input.focus();
                            input.style.width = "100%";
                        });

                        function saveValue() {
                            success(input.value);
                        }

                        input.addEventListener("blur", saveValue);

                        input.addEventListener("keydown", function(e){
                            if(e.key === "Enter"){
                                saveValue();

                                setTimeout(() => {
                                    var pieceCell = cell.getRow().getCell("piece_justificative");
                                    if(pieceCell){
                                        var inputElement = pieceCell.getElement().querySelector("input");
                                        if(inputElement){
                                            inputElement.focus();
                                            // Déplacer le curseur à la fin du texte
                                            var valLength = inputElement.value.length;
                                            inputElement.setSelectionRange(valLength, valLength);
                                        }
                                    }
                                }, 50);

                            } else if(e.key === "Escape"){
                                cancel();
                            }
                        });

                        return input;
                    },
                    headerFilter: "input"
                },
                { title: "Contre-Partie", 
                field: "contre_partie", 
                width: 100, 
                editor: "textarea",
                editable: false, 
                headerFilter: "input",
                },  
                { title: "Pièce justificative",
  field: "piece_justificative",
  width: 200,
  headerFilter: "input",
  formatter: function(cell) {
    var rowData = cell.getRow().getData(); // Données de la ligne complète
    var justificatif = cell.getValue() || ''; // Le champ "piece_justificative"
    var filePath = rowData.file?.path || ''; // Le chemin réel du fichier (file.path)

    // Champ texte avec gestion de la touche Entrée, sans id pour éviter doublons
    var input = "<input type='text' class='selected-file-input' value='" + justificatif + 
      "' onkeydown='if(event.key === \"Enter\") { " +
      "var cellElement = this.closest(\".tabulator-cell\");" +
      "var uploadIcon = cellElement.querySelector(\".upload-icon\");" +
      "if(uploadIcon) uploadIcon.focus();" +
      "}'>";

    // Icône œil (vue fichier)
    var iconView = filePath
      ? "<i class='fas fa-eye view-icon' title='Voir le fichier' tabindex='0' onclick='viewFile(\"" + filePath + "\")'></i>"
      : '';

    // Icône upload (trombone) sans id pour éviter doublons
    var iconUpload = "<i class='fas fa-paperclip upload-icon' id='upload-icone-banque' data-action='open-modal' title='Choisir un fichier' tabindex='0'></i>";

    // Icône "eye" si justificatif vide
    var iconEye = justificatif === ''
      ? "<i class='fas fa-eye view-icon' title='Voir le fichier' tabindex='0' onclick='viewFile(null)'></i>"
      : '';

    return input + iconUpload + iconEye + iconView;
  },

  // Optionnel : clic sur la cellule déclenche aussi l'ouverture du fichier
  cellClick: function(e, cell) {
    var filePath = cell.getRow().getData().file?.path;
    currentPieceCellBanque = cell;
    if (filePath) viewFile(filePath);
  },
                },
                { title: "<input type='checkbox' id='selectAll'>", 
        field: "selected", // Utilisez le champ de données
        width: 60, 
        formatter: function(cell) {
            var checkbox = "<input type='checkbox' class='select-row' " + (cell.getValue() ? "checked" : "") + ">";
            var row = cell.getRow();
            var data = row.getData();
            // Vérifier si la ligne est la ligne de saisie (tous les champs principaux sont vides)
            var isSaisie = !data.date && !data.mode_paiement && !data.compte && !data.libelle && !data.debit && !data.credit;
            if (isSaisie) {
                return checkbox + " <span class='clear-row-btn' style='color:red;cursor:pointer;font-size:18px;' title='Vider la ligne'>&times;</span>";
            } else {
                return checkbox;
            }
        },
        headerSort: false,
        headerFilter: false,
        align: "center",
        cellClick: function(e, cell) {
            // Si clic sur la croix, vider la ligne
            if (e.target.classList.contains('clear-row-btn')) {
                var row = cell.getRow();
                var emptyData = {};
                Object.keys(row.getData()).forEach(function(key) {
                    emptyData[key] = '';
                });
                row.update(emptyData);
                // Décocher la case si présente
                cell.setValue(false);
                return;
            }
            // Inverser l'état de la case à cocher
            const isChecked = !cell.getValue();
            cell.getRow().update({ selected: isChecked }); // Mettre à jour l'état dans les données
            cell.getElement().querySelector("input").checked = isChecked; // Mettre à jour l'élément de la case à cocher
        }
        
                }   
            ],

            editable: true,
            footerElement: "<table style='width: auto; margin-top: 6px; border: 1px solid #000; border-collapse: collapse; float: left;'>" +
                // "<tr>" +
                //     "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde actuel :</td>" +
                //     "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-actuel'></span></td>" +
                //     "<td colspan='2' style='border: 1px solid #000;'></td>" + 
                // "</tr>" +
                "<tr>" +
                    "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde initial DB :</td>" +
                    "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-initial-db'></span></td>" +
                    "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde initial CR :</td>" +
                    "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-initial-cr'></span></td>" +
                "</tr>" +
                "<tr>" +
                    "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Cumul débit :</td>" +
                    "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='cumul-debit'></span></td>" +
                    "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Cumul crédit :</td>" +
                    "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='cumul-credit'></span></td>" +
                "</tr>" +
                "<tr>" +
                    "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde débiteur :</td>" +
                    "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-debiteur'></span></td>" +
                    "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde créditeur :</td>" +
                    "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-crediteur'></span></td>" +
                "</tr>" +
                "</table>" +
                "<div style='display: flex; align-items: center; gap: 10px;'>"+
                // "<label for='JoindreReleveBancaire' class='btn-fichier'>Joindre un relevé bancaire"+
                // "</label>"+
                // "<input type='file' id='JoindreReleveBancaire' style='display: none;' />"+
                  "<span onclick='JoindreReleveBancaire()' style='font-size: 12px;'>Joindre un relevé bancaire <i class='fas fa-paperclip'></i></span>"+
                "<span style='cursor: pointer;' onclick='viewReleveBancaire(9, 2025)' title='afficher le relever bancaire'>👁️</span>"+
                "</div>"
                ,

            rowAdded: function(row) {
                // Ajoutez ici une logique pour remplir cette ligne vide si nécessaire
                // Exemple : lorsque l'utilisateur termine une saisie, ajoutez la ligne à la base de données ou au tableau.
            }
        });
// Stocker l'ancienne valeur avant édition
tableBanque.on("cellEditing", function(cell) {
    const row = cell.getRow();
    const field = cell.getField();
    row._oldValues = row._oldValues || {};
    row._oldValues[field] = cell.getValue(); // sauvegarde l'ancienne valeur
});

// Flag pour éviter la boucle
let restoringValue = false;

tableBanque.on("cellEdited", function(cell) {
    if (restoringValue) return; // Ignore si on est en train de restaurer

    const row = cell.getRow();
    const field = cell.getField();
    const rowData = row.getData();
    const oldValue = row._oldValues && row._oldValues[field] !== undefined 
                     ? row._oldValues[field] 
                     : "";

    // Ne pas tenter une mise à jour si la ligne n'a pas d'ID (nouvelle saisie -> gestion ailleurs)
    if (!rowData || !rowData.id) {
        return;
    }

    // Normaliser fact_lettrer si nécessaire (accepte tableau ou chaîne)
    function normalizeFactLettrer(val) {
        if (!val) return val;
        if (Array.isArray(val)) return val.map(item => String(item).trim()).join(' & ');
        if (typeof val === 'string') return val.split(/\s*&\s*/).map(s => s.trim()).filter(Boolean).join(' & ');
        return val;
    }

    if (field === 'fact_lettrer') {
        rowData.fact_lettrer = normalizeFactLettrer(rowData.fact_lettrer);
    }

    function sendAjaxUpdate() {
        $.ajax({
            url: '/update-banque-operation',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                data: rowData,
                oldPieceJustificative: oldValue
            },
            success: function(response) {
                console.log("✅ Données mises à jour avec succès :", response);
            },
            error: function(xhr) {
                console.error("❌ Erreur lors de la mise à jour :", xhr.responseText);
                // Restaurer l'ancienne valeur en cas d'erreur
                restoringValue = true;
                cell.setValue(oldValue, true);
                restoringValue = false;
                alert("Erreur lors de la mise à jour, valeur restaurée !");
            }
        });
    }

    // Si l'ancienne valeur est vide, on envoie sans confirmation (cas: ajout d'une valeur sur une ligne existante)
    if (oldValue === "" || oldValue === null) {
        sendAjaxUpdate();
        return;
    }

    const confirmation = confirm("Voulez-vous vraiment modifier cette donnée ?");

    if (confirmation) {
        sendAjaxUpdate();
    } else {
        // Annulation immédiate
        restoringValue = true;
        cell.setValue(oldValue, true);
        restoringValue = false;
    }
});



    });

});
    

  // === tabulator Caisse ===
$(document).ready(function () {
// Récupération de l'année de l'exercice
var exerciceDateCaisse = $('#exercice-date').data('exercice-date');
var exerciceYearCaisse = new Date(exerciceDateCaisse).getFullYear(); // Extraire l'année

    // Gérer le changement de la période (radio boutons)
    $('input[name="filter-period-Caisse"]').on('change', function() {
        var selectedPeriod = $('input[name="filter-period-Caisse"]:checked').val();

        if (selectedPeriod === 'mois') {
            $('#periode-Caisse').show();
            $('#annee-Caisse').hide();
        } else if (selectedPeriod === 'exercice') {
            $('#periode-Caisse').hide();
            $('#annee-Caisse').show().val(exerciceYearCaisse); // ← correction ici
        }

        // Met à jour les opérations quand on change de période
        fetchOperationsCaisse();
    });

    // Initialisation à l'ouverture de la page
    if ($('input[name="filter-period-Caisse"]:checked').val() === 'mois') {
        $('#periode-Caisse').show();
        $('#annee-Caisse').hide();
    } else if ($('input[name="filter-period-Caisse"]:checked').val() === 'exercice') {
        $('#periode-Caisse').hide();
        $('#annee-Caisse').show().val(exerciceYearCaisse); // ← correction ici aussi
    }
 
       $.ajax({
            url: '/journaux-Caisse', // Assurez-vous que l'URL correspond à la route Laravel
            method: 'GET',
            success: function(response) {
                // Vider les options existantes avant d'ajouter de nouvelles options
                $('#journal-Caisse').empty();

                // Vérifier s'il y a des journaux
                if (response && response.length > 0) {
                    // Ajouter les options dans le select
                    response.forEach(function(journal) {
                        $('#journal-Caisse').append(
                            $('<option>', {
                                value: journal.code_journal,
                                text: journal.code_journal, // Utiliser l'intitulé pour l'affichage
                                'data-intitule': journal.intitule,
                                    'data-contre-partie': journal.contre_partie

                            })
                        );
                    });
               var firstOption = $('#journal-Caisse option:first');
            if(firstOption.length) {
                $('#filter-intitule-Caisse').val(firstOption.data('intitule'));
            }
        } else {
            console.log("Aucun journal trouvé.");
        }
            },
            error: function() {
                console.log("Erreur lors de la récupération des journaux.");
            }
        });

    // Changer l'intitulé lorsque l'utilisateur sélectionne un journal
    $('#journal-Caisse').on('change', function() {
        var selectedCode = $(this).val(); // Récupérer la valeur du code sélectionné
        var selectedOption = $(this).find('option:selected');
        
        if (selectedCode) {
            // Afficher l'intitulé correspondant dans l'input
            var intitule = selectedOption.data('intitule'); // Récupérer l'intitulé depuis l'attribut data
            $('#filter-intitule-Caisse').val(intitule);
        } else {
            // Si aucune option n'est sélectionnée, vider l'input
            $('#filter-intitule-Caisse').val('');
        }
    });

    $('.tab[data-tab="Caisse"]').on('click', function () {
        $('.tab-content').removeClass('active');
        $('#Caisse').addClass('active');

        if (!window.tableCaissePrincipale) {
            window.tableCaissePrincipale = new Tabulator("#table-Caisse", {
                height: "650px",
                layout: "fitColumns",
                  data: [{ 
                date: "",
                mode_paiement: "",
                compte: "",
                libelle: "",
                debit: "",
                credit: "",
                facture: "",
                taux_ras_tva: "",
                nature_operation: "",
                date_lettrage: "",
                contre_partie:  $('#journal-Caisse option:selected').data('contre-partie'),
                piece_justificative: ""
            }],
                columns: [
                    { title: "Date paiement",
                      field: "date",
                      sorter: "date",
                      width: 100,
                      editor: customDateEditorCaisse,
                      headerFilter: "input",
                      cellEdited: function(cell) {
                          var dateValue = cell.getValue(); 
                          var row = cell.getRow(); 
                          var dateLettrageCell = row.getCell("date_lettrage"); 
                          dateLettrageCell.setValue(dateValue); 

                          // --- Déplacement du focus vers "compte" après validation (Enter) ---
                          setTimeout(() => {
                              const compteCell = row.getCell("compte");
                              if (compteCell) {
                                  compteCell.edit(); // Ouvre directement l'éditeur de la cellule "compte"
                              }
                          }, 50); // petit délai pour que l'édition précédente se termine
                      }
                    },
                    { title: "Mode de paiement",
                    field: "mode_paiement",
                    formatter: function () {
                        return "1.espèce";
                    },
                    headerFilter: "input",
                    visible: false 
                    },  
                    { title: "Compte",
    field: "compte",
    width: 100,
    editor: "list",
    editorParams: {
        autocomplete: true,
        listOnEmpty: true,
        values: planComptable.reduce((acc, c) => {
            acc[c.compte] = `${c.compte} - ${c.intitule}`;
            return acc;
        }, {}),
        clearable: true,
        verticalNavigation: "editor",
    },
    headerFilter: "input",
    formatter: function(cell) {
        return cell.getValue() || " ";
    },
    cellEdited: function(cell) {
        var compteCode = cell.getValue();
        var row = cell.getRow();
        var data = row.getData();
        var contrePartie = data.contre_partie;

        // ✅ Vérifier si le compte est identique à la contre-partie
        if (compteCode && contrePartie && String(compteCode).trim() === String(contrePartie).trim()) {
            alert("❌ Le compte sélectionné est identique à la contre-partie.");
            cell.setValue(""); // Vider la cellule
            setTimeout(() => {
                cell.edit(); // Remettre le focus sur la cellule
            }, 100);
            return;
        }

        var compte = planComptable.find(c => c.compte == compteCode);
var modePaiement = row.getCell("mode_paiement").getValue();
        var intituleCompte = compte ? compte.intitule : '';
        let modePaiementSansNumero = modePaiement ? modePaiement.replace(/^\d+\.\s*/, '') : '';

        // --- Générer le libellé ---
        if (compte) {
            let libelle = '';
            if (compte.compte.startsWith('441')) {
                libelle = `PAIEMENT ${modePaiementSansNumero} ${intituleCompte}`;
            } else if (compte.compte.startsWith('342')) {
                libelle = `REGLEMENT ${modePaiementSansNumero} ${intituleCompte}`;
            } else if (compte.compte.startsWith('6147')) {
                libelle = `${modePaiementSansNumero} FRAIS BANCAIRE`;
            } else if (compte.compte.startsWith('61671')) {
                libelle = `${modePaiementSansNumero} FRAIS TIMBRE`;
            } else {
                libelle = `${modePaiementSansNumero} ${intituleCompte}`;
            }
            row.getCell("libelle").setValue(libelle);

            // ✅ Focus automatique sur "libelle"
            row.getCell("libelle").edit();
        } else {
            row.getCell("libelle").setValue('');
        }

        // --- Appliquer les règles de taux_ras_tva & nature_op ---
        if (!compte) return;

        const tauxCell = row.getCell("taux_ras_tva");
        const natureCell = row.getCell("nature_op");

        const is441 = compte.compte.startsWith('441');
        const is342 = compte.compte.startsWith('342');

        tauxCell.setValue('');
        natureCell.setValue('');

        if (!is441 && !is342) {
            disableEditor(tauxCell);
            disableEditor(natureCell);
        }
        else if (is342) {
            tauxCell.setValue('0%');
            enableEditor(tauxCell);
            disableEditor(natureCell);

            setTimeout(() => {
                let taux = tauxCell.getValue();
                if (taux !== '0%' && taux !== '0' && taux !== 0) {
                    enableEditor(natureCell);
                    const natureList = getNatureFromSociete().filter(n => n.toLowerCase().startsWith('vente'));
                    natureCell.setValue(getDefaultNatureFromSociete());
                    setCustomEditorOptions("nature_op", natureList);
                } else {
                    natureCell.setValue('');
                    disableEditor(natureCell);
                }
            }, 10);
        }
        else if (is441) {
            tauxCell.setValue('0%');
            enableEditor(tauxCell);
            disableEditor(natureCell);

            setTimeout(() => {
                let taux = tauxCell.getValue();
                if (taux !== '0%' && taux !== '0' && taux !== 0) {
                    enableEditor(natureCell);
                    const natureList = getNatureFromFournisseur().filter(n => n.toLowerCase().startsWith('achat'));
                    natureCell.setValue(getDefaultNatureFromFournisseur());
                    setCustomEditorOptions("nature_op", natureList);
                } else {
                    natureCell.setValue('');
                    disableEditor(natureCell);
                }
            }, 10);
        }

        // --- Fonctions utilitaires ---
        function disableEditor(cell) {
            if (cell) {
                let el = cell.getElement();
                el.style.pointerEvents = "none";
                el.style.backgroundColor = "#f2f2f2";
            }
        }

        function enableEditor(cell) {
            if (cell) {
                let el = cell.getElement();
                el.style.pointerEvents = "auto";
                el.style.backgroundColor = "";
            }
        }

        function getNatureFromFournisseur() {
            return ["achat matériel", "achat service", "achat divers"];
        }

        function getDefaultNatureFromFournisseur() {
            return "achat matériel";
        }

        function getNatureFromSociete() {
            return ["vente produit", "vente service"];
        }

        function getDefaultNatureFromSociete() {
            return "vente produit";
        }

        function setCustomEditorOptions(field, options) {
            console.log(`Mise à jour des options de "${field}" :`, options);
        }
    }
                    },
                    {title: "Libellé",
    field: "libelle",
    width: 100,
    editor: "input",
    headerFilter: "input",
    editorParams: {
        elementAttributes: {
            tabindex: "1"
        }
    },
    editor: function (cell, onRendered, success, cancel) {
        const input = document.createElement("input");
        input.type = "text";
        input.style.width = "100%";
        input.value = cell.getValue() || "";

        onRendered(() => {
            input.focus();
            // Place le curseur à la fin du texte
            input.setSelectionRange(input.value.length, input.value.length);
        });

        input.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                success(input.value);

                setTimeout(() => {
                    const row = cell.getRow();
                    const compteCode = row.getCell("compte").getValue();
                    const compte = planComptable.find(c => c.compte == compteCode);
                    if (!compte) return;

                    if (/^[642]/.test(compte.compte)) {
                        row.getCell("debit").edit();
                    } else {
                        row.getCell("credit").edit();
                    }
                }, 10);
            } else if (e.key === "Escape") {
                cancel();
            }
        });

        return input;
    }
                    },
                    {   title: "Débit",
  field: "debit",
  sorter: "number",
  width: 100,
  editor: customNumberEditor,
                      bottomCalc: "sum",
  headerFilter: "input",
  cellEdited: function(cell) {
    const row = cell.getRow();
    const debitValue = cell.getValue();
    const creditCell = row.getCell("credit");

    if (debitValue !== null && debitValue !== '' && debitValue !== 0) {
      creditCell.setValue('');
    }

    const factLettrerCell = row.getCell("fact_lettrer");

    // Laisse le focus sur fact_lettrer quand on appuie sur Enter dans débit
    cell.getElement().addEventListener("keydown", function(event) {
      if (event.key === "Enter") {
        setTimeout(function() {
          factLettrerCell.edit();
        }, 100);
      }
    });
  }
                    },
                    {   title: "Crédit",
                    field: "credit", 
                    sorter: "number", 
                    width: 100, 
                    editor: customNumberEditor, 
                                        bottomCalc: "sum",
                    headerFilter: "input",
                    cellEdited: function(cell) {
                        const row = cell.getRow();
                        const creditValue = cell.getValue();
                        const debitCell = row.getCell("debit");

                        if (creditValue !== null && creditValue !== '' && creditValue !== 0) {
                        debitCell.setValue('');
                        }
                    }
                    },    
    //                 { title: "N° facture lettrée",
    // field: "fact_lettrer",
    // width: 200,
    // headerFilter: "input",

    // formatter: function(cell) {
    //     const value = cell.getValue();
    //     if (Array.isArray(value)) {
    //         return value.map(item => {
    //             const [id, numero, montant, date] = item.split('|');
    //             return `${numero} / ${montant} / ${date}`;
    //         }).join(", ");
    //     }
    //     return value || "";
    // },

    // editor: function(cell, onRendered, success, cancel) {
    //     const row = cell.getRow();
    //     const rowData = row.getData();
    //     // Sauvegarder l'ancienne valeur pour permettre une comparaison fiable lors du cellEdited
    //     row._oldFactLettrer = (cell.getValue() !== undefined && cell.getValue() !== null) ? String(cell.getValue()) : '';
    //     const compte = row.getCell("compte").getValue();
    //     const debit = row.getCell("debit").getValue();
    //     const credit = row.getCell("credit").getValue();

    //     // Si pas de montant, on annule
    //     if (!debit && !credit) {
    //         alert("Veuillez remplir une valeur de débit ou crédit.");
    //         cancel();
    //         return document.createElement("div");
    //     }

    //     // Fonction pour déplacer le focus après validation
    //     function moveFocus(selectedValues) {
    //         const tauxCell = row.getCell("taux_ras_tva");
    //         const tauxElement = tauxCell.getElement();
    //         const isTauxDisabled = tauxElement.style.pointerEvents === "none";

    //         if (!selectedValues.length || isTauxDisabled) {
    //             const dateCell = row.getCell("date_lettrage");
    //             dateCell.edit();
    //             setTimeout(() => {
    //                 const input = dateCell.getElement().querySelector("input");
    //                 if (input) input.focus();
    //             }, 10);
    //         } else {
    //             tauxCell.edit();
    //             setTimeout(() => {
    //                 const input = tauxElement.querySelector("input, select");
    //                 if (input) input.focus();
    //             }, 10);
    //         }
    //     }

    //     // Création d'une modal pour la sélection des factures
    //     const overlay = document.createElement("div");
    //     overlay.style.position = "fixed";
    //     overlay.style.top = 0;
    //     overlay.style.left = 0;
    //     overlay.style.width = "100%";
    //     overlay.style.height = "100%";
    //     overlay.style.backgroundColor = "rgba(0,0,0,0.4)";
    //     overlay.style.display = "flex";
    //     overlay.style.justifyContent = "center";
    //     overlay.style.alignItems = "center";
    //     overlay.style.zIndex = 10002;

    //     const modal = document.createElement("div");
    //     modal.style.background = "#fff";
    //     modal.style.padding = "15px";
    //     modal.style.borderRadius = "6px";
    //     modal.style.minWidth = "360px";
    //     modal.style.boxShadow = "0 6px 20px rgba(0,0,0,0.2)";

    //     const title = document.createElement("h4");
    //     title.textContent = "Sélection des factures lettrées";
    //     title.style.marginTop = 0;
    //     modal.appendChild(title);

    //     // Afficher ancienne valeur (si présente) — normaliser la valeur en tableau
    //     const existingDisplay = document.createElement('div');
    //     existingDisplay.style.marginBottom = '8px';
    //     const existingRaw = cell.getValue() ?? [];
    //     let existingValues = [];
    //     if (typeof existingRaw === 'string') {
    //         // format ancien possible: "val1 & val2" ou simple "val"
    //         existingValues = existingRaw.indexOf('&') !== -1 ? existingRaw.split(/\s*&\s*/).map(s => s.trim()).filter(Boolean) : (existingRaw.trim() ? [existingRaw.trim()] : []);
    //     } else if (Array.isArray(existingRaw)) {
    //         existingValues = existingRaw;
    //     } else {
    //         existingValues = [];
    //     }

    //     if (existingValues.length) {
    //         existingDisplay.innerHTML = '<strong>Valeur actuelle :</strong> ' + existingValues.map(v => {
    //             const parts = (''+v).split('|');
    //             return (parts[1] || parts[0] || '') + ' / ' + (parts[2] || '') + ' / ' + (parts[3] || '');
    //         }).join(', ');
    //         modal.appendChild(existingDisplay);
    //     }

    //     // Avertissement pour ligne de saisie (si applicable)
    //     if (rowData.id === undefined || rowData.id === null) {
    //         const warn = document.createElement('p');
    //         warn.style.color = '#b33';
    //         warn.style.marginTop = 0;
    //         warn.textContent = "Note : ligne de saisie — modifications appliquées au tableau mais non sauvegardées côté serveur tant que la ligne n'a pas d'ID.";
    //         modal.appendChild(warn);

    //         // Pour la ligne de saisie : afficher un select inline multi-sélection (select2 si disponible)
    //         const selectInline = document.createElement('select');
    //         selectInline.style.width = '100%';
    //         selectInline.multiple = true; // permettre multi-sélection
    //         selectInline.size = 6; // fallback natif : montrer plusieurs lignes

    //         onRendered(() => {
    //             // init UI
    //             try {
    //                 $(selectInline).select2({
    //                     placeholder: '-- Sélectionnez une ou plusieurs factures --',
    //                     dropdownParent: $(document.body),
    //                     width: '100%',
    //                     closeOnSelect: false,
    //                     allowClear: true
    //                 });
    //                 setTimeout(() => { try { $(selectInline).select2('open'); } catch(e){} }, 0);
    //             } catch (e) {
    //                 // select2 peut ne pas être disponible, focus sur le select natif
    //                 selectInline.focus();
    //             }
    //         });

    //         // Remplir le select via AJAX
    //         $.ajax({
    //             url: `/get-nfacturelettree?debit=${encodeURIComponent(debit)}&credit=${encodeURIComponent(credit)}&compte=${encodeURIComponent(compte)}`,
    //             method: 'GET',
    //             success: function(response) {
    //                 if (!response) response = [];
    //                 response.forEach(item => {
    //                     const montantVal = item.debit != null ? item.debit : item.credit;
    //                     const valeur = `${item.id}|${item.numero_facture}|${montantVal}|${item.date}`;
    //                     const opt = new Option(`${item.numero_facture} / ${montantVal} / ${item.date}`, valeur, false, false);
    //                     selectInline.appendChild(opt);
    //                 });

    //                 // pré-sélectionner les valeurs existantes (si présentes)
    //                 if (existingValues && existingValues.length) {
    //                     const present = existingValues.filter(v => Array.from(selectInline.options).some(o => o.value === v));
    //                     if (present.length) {
    //                         try { $(selectInline).val(present).trigger('change'); } catch(e) {
    //                             Array.from(selectInline.options).forEach(o => { if (present.includes(o.value)) o.selected = true; });
    //                         }
    //                     }
    //                 }
    //             },
    //             error: function(err) {
    //                 console.error('Erreur chargement liste inline :', err);
    //             }
    //         });

    //         // Appliquer la sélection
    //         function applyInlineSelection() {
    //             let vals = [];
    //             try {
    //                 vals = $(selectInline).val() || [];
    //             } catch(e) {
    //                 vals = Array.from(selectInline.selectedOptions || []).map(o => o.value);
    //             }

    //             // Convertir au format attendu
    //             const joined = Array.isArray(vals) ? vals.join(' & ') : (vals || '');
    //             cell.setValue(joined);
    //             success(joined);
    //             try { $(selectInline).select2('destroy'); } catch(e){}
    //             moveFocus(vals);
    //         }

    //         // Événements
    //         $(selectInline).on('change', function() {
    //             applyInlineSelection();
    //         });

    //         selectInline.addEventListener('keydown', function(e) {
    //             if (e.key === 'Enter') {
    //                 e.preventDefault();
    //                 applyInlineSelection();
    //             } else if (e.key === 'Escape') {
    //                 try { $(selectInline).select2('destroy'); } catch(e){}
    //                 cancel();
    //             }
    //         });

    //         selectInline.addEventListener('blur', function() {
    //             try { $(selectInline).select2('destroy'); } catch(e){}
    //         });

    //         return selectInline;
    //     }

    //     // Zone d'affichage: checklist (checkboxes)
    //     const checkboxContainer = document.createElement('div');
    //     checkboxContainer.style.maxHeight = '260px';
    //     checkboxContainer.style.overflowY = 'auto';
    //     checkboxContainer.style.border = '1px solid #ddd';
    //     checkboxContainer.style.padding = '6px';
    //     checkboxContainer.style.marginTop = '6px';
    //     modal.appendChild(checkboxContainer);

    //     // Instruction: seul les checkboxes (sélection/désélection)
    //     const infoPara = document.createElement('p');
    //     infoPara.style.marginTop = '8px';
    //     infoPara.textContent = 'Sélectionnez ou désélectionnez les factures ci-dessous.';
    //     modal.appendChild(infoPara);

    //     const btnRow = document.createElement("div");
    //     btnRow.style.marginTop = "10px";
    //     btnRow.style.display = "flex";
    //     btnRow.style.justifyContent = "flex-end";
    //     btnRow.style.gap = "8px";

    //     const cancelBtn = document.createElement("button");
    //     cancelBtn.textContent = "Annuler";
    //     cancelBtn.className = "btn btn-secondary";

    //     const saveBtn = document.createElement("button");
    //     saveBtn.textContent = "Valider";
    //     saveBtn.className = "btn btn-primary";

    //     btnRow.appendChild(cancelBtn);
    //     btnRow.appendChild(saveBtn);
    //     modal.appendChild(btnRow);
    //     overlay.appendChild(modal);
    //     document.body.appendChild(overlay);

    //     // Fermeture propre
    //     function closeModal() {
    //         document.body.removeChild(overlay);
    //         document.removeEventListener('keydown', escHandler);
    //     }

    //     function escHandler(e) {
    //         if (e.key === 'Escape') {
    //             closeModal();
    //             cancel();
    //         }
    //     }

    //     document.addEventListener('keydown', escHandler);

    //     // Récupération des factures via AJAX
    //     $.ajax({
    //         url: `/get-nfacturelettree?debit=${encodeURIComponent(debit)}&credit=${encodeURIComponent(credit)}&compte=${encodeURIComponent(compte)}`,
    //         method: 'GET',
    //         success: function(response) {
    //             if (!response) response = [];

    //             // construire map des items dispo
    //             const dispoMap = {};
    //             response.forEach(item => {
    //                 const montantVal = item.debit != null ? item.debit : item.credit;
    //                 const valeur = `${item.id}|${item.numero_facture}|${montantVal}|${item.date}`;
    //                 dispoMap[valeur] = `${item.numero_facture} / ${montantVal} / ${item.date}`;
    //             });

    //             // existingValues est défini plus haut
    //             // Ajouter checkbox pour valeurs existantes (y compris manuelles)
    //             const existSet = new Set(existingValues);
    //             existingValues.forEach(v => {
    //                 const parts = v.split('|');
    //                 const label = (parts[1] || '') + ' / ' + (parts[2] || '') + ' / ' + (parts[3] || '');
    //                 const cb = document.createElement('div');
    //                 cb.innerHTML = `<label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" value="${v}" checked> <span>${label}</span></label>`;
    //                 checkboxContainer.appendChild(cb);
    //                 // remove from dispoMap candidate so it doesn't appear in availableSelect
    //                 if (dispoMap[v]) delete dispoMap[v];
    //             });

    //             // Ajouter le reste des dispo comme unchecked
    //             Object.keys(dispoMap).forEach(val => {
    //                 const cb = document.createElement('div');
    //                 cb.innerHTML = `<label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" value="${val}"> <span>${dispoMap[val]}</span></label>`;
    //                 checkboxContainer.appendChild(cb);
    //             });



    //             // valider à la fermeture ou clic sur Valider
    //             saveBtn.addEventListener('click', function() {
    //                 // collect checked values
    //                 const checked = [];
    //                 checkboxContainer.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
    //                     checked.push(cb.value);
    //                 });

    //                 // Convertir au format attendu par l'app (ancienne impl. : 'val1 & val2')
    //                 const joined = Array.isArray(checked) ? checked.join(' & ') : (checked || '');

    //                 // Appliquer la valeur jointe au tableau (compatible server)
    //                 cell.setValue(joined);
    //                 success(joined);
    //                 closeModal();
    //                 // moveFocus s'attend à un tableau ou longueur -> on passe le tableau
    //                 moveFocus(checked);
    //             });

    //             cancelBtn.addEventListener('click', function() {
    //                 closeModal();
    //                 cancel();
    //             });

    //         },
    //         error: function(error) {
    //             console.error("Erreur AJAX :", error);
    //             closeModal();
    //             cancel();
    //         }
    //     });
    //     // Retourner un élément factice car l'édition est gérée dans la modal
    //     return document.createElement("div");
    // }
    //                 },
                    {title: "N° facture lettrée",
                        field: "fact_lettrer",
                        width: 200,
                        headerFilter: "input",

                        // =========================
                        // FORMATTER (MODIFIÉ)
                        // =========================
                        formatter: function (cell) {
                            const value = cell.getValue();
                            if (!value) return "";

                            // Normaliser en tableau (format : "val1 & val2")
                            const values = typeof value === "string"
                                ? value.split(/\s*&\s*/).filter(Boolean)
                                : Array.isArray(value) ? value : [];

                            // Afficher uniquement les numéros de facture
                            return values
                                .map(v => {
                                    const parts = v.split("|");
                                    return parts[1] || ""; // numero_facture
                                })
                                .filter(Boolean)
                                .join(" | ");
                        },

                        // =========================
                        // EDITOR (INCHANGÉ)
                        // =========================
                        editor: function(cell, onRendered, success, cancel) {
                            const row = cell.getRow();
                            const rowData = row.getData();

                            row._oldFactLettrer = (cell.getValue() !== undefined && cell.getValue() !== null)
                                ? String(cell.getValue())
                                : '';

                            const compte = row.getCell("compte").getValue();
                            const debit = row.getCell("debit").getValue();
                            const credit = row.getCell("credit").getValue();

                            if (!debit && !credit) {
                                alert("Veuillez remplir une valeur de débit ou crédit.");
                                cancel();
                                return document.createElement("div");
                            }

                            function moveFocus(selectedValues) {
                                const tauxCell = row.getCell("taux_ras_tva");
                                const tauxElement = tauxCell.getElement();
                                const isTauxDisabled = tauxElement.style.pointerEvents === "none";

                                if (!selectedValues.length || isTauxDisabled) {
                                    const dateCell = row.getCell("date_lettrage");
                                    dateCell.edit();
                                    setTimeout(() => {
                                        const input = dateCell.getElement().querySelector("input");
                                        if (input) input.focus();
                                    }, 10);
                                } else {
                                    tauxCell.edit();
                                    setTimeout(() => {
                                        const input = tauxElement.querySelector("input, select");
                                        if (input) input.focus();
                                    }, 10);
                                }
                            }

                            const overlay = document.createElement("div");
                            overlay.style.position = "fixed";
                            overlay.style.top = 0;
                            overlay.style.left = 0;
                            overlay.style.width = "100%";
                            overlay.style.height = "100%";
                            overlay.style.backgroundColor = "rgba(0,0,0,0.4)";
                            overlay.style.display = "flex";
                            overlay.style.justifyContent = "center";
                            overlay.style.alignItems = "center";
                            overlay.style.zIndex = 10002;

                            const modal = document.createElement("div");
                            modal.style.background = "#fff";
                            modal.style.padding = "15px";
                            modal.style.borderRadius = "6px";
                            modal.style.minWidth = "360px";
                            modal.style.boxShadow = "0 6px 20px rgba(0,0,0,0.2)";

                            const title = document.createElement("h4");
                            title.textContent = "Sélection des factures lettrées";
                            title.style.marginTop = 0;
                            modal.appendChild(title);

                            const existingRaw = cell.getValue() ?? [];
                            let existingValues = [];

                            if (typeof existingRaw === 'string') {
                                existingValues = existingRaw.indexOf('&') !== -1
                                    ? existingRaw.split(/\s*&\s*/).map(s => s.trim()).filter(Boolean)
                                    : (existingRaw.trim() ? [existingRaw.trim()] : []);
                            } else if (Array.isArray(existingRaw)) {
                                existingValues = existingRaw;
                            }

                            const checkboxContainer = document.createElement('div');
                            checkboxContainer.style.maxHeight = '260px';
                            checkboxContainer.style.overflowY = 'auto';
                            checkboxContainer.style.border = '1px solid #ddd';
                            checkboxContainer.style.padding = '6px';
                            checkboxContainer.style.marginTop = '6px';
                            modal.appendChild(checkboxContainer);

                            const btnRow = document.createElement("div");
                            btnRow.style.marginTop = "10px";
                            btnRow.style.display = "flex";
                            btnRow.style.justifyContent = "flex-end";
                            btnRow.style.gap = "8px";

                            const cancelBtn = document.createElement("button");
                            cancelBtn.textContent = "Annuler";
                            cancelBtn.className = "btn btn-secondary";

                            const saveBtn = document.createElement("button");
                            saveBtn.textContent = "Valider";
                            saveBtn.className = "btn btn-primary";

                            btnRow.appendChild(cancelBtn);
                            btnRow.appendChild(saveBtn);
                            modal.appendChild(btnRow);
                            overlay.appendChild(modal);
                            document.body.appendChild(overlay);

                            function closeModal() {
                                document.body.removeChild(overlay);
                            }

                            $.ajax({
                                url: `/get-nfacturelettree?debit=${encodeURIComponent(debit)}&credit=${encodeURIComponent(credit)}&compte=${encodeURIComponent(compte)}`,
                                method: 'GET',
                                success: function(response) {
                                    if (!response) response = [];

                                    const dispoMap = {};
                                    response.forEach(item => {
                                        const montantVal = item.debit != null ? item.debit : item.credit;
                                        const valeur = `${item.id}|${item.numero_facture}|${montantVal}|${item.date}`;
                                        dispoMap[valeur] = `${item.numero_facture} / ${montantVal} / ${item.date}`;
                                    });

                                    existingValues.forEach(v => {
                                        const parts = v.split('|');
                                        const label = `${parts[1] || ''} / ${parts[2] || ''} / ${parts[3] || ''}`;
                                        const cb = document.createElement('div');
                                        cb.innerHTML = `<label><input type="checkbox" value="${v}" checked> ${label}</label>`;
                                        checkboxContainer.appendChild(cb);
                                        delete dispoMap[v];
                                    });

                                    Object.keys(dispoMap).forEach(val => {
                                        const cb = document.createElement('div');
                                        cb.innerHTML = `<label><input type="checkbox" value="${val}"> ${dispoMap[val]}</label>`;
                                        checkboxContainer.appendChild(cb);
                                    });

                                    saveBtn.onclick = function() {
                                        const checked = [];
                                        checkboxContainer.querySelectorAll('input[type="checkbox"]:checked')
                                            .forEach(cb => checked.push(cb.value));

                                        const joined = checked.join(' & ');
                                        cell.setValue(joined);
                                        success(joined);
                                        closeModal();
                                        moveFocus(checked);
                                    };

                                    cancelBtn.onclick = function() {
                                        closeModal();
                                        cancel();
                                    };
                                }
                            });

                            return document.createElement("div");
                        }
                    }
                    ,
                    { title: "Taux RAS TVA",
        field: "taux_ras_tva",
        width: 100,
        headerFilter: "input",
        editor: customListEditor1,
        cellEdited: function(cell) {
            const row = cell.getRow();
            const taux = (cell.getValue() || '').toString().trim();
            const natureCell = row.getCell("nature_op");

            // Fonctions pour activer/désactiver nature_op
            function disableEditor(cell) {
                if (cell) {
                    let el = cell.getElement();
                    if(el) {
                        el.style.pointerEvents = "none";
                        el.style.backgroundColor = "#f2f2f2";
                    }
                }
            }
            function enableEditor(cell) {
                if (cell) {
                    let el = cell.getElement();
                    if(el) {
                        el.style.pointerEvents = "auto";
                        el.style.backgroundColor = "";
                    }
                }
            }

            if (taux === '0%' || taux === '0' || taux === '0') {
                // Si taux 0%, on désactive nature_op
                disableEditor(natureCell);
                natureCell.setValue('');
            } else {
                // Sinon on active nature_op
                enableEditor(natureCell);
            }
        }
                    },
                    { title: "Nature de l'opération", 
                        field: "nature_op", 
                        width: 100, 
                        editor: customListEditor2, 
                        headerFilter: "input"
                    },
                    { title: "Date lettrage", 
                        field: "date_lettrage", 
                        headerFilter: "input",
                        editor: function(cell, onRendered, success, cancel) {
                            const input = document.createElement("input");
                            input.type = "text";
                            input.style.width = "100%";
                            input.value = cell.getValue() || "";

                            onRendered(() => {
                                input.focus();
                                input.setSelectionRange(input.value.length, input.value.length);
                            });

                            // Gérer Enter pour passer au champ "Pièce justificative"
                            input.addEventListener("keydown", function(e) {
                                if (e.key === "Enter") {
                                    success(input.value); // valide la cellule
                                    setTimeout(() => {
                                        const row = cell.getRow();
                                        const pieceCell = row.getCell("piece_justificative");
                                        if (pieceCell) {
                                            const pieceInput = pieceCell.getElement().querySelector("input");
                                            if (pieceInput) pieceInput.focus();
                                        }
                                    }, 10); // petit délai pour s'assurer que l'édition est terminée
                                } else if (e.key === "Escape") {
                                    cancel();
                                }
                            });

                            return input;
                        }
                    },
                    { title: "Contre-Partie", 
                    field: "contre_partie", 
                    width: 100, 
                    editor: "textarea",
                    editable: false, 
                    headerFilter: "input",
                    },                     
                    { title: "Pièce justificative",
                        field: "piece_justificative",
                        formatter: function (cell) {
                            var input = document.createElement("input");
                            input.type = "text";
                            input.className = "selected-file-input";
                            input.readOnly = true;
                            input.value = cell.getValue() || "";

                            // 🔹 Gérer la touche flèche droite
                            input.addEventListener("keydown", function (e) {
                                if (e.key === "ArrowRight") {
                                    e.preventDefault(); // Empêche le comportement par défaut
                                    const row = cell.getRow();
                                    const nextCell = row.getCell("selectAllCaisse"); // nom du champ cible
                                    if (nextCell) {
                                        const checkbox = nextCell.getElement().querySelector("input.select-row-Caisse");
                                        if (checkbox) checkbox.focus(); // Donne le focus au checkbox
                                    }
                                }
                            });

                            return input;
                        },
                    },
                    { title: "<input type='checkbox' id='selectAllCaisse'>", 
                        field: "selectAllCaisse", 
                        width: 40, 
                        formatter: function() {
                            return "<input type='checkbox' class='select-row-Caisse'>";
                        },
                        headerSort: false,
                        headerFilter: false,
                        align: "center",
                        cellClick: function(e, cell) {
                            var isChecked = $("#selectAllCaisse").prop("checked");
                            tableCaissePrincipale.getRows().forEach(function(row) {
                                row.getCell("select").getElement().querySelector("input.select-row-Caisse").checked = isChecked;
                            });
                        }
                    }

                ],
                
            editable: true,
         footerElement: "<table style='width: auto; margin-top: 6px; border: 1px solid #000; border-collapse: collapse; float: left;'>" +
    // "<tr>" +
    //     "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde actuel :</td>" +
    //     "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-actuel'></span></td>" +
    //     "<td colspan='2' style='border: 1px solid #000;'></td>" + 
    // "</tr>" +
    "<tr>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde initial DB :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-initial-db'></span></td>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde initial CR :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-initial-cr'></span></td>" +
    "</tr>" +
    "<tr>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Cumul débit :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='cumul-debit'></span></td>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Cumul crédit :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='cumul-credit'></span></td>" +
    "</tr>" +
    "<tr>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde débiteur :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-debiteur'></span></td>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde créditeur :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-crediteur'></span></td>" +
    "</tr>" +
"</table>" +
                "<div style='display: flex; align-items: center; gap: 10px;'>"+
                "<label for='joindreetatdecaisse' class='btn-fichier'>état de caisse <i class='fas fa-paperclip'></i>"+
                "</label>" +
  // "<input type='file' id='joindreetatdecaisse' style='display: none;' />"+
                "<span style='cursor: pointer;' onclick='viewl_etat_de_caisse(9, 2025)' title='afficher l_etat de caisse'>👁️</span>"+
                "</div>",

            rowAdded: function(row) {
                // Ajoutez ici une logique pour remplir cette ligne vide si nécessaire
                // Exemple : lorsque l'utilisateur termine une saisie, ajoutez la ligne à la base de données ou au tableau.
            }
        });
          
        }
     // Drapeau pour éviter boucle infinie
    let isProgrammaticEdit = false;

    // Action Caisse sur Enter
    $('#periode-Caisse').off('keydown').on('keydown', function (e) {
        if (e.key === "Enter") {
            const table = window.tableCaissePrincipale;
            const rows = table.getRows();
            if (rows.length > 0) {
                const lastRow = rows[rows.length - 1];
                const dateCell = lastRow.getCell("date");
                if (dateCell && !dateCell.isEditing()) {
                    dateCell.edit();
                }
            }
        }
    });

    window.tableCaissePrincipale.on("cellEdited", function(cell) {
        if (isProgrammaticEdit) return;

        try {
            const row = cell.getRow();
            const rowData = row.getData();

            // Ignorer la ligne d'ajout (nouvelle ligne vide)
            if (rowData.id === undefined || rowData.id === null) {
                return; // ne rien faire pour la ligne vide d'ajout
            }

                // Déterminer l'ancienne valeur selon le champ édité (prise en charge de fact_lettrer)
            const field = cell.getField();
            let oldValue = "";
            if (field === 'piece_justificative') {
                oldValue = row._oldPieceJustificative || "";
            } else if (field === 'fact_lettrer') {
                oldValue = row._oldFactLettrer || "";
            } else {
                // fallback générique si nécessaire
                oldValue = row[`_old_${field}`] || "";
            }

            // Normalisation client-side pour comparaison (tolère espaces autour de '&')
            const normalizeLettrageClient = function(val) {
                if (val === null || val === undefined) return '';
                if (Array.isArray(val)) {
                    return val.map(function(s){ return (s||'').toString().trim(); }).filter(Boolean).join(' & ');
                }
                return (''+val).toString().split(/\s*&\s*/).map(function(s){ return s.trim(); }).filter(Boolean).join(' & ');
            };

            const newValue = cell.getValue();
            const oldValueNorm = normalizeLettrageClient(oldValue);
            const newValueNorm = normalizeLettrageClient(newValue);

            // Ignorer si la cellule n'a pas vraiment changé après normalisation
            if ((oldValueNorm === "" && newValueNorm === "") || oldValueNorm === newValueNorm) {
                return;
            }

            // remplacer oldValue par la valeur normalisée pour restaurations/fallback
            oldValue = oldValueNorm;
            // garder newValue tel qu'envoyé (on laisse cell.getValue())

            if (confirm("Voulez-vous enregistrer cette modification ?")) {
                // conserver l'ancienne piece justificative si existante (API attend ce paramètre)
                const oldPieceJustificative = row._oldPieceJustificative || '';

                $.ajax({
                    url: '/update-banque-operation',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        data: rowData,
                        oldPieceJustificative: oldPieceJustificative
                    },
                    success: function(response) {
                        console.log("✅ Données Caisse mises à jour avec succès :", response);
                    },
                    error: function(xhr) {
                        console.error("❌ Erreur lors de la mise à jour Caisse :", xhr.responseText);
                        isProgrammaticEdit = true;
                        // restaurer la valeur précédente du champ édité
                        cell.setValue(oldValue);
                        isProgrammaticEdit = false;
                        alert("Erreur lors de la mise à jour, valeur restaurée !");
                    }
                });
            } else {
                isProgrammaticEdit = true;
                // restaurer la valeur précédente du champ édité
                cell.setValue(oldValue);
                isProgrammaticEdit = false;
                console.log("Modification annulée");
            }
        } catch (err) {
            console.error("Erreur cellEdited Caisse:", err);
        }
    });




    });
});



document.getElementById("modifier-compte-caisse").addEventListener("click", function() {
  // Créer le fond du pop-up
  const overlay = document.createElement("div");
  overlay.style.position = "fixed";
  overlay.style.top = 0;
  overlay.style.left = 0;
  overlay.style.width = "100%";
  overlay.style.height = "100%";
  overlay.style.backgroundColor = "rgba(0,0,0,0.5)";
  overlay.style.display = "flex";
  overlay.style.justifyContent = "center";
  overlay.style.alignItems = "center";
  overlay.style.zIndex = "1000";

  // Contenu du pop-up
  const popup = document.createElement("div");
  popup.style.background = "#fff";
  popup.style.padding = "20px";
  popup.style.borderRadius = "8px";
  popup.style.minWidth = "320px";
  popup.style.boxShadow = "0 4px 10px rgba(0,0,0,0.3)";
  popup.style.textAlign = "center";

  // Titre
  const title = document.createElement("h3");
  title.textContent = "Remplacer tous les comptes";
  popup.appendChild(title);

  // Helper pour créer un label + select et le remplir
function createSelectField(id, labelText) {
  const wrap = document.createElement("div");
  wrap.style.marginTop = "10px";
  wrap.style.textAlign = "left";

  const label = document.createElement("label");
  label.textContent = labelText;
  label.setAttribute("for", id);
  label.style.display = "block";
  label.style.marginBottom = "6px";
  wrap.appendChild(label);

  // Conteneur du select custom
  const container = document.createElement("div");
  container.style.position = "relative";

  // Input pour la recherche
  const searchInput = document.createElement("input");
  searchInput.type = "text";
  searchInput.placeholder = "Rechercher ou sélectionner...";
  searchInput.style.width = "100%";
  searchInput.style.padding = "6px 8px";
  searchInput.style.boxSizing = "border-box";
  container.appendChild(searchInput);

  // Le vrai select, masqué mais utilisé pour la valeur
  const select = document.createElement("select");
  select.id = id;
  select.size = 6; // montre 6 lignes
  select.style.width = "100%";
  select.style.boxSizing = "border-box";
  select.style.position = "absolute";
  select.style.top = "36px";
  select.style.left = 0;
  select.style.zIndex = 10;
  select.style.background = "#fff";
  select.style.border = "1px solid #ccc";
  select.style.display = "none"; // masqué au départ
  select.style.maxHeight = "180px";
  select.style.overflowY = "auto";

  // Placeholder
  select.appendChild(new Option("— Sélectionnez un compte —", ""));
  container.appendChild(select);

  wrap.appendChild(container);

  // Événement : focus ouvre la liste
  searchInput.addEventListener("focus", () => {
    select.style.display = "block";
  });

  // Fermer la liste quand on clique ailleurs
  document.addEventListener("click", (e) => {
    if (!container.contains(e.target)) {
      select.style.display = "none";
    }
  });

  // Sélection d’un élément
  select.addEventListener("change", () => {
    const option = select.options[select.selectedIndex];
    searchInput.value = option.text;
    select.style.display = "none";
  });

  // Recherche dans les options
  searchInput.addEventListener("input", () => {
    const filter = searchInput.value.toLowerCase();
    Array.from(select.options).forEach(opt => {
      if (opt.value === "") return; // garder placeholder
      const txt = opt.text.toLowerCase();
      opt.style.display = txt.includes(filter) ? "" : "none";
    });
  });

  return { wrap, select, searchInput };
}


  const ancienField = createSelectField("modifier-compte-caisse-ancien", "Compte à remplacer :");
  popup.appendChild(ancienField.wrap);

  const nouveauField = createSelectField("modifier-compte-caisse-nouveau", "Remplacer par :");
  popup.appendChild(nouveauField.wrap);

  // Remplir les selects depuis planComptable (si disponible) sinon fetch
  async function fillSelects() {
    let plan = window.planComptable || window.planComptableGlobal || null;
    if (!Array.isArray(plan) || plan.length === 0) {
      try {
        const soc = document.querySelector('#societe_id')?.value || document.querySelector('meta[name="societe_id"]')?.getAttribute('content') || '';
        const url = '/get-plan-comptable' + (soc ? ('?societe_id=' + encodeURIComponent(soc)) : '');
        const resp = await fetch(url, { credentials: 'same-origin' });
        if (resp.ok) plan = await resp.json();
      } catch (e) {
        console.warn("Impossible de récupérer le plan comptable :", e);
        plan = [];
      }
    }

    // normaliser tableau d'objets ou de strings
    if (!Array.isArray(plan)) plan = [];
    const items = plan.map(p => {
      if (!p) return null;
      if (typeof p === 'string') {
        const code = p.split(" - ")[0].trim();
        return { compte: code, intitule: p.replace(code, '').replace(/^-/, '').trim() };
      }
      return { compte: String(p.compte ?? p.code ?? p.id ?? ""), intitule: String(p.intitule ?? p.intitule_compte ?? p.label ?? "") };
    }).filter(Boolean);


    function populate(select) {
      // remove existing options except placeholder
      select.querySelectorAll("option:not([value=''])").forEach(o => o.remove());
     items.forEach(it => {
      const opt = new Option(`${it.compte} - ${it.intitule}`, it.compte, false, false);
       select.appendChild(opt);
     });
     // afficher seulement le numéro de compte (sans intitulé)
    items.forEach(it => {
      const opt = new Option(it.compte, it.compte, false, false);
       select.appendChild(opt);
     });
    }


    populate(ancienField.select);
    populate(nouveauField.select);
  }

  // call fill
  fillSelects();

  // Conteneur boutons
  const btnContainer = document.createElement("div");
  btnContainer.style.marginTop = "15px";
  btnContainer.style.display = "flex";
  btnContainer.style.justifyContent = "space-between";

  // Bouton envoyer
  const sendBtn = document.createElement("button");
  sendBtn.textContent = "Remplacer Tous";
  sendBtn.style.padding = "8px 12px";
  sendBtn.style.border = "none";
  sendBtn.style.background = "#007bff";
  sendBtn.style.color = "#fff";
  sendBtn.style.borderRadius = "4px";
  sendBtn.style.cursor = "pointer";

  // Bouton fermer
  const closeBtn = document.createElement("button");
  closeBtn.textContent = "Fermer";
  closeBtn.style.padding = "8px 12px";
  closeBtn.style.border = "none";
  closeBtn.style.background = "#d33";
  closeBtn.style.color = "#fff";
  closeBtn.style.borderRadius = "4px";
  closeBtn.style.cursor = "pointer";

  // Fermer le popup
  closeBtn.addEventListener("click", () => {
    document.body.removeChild(overlay);
  });

  // Envoyer les données
  sendBtn.addEventListener("click", async () => {
    const ancien = ancienField.select.value.trim();
    const nouveau = nouveauField.select.value.trim();

    if (!ancien || !nouveau) {
      alert("Veuillez sélectionner l'ancien et le nouveau compte.");
      return;
    }

    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const response = await fetch("/modifier-tous-compte-caise", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrf
        },
        body: JSON.stringify({
          ancien_compte: ancien,
          nouveau_compte: nouveau
        }),
        credentials: 'same-origin'
      });

      if (!response.ok) {
        throw new Error("Erreur serveur : " + response.status);
      }

      const result = await response.json();
      alert("Succès : " + (result.message || "Compte modifié !"));
      document.body.removeChild(overlay);

    } catch (error) {
      console.error("Erreur :", error);
      alert("Une erreur est survenue lors de l’envoi.");
    }
  });

  btnContainer.appendChild(sendBtn);
  btnContainer.appendChild(closeBtn);
  popup.appendChild(btnContainer);
  overlay.appendChild(popup);
  document.body.appendChild(overlay);
});


document.getElementById("modifier-compte-banque").addEventListener("click", function() {
  // Créer le fond du pop-up
  const overlay = document.createElement("div");
  overlay.style.position = "fixed";
  overlay.style.top = 0;
  overlay.style.left = 0;
  overlay.style.width = "100%";
  overlay.style.height = "100%";
  overlay.style.backgroundColor = "rgba(0,0,0,0.5)";
  overlay.style.display = "flex";
  overlay.style.justifyContent = "center";
  overlay.style.alignItems = "center";
  overlay.style.zIndex = "1000";

  // Contenu du pop-up
  const popup = document.createElement("div");
  popup.style.background = "#fff";
  popup.style.padding = "20px";
  popup.style.borderRadius = "8px";
  popup.style.minWidth = "320px";
  popup.style.boxShadow = "0 4px 10px rgba(0,0,0,0.3)";
  popup.style.textAlign = "center";

  // Titre
  const title = document.createElement("h3");
  title.textContent = "Remplacer tous les comptes";
  popup.appendChild(title);

  // --- Champ select avec recherche intégrée ---
  function createSelectField(id, labelText) {
    const wrap = document.createElement("div");
    wrap.style.marginTop = "10px";
    wrap.style.textAlign = "left";

    const label = document.createElement("label");
    label.textContent = labelText;
    label.setAttribute("for", id);
    label.style.display = "block";
    label.style.marginBottom = "6px";
    wrap.appendChild(label);

    // Conteneur du select custom
    const container = document.createElement("div");
    container.style.position = "relative";

    // Input de recherche
    const searchInput = document.createElement("input");
    searchInput.type = "text";
    searchInput.placeholder = "Rechercher ou sélectionner...";
    searchInput.style.width = "100%";
    searchInput.style.padding = "6px 8px";
    searchInput.style.boxSizing = "border-box";
    container.appendChild(searchInput);

    // Vrai select (pour la valeur)
    const select = document.createElement("select");
    select.id = id;
    select.size = 6;
    select.style.width = "100%";
    select.style.position = "absolute";
    select.style.top = "36px";
    select.style.left = 0;
    select.style.zIndex = 10;
    select.style.background = "#fff";
    select.style.border = "1px solid #ccc";
    select.style.display = "none";
    select.style.maxHeight = "180px";
    select.style.overflowY = "auto";
    select.style.boxSizing = "border-box";
    select.appendChild(new Option("— Sélectionnez un compte —", ""));
    container.appendChild(select);

    wrap.appendChild(container);

    // Ouvrir la liste au focus
    searchInput.addEventListener("focus", () => {
      select.style.display = "block";
    });

    // Fermer quand on clique ailleurs
    document.addEventListener("click", (e) => {
      if (!container.contains(e.target)) {
        select.style.display = "none";
      }
    });

    // Sélection d’un compte
    select.addEventListener("change", () => {
      const opt = select.options[select.selectedIndex];
      searchInput.value = opt.text;
      select.style.display = "none";
    });

    // Filtrer la liste pendant la saisie
    searchInput.addEventListener("input", () => {
      const filter = searchInput.value.toLowerCase();
      Array.from(select.options).forEach(opt => {
        if (opt.value === "") return;
        const txt = opt.text.toLowerCase();
        opt.style.display = txt.includes(filter) ? "" : "none";
      });
    });

    return { wrap, select, searchInput };
  }

  // Créer les deux champs
  const ancienField = createSelectField("modifier-compte-banque-ancien", "Compte à remplacer :");
  popup.appendChild(ancienField.wrap);

  const nouveauField = createSelectField("modifier-compte-banque-nouveau", "Remplacer par :");
  popup.appendChild(nouveauField.wrap);

  // --- Remplir les selects depuis le plan comptable ---
  async function fillSelects() {
    let plan = window.planComptable || window.planComptableGlobal || null;
    if (!Array.isArray(plan) || plan.length === 0) {
      try {
        const soc = document.querySelector('#societe_id')?.value || document.querySelector('meta[name="societe_id"]')?.getAttribute('content') || '';
        const url = '/get-plan-comptable' + (soc ? ('?societe_id=' + encodeURIComponent(soc)) : '');
        const resp = await fetch(url, { credentials: 'same-origin' });
        if (resp.ok) plan = await resp.json();
      } catch (e) {
        console.warn("Impossible de récupérer le plan comptable :", e);
        plan = [];
      }
    }

    if (!Array.isArray(plan)) plan = [];
    const items = plan.map(p => {
      if (!p) return null;
      if (typeof p === 'string') {
        const code = p.split(" - ")[0].trim();
        return { compte: code, intitule: p.replace(code, '').replace(/^-/, '').trim() };
      }
      return { compte: String(p.compte ?? p.code ?? p.id ?? ""), intitule: String(p.intitule ?? p.intitule_compte ?? p.label ?? "") };
    }).filter(Boolean);

    function populate(select) {
      select.querySelectorAll("option:not([value=''])").forEach(o => o.remove());
      items.forEach(it => {
        const opt = new Option(`${it.compte} - ${it.intitule}`, it.compte, false, false);
        select.appendChild(opt);
      });
    }

    populate(ancienField.select);
    populate(nouveauField.select);
  }

  fillSelects();

  // --- Boutons ---
  const btnContainer = document.createElement("div");
  btnContainer.style.marginTop = "15px";
  btnContainer.style.display = "flex";
  btnContainer.style.justifyContent = "space-between";

  const sendBtn = document.createElement("button");
  sendBtn.textContent = "Remplacer Tous";
  sendBtn.style.padding = "8px 12px";
  sendBtn.style.border = "none";
  sendBtn.style.background = "#007bff";
  sendBtn.style.color = "#fff";
  sendBtn.style.borderRadius = "4px";
  sendBtn.style.cursor = "pointer";

  const closeBtn = document.createElement("button");
  closeBtn.textContent = "Fermer";
  closeBtn.style.padding = "8px 12px";
  closeBtn.style.border = "none";
  closeBtn.style.background = "#d33";
  closeBtn.style.color = "#fff";
  closeBtn.style.borderRadius = "4px";
  closeBtn.style.cursor = "pointer";

  closeBtn.addEventListener("click", () => {
    document.body.removeChild(overlay);
  });

  sendBtn.addEventListener("click", async () => {
    const ancien = ancienField.select.value.trim();
    const nouveau = nouveauField.select.value.trim();

    if (!ancien || !nouveau) {
      alert("Veuillez sélectionner l'ancien et le nouveau compte.");
      return;
    }

    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const response = await fetch("/modifier-tous-compte-banque", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrf
        },
        body: JSON.stringify({
          ancien_compte: ancien,
          nouveau_compte: nouveau
        }),
        credentials: 'same-origin'
      });

      if (!response.ok) throw new Error("Erreur serveur : " + response.status);
      const result = await response.json();
      alert("Succès : " + (result.message || "Compte modifié !"));
      document.body.removeChild(overlay);
    } catch (error) {
      console.error("Erreur :", error);
      alert("Une erreur est survenue lors de l’envoi.");
    }
  });

  btnContainer.appendChild(sendBtn);
  btnContainer.appendChild(closeBtn);
  popup.appendChild(btnContainer);
  overlay.appendChild(popup);
  document.body.appendChild(overlay);
});



function ensureTransferModal(type) {
  const key = String((type || '')).toLowerCase();
  const modalId = `transferJournalModal-${key}`;
  if (document.getElementById(modalId)) return modalId;

  const selId = `transfer-target-${key}`;
  const html = `
  <div id="${modalId}" class="modal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.4);z-index:9999;">
    <div class="modal-content" style="max-width:420px;margin:8% auto;padding:16px;border-radius:8px;background:#fff;position:relative;">
      <h5 style="margin-top:0;margin-bottom:10px;">Transférer lignes — ${type}</h5>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <label style="font-size:12px;">Journal cible</label>
        <select id="${selId}" style="padding:6px;border:1px solid #ccc;border-radius:6px;"></select>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;">
          <button id="transfer-cancel-${key}" class="btn btn-secondary" type="button">Annuler</button>
          <button id="transfer-confirm-${key}" class="btn btn-primary" type="button">Transférer</button>
        </div>
        <div id="transfer-feedback-${key}" style="font-size:13px;color:#666;margin-top:6px;display:none;"></div>
      </div>
    </div>
  </div>`;
  const wrap = document.createElement('div');
  wrap.innerHTML = html;
  document.body.appendChild(wrap.firstElementChild);
  return modalId;
}

function showTransferModal(type, options = []) {
  return new Promise(resolve => {
    const key = String((type || '')).toLowerCase();
    const modalId = ensureTransferModal(type);
    const modal = document.getElementById(modalId);
    const sel = document.getElementById(`transfer-target-${key}`);
    const btnCancel = document.getElementById(`transfer-cancel-${key}`);
    const btnConfirm = document.getElementById(`transfer-confirm-${key}`);
    const feedback = document.getElementById(`transfer-feedback-${key}`);

    // fill options
    sel.innerHTML = '';
    options.forEach(o => {
      const opt = document.createElement('option');
      // Afficher en priorité le code du journal (value / code_journal)
      opt.value = o.value ?? o.code_journal ?? o;
      // TEXTE affiché : prioriser le code (opt.value) puis fallback sur intitule ou text si besoin
      opt.textContent = String(o.value ?? o.code_journal ?? o.text ?? o.intitule ?? opt.value);
      sel.appendChild(opt);
    });

    // show
    modal.style.display = 'block';
    sel.focus();

    function cleanup() {
      modal.style.display = 'none';
      btnCancel.removeEventListener('click', onCancel);
      btnConfirm.removeEventListener('click', onConfirm);
      modal.removeEventListener('click', onBackdropClick);
      sel.removeEventListener('keydown', onKeydown);
    }

    function onCancel() { cleanup(); resolve(null); }
    function onConfirm() {
      const v = sel.value;
      if (!v) {
        if (feedback) { feedback.style.display = 'block'; feedback.textContent = 'Veuillez choisir un journal'; setTimeout(()=> feedback.style.display='none',1600); }
        return;
      }
      cleanup(); resolve(v);
    }
    function onBackdropClick(e) { if (e.target === modal) { onCancel(); } }
    function onKeydown(e) {
      if (e.key === 'Enter') { e.preventDefault(); onConfirm(); }
      else if (e.key === 'Escape') { e.preventDefault(); onCancel(); }
    }

    btnCancel.addEventListener('click', onCancel);
    btnConfirm.addEventListener('click', onConfirm);
    modal.addEventListener('click', onBackdropClick);
    sel.addEventListener('keydown', onKeydown);
  });
}


document.getElementById('transfereBanque')?.addEventListener('click', async function (e) {
    try {
        if (typeof tableBanque === 'undefined' || !tableBanque) {
            (window.Swal ?? alert)('Table Banque non initialisée.');
            return;
        }

        // Récupérer les lignes sélectionnées
        const rows = tableBanque.getRows ? tableBanque.getRows() : [];
        const selected = [];
        rows.forEach(row => {
            try {
                const data = row.getData() || {};
                if (data.selected) { selected.push(data); return; }
                const cell = row.getCell && row.getCell("selected");
                if (cell) {
                    const el = cell.getElement();
                    const input = el && el.querySelector && el.querySelector('input.select-row[type="checkbox"]');
                    if (input && input.checked) selected.push(data);
                }
            } catch (err) { /* ignore */ }
        });

        if (!selected.length) {
            (window.Swal ?? alert)('Aucune ligne sélectionnée à transférer.');
            return;
        }

        // Récupérer la liste des journaux disponibles depuis le select #journal-Banque (fallback fetch)
        let journalOptions = Array.from(document.querySelectorAll('#journal-Banque option'))
            .map(o => ({ value: o.value, text: (o.dataset.intitule ? o.dataset.intitule : o.textContent) || o.value }))
            .filter(j => j.value);

        if (!journalOptions.length) {
            try {
                const resp = await fetch('/journaux-Banque', { credentials: 'same-origin' });
                if (resp.ok) {
                    const data = await resp.json();
                    if (Array.isArray(data)) journalOptions = data.map(j => ({ value: j.code_journal, text: j.intitule || j.code_journal }));
                }
            } catch (e) { /* ignore */ }
        }

        // Afficher notre modal stylé de choix de journal
        const chosenJournal = await showTransferModal('Banque', journalOptions);
        if (!chosenJournal) return; // annulation

        // Préparer payload
        const payload = { lignes: selected, code_journal: chosenJournal };

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const resp = await fetch('/transfere-banque', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        if (!resp.ok) {
            const text = await resp.text().catch(()=>null);
            throw new Error(`HTTP ${resp.status} ${text || ''}`);
        }

        const json = await resp.json().catch(()=>null);

        if (window.Swal) {
            Swal.fire({ icon: 'success', title: 'Transfert', text: 'Lignes transférées avec succès', timer: 1400, showConfirmButton: false });
        } else {
            console.log('Transfert OK', json);
        }

        if (typeof fetchOperations === 'function') fetchOperations();
        else if (typeof tableBanque.replaceData === 'function') tableBanque.replaceData([]); // fallback léger

    } catch (err) {
        console.error('Erreur transfert banque :', err);
        if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur', text: String(err.message || err) });
        else alert('Erreur lors du transfert: ' + (err.message || err));
    }
});

document.getElementById('transfereCaisse')?.addEventListener('click', async function (e) {
    try {
        if (typeof tableCaissePrincipale === 'undefined' || !tableCaissePrincipale) {
            (window.Swal ?? alert)('Table Caisse non initialisée.');
            return;
        }

        // Récupérer les lignes sélectionnées
        const rows = typeof tableCaissePrincipale.getRows === 'function' ? tableCaissePrincipale.getRows() : [];
        const selected = [];
        rows.forEach(row => {
            try {
                const data = row.getData() || {};
                if (data.selected) { selected.push(data); return; }
                const cell = row.getCell && row.getCell("selectAllCaisse");
                if (cell) {
                    const el = cell.getElement();
                    const input = el && el.querySelector && el.querySelector('input.select-row-Caisse[type="checkbox"], input.select-row-Caisse');
                    if (input && input.checked) selected.push(data);
                }
            } catch (err) { /* ignore */ }
        });

        if (!selected.length) {
            (window.Swal ?? alert)('Aucune ligne sélectionnée à transférer.');
            return;
        }

        // Récupérer la liste des journaux disponibles depuis le select #journal-Caisse (fallback fetch)
        let journalOptions = Array.from(document.querySelectorAll('#journal-Caisse option'))
            .map(o => ({ value: o.value, text: (o.dataset.intitule ? o.dataset.intitule : o.textContent) || o.value }))
            .filter(j => j.value);

        if (!journalOptions.length) {
            try {
                const resp = await fetch('/journaux-Caisse', { credentials: 'same-origin' });
                if (resp.ok) {
                    const data = await resp.json();
                    if (Array.isArray(data)) journalOptions = data.map(j => ({ value: j.code_journal, text: j.intitule || j.code_journal }));
                }
            } catch (e) { /* ignore */ }
        }

        // Afficher notre modal stylé de choix de journal (Caisse)
        const chosenJournal = await showTransferModal('Caisse', journalOptions);
        if (!chosenJournal) return; // annulation

        // Préparer payload
        const payload = { lignes: selected, code_journal: chosenJournal };

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const resp = await fetch('/transfere-caisse', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        if (!resp.ok) {
            const text = await resp.text().catch(()=>null);
            throw new Error(`HTTP ${resp.status} ${text || ''}`);
        }

        const json = await resp.json().catch(()=>null);

        if (window.Swal) {
            Swal.fire({ icon: 'success', title: 'Transfert', text: 'Lignes transférées avec succès', timer: 1400, showConfirmButton: false });
        } else {
            console.log('Transfert Caisse OK', json);
        }

        // rafraîchir la table ou recharger les données
        if (typeof fetchOperationsCaisse === 'function') fetchOperationsCaisse();
        else if (typeof tableCaissePrincipale.replaceData === 'function') tableCaissePrincipale.replaceData([]); // fallback léger

    } catch (err) {
        console.error('Erreur transfert caisse :', err);
        if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur', text: String(err.message || err) });
        else alert('Erreur lors du transfert: ' + (err.message || err));
    }
});

document.querySelector('#periode-Caisse').addEventListener('keydown', function(e) {
    if (e.key === "Enter") {
        e.preventDefault(); // empêche le comportement par défaut

        // Récupère toutes les lignes
        const rows = tableCaissePrincipale.getRows();
        let emptyRowCell = null;

        // Cherche la première ligne avec la cellule "date" vide
        for (let row of rows) {
            const cell = row.getCell("date");
            if (cell && !cell.getValue()) { // si la cellule est vide
                emptyRowCell = cell;
                break;
            }
        }

        // Si on trouve une cellule vide, on ouvre l'éditeur
        if (emptyRowCell) {
            emptyRowCell.edit();
        } else {
            // Optionnel : créer une nouvelle ligne vide si aucune cellule vide n'existe
            const newRow = tableCaissePrincipale.addRow({date: ""}, true); // true = ajouter en haut
            const newCell = newRow.getCell("date");
            if (newCell) {
                newCell.edit();
            }
        }
    }
});


 
$(document).on('change', '#joindreetatdecaisse', function (e) {
  console.log('import etat de caisse');
    var file = e.target.files[0];
    if (!file) return;

    // utiliser le journal CAISSE et la période CAISSE
    var codeJournal = $('#journal-Caisse').val() || '';

    // extraire l'année depuis data-exercice-date (comme pour Banque)
    var exerciceDate = $('#exercice-date').data('exercice-date');
    var annee = exerciceDate ? new Date(exerciceDate).getFullYear() : '';

    // période/ mois pour la caisse
    var mois = $('#periode-Caisse').val() || '';
// console.log(mois);
    var formData = new FormData();
    // nom du champ upload adapté pour "etat de caisse"
    formData.append('etat_de_caisse', file);
    formData.append('code_journal', codeJournal);
    formData.append('annee', annee);
    formData.append('mois', mois);
    // optionnel : préciser la source
    formData.append('source', 'caisse');

    $.ajax({
        url: '/upload-etat-de-caisse', // endpoint côté serveur à créer/adapter
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            // désactiver l'input temporairement pour éviter re-envois
            $('#joindreetatdecaisse').prop('disabled', true);
        },
        success: function (response) {
            console.log("État de caisse envoyé avec succès :", response);
            if (window.Swal) Swal.fire({ icon: 'success', title: 'OK', text: 'État de caisse envoyé.' });
            else alert("État de caisse envoyé avec succès !");
            // reset input
            $('#joindreetatdecaisse').val('');
        },
        error: function (xhr, status, error) {
            console.error("Erreur lors de l'envoi de l'état de caisse :", error, xhr.responseText);
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur', text: 'Envoi de l\'état de caisse échoué.' });
            else alert("Une erreur est survenue lors de l'envoi de l'état de caisse.");
        },
        complete: function() {
            $('#joindreetatdecaisse').prop('disabled', false);
        }
    });
});

$(document).on('click', '#export-CaisseExcel', function () {
    exportCaisseToExcel();
});
$(document).on('click', '#export-CaissePDF', function () {
    exportCaisseToPDF();
});

$(document).off('change', '#selectAll').on('change', '#selectAll', function() {
    const isChecked = $(this).is(':checked');

    tableBanque.getRows().forEach(function(row) {
        const data = row.getData();

        const isSaisie = !data.date && !data.mode_paiement && !data.compte &&
                         !data.libelle && !data.debit && !data.credit;

        if (!isSaisie) {
            row.update({ selected: isChecked });
        } else {
            row.update({ selected: false });
        }
    });
});

$(document).off('change', '#selectAllCaisse').on('change', '#selectAllCaisse', function () {
    const isChecked = $(this).is(':checked');

    tableCaissePrincipale.getRows().forEach(function (row) {
        const checkbox = row.getElement().querySelector('input.select-row-Caisse');

        if (checkbox) {
            const data = row.getData();

            const isSaisie =
                !data.date && !data.mode_paiement && !data.compte &&
                !data.libelle && !data.debit && !data.credit;

            if (!isSaisie) {
                checkbox.checked = isChecked;
            } else {
                checkbox.checked = false;
            }
        }
    });
});

$('#journal-Caisse').on('change', function() {
    var selectedJournalCode = $(this).val();
    var selectedOption = $(this).find('option:selected');
    var intitule = selectedOption.data('intitule');
    var tabId = $(this).attr('id').replace('journal-', 'filter-intitule-');
    $('#' + tabId).val(intitule ? intitule : '');
    fetchOperationsCaisse(selectedJournalCode);
    updateFooterCaisse();
  });
// $(document).on('click', '.upload-icon[data-action="open-modal"]', function(e) {
//     e.stopPropagation();
//     $('#files_banque_Modal').show();

// });

function JoindreReleveBancaire(){
    $('#banqueModal_main').show();
}
$(document).on('dblclick', '.banqueModal_file-card .banqueModal_card', function () {
    var fileId = $(this).data('fileid'); // Récupère l'id du fichier
    console.log('Fichier sélectionné : ' + fileId);

    var codeJournal = $('#journal-Banque').val();
    var exerciceDate = $('#exercice-date').data('exercice-date');
    var annee = exerciceDate ? new Date(exerciceDate).getFullYear() : '';
    var mois = $('#periode-Banque').val();

    $.ajax({
        url: '/upload-releve-bancaire', // Endpoint pour enregistrer l'id
        type: 'POST',
        data: {
            file_id: fileId,
            code_journal: codeJournal,
            annee: annee,
            mois: mois
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log("Fichier enregistré avec succès :", response);
            alert("Relevé bancaire enregistré !");
        },
        error: function(xhr, status, error) {
            console.error("Erreur lors de l'enregistrement :", error);
            alert("Une erreur est survenue lors de l'enregistrement du relevé bancaire.");
        }
    });
});



// $(document).on('change', '#JoindreReleveBancaire', function (e) {
//     var file = e.target.files[0];
//     if (!file) return;

//     var codeJournal = $('#journal-Banque').val();

//     // Correction ici : extraire l'année depuis data-exercice-date
//     var exerciceDate = $('#exercice-date').data('exercice-date');
//     var annee = exerciceDate ? new Date(exerciceDate).getFullYear() : '';

//     var mois = $('#periode-Banque').val();

//     var formData = new FormData();
//     formData.append('releve_bancaire', file);
//     formData.append('code_journal', codeJournal);
//     formData.append('annee', annee);
//     formData.append('mois', mois);

//     $.ajax({
//         url: '/upload-releve-bancaire',
//         type: 'POST',
//         data: formData,
//         contentType: false,
//         processData: false,
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         },
//         success: function (response) {
//             console.log("Fichier envoyé avec succès :", response);
//             alert("Relevé bancaire envoyé avec succès !");
//         },
//         error: function (xhr, status, error) {
//             console.error("Erreur lors de l'envoi :", error);
//             alert("Une erreur est survenue lors de l'envoi du relevé bancaire.");
//         }
//     });
// });

$('.close-btn').on('click', function() {
        $('#files_banque_Modal').hide();
});
 $(window).on('click', function(event) {
     // Fermer la modale si on clique en dehors de la modale
        if ($(event.target).is('#files_banque_Modal')) {
            $('#files_banque_Modal').hide();
        }
});
$(document).on('keydown', function(e) {
    if (e.key === "Enter" && $(e.target).is('input[type="checkbox"]')) {
        const checkboxElement = e.target;

        // Si tableBanque est définie, on l'utilise
        if (window.tableBanque) {
            const rows = window.tableBanque.getRows();

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const rowElement = row.getElement();

                if (rowElement.contains(checkboxElement)) {
                    const rowData = row.getData();
                    console.log("Donnée de la ligne active :", rowData);

                    sendDataToController([rowData]); // Appel fonction banque
                    return; // stop boucle dès qu'on trouve la ligne
                }
            }
            console.log("Aucune ligne correspondante trouvée dans tableBanque.");
        } else {
            // Si tableBanque n'existe pas, on initialise tableCaissePrincipale si besoin
            if (!window.tableCaissePrincipale) {
                window.tableCaissePrincipale = new Tabulator("#table-Caisse", {
                    // tes options Tabulator ici
                });
            }

            // Puis on récupère les lignes de tableCaissePrincipale
            const rows = window.tableCaissePrincipale.getRows();

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const rowElement = row.getElement();

                if (rowElement.contains(checkboxElement)) {
                    const rowData = row.getData();
                    console.log("Donnée de la ligne active (Caisse) :", rowData);

                    sendDataToControllerCaisse([rowData]); // Appel fonction caisse
                    return; // stop boucle dès qu'on trouve la ligne
                }
            }

            console.log("Aucune ligne correspondante trouvée dans tableCaissePrincipale.");
        }
    }
});

$('#periode-Banque').on('change', function() {
    fetchOperations(); 
});
$('#periode-Caisse').on('change', function() {
    fetchOperationsCaisse(); 
    updateFooterCaisse();
});
$('#filter-exercice-Caisse').on('change', function() {
    fetchOperationsCaisse(); 
    updateFooterCaisse();
});
$('#journal-Banque').on('keydown', function(e) {
  if (e.key === "Enter") {
    $('#filter-contre-partie-Banque').focus();
  }
});
$('#filter-contre-partie-Banque').on('keydown', function(e) {
  if (e.key === "Enter") {
    $('#filter-libre-Banque').focus();
  }
});
$('#filter-libre-Banque').on('keydown', function(e) {
  if (e.key === "Enter") {
    $('#filter-mois-Banque').focus();
  }
});
$('#filter-mois-Banque').on('keydown', function(e) {
  if (e.key === "Enter") {
    $('#filter-exercice-Banque').focus();
  }
});
$('#filter-exercice-Banque').on('keydown', function(e) {
  if (e.key === "Enter") {
    $('#periode-Banque').focus();
  }
});
$('#periode-Banque').on('keydown', function(e) {
  if (e.key === "Enter") {
    const table = tableBanque;
    const rows = table.getRows();
    const lastRow = rows[rows.length - 1]; // Récupérer la dernière ligne (ligne vide)
    const dateCell = lastRow.getCell("date");
    dateCell.edit(); // Déplacer le focus vers la cellule "date"
  }
});
$('#journal-Banque').on('change', function() {
    var selectedJournalCode = $(this).val();
    var selectedOption = $(this).find('option:selected');
    var intitule = selectedOption.data('intitule');
    var tabId = $(this).attr('id').replace('journal-', 'filter-intitule-');
    $('#' + tabId).val(intitule ? intitule : '');
    fetchOperations(selectedJournalCode);
});
$('#delete-row-btn_Banque').on('click', function() {
    // Récupérer toutes les lignes de la table
    const rows = tableBanque.getRows();
    let selectedIds = [];

    // Parcourir les lignes pour trouver celles qui sont sélectionnées
    rows.forEach(row => {
        const cell = row.getCell("selected"); // Utilisez "selected" au lieu de "selectAll"
        if (cell) {
            const checkbox = cell.getElement().querySelector("input");
            if (checkbox && checkbox.checked) {
                selectedIds.push(row.getData().id); // Assurez-vous que 'id' est le champ qui contient l'ID de l'opération
            }
        }
    });

    // Si des lignes sont sélectionnées, envoyer les données
    if (selectedIds.length > 0) {
        deleteOperations(selectedIds);
    } else {
        alert("Veuillez sélectionner au moins une ligne à supprimer.");
    }
});
$('#delete-row-btn-caisse').on('click', function() {
    // Récupérer toutes les lignes de la table
    const rows = tableCaissePrincipale.getRows();
    let selectedIds = [];

    // Parcourir les lignes pour trouver celles qui sont sélectionnées
    rows.forEach(row => {
        const cell = row.getCell("selectAllCaisse"); // Utilisez "selected" au lieu de "selectAll"
        if (cell) {
            const checkbox = cell.getElement().querySelector("input");
            if (checkbox && checkbox.checked) {
                selectedIds.push(row.getData().id); // Assurez-vous que 'id' est le champ qui contient l'ID de l'opération
            }
        }
    });

    // Si des lignes sont sélectionnées, envoyer les données
    if (selectedIds.length > 0) {
        deleteOperations(selectedIds);
    } else {
        alert("Veuillez sélectionner au moins une ligne à supprimer.");
    }
});
$('#export-BanqueExcel').on('click', function() {
    exportToExcel();
});
$('#export-BanquePDF').on('click', function() {
    exportToPDF();
});
$('#import-Banque').on('click', function() {
    $('#importModalBanque').show();
});
$('#import-Caisse').on('click', function() {
    $('#importModalCaisse').show();
});
document.getElementById('selectAll').addEventListener('change', function() {
  // Si le checkbox est cochée, parcourir les lignes de la table et mettre à jour les checkbox individuels
  if (this.checked) {
    tableBanque.getRows().forEach(function(row) {
      row.update({ selected: true });
      row.getCell("selected").getElement().querySelector("input").checked = true;
    });
  } else {
    tableBanque.getRows().forEach(function(row) {
      row.update({ selected: false });
      row.getCell("selected").getElement().querySelector("input").checked = false;
    });
  }
});

/**
 * Ajoute la navigation par la touche Enter à l'élément d'édition.
 * @param {HTMLElement} editorElement - L'élément de l'éditeur (input, textarea, etc.).
 * @param {Object} cell - La cellule Tabulator en cours d'édition.
 * @param {Function} successCallback - La fonction à appeler pour valider la saisie.
 * @param {Function} cancelCallback - (Optionnel) La fonction à appeler en cas d'annulation.
 * @param {Function} getValueCallback - (Optionnel) Fonction pour récupérer la valeur courante de l'éditeur.
 */
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
function customDateEditor(cell, onRendered, success, cancel) {
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";

    // Année de l'exercice
    const exerciceDate = document
        .getElementById('exercice-date')
        .getAttribute('data-exercice-date');
    const exerciceYear = new Date(exerciceDate).getFullYear();

    const selectedPeriod = $('input[name="filter-period-Banque"]:checked').val();

    const currentValue = cell.getValue() || "";
    const [currentDay, currentMonth] = currentValue.split("/");

    /* ================================
       INITIALISATION
       ================================ */
    if (selectedPeriod === "mois") {
        input.placeholder = "Jour";
        input.value = currentDay || "";
    } 
    else if (selectedPeriod === "exercice") {
        input.placeholder = "JJ/MM";
        input.value = currentDay && currentMonth
            ? `${currentDay}/${currentMonth}`
            : "JJ/MM";
    }

    onRendered(() => input.focus());

    /* ================================
       SAISIE JJ/MM AVEC JJ & MM VISIBLES
       ================================ */
    input.addEventListener("input", () => {
        if (selectedPeriod !== "exercice") return;

        let raw = input.value.replace(/\D/g, "");
        raw = raw.slice(0, 4); // JJMM max

        let day = "JJ";
        let month = "MM";

        // Jour
        if (raw.length === 1) day = raw[0] + "J";
        if (raw.length >= 2) day = raw.slice(0, 2);

        // Mois
        if (raw.length === 3) month = raw[2] + "M";
        if (raw.length >= 4) month = raw.slice(2, 4);

        input.value = `${day}/${month}`;
    });

    /* ================================
       BLUR (ajout année)
       ================================ */
    input.addEventListener("blur", () => {
        let value = input.value;

        if (selectedPeriod === "mois") {
            const selectedMonth = $('#periode-Banque').val();
            if (selectedMonth && value) {
                const day = value.padStart(2, "0");
                const month = selectedMonth.toString().padStart(2, "0");
                value = `${day}/${month}/${exerciceYear}`;
            }
        } 
        else if (selectedPeriod === "exercice") {
            const [day, month] = value.split("/");
            if (!day.includes("J") && !month.includes("M")) {
                value = `${day.padStart(2, "0")}/${month.padStart(2, "0")}/${exerciceYear}`;
            } else {
                value = "";
            }
        }

        success(value);
    });

    /* ================================
       ENTER → PIÈCE JUSTIFICATIVE
       ================================ */
    input.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            input.blur();

            // Génération pièce justificative
            setTimeout(() => {
                const row = cell.getRow();

                function generatePieceJustificativeNum(row) {
                    const date = row.getCell("date").getValue();
                    let jour = "";
                    let annee = "";

                    if (date) {
                        // Regex modifiée pour accepter 1 ou 2 chiffres pour jour/mois
                        let match = date.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
                        if (match) {
                            jour = match[1].padStart(2, "0");
                            annee = match[3];
                        }
                    }

                    let rows = tableBanque.getRows();
                    let maxNum = 0;

                    rows.forEach(r => {
                        let val = r.getCell("piece_justificative").getValue();
                        if (val && val.length >= 4) {
                            let num = parseInt(val.slice(-4), 10);
                            if (!isNaN(num) && num > maxNum) maxNum = num;
                        }
                    });

                    let nextNum = (maxNum + 1).toString().padStart(4, '0');
                    const codeJournal = ($("#journal-Banque").val() || "J").replace(/\s+/g, "");

                    return `P${jour}${annee}${codeJournal}${nextNum}`;
                }

                let pieceCell = row.getCell("piece_justificative");
                if (pieceCell && !pieceCell.getValue()) {
                    pieceCell.setValue(generatePieceJustificativeNum(row));
                }
            }, 50);
        }
    });

    return input;
}





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
function customListEditor1(cell, onRendered, success, cancel) {
    const input = document.createElement("select");
    input.style.width = "100%";

    const options = ["0", "75", "100"];
    options.forEach(option => {
        const opt = document.createElement("option");
        opt.value = option;
        opt.innerHTML = option;
        input.appendChild(opt);
    });

    input.value = cell.getValue() || "";

    onRendered(function () {
        input.focus();
        input.style.height = "100%";
    });

    function validateAndCommit() {
        success(input.value);
    }

    input.addEventListener("blur", function () {
        validateAndCommit();
    });

    input.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            validateAndCommit();

            setTimeout(function () {
                const row = cell.getRow();
                const natureCell = row.getCell("nature_op");
                const pjCell = row.getCell("piece_justificative");

                const el = natureCell.getElement();
                const isDisabled = el && el.style.pointerEvents === "none";

                if (isDisabled) {
                    // Focus sur l'input dans la cellule "piece_justificative"
                    const pjInput = pjCell.getElement().querySelector(".selected-file-input");
                    if (pjInput) {
                        pjInput.focus();
                    }
                } else {
                    // Passer à l'édition de "nature_op"
                    natureCell.edit();
                }
            }, 50);
        }
    });

    return input;
}
function customListEditor2(cell, onRendered, success, cancel) {
    const input = document.createElement("select");
    input.style.width = "100%";

    // Récupérer le compte sélectionné dans la ligne actuelle
    const compteCode = cell.getRow().getCell("compte").getValue();
    const compte = planComptable.find(c => c.compte == compteCode);

    // Déterminer les options en fonction du compte
    let options = [];
    if (compte) {
        if (compte.compte.startsWith('441')) {
            options = [ 
                "1.Achat de biens d'équipement", 
                "2.Achat de travaux", 
                "3.Achat de services"
            ];
        } else if (compte.compte.startsWith('342')) {
            options = [ 
                "4.Vente de biens d'équipement", 
                "5.Vente de travaux", 
                "6.Vente de services"
            ];
        } else {
            options = [
                "1.Achat de biens d'équipement", 
                "2.Achat de travaux", 
                "3.Achat de services", 
                "4.Vente de biens d'équipement",
                "5.Vente de travaux", 
                "6.Vente de services"
            ];
        }
    }

    // Remplir le select avec les options
    options.forEach(option => {
        const opt = document.createElement("option");
        opt.value = option;
        opt.innerHTML = option;
        input.appendChild(opt);
    });

    // Initialiser la valeur avec la valeur actuelle de la cellule
    input.value = cell.getValue() || "";

    // Focus sur l'input une fois rendu
    onRendered(function() {
        input.focus();
        input.style.height = "100%";
    });

    // Fonction de validation : ici, nous validons simplement en retournant la valeur de l'input
    function validateAndCommit() {
        success(input.value);
    }

    // Lors du blur, valider la saisie
    input.addEventListener("blur", function() {
        validateAndCommit();
    });

    // Intercepter la touche Entrée pour valider et naviguer vers "piece_justificative"
    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            validateAndCommit();

            // Aller à la cellule "piece_justificative"
            setTimeout(function() {
                const row = cell.getRow();
                const targetCell = row.getCell("piece_justificative");

                if (targetCell) {
                    const cellElement = targetCell.getElement();
                    const inputElement = cellElement.querySelector('input.selected-file-input');

                    if (inputElement) {
                        inputElement.focus();
                    } else {
                        // Si l'input n'est pas encore présent, déclencher le cellClick
                        cellElement.click();
                    }
                }
            }, 50);
        }
    });

    return input;
}
function customNumberEditor1(cell, onRendered, success, cancel) {
    const input = document.createElement("input");
    input.type = "number";
    input.style.width = "100%";
    input.placeholder = "jj";

    input.value = cell.getValue() || "";

    onRendered(function() {
        input.focus();
        input.style.height = "100%";
    });

    let isValidating = false; // Drapeau pour éviter les appels multiples

    function validateAndCommit() {
        if (isValidating) return; // Éviter les appels multiples
        isValidating = true; // Définir le drapeau

        const value = parseInt(input.value, 10); // Convertir la valeur en entier

        // Vérifier si la valeur est un nombre valide et entre 1 et 31
        if (isNaN(value) || value < 1 || value > 31) {
            alert("La valeur doit être un nombre entre 1 et 31.");
            isValidating = true; // Réinitialiser le drapeau
            return;
        }

        // Vérifier la longueur de la valeur
        if (input.value.length > 2) {
            alert("La valeur ne peut pas dépasser 2 chiffres.");
            isValidating = true; // Réinitialiser le drapeau
            return;
        }

        success(input.value);
        isValidating = false; // Réinitialiser le drapeau
    }

    input.addEventListener("blur", function() {
        validateAndCommit();
    });

    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            validateAndCommit();
            setTimeout(function() {
                // Vérifiez si la cellule suivante est différente avant de la focaliser
                const nextCell = focusNextEditableCell(cell);
                if (nextCell && nextCell !== cell) {
                    nextCell.focus(); // Focaliser la cellule suivante
                }
            }, 50);
        }
    });

    return input;
}
function customNumberEditor2(cell, onRendered, success, cancel) {
    const input = document.createElement("input");
    input.type = "date";
    input.style.width = "100%";
    input.value = cell.getValue() || "";

    onRendered(function () {
        input.focus();
        input.style.height = "100%";
    });

    function validateAndCommit() {
        success(input.value);

        // Aller à la cellule "piece_justificative" sur la même ligne
        setTimeout(() => {
            const row = cell.getRow();
            const nextCell = row.getCell("piece_justificative");
            
            if (nextCell) {
                const cellElement = nextCell.getElement();
                const inputInCell = cellElement.querySelector("input.selected-file-input");

                if (inputInCell) {
                    inputInCell.focus();
                    inputInCell.select(); // sélectionne le texte si besoin
                }
            }
        }, 10);
    }

    input.addEventListener("blur", function () {
        validateAndCommit();
    });

    input.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            e.stopPropagation();
            validateAndCommit();
        } else if (e.key === "Escape") {
            cancel();
        }
    });

    return input;
}
function customNumberEditor(cell, onRendered, success, cancel) {
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.value = cell.getValue() || "";

    onRendered(function() {
        input.focus();
        input.style.height = "100%";
    });

    function evalExpression(expr) {
        try {
            if (/^[0-9+\-*/().\s]+$/.test(expr)) {
                return Function('"use strict";return (' + expr + ')')();
            }
        } catch (e) {
            return null;
        }
        return null;
    }

    // Arrondi spécial à 2 chiffres après la virgule
    function customRound(value) {
        let str = value.toString();
        let [integer, decimal = ""] = str.split(".");
        decimal = decimal.padEnd(3, "0"); // s'assure d'avoir au moins 3 chiffres après la virgule

        let firstTwo = decimal.slice(0,2);
        let third = parseInt(decimal[2], 10);

        if(third > 5){
            firstTwo = (parseInt(firstTwo, 10) + 1).toString().padStart(2,"0");
        }

        return parseFloat(integer + "." + firstTwo);
    }

    function validateAndCommit() {
        const val = input.value.trim();
        const calc = evalExpression(val);
        if (calc !== null) {
            success(customRound(calc)); // <-- applique l'arrondi spécial
        } else {
            success(val);
        }
    }

    input.addEventListener("blur", function() {
        validateAndCommit();
    });

    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            validateAndCommit();
            setTimeout(function() {
                const nextCell = focusNextEditableCell(cell);
                if (nextCell && nextCell !== cell) nextCell.focus();
            }, 50);
        }
        if (e.key === "Escape") cancel();
    });

    return input;
}



function getSaisieChoisie() {
    return $('input[name="filter-Banque"]:checked').val(); // Récupérer la valeur du bouton radio sélectionné

}

function sendDataToController(data) {
  const selectedJournalCode = $('#journal-Banque').val();
  console.log("Code journal sélectionné :", selectedJournalCode);
  isSending = true;
  let completedRequests = 0;

  console.log("Données à envoyer :", data);

  data.forEach(row => {
    console.log(row.fact_lettrer);

    // Reformater la date au format YYYY-MM-DD
    let formattedDate = '';
    if (row.date) {
      const [day, month, year] = row.date.split('/');
      const monthIndex = new Date(Date.parse(month + " 1, 2020")).getMonth() + 1;
      formattedDate = `${year}-${monthIndex.toString().padStart(2, '0')}-${day.padStart(2, '0')}`;
    }

    // Récupérer valeur du compte
    let compteValue = '';
    if (row.compte) {
      const compteObj = planComptable.find(c => c.id == row.compte);
      compteValue = compteObj ? compteObj.compte : row.compte;
    }
    console.log('compte value:' + compteValue);

    // Traiter fact_lettrer : accepter tableau (ancienne logique) OU chaîne (édition via modal)
    let factLettrerString = '';
    if (row.fact_lettrer) {
      if (Array.isArray(row.fact_lettrer) && row.fact_lettrer.length > 0) {
        factLettrerString = row.fact_lettrer
          .map(item => {
            const [id, numero, montant, date] = item.split('|');
            return `${id}|${numero}|${montant}|${date}`;
          })
          .join(' & ');
      } else if (typeof row.fact_lettrer === 'string') {
        // Normalize string (remove extra spaces around & and trim parts)
        factLettrerString = row.fact_lettrer
          .split(/\s*&\s*/)
          .map(s => s.trim())
          .filter(Boolean)
          .map(item => {
            const parts = item.split('|').map(p => p.trim());
            if (parts.length === 4) return `${parts[0]}|${parts[1]}|${parts[2]}|${parts[3]}`;
            return item; // if unexpected format, keep as-is
          })
          .join(' & ');
      }
    }

    $.ajax({
      url: '/operation-courante-banque',
      method: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        date: formattedDate,
        numero_dossier: row.numero_dossier,
        fact_lettrer: factLettrerString,
        compte: compteValue,
        libelle: row.libelle,
        debit: row.debit,
        credit: row.credit,
        contre_partie: row.contre_partie,
        piece_justificative: row.piece_justificative,
        taux_ras_tva: row.taux_ras_tva,
        nature_op: row.nature_op,
        date_lettrage: formattedDate,
        mode_pay: row.mode_pay,
        type_journal: selectedJournalCode,
        saisie_choisie: getSaisieChoisie(),
        file_id: selectedFileId
      },
      success: function(response) {
        completedRequests++;
        if (completedRequests === data.length) {
          fetchOperations();
          isSending = false;
          setTimeout(function() {
            const rows = tableBanque.getRows();
            if (rows.length > 0) {
              const lastRow = rows[rows.length - 1];
              const dateCell = lastRow.getCell("date");
              if (dateCell && typeof dateCell.edit === 'function') {
                dateCell.edit();
              }
            }
          }, 300);
        }
      },
      error: function(xhr, status, error) {
        alert("Erreur lors de l'envoi des données :", error);
        console.error("Erreur AJAX :", xhr, status, error);
      }
    });
  });
}
function fetchOperations() {
    var selectedJournalCode = $('#journal-Banque').val();  
    var selectedMonth = $('#periode-Banque').val();  
    var selectedYear = $('#annee-Banque').val();  
    var selectedPeriod = $('input[name="filter-period-Banque"]:checked').val(); // ← vérifier période sélectionnée

    $.ajax({
        url: '/operation-courante-banque',
        method: 'GET',
        success: function(response) {
            if (response && response.length > 0) {
                console.log(response);

                var filteredOperations = response.filter(function(operation) {
                    var operationDate = new Date(operation.date);
                    var operationMonth = operationDate.getMonth() + 1;
                    var operationYear = operationDate.getFullYear();

                    // On filtre selon la période choisie
                    if (selectedPeriod === 'mois') {
                        return (
                            operation.type_journal === selectedJournalCode &&
                            operationMonth == selectedMonth
                        );
                    } else if (selectedPeriod === 'exercice') {
                        return (
                            operation.type_journal === selectedJournalCode &&
                            operationYear == selectedYear
                        );
                    }

                    return false; // Si aucune période valide
                });

                // Ajouter une ligne vide
                filteredOperations.push({
                    date: '',
                    mode_paiement: '',
                    compte: '',
                    libelle: '',
                    debit: '',
                    credit: '',
                    facture: '',
                    taux_ras_tva: '',
                    nature_operation: '',
                    date_lettrage: '',
                    contre_partie: $('#journal-Banque option:selected').data('contre-partie'),
                    piece_justificative: '',
                });

                allOperations = filteredOperations;
                tableBanque.setData(allOperations);
                updateFooter();
            } else {
                console.log("Aucune opération trouvée.");
                tableBanque.clearData();
            }
        },
        error: function() {
            console.log("Erreur lors de la récupération des opérations.");
        }
    });
}

function deleteOperations(ids) {
  $.ajax({
    url: '/operation-courante-caisse',
    method: 'DELETE',
    data: JSON.stringify({
      _token: $('meta[name="csrf-token"]').attr('content'),
      ids: ids
    }),
    contentType: 'application/json',
    success: function(response) {
      console.log("Opérations supprimées avec succès :", response);
      // Mettre à jour le tableau après la suppression
      fetchOperations(); // Récupérer à nouveau les opérations pour mettre à jour le tableau
   location.reload();
    },
    error: function(xhr, status, error) {
      console.error("Erreur lors de la suppression des opérations :", error);
      // alert("Erreur lors de la suppression des opérations.");
    }
  });
}
function printTable() {
    // Récupérer les données du tableau
    const tableData = tableBanque.getData();

    // Vérifier si le tableau contient des données
    if (tableData.length === 0) {
        alert("Aucune donnée à imprimer.");
        return;
    }

    // Créer une nouvelle fenêtre
    const printWindow = window.open('', '', 'height=600,width=800');
    
    // Construire le contenu HTML pour l'impression
    let html = `
        <html>
        <head>
            <title>Impression du tableau</title>
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th, td {
                    border: 1px solid black;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                }
            </style>
        </head>
        <body>
            <h2>Tableau des opérations</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Mode de paiement</th>
                        <th>Compte</th>
                        <th>Libellé</th>
                        <th>Débit</th>
                        <th>Cr édit</th>
                        <th>N° facture lettrée</th>
                        <th>Taux RAS TVA</th>
                        <th>Nature de l'opération</th>
                        <th>Date lettrage</th>
                        <th>Contre-Partie</th>
                        <th>Pièce justificative</th>
                    </tr>
                </thead>
                <tbody>
    `;

    // Remplir le corps du tableau avec les données
    tableData.forEach(row => {
        html += `
            <tr>
                <td>${row.date}</td>
                <td>${row.mode_paiement}</td>
                <td>${row.compte}</td>
                <td>${row.libelle}</td>
                <td>${row.debit}</td>
                <td>${row.credit}</td>
                <td>${row.facture}</td>
                <td>${row.taux_ras_tva}</td>
                <td>${row.nature_operation}</td>
                <td>${row.date_lettrage}</td>
                <td>${row.contre_partie}</td>
                <td>${row.piece_justificative}</td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </body>
        </html>
    `;

    // Écrire le contenu HTML dans la nouvelle fenêtre
    printWindow.document.write(html);
    printWindow.document.close(); // Fermer le document pour que le contenu soit rendu
    printWindow.print(); // Lancer l'impression
    printWindow.close(); // Fermer la fenêtre après l'impression
}
function exportToExcel() {
    // Récupérer les données du tableau
    const tableData = tableBanque.getData();
    
    // Créer un nouveau classeur
    const wb = XLSX.utils.book_new();
    
    // Convertir les données en feuille de calcul
    const ws = XLSX.utils.json_to_sheet(tableData);
    
    // Ajouter la feuille de calcul au classeur
    XLSX.utils.book_append_sheet(wb, ws, "Banque");
    
    // Exporter le classeur
    XLSX.writeFile(wb, "Banque_data.xlsx");
}
function exportToPDF() {
    const { jsPDF } = window.jspdf; // Accéder à jsPDF via l'espace de noms
    const doc = new jsPDF('l', 'mm', 'a4'); // 'l' pour paysage, 'mm' pour millimètres, 'a4' pour le format A4
    const tableData = tableBanque.getData();

    // Vérifiez si tableData est vide
    if (tableData.length === 0) {
        alert("Aucune donnée à exporter.");
        return;
    }

    const pdfTableData = tableData.map(row => [
        row.date,
        row.mode_paiement,
        row.compte,
        row.libelle,
        row.debit,
        row.credit,
        row.facture,
        row.taux_ras_tva,
        row.nature_operation,
        row.date_lettrage,
        row.contre_partie,
        row.piece_justificative
    ]);

    const headers = [
        "Date", "Mode de paiement", "Compte", "Libellé", 
        "Débit", "Crédit", "N° facture lettrée", "Taux RAS TVA",
        "Nature de l'opération", "Date lettrage", "Contre-Partie", "Pièce justificative"
    ];

    doc.autoTable({
        head: [headers],
        body: pdfTableData,
    });

    doc.save("Banque_data.pdf");
}
function updateFooter() {
    const table = window.tableBanque || (typeof tableBanque !== 'undefined' ? tableBanque : null);
    if (!table) {
        console.warn('updateFooterCaisse: table Caisse introuvable');
        return;
    }

    // Récupérer les données de façon sûre
    const data = (typeof table.getData === 'function') ? table.getData() : (Array.isArray(table) ? table : []);

    // récupérer la contre-partie du journal sélectionné (normalisée)
    let contrePartieJournal = '';
    try {
        contrePartieJournal = $('#journal-Banque option:selected').data('contre-partie') ?? '';
        contrePartieJournal = String(contrePartieJournal).trim();
    } catch (e) {
        contrePartieJournal = '';
    }

    // récupérer le code du journal sélectionné (si disponible)
    let codeJournal = '';
    try {
        codeJournal = String($('#journal-Banque').val() || '').trim();
    } catch (e) {
        codeJournal = '';
    }

    let cumulDebit = 0;
    let cumulCredit = 0;

    data.forEach(row => {
        const compteRow = (row.compte !== undefined && row.compte !== null) ? String(row.compte).trim() : '';
        if (contrePartieJournal && compteRow === contrePartieJournal) return;
        cumulDebit += parseFloat(row.debit) || 0;
        cumulCredit += parseFloat(row.credit) || 0;
    });

    // Fonction utilitaire pour remplir un champ (span ou input)
    const setFieldValue = (id, val, isInput = false) => {
        const el = document.getElementById(id);
        if (!el) return;
        const formatted = isNaN(val) ? "0.00" : Number(val).toFixed(2);
        if (isInput) {
            el.value = formatted;
        } else {
            el.innerText = formatted;
        }
    };

    // Appeler l'API pour récupérer les soldes initiaux
    $.ajax({
        url: '/api/solde-initial',
        method: 'GET',
        dataType: 'json',
        data: {
            code_journal: codeJournal || '',
            contre_partie: contrePartieJournal || ''
        },
        success: function (response) {
            const soldeInitialDB = parseFloat(response.solde_initial_db) || 0;
            const soldeInitialCR = parseFloat(response.solde_initial_cr) || 0;

            // Calcul du solde actuel
            const soldeInitialEffectif = soldeInitialDB - soldeInitialCR;
            const soldeActuel = soldeInitialEffectif + cumulCredit - cumulDebit;

            const soldeDebiteur = soldeActuel > 0 ? soldeActuel : 0;
            const soldeCrediteur = soldeActuel < 0 ? Math.abs(soldeActuel) : 0;

            // Remplir les champs visibles (span ou td)
            setFieldValue('cumul-debit', cumulDebit);
            setFieldValue('cumul-credit', cumulCredit);
            setFieldValue('solde-initial-db', soldeInitialDB);
            setFieldValue('solde-initial-cr', soldeInitialCR);
            setFieldValue('solde-debiteur', soldeDebiteur);
            setFieldValue('solde-crediteur', soldeCrediteur);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération du solde initial.', textStatus, errorThrown);
        }
    });

    // Récupérer le solde actuel spécifique pour l'input
    const codeJournal01 = String($('#journal-Caisse').val() || '').trim();
    const contrePartieJournal01 = $('#journal-Caisse option:selected').data('contre-partie') ?? '';
    const contrePartieJournalTrim = String(contrePartieJournal01).trim();

    $.ajax({
        url: '/soldeActuel',
        method: 'GET',
        dataType: 'json',
        data: {
            code_journal: codeJournal01,
            contre_partie: contrePartieJournalTrim
        },
        success: function (response) {
            const soldeActuel = parseFloat(response.soldeActuel) || 0;
            // Ici true signifie que c'est un input
            setFieldValue('solde-actuel', soldeActuel, true);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération du solde actuel.', textStatus, errorThrown);
        }
    });
}



function updateFooterCaisse() {
    const table = window.tableCaissePrincipale || window.tableCaisse || (typeof tableCaisse !== 'undefined' ? tableCaisse : null);
    if (!table) {
        console.warn('updateFooterCaisse: table Caisse introuvable');
        return;
    }

    // Récupérer les données de façon sûre
    const data = (typeof table.getData === 'function') ? table.getData() : (Array.isArray(table) ? table : []);

    // récupérer la contre-partie du journal sélectionné (normalisée)
    let contrePartieJournal = '';
    try {
        contrePartieJournal = $('#journal-Caisse option:selected').data('contre-partie') ?? '';
        contrePartieJournal = String(contrePartieJournal).trim();
    } catch (e) {
        contrePartieJournal = '';
    }

    // récupérer le code du journal sélectionné (si disponible)
    let codeJournal = '';
    try {
        codeJournal = String($('#journal-Caisse').val() || '').trim();
    } catch (e) {
        codeJournal = '';
    }

    let cumulDebit = 0;
    let cumulCredit = 0;

    data.forEach(row => {
        const compteRow = (row.compte !== undefined && row.compte !== null) ? String(row.compte).trim() : '';
        if (contrePartieJournal && compteRow === contrePartieJournal) return;
        cumulDebit += parseFloat(row.debit) || 0;
        cumulCredit += parseFloat(row.credit) || 0;
    });

    // Fonction utilitaire pour remplir un champ (span/td ou input)
    const setFieldValue = (id, val, isInput = false) => {
        const el = document.getElementById(id);
        if (!el) return;
        const formatted = isNaN(val) ? "0.00" : Number(val).toFixed(2);
        if (isInput) {
            el.value = formatted;
        } else {
            el.innerText = formatted;
        }
    };

    // Appeler l'API pour récupérer les soldes initiaux
    $.ajax({
        url: '/api/solde-initial',
        method: 'GET',
        dataType: 'json',
        data: {
            code_journal: codeJournal || '',
            contre_partie: contrePartieJournal || ''
        },
        success: function (response) {
            const soldeInitialDB = parseFloat(response.solde_initial_db) || 0;
            const soldeInitialCR = parseFloat(response.solde_initial_cr) || 0;

            // Calcul du solde actuel
            const soldeInitialEffectif = soldeInitialDB - soldeInitialCR;
            const soldeActuel = soldeInitialEffectif + cumulCredit - cumulDebit;

            const soldeDebiteur = soldeActuel > 0 ? soldeActuel : 0;
            const soldeCrediteur = soldeActuel < 0 ? Math.abs(soldeActuel) : 0;

            // Remplir les champs visibles (span ou td)
            setFieldValue('cumul-debit', cumulDebit);
            setFieldValue('cumul-credit', cumulCredit);
            setFieldValue('solde-initial-db', soldeInitialDB);
            setFieldValue('solde-initial-cr', soldeInitialCR);
            setFieldValue('solde-debiteur', soldeDebiteur);
            setFieldValue('solde-crediteur', soldeCrediteur);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération du solde initial.', textStatus, errorThrown);
        }
    });

    // Récupérer le solde actuel spécifique pour l'input
    const codeJournal01 = String($('#journal-Caisse').val() || '').trim();
    const contrePartieJournal01 = $('#journal-Caisse option:selected').data('contre-partie') ?? '';
    const contrePartieJournalTrim = String(contrePartieJournal01).trim();

    $.ajax({
        url: '/soldeActuel',
        method: 'GET',
        dataType: 'json',
        data: {
            code_journal: codeJournal01,
            contre_partie: contrePartieJournalTrim
        },
        success: function (response) {
            const soldeActuel = parseFloat(response.soldeActuel) || 0;
            // Ici true signifie que c'est un input
            setFieldValue('solde-actuel-Caisse', soldeActuel, true);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération du solde actuel.', textStatus, errorThrown);
        }
    });
}


function viewReleveBancaire(mois, annee) {
    const codeJournalSession = $('#journal-Banque').val();
    const moisSession = $('#periode-Banque').val();
    const anneeSession = new Date($('#exercice-date').data('exercice-date')).getFullYear();

    const url = `/releve-bancaire/view?mois=${moisSession}&annee=${anneeSession}&code_journal=${codeJournalSession}`;
   console.log(url);
    window.open(url, '_blank'); // Ouvre dans un nouvel onglet
}
function viewFile(fileUrl) {
    if (fileUrl) {
        window.open(fileUrl, '_blank');
    } else {
        alert("Aucun fichier disponible.");
    }
    
}
 
// Actions Caisse

// Functions Caisse


function fetchOperationsCaisse() {
    var selectedJournalCode = $('#journal-Caisse').val();  
    var selectedMonth = $('#periode-Caisse').val();  
    var selectedYear = $('#annee-Caisse').val();
    var selectedPeriod = $('input[name="filter-period-Caisse"]:checked').val();

    var contrePartie = $('#journal-Caisse option:selected').data('contre-partie');

    $.ajax({
        url: '/operation-courante-caisse',
        method: 'GET',
        success: function(response) {
            // Vérifier si le serveur renvoie un message "Aucune donnée"
            if (response.message && response.message.includes("Aucune donnée")) {
                console.log("Aucune opération trouvée pour cette période.");
                tableCaissePrincipale.clearData();
                return; // On sort de la fonction, pas d'erreur
            }

            // Vérifier si response contient un tableau d'opérations
            if (response && response.length > 0) {
                var filteredOperations = response.filter(function(operation) {
                    var operationDate = new Date(operation.date);
                    var operationMonth = operationDate.getMonth() + 1;
                    var operationYear = operationDate.getFullYear();

                    if (selectedPeriod === 'mois') {
                        return (
                            operation.type_journal === selectedJournalCode &&
                            operationMonth == selectedMonth
                        );
                    } else if (selectedPeriod === 'exercice') {
                        return (
                            operation.type_journal === selectedJournalCode &&
                            operationYear == selectedYear
                        );
                    }
                    return false;
                });

                // Ajouter une ligne vide
                filteredOperations.push({
                    date: '',
                    mode_paiement: '',
                    compte: '',
                    libelle: '',
                    debit: '',
                    credit: '',
                    facture: '',
                    taux_ras_tva: '',
                    nature_operation: '',
                    date_lettrage: '',
                    contre_partie: contrePartie,
                    piece_justificative: '',
                });

                allOperations = filteredOperations;
                tableCaissePrincipale.setData(allOperations);
                updateFooterCaisse();
            } else {
                console.log("Aucune opération filtrée pour cette période.");
                tableCaissePrincipale.clearData();
            }
        },
        error: function(xhr, status, error) {
            // Afficher une vraie erreur uniquement si la requête échoue
            console.log("Erreur Ajax :", status, error);
            console.log("Réponse du serveur :", xhr.responseText);
        }
    });
}

function getSaisieChoisieCaisse() {
    return $('input[name="filter-Caisse"]:checked').val(); // Récupérer la valeur du bouton radio sélectionné

}

function sendDataToControllerCaisse(data) {
    console.log('arrrrive2');
    const selectedJournalCode = $('#journal-Caisse').val();
    console.log("Code journal sélectionné :", selectedJournalCode);
    isSending = true;
    let completedRequests = 0;

    console.log("Données à envoyer (Caisse) :", data);

    data.forEach(row => {
        let formattedDate = '';
        if (row.date) {
            const [day, month, year] = row.date.split('/');
            const monthIndex = new Date(Date.parse(month + " 1, 2020")).getMonth() + 1;
            formattedDate = `${year}-${monthIndex.toString().padStart(2, '0')}-${day.padStart(2, '0')}`;
        }

        let compteValue = '';
        if (row.compte) {
            const compteObj = planComptable.find(c => c.id == row.compte || c.compte == row.compte);
            compteValue = compteObj ? compteObj.compte : row.compte;
        }

        $.ajax({
            url: '/operation-courante-caisse-store',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                date: formattedDate,
                compte: compteValue,
                libelle: row.libelle,
                debit: row.debit,
                credit: row.credit,
                facture: row.fact_lettrer,
                taux_ras_tva: row.taux_ras_tva,
                nature_op: row.nature_op,
                date_lettrage: row.date_lettrage,
                contre_partie: row.contre_partie,
                piece_justificative: row.piece_justificative,
                mode_pay: 'espèce',
                type_journal: selectedJournalCode,
                saisie_choisie: getSaisieChoisieCaisse(),
                file_id: selectedFileId
            },
            success: function (response) {
                completedRequests++;
                if (completedRequests === data.length) {
                    fetchOperationsCaisse(); // Recharge les opérations
                    isSending = false;

                    setTimeout(() => {
                        if (!window.tableCaissePrincipale) {
                            console.warn("Impossible de focus une ligne : tableCaissePrincipale est undefined");
                            return;
                        }

                        const rows = window.tableCaissePrincipale.getRows();
                        if (rows.length > 0) {
                            const lastRow = rows[rows.length - 1];
                            const dateCell = lastRow.getCell("date");
                            if (dateCell && typeof dateCell.edit === 'function') {
                                dateCell.edit(); // Focus automatique sur la cellule "date"
                            }
                        }
                    }, 300);
                }
            },
            error: function (xhr, status, error) {
                alert("Erreur lors de l'envoi des données caisse : " + error);
                console.error("Erreur AJAX :", xhr, status, error);
            }
        });
    });
}

// function customDateEditorCaisse(cell, onRendered, success, cancel) {
//     const input = document.createElement("input");
//     input.type = "text";
//     input.style.width = "100%";
//     input.placeholder = "Jour/Mois";

//     // Utiliser l'année extraite
//     const exerciceDate = document.getElementById('exercice-date').getAttribute('data-exercice-date');
//     const exerciceYear = new Date(exerciceDate).getFullYear(); // Extraire l'année

//     const selectedPeriod = $('input[name="filter-period-Caisse"]:checked').val(); // Vérifier la période sélectionnée

//     // Pré-remplir la valeur si elle existe
//     const currentValue = cell.getValue() || "";
//     const [currentDay, currentMonth, currentYear] = currentValue.split("/");

//     if (selectedPeriod === "mois") {
//         // Si "mois" est sélectionné, l'utilisateur entre uniquement le jour
//         input.placeholder = "Jour";
//         input.value = currentDay || "";
//     } else if (selectedPeriod === "exercice") {
//         // Si "exercice" est sélectionné, l'utilisateur entre le jour et le mois
//         input.placeholder = "Jour/Mois";
//         input.value = currentDay && currentMonth ? `${currentDay}/${currentMonth}` : "";
//     }

//     onRendered(() => {
//         input.focus();
//     });

//     input.addEventListener("blur", () => {
//         let value = input.value;

//         if (selectedPeriod === "mois") {
//             // Ajouter le mois sélectionné et l'année de l'exercice
//             const selectedMonth = $('#periode-Caisse').val();
//             if (selectedMonth) {
//                 value = `${value}/${selectedMonth}/${exerciceYear}`;
//             }
//         } else if (selectedPeriod === "exercice") {
//             // Ajouter uniquement l'année de l'exercice
//             const [day, month] = value.split("/");
//             if (day && month) {
//                 value = `${day}/${month}/${exerciceYear}`;
//             }
//         }

//         success(value);
//     });

//     input.addEventListener("keydown", (e) => {
//         if (e.key === "Enter") {
//             e.preventDefault();
//             input.blur();
//                 // Générer le numéro de pièce justificative après saisie de la date
//                 setTimeout(() => {
//                     const row = cell.getRow();
//                     function generatePieceJustificativeNum(row) {
//                         const date = row.getCell("date").getValue();
//                         let jour = "";
//                         let annee = "";
//                         if (date) {
//                             let match = date.match(/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/); // JJ/MM/AAAA
//                             if (match) {
//                                 jour = match[1];
//                                 annee = match[3];
//                             } else {
//                                 match = date.match(/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})$/); // AAAA/MM/JJ
//                                 if (match) {
//                                     jour = match[3];
//                                     annee = match[1];
//                                 } else {
//                                     let d = new Date(date);
//                                     if (!isNaN(d)) {
//                                         jour = d.getDate().toString().padStart(2, '0');
//                                         annee = d.getFullYear().toString();
//                                     }
//                                 }
//                             }
//                         }
//                         // Chercher le plus grand numéro sur les 4 derniers chiffres
//                         let rows = tableBanque.getRows();
//                         let maxNum = 0;
//                         rows.forEach(r => {
//                             let val = r.getCell("piece_justificative").getValue();
//                             if (val && val.length >= 4) {
//                                 let last4 = val.slice(-4);
//                                 let num = parseInt(last4, 10);
//                                 if (!isNaN(num) && num > maxNum) maxNum = num;
//                             }
//                         });
//                         let nextNum = (maxNum + 1).toString().padStart(4, '0');
//                         const codeJournal = $("#journal-Banque").val() || "J";
//                         return `p${jour}${annee}${codeJournal}${nextNum}`;
//                     }
//                     let pieceCell = row.getCell("piece_justificative");
//                     if (pieceCell && !pieceCell.getValue()) {
//                         pieceCell.setValue(generatePieceJustificativeNum(row));
//                     }
//                 }, 50);
//         }
//     });

//     return input;
// }
function customDateEditorCaisse(cell, onRendered, success, cancel) {
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";

    // Année de l'exercice
    const exerciceDateCaisse = document
        .getElementById('exercice-date')
        .getAttribute('data-exercice-date');
    const exerciceYearCaisse = new Date(exerciceDateCaisse).getFullYear();

    const selectedPeriod = $('input[name="filter-period-Caisse"]:checked').val();

    const currentValue = cell.getValue() || "";
    const [currentDay, currentMonth] = currentValue.split("/");

    /* ================================
       INITIALISATION
       ================================ */
    if (selectedPeriod === "mois") {
        input.placeholder = "Jour";
        input.value = currentDay || "";
    } 
    else if (selectedPeriod === "exercice") {
        input.placeholder = "JJ/MM";
        input.value = currentDay && currentMonth
            ? `${currentDay}/${currentMonth}`
            : "JJ/MM";
    }

    onRendered(() => input.focus());

    /* ================================
       SAISIE JJ/MM AVEC JJ & MM VISIBLES
       ================================ */
    input.addEventListener("input", () => {
        if (selectedPeriod !== "exercice") return;

        let raw = input.value.replace(/\D/g, "").slice(0,4);
        let day = "JJ", month = "MM";

        if (raw.length === 1) day = raw[0] + "J";
        if (raw.length >= 2) day = raw.slice(0, 2);
        if (raw.length === 3) month = raw[2] + "M";
        if (raw.length >= 4) month = raw.slice(2, 4);

        input.value = `${day}/${month}`;
    });

    /* ================================
       BLUR (ajout année)
       ================================ */
    input.addEventListener("blur", () => {
        let value = input.value;

        if (selectedPeriod === "mois") {
            const selectedMonth = $('#periode-Caisse').val();
            if (selectedMonth && value) {
                const day = value.padStart(2, "0");
                const month = selectedMonth.toString().padStart(2, "0");
                value = `${day}/${month}/${exerciceYearCaisse}`;
            }
        } 
        else if (selectedPeriod === "exercice") {
            const [day, month] = value.split("/");
            if (!day.includes("J") && !month.includes("M")) {
                value = `${day.padStart(2, "0")}/${month.padStart(2, "0")}/${exerciceYearCaisse}`;
            } else {
                value = "";
            }
        }

        success(value);
    });

    /* ================================
       ENTER → PIÈCE JUSTIFICATIVE
       ================================ */
    input.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            input.blur();

            setTimeout(() => {
                const row = cell.getRow();

                function generatePieceJustificativeNum(row) {
                    const date = row.getCell("date").getValue();
                    let jour = "", annee = "";

                    if (date) {
                        let match = date.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
                        if (match) {
                            jour = match[1].padStart(2, "0");
                            annee = match[3];
                        }
                    }

                    let rows = tableCaissePrincipale.getRows();
                    let maxNum = 0;
                    rows.forEach(r => {
                        let val = r.getCell("piece_justificative").getValue();
                        if (val && val.length >= 4) {
                            let num = parseInt(val.slice(-4), 10);
                            if (!isNaN(num) && num > maxNum) maxNum = num;
                        }
                    });

                    let nextNum = (maxNum + 1).toString().padStart(4, '0');
                    const codeJournal = ($("#journal-Caisse").val() || "J").replace(/\s+/g, "");

                    return `P${jour}${annee}${codeJournal}${nextNum}`;
                }

                let pieceCell = row.getCell("piece_justificative");
                if (pieceCell && !pieceCell.getValue()) {
                    pieceCell.setValue(generatePieceJustificativeNum(row));
                }
            }, 50);
        }
    });

    return input;
}
  

function exportCaisseToExcel() {
    const table = window.tableCaissePrincipale;
    if (!table) {
        alert("Le tableau Caisse n'est pas encore initialisé.");
        return;
    }
    const data = (table.getData() || []).map(row => {
        // normaliser champs complexes avant export
        return Object.assign({}, row, {
            fact_lettrer: Array.isArray(row.fact_lettrer) ? row.fact_lettrer.join(' & ') : (row.fact_lettrer || ''),
            piece_justificative: row.piece_justificative || '',
        });
    });

    try {
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.json_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Caisse");
        XLSX.writeFile(wb, "Caisse_data.xlsx");
    } catch (err) {
        console.error("Erreur export Excel Caisse:", err);
        alert("Erreur lors de l'export Excel (voir console).");
    }
}
function exportCaisseToPDF() {
    const table = window.tableCaissePrincipale;
    if (!table) {
        alert("Le tableau Caisse n'est pas encore initialisé.");
        return;
    }
    const data = table.getData() || [];

    if (!data.length) {
        alert("Aucune donnée à exporter.");
        return;
    }

    try {
        const { jsPDF } = window.jspdf || {};
        if (!jsPDF) {
            alert("jsPDF non chargé.");
            return;
        }
        const doc = new jsPDF('l', 'mm', 'a4');

        const headers = [
            "Date", "Mode paiement", "Compte", "Libellé",
            "Débit", "Crédit", "N° facture lettrée", "Taux RAS TVA",
            "Nature op", "Date lettrage", "Contre-Partie", "Pièce justificative"
        ];

        const body = data.map(row => [
            row.date || "",
            row.mode_paiement || row.mode_pay || "",
            String(row.compte || ""),
            row.libelle || "",
            (row.debit != null) ? row.debit : "",
            (row.credit != null) ? row.credit : "",
            Array.isArray(row.fact_lettrer) ? row.fact_lettrer.join(' & ') : (row.fact_lettrer || ""),
            row.taux_ras_tva || row.taux || "",
            row.nature_op || row.nature_operation || "",
            row.date_lettrage || "",
            row.contre_partie || "",
            row.piece_justificative || ""
        ]);

        // autoTable
        if (typeof doc.autoTable === 'function') {
            doc.autoTable({ head: [headers], body: body, styles: { fontSize: 8 } });
            doc.save("Caisse_data.pdf");
        } else {
            // fallback simple: write text if autoTable indisponible
            doc.setFontSize(10);
            doc.text("Export Caisse (tableau) - autoTable non disponible", 10, 10);
            doc.save("Caisse_data.pdf");
        }
    } catch (err) {
        console.error("Erreur export PDF Caisse:", err);
        alert("Erreur lors de l'export PDF (voir console).");
    }
}


// function viewl_etat_de_caisse(mois, annee) {
//     const codeJournalSession = $('#journal-Caisse').val();
//     const moisSession = $('#periode-Caisse').val();
//     const anneeSession = new Date($('#exercice-date').data('exercice-date')).getFullYear();

//     const url = `/etat-de-caisse/view?mois=${moisSession}&annee=${anneeSession}&code_journal=${codeJournalSession}`;
//     window.open(url, '_blank'); // Ouvre dans un nouvel onglet
// }
function viewl_etat_de_caisse() {
    const codeJournalSession = $('#journal-Caisse').val();
    const moisSession = $('#periode-Caisse').val();
    const anneeSession = new Date($('#exercice-date').data('exercice-date')).getFullYear();

    const params = {
        code_journal: codeJournalSession,
        mois: moisSession,
        annee: anneeSession,
    };

    $.ajax({
        url: '/etat-de-caisse/view',
        type: 'GET',
        data: params,
        success: function(response) {
            if(response.success) {
                generatePDF(response.transactions, response.soldeInitial, response.soldeFinal, codeJournalSession, moisSession, anneeSession);
            } else {
                alert('Aucune donnée trouvée pour ces critères.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur lors de la récupération des données :', error);
        }
    });
}

// Fonction pour générer le PDF
function generatePDF(transactions, soldeInitial, soldeFinal, codeJournal, mois, annee) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // ─── Infos société depuis le DOM ───────────────────────────────────────────
    const societeRaisonSociale     = $('#session-data').data('societe-raison_sociale') || "";
    const societeFormeJuridique    = $('#session-data1').data('societe-forme_juridique') || "";
    const societeIdentifiantFiscal = $('#session-data2').data('societe-identifiant_fiscal') || "";

    const moisText = $("#periode-Caisse option:selected").text();
    const periodeText = `${moisText} ${annee}`;

    // ─── 1) En-tête bleu ─────────────────────────────────────────────────────
    const pageWidth  = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    doc.setFillColor(41, 128, 185);
    doc.rect(0, 0, pageWidth, 30, 'F');

    doc.setFontSize(9).setTextColor(255).setFont("helvetica", "bold");
    doc.text(`${societeRaisonSociale} ${societeFormeJuridique}`, 10, 10);

    doc.setFontSize(9).setFont("helvetica", "normal");
    doc.text(`IF : ${societeIdentifiantFiscal}`, 10, 16);

    const title = "État de Caisse Mensuel";
    doc.setFontSize(14).setFont("helvetica", "bold");
    const titleWidth = doc.getTextWidth(title);
    doc.text(title, (pageWidth - titleWidth) / 2, 16);

    const yHeader = 26;
    const third = pageWidth / 3;
    doc.setFontSize(10).setTextColor(255);
    doc.text(`Code : ${codeJournal}`, 10, yHeader);
    doc.text(`Intitulé : ${societeRaisonSociale}`, third + 10, yHeader);
    doc.text(`Période : ${periodeText}`, 2 * third + 10, yHeader);

    // ─── 2) Solde initial ───────────────────────────────────────────────────
    const ySoldeInitial = 36;
    doc.setFontSize(11).setTextColor(0);
    doc.text(`Solde initial : ${parseFloat(soldeInitial).toFixed(2)} MAD`, pageWidth - 69, ySoldeInitial, { align: "right" });

    // ─── 3) Préparer les données du tableau ─────────────────────────────────
    let totalRecette = 0;
    let totalDepense = 0;
    const rows = transactions.map(trx => {
        const rc = trx.recette ? parseFloat(trx.recette) : 0;
        const dp = trx.depense ? parseFloat(trx.depense) : 0;
        totalRecette += rc;
        totalDepense += dp;
        return [
            new Date(trx.date).getDate(), // Jour
            trx.libelle || "",
            rc.toFixed(2),
            dp.toFixed(2),
            trx.reference || ""
        ];
    });

    // Ligne TOTAL
    rows.push([
        { content: "TOTAL", colSpan: 2, styles: { halign: "right", fontStyle: "bold" } },
        { content: totalRecette.toFixed(2), styles: { fontStyle: "bold" } },
        { content: totalDepense.toFixed(2), styles: { fontStyle: "bold" } },
        ""
    ]);

    // ─── 4) Tableau autoTable ────────────────────────────────────────────────
    doc.autoTable({
        startY: 40,
        head: [["Jour", "Libellé", "Recette", "Dépense", "N° Pièce"]],
        body: rows,
        styles: { fontSize: 10, halign: "center", cellPadding: 2 },
        headStyles: { fillColor: [41,128,185], textColor: 255, fontStyle: "bold" },
        alternateRowStyles: { fillColor: [245,245,245] },
        columnStyles: { 0:{cellWidth:14}, 1:{cellWidth:80}, 2:{cellWidth:25}, 3:{cellWidth:25}, 4:{cellWidth:35} },
        didDrawPage: function(data) {
            const pageNumber = doc.internal.getCurrentPageInfo().pageNumber;
            doc.setFontSize(9).setTextColor(150);
            doc.text(`Page ${pageNumber}`, pageWidth - 20, pageHeight - 10);
        }
    });

    // ─── 5) Solde final ─────────────────────────────────────────────────────
    const soldeFinalY = doc.lastAutoTable.finalY + 6;
    doc.setFontSize(11).setTextColor(0);
    doc.text(`Solde final : ${parseFloat(soldeFinal).toFixed(2)} MAD`, pageWidth - 69, soldeFinalY, { align: "right" });

    // ─── 6) Clôturé le & Fait par ───────────────────────────────────────────
    const xFooter = pageWidth - 14;
    const yFooterClos = soldeFinalY + 14;
    const yFooterFait = yFooterClos + 6;
    const now = new Date();
    const closureDateFormatted = now.toLocaleDateString('fr-FR');
    doc.setTextColor(0);
    doc.text(`Clôturé le : ${closureDateFormatted}`, xFooter, yFooterClos, { align: "right" });
    doc.setFontSize(10);
    doc.text(`Fait par : ${transactions.length ? transactions[0].updated_by : "Inconnu"}`, xFooter, yFooterFait, { align: "right" });

    // ─── 7) Sauvegarder PDF ────────────────────────────────────────────────
    doc.save(`etat_caisse_${mois}_${annee}.pdf`);
}



 