@extends('layouts.user_type.auth')

@section('content')
<div class="container mt-4">
    <h3>Afficher le fichier : {{ $file->name }}</h3>

    <!-- Logique pour afficher le contenu du fichier en fonction de son type MIME -->
    @if ($mimeType == 'image/jpeg' || $mimeType == 'image/png' || $mimeType == 'image/gif')
        <div class="text-center">
            <!-- Affichage de l'image -->
            <img src="{{ asset('files/achats/' . $file->name) }}" alt="{{ $file->name }}" class="img-fluid">
        </div>

    @elseif ($mimeType == 'application/pdf')
        <div class="text-center">
            <!-- Affichage du PDF -->
            <embed src="{{ asset('files/achats/' . $file->name) }}" type="application/pdf" width="100%" height="600px">
        </div>

    @elseif ($mimeType == 'text/plain')
        <div class="mt-3">
            <!-- Affichage du contenu d'un fichier texte -->
            <pre>{{ file_get_contents($filePath) }}</pre>
        </div>

    @elseif ($mimeType == 'text/html')
        <div class="mt-3">
            <!-- Affichage du contenu HTML -->
            {!! file_get_contents($filePath) !!}
        </div>

    @else
        <p>Le fichier ne peut pas être affiché dans ce format. Type MIME : {{ $mimeType }}</p>
    @endif
</div>
@endsection
