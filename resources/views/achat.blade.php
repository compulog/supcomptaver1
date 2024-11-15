@extends('layouts.user_type.auth')

@section('content')
<head>
    <!-- Ajoutez cette ligne dans le head de votre layout -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<div class="container mt-4">
    <h3>Fichiers Achats de la société</h3>

    <!-- Formulaire pour créer un nouveau dossier -->
    <div class="mb-4">
        <form action="{{ route('folder.create') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" class="form-control" name="folder_name" placeholder="Nom du nouveau dossier" required>
                <button class="btn btn-primary" type="submit">Créer un Dossier</button>
            </div>
        </form>
    </div>

    @if ($files->isEmpty())
        <p>Aucun fichier trouvé pour cette société.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <!-- 'g-4' ajoute un espacement de 1.5rem entre les éléments -->
            @foreach ($files as $file)
                <div class="col">
                    <div class="card shadow-sm" style="width: 12rem; height: 6rem;">
                        <div class="card-body text-center p-2 d-flex flex-column justify-content-between">
                            <!-- Affichage du nom du fichier -->
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">
                                {{ $file->name }}
                            </h5>

                            <!-- Boutons de téléchargement et de consultation -->
                            <div class="d-flex justify-content-center gap-2">
                                <!-- Bouton de téléchargement avec icône -->
                                <a href="{{ route('file.download', $file->id) }}" class="btn btn-link mt-1" style="font-size: 1.2rem; color: #007bff;" title="Télécharger">
                                    <i class="fas fa-download"></i>
                                </a>

                                <!-- Bouton de consultation avec icône -->
                                <a href="{{ route('file.view', $file->id) }}" class="btn btn-link mt-1" style="font-size: 1.2rem; color: #28a745;" title="Consulter">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection






@extends('layouts.user_type.auth')

@section('content')
<head>
    <!-- Ajoutez cette ligne dans le head de votre layout -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<div class="container mt-4">
    <h3>Fichiers Achats de la société</h3>

    <!-- Formulaire pour créer un nouveau dossier -->
    <div class="mb-4">
        <form action="{{ route('folder.create') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" class="form-control" name="folder_name" placeholder="Nom du nouveau dossier" required>
                <button class="btn btn-primary" type="submit">Créer un Dossier</button>
            </div>
        </form>
    </div>

    @if ($files->isEmpty())
        <p>Aucun fichier trouvé pour cette société.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <!-- 'g-4' ajoute un espacement de 1.5rem entre les éléments -->
            @foreach ($files as $file)
                <div class="col">
                    <div class="card shadow-sm" style="width: 12rem; height: 6rem;">
                        <div class="card-body text-center p-2 d-flex flex-column justify-content-between">
                            <!-- Affichage du nom du fichier -->
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">
                                {{ $file->name }}
                            </h5>

                            <!-- Boutons de téléchargement et de consultation -->
                            <div class="d-flex justify-content-center gap-2">
                                <!-- Bouton de téléchargement avec icône -->
                                <a href="{{ route('file.download', $file->id) }}" class="btn btn-link mt-1" style="font-size: 1.2rem; color: #007bff;" title="Télécharger">
                                    <i class="fas fa-download"></i>
                                </a>

                                <!-- Bouton de consultation avec icône -->
                                <a href="{{ route('file.view', $file->id) }}" class="btn btn-link mt-1" style="font-size: 1.2rem; color: #28a745;" title="Consulter">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection