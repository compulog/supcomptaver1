// function getSaisieChoisie() {
//     return $('input[name="filter-Caisse"]:checked').val(); // Récupérer la valeur du bouton radio sélectionné
// }

// // Déclaration de la variable tableCaisse en dehors de la fonction
// var tableCaisse;
// let allOperations = []; // Variable pour stocker toutes les opérations

// // Fonction pour lire un fichier Excel
// function readExcelFile(file) {
//     const fileReader = new FileReader();
//     fileReader.onload = function() {
//         const data = new Uint8Array(this.result);
//         const workbook = XLSX.read(data, { type: 'array' });
//         const sheet = workbook.Sheets[workbook.SheetNames[0]];
//         const sheetData = XLSX.utils.sheet_to_json(sheet, { header: 1 });
//         displayExcelData(sheetData);
//     };
//     fileReader.readAsArrayBuffer(file);
// }

// // Fonction pour afficher les données Excel dans Tabulator
// function displayExcelData(data) {
//     const fields = [
//         'Date', 'Mode de paiement', 'Compte', 'Libellé', 
//         'Débit', 'Crédit', 'N° facture lettrée', 'Taux RAS TVA',
//         'Nature de l\'opération', 'Date lettrage', 'Contre-Partie'
//     ];
    
//     const rows = [];
//     for (let i = 1; i < data.length; i++) {
//         const row = {};
//         fields.forEach((field, index) => {
//             let value = data[i][index] !== undefined ? data[i][index] : ''; // Gérer les valeurs undefined
            
//             // Vérifier si la valeur est un nombre et correspond à une date Excel
//             if (field === 'Date' && typeof value === 'number') {
//                 // Convertir le nombre Excel en date JavaScript
//                 const excelDate = new Date((value - 25569) * 86400 * 1000);
//                 value = excelDate.toLocaleDateString(); // Formater la date selon vos besoins
//             }
            
//             row[field.toLowerCase().replace(/ /g, "_")] = value;
//         });
//         console.log(row); // Afficher chaque ligne pour déboguer
//         rows.push(row);
//     }
    
//     tableCaisse.setData(rows); // Mettre à jour les données de Tabulator
// }

// // Fonction pour lire un fichier PDF
// function readPdfFile(file) {
//     const reader = new FileReader();
//     reader.onload = function() {
//         const pdfData = new Uint8Array(this.result);
//         pdfjsLib.getDocument(pdfData).promise.then(pdf => {
//             const numPages = pdf.numPages;
//             let pdfText = '';
//             let textPromises = [];
//             for (let pageNum = 1; pageNum <= numPages; pageNum++) {
//                 textPromises.push(pdf.getPage(pageNum).then(page => {
//                     return page.getTextContent().then(textContent => {
//                         let pageText = '';
//                         textContent.items.forEach(item => {
//                             pageText += item.str + ' ';
//                         });
//                         return pageText;
//                     });
//                 }));
//             }
//             Promise.all(textPromises).then(pagesText => {
//                 pdfText = pagesText.join(' ');
//                 displayPdfData(pdfText);
//             });
//         });
//     };
//     reader.readAsArrayBuffer(file);
// }

// // Fonction pour afficher le contenu PDF dans Tabulator
// function displayPdfData(text) {
//     const lines = text.split('\n');
//     const rows = [];
//     lines.forEach(line => {
//         const columns = line.trim().split(/\s{2,}/);
//         const row = {
//             date: columns[0] || '',
//             mode_paiement: columns[1] || '',
//             compte: columns[2] || '',
//             libelle: columns[3] || '',
//             debit: columns[4] || '',
//             credit: columns[5] || '',
//             facture: columns[6] || '',
//             taux_ras_tva: columns[7] || '',
//             nature_op: columns[8] || '',
//             date_lettrage: columns[9] || '',
//             contre_partie: columns[10] || '',
//         };
//         rows.push(row);
//     });

//     tableCaisse.setData(rows); // Mettre à jour les données de Tabulator
// }

// $(document).ready(function() {
//     // Récupérer la date de l'exercice depuis l'attribut data-exercice-date
//     var exerciceDate = $('#exercice-date').data('exercice-date');
//     var exerciceYear = new Date(exerciceDate).getFullYear(); // Extraire l'année de la date
    
//     // Fonction pour gérer le changement de période
//     $('input[name="filter-period-Caisse"]').on('change', function() {
//         var selectedPeriod = $('input[name="filter-period-Caisse"]:checked').val();

//         if (selectedPeriod === 'mois') {
//             // Afficher la liste des mois
//             $('#periode-Caisse').show();
//             // Masquer le champ d'année
//             $('#annee-Caisse').hide();
//         } else if (selectedPeriod === 'exercice') {
//             // Masquer la liste des mois
//             $('#periode-Caisse').hide();
//             // Afficher le champ d'année avec l'année extraite
//             $('#annee-Caisse').show().val(exerciceYear);
//         }
//     });

//     // Initialiser la période au chargement de la page (si le radio 'Mois' est sélectionné par défaut)
//     if ($('input[name="filter-period-Caisse"]:checked').val() === 'mois') {
//         $('#periode-Caisse').show();
//         $('#annee-Caisse').hide();
//     } else if ($('input[name="filter-period-Caisse"]:checked').val() === 'exercice') {
//         $('#periode-Caisse').hide();
//         $('#annee-Caisse').show().val(exerciceYear);
//     }

