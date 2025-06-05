@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
        <h2>Fichiers téléchargés</h2>
        <a href="{{ route('files.create') }}" class="btn btn-primary">Télécharger un fichier</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        @foreach($files as $file)
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $file->name }}</h5>
                        <p class="card-text"><strong>Type:</strong> {{ $file->type }}</p>
                        <div class="d-flex">
                            <a href="{{ route('files.download', $file->id) }}" class="btn btn-primary me-2">
                                <i class="bi bi-download"></i> Télécharger
                            </a>
                            <a href="{{ route('files.edit', $file->id) }}" class="btn btn-warning me-2">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('files.destroy', $file->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if(count($files) === 0)
            <div class="col-12">
                <div class="alert alert-info">
                    Aucun fichier téléchargé. Commencez par <a href="{{ route('files.create') }}">télécharger un fichier</a>.
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
