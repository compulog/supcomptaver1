


    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
        // loadRubriquesTVA(); // Charger les rubriques TVA au chargement de la page
        loadFournisseurs(); // Charger les fournisseurs lors du chargement de la page
    
        // Ouvrir le modal d'ajout de fournisseur
        $('#openModal').click(function() {
            $('#fournisseurId').val(''); // Réinitialiser l'ID
            $('#fournisseurForm')[0].reset(); // Réinitialiser le formulaire
            $('#fournisseurModal').modal('show'); // Afficher le modal
        });
    
        // Soumettre le formulaire
        // $('#fournisseurForm').submit(function(event) {
        //     event.preventDefault(); // Empêcher le rechargement de la page
    
        //     $.ajax({
        //         url: $(this).attr('action'),
        //         type: 'POST',
        //         data: $(this).serialize(), // Sérialiser le formulaire
        //         success: function(response) {
        //             if (response.success) {
        //                 // addFournisseurToTabulator(response.fournisseur); // Ajouter le fournisseur au tableau
        //                 $('#fournisseurForm')[0].reset(); // Réinitialiser le formulaire
        //                 $('#fournisseurModal').modal('hide'); // Fermer le modal
        //             } else {
        //                 alert('Une erreur est survenue lors de l\'ajout du fournisseur.');
        //             }
        //         },
        //         error: function(xhr) {
        //             console.error(xhr.responseText);
        //             alert('Une erreur est survenue lors de l\'ajout du fournisseur.');
        //         }
        //     });
        // });
    });
    
    // Charger les rubriques TVA
    // function loadRubriquesTVA() {
    //     $.ajax({
    //         url: "./rubrique_tva",
    //         type: "GET",
    //         dataType: "json",
    //         success: function(response) {
    //             var $lignes = '<option value="null">Sélectionner une rubrique</option>';
    //             $.each(response.loadRubriquesTVA, function(key, item) {
    //                 $lignes += `<option value="${item.id}">${item.Num_racines} | ${item.Nom_racines} | ${item.Taux}</option>`;
    //             });
    //             $("#edit-rubrique_tva").html($lignes); // Remplir le select avec les rubriques TVA
    //         },
    //     });
    // }
    
    
    // Fonction pour charger les fournisseurs dans Tabulator
    function loadFournisseurs() {
        const table = new Tabulator("#fournisseur-table", {
            height: "311px", // Hauteur du tableau
            layout: "fitData", // Ajuste les colonnes à la largeur du tableau
            columns: [ // Définir les colonnes
                { title: "ID", field: "id", width: 50 },
                { title: "Compte", field: "compte" },
                { title: "Intitulé", field: "intitule" },
                { title: "Identifiant Fiscal", field: "identifiant_fiscal" },
                { title: "ICE", field: "ice" },
                { title: "Contre Partie", field: "contre_partie" },
                { title: "Nature de l'Opération", field: "nature_operation" },
                { title: "Rubrique TVA", field: "rubrique_tva" },
                { title: "Désignation", field: "designation" },
                { title: "Actions", field: "actions", formatter: function(cell) {
                    const id = cell.getRow().getData().id;
                    return `<button class="btn btn-warning btn-sm" onclick="editFournisseur(${id})">Modifier</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteFournisseur(${id})">Supprimer</button>`;
                }},
            ],
        });
   
    
        // Charger les données du tableau
        fetch('/fournisseurs')
            .then(response => response.json())
            .then(data => {
                table.setData(data); // Charger les données dans le tableau
            })
            .catch(error => console.error('Erreur:', error));
   
    
   }
    
   
   
    


    $(document).ready(function () {
        // ******Ajouter fournisseur******
        $("#fournisseurForm").on("submit", function (e) {
          e.preventDefault();
          var $this = jQuery(this);
          var formData = jQuery($this).serializeArray();
          jQuery.ajax({
            url: $this.attr("action"),
            type: $this.attr("method"), // Le nom du fichier indiqué dans le formulaire
            data: formData, // Je sérialise les données (j'envoie toutes les valeurs présentes dans le formulaire)
            // dataFilter: 'json', //forme data
            success: function (response) {
              // Je récupère la réponse du fichier PHP
              toastr.options = {
                progressBar: true,
                closeButton: true,
              };
              toastr.success(response.message, { timeOut: 12000 });
              table_fournisseur();
            //   viderchamp();
            },
            error: function (response) {
              toastr.options = {
                progressBar: true,
                closeButton: true,
              };
              toastr.error("Merci de vérifier les champs");
            },
          });
        });
    });