//     // Récupérer les journaux de caisse via AJAX
//     $.ajax({
//         url: '/journaux-Caisse', // Assurez-vous que l'URL correspond à la route Laravel
//         method: 'GET',
//         success: function(response) {
//             // Vérifier s'il y a des journaux
//             if (response && response.length > 0) {
//                 // Ajouter les options dans le select
//                 response.forEach(function(journal) {
//                     $('#journal-Caisse').append(
//                         $('<option>', {
//                             value: journal.code_journal,
//                             text: journal.code_journal, // Utiliser l'intitulé pour l'affichage
//                             'data-intitule': journal.intitule // Stocker l'intitulé dans un attribut data
//                         })
//                     );
//                 });
//             } else {
//                 console.log("Aucun journal trouvé.");
//             }
//         },
//         error: function() {
//             console.log("Erreur lors de la récupération des journaux.");
//         }
//     });

//     // Changer l'intitulé lorsque l'utilisateur sélectionne un journal
//     $('#journal-Caisse').on('change', function() {
//         var selectedCode = $(this).val(); // Récupérer la valeur du code sélectionné
//         var selectedOption = $(this).find('option:selected');
        
//         if (selectedCode) {
//             // Afficher l'intitulé correspondant dans l'input
//             var intitule = selectedOption.data('intitule'); // Récupérer l'intitulé depuis l'attribut data
//             $('#filter-intitule-Caisse').val(intitule);
//         } else {
//             // Si aucune option n'est sélectionnée, vider l'input
//             $('#filter-intitule-Caisse').val('');
//         }
//     });
//     function customDateEditor(cell, onRendered, success, cancel) {
//         const input = document.createElement("input");
//         input.type = "text";
//         input.style.width = "100%";
//         input.placeholder = "Jour/Mois";
    
//         // Utiliser l'année extraite
//         const exerciceDate = document.getElementById('exercice-date').getAttribute('data-exercice-date');
//         const exerciceYear = new Date(exerciceDate).getFullYear(); // Extraire l'année
    
//         const selectedPeriod = $('input[name="filter-period-Caisse"]:checked').val(); // Vérifier la période sélectionnée
    
//         // Pré-remplir la valeur si elle existe
//         const currentValue = cell.getValue() || "";
//         const [currentDay, currentMonth, currentYear] = currentValue.split("/");
    
//         if (selectedPeriod === "mois") {
//             // Si "mois" est sélectionné, l'utilisateur entre uniquement le jour
//             input.placeholder = "Jour";
//             input.value = currentDay || "";
//         } else if (selectedPeriod === "exercice") {
//             // Si "exercice" est sélectionné, l'utilisateur entre le jour et le mois
//             input.placeholder = "Jour/Mois";
//             input.value = currentDay && currentMonth ? `${currentDay}/${currentMonth}` : "";
//         }
    
//         onRendered(() => {
//             input.focus();
//         });
    
//         input.addEventListener("blur", () => {
//             let value = input.value;
    
//             if (selectedPeriod === "mois") {
//                 // Ajouter le mois sélectionné et l'année de l'exercice
//                 const selectedMonth = $('#periode-Caisse').val();
//                 if (selectedMonth) {
//                     value = `${value}/${selectedMonth}/${exerciceYear}`;
//                 }
//             } else if (selectedPeriod === "exercice") {
//                 // Ajouter uniquement l'année de l'exercice
//                 const [day, month] = value.split("/");
//                 if (day && month) {
//                     value = `${day}/${month}/${exerciceYear}`;
//                 }
//             }
    
//             success(value);
//         });
    
//         input.addEventListener("keydown", (e) => {
//             if (e.key === "Enter") {
//                 e.preventDefault();
//                 input.blur();
//             }
//         });
    
//         return input;
//     }
    
//     // Gestionnaire d'événements pour l'onglet Caisse
//     $('.tab[data-tab="Caisse"]').on('click', function() {
//         // Afficher le contenu de l'onglet Caisse
//         $('.tab-content').removeClass('active');
//         $('#Caisse').addClass('active');

//         // Initialiser Tabulator pour l'onglet Caisse
//         tableCaisse = new Tabulator("#table-Caisse", {
//             height: "511px", // Hauteur du tableau
//             layout: "fitData", // Ajuste la largeur des colonnes
//             rowheight: "30px",
//             columns: [ 
//                 {
//                     title: "Date",
//                     field: "date",
//                     sorter: "date",
//                     width: 100,
//                     editor: customDateEditor, // Utiliser l'éditeur personnalisé
//                     headerFilter: "input",
//                 },
//                 { title: "Mode de paiement", field: "mode_pay", width: 100, editor: customListEditor, headerFilter: "input",
//                      },
//                 {
//                     title: "Compte", 
//                     field: "compte", 
//                     width: 100, 
//                     editor: "list", 
                    
//                     editorParams: {
//                         values: planComptable.reduce((acc, compte) => {
//                             acc[compte.id] = compte.compte; // Assurez-vous que 'id' et 'compte' correspondent à vos colonnes
//                             return acc;
//                         }, {}),
//                         clearable: true,
//                         verticalNavigation: "editor",
                        
//                     },
//                     headerFilter: "input",
//                     formatter: function(cell) {
//                         var compteId = cell.getValue();
//                         var compte = planComptable.find(c => c.id == compteId);
//                         return compte ? compte.compte : " ";
//                     },
//                     cellEdited: function(cell) {
//                         var compteId = cell.getValue();
//                         var compte = planComptable.find(c => c.id == compteId);
//                         var modePaiement = cell.getRow().getCell("mode_pay").getValue();
//                         var intituleCompte = compte ? compte.intitule : '';
//                         if (compte) {
//                             var libelle = `${modePaiement} ${intituleCompte}`;
//                             cell.getRow().getCell("libelle").setValue(libelle);
//                         } else {
//                             cell.getRow().getCell("libelle").setValue('');
//                         }
                        
