

<!-- <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lire PDF et ajouter à Tabulator</title>
    <link href="https://unpkg.com/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://unpkg.com/pdfjs-dist@2.10.377/build/pdf.js"></script>
    <script src="https://unpkg.com/pdfjs-dist@2.10.377/build/pdf.worker.js"></script>
    <script src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>
</head>
<body>

<input type="file" id="file-input" accept="application/pdf">
<div id="table"></div>

<script>
    // Initialiser Tabulator
    var table = new Tabulator("#table", {
        height: "311px",
        layout: "fitData",
        columns: [
            {title: "Date", field: "date", width: 100},
            {title: "Mode de paiement", field: "mode_paiement", width: 150},
            {title: "Compte", field: "compte", width: 100},
            {title: "Libellé", field: "libelle", width: 200},
            {title: "Débit", field: "debit", width: 100},
            {title: "Crédit", field: "credit", width: 100},
            {title: "N° facture lettrée", field: "facture", width: 150},
            {title: "Taux RAS TVA", field: "taux_ras_tva", width: 100},
            {title: "Nature de l'opération", field: "nature_operation", width: 150},
            {title: "Date lettrage", field: "date_lettrage", width: 100},
            {title: "Contre-Partie", field: "contre_partie", width: 150},
        ],
    });

    // Configuration de pdf.js
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://unpkg.com/pdfjs-dist@2.10.377/build/pdf.worker.js';

    document.getElementById('file-input').addEventListener('change', function(event) {
        var file = event.target.files[0];
        if (file && file.type === "application/pdf") {
            var reader = new FileReader();
            reader.onload = function() {
                var typedarray = new Uint8Array(this.result);
                pdfjsLib.getDocument(typedarray).promise.then(function(pdf) {
                    var textPromises = [];
                    for (var i = 1; i <= pdf.numPages; i++) {
                        textPromises.push(pdf.getPage(i).then(function(page) {
                            return page.getTextContent().then(function(textContent) {
                                return textContent.items.map(function(item) {
                                    return item.str;
                                }).join(' ');
                            });
                        }));
                    }
                    Promise.all(textPromises).then(function(pagesText) {
                        var fullText = pagesText.join('\n');
                        console.log("Texte brut extrait : ", fullText);  // Debug: afficher le texte complet du PDF
                        var cleanedText = cleanText(fullText);
                        console.log("Texte nettoyé : ", cleanedText);  // Debug: afficher le texte nettoyé
                        var data = extractData(cleanedText);
                        if (data) {
                            console.log("Données ajoutées : ", data);  // Debug: afficher les données avant de les ajouter à Tabulator
                            table.addData([data]);  // Ajoute les données à Tabulator
                        } else {
                            alert("Aucune donnée valide trouvée dans le PDF.");
                        }
                    });
                }).catch(function(error) {
                    console.error('Erreur lors de la lecture du PDF:', error);
                    alert("Erreur lors de la lecture du PDF.");
                });
            };
            reader.readAsArrayBuffer(file);
        } else {
            alert("Veuillez sélectionner un fichier PDF valide.");
        }
    });

    // Fonction pour nettoyer le texte extrait
    function cleanText(text) {
        // Enlever les caractères indésirables et réduire les espaces
        text = text.replace(/[^a-zA-Z0-9éèàùçôîâêîô\s]+/g, ' '); // Garder les caractères alphanumériques et les espaces
        text = text.replace(/\s+/g, ' ').trim(); // Réduire les espaces multiples en un seul
        return text;
    }

    // Extraction des données
    function extractData(text) {
        var data = {
            date: 'nulle',
            mode_paiement: 'nulle',
            compte: 'nulle',
            libelle: 'nulle',
            debit: 'nulle',
            credit: 'nulle',
            facture: 'nulle',
            taux_ras_tva: 'nulle',
            nature_operation: 'nulle',
            date_lettrage: 'nulle',
            contre_partie: 'nulle'
        };

        // Définir des expressions régulières pour capturer les données
        var patterns = [
            {key: 'date', regex: /date[\s:]+([^\n]+)/i},
            {key: 'mode_paiement', regex: /mode\s+de\s+réglément[\s:]+([^\n]+)/i},
            {key: 'compte', regex: /compte[\s:]+([^\n]+)/i},
            {key: 'libelle', regex: /libellé[\s:]+([^\n]+)/i},
            {key: 'debit', regex: /débit[\s:]+([^\n]+)/i},
            {key: 'credit', regex: /crédit[\s:]+([^\n]+)/i},
            {key: 'facture', regex: /facture[\s:]+([^\n]+)/i},
            {key: 'taux_ras_tva', regex: /taux ras tva[\s:]+([^\n]+)/i},
            {key: 'nature_operation', regex: /nature de l'opération[\s:]+([^\n]+)/i},
            {key: 'date_lettrage', regex: /date lettrage[\s:]+([^\n]+)/i},
            {key: 'contre_partie', regex: /contre-partie[\s:]+([^\n]+)/i}
        ];

        // Extraction des données avec les expressions régulières
        var foundData = false;
        patterns.forEach(function(pattern) {
            var match = text.match(pattern.regex);
            if (match && match[1]) {
                data[pattern.key] = match[1].trim();
                foundData = true;
            }
        });

        // Si aucune donnée valide n'est extraite, retourner null
        return foundData ? data : null;
    }
</script>

</body>
</html> -->


















































@extends('layouts.user_type.auth')

@section('content')
    {{-- Métadonnées et styles/JS tiers --}}
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="societe_id" content="{{ session('societeId') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance Comptable</title>

    {{-- SweetAlert2 CSS et JS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    {{-- Tabulator CSS --}}
    <link href="https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">

    {{-- Flatpickr (pour la gestion des dates) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- Select2 (pour des listes déroulantes avancées) --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    {{-- Personnalisation CSS si nécessaire --}}
    <style>
        .select2-container--open {
            z-index: 10700 !important;
        }
        /* Exemple de style pour l’éditeur custom */
        .custom-datalist-editor-container {
            position: relative;
            width: 100%;
            display: flex;
            flex-direction: column-reverse;
        }
        .custom-list-editor-container {
            position: relative;
        }
        .list-container {
            position: absolute;
            bottom: 100%;
            left: 0;
            width: 100%;
            background-color: #fff;
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .list-container li {
            padding: 5px;
            cursor: pointer;
            list-style: none;
        }
        .list-container li:hover {
            background-color: #ddd;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="card p-4">
            <h4 class="mb-4">Balance Comptable</h4>

            {{-- Filtres haut de page (comptes, dates, format des colonnes, etc.) --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Compte Début :</label>
                    <select class="form-control select2" id="compte_debut">
                        <option value="">LD plan comptable</option>
                        <!-- Remplir dynamiquement -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Compte Fin :</label>
                    <select class="form-control select2" id="compte_fin">
                        <option value="">LD plan comptable</option>
                        <!-- Remplir dynamiquement -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Période Début :</label>
                    <input type="text" class="form-control flatpickr" placeholder="JJ/MM/AAAA" id="periode_debut">
                </div>
                <div class="col-md-3">
                    <label>Période Fin :</label>
                    <input type="text" class="form-control flatpickr" placeholder="JJ/MM/AAAA" id="periode_fin">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="format_colonnes" id="simplifie" value="6" checked>
                        <label class="form-check-label" for="simplifie">Simplifiée (6 colonnes)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="format_colonnes" id="detaille" value="8">
                        <label class="form-check-label" for="detaille">Détaillée (8 colonnes)</label>
                    </div>
                </div>
            </div>

            {{-- Boutons d'action --}}
            <div class="mb-3">
                <button id="btnImport" class="btn btn-secondary">Importer</button>
                <button id="btnExportExcel" class="btn btn-success">Exporter Excel</button>
                <button id="btnExportPDF" class="btn btn-danger">Exporter PDF</button>
            </div>

            {{-- Tableau (Tabulator) --}}
            <div id="balance-table"></div>

            {{-- Affichage des totaux en bas (exemple) --}}
            <div class="row mt-4">
                <div class="col-md-6 text-end">
                    <strong>Total Balance:</strong>
                </div>
                <div class="col-md-6">
                    <span id="totalDebit">0.00</span> / <span id="totalCredit">0.00</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 text-end">
                    <strong>Résultat CPC:</strong>
                </div>
                <div class="col-md-6">
                    <span id="resultatCPC">0.00</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 text-end">
                    <strong>Résultat Bilan:</strong>
                </div>
                <div class="col-md-6">
                    <span id="resultatBilan">0.00</span>
                </div>
            </div>
        </div>
    </div>

    {{-- JS nécessaires (chargement en fin de page) --}}
    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Tabulator --}}
    <script src="https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js"></script>
    {{-- SheetJS pour export Excel --}}
    <script type="text/javascript" src="https://oss.sheetjs.com/sheetjs/xlsx.full.min.js"></script>
    {{-- jsPDF + autoTable pour export PDF --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
    {{-- Select2 --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        // Initialisation de Select2 et Flatpickr
        $(document).ready(function() {
            $('.select2').select2();
            $('.flatpickr').flatpickr();
        });

        // Récupère les données de la Balance depuis Laravel (variable $balanceData)
        const balanceDataFromServer = @json($balanceData);

        // Définition des colonnes pour Tabulator (adaptables selon vos besoins : 6 ou 8 colonnes)
        let columns = [
            {title: "Compte", field: "compte", width: 120},
            {title: "Intitulé", field: "intitule", width: 250},
            {title: "A nouveau Débit", field: "anv_debit", hozAlign: "right"},
            {title: "A nouveau Crédit", field: "anv_credit", hozAlign: "right"},
            {title: "Opération Débit", field: "ope_debit", hozAlign: "right"},
            {title: "Opération Crédit", field: "ope_credit", hozAlign: "right"},
            {title: "Cumul Débit", field: "cumul_debit", hozAlign: "right"},
            {title: "Cumul Crédit", field: "cumul_credit", hozAlign: "right"},
            {title: "Solde Débit", field: "solde_debit", hozAlign: "right"},
            {title: "Solde Crédit", field: "solde_credit", hozAlign: "right"},
        ];

        // Création du tableau Tabulator
        let table = new Tabulator("#balance-table", {
            data: balanceDataFromServer,
            layout: "fitColumns",
            columns: columns,
            responsiveLayout: true,
            height: "400px",
            rowFormatter: function(row) {
                // Personnalisation éventuelle des lignes
            },
            dataLoaded: function(data) {
                // Calcul et affichage des totaux une fois les données chargées
                updateTotals(data);
            },
        });

        // Fonction de calcul des totaux affichés en bas du tableau
        function updateTotals(data) {
            let totalDebit = 0;
            let totalCredit = 0;
            data.forEach(item => {
                totalDebit += parseFloat(item.cumul_debit || 0);
                totalCredit += parseFloat(item.cumul_credit || 0);
            });
            document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
            document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);
            // Exemple : calcul du Résultat CPC et Bilan
            document.getElementById('resultatCPC').textContent = (totalDebit - totalCredit).toFixed(2);
            document.getElementById('resultatBilan').textContent = (totalDebit - totalCredit).toFixed(2);
        }

        // Événement pour l'export Excel via SheetJS
        document.getElementById("btnExportExcel").addEventListener("click", function() {
            let data = table.getData();
            let ws_data = [];
            // Ajout des en-têtes
            ws_data.push(columns.map(col => col.title));
            // Ajout des lignes
            data.forEach(row => {
                ws_data.push(columns.map(col => row[col.field]));
            });
            let wb = XLSX.utils.book_new();
            let ws = XLSX.utils.aoa_to_sheet(ws_data);
            XLSX.utils.book_append_sheet(wb, ws, "Balance");
            XLSX.writeFile(wb, "BalanceComptable.xlsx");
        });

        // Événement pour l'export PDF via jsPDF et autoTable
        document.getElementById("btnExportPDF").addEventListener("click", function() {
            let { jsPDF } = window.jspdf;
            let doc = new jsPDF('l', 'pt', 'a4'); // Orientation paysage, unité pt, format A4

            let data = table.getData();
            let body = data.map(item => columns.map(col => item[col.field]));

            let head = [columns.map(col => col.title)];

            doc.autoTable({
                head: head,
                body: body,
                startY: 20,
                margin: { left: 10, right: 10 },
                styles: {
                    fontSize: 8,
                    cellPadding: 3,
                },
                headStyles: {
                    fillColor: [52, 73, 94],
                    textColor: 255,
                },
                tableWidth: 'auto',
            });

            doc.save('BalanceComptable.pdf');
        });
    </script>
@endsection



































