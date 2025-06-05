@extends('layouts.app')
@section('title')
    Tableau de bord
@endsection
@section('content')
    <div class="container">
        <h2 class="mb-3">Bienvenue sur votre Tableau de bord</h2>
        <p class="mb-4">Voici votre tableau de bord où vous pouvez gérer vos tâches, routines, notes et fichiers.</p>
        
        <div class="row mb-4 dashboard-stats-row">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card shadow-sm h-100 dashboard-card">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Tâches</h5>
                        <p class="card-text flex-grow-1">Vous avez <strong>{{ $tasksCount }}</strong> tâches en attente.</p>
                        <a href="{{ route('tasks.index') }}" class="btn btn-primary mt-auto">Voir les tâches</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card shadow-sm h-100 dashboard-card">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Routines</h5>
                        <p class="card-text flex-grow-1">Vous avez <strong>{{ $routinesCount }}</strong> routines programmées aujourd'hui.</p>
                        <a href="{{ route('routines.index') }}" class="btn btn-primary mt-auto">Voir les routines</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card shadow-sm h-100 dashboard-card">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Notes</h5>
                        <p class="card-text flex-grow-1">Vous avez <strong>{{ $notesCount }}</strong> notes enregistrées.</p>
                        <a href="{{ route('notes.index') }}" class="btn btn-primary mt-auto">Voir les notes</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card shadow-sm h-100 dashboard-card">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Fichiers</h5>
                        <p class="card-text flex-grow-1">Vous avez <strong>{{ $filesCount }}</strong> fichiers.</p>
                        <a href="{{ route('files.index') }}" class="btn btn-primary mt-auto">Voir les fichiers</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Tâches récentes</h5>
                        <div class="table-responsive flex-grow-1">
                            <ul class="list-group">
                                @foreach($recentTasks as $task)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-truncate me-2">{{ $task->title }}</span>
                                        <span class="badge bg-{{ $task->status == 'to_do' ? 'primary' : 'warning' }} rounded-pill">
                                            {{ $task->status == 'to_do' ? 'À faire' : 'En cours' }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Routines d'aujourd'hui</h5>
                        <div class="table-responsive flex-grow-1">
                            <ul class="list-group">
                                @foreach($todayRoutines as $routine)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-truncate me-2">{{ $routine->title }}</span>
                                        <span class="badge bg-primary rounded-pill">{{ $routine->frequency }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Notes récentes</h5>
                        <div class="table-responsive flex-grow-1">
                            <ul class="list-group">
                                @foreach($recentNotes as $note)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-truncate">{{ $note->title }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Rappels à venir</h5>
                        <div class="table-responsive flex-grow-1">
                            <ul class="list-group">
                                @foreach($upcomingReminders as $reminder)
                                    @php
                                        $reminderDate = \Carbon\Carbon::parse($reminder->date);
                                        $reminderTime = $reminder->time;
                                        $isPast = $reminderDate->isPast() && ($reminderDate->format('Y-m-d') != now()->format('Y-m-d') || 
                                                 ($reminderDate->format('Y-m-d') == now()->format('Y-m-d') && $reminderTime < now()->format('H:i')));
                                    @endphp
                                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $reminderDate->isToday() ? 'bg-warning' : ($isPast ? 'bg-danger' : '') }}">
                                        <span class="text-truncate me-2">{{ $reminder->title }}</span>
                                        <span class="badge bg-primary rounded-pill notification-badge">{{ $reminderDate->format('M d') }} {{ $reminderTime }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteneur pour l'effet confetti -->
    <div class="confetti-container"></div>
@endsection