//                     }
//                 },
//                 { title: "Libellé", field: "libelle", width: 100, editor: "input", headerFilter: "input" },
//                 { title: "Débit", field: "debit", sorter: "number", width: 100, editor: customNumberEditor, headerFilter: "input" },
//                 { title: "Crédit", field: "credit", sorter: "number", width: 100, editor: customNumberEditor, headerFilter: "input" },
//                 { title: "N° facture lettrée", field: "fact_lettrer", width: 100, editor: customNumberEditor, headerFilter: "input" },
//                 {
//                     title: "Taux RAS TVA", 
//                     field: "taux_ras_tva", 
//                     width: 100, 
//                     headerFilter: "input",
//                     editor: customListEditor1, 
                    
//                 },
//                 {
//                     title: "Nature de l'opération", 
//                     field: "nature_op", 
//                     width: 100, 
//                     editor: customListEditor2, 
//                     headerFilter: "input"
//                 },
//                 { title: "Date lettrage", field: "date_lettrage", sorter: "date", width: 100, editor: customNumberEditor2, headerFilter: "input" },
//                 {
//                     title: "Contre-Partie", 
//                     field: "contre_partie", 
//                     width: 100, 
//                     editor: "list", 
//                     editorParams: {
//                         values: planComptable
//                             .filter(compte => compte.compte.startsWith('516'))
//                             .reduce((acc, compte) => {
//                                 acc[compte.id] = compte.compte;
//                                 return acc;
//                             }, {}),
//                         clearable: true,
//                         verticalNavigation: "editor",
//                     },
//                     headerFilter: "input"
//                 },
//                 {
//                     title: "Pièce justificative", 
//                     field: "piece_justificative", 
//                     width: 200,
//                     headerFilter: "input",
//                     formatter: function(cell) {
//                         var icon = "<i class='fas fa-paperclip upload-icon' title='Choisir un fichier'></i>";
//                         var input = "<input type='text' class='selected-file-input' id='selectedFile' readonly value='" + (cell.getValue() || '') + "'>";
//                         return  input + icon ;
//                     },
//                     cellClick: function(e, cell) {
//                         $('#fileModal').show();
//                         $('#confirmBtn').data('cell', cell);  
//                     }
//                 },
//                 {
//                     title: "<input type='checkbox' id='selectAll'>", 
//                     field: "selectAll", 
//                     width: 40, 
//                     formatter: function() {
//                         return "<input type='checkbox' class='select-row'>";
//                     },
//                     headerSort: false,
//                     headerFilter: false,
//                     align: "center",
//                     cellClick: function(e, cell) {
//                         var isChecked = $("#selectAll").prop("checked");
//                         tableCaisse.getRows().forEach(function(row) {
//                             row.getCell("select").getElement().querySelector("input").checked = isChecked;
//                         });
//                     }
//                 },
//             ],
//             data: [
//                 { // Ligne vide
//                     date: '',
//                     mode_paiement: '',
//                     compte: '',
//                     libelle: '',
//                     debit: '',
//                     credit: '',
//                     facture: '',
//                     taux_ras_tva: '',
//                     nature_op: '',
//                     date_lettrage: '',
//                     contre_partie: '',
//                     piece_justificative: '',
//                 }
//             ],
//             editable: true,
//             footerElement: "<table style='width: 100%; margin-top: 6px; border-collapse: collapse;'>" +
//                 "<tr>" +
//                     "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px;'>Solde actuel :</td>" +
//                     "<td style='padding: 8px; text-align: center; font-size: 10px;'><span id='solde-actuel'></span></td>" +
//                     "<td colspan='2'></td>" + 
 
//                     "</tr>" +
//                 "<tr>" +
//                 "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px;'>Solde initial DB :</td>" +
//                 "<td style='padding: 8px; text-align: center; font-size: 10px;'><span id='solde-initial-db'></span></td>" +
          
//                     "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px;'>Solde initial CR :</td>" +
//                     "<td style='padding: 8px; text-align: center; font-size: 10px;'><span id='solde-initial-cr'></span></td>" +
//                         "</tr>" +
//                 "<tr>" +
//                 "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px;'>Cumul débit :</td>" +
//                 "<td style='padding: 8px; text-align: center; font-size: 10px;'><span id='cumul-debit'></span></td>" +
       
//                     "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px;'>Cumul crédit :</td>" +
//                     "<td style='padding: 8px; text-align: center; font-size: 10px;'><span id='cumul-credit'></span></td>" +
//                        "</tr>" +
//                 "<tr>" +
//                 "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px;'>Solde débiteur :</td>" +
//                 "<td style='padding: 8px; text-align: center; font-size: 10px;'><span id='solde-debiteur'></span></td>" +
        
//                     "<td style='padding: 8px; text-align: left; font-weight: bold; font-size: 11px;'>Solde créditeur :</td>" +
//                     "<td style='padding: 8px; text-align: center; font-size: 10px;'><span id='solde-crediteur'></span></td>" +
//                 "</tr>" +
//             "</table>",
//         });


        
// function focusNextEditableCell(currentCell) {
//     const row = currentCell.getRow();
//     const cells = row.getCells();
//     const currentIndex = cells.findIndex(c => c === currentCell);

//     // Chercher dans la même ligne la prochaine cellule éditable
//     for (let i = currentIndex + 1; i < cells.length; i++) {
//         const colDef = cells[i].getColumn().getDefinition();
//         if (colDef.editor) {
//             cells[i].edit();
//             return;
//         }
//     }

//     // Sinon, passer à la première cellule éditable de la ligne suivante
//     const table = currentCell.getTable();
//     const rows = table.getRows();
//     const currentRowIndex = rows.findIndex(r => r.getIndex() === row.getIndex());
//     if (currentRowIndex < rows.length - 1) {
//         const nextRow = rows[currentRowIndex + 1];
//         for (let cell of nextRow.getCells()) {
//             if (cell.getColumn().getDefinition().editor) {
//                 cell.edit();
//                 return;
//             }
//         }
//     }
// }
// function genericTextEditor(cell, onRendered, success, cancel, editorParams) {
//     const input = document.createElement("input");
//     input.type = "text";
//     input.style.width = "100%";
//     input.value = cell.getValue() || "";

