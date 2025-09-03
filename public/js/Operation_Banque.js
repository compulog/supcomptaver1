var tableBanque;
function getSaisieChoisie() {
    return $('input[name="filter-Banque"]:checked').val(); // Récupérer la valeur du bouton radio sélectionné

}
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
        'Date', 'Mode de paiement', 'Compte', 'Libellé', 
        'Débit', 'Crédit', 'N° facture lettrée', 'Taux RAS TVA',
        'Nature de l\'opération', 'Date lettrage', 'Contre-Partie'
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
    
    tableBanque.setData(rows); // Mettre à jour les données de Tabulator
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
            mode_paiement: columns[1] || '',
            compte: columns[2] || '',
            libelle: columns[3] || '',
            debit: columns[4] || '',
            credit: columns[5] || '',
            facture: columns[6] || '',
            taux_ras_tva: columns[7] || '',
            nature_operation: columns[8] || '',
            date_lettrage: columns[9] || '',
            contre_partie: columns[10] || '',
        };
        rows.push(row);
    });

    tableBanque.setData(rows); // Mettre à jour les données de Tabulator
}

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

});

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
$(document).ready(function() {
    // Gestionnaire d'événements pour l'onglet Banque
    $('.tab[data-tab="Banque"]').on('click', function() {
        // Afficher le contenu de l'onglet Banque
        $('.tab-content').removeClass('active');
        $('#Banque').addClass('active');

        tableBanque = new Tabulator("#tableBanque", {     
                   data: [{ // Ligne vide pour une nouvelle opération
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
            height: "650px", // Hauteur du tableau
            layout: "fitColumns", // Ajuste la largeur des colonnes
            columns: [
           { 
    title: "Date", 
    field: "date", 
    sorter: "date", 
    width: 100, 
    editor: customDateEditor, // Utiliser l'éditeur personnalisé
    headerFilter: "input",
    cellEdited: function(cell) {
        var dateValue = cell.getValue(); // Récupérer la valeur de la date saisie
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

               {
  title: "Mode de paiement",
  field: "mode_pay",
    editor: "list",
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
    },
},
     {
    title: "Compte", 
    field: "compte", 
    width: 100, 
    editor: "list", 
    editorParams: {
        autocomplete: true,
        listOnEmpty: true,
        values: planComptable.reduce((acc, c) => {
        acc[c.id] = `${c.compte} - ${c.intitule}`;
        return acc;
    }, {}),
        clearable: true,
        verticalNavigation: "editor",
    },
    headerFilter: "input",
    formatter: function(cell) {
        var compteId = cell.getValue();
        var compte = planComptable.find(c => c.id == compteId);
        return compte ? compte.compte : " ";
    },
    cellEdited: function(cell) {
        var compteId = cell.getValue();
        var compte = planComptable.find(c => c.id == compteId);
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

        // Cas 342
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

        // Cas 441
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


                    
                { title: "Libellé", field: "libelle", width: 100, editor: "input", headerFilter: "input" },
                {
                title: "Débit",
                field: "debit",
                sorter: "number",
                width: 100,
                editor: customNumberEditor,
                headerFilter: "input",
                cellEdited: function(cell) {
                    const row = cell.getRow();
                    const debitCell = row.getCell("debit");
                    const factLettrerCell = row.getCell("fact_lettrer");

                    debitCell.getElement().addEventListener("keydown", function(event) {
                    if (event.key === "Enter") {
                        setTimeout(function() {
                        factLettrerCell.edit();
                        }, 100); // ajouter un délai de 100ms
                    }
                    });
                }
                },
                { title: "Crédit", field: "credit", sorter: "number", width: 100, editor: customNumberEditor, headerFilter: "input" },
               
               
                { title: "N° facture lettrée", field: "fact_lettrer", width: 100, editor: customNumberEditor , headerFilter: "input"},
             
             
             
                {
    title: "Taux RAS TVA",
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

                {
                    title: "Nature de l'opération", 
                    field: "nature_op", 
                    width: 100, 
                    editor: customListEditor2, 
                    headerFilter: "input"
                },
                { title: "Date lettrage", field: "date_lettrage", sorter: "date", width: 100, editor: customNumberEditor2 , headerFilter: "input"},
                // {
                //     title: "Contre-Partie", 
                //     field: "contre_partie", 
                //     width: 100, 
                //     editor: "list", 
                //     editorParams: {
                //         values: planComptable
                //         .filter(compte => compte.compte.startsWith('514') || compte.compte.startsWith('554'))
                //         .reduce((acc, compte) => {
                //                 acc[compte.id] = compte.compte; // Ajoutez ici l'ID et le compte dans l'objet
                //                 return acc;
                //             }, {}),
                //         clearable: true,
                //         verticalNavigation: "editor", // Permet de naviguer verticalement dans la liste
                //     },
                //     headerFilter: "input"
                // },
                {
                title: "Contre-Partie", 
                field: "contre_partie", 
                width: 100, 
                editor: "textarea", // Remplace la liste déroulante par un champ de texte
                editable: false, // Rend le champ non modifiable
                headerFilter: "input",
                },  
                {
                    title: "Pièce justificative", 
                    field: "piece_justificative", 
                    width: 200,
                    headerFilter: "input",
                    formatter: function(cell) {
                         var icon = "<i class='fas fa-paperclip upload-icon' title='Choisir un fichier'></i>";
                        var input = "<input type='text' class='selected-file-input' id='selectedFile' readonly value='" + (cell.getValue() || '') + "'>";
                         return input + icon ;
                        // return input;

                    },
                    cellClick: function(e, cell) {
                        $('#files_banque_Modal').show();
                        $('#confirmBtn_Banque').data('cell', cell);  
                    }
                },
               { 
        title: "<input type='checkbox' id='selectAll'>", 
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
    }   ],

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
    });
});
fetchOperations();
    // Fermer la modale lorsqu'on clique sur la croix
    $('.close-btn').on('click', function() {
        $('#files_banque_Modal').hide();
    });

    // Fermer la modale si on clique en dehors de la modale
    $(window).on('click', function(event) {
        if ($(event.target).is('#files_banque_Modal')) {
            $('#files_banque_Modal').hide();
        }
    });

    // Gestionnaire d'événements pour le bouton "Confirmer"
    $('#confirmBtn_Banque').on('click', function() {
        var selectedFileName = $('.file-button.selected').data('filename');
        if (selectedFileName) {
            var cell = $(this).data('cell'); // Récupérer la cellule à mettre à jour
            cell.setValue(selectedFileName); // Mettre à jour la cellule avec le nom du fichier

            // Lire le fichier à partir de l'URL
            var fileUrl = '/files/' + selectedFileName; // Assurez-vous que cette URL est correcte
            readFile(fileUrl);
        } else {
            alert("Veuillez sélectionner un fichier avant de confirmer.");
        }
        $('#files_banque_Modal').hide();
    });

    // Gestionnaire d'événements pour les boutons de fichier
    $('.file-button').on('click', function() {
        $('.file-button').removeClass('selected'); // Retirer la classe 'selected' de tous les boutons
        $(this).addClass('selected'); // Ajouter la classe 'selected' au bouton cliqué
    });

    // document.getElementById('file-input').addEventListener('change', function(event) {
    //     const file = event.target.files[0];
    //     if (file) {
    //         if (file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || file.type === 'application/vnd.ms-excel') {
    //             readExcelFile(file);
    //         } else if (file.type === 'application/pdf') {
    //             readPdfFile(file);
    //         } else {
    //             alert('Veuillez sélectionner un fichier Excel ou PDF valide.');
    //         }
    //     }
    // });


        
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
// function customListEditor(cell, onRendered, success, cancel) {
//     const input = document.createElement("select");
//     input.style.width = "100%";

//     // Remplir le select avec les options
//     // const options = ["1.Espèces", "2.Chèques", "3.Prélèvements", "4.Virement", "5.Effet",  "6.Compensations", "7.Autres"]; 
//     const options = ["2.Chèques", "3.Prélèvements", "4.Virement", "5.Effet",  "6.Compensations", "7.Autres"]; 
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

function customListEditor1(cell, onRendered, success, cancel) {
    const input = document.createElement("select");
    input.style.width = "100%";

    // Remplir le select avec les options
    const options = ["0", "75", "100"]; // Remplacez par vos options
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
function customListEditor2(cell, onRendered, success, cancel) {
    const input = document.createElement("select");
    input.style.width = "100%";

    // Récupérer le compte sélectionné dans la ligne actuelle
    const compteId = cell.getRow().getCell("compte").getValue();
    const compte = planComptable.find(c => c.id == compteId);
    
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



 //   $(document).on('keydown', function(e) {
//     if (e.key === "Enter") {
//         const rows = tableBanque.getRows();
//         let selectedData = [];

//         rows.forEach(row => {
//             const cell = row.getCell("selected");
//             if (cell) {
//                 const checkbox = cell.getElement().querySelector("input");
//                 if (checkbox && checkbox.checked) {
//                     selectedData.push(row.getData());
//                 }
//             }
//         });

//         console.log("Données sélectionnées :", selectedData);

//         if (selectedData.length > 0) {
//             sendDataToController(selectedData);
//         } else {
//             console.log("Aucune ligne sélectionnée.");
//         }
//     }
// });

  // Gestionnaire d'événements pour la touche "Entrée"
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

let isSending = false;

function sendDataToController(data) {
    // Récupérer le code journal sélectionné
    var selectedJournalCode = $('#journal-Banque').val(); // Récupérer la valeur du journal sélectionné
    console.log("Code journal sélectionné :", selectedJournalCode); // Pour déboguer
    isSending = true; // Active le verrou
    let completedRequests = 0;

    console.log("Données à envoyer :", data); // Ajoutez cette ligne pour déboguer
    data.forEach(row => {
        // Reformater la date au format YYYY-MM-DD
        let formattedDate = '';
        if (row.date) {
            const [day, month, year] = row.date.split('/');
            const monthIndex = new Date(Date.parse(month + " 1, 2020")).getMonth() + 1; // Convertir le mois en index numérique
            formattedDate = `${year}-${monthIndex.toString().padStart(2, '0')}-${day.padStart(2, '0')}`; // Reformatage de la date
        }
        // console.log("contre partie :", row.contre_partie);
        $.ajax({
            url: '/operation-courante-banque', 
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'), 
                date: formattedDate, // Utiliser la date formatée
                numero_dossier: row.numero_dossier,
                fact_lettrer: row.fact_lettrer,
                compte: row.compte,
                libelle: row.libelle,
                debit: row.debit,
                credit: row.credit,
                contre_partie: row.contre_partie,
                piece_justificative: row.piece_justificative,
                taux_ras_tva: row.taux_ras_tva,
                nature_op: row.nature_op,
                date_lettrage: formattedDate,
                mode_pay: row.mode_pay,
                type_journal: selectedJournalCode, // Ajouter le code journal ici
                saisie_choisie: getSaisieChoisie() // Ajouter la saisie choisie ici
            },
            success: function(response) {
                completedRequests++;
                if (completedRequests === data.length) {
                        // location.reload();
                        fetchOperations();
                        isSending = false; // Reset au reload ça ne sert plus, mais juste au cas où
                        // Après le fetch, déplacer le focus vers le champ date de la ligne vide
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
                console.error("Erreur AJAX :", xhr, status, error); // Pour déboguer
            }
        });
    });
}


// Fonction pour récupérer les opérations courantes
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
$('#periode-Banque').on('change', function() {
    fetchOperations(); // Relance le filtrage à chaque changement
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

// Ajouter un gestionnaire d'événements pour détecter les changements dans le select de journal
$('#journal-Banque').on('change', function() {
    var selectedJournalCode = $(this).val();
    var selectedOption = $(this).find('option:selected');
    var intitule = selectedOption.data('intitule');
    var tabId = $(this).attr('id').replace('journal-', 'filter-intitule-');
    $('#' + tabId).val(intitule ? intitule : '');
    fetchOperations(selectedJournalCode);
});

function readFile(fileUrl) {
    // Utiliser fetch pour récupérer le fichier
    fetch(fileUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération du fichier');
            }
            return response.blob(); // Convertir la réponse en blob
        })
        .then(blob => {
            const fileType = blob.type;
            const fileReader = new FileReader();

            fileReader.onload = function() {
                if (fileType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || 
                    fileType === 'application/vnd.ms-excel') {
                    const data = new Uint8Array(this.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const sheet = workbook.Sheets[workbook.SheetNames[0]];
                    const sheetData = XLSX.utils.sheet_to_json(sheet, { header: 1 });
                    displayExcelData(sheetData);
                } else if (fileType === 'application/pdf') {
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
                } else {
                    alert('Type de fichier non pris en charge.');
                }
            };

            fileReader.readAsArrayBuffer(blob); // Lire le blob comme un ArrayBuffer
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
}





// Gestionnaire d'événements pour le bouton de suppression
// Gestionnaire d'événements pour le bouton de suppression
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

// Fonction pour exporter les données en Excel
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

// Gestionnaire d'événements pour le bouton d'exportation Excel
$('#export-BanqueExcel').on('click', function() {
    exportToExcel();
});

// Fonction pour exporter les données en PDF
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

// Gestionnaire d'événements pour le bouton d'exportation PDF
$('#export-BanquePDF').on('click', function() {
    exportToPDF();
});




// Fonction pour mettre à jour le footer
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
    document.getElementById('solde-actuel').innerText = soldeActuel.toFixed(2) || "0.00";
    document.getElementById('solde-initial-db').innerText = soldeInitialDB.toFixed(2) || "0.00";
    document.getElementById('solde-initial-cr').innerText = soldeInitialCR.toFixed(2) || "0.00";
    document.getElementById('solde-debiteur').innerText = soldeDebiteur.toFixed(2) || "0.00";
    document.getElementById('solde-crediteur').innerText = soldeCrediteur.toFixed(2) || "0.00";
}

// Ajouter un événement de changement sur le checkbox du titre
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

// function generatePiece(dateStr, codeJournal) {
//   const dateTime = luxon.DateTime.fromISO(dateStr);
//   if (!dateTime.isValid) {
//     dateTime = luxon.DateTime.fromFormat(dateStr, "yyyy-MM-dd HH:mm:ss");
//   }
//   if (!dateTime.isValid) {
//     dateTime = luxon.DateTime.fromFormat(dateStr, "yyyy-MM-dd");
//   }
//   if (!dateTime.isValid) {
//     throw new Error(`Date invalide pour génération de pièce : ${dateStr}`);
//   }
//   const month = dateTime.toFormat("MM");
//   const year = dateTime.toFormat("yy");
//   const prefix = `P${month}${year}${codeJournal}`;
//   const nextNumber = (compteurMap[prefix] || 0) + 1;
//   compteurMap[prefix] = nextNumber;
//   return `${prefix}${String(nextNumber).padStart(4, '0')}`;
// }

    