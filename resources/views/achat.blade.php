@extends('layouts.user_type.auth')

@section('content')
    <div class="container mt-4">
        <h3>Fichiers de la société</h3>
        @if ($files->isEmpty())
            <p>Aucun fichier trouvé pour cette société.</p>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Fichier</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($files as $file)
                        <tr>
                            <td>
                                @if (in_array(pathinfo($file->filename, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'bmp']))
                                    <!-- Si c'est une image -->
                                    <img src="{{ asset('storage/' . $file->file_path) }}" alt="{{ $file->filename }}" style="width: 100px; height: auto;">
                                @elseif (pathinfo($file->filename, PATHINFO_EXTENSION) == 'pdf')
                                    <!-- Si c'est un PDF -->
                                    <iframe src="{{ asset('storage/' . $file->file_path) }}" width="300" height="200"></iframe>
                                @else
                                    <!-- Pour d'autres types de fichiers -->
                                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="btn btn-primary btn-sm">Voir le fichier</a>
                                @endif
                            </td>
                            <td>{{ $file->type }}</td>
                            <td>
                                <a href="{{ asset('storage/' . $file->file_path) }}" class="btn btn-primary btn-sm" download>
                                    Télécharger
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