//     onRendered(() => {
//         input.focus();
//     });

//     input.addEventListener("blur", () => {
//         success(input.value);
//     });

//     input.addEventListener("keydown", (e) => {
//         if (e.key === "Enter") {
//             e.preventDefault();
//             success(input.value);
//             setTimeout(() => {
//                 focusNextEditableCell(cell);
//             }, 50);
//         }
//     });

//     return input;
// }


// /**
//  * Ajoute la navigation par la touche Enter à l'élément d'édition.
//  * @param {HTMLElement} editorElement - L'élément de l'éditeur (input, textarea, etc.).
//  * @param {Object} cell - La cellule Tabulator en cours d'édition.
//  * @param {Function} successCallback - La fonction à appeler pour valider la saisie.
//  * @param {Function} cancelCallback - (Optionnel) La fonction à appeler en cas d'annulation.
//  * @param {Function} getValueCallback - (Optionnel) Fonction pour récupérer la valeur courante de l'éditeur.
//  */
// function addEnterNavigation(editorElement, cell, successCallback, cancelCallback, getValueCallback) {
//     editorElement.addEventListener("keydown", function(e) {
//         if (e.key === "Enter") {
//             e.preventDefault();
//             // Récupérer la valeur courante (pour un input, editorElement.value suffit)
//             const value = (getValueCallback && typeof getValueCallback === "function")
//                 ? getValueCallback(editorElement)
//                 : editorElement.value;
//             // Valider la saisie en appelant le callback success
//             successCallback(value);
//             // Passer à la cellule éditable suivante
//             setTimeout(() => {
//                 focusNextEditableCell(cell);
//             }, 50);
//         }
//     });
// }
// function customListEditor(cell, onRendered, success, cancel) {
//     const input = document.createElement("select");
//     input.style.width = "100%";

//     // Remplir le select avec les options
//     const options = ["Espèces", "Chèques", "Virement", "Effet", "Prélèvements", "Compensations", "Autres"]; // Remplacez par vos options
//     options.forEach(option => {
//         const opt = document.createElement("option");
//         opt.value = option;
//         opt.innerHTML = option;
//         input.appendChild(opt);
//     });

//     // Initialiser la valeur avec la valeur actuelle de la cellule
//     input.value = cell.getValue() || "";

//     // Focus sur l'input une fois rendu
//     onRendered(function() {
//         input.focus();
//         input.style.height = "100%";
//     });

//     // Fonction de validation : ici, nous validons simplement en retournant la valeur de l'input
//     function validateAndCommit() {
//         success(input.value);
//     }

//     // Lors du blur, valider la saisie
//     input.addEventListener("blur", function() {
//         validateAndCommit();
//     });

//     // Intercepter la touche Entrée pour valider et naviguer
//     input.addEventListener("keydown", function(e) {
//         if (e.key === "Enter") {
//             e.preventDefault();
//             validateAndCommit();
//             // Passer à la cellule éditable suivante
//             setTimeout(function() {
//                 focusNextEditableCell(cell);
//             }, 50);
//         }
//     });

//     return input;
// }

// function customListEditor1(cell, onRendered, success, cancel) {
//     const input = document.createElement("select");
//     input.style.width = "100%";

//     // Remplir le select avec les options
//     const options = ["0", "75", "100"]; // Remplacez par vos options
//     options.forEach(option => {
//         const opt = document.createElement("option");
//         opt.value = option;
//         opt.innerHTML = option;
//         input.appendChild(opt);
//     });

//     // Initialiser la valeur avec la valeur actuelle de la cellule
//     input.value = cell.getValue() || "";

//     // Focus sur l'input une fois rendu
//     onRendered(function() {
//         input.focus();
//         input.style.height = "100%";
//     });

//     // Fonction de validation : ici, nous validons simplement en retournant la valeur de l'input
//     function validateAndCommit() {
//         success(input.value);
//     }

//     // Lors du blur, valider la saisie
//     input.addEventListener("blur", function() {
//         validateAndCommit();
//     });

//     // Intercepter la touche Entrée pour valider et naviguer
//     input.addEventListener("keydown", function(e) {
//         if (e.key === "Enter") {
//             e.preventDefault();
//             validateAndCommit();
//             // Passer à la cellule éditable suivante
//             setTimeout(function() {
//                 focusNextEditableCell(cell);
//             }, 50);
//         }
//     });

//     return input;
// }



// function customListEditor2(cell, onRendered, success, cancel) {
//     const input = document.createElement("select");
//     input.style.width = "100%";

//     // Récupérer le compte sélectionné dans la ligne actuelle
//     const compteId = cell.getRow().getCell("compte").getValue();
//     const compte = planComptable.find(c => c.id == compteId);
    
//     // Déterminer les options en fonction du compte
//     let options = [];
//     if (compte) {
//         if (compte.compte.startsWith('441')) {
//             options = ["Achat de travaux", "Achat de biens d'équipement", "Achat de services"];
//         } else if (compte.compte.startsWith('341')) {
//             options = ["Vente de travaux", "Vente de services", "Vente de biens d'équipement"];
//         } else {
//             options = [
//                 "Achat de travaux", 
//                 "Achat de biens d'équipement", 
//                 "Achat de services", 
//                 "Vente de travaux", 
//                 "Vente de services", 
//                 "Vente de biens d'équipement"
//             ];
//         }
//     }

//     // Remplir le select avec les options
//     options.forEach(option => {
//         const opt = document.createElement("option");
//         opt.value = option;
//         opt.innerHTML = option;
//         input.appendChild(opt);
//     });

