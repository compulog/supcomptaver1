<!DOCTYPE html>
<html>
<head>
    <title>Liste des Fournisseurs</title>
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
    <h1>Liste des Fournisseurs</h1>
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
                <th>Contre Partie</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fournisseurs as $fournisseur)
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
</body>
</html>
