<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de saisie de mouvements</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS de Tabulator -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/css/tabulator.min.css" rel="stylesheet">

    <!-- Bibliothèque XLSX pour exporter en XLSX -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>

    <!-- Bibliothèque jsPDF pour exporter en PDF -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        :root {
            --primary-color: #007bff;
        }


        body {
    background-color: #f9f9f9; /* Light background */
    color: #333;               /* Dark text */
}

.card-header {
    background-color: #007bff; /* Primary color */
    color: #ffffff;             /* White text for contrast */
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    color: #ffffff;             /* Ensure button text is white */
}

.btn-primary:hover {
    background-color: #0056b3; /* Darker shade for hover state */
    border-color: #0056b3;
}






        /* .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        } */

        /* .btn-primary:hover {
            background-color: darken(var(--primary-color), 10%);
            border-color: darken(var(--primary-color), 10%);
        } */

        /* .card-header {
            background-color: var(--primary-color);
            color: #fff;
        } */

        .tabulator {
            border: 1px solid var(--primary-color);
        }

        .tabulator .tabulator-header {
            background-color: var(--primary-color);
            color: #fff;
        }

        .tabulator .tabulator-cell {
            border: 1px solid #ddd;
        }

        .tabulator .tabulator-row {
            background-color: #f9f9f9;
        }

        .tabulator .tabulator-row:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .file-input {
            border: 1px solid var(--primary-color);
        }
    </style>
</head>

@extends('layouts.user_type.auth')

@section('content')
<body>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Formulaire de Saisie de Mouvements</h5>
                </div>
                <div class="card-body">
                    <form id="saisie-form">
                        
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="journal">Journal</label>
                                <select class="form-control" id="journal" name="journal">
                                    <option value="">Sélectionner un journal</option>
                                    <option value="ACH">ACH</option>
                                    <option value="VTE">VTE</option>
                                    <option value="AWB">AWB</option>
                                    <option value="CIH">CIH</option>
                                    <option value="CAI">CAI</option>
                                    <option value="DOT">DOT</option>
                                    <option value="IMMO">IMMO</option>
                                    <option value="OD">OD</option>
                                </select>
                            </div>
                          
                            <div class="col-md-4">
                                <label for="exercice">Exercice en cours</label>
                                <input type="text" class="form-control" id="exercice" name="exercice" value="{{ date('F Y') }}" readonly>
                            </div>
                           
                        </div>
                        </div>

                        <div class="form-group">
                            <label for="saisie-type">Type de saisie</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="saisie_type" id="saisie_mois" value="mois" checked>
                                        <label class="form-check-label" for="saisie_mois">Saisie par mois</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="saisie_type" id="saisie_contrepartie" value="contrepartie">
                                        <label class="form-check-label" for="saisie_contrepartie">Contre partie automatique</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="saisie_type" id="saisie_exercice" value="exercice">
                                        <label class="form-check-label" for="saisie_exercice">Exercice entier</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="saisie_type" id="saisie_libre" value="libre">
                                        <label class="form-check-label" for="saisie_libre">Saisie libre</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Soumettre
                            </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
              
                <div class="card-body">
                    <div id="example-table"></div>
                    <div class="mt-2">
                        <!-- Boutons d'exportation et de vidage -->
                        <button class="btn btn-primary" id="export-xlsx">Exporter en XLSX</button>
                        <button class="btn btn-danger" id="export-pdf">Exporter en PDF</button>
                        <button class="btn btn-secondary" id="clear-table">Vider le tableau</button>
                    </div>
                    <div id="footer-summary" class="mt-3" aria-live="polite" aria-relevant="additions removals">
    <strong>Total Débit:</strong> <span id="total-debit">0.00</span> | 
    <strong>Total Crédit:</strong> <span id="total-credit">0.00</span> | 
    <strong>Solde Débit:</strong> <span id="solde-debit">0.00</span> | 
    <strong>Solde Crédit:</strong> <span id="solde-credit">0.00</span>
</div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript de Tabulator -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/js/tabulator.min.js"></script>
<!-- Bouton "Ajouter ligne" et "Sauvegarder" -->
<div class="mt-2">
    <button class="btn btn-primary" id="add-row">Ajouter ligne</button>
    <button class="btn btn-success" id="save-data">Sauvegarder</button>