//     // Initialiser la valeur avec la valeur actuelle de la cellule
//     input.value = cell.getValue() || "";

//     // Focus sur l'input une fois rendu
//     onRendered(function() {
//         input.focus();
//         input.style.height = "100%";
//     });

//     // Fonction de validation : ici, nous validons simplement en retournant la valeur de l'input
//     function validateAndCommit() {
//         success(input.value);
//     }

//     // Lors du blur, valider la saisie
//     input.addEventListener("blur", function() {
//         validateAndCommit();
//     });

//     // Intercepter la touche Entrée pour valider et naviguer
//     input.addEventListener("keydown", function(e) {
//         if (e.key === "Enter") {
//             e.preventDefault();
//             validateAndCommit();
//             // Passer à la cellule éditable suivante
//             setTimeout(function() {
//                 focusNextEditableCell(cell);
//             }, 50);
//         }
//     });

//     return input;
// }




// function customNumberEditor1(cell, onRendered, success, cancel) {
//     const input = document.createElement("input");
//     input.type = "number";
//     input.style.width = "100%";
//     input.placeholder = "jj";

//     input.value = cell.getValue() || "";

//     onRendered(function() {
//         input.focus();
//         input.style.height = "100%";
//     });

//     let isValidating = false; // Drapeau pour éviter les appels multiples

//     function validateAndCommit() {
//         if (isValidating) return; // Éviter les appels multiples
//         isValidating = true; // Définir le drapeau

//         const value = parseInt(input.value, 10); // Convertir la valeur en entier

//         // Vérifier si la valeur est un nombre valide et entre 1 et 31
//         if (isNaN(value) || value < 1 || value > 31) {
//             alert("La valeur doit être un nombre entre 1 et 31.");
//             isValidating = true; // Réinitialiser le drapeau
//             return;
//         }

//         // Vérifier la longueur de la valeur
//         if (input.value.length > 2) {
//             alert("La valeur ne peut pas dépasser 2 chiffres.");
//             isValidating = true; // Réinitialiser le drapeau
//             return;
//         }

//         success(input.value);
//         isValidating = false; // Réinitialiser le drapeau
//     }

//     input.addEventListener("blur", function() {
//         validateAndCommit();
//     });

//     input.addEventListener("keydown", function(e) {
//         if (e.key === "Enter") {
//             e.preventDefault();
//             validateAndCommit();
//             setTimeout(function() {
//                 // Vérifiez si la cellule suivante est différente avant de la focaliser
//                 const nextCell = focusNextEditableCell(cell);
//                 if (nextCell && nextCell !== cell) {
//                     nextCell.focus(); // Focaliser la cellule suivante
//                 }
//             }, 50);
//         }
//     });

//     return input;
// }
// function customNumberEditor(cell, onRendered, success, cancel) {
//     // Crée un input de type number
//     const input = document.createElement("input");
//     input.type = "number";
//     input.style.width = "100%";
//     // Initialiser la valeur avec la valeur actuelle de la cellule ou une chaîne vide
//     input.value = cell.getValue() || "";

//     // Focus sur l'input une fois rendu
//     onRendered(function() {
//         input.focus();
//         input.style.height = "100%";
//     });

//     // Fonction de validation : ici, nous validons simplement en retournant la valeur de l'input
//     function validateAndCommit() {
//         // Vous pouvez ajouter des validations supplémentaires si besoin
//         success(input.value);
//     }

//     // Lors du blur, valider la saisie
//     input.addEventListener("blur", function() {
//         validateAndCommit();
//     });

//     // Intercepter la touche Entrée pour valider et naviguer
//     input.addEventListener("keydown", function(e) {
//         if (e.key === "Enter") {
//             e.preventDefault();
//             validateAndCommit();
//             // Passer à la cellule éditable suivante
//             setTimeout(function() {
//                 focusNextEditableCell(cell);
//             }, 50);
//         }
//     });

//     return input;
// }



// function customNumberEditor2(cell, onRendered, success, cancel) {
//     // Crée un input de type number
//     const input = document.createElement("input");
//     input.type = "date";
//     input.style.width = "100%";
//     // Initialiser la valeur avec la valeur actuelle de la cellule ou une chaîne vide
//     input.value = cell.getValue() || "";

//     // Focus sur l'input une fois rendu
//     onRendered(function() {
//         input.focus();
//         input.style.height = "100%";
//     });

//     // Fonction de validation : ici, nous validons simplement en retournant la valeur de l'input
//     function validateAndCommit() {
//         // Vous pouvez ajouter des validations supplémentaires si besoin
//         success(input.value);
//     }

//     // Lors du blur, valider la saisie
//     input.addEventListener("blur", function() {
//         validateAndCommit();
//     });

//     // Intercepter la touche Entrée pour valider et naviguer
//     input.addEventListener("keydown", function(e) {
//         if (e.key === "Enter") {
//             e.preventDefault();
//             validateAndCommit();
//             // Passer à la cellule éditable suivante
//             setTimeout(function() {
//                 focusNextEditableCell(cell);
//             }, 50);
//         }
//     });

//     return input;
// }


//         // Récupérer les opérations courantes via AJAX
//         fetchOperations();

//         // Fermer la modale lorsqu'on clique sur la croix
//         $('.close-btn').on('click', function() {
//             $('#fileModal').hide();
//         });

//         // Fermer la modale si on clique en dehors de la modale
//         $(window).on('click', function(event) {
//             if ($(event.target).is('#fileModal')) {
//                 $('#fileModal').hide();
//             }
//         });

//         // Gestionnaire d'événements pour le bouton "Confirmer"
//         $('#confirmBtn').on('click', function() {
//             var selectedFileName = $('.file-button.selected').data('filename');
//             if (selectedFileName) {
//                 var cell = $(this).data('cell'); // Récupérer la cellule à mettre à jour
//                 cell.setValue(selectedFileName); // Mettre à jour la cellule avec le nom du fichier

