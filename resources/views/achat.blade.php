@extends('layouts.user_type.auth')

@section('content')
    <div class="container mt-4">
        <h3>Fichiers Achats de la société</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <!-- Div pour ajouter un nouveau dossier -->
            <div class="col">
                <div class="card shadow-sm" style="width: 12rem; height: 6rem; display: flex; justify-content: center; align-items: center; cursor: pointer;" onclick="openCreateFolderForm()">
                    <div class="card-body text-center p-2">
                        <i class="fas fa-plus fa-3x text-primary"></i> <!-- Icône + -->
                        <p class="mt-2">Ajouter un Dossier</p>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($folders->isEmpty())
                <p>Aucun dossier trouvé pour cette société.</p>
            @else
                @foreach ($folders as $folder)
                    <div class="col" ondblclick="openFile({{ $folder->id }})">
                        <div class="card shadow-sm" style="width: 12rem; height: 6rem;">
                            <div class="card-body text-center p-2 d-flex flex-column justify-content-between">
                                <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">
                                    {{ $folder->name }}
                                </h5>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Modal pour la création de dossier -->
    <div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createFolderModalLabel">Créer un Nouveau Dossier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('folder.create') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="folder_name" class="form-label">Nom du Dossier</label>
                            <input type="text" class="form-control" id="folder_name" name="folder_name" required>
                        </div>
                        <input type="hidden" name="societe_id" id="societe_id" value="{{ $societe->id }}">
                        <button type="submit" class="btn btn-primary">Créer le Dossier</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openCreateFolderForm() {
            var myModal = new bootstrap.Modal(document.getElementById('createFolderModal'));
            myModal.show();
        }

        function openFile(folderId) {
            window.location.href = '/folder/' + folderId;
        }
    </script>
@endsection