</div>

<script>
    // Initialisation du tableau Tabulator
    var table = new Tabulator("#example-table", {
        height: "400px",
        data: [],  // Démarrer avec des lignes vides
        layout: "fitColumns",
        movableColumns: true,
        addRowPos: "top",
        pagination: false,
        columns: [
            {title: "Date", field: "date", editor: "input"},
            {title: "Dossier", field: "dossier", editor: "input"},
            {title: "Facture", field: "facture", editor: "input"},
            {title: "Compte", field: "compte", editor: "input"},
            {title: "Libellé", field: "libelle", editor: "input"},
            {title: "Débit", field: "debit", editor: "number", formatter: "money", bottomCalc: "sum"},
            {title: "Crédit", field: "credit", editor: "number", formatter: "money", bottomCalc: "sum"},
            {title: "Contre partie", field: "contrepartie", editor: "input"},
            {title: "Rubrique TVA", field: "rubrique_tva", editor: "input"},
            {title: "Compte TVA", field: "compte_tva", editor: "input"},
            {title: "Prorata de Déduction (%)", field: "prorata", editor: "number"},
            {title: "Pièce justificative", field: "file", formatter: function() {
                return `<input type="file" class="file-input" />`;
            }},
            {title: "Actions", field: "actions", formatter: function() {
                return "<i class='fas fa-trash-alt delete-btn'></i>";
            }, width: 100}
        ],
        footerElement: "<div><strong>Total Crédit:</strong> <span id='total-credit'></span> | <strong>Total Débit:</strong> <span id='total-debit'></span></div>",
    });


    
   // Ajouter trois lignes vides au tableau
document.getElementById("add-row").addEventListener("click", function() {
    for (let i = 0; i < 3; i++) {
        table.addRow({});
    }
});


    // Fonction de sauvegarde des données
    document.getElementById("save-data").addEventListener("click", function() {
        var data = table.getData();  // Récupérer les données saisies dans le tableau
        console.log("Données sauvegardées :", data);
        // Envoyer les données au backend avec AJAX
        fetch('/sauvegarder-mouvements', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        }).then(response => response.json()).then(result => {
            alert("Données sauvegardées avec succès !");
        }).catch(error => {
            console.error("Erreur lors de la sauvegarde :", error);
            alert("Échec de la sauvegarde.");
        });
    });

    // Supprimer une ligne au clic sur l'icône de suppression
    document.getElementById("example-table").addEventListener("click", function(event) {
        if(event.target.classList.contains("delete-btn")) {
            var row = table.getRow(event.target.closest(".tabulator-row").getAttribute("data-row"));
            row.delete();
        }
    });




    // Fonction de suppression de ligne avec confirmation
$("#example-table").on("click", ".delete-btn", function() {
    if (confirm("Êtes-vous sûr de vouloir supprimer cette ligne ?")) {
        var row = table.getRow($(this).closest(".tabulator-row").data("row"));
        row.delete();
    }
});

   

    // Fonction pour vider le tableau
    function clearTable() {
        table.setData([]);
    }

    // Bouton pour vider le tableau
    document.getElementById('clear-table').addEventListener('click', clearTable);

    // Fonction pour mettre à jour les totaux
    function updateTotals() {
        var totalDebit = table.getData().reduce((sum, row) => sum + (row.debit || 0), 0).toFixed(2);
        var totalCredit = table.getData().reduce((sum, row) => sum + (row.credit || 0), 0).toFixed(2);
        var soldeDebit = (totalDebit - totalCredit).toFixed(2);

        document.getElementById('total-debit').innerText = totalDebit;
        document.getElementById('total-credit').innerText = totalCredit;
        document.getElementById('solde-debit').innerText = soldeDebit;
        document.getElementById('solde-credit').innerText = soldeDebit;
    }

    // Exporter en XLSX
    document.getElementById('export-xlsx').addEventListener('click', function() {
        table.download("xlsx", "data.xlsx", {});
    });

    // Exporter en PDF
    document.getElementById('export-pdf').addEventListener('click', function() {
        table.downloadToTab("pdf");
    });
</script>

<!-- Bootstrap JS (optionnel) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
@endsection