//                 // Lire le fichier à partir de l'URL
//                 var fileUrl = '/files/' + selectedFileName; // Assurez-vous que cette URL est correcte
//                 readFile(fileUrl);
//             } else {
//                 alert("Veuillez sélectionner un fichier avant de confirmer.");
//             }
//             $('#fileModal').hide();
//         });

//         // Gestionnaire d'événements pour les boutons de fichier
//         $('.file-button').on('click', function() {
//             $('.file-button').removeClass('selected'); // Retirer la classe 'selected' de tous les boutons
//             $(this).addClass('selected'); // Ajouter la classe 'selected' au bouton cliqué
//         });

//         document.getElementById('file-input').addEventListener('change', function(event) {
//             const file = event.target.files[0];
//             if (file) {
//                 if (file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || file.type === 'application/vnd.ms-excel') {
//                     readExcelFile(file);
//                 } else if (file.type === 'application/pdf') {
//                     readPdfFile(file);
//                 } else {
//                     alert('Veuillez sélectionner un fichier Excel ou PDF valide.');
//                 }
//             }
//         });
//     });
// });

// // Fonction pour récupérer les opérations courantes
// function fetchOperations() {
//     $.ajax({
//         url: '/operation-courante-caisse', // Assurez-vous que l'URL correspond à la route Laravel
//         method: 'GET',
//         success: function(response) {
//             // Vérifier s'il y a des opérations
//             if (response && response.length > 0) {
//                 // Ajouter une ligne vide à la fin des données récupérées
//                 response.push({
//                     date: '',
//                     mode_pay: '',
//                     compte: '',
//                     libelle: '',
//                     debit: '',
//                     credit: '',
//                     facture: '',
//                     taux_ras_tva: '',
//                     nature_op: '',
//                     date_lettrage: '',
//                     contre_partie: '',
//                     piece_justificative: '',
//                 });
//                 allOperations = response; // Stocker toutes les opérations
//                 // Mettre à jour les données de Tabulator avec les opérations récupérées
//                 tableCaisse.setData(allOperations);
//                 updateFooter(); // Mettre à jour le footer après avoir défini les données
//             } else {
//                 console.log("Aucune opération trouvée.");
//             }
//         },
//         error: function() {
//             console.log("Erreur lors de la récupération des opérations.");
//         }
//     });
// }

// // Fonction pour mettre à jour le footer
// function updateFooter() {
//     const data = tableCaisse.getData();
//     let cumulDebit = 0;
//     let cumulCredit = 0;
//     let soldeInitialDB = 0; // Exemple de valeur initiale
//     let soldeInitialCR = 0; // Exemple de valeur initiale

//     // Calculer le cumul des débits et crédits
//     data.forEach(row => {
//         cumulDebit += parseFloat(row.debit) || 0; // Ajouter 0 si la valeur est null ou NaN
//         cumulCredit += parseFloat(row.credit) || 0; // Ajouter 0 si la valeur est null ou NaN
//     });

//     // Calculer le solde actuel
//     const soldeActuel = soldeInitialDB - soldeInitialCR + cumulDebit - cumulCredit;

//     // Calculer le solde débiteur et créditeur
//     const soldeDebiteur = soldeActuel > 0 ? soldeActuel : 0;
//     const soldeCrediteur = soldeActuel < 0 ? Math.abs(soldeActuel) : 0;

//     // Mettre à jour les éléments du footer
//     document.getElementById('cumul-debit').innerText = cumulDebit.toFixed(2) || "0.00";
//     document.getElementById('cumul-credit').innerText = cumulCredit.toFixed(2) || "0.00";
//     document.getElementById('solde-actuel').innerText = soldeActuel.toFixed(2) || "0.00";
//     document.getElementById('solde-initial-db').innerText = soldeInitialDB.toFixed(2) || "0.00";
//     document.getElementById('solde-initial-cr').innerText = soldeInitialCR.toFixed(2) || "0.00";
//     document.getElementById('solde-debiteur').innerText = soldeDebiteur.toFixed(2) || "0.00";
//     document.getElementById('solde-crediteur').innerText = soldeCrediteur.toFixed(2) || "0.00";
// }

// // Fonction pour filtrer les données
// function filterData() {
//     const selectedMonth = $('#periode-Caisse').val();
//     const selectedYear = $('#annee-Caisse').val();
//     const selectedJournalCode = $('#journal-Caisse').val();

//     // Filtrer les lignes
//     const filteredRows = allOperations.filter(row => {
//         const rowDate = new Date(row.date);
//         const matchesMonth = selectedMonth !== "selectionner un mois" ? rowDate.toLocaleString('default', { month: 'long' }) === selectedMonth : true; // Vérifier si le mois correspond
//         const matchesYear = selectedYear ? rowDate.getFullYear() == selectedYear : true; // Vérifier si l'année correspond
//         const matchesJournal = selectedJournalCode ? row.type_journal === selectedJournalCode : true; // Vérifier si le code journal correspond
//         const isEmptyRow = !row.date && !row.mode_pay && !row.compte && !row.libelle && !row.debit && !row.credit && !row.fact_lettrer && !row.taux_ras_tva && !row.nature_op && !row.date_lettrage && !row.contre_partie && !row.piece_justificative; // Vérifier si la ligne est vide

//         return (matchesMonth && matchesYear && matchesJournal) || isEmptyRow; // Retourner vrai si les conditions sont remplies ou si la ligne est vide
//     });

//     // Mettre à jour les données de Tabulator avec les lignes filtrées
//     tableCaisse.setData(filteredRows);
//     updateFooter(); // Mettre à jour le footer après filtrage
// }

