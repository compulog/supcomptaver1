<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Comptable</title>

    <!-- Style de la page -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        header {
            background-color: #007bff;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tbody tr:hover {
            background-color: #e2e2e2;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <header>
        <h1>Plan Comptable de la société : {{ $societe->raison_sociale }}</h1>
    </header>

    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Compte</th>
                    <th>Intitulé</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plansComptables as $plan)
                    <tr>
                        <td>{{ $plan->compte }}</td>
                        <td>{{ $plan->intitule }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; {{ date('Y') }} Plan Comptable. Tous droits réservés.</p>
    </footer>
</body>
</html>
