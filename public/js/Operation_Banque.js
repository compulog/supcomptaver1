var tableBanque;
let isSending = false;
let selectedFileId = null;
let currentPieceCellBanque = null;
const fileIds = [];


fetchOperations();


// importation pour Banque
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
     
  } catch (error) {
    alert('Erreur réseau ou serveur : ' + error.message);
  }
});
document.querySelectorAll('#file-card').forEach(function(card) {
    card.addEventListener('dblclick', function () {
        // ✅ Affecte à la variable globale
        selectedFileId = this.dataset.fileid;
        const selectedFilePath = this.dataset.filepath;

        console.log("ID du fichier sélectionné :", selectedFileId);
        console.log("Chemin du fichier sélectionné :", selectedFilePath);

        $('#files_banque_Modal').hide();

        if (currentPieceCellBanque) {
            const cellElement = currentPieceCellBanque.getElement();
            let viewIcon = cellElement.querySelector('.fas.fa-eye.view-icon');

            // Si l'icône n'existe pas, on la crée et on l'ajoute
            if (!viewIcon) {
                console.warn("L'icône œil n'a pas été trouvée dans la ligne. Elle va être ajoutée.");

                viewIcon = document.createElement('i');
                viewIcon.className = 'fas fa-eye view-icon';
                viewIcon.style.cursor = 'pointer';

                // Ajoute l'icône dans la cellule (à la fin, ou adapte selon structure HTML)
                cellElement.appendChild(viewIcon);
            }

            // Rendre l'icône focusable et visible pour l'utilisateur
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

            // Ajoute un effet visuel temporaire
            viewIcon.style.outline = '2px solid #333';
            viewIcon.style.outlineOffset = '2px';

            setTimeout(() => {
                viewIcon.removeAttribute('tabindex');
                viewIcon.style.outline = '';
                viewIcon.style.outlineOffset = '';
            }, 2000);

            // ✅ Enregistrer l'ID du fichier sélectionné dans un tableau (optionnel)
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
    
    // Fonction pour gérer le changement de période
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
    });

    // Initialiser la période au chargement de la page (si le radio 'Mois' est sélectionné par défaut)
    if ($('input[name="filter-period-Banque"]:checked').val() === 'mois') {
        $('#periode-Banque').show();
        $('#annee-Banque').hide();
    } else if ($('input[name="filter-period-Banque"]:checked').val() === 'exercice') {
        $('#periode-Banque').hide();
        $('#annee-Banque').show().val(exerciceYear);
    }
    
    $.ajax({
    url: '/journaux-Banque', // Assurez-vous que l'URL correspond à la route Laravel
    method: 'GET',
    success: function(response) {
        // Vider les options existantes avant d'ajouter de nouvelles options
        $('#journal-Banque').empty();

        // Vérifier s'il y a des journaux
        if (response && response.length > 0) {
            // Ajouter les options dans le select
            response.forEach(function(journal) {
                $('#journal-Banque').append(
                    $('<option>', {
                        value: journal.code_journal,
                        text: journal.code_journal, // Utiliser l'intitulé pour l'affichage
                        'data-intitule': journal.intitule,
                            'data-contre-partie': journal.contre_partie

                    })
                );
            });
        } else {
            console.log("Aucun journal trouvé.");
        }
    },
    error: function() {
        console.log("Erreur lors de la récupération des journaux.");
    }
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
        var modePaiement = row.getCell("mode_pay").getValue();
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
                { title: "N° facture lettrée",
                field: "fact_lettrer",
                width: 200,
                headerFilter: "input",

                formatter: function(cell) {
                    const value = cell.getValue();
                    if (Array.isArray(value)) {
                    return value.map(item => {
                        const [id, numero, montant, date] = item.split('|');
                        return `${numero} / ${montant} / ${date}`;
                    }).join(", ");
                    }
                    return value || "";
                },

                editor: function(cell, onRendered, success, cancel) {
                    const select = document.createElement("select");
                    select.style.width = "350px";
                    select.multiple = true;

                    const row = cell.getRow();
                    const compte = row.getCell("compte").getValue();
                    const debit = row.getCell("debit").getValue();
                    const credit = row.getCell("credit").getValue();

                    if (!debit && !credit) {
                    alert("Veuillez remplir une valeur de débit ou crédit.");
                    cancel();
                    return select;
                    }

                    const existingValues = cell.getValue() || [];

                    $.ajax({
                    url: `/get-nfacturelettree?debit=${encodeURIComponent(debit)}&credit=${encodeURIComponent(credit)}&compte=${encodeURIComponent(compte)}`,
                    method: 'GET',
                    success: function(response) {
                        response.forEach(item => {
                        const montant = item.debit != null ? item.debit : item.credit;
                        const valeur = `${item.id}|${item.numero_facture}|${montant}|${item.date}`;

                        const option = new Option(
                            `${item.numero_facture} / ${montant} / ${item.date}`,
                            valeur,
                            existingValues.includes(valeur),
                            existingValues.includes(valeur)
                        );
                        select.appendChild(option);
                        });

                        $(select).select2({
                        placeholder: "-- Sélectionnez une ou plusieurs factures --",
                        closeOnSelect: false,
                        width: '350px',
                        });

                        $(select).select2('open');
                    },
                    error: function(error) {
                        console.error("Erreur AJAX :", error);
                    }
                    });

                    select.addEventListener("change", () => {
                    const selectedValues = $(select).val() ?? [];
                    cell.setValue(selectedValues);
                    });

            onRendered(() => {
                $(select).on('select2:close', () => {
                    const selectedValues = $(select).val() ?? [];
                    const rowComponent = cell.getRow();
                    rowComponent.update({ fact_lettrer: selectedValues });

                    success(selectedValues);

                    const rowElement = rowComponent.getElement();
                    setTimeout(() => {
                        const fileInput = rowElement.querySelector("#selectedFile");
                        if (fileInput) fileInput.focus();
                    }, 50);
                });

                select.addEventListener("keydown", e => {
                    if (e.key === "Escape") {
                        cancel();
                    }
                });
            });


                    return select;
                }
                },
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
                { title: "Date lettrage", field: "date_lettrage", sorter: "date", width: 100, editor: customNumberEditor2 , headerFilter: "input"},
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
                "<tr>" +
                    "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde actuel :</td>" +
                    "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-actuel'></span></td>" +
                    "<td colspan='2' style='border: 1px solid #000;'></td>" + 
                "</tr>" +
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
                "<label for='JoindreReleveBancaire' class='btn-fichier'>Joindre un relevé bancaire"+
                "</label>"+
                "<input type='file' id='JoindreReleveBancaire' style='display: none;' />"+
                "<span style='cursor: pointer;' onclick='viewReleveBancaire(9, 2025)' title='afficher le relever bancaire'>👁️</span>"+
                "</div>"
                ,

            rowAdded: function(row) {
                // Ajoutez ici une logique pour remplir cette ligne vide si nécessaire
                // Exemple : lorsque l'utilisateur termine une saisie, ajoutez la ligne à la base de données ou au tableau.
            }
        });
        tableBanque.on("cellEdited", function(cell) {
            const row = cell.getRow();
            const rowData = row.getData();

            const oldPieceJustificative = row._oldPieceJustificative || "";

            $.ajax({
                url: '/update-banque-operation',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    data: rowData,
                    oldPieceJustificative: oldPieceJustificative
                },
                success: function(response) {
                    console.log("✅ Données mises à jour avec succès :", response);
                },
                error: function(xhr) {
                    console.error("❌ Erreur lors de la mise à jour :", xhr.responseText);
                }
            });
        });

    });

});
    

  // === tabulator Caisse ===
