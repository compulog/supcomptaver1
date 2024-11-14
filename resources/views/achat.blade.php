@extends('layouts.user_type.auth')

@section('content')
    <div class="container mt-4">
        <h3>Fichiers de la société</h3>
        
        @if ($files->isEmpty())
            <p>Aucun fichier trouvé pour cette société.</p>
        @else
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4"> 
                <!-- 'g-4' ajoute un espacement de 1.5rem entre les éléments -->
                @foreach ($files as $file)
                    <div class="col"> <!-- Colonne flexible qui occupe un espace proportionnel -->
                        <div class="card" style="width: 12rem; height: 16rem;"> <!-- Réduire la taille des cartes -->
                            <div class="card-body p-2"> <!-- Ajuster l'intérieur de la carte -->
                                @php
                                    $fileExtension = pathinfo($file->filename, PATHINFO_EXTENSION);
                                @endphp

                                @if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp']))
                                    <!-- Si c'est une image -->
                                    <img src="{{ asset('storage/' . $file->file_path) }}" alt="{{ $file->filename }}" class="card-img-top" style="height: 100px; object-fit: cover;">
                                @elseif ($fileExtension == 'pdf')
                                    <!-- Si c'est un PDF -->
                                    <iframe src="{{ asset('storage/' . $file->file_path) }}" width="100%" height="100"></iframe>
                                @elseif (in_array($fileExtension, ['doc', 'docx', 'xls', 'xlsx']))
                                    <!-- Pour les fichiers Word ou Excel, une icône générique -->
                                    <img src="{{ asset('images/icons/file-icon.png') }}" class="card-img-top" style="height: 100px;">
                                @else
                                    <!-- Pour d'autres types de fichiers -->
                                    <img src="{{ asset('images/icons/unknown-file.png') }}" class="card-img-top" style="height: 100px;">
                                @endif
                                
                                <h5 class="card-title mt-2 text-truncate" style="font-size: 0.9rem;">{{ $file->filename }}</h5>
                                <p class="card-text text-truncate" style="font-size: 0.8rem;">Type : {{ $file->type }}</p>
                                
                                <!-- Bouton de téléchargement -->
                                <a href="{{ route('file.download', $file->id) }}" class="btn btn-primary btn-sm mt-2">
    Télécharger
</a>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
