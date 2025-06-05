@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4 shadow-sm p-3 rounded bg-white">🔄 Créer une Routine</h2>
        <div class="card border-0 shadow-sm m-auto" style="max-width: 600px;">
            <div class="card-body">
                <form action="{{ route('routines.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label">📝 Titre de la routine</label>
                        <input type="text" name="title" id="title" class="form-control" required
                               placeholder="Ex: Vérification des sauvegardes">
                        @error('title')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">📄 Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"
                                  placeholder="Décrivez les détails de cette tâche routinière..."></textarea>
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="frequency" class="form-label">🔄 Fréquence</label>
                            <select name="frequency" id="frequency" class="form-select" required>
                                <option value="daily">Quotidienne</option>
                                <option value="weekly">Hebdomadaire</option>
                                <option value="monthly">Mensuelle</option>
                            </select>
                            @error('frequency')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">⚡ Priorité</label>
                            <select name="priority" id="priority" class="form-select" required>
                                <option value="low">Faible</option>
                                <option value="medium" selected>Moyenne</option>
                                <option value="high">Haute</option>
                            </select>
                            @error('priority')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="start_time" class="form-label">🕐 Heure de début</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                            @error('start_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="end_time" class="form-label">🕐 Heure de fin</label>
                            <input type="time" name="end_time" id="end_time" class="form-control" required>
                            @error('end_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="due_time" class="form-label">⏰ Heure d'échéance</label>
                            <input type="time" name="due_time" id="due_time" class="form-control"
                                   placeholder="Si différente de l'heure de fin">
                            @error('due_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Optionnel - Par défaut: heure de fin</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="workdays_only" id="workdays_only" value="1">
                                <label class="form-check-label" for="workdays_only">
                                    📅 Jours ouvrables uniquement (Lundi-Vendredi)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">
                                    ✅ Routine active (génération automatique)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="days" style="display: none;">
                        <label class="form-label">Select Days</label>
                        @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="days[]" value="{{ $day }}"
                                    id="{{ $day }}">
                                <label class="form-check-label" for="{{ $day }}">{{ ucfirst($day) }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="mb-3" id="weeks" style="display: none;">
                        <label class="form-label">Select Weeks</label>
                        @for ($i = 1; $i <= 52; $i++)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="weeks[]" value="{{ $i }}"
                                    id="week{{ $i }}">
                                <label class="form-check-label" for="week{{ $i }}">Week
                                    {{ $i }}</label>
                            </div>
                        @endfor
                    </div>
                    <div class="mb-3" id="months" style="display: none;">
                        <label class="form-label">Select Months</label>
                        @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="months[]" value="{{ $index + 1 }}"
                                    id="month{{ $index + 1 }}">
                                <label class="form-check-label" for="month{{ $index + 1 }}">{{ $month }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Créer la routine
                        </button>
                        <button type="button" class="btn btn-outline-info" id="previewBtn">
                            <i class="bi bi-eye"></i> Aperçu des tâches
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour l'aperçu -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">👁️ Aperçu des tâches générées</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const frequencyElement = document.getElementById('frequency');
            const daysElement = document.getElementById('days');
            const weeksElement = document.getElementById('weeks');
            const monthsElement = document.getElementById('months');
            const previewBtn = document.getElementById('previewBtn');

            function updateVisibility() {
                const value = frequencyElement.value;
                daysElement.style.display = 'none';
                weeksElement.style.display = 'none';
                monthsElement.style.display = 'none';

                if (value === 'daily') {
                    daysElement.style.display = 'block';
                } else if (value === 'weekly') {
                    weeksElement.style.display = 'block';
                } else if (value === 'monthly') {
                    monthsElement.style.display = 'block';
                }
            }

            // Fonction de prévisualisation (simulée pour le formulaire de création)
            function showPreview() {
                const title = document.getElementById('title').value;
                const frequency = document.getElementById('frequency').value;
                const priority = document.getElementById('priority').value;
                const dueTime = document.getElementById('due_time').value || document.getElementById('end_time').value;

                if (!title || !frequency) {
                    alert('Veuillez remplir au moins le titre et la fréquence pour voir l\'aperçu.');
                    return;
                }

                const previewContent = document.getElementById('previewContent');
                previewContent.innerHTML = `
                    <div class="alert alert-info">
                        <h6>Configuration de la routine :</h6>
                        <ul class="mb-0">
                            <li><strong>Titre :</strong> ${title}</li>
                            <li><strong>Fréquence :</strong> ${frequency}</li>
                            <li><strong>Priorité :</strong> ${priority}</li>
                            <li><strong>Heure d'échéance :</strong> ${dueTime || 'Non définie'}</li>
                        </ul>
                    </div>
                    <p class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        L'aperçu détaillé des tâches sera disponible après la création de la routine.
                    </p>
                `;

                const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                modal.show();
            }

            frequencyElement.addEventListener('change', updateVisibility);
            previewBtn.addEventListener('click', showPreview);
            updateVisibility(); // Call on load to set initial visibility
        });
    </script>
@endsection