$(document).ready(function () {
    var exerciceDateCaisse = $('#exercice-date').data('exercice-date');
    var exerciceYearCaisse = new Date(exerciceDateCaisse).getFullYear(); // Extraire l'année de la date

    // Fonction pour gérer le changement de période
    $('input[name="filter-period-Caisse"]').on('change', function() {
        var selectedPeriod = $('input[name="filter-period-Caisse"]:checked').val();

        if (selectedPeriod === 'mois') {
            // Afficher la liste des mois
            $('#periode-Caisse').show();
            // Masquer le champ d'année
            $('#annee-Caisse').hide();
        } else if (selectedPeriod === 'exercice') {
            // Masquer la liste des mois
            $('#periode-Caisse').hide();
            // Afficher le champ d'année avec l'année extraite
            $('#annee-Caisse').show().val(exerciceYear);
        }
    });

    // Initialiser la période au chargement de la page (si le radio 'Mois' est sélectionné par défaut)
    if ($('input[name="filter-period-Caisse"]:checked').val() === 'mois') {
        $('#periode-Caisse').show();
        $('#annee-Caisse').hide();
    } else if ($('input[name="filter-period-Caisse"]:checked').val() === 'exercice') {
        $('#periode-Caisse').hide();
        $('#annee-Caisse').show().val(exerciceYear);
    }

    // Récupérer les journaux de caisse via AJAX
    $.ajax({
        url: '/journaux-Caisse', // Assurez-vous que l'URL correspond à la route Laravel
        method: 'GET',
        success: function(response) {
            // Vérifier s'il y a des journaux
            if (response && response.length > 0) {
                // Ajouter les options dans le select
                response.forEach(function(journal) {
                    $('#journal-Caisse').append(
                        $('<option>', {
                            value: journal.code_journal,
                            text: journal.code_journal, // Utiliser l'intitulé pour l'affichage
                            'data-intitule': journal.intitule // Stocker l'intitulé dans un attribut data
                        })
                    );
                });
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
                contre_partie: "",
                piece_justificative: ""
            }],
                columns: [
                    { title: "Date paiement", field: "date", editor: customDateEditorCaisse, headerFilter: "input" }, 
                    { title: "Mode de paiement",
                    field: "mode_paiement",
                    formatter: function () {
                        return "1.espèce";
                    },
                    headerFilter: "input"
                    },  
                    {title: "Compte", 
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
                            // Affiche directement le code du compte
                            return cell.getValue() || " ";
                        },
                        cellEdited: function(cell) {
                            var compteCode = cell.getValue();
                            var compte = planComptable.find(c => c.compte == compteCode);
                            var row = cell.getRow();
                            var modePaiement = row.getCell("mode_pay").getValue();
                            var intituleCompte = compte ? compte.intitule : '';

                            // --- Générer le libellé ---
                            if (compte) {
                                let libelle = '';
                                if (compte.compte.startsWith('441')) {
                                    libelle = `PAIEMENT ${modePaiement} ${intituleCompte}`;
                                } else if (compte.compte.startsWith('342')) {
                                    libelle = `REGLEMENT ${modePaiement} ${intituleCompte}`;
                                } else if (compte.compte.startsWith('6147')) {
                                    libelle = `${modePaiement} FRAIS BANCAIRE`;
                                } else if (compte.compte.startsWith('61671')) {
                                    libelle = `${modePaiement} FRAIS TIMBRE`;
                                } else {
                                    libelle = `${modePaiement} ${intituleCompte}`;
                                }
                                row.getCell("libelle").setValue(libelle);
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

                            // --- Focus automatique sur Débit ou Crédit ---
                            if (compte) {
                                const debitCell = row.getCell("debit");
                                const creditCell = row.getCell("credit");

                                setTimeout(() => {
                                    if (/^[642]/.test(compte.compte)) {
                                        debitCell.edit();
                                    } else {
                                        creditCell.edit();
                                    }
                                }, 20);
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
                                // À adapter selon ton système d'éditeur dynamique
                                console.log(`Mise à jour des options de "${field}" :`, options);
                            }
                        }
                    },
                    { title: "Libellé", field: "libelle", editor: "input", headerFilter: "input" },
                    { title: "Débit", field: "debit", editor: "number", headerFilter: "input" }, 
                    { title: "Crédit", field: "credit", editor: "number", headerFilter: "input" },
                    { title: "N° facture lettrée", field: "fact_lettrer", editor: "input" , headerFilter: "input"},
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
                    { title: "Date lettrage", field: "date_lettrage", editor: "input", headerFilter: "input" },
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
                            // var icon = "<i class='fas fa-paperclip upload-icon' title='Choisir un fichier'></i>";
                            var input = "<input type='text' class='selected-file-input' readonly value='" + (cell.getValue() || '') + "'>";
                            // return input + icon;
                              return input;
                        },
                        cellClick: function (e, cell) {
                            $('#files_caisse_Modal').show();
                        }, headerFilter: "input"
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
                            tableCaisse.getRows().forEach(function(row) {
                                row.getCell("select").getElement().querySelector("input.select-row-Caisse").checked = isChecked;
                            });
                        }
                    }

                ],
                
            editable: true,
         footerElement: "<table style='width: auto; margin-top: 6px; border: 1px solid #000; border-collapse: collapse; float: left;'>" +
    "<tr>" +
        "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px; border: 1px solid #000;'>Solde actuel :</td>" +
        "<td style='padding: 8px; text-align: center; font-size: 10px; border: 1px solid #000;'><span id='solde-actuel'></span></td>" +
        "<td colspan='2' style='border: 1px solid #000;'></td>" + 
    "</tr>" +
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
"<div style='float: right; margin-top: 6px;font-size:12px;'>" +
    
    " JOINDRE LE RELEVE BANCAIRE <i class='fas fa-file-import'></i>" +
"</div>",

            rowAdded: function(row) {
                // Ajoutez ici une logique pour remplir cette ligne vide si nécessaire
                // Exemple : lorsque l'utilisateur termine une saisie, ajoutez la ligne à la base de données ou au tableau.
            }
        });
          
        }
        // Action Caisse
        $('#periode-Caisse').off('keydown').on('keydown', function (e) {
            if (e.key === "Enter") {
                const table = window.tableCaissePrincipale;
                const rows = table.getRows();
                if (rows.length > 0) {
                const lastRow = rows[rows.length - 1];
                const dateCell = lastRow.getCell("date");
                if (dateCell) {
                    dateCell.edit();
                }
                }
            }
        });
    });
});