// // Gestionnaire d'événements pour le sélecteur de mois
// $('#periode-Caisse').on('change', function() {
//     filterData();
// });


// // Gestionnaire d'événements pour le sélecteur d'année
// $('#annee-Caisse').on('change', function() {
//     filterData();
// });
 
// // Gestionnaire d'événements pour le sélecteur de journal
// $('#journal-Caisse').on('change', function() {
//     filterData();
// });

// function readFile(fileUrl) {
//     // Utiliser fetch pour récupérer le fichier
//     fetch(fileUrl)
//         .then(response => {
//             if (!response.ok) {
//                 throw new Error('Erreur lors de la récupération du fichier');
//             }
//             return response.blob(); // Convertir la réponse en blob
//         })
//         .then(blob => {
//             const fileType = blob.type;
//             const fileReader = new FileReader();

//             fileReader.onload = function() {
//                 if (fileType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || 
//                     fileType === 'application/vnd.ms-excel') {
//                     const data = new Uint8Array(this.result);
//                     const workbook = XLSX.read(data, { type: 'array' });
//                     const sheet = workbook.Sheets[workbook.SheetNames[0]];
//                     const sheetData = XLSX.utils.sheet_to_json(sheet, { header: 1 });
//                     displayExcelData(sheetData);
//                 } else if (fileType === 'application/pdf') {
//                     const pdfData = new Uint8Array(this.result);
//                     pdfjsLib.getDocument(pdfData).promise.then(pdf => {
//                         const numPages = pdf.numPages;
//                         let pdfText = '';
//                         let textPromises = [];
//                         for (let pageNum = 1; pageNum <= numPages; pageNum++) {
//                             textPromises.push(pdf.getPage(pageNum).then(page => {
//                                 return page.getTextContent().then(textContent => {
//                                     let pageText = '';
//                                     textContent.items.forEach(item => {
//                                         pageText += item.str + ' ';
//                                     });
//                                     return pageText;
//                                 });
//                             }));
//                         }
//                         Promise.all(textPromises).then(pagesText => {
//                             pdfText = pagesText.join(' ');
//                             displayPdfData(pdfText);
//                         });
//                     });
//                 } else {
//                     alert('Type de fichier non pris en charge.');
//                 }
//             };

//             fileReader.readAsArrayBuffer(blob); // Lire le blob comme un ArrayBuffer
//         })
//         .catch(error => {
//             console.error('Erreur:', error);
//         });
// }

// // Gestionnaire d'événements pour la touche "Entrée"
// $(document).on('keydown', function(e) {
//     if (e.key === "Enter") {
//         // Récupérer toutes les lignes de la table
//         const rows = tableCaisse.getRows();
//         let selectedData = [];

//         // Parcourir les lignes pour trouver celles qui sont sélectionnées
//         rows.forEach(row => {
//             const checkbox = row.getCell("selectAll").getElement().querySelector("input");
//             if (checkbox && checkbox.checked) {
//                 selectedData.push(row.getData());
//             }
//         });

//         // Si des lignes sont sélectionnées, envoyer les données
//         if (selectedData.length > 0) {
//             sendDataToController(selectedData);
//         } 
//     }
// });
// function sendDataToController(data) {
//     // Récupérer le code journal sélectionné
//     var selectedJournalCode = $('#journal-Caisse').val(); // Récupérer la valeur du journal sélectionné
//     console.log("Code journal sélectionné :", selectedJournalCode); // Pour déboguer

//     console.log("Données à envoyer :", data); // Ajoutez cette ligne pour déboguer
//     data.forEach(row => {
//         // Convertir la date au format MySQL (YYYY-MM-DD)
//         let formattedDate = '';
//         if (row.date) {
//             const [day, month, year] = row.date.split('/');
//             const monthIndex = new Date(Date.parse(month + " 1, 2020")).getMonth() + 1; // Convertir le mois en index numérique
//             formattedDate = `${year}-${monthIndex.toString().padStart(2, '0')}-${day.padStart(2, '0')}`; // Reformatage de la date
//         }

//         $.ajax({
//             url: '/operation-courante-caisse', 
//             method: 'POST',
//             data: {
//                 _token: $('meta[name="csrf-token"]').attr('content'), 
//                 date: formattedDate, // Utiliser la date formatée
//                 numero_dossier: row.numero_dossier,
//                 fact_lettrer: row.fact_lettrer,
//                 compte: row.compte,
//                 libelle: row.libelle,
//                 debit: row.debit,
//                 credit: row.credit,
//                 contre_partie: row.contre_partie,
//                 piece_justificative: row.piece_justificative,
//                 taux_ras_tva: row.taux_ras_tva,
//                 nature_op: row.nature_op,
//                 date_lettrage: row.date_lettrage,
//                 mode_pay: row.mode_pay,
//                 type_journal: selectedJournalCode, // Ajouter le code journal ici
//                 saisie_choisie: getSaisieChoisie() // Ajouter la saisie choisie ici
//             },
//             success: function(response) {
//                 console.log("Données envoyées avec succès :", response);
//                 location.reload();
//             },
//             error: function(xhr, status, error) {
//                 console.error("Erreur lors de l'envoi des données :", error);
//             }
//         });
//     });
// }

// // Fonction pour exporter les données en Excel
// function exportToExcel() {
//     // Récupérer les données du tableau
//     const tableData = tableCaisse.getData();
    
//     // Créer un nouveau classeur
//     const wb = XLSX.utils.book_new();
    
//     // Convertir les données en feuille de calcul
//     const ws = XLSX.utils.json_to_sheet(tableData);
    
//     // Ajouter la feuille de calcul au classeur
//     XLSX.utils.book_append_sheet(wb, ws, "Caisse");
    
//     // Exporter le classeur
//     XLSX.writeFile(wb, "caisse_data.xlsx");
// }

