<!DOCTYPE html>
<html>
<head>
    <title>Liste des Plans Comptables</title>
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
    <h1>Liste des Plans Comptables</h1>
    <table>
        <thead>
            <tr>
                <th>Compte</th>
                <th>Intitulé</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plansComptables as $plan)
                <tr>
                    <td>{{ $plan->compte }}</td>
                    <td>{{ $plan->intitule }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
