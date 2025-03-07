
$(document).ready(function() {
    // Récupérer la date de l'exercice depuis l'attribut data-exercice-date
    var exerciceDate = $('#exercice-date').data('exercice-date');
    var exerciceYear = new Date(exerciceDate).getFullYear(); // Extraire l'année de la date
    
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
});

$(document).ready(function() {
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
});


$(document).ready(function() {
    // Gestionnaire d'événements pour l'onglet Caisse
    $('.tab[data-tab="Caisse"]').on('click', function() {
        // Afficher le contenu de l'onglet Caisse
        $('.tab-content').removeClass('active');
        $('#Caisse').addClass('active');

        // Initialiser Tabulator pour l'onglet Caisse
        var tableCaisse = new Tabulator("#table-Caisse", {
            height: "311px", // Hauteur du tableau
            layout: "fitData", // Ajuste la largeur des colonnes
            rowheight:"30px",
            columns: [ 
                // Colonne de sélection "Sélectionner tout"
                
                { title: "Date", field: "date", sorter: "date", width: 100, editor: "date", headerFilter: "input" },
                { title: "Mode de paiement", field: "mode_paiement", width: 100, editor: "input", headerFilter: "input",
                    editor: "list",
                    editorParams: {
                        values: ["Espèces", "Chèques", "Virement", "Effet", "Prélèvements", "Compensations", "Autres"],
                        clearable: true,
                        verticalNavigation: "editor",
                    }, },
                { title: "Compte", field: "compte", width: 100, editor: "input", headerFilter: "input" },
                { title: "Libellé", field: "libelle", width: 100, editor: "input", headerFilter: "input" },
                { title: "Débit", field: "debit", sorter: "number", width: 100, editor: "input", headerFilter: "input" },
                { title: "Crédit", field: "credit", sorter: "number", width: 100, editor: "input", headerFilter: "input" },
                { title: "N° facture lettrée", field: "facture", width: 100, editor: "input" , headerFilter: "input"},
                { title: "Taux RAS TVA", field: "taux_ras_tva", width: 100, editor: "input", headerFilter: "input" },
                { title: "Nature de l'opération", field: "nature_operation", width: 100, editor: "input", headerFilter: "input" },
                { title: "Date lettrage", field: "date_lettrage", sorter: "date", width: 100, editor: "input" , headerFilter: "input"},
                { title: "Contre-Partie", field: "contre_partie", width: 100, editor: "input", headerFilter: "input" },
                {
                    title: "Pièce justificative", 
                    field: "piece_justificative", 
                    width: 200, // Ajustez la largeur selon votre besoin
                    headerFilter: "input",
                    formatter: function(cell, formatterParams, onRendered) {
                        // Créer une icône de trombone pour chaque ligne et un champ input pour afficher le nom du fichier sélectionné
                        var icon = "<i class='fas fa-paperclip upload-icon' title='Choisir un fichier'></i>";
                        var input = "<input type='text' class='selected-file-input' id='selectedFile' placeholder='Nom du fichier sélectionné' readonly>";
                        
                        // Retourner l'icône + input dans la cellule
                        return icon + input;
                    },
                    cellClick: function(e, cell) {
                        // Afficher la modale lors du clic sur la cellule
                        $('#fileModal').show();
                        
                        // Stocker la cellule dans un attribut data pour pouvoir la mettre à jour plus tard
                        $('#confirmBtn').data('cell', cell);  
                    }
                },
                            
                
                                {
                    title: "<input type='checkbox' id='selectAll'>", 
                    field: "selectAll", 
                    width: 40, 
                    formatter: function(cell, formatterParams, onRendered) {
                        return "<input type='checkbox' class='select-row'>";
                    },
                    headerSort: false,
                    headerFilter: false,
                    align: "center",
                    cellClick: function(e, cell) {
                        // Lorsque la case "Sélectionner tout" est cliquée
                        var isChecked = $("#selectAll").prop("checked");
                        tableCaisse.getRows().forEach(function(row) {
                            row.getCell("select").getElement().querySelector("input").checked = isChecked;
                        });
                    }
                },
            ],
            data: [ // Ajouter une ligne vide
                {
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
                }
            ],
            // Rendre toutes les cellules éditables
            editable: true,

        });

        // Mettre à jour la case "Sélectionner tout" en fonction des cases des lignes
        $('#selectAll').change(function() {
            var isChecked = $(this).prop('checked');
            tableCaisse.getRows().forEach(function(row) {
                row.getCell("select").getElement().querySelector("input").checked = isChecked;
            });
        });

        // Détecter les changements dans les cases des lignes
        $("#table-Caisse").on('change', '.select-row', function() {
            var allChecked = true;
            $(".select-row").each(function() {
                if (!$(this).prop('checked')) {
                    allChecked = false;
                }
            });
            $('#selectAll').prop('checked', allChecked);
        });
    });
});


// Fermer la modale lorsqu'on clique sur la croix
$('.close-btn').on('click', function() {
    $('#fileModal').hide();
});

// Fermer la modale si on clique en dehors de la modale
$(window).on('click', function(event) {
    if ($(event.target).is('#fileModal')) {
        $('#fileModal').hide();
    }
});
$(document).ready(function() {
    // Fermer la modale lorsqu'on clique sur la croix
    $('.close-btn').on('click', function() {
        $('#fileModal').hide();
    });

    // Fermer la modale si on clique en dehors de la modale
    $(window).on('click', function(event) {
        if ($(event.target).is('#fileModal')) {
            $('#fileModal').hide();
        }
    });

    // Gestionnaire d'événements pour le bouton "Confirmer"
    $('#confirmBtn').on('click', function() {
        // Récupérer le nom du fichier sélectionné
        var selectedFileName = $('.file-button.selected').data('filename');

        if (selectedFileName) {
            // Mettre le nom du fichier dans le champ d'entrée
            $('#selectedFile').val(selectedFileName);
        } else {
            alert("Veuillez sélectionner un fichier avant de confirmer.");
        }

        // Fermer la modale
        $('#fileModal').hide();
    });

    // Gestionnaire d'événements pour les boutons de fichier
    $('.file-button').on('click', function() {
        // Supprimer la classe 'selected' de tous les boutons
        $('.file-button').removeClass('selected');
        // Ajouter la classe 'selected' au bouton cliqué
        $(this).addClass('selected');
    });
});
