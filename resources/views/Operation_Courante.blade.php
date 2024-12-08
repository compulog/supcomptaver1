@extends('layouts.user_type.auth')

@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabulator Table</title>
    <link href="https://unpkg.com/tabulator-tables@5.4.4/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://unpkg.com/tabulator-tables@5.4.4/dist/js/tabulator.min.js"></script>
</head>
<body>
    <h2>Journal Achat</h2>
    <div>
        <strong>Code Journal:</strong> ACH <br>
        <strong>Mois:</strong> Janv-2024 <br>
        <strong>Intitulé:</strong> Contre Partie Automatique <br>
        <strong>Saisie par:</strong> Exercice entier 2024
    </div>
    <br>
    <div id="example-table"></div>

    <script>
        // Sample data
        const tableData = [
            {
                Date: "01/01/2024",
                "N° dossier": "type ventes",
                "N° facture": 123,
                Compte: "44110009",
                Libellé: "F° 123 SRM-CS",
                Débit: "",
                Crédit: "6 000,00",
                "Contre-Partie": "61455000",
                "Rubrique TVA": "146",
                "Compte TVA": "34552020",
                "Prorat de déduction": "OUI",
                Pièce: "PACH010001",
            },
            {
                Date: "01/01/2024",
                "N° dossier": "",
                "N° facture": 123,
                Compte: "61455000",
                Libellé: "F° 123 SRM-CS",
                Débit: "5 000,00",
                Crédit: "",
                "Contre-Partie": "44110009",
                "Rubrique TVA": "",
                "Compte TVA": "",
                "Prorat de déduction": "",
                Pièce: "PACH010001",
            },
            {
                Date: "01/01/2024",
                "N° dossier": "",
                "N° facture": 123,
                Compte: "34552020",
                Libellé: "F° 123 SRM-CS",
                Débit: "1 000,00",
                Crédit: "",
                "Contre-Partie": "44110009",
                "Rubrique TVA": "",
                "Compte TVA": "",
                "Prorat de déduction": "",
                Pièce: "PACH010001",
            },
        ];

        // Create Tabulator table
        const table = new Tabulator("#example-table", {
            height: "311px",
            layout: "fitColumns",
            data: tableData,
            columns: [
                { title: "Date", field: "Date", sorter: "date", hozAlign: "center" },
                { title: "N° dossier", field: "N° dossier", hozAlign: "center" },
                { title: "N° facture", field: "N° facture", hozAlign: "center" },
                { title: "Compte", field: "Compte", hozAlign: "center" },
                { title: "Libellé", field: "Libellé" },
                { title: "Débit", field: "Débit", hozAlign: "right", formatter: "money", formatterParams: { symbol: "€", symbolAfter: true } },
                { title: "Crédit", field: "Crédit", hozAlign: "right", formatter: "money", formatterParams: { symbol: "€", symbolAfter: true } },
                { title: "Contre-Partie", field: "Contre-Partie", hozAlign: "center" },
                { title: "Rubrique TVA", field: "Rubrique TVA", hozAlign: "center" },
                { title: "Compte TVA", field: "Compte TVA", hozAlign: "center" },
                { title: "Prorat de déduction", field: "Prorat de déduction", hozAlign: "center" },
                { title: "Pièce", field: "Pièce", hozAlign: "center" },
            ],
        });
    </script>
</body>
</html>


@endsection
