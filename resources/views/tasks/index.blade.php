@extends('layouts.app')
@section('title')
    Mes Tâches
@endsection
@section('content')
    <style>
        .kanban-column {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            height: 100%;
            margin-bottom: 20px;
        }

        .kanban-list {
            min-height: 300px;
            background-color: #e9ecef;
            border-radius: 5px;
            padding: 10px;
        }

        .kanban-item {
            cursor: move;
        }

        .kanban-item.invisible {
            opacity: 0.4;
        }

        /* Styles responsives pour le Kanban */
        @media (max-width: 991.98px) {
            .kanban-tabs {
                display: flex;
                margin-bottom: 15px;
                overflow-x: auto;
                border-bottom: 1px solid #dee2e6;
            }
            
            .kanban-tab {
                padding: 8px 15px;
                cursor: pointer;
                border: 1px solid transparent;
                border-top-left-radius: 0.25rem;
                border-top-right-radius: 0.25rem;
                margin-right: 5px;
                font-weight: 500;
                white-space: nowrap;
            }
            
            .kanban-tab.active {
                background-color: #fff;
                border-color: #dee2e6 #dee2e6 #fff;
            }
            
            .kanban-tab-content {
                display: none;
            }
            
            .kanban-tab-content.active {
                display: block;
            }
            
            .kanban-list {
                min-height: 400px;
            }
        }
        
        @media (min-width: 992px) {
            .kanban-tabs {
                display: none;
            }
            
            .kanban-tab-content {
                display: block !important;
            }
        }

        /* Empêcher les débordements de texte dans les cartes */
        .card-title, .card-text {
            word-break: break-word;
        }
    </style>
    <div class="container">
        <div class="bg-white align-items-center mb-4 shadow-sm p-3 rounded">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Mes Tâches</h2>
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Nouvelle Tâche
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tabs pour mobile/tablet -->
        <div class="kanban-tabs d-lg-none">
            <div class="kanban-tab active" data-target="tab-to-do">
                À faire <span class="badge bg-primary ms-1">{{ count($tasks['to_do'] ?? []) }}</span>
            </div>
            <div class="kanban-tab" data-target="tab-in-progress">
                En cours <span class="badge bg-warning ms-1">{{ count($tasks['in_progress'] ?? []) }}</span>
            </div>
            <div class="kanban-tab" data-target="tab-completed">
                Terminé <span class="badge bg-success ms-1">{{ count($tasks['completed'] ?? []) }}</span>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 kanban-tab-content active" id="tab-to-do">
                <div class="kanban-column">
                    <div class="d-flex justify-content-between bg-primary text-white shadow-sm align-items-center px-3 py-2 rounded-top">
                        <h4 class="text-white fw-bolder m-0">À faire</h4>
                    </div>
                    
                    <div class="kanban-list" id="to_do">
                        @foreach ($tasks['to_do'] ?? [] as $task)
                            <div class="card mb-3 kanban-item" data-id="{{ $task->id }}" draggable="true">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        {{ $task->title }}
                                        <span style="font-size: 12px;" class="badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}">{{ $task->priority == 'low' ? 'Faible' : ($task->priority == 'medium' ? 'Moyenne' : 'Haute') }}</span>
                                        @if($task->is_auto_generated)
                                        <span style="font-size: 10px;" class="badge bg-info ms-1" title="Tâche générée automatiquement">
                                            <i class="bi bi-robot"></i> Auto
                                        </span>
                                        @endif
                                    </h5>

                                    <p class="card-text">{{ Str::limit($task->description, 100) }}</p>

                                    @if($task->assigned_to)
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="bi bi-person-fill"></i>
                                                Assigné à : <strong>{{ $task->assignedUser->name }}</strong>
                                            </small>
                                        </div>
                                    @endif
                                    <div class="d-flex">
                                        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-primary btn-sm me-2"><i class="bi bi-eye"></i></a>
                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline delete-task-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4 kanban-tab-content" id="tab-in-progress">
                <div class="kanban-column">
                    <div class="d-flex justify-content-between shadow-sm align-items-center bg-warning px-3 py-2 rounded-top">
                        <h4 class="text-white fw-bolder m-0">En cours</h4>
                    </div>
                    
                    <div class="kanban-list" id="in_progress">
                        @foreach ($tasks['in_progress'] ?? [] as $task)
                            <div class="card mb-3 kanban-item" data-id="{{ $task->id }}" draggable="true">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        {{ $task->title }}
                                        <span style="font-size: 12px;" class="badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}">{{ $task->priority == 'low' ? 'Faible' : ($task->priority == 'medium' ? 'Moyenne' : 'Haute') }}</span>
                                        @if($task->is_auto_generated)
                                        <span style="font-size: 10px;" class="badge bg-info ms-1" title="Tâche générée automatiquement">
                                            <i class="bi bi-robot"></i> Auto
                                        </span>
                                        @endif
                                    </h5>
                                    <p class="card-text">{{ Str::limit($task->description, 100) }}</p>

                                    @if($task->assigned_to)
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="bi bi-person-fill"></i>
                                                Assigné à : <strong>{{ $task->assignedUser->name }}</strong>
                                            </small>
                                        </div>
                                    @endif
                                    <div class="d-flex">
                                        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-warning btn-sm me-2"><i class="bi bi-eye"></i></a>
                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline delete-task-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4 kanban-tab-content" id="tab-completed">
                <div class="kanban-column">
                    <div class="d-flex justify-content-between shadow-sm align-items-center bg-success px-3 py-2 rounded-top">
                        <h4 class="text-white fw-bolder m-0">Terminé</h4>
                    </div>
                    <div class="kanban-list" id="completed">
                        @foreach ($tasks['completed'] ?? [] as $task)
                            <div class="card mb-3 kanban-item" data-id="{{ $task->id }}" draggable="true">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        {{ $task->title }}
                                        <span style="font-size: 12px;" class="badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}">{{ $task->priority == 'low' ? 'Faible' : ($task->priority == 'medium' ? 'Moyenne' : 'Haute') }}</span>
                                        @if($task->is_auto_generated)
                                        <span style="font-size: 10px;" class="badge bg-info ms-1" title="Tâche générée automatiquement">
                                            <i class="bi bi-robot"></i> Auto
                                        </span>
                                        @endif
                                    </h5>
                                    <p class="card-text">{{ Str::limit($task->description, 100) }}</p>

                                    @if($task->assigned_to)
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="bi bi-person-fill"></i>
                                                Assigné à : <strong>{{ $task->assignedUser->name }}</strong>
                                            </small>
                                        </div>
                                    @endif
                                    <div class="d-flex">
                                        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-success btn-sm me-2"><i class="bi bi-eye"></i></a>
                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline delete-task-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>


    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const kanbanItems = document.querySelectorAll('.kanban-item');
            const kanbanLists = document.querySelectorAll('.kanban-list');

            // Gestion des onglets sur mobile/tablet
            const kanbanTabs = document.querySelectorAll('.kanban-tab');
            const kanbanTabContents = document.querySelectorAll('.kanban-tab-content');

            kanbanTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Retirer la classe active de tous les onglets
                    kanbanTabs.forEach(t => t.classList.remove('active'));
                    // Ajouter la classe active à l'onglet cliqué
                    this.classList.add('active');

                    // Masquer tous les contenus d'onglets
                    kanbanTabContents.forEach(content => content.classList.remove('active'));

                    // Afficher le contenu correspondant à l'onglet
                    const targetId = this.getAttribute('data-target');
                    document.getElementById(targetId).classList.add('active');
                });
            });

            kanbanItems.forEach(item => {
                item.addEventListener('dragstart', handleDragStart);
                item.addEventListener('dragend', handleDragEnd);
            });

            kanbanLists.forEach(list => {
                list.addEventListener('dragover', handleDragOver);
                list.addEventListener('drop', handleDrop);
            });

            function handleDragStart(e) {
                e.dataTransfer.setData('text/plain', e.target.dataset.id);
                setTimeout(() => {
                    e.target.classList.add('invisible');
                }, 0);
            }

            function handleDragEnd(e) {
                e.target.classList.remove('invisible');
            }

            function handleDragOver(e) {
                e.preventDefault();
            }

            function handleDrop(e) {
                e.preventDefault();
                const id = e.dataTransfer.getData('text');
                const draggableElement = document.querySelector(`.kanban-item[data-id='${id}']`);
                const dropzone = e.target.closest('.kanban-list');
                dropzone.appendChild(draggableElement);

                const status = dropzone.id;

                updateTaskStatus(id, status);
            }

            function updateTaskStatus(taskId, status) {
                fetch(`/tasks/${taskId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors de la mise à jour du statut');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Statut mis à jour avec succès');
                    // Optionnel: notification ou autre action après mise à jour réussie
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    // Optionnel: notification d'erreur ou autre action
                });
            }
        });
    </script>
@endsection
