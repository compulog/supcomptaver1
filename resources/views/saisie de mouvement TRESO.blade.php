<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de saisie de mouvements</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS de Tabulator -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/css/tabulator.min.css" rel="stylesheet">
</head>

@extends('layouts.user_type.auth')

@section('content')
<body>

<div class="container-fluid mt-5">
    <div class="row">
        <!-- Formulaire de saisie -->
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Formulaire de Saisie de Mouvements</h5>
                </div>
                <div class="card-body">
                    <!-- Début du formulaire -->
                    <form id="saisie-form">
                        <!-- Aligner Journal, Mois, Année sur la même ligne -->
                        <div class="form-group row">
                            <!-- Journal (Liste déroulante) -->
                            <div class="col-md-4">
                                <label for="journal">Journal</label>
                                <select class="form-control" id="journal" name="journal">
                                    <option value="">Sélectionner un journal</option>
                                    <option value="journal1">Journal 1</option>
                                    <option value="journal2">Journal 2</option>
                                    <option value="journal3">Journal 3</option>
                                    <option value="journal4">Journal 4</option>
                                </select>
                            </div>

                            <!-- Mois (Liste déroulante) -->
                            <div class="col-md-4">
                                <label for="mois">Mois</label>
                                <select class="form-control" id="mois" name="mois">
                                    <option value="">Sélectionner un mois</option>
                                    <option value="01">Janvier</option>
                                    <option value="02">Février</option>
                                    <option value="03">Mars</option>
                                    <option value="04">Avril</option>
                                    <option value="05">Mai</option>
                                    <option value="06">Juin</option>
                                    <option value="07">Juillet</option>
                                    <option value="08">Août</option>
                                    <option value="09">Septembre</option>
                                    <option value="10">Octobre</option>
                                    <option value="11">Novembre</option>
                                    <option value="12">Décembre</option>
                                </select>
                            </div>

                            <!-- Année (Liste déroulante) -->
                            <div class="col-md-4">
                                <label for="annee">Année</label>
                                <select class="form-control" id="annee" name="annee">
                                    <option value="">Sélectionner une année</option>
                                    @php
                                        $currentYear = date('Y');
                                        $startYear = $currentYear - 10;
                                        $endYear = $currentYear + 10;
                                    @endphp
                                    @for ($year = $startYear; $year <= $endYear; $year++)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Choix du type de saisie (Radio buttons) -->
                        <div class="form-group">
                            <label for="saisie-type">Type de saisie</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="saisie_type" id="saisie_mois" value="mois" checked>
                                        <label class="form-check-label" for="saisie_mois">
                                            Saisie par mois
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="saisie_type" id="saisie_contrepartie" value="contrepartie">
                                        <label class="form-check-label" for="saisie_contrepartie">
                                            Contre partie automatique
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="saisie_type" id="saisie_exercice" value="exercice">
                                        <label class="form-check-label" for="saisie_exercice">
                                            Exercice entier
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="saisie_type" id="saisie_libre" value="libre">
                                        <label class="form-check-label" for="saisie_libre">
                                            Saisie libre
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bouton de soumission -->
                        <button type="submit" class="btn btn-primary mt-3">Soumettre</button>
                    </form>
                    <!-- Fin du formulaire -->
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tableau -->
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Tableau de saisie de mouvement</h5>
                </div>
                <div class="card-body">
                    <div id="example-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript de Tabulator -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/js/tabulator.min.js"></script>