$(document).on('click', '.upload-icon[data-action="open-modal"]', function(e) {
    e.stopPropagation();
    $('#files_banque_Modal').show();

});

$(document).on('change', '#JoindreReleveBancaire', function (e) {
    var file = e.target.files[0];
    if (!file) return;

    var codeJournal = $('#journal-Banque').val();

    // Correction ici : extraire l'année depuis data-exercice-date
    var exerciceDate = $('#exercice-date').data('exercice-date');
    var annee = exerciceDate ? new Date(exerciceDate).getFullYear() : '';

    var mois = $('#periode-Banque').val();

    var formData = new FormData();
    formData.append('releve_bancaire', file);
    formData.append('code_journal', codeJournal);
    formData.append('annee', annee);
    formData.append('mois', mois);

    $.ajax({
        url: '/upload-releve-bancaire',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            console.log("Fichier envoyé avec succès :", response);
            alert("Relevé bancaire envoyé avec succès !");
        },
        error: function (xhr, status, error) {
            console.error("Erreur lors de l'envoi :", error);
            alert("Une erreur est survenue lors de l'envoi du relevé bancaire.");
        }
    });
});
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
        const rows = tableBanque.getRows();

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const rowElement = row.getElement();

            if (rowElement.contains(checkboxElement)) {
                const rowData = row.getData();
                console.log("Donnée de la ligne active :", rowData);

                sendDataToController([rowData]);
                return; // stoppe la boucle après avoir trouvé la ligne
            }
        }

        console.log("Aucune ligne correspondante trouvée.");
    }
});
$('#periode-Banque').on('change', function() {
    fetchOperations(); 
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
$('#export-BanqueExcel').on('click', function() {
    exportToExcel();
});
$('#export-BanquePDF').on('click', function() {
    exportToPDF();
});
$('#import-Banque').on('click', function() {
    $('#importModalBanque').show();
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
    input.placeholder = "Jour/Mois";

    // Utiliser l'année extraite
    const exerciceDate = document.getElementById('exercice-date').getAttribute('data-exercice-date');
    const exerciceYear = new Date(exerciceDate).getFullYear(); // Extraire l'année

    const selectedPeriod = $('input[name="filter-period-Banque"]:checked').val(); // Vérifier la période sélectionnée

    // Pré-remplir la valeur si elle existe
    const currentValue = cell.getValue() || "";
    const [currentDay, currentMonth, currentYear] = currentValue.split("/");

    if (selectedPeriod === "mois") {
        // Si "mois" est sélectionné, l'utilisateur entre uniquement le jour
        input.placeholder = "Jour";
        input.value = currentDay || "";
    } else if (selectedPeriod === "exercice") {
        // Si "exercice" est sélectionné, l'utilisateur entre le jour et le mois
        input.placeholder = "Jour/Mois";
        input.value = currentDay && currentMonth ? `${currentDay}/${currentMonth}` : "";
    }

    onRendered(() => {
        input.focus();
    });

    input.addEventListener("blur", () => {
        let value = input.value;

        if (selectedPeriod === "mois") {
            // Ajouter le mois sélectionné et l'année de l'exercice
            const selectedMonth = $('#periode-Banque').val();
            if (selectedMonth) {
                value = `${value}/${selectedMonth}/${exerciceYear}`;
            }
        } else if (selectedPeriod === "exercice") {
            // Ajouter uniquement l'année de l'exercice
            const [day, month] = value.split("/");
            if (day && month) {
                value = `${day}/${month}/${exerciceYear}`;
            }
        }

        success(value);
    });

    input.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            input.blur();
                // Générer le numéro de pièce justificative après saisie de la date
                setTimeout(() => {
                    const row = cell.getRow();
                    function generatePieceJustificativeNum(row) {
                        const date = row.getCell("date").getValue();
                        let jour = "";
                        let annee = "";
                        if (date) {
                            let match = date.match(/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/); // JJ/MM/AAAA
                            if (match) {
                                jour = match[1];
                                annee = match[3];
                            } else {
                                match = date.match(/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})$/); // AAAA/MM/JJ
                                if (match) {
                                    jour = match[3];
                                    annee = match[1];
                                } else {
                                    let d = new Date(date);
                                    if (!isNaN(d)) {
                                        jour = d.getDate().toString().padStart(2, '0');
                                        annee = d.getFullYear().toString();
                                    }
                                }
                            }
                        }
                        // Chercher le plus grand numéro sur les 4 derniers chiffres
                        let rows = tableBanque.getRows();
                        let maxNum = 0;
                        rows.forEach(r => {
                            let val = r.getCell("piece_justificative").getValue();
                            if (val && val.length >= 4) {
                                let last4 = val.slice(-4);
                                let num = parseInt(last4, 10);
                                if (!isNaN(num) && num > maxNum) maxNum = num;
                            }
                        });
                        let nextNum = (maxNum + 1).toString().padStart(4, '0');
                        const codeJournal = $("#journal-Banque").val() || "J";
                        return `p${jour}${annee}${codeJournal}${nextNum}`;
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
    // Crée un input de type text (pas number, sinon on ne peut pas saisir "1+1")
    const input = document.createElement("input");
    input.type = "text";  // <-- CHANGÉ ici
    input.style.width = "100%";

    // Initialiser la valeur avec la valeur actuelle de la cellule ou une chaîne vide
    input.value = cell.getValue() || "";

    onRendered(function() {
        input.focus();
        input.style.height = "100%";
    });

    // Fonction d'évaluation sécurisée d'une expression arithmétique
    function evalExpression(expr) {
        try {
            // Autoriser uniquement chiffres, opérateurs + - * / . () et espaces
            if (/^[0-9+\-*/().\s]+$/.test(expr)) {
                return Function('"use strict";return (' + expr + ')')();
            }
        } catch (e) {
            return null;
        }
        return null;
    }

    function validateAndCommit() {
        const val = input.value.trim();
        const calc = evalExpression(val);
        if (calc !== null) {
            success(calc); // si expression valide, on valide avec le résultat calculé
        } else {
            success(val); // sinon on valide la valeur brute
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
                focusNextEditableCell(cell);
            }, 50);
        }

        if (e.key === "Escape") {
            cancel();
        }
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

    // Traiter fact_lettrer seulement si elle existe
    let factLettrerString = '';
    if (row.fact_lettrer && Array.isArray(row.fact_lettrer) && row.fact_lettrer.length > 0) {
      factLettrerString = row.fact_lettrer
        .map(item => {
          const [id, numero, montant, date] = item.split('|');
          return `${id}|${numero}|${montant}|${date}`;
        })
        .join(' & ');
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
function fetchOperations(selectedJournalCode) {
    var selectedJournalCode = $('#journal-Banque').val(); // Journal
    var selectedMonth = $('#periode-Banque').val(); // Mois

    $.ajax({
        url: '/operation-courante-banque',
        method: 'GET',
        success: function(response) {
            if (response && response.length > 0) {
                console.log(response);

                var filteredOperations = response.filter(function(operation) {
                    var operationMonth = new Date(operation.date).getMonth() + 1; // JS: mois de 0 à 11, donc +1
                    return (
                        operation.type_journal === selectedJournalCode &&
                        (selectedMonth === "selectionner un mois" || operationMonth == selectedMonth)
                    );
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
    const data = tableBanque.getData();
    let cumulDebit = 0;
    let cumulCredit = 0;
    let soldeInitialDB = 0; // Exemple de valeur initiale
    let soldeInitialCR = 0; // Exemple de valeur initiale

    // Calculer le cumul des débits et crédits
    data.forEach(row => {
        cumulDebit += parseFloat(row.debit) || 0; // Ajouter 0 si la valeur est null ou NaN
        cumulCredit += parseFloat(row.credit) || 0; // Ajouter 0 si la valeur est null ou NaN
    });

    // Calculer le solde actuel
    const soldeActuel = soldeInitialDB - soldeInitialCR + cumulDebit - cumulCredit;

    // Calculer le solde débiteur et créditeur
    const soldeDebiteur = soldeActuel > 0 ? soldeActuel : 0;
    const soldeCrediteur = soldeActuel < 0 ? Math.abs(soldeActuel) : 0;

    // Mettre à jour les éléments du footer
    document.getElementById('cumul-debit').innerText = cumulDebit.toFixed(2) || "0.00";
    document.getElementById('cumul-credit').innerText = cumulCredit.toFixed(2) || "0.00";
    // document.getElementById('solde-actuel').innerText = soldeActuel.toFixed(2) || "0.00";
    // document.getElementById('solde-initial-db').innerText = soldeInitialDB.toFixed(2) || "0.00";
    // document.getElementById('solde-initial-cr').innerText = soldeInitialCR.toFixed(2) || "0.00";
    // document.getElementById('solde-debiteur').innerText = soldeDebiteur.toFixed(2) || "0.00";
    // document.getElementById('solde-crediteur').innerText = soldeCrediteur.toFixed(2) || "0.00";
}
function viewReleveBancaire(mois, annee) {
    const codeJournalSession = $('#journal-Banque').val();
    const moisSession = $('#periode-Banque').val();
    const anneeSession = new Date($('#exercice-date').data('exercice-date')).getFullYear();

    const url = `/releve-bancaire/view?mois=${moisSession}&annee=${anneeSession}&code_journal=${codeJournalSession}`;
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
function customDateEditorCaisse(cell, onRendered, success, cancel) {
    const input = document.createElement("input");
    input.type = "text";
    input.style.width = "100%";
    input.placeholder = "Jour/Mois";

    // Utiliser l'année extraite
    const exerciceDateCaisse = document.getElementById('exercice-date').getAttribute('data-exercice-date');
    const exerciceYearCaisse = new Date(exerciceDateCaisse).getFullYear(); // Extraire l'année

    const selectedPeriod = $('input[name="filter-period-Caisse"]:checked').val(); // Vérifier la période sélectionnée

    // Pré-remplir la valeur si elle existe
    const currentValue = cell.getValue() || "";
    const [currentDay, currentMonth, currentYear] = currentValue.split("/");

    if (selectedPeriod === "mois") {
        // Si "mois" est sélectionné, l'utilisateur entre uniquement le jour
        input.placeholder = "Jour";
        input.value = currentDay || "";
    } else if (selectedPeriod === "exercice") {
        // Si "exercice" est sélectionné, l'utilisateur entre le jour et le mois
        input.placeholder = "Jour/Mois";
        input.value = currentDay && currentMonth ? `${currentDay}/${currentMonth}` : "";
    }

    onRendered(() => {
        input.focus();
    });

    input.addEventListener("blur", () => {
        let value = input.value;

        if (selectedPeriod === "mois") {
            // Ajouter le mois sélectionné et l'année de l'exercice
            const selectedMonth = $('#periode-Caisse').val();
            if (selectedMonth) {
                value = `${value}/${selectedMonth}/${exerciceYearCaisse}`;
            }
        } else if (selectedPeriod === "exercice") {
            // Ajouter uniquement l'année de l'exercice
            const [day, month] = value.split("/");
            if (day && month) {
                value = `${day}/${month}/${exerciceYearCaisse}`;
            }
        }

        success(value);
    });

    input.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            input.blur();
                // Générer le numéro de pièce justificative après saisie de la date
                setTimeout(() => {
                    const row = cell.getRow();
                    function generatePieceJustificativeNum(row) {
                        const date = row.getCell("date").getValue();
                        let jour = "";
                        let annee = "";
                        if (date) {
                            let match = date.match(/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/); // JJ/MM/AAAA
                            if (match) {
                                jour = match[1];
                                annee = match[3];
                            } else {
                                match = date.match(/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})$/); // AAAA/MM/JJ
                                if (match) {
                                    jour = match[3];
                                    annee = match[1];
                                } else {
                                    let d = new Date(date);
                                    if (!isNaN(d)) {
                                        jour = d.getDate().toString().padStart(2, '0');
                                        annee = d.getFullYear().toString();
                                    }
                                }
                            }
                        }
                        // Chercher le plus grand numéro sur les 4 derniers chiffres
                        let rows = tableBanque.getRows();
                        let maxNum = 0;
                        rows.forEach(r => {
                            let val = r.getCell("piece_justificative").getValue();
                            if (val && val.length >= 4) {
                                let last4 = val.slice(-4);
                                let num = parseInt(last4, 10);
                                if (!isNaN(num) && num > maxNum) maxNum = num;
                            }
                        });
                        let nextNum = (maxNum + 1).toString().padStart(4, '0');
                        const codeJournal = $("#journal-Caisse").val() || "J";
                        return `p${jour}${annee}${codeJournal}${nextNum}`;
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

