@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- En-tête du Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">📊 Dashboard</h1>
                    <p class="text-muted">Vue d'ensemble de votre productivité</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Dernière mise à jour : {{ now()->format('d/m/Y H:i') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des Tâches -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">📋 Statistiques des Tâches</h4>
        </div>

        <!-- Cartes de statistiques principales -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total des Tâches</h6>
                            <h2 class="mb-0">{{ $taskStats['total'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-list-task fs-1"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small>Taux de completion: {{ $taskStats['completion_rate'] }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Terminées</h6>
                            <h2 class="mb-0">{{ $taskStats['completed'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small>{{ $taskStats['completed_this_week']->count() }} cette semaine</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">En Cours</h6>
                            <h2 class="mb-0">{{ $taskStats['in_progress'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-arrow-clockwise fs-1"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small>{{ $taskStats['high_priority'] }} haute priorité</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">À Faire</h6>
                            <h2 class="mb-0">{{ $taskStats['todo'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clipboard fs-1"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('tasks.create') }}" class="text-white text-decoration-none">
                            <small><i class="bi bi-plus"></i> Nouvelle tâche</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">📈 Progression des Tâches (7 derniers jours)</h5>
                </div>
                <div class="card-body">
                    <canvas id="taskProgressChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">🎯 Répartition par Priorité</h5>
                </div>
                <div class="card-body">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tâches complétées cette semaine -->
    @if($taskStats['completed_this_week']->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">🏆 Tâches Complétées Cette Semaine</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($taskStats['completed_this_week'] as $task)
                        <div class="col-md-6 col-lg-4 mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span class="text-truncate">{{ $task->title }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistiques des Routines et Rappels -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🔄 Statistiques des Routines</h5>
                    <a href="{{ route('routines.index') }}" class="btn btn-sm btn-outline-primary">Voir toutes</a>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <h4 class="text-primary">{{ $routineStats['total'] }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-success">{{ $routineStats['daily'] }}</h4>
                            <small class="text-muted">Quotidiennes</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-warning">{{ $routineStats['weekly'] }}</h4>
                            <small class="text-muted">Hebdomadaires</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-info">{{ $routineStats['monthly'] }}</h4>
                            <small class="text-muted">Mensuelles</small>
                        </div>
                    </div>

                    @if($routineStats['today']->count() > 0)
                    <hr>
                    <h6>Routines d'aujourd'hui :</h6>
                    @foreach($routineStats['today'] as $routine)
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-arrow-repeat text-primary me-2"></i>
                            <span>{{ $routine->title }}</span>
                        </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">⏰ Statistiques des Rappels</h5>
                    <a href="{{ route('reminders.index') }}" class="btn btn-sm btn-outline-primary">Voir tous</a>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <h4 class="text-primary">{{ $reminderStats['total'] }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-info">{{ $reminderStats['upcoming'] }}</h4>
                            <small class="text-muted">À venir</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-danger">{{ $reminderStats['overdue'] }}</h4>
                            <small class="text-muted">En retard</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-success">{{ $reminderStats['sent'] }}</h4>
                            <small class="text-muted">Envoyés</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Données récentes -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">📝 Tâches Récentes</h5>
                </div>
                <div class="card-body">
                    @if($recentData['tasks']->count() > 0)
                        @foreach($recentData['tasks'] as $task)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'success') }} me-2">
                                    {{ ucfirst($task->priority) }}
                                </span>
                                <span class="text-truncate">{{ $task->title }}</span>
                            </div>
                            <small class="text-muted">{{ $task->created_at->diffForHumans() }}</small>
                        </div>
                        @endforeach
                        <div class="text-center mt-3">
                            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-primary">Voir toutes les tâches</a>
                        </div>
                    @else
                        <p class="text-muted text-center">Aucune tâche récente</p>
                        <div class="text-center">
                            <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary">Créer une tâche</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">📋 Notes Récentes</h5>
                </div>
                <div class="card-body">
                    @if($recentData['notes']->count() > 0)
                        @foreach($recentData['notes'] as $note)
                        <div class="mb-2">
                            <h6 class="mb-1">{{ $note->title }}</h6>
                            <p class="text-muted small mb-1">{{ Str::limit($note->content, 100) }}</p>
                            <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                        </div>
                        <hr>
                        @endforeach
                        <div class="text-center">
                            <a href="{{ route('notes.index') }}" class="btn btn-sm btn-primary">Voir toutes les notes</a>
                        </div>
                    @else
                        <p class="text-muted text-center">Aucune note récente</p>
                        <div class="text-center">
                            <a href="{{ route('notes.create') }}" class="btn btn-sm btn-primary">Créer une note</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique de progression des tâches
    const taskProgressCtx = document.getElementById('taskProgressChart').getContext('2d');
    const taskProgressData = @json($chartData['task_progress']);

    new Chart(taskProgressCtx, {
        type: 'line',
        data: {
            labels: taskProgressData.map(item => item.date),
            datasets: [{
                label: 'Tâches Complétées',
                data: taskProgressData.map(item => item.completed),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Graphique de répartition par priorité
    const priorityCtx = document.getElementById('priorityChart').getContext('2d');

    new Chart(priorityCtx, {
        type: 'doughnut',
        data: {
            labels: ['Haute', 'Moyenne', 'Faible'],
            datasets: [{
                data: [
                    {{ $taskStats['high_priority'] }},
                    {{ $taskStats['medium_priority'] }},
                    {{ $taskStats['low_priority'] }}
                ],
                backgroundColor: [
                    '#dc3545',
                    '#ffc107',
                    '#28a745'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endsection
