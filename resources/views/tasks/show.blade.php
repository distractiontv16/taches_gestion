@extends('layouts.app')
@section('title')
    {{ $task->title }} - Task Details
@endsection
@section('content')
    <div class="container">
        <h2 class="mb-4 shadow-sm p-3 rounded bg-white text-center">{{ $task->title }} - Task Details</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="card-title">{{ $task->title }}</h5>
                                <p class="card-text">{{ $task->description }}</p>
                                <p class="card-text"><strong>Due Date:</strong> {{ $task->due_date }}</p>
                                <p class="card-text"><strong>Priority:</strong> <span
                                        class="badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}">{{ ucfirst($task->priority) }}</span>
                                </p>
                                <p class="card-text"><strong>Status:</strong>
                                    @if ($task->status == 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($task->status == 'to_do')
                                        <span class="badge bg-primary">To Do</span>
                                    @elseif($task->status == 'in_progress')
                                        <span class="badge bg-warning">In Progress</span>
                                    @endif
                                </p>

                                <p class="card-text"><strong>Assign To:</strong> {{ $task->user->name }}</p>

                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#editTaskModal"> <i class="bi bi-pencil-square"></i> </button>
                                <button type="button" class="btn {{ $task->status == 'completed' ? 'btn-warning' : 'btn-success' }} toggle-complete" data-task-id="{{ $task->id }}">
                                    <i class="bi {{ $task->status == 'completed' ? 'bi-x-circle' : 'bi-check-circle' }}"></i>
                                    {{ $task->status == 'completed' ? 'Marquer comme non terminée' : 'Marquer comme terminée' }}
                                </button>
                                <a href="{{ route('projects.tasks.index', $task->project->id) }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-90deg-left"></i> </a>
                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche?')">
                                        <i class="bi bi-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>

                            <div class="col-md-6 border-start">
                                <h5>Time Tracker</h5>
                                <div id="time-tracker">
                                    <span id="time-display">00:00:00</span>
                                    <div>
                                        <button id="start-btn" class="btn btn-success btn-sm"><i
                                                class="bi bi-play-fill"></i></button>
                                        <button id="pause-btn" class="btn btn-warning btn-sm"><i
                                                class="bi bi-pause-fill"></i></button>
                                        <button id="reset-btn" class="btn btn-danger btn-sm"><i
                                                class="bi bi-stop-fill"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                    <h5>Checklist</h5>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addChecklistModal"> <i class="bi bi-plus-circle"></i> </button>
                                </div>

                                <!-- Checklist items -->
                                <ul class="list-group mt-2" id="checklist-items">
                                    @foreach ($task->checklistItems as $item)
                                        <li class="list-group-item d-flex justify-content-between align-items-center"
                                            id="checklist-item-{{ $item->id }}">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    id="checklist-item-checkbox-{{ $item->id }}"
                                                    {{ $item->completed ? 'checked' : '' }}
                                                    onchange="toggleChecklistItem({{ $item->id }})">
                                                <label
                                                    class="form-check-label {{ $item->completed ? 'text-decoration-line-through' : '' }}">{{ $item->name }}</label>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-primary btn-sm edit-checklist-item"
                                                    data-item-id="{{ $item->id }}" data-item-name="{{ $item->name }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm delete-checklist-item"
                                                    data-item-id="{{ $item->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                {{-- <div class="modal fade" id="editChecklistModal-{{ $item->id }}" tabindex="-1"
                                        aria-labelledby="editChecklistModalLabel-{{ $item->id }}"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form id="edit-checklist-form-{{ $item->id }}"
                                                    action="{{ route('checklist-items.update', $item->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="editChecklistModalLabel-{{ $item->id }}">Edit
                                                            Checklist Item</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="checklist-name-{{ $item->id }}"
                                                                class="form-label">Item Name</label>
                                                            <input type="text" name="name"
                                                                id="checklist-name-{{ $item->id }}"
                                                                class="form-control" value="{{ $item->name }}"
                                                                required>
                                                            <div class="invalid-feedback"
                                                                id="checklist-name-error-{{ $item->id }}"></div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Update
                                                            Item</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Checklist Modal -->
        <div class="modal fade" id="addChecklistModal" tabindex="-1" aria-labelledby="addChecklistModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="add-checklist-form">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addChecklistModalLabel">Add Checklist Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="checklist-name" class="form-label">Item Name</label>
                                <input type="text" name="name" id="checklist-name" class="form-control" required>
                                <div class="invalid-feedback" id="checklist-name-error"></div>
                            </div>
                            <input type="hidden" name="task_id" id="task_id" value="{{ $task->id }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Task Modal -->
        <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" name="title" id="title" class="form-control"
                                    value="{{ $task->title }}" required>
                                @error('title')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control">{{ $task->description }}</textarea>
                                @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" name="due_date" id="due_date" class="form-control"
                                    value="{{ $task->due_date }}">
                                @error('due_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select name="priority" id="priority" class="form-select" required>
                                    <option value="low" {{ $task->priority == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ $task->priority == 'medium' ? 'selected' : '' }}>Medium
                                    </option>
                                    <option value="high" {{ $task->priority == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                @error('priority')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="to_do" {{ $task->status == 'to_do' ? 'selected' : '' }}>To Do</option>
                                    <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In
                                        Progress</option>
                                    <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                </select>
                                @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let timer;
        let seconds = 0;
        let isRunning = false;

        function formatTime(sec) {
            let hours = Math.floor(sec / 3600);
            let minutes = Math.floor((sec % 3600) / 60);
            let seconds = sec % 60;

            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        function updateTimeDisplay() {
            document.getElementById('time-display').innerText = formatTime(seconds);
        }

        document.getElementById('start-btn').addEventListener('click', () => {
            if (!isRunning) {
                isRunning = true;
                timer = setInterval(() => {
                    seconds++;
                    updateTimeDisplay();
                }, 1000);
            }
        });

        document.getElementById('pause-btn').addEventListener('click', () => {
            if (isRunning) {
                isRunning = false;
                clearInterval(timer);
            }
        });

        document.getElementById('reset-btn').addEventListener('click', () => {
            isRunning = false;
            clearInterval(timer);
            seconds = 0;
            updateTimeDisplay();
        });

        updateTimeDisplay();

        function toggleChecklistItem(itemId) {
            const url = '{{ route('checklist-items.update-status', ':id') }}'.replace(':id', itemId);
            const checkbox = document.getElementById(`checklist-item-checkbox-${itemId}`);
            fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const label = checkbox.closest('.form-check').querySelector('.form-check-label');
                        label.classList.toggle('text-decoration-line-through', checkbox.checked);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // function toggleChecklistItem(itemId) {
        //     const checkbox = document.getElementById(`checklist-item-checkbox-${itemId}`);
        //     const form = document.getElementById(`edit-checklist-form-${itemId}`);
        //     const formData = new FormData(form);
        //     formData.append('completed', checkbox.checked ? '1' : '0');

        //     fetch(form.action, {
        //         method: 'POST',
        //         headers: {
        //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
        //         },
        //         body: formData
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         if (data.success) {
        //             const itemElement = checkbox.closest('li');
        //             const label = checkbox.nextElementSibling;
        //             label.classList.toggle('text-decoration-line-through', checkbox.checked);
        //         }
        //     })
        //     .catch(error => console.error('Error:', error));
        // }

        function deleteChecklistItem(itemId) {
            const form = document.getElementById(`delete-checklist-form-${itemId}`);
            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`checklist-item-${itemId}`).remove();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // AJAX for adding checklist item
        document.getElementById('add-checklist-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);

            fetch('{{ route('checklist-items.store', $task->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log(data)
                        const checklistItem = document.createElement('li');
                        checklistItem.className =
                            'list-group-item d-flex justify-content-between align-items-center';
                        checklistItem.id = `checklist-item-${data.id}`;
                        checklistItem.innerHTML = `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checklist-item-checkbox-${data.id}"
                                onchange="toggleChecklistItem(${data.id})">
                            <label class="form-check-label">${data.name}</label>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editChecklistModal-${data.id}"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteChecklistItem(${data.id})"><i class="bi bi-trash"></i></button>
                        </div>
                    `;

                        document.getElementById('checklist-items').appendChild(checklistItem);
                        form.reset();
                        document.querySelector('#addChecklistModal .btn-close').click();
                    } else {
                        const errorElement = document.getElementById('checklist-name-error');
                        errorElement.textContent = data.message;
                        errorElement.style.display = 'block';
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        // Script pour la fonctionnalité de marquage des tâches
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion du bouton pour marquer une tâche comme terminée/non terminée
            const toggleCompleteBtn = document.querySelector('.toggle-complete');
            if (toggleCompleteBtn) {
                toggleCompleteBtn.addEventListener('click', function() {
                    const taskId = this.getAttribute('data-task-id');
                    const isCompleted = this.classList.contains('btn-warning');
                    
                    // Appel AJAX pour changer le statut
                    fetch(`/tasks/${taskId}/toggle-complete`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            completed: !isCompleted
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Mettre à jour l'interface utilisateur
                            if (isCompleted) {
                                // Changer en non terminé
                                this.classList.replace('btn-warning', 'btn-success');
                                this.querySelector('i').classList.replace('bi-x-circle', 'bi-check-circle');
                                this.innerHTML = '<i class="bi bi-check-circle"></i> Marquer comme terminée';
                                document.querySelector('p.card-text strong:contains("Status:")').nextElementSibling.innerHTML = '<span class="badge bg-primary">To Do</span>';
                            } else {
                                // Changer en terminé
                                this.classList.replace('btn-success', 'btn-warning');
                                this.querySelector('i').classList.replace('bi-check-circle', 'bi-x-circle');
                                this.innerHTML = '<i class="bi bi-x-circle"></i> Marquer comme non terminée';
                                document.querySelector('p.card-text strong:contains("Status:")').nextElementSibling.innerHTML = '<span class="badge bg-success">Completed</span>';
                                
                                // Afficher l'animation de confetti lorsqu'une tâche est marquée comme terminée
                                if (typeof window.showConfetti === 'function') {
                                    window.showConfetti();
                                }
                                
                                // Afficher un message de félicitations
                                if (typeof window.showSuccessMessage === 'function') {
                                    window.showSuccessMessage('Félicitations! Tâche terminée avec succès!');
                                }
                                
                                // Ajouter l'icône avec l'animation de complétion
                                const icon = document.createElement('i');
                                icon.className = 'bi bi-check-circle-fill task-completed-icon';
                                icon.style.color = 'var(--success)';
                                icon.style.fontSize = '2rem';
                                icon.style.position = 'absolute';
                                icon.style.top = '50%';
                                icon.style.left = '50%';
                                icon.style.transform = 'translate(-50%, -50%)';
                                icon.style.zIndex = '1000';
                                
                                document.body.appendChild(icon);
                                
                                // Supprimer l'icône après l'animation
                                setTimeout(() => {
                                    icon.remove();
                                }, 2000);
                            }
                            
                            // Recharger la page pour refléter tous les changements
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            alert('Une erreur est survenue lors de la mise à jour du statut de la tâche.');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la mise à jour du statut de la tâche.');
                    });
                });
            }
        });
    </script>
    
    <!-- Conteneur pour l'effet confetti -->
    <div class="confetti-container"></div>
@endsection

@section('scripts')
    <script>
        // Toggle de l'état d'une tâche (complété ou non)
        $('.toggle-complete').on('click', function() {
            let taskId = $(this).data('task-id');
            $.ajax({
                url: `/tasks/${taskId}/toggle-complete`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Mettre à jour l'interface en fonction du nouvel état
                        location.reload();
                    }
                }
            });
        });

        // Ajouter un élément à la checklist
        $('#add-checklist-form').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            
            $.ajax({
                url: "{{ route('checklist-items.store') }}",
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        let newItem = response.data;
                        let itemHtml = `
                            <li class="list-group-item d-flex justify-content-between align-items-center" id="checklist-item-${newItem.id}">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checklist-item-checkbox-${newItem.id}" onchange="toggleChecklistItem(${newItem.id})">
                                    <label class="form-check-label">${newItem.name}</label>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-primary btn-sm edit-checklist-item" data-item-id="${newItem.id}" data-item-name="${newItem.name}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm delete-checklist-item" data-item-id="${newItem.id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </li>
                        `;
                        $('#checklist-items').append(itemHtml);
                        $('#addChecklistModal').modal('hide');
                        $('#checklist-name').val('');
                    }
                }
            });
        });

        // Fonction pour changer l'état d'un élément de checklist
        function toggleChecklistItem(itemId) {
            $.ajax({
                url: `/checklist-items/${itemId}/update-status`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        let checkbox = $(`#checklist-item-checkbox-${itemId}`);
                        let label = checkbox.next('label');
                        
                        if (checkbox.is(':checked')) {
                            label.addClass('text-decoration-line-through');
                        } else {
                            label.removeClass('text-decoration-line-through');
                        }
                    }
                }
            });
        }

        // Gérer la suppression d'un élément de checklist
        $(document).on('click', '.delete-checklist-item', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet élément?')) {
                let itemId = $(this).data('item-id');
                
                $.ajax({
                    url: `/checklist-items/${itemId}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $(`#checklist-item-${itemId}`).fadeOut('slow', function() {
                                $(this).remove();
                            });
                        }
                    }
                });
            }
        });

        // Gérer la modification d'un élément de checklist
        $(document).on('click', '.edit-checklist-item', function() {
            let itemId = $(this).data('item-id');
            let itemName = $(this).data('item-name');
            
            // Créer une boîte de dialogue modale dynamique pour l'édition
            let modalHtml = `
                <div class="modal fade" id="editChecklistModal-${itemId}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Modifier l'élément</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="edit-item-name-${itemId}" value="${itemName}">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-primary save-checklist-edit" data-item-id="${itemId}">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Ajouter la modale au DOM s'il n'existe pas déjà
            if ($(`#editChecklistModal-${itemId}`).length === 0) {
                $('body').append(modalHtml);
            } else {
                $(`#edit-item-name-${itemId}`).val(itemName);
            }
            
            // Afficher la modale
            $(`#editChecklistModal-${itemId}`).modal('show');
        });

        // Sauvegarder les modifications d'un élément de checklist
        $(document).on('click', '.save-checklist-edit', function() {
            let itemId = $(this).data('item-id');
            let newName = $(`#edit-item-name-${itemId}`).val();
            
            if (newName.trim() === '') {
                alert('Le nom ne peut pas être vide');
                return;
            }
            
            $.ajax({
                url: `/checklist-items/${itemId}`,
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    name: newName
                },
                success: function(response) {
                    $(`#checklist-item-${itemId}`).find('label').text(newName);
                    $(`#editChecklistModal-${itemId}`).modal('hide');
                    
                    // Mettre à jour l'attribut data-item-name pour de futures éditions
                    $(`.edit-checklist-item[data-item-id="${itemId}"]`).data('item-name', newName);
                }
            });
        });
    </script>
@endsection