<script>
    // Initialisation de Tabulator avec données de test et possibilité d'édition
    var table = new Tabulator("#example-table", {
        height: "400px", // Hauteur de la table
        data: [ // Données de test
            {id: 1, date: "2023-10-10", dossier: "D123", facture: "F001", compte: "Compte A", libelle: "", debit: 1500, credit: 0, contrepartie: "Compte B", rubrique_tva: "T1", taux_tva: 20, compte_tva: "Compte C", prorata: 100, file: ""}, 
            {id: 2, date: "2023-10-11", dossier: "D124", facture: "F002", compte: "Compte D", libelle: "", debit: 0, credit: 2000, contrepartie: "Compte E", rubrique_tva: "T2", taux_tva: 10, compte_tva: "Compte F", prorata: 50, file: ""}, 
            {id: 3, date: "2023-10-12", dossier: "D125", facture: "F003", compte: "Compte G", libelle: "", debit: 1000, credit: 0, contrepartie: "Compte H", rubrique_tva: "T3", taux_tva: 5, compte_tva: "Compte I", prorata: 75, file: ""}
        ],
        layout: "fitColumns", // Adapter les colonnes à la taille de l'écran
        pagination: true, // Activer la pagination
        paginationSize: 5, // Nombre d'éléments par page
        columns: [ // Définir les colonnes
            {title: "Date", field: "date", sorter: "date", width: 150, editor: "input"},
            {title: "N° Dossier", field: "dossier", sorter: "string", width: 150, editor: "input"},
            {title: "N° Facture", field: "facture", sorter: "string", width: 150, editor: "input"},
            {title: "Compte", field: "compte", sorter: "string", width: 150, editor: "input"},
            {title: "Libellé", field: "libelle", sorter: "string", width: 200, editor: "input", 
                formatter: function(cell, formatterParams, onRendered) {
                    // Récupérer les valeurs de N° Dossier, N° Facture, et Compte pour les afficher dans le Libellé
                    var dossier = cell.getRow().getData().dossier || "";
                    var facture = cell.getRow().getData().facture || "";
                    var compte = cell.getRow().getData().compte || "";
                    
                    // Retourner la concaténation des valeurs
                    return `F ${facture} ${dossier}    ${compte}`;
                }
            },
            {title: "Débit", field: "debit", sorter: "number", align: "center", width: 100, editor: "number", topCalc: "sum", topCalcParams: {precision: 2}},
            {title: "Crédit", field: "credit", sorter: "number", align: "center", width: 100, editor: "number", topCalc: "sum", topCalcParams: {precision: 2}},
            {title: "Contre-Partie", field: "contrepartie", sorter: "string", width: 150, editor: "input"},
            {title: "Rubrique TVA", field: "rubrique_tva", sorter: "string", width: 150, editor: "input"},
            {title: "Taux TVA (%)", field: "taux_tva", sorter: "number", align: "center", width: 100, editor: "number"},
            {title: "Compte TVA", field: "compte_tva", sorter: "string", width: 150, editor: "input"},
            {title: "Prorata de Déduction (%)", field: "prorata", sorter: "number", align: "center", width: 150, editor: "number"},
            {title: "Pièce Justificative", field: "file", formatter: function(cell, formatterParams) {
                return `<input type="file" class="file-input" />`;
            }, width: 150}
        ],
        footerElement: "<div><strong>Total Crédit:</strong> <span id='total-credit'></span> | <strong>Total Débit:</strong> <span id='total-debit'></span> | <strong>Solde Débit:</strong> <span id='solde-debit'></span> | <strong>Solde Crédit:</strong> <span id='solde-credit'></span></div>",
        editable: true,
        // Calculer le solde total Debit et Crédit
        rowFormatter: function(row) {
            var totalDebit = table.getData().reduce((sum, row) => sum + (row.debit || 0), 0).toFixed(2);
            var totalCredit = table.getData().reduce((sum, row) => sum + (row.credit || 0), 0).toFixed(2);
            var soldeDebit = (totalDebit - totalCredit).toFixed(2);
            
            document.getElementById('total-debit').innerText = totalDebit;
            document.getElementById('total-credit').innerText = totalCredit;
            document.getElementById('solde-debit').innerText = soldeDebit;
            document.getElementById('solde-credit').innerText = soldeDebit;
        }
    });
</script>

<!-- Bootstrap JS (optionnel) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
@endsection
