<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fournisseurs - Société {{ $societeId }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <h2>Liste des Fournisseurs - Société {{ $societeId }}</h2>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Compte</th>
                <th>Intitulé</th>
                <th>Identifiant Fiscal</th>
                <th>ICE</th>
                <th>Nature de l'Opération</th>
                <th>Rubrique TVA</th>
                <th>Désignation</th>
                <th>Contre Part</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fournisseurs as $fournisseur)
                <tr>
                    <td>{{ $fournisseur->id }}</td>
                    <td>{{ $fournisseur->compte }}</td>
                    <td>{{ $fournisseur->intitule }}</td>
                    <td>{{ $fournisseur->identifiant_fiscal }}</td>
                    <td>{{ $fournisseur->ICE }}</td>
                    <td>{{ $fournisseur->nature_operation }}</td>
                    <td>{{ $fournisseur->rubrique_tva }}</td>
                    <td>{{ $fournisseur->designation }}</td>
                    <td>{{ $fournisseur->contre_partie }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Exporté le {{ now()->format('d/m/Y H:i') }}</p>
    </div>

</body>
</html>
