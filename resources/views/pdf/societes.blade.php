<!DOCTYPE html>
<html>
<head>
    <title>Liste des Sociétés</title>
    <style>
        /* Ajoutez ici le style souhaité pour votre PDF */
    </style>
</head>
<body>
    <h1>Liste des Sociétés</h1>
    <table>
        <thead>
            <tr>
                <th>Raison Sociale</th>
                <th>Forme Juridique</th>
                <th>Siège Social</th>
                <th>Patente</th>
                <th>RC</th>
                <th>Centre RC</th>
                <th>Identifiant Fiscal</th>
                <th>ICE</th>
                <th>Assujettie Partielle TVA</th>
                <th>Prorata de Déduction</th>
                <th>Exercice Social Début</th>
                <th>Exercice Social Fin</th>
                <th>Date de Création</th>
                <th>Nature Activité</th>
                <th>Activité</th>
                <th>Régime Déclaration</th>
                <th>Fait Générateur</th>
                <th>Rubrique TVA</th>
                <th>Désignation</th>
                <th>Nombre Chiffre Compte</th>
                <th>Modèle Comptable</th>
            </tr>
        </thead>
        <tbody>
            @foreach($societes as $societe)
                <tr>
                    <td>{{ $societe->raison_sociale }}</td>
                    <td>{{ $societe->forme_juridique }}</td>
                    <td>{{ $societe->siege_social }}</td>
                    <td>{{ $societe->patente }}</td>
                    <td>{{ $societe->rc }}</td>
                    <td>{{ $societe->centre_rc }}</td>
                    <td>{{ $societe->identifiant_fiscal }}</td>
                    <td>{{ $societe->ice }}</td>
                    <td>{{ $societe->assujettie_partielle_tva }}</td>
                    <td>{{ $societe->prorata_de_deduction }}</td>
                    <td>{{ $societe->exercice_social_debut }}</td>
                    <td>{{ $societe->exercice_social_fin }}</td>
                    <td>{{ $societe->date_creation }}</td>
                    <td>{{ $societe->nature_activite }}</td>
                    <td>{{ $societe->activite }}</td>
                    <td>{{ $societe->regime_declaration }}</td>
                    <td>{{ $societe->fait_generateur }}</td>
                    <td>{{ $societe->rubrique_tva }}</td>
                    <td>{{ $societe->designation }}</td>
                    <td>{{ $societe->nombre_chiffre_compte }}</td>
                    <td>{{ $societe->modele_comptable }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
