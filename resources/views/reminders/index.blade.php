@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
        <h2>Rappels</h2>
        <a href="{{ route('reminders.create') }}" class="btn btn-primary">Ajouter un rappel</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        @forelse($reminders as $reminder)
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $reminder->title }}</h5>
                        <p class="card-text">{{ Str::limit($reminder->description, 150) }}</p>
                        <p class="card-text"><strong>Date:</strong> {{ $reminder->date }}</p>
                        <p class="card-text"><strong>Heure:</strong> {{ $reminder->time }}</p>
                        <p class="card-text">
                            <strong>Statut:</strong>
                            <span class="badge {{ isset($reminder->email_sent) && $reminder->email_sent ? 'bg-success' : 'bg-warning' }}">
                                {{ isset($reminder->email_sent) && $reminder->email_sent ? 'Rappel envoyé' : 'Rappel en attente' }}
                            </span>
                        </p>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('reminders.edit', $reminder->id) }}" class="btn btn-warning"><i class="bi bi-pencil-square"></i> </a>
                            <form action="{{ route('reminders.destroy', $reminder->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rappel?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p>Aucun rappel trouvé.</p>
        @endforelse
    </div>
</div>
@endsection
