@extends('layouts.user_type.auth')

@section('content')


    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">



<div class="container">
    <h1>Modifier l'Opération Courante</h1>

    <!-- Formulaire de modification -->
    <form action="{{ route('operation_courante.update', $operation->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" name="date" class="form-control" value="{{ old('date', $operation->date) }}" required>
        </div>

        <div class="form-group">
            <label for="numero_dossier">Numéro de Dossier</label>
            <input type="text" name="numero_dossier" class="form-control" value="{{ old('numero_dossier', $operation->numero_dossier) }}" required>
        </div>

        <div class="form-group">
            <label for="numero_facture">Numéro de Facture</label>
            <input type="text" name="numero_facture" class="form-control" value="{{ old('numero_facture', $operation->numero_facture) }}" required>
        </div>

        <div class="form-group">
            <label for="compte">Compte</label>
            <input type="text" name="compte" class="form-control" value="{{ old('compte', $operation->compte) }}" required>
        </div>

        <div class="form-group">
            <label for="libelle">Libellé</label>
            <input type="text" name="libelle" class="form-control" value="{{ old('libelle', $operation->libelle) }}" required>
        </div>

        <div class="form-group">
            <label for="debit">Débit</label>
            <input type="number" name="debit" class="form-control" value="{{ old('debit', $operation->debit) }}" required>
        </div>

        <div class="form-group">
            <label for="credit">Crédit</label>
            <input type="number" name="credit" class="form-control" value="{{ old('credit', $operation->credit) }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
@endsection