// // Gestionnaire d'événements pour le bouton d'exportation Excel
// $('#export-CaisseExcel').on('click', function() {
//     exportToExcel();
// });

// // Fonction pour exporter les données en PDF
// function exportToPDF() {
//     const { jsPDF } = window.jspdf; // Accéder à jsPDF via l'espace de noms
//     const doc = new jsPDF('l', 'mm', 'a4'); // 'l' pour paysage, 'mm' pour millimètres, 'a4' pour le format A4
//     const tableData = tableCaisse.getData();

//     // Vérifiez si tableData est vide
//     if (tableData.length === 0) {
//         alert("Aucune donnée à exporter.");
//         return;
//     }

//     const pdfTableData = tableData.map(row => [
//         row.date,
//         row.mode_pay,
//         row.compte,
//         row.libelle,
//         row.debit,
//         row.credit,
//         row.facture,
//         row.taux_ras_tva,
//         row.nature_op,
//         row.date_lettrage,
//         row.contre_partie,
//         row.piece_justificative
//     ]);

//     const headers = [
//         "Date", "Mode de paiement", "Compte", "Libellé", 
//         "Débit", "Crédit", "N° facture lettrée", "Taux RAS TVA",
//         "Nature de l'opération", "Date lettrage", "Contre-Partie", "Pièce justificative"
//     ];

//     doc.autoTable({
//         head: [headers],
//         body: pdfTableData,
//     });

//     doc.save("caisse_data.pdf");
// }

// // Gestionnaire d'événements pour le bouton d'exportation PDF
// $('#export-CaissePDF').on('click', function() {
//     exportToPDF();
// });

// // Gestionnaire d'événements pour le bouton de suppression
// $('#delete-row-btn-caisse').on('click', function() {
//     // Récupérer toutes les lignes de la table
//     const rows = tableCaisse.getRows();
//     let selectedIds = [];

//     // Parcourir les lignes pour trouver celles qui sont sélectionnées
//     rows.forEach(row => {
//         const checkbox = row.getCell("selectAll").getElement().querySelector("input");
//         if (checkbox && checkbox.checked) {
//             selectedIds.push(row.getData().id); // Assurez-vous que 'id' est le champ qui contient l'ID de l'opération
//         }
//     });

//     // Si des lignes sont sélectionnées, envoyer les données
//     if (selectedIds.length > 0) {
//         deleteOperations(selectedIds);
//     } else {
//         alert("Veuillez sélectionner au moins une ligne à supprimer.");
//     }
// });

// function deleteOperations(ids) {
//     $.ajax({
//         url: '/operation-courante-caisse', // Assurez-vous que l'URL correspond à la route Laravel
//         method: 'DELETE',
//         data: {
//             _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token pour la sécurité
//             ids: ids // Envoyer les IDs des opérations à supprimer
//         },
//         success: function(response) {
//             console.log("Opérations supprimées avec succès :", response);
//             // Mettre à jour le tableau après la suppression
//             fetchOperations(); // Récupérer à nouveau les opérations pour mettre à jour le tableau
//         },
//         error: function(xhr, status, error) {
//             console.error("Erreur lors de la suppression des opérations :", error);
//             alert("Erreur lors de la suppression des opérations.");
//         }
//     });
// }

// function printTable() {
//     // Récupérer les données du tableau
//     const tableData = tableCaisse.getData();

//     // Vérifier si le tableau contient des données
//     if (tableData.length === 0) {
//         alert("Aucune donnée à imprimer.");
//         return;
//     }

//     // Créer une nouvelle fenêtre
//     const printWindow = window.open('', '', 'height=600,width=800');
    
//     // Construire le contenu HTML pour l'impression
//     let html = `
//         <html>
//         <head>
//             <title>Impression du tableau</title>
//             <style>
//                 table {
//                     width: 100%;
//                     border-collapse: collapse;
//                 }
//                 th, td {
//                     border: 1px solid black;
//                     padding: 8px;
//                     text-align: left;
//                 }
//                 th {
//                     background-color: #f2f2f2;
//                 }
//             </style>
//         </head>
//         <body>
//             <h2>Tableau des opérations</h2>
//             <table>
//                 <thead>
//                     <tr>
//                         <th>Date</th>
//                         <th>Mode de paiement</th>
//                         <th>Compte</th>
//                         <th>Libellé</th>
//                         <th>Débit</th>
//                         <th>Cr édit</th>
//                         <th>N° facture lettrée</th>
//                         <th>Taux RAS TVA</th>
//                         <th>Nature de l'opération</th>
//                         <th>Date lettrage</th>
//                         <th>Contre-Partie</th>
//                         <th>Pièce justificative</th>
//                     </tr>
//                 </thead>
//                 <tbody>
//     `;

//     // Remplir le corps du tableau avec les données
//     tableData.forEach(row => {
//         html += `
//             <tr>
//                 <td>${row.date}</td>
//                 <td>${row.mode_pay}</td>
//                 <td>${row.compte}</td>
//                 <td>${row.libelle}</td>
//                 <td>${row.debit}</td>
//                 <td>${row.credit}</td>
//                 <td>${row.facture}</td>
//                 <td>${row.taux_ras_tva}</td>
//                 <td>${row.nature_op}</td>
//                 <td>${row.date_lettrage}</td>
//                 <td>${row.contre_partie}</td>
//                 <td>${row.piece_justificative}</td>
//             </tr>
//         `;
//     });

//     html += `
//                 </tbody>
//             </table>
//         </body>
//         </html>
//     `;

//     // Écrire le contenu HTML dans la nouvelle fenêtre
//     printWindow.document.write(html);
//     printWindow.document.close(); // Fermer le document pour que le contenu soit rendu
//     printWindow.print(); // Lancer l'impression
//     printWindow.close(); // Fermer la fenêtre après l'impression
// }
