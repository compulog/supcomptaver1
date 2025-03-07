<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Liste des Clients</title>
    <style>
        /* Style pour le PDF */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Liste des Clients</h1>
    <table>
        <thead>
            <tr>
                <th>Compte</th>
                <th>Intitulé</th>
                <th>Identifiant fiscal</th>
                <th>ICE</th>
                <th>Type client</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
                <tr>
                    <td>{{ $client->compte }}</td>
                    <td>{{ $client->intitule }}</td>
                    <td>{{ $client->identifiant_fiscal }}</td>
                    <td>{{ $client->ICE }}</td>
                    <td>{{ $client->type_client }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
