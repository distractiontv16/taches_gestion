@extends('layouts.app')
@section('title')
    Paramètres
@endsection
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded-3">
                        <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="bi bi-person me-2"></i> Profil
                        </a>
                        <a href="#appearance" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-palette me-2"></i> Apparence
                        </a>
                        <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-shield-lock me-2"></i> Sécurité
                        </a>
                        <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-bell me-2"></i> Notifications
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Section Profil -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent py-3 d-flex align-items-center">
                            <h5 class="mb-0">Information de profil</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="position-relative">
                                            <div style="width: 100px; height: 100px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #6c757d; overflow: hidden;">
                                                <span>{{ substr(Auth::user()->name, 0, 1) }}</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 32px; height: 32px; padding: 0;">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                        </div>
                                        <div class="ms-4">
                                            <h5 class="mb-1">{{ Auth::user()->name }}</h5>
                                            <p class="text-muted mb-0">{{ Auth::user()->email }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Nom complet</label>
                                        <input type="text" class="form-control" id="name" value="{{ Auth::user()->name }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Adresse e-mail</label>
                                        <input type="email" class="form-control" id="email" value="{{ Auth::user()->email }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Biographie</label>
                                    <textarea class="form-control" id="bio" rows="3" placeholder="Parlez-nous de vous..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Section Apparence -->
                <div class="tab-pane fade" id="appearance">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent py-3">
                            <h5 class="mb-0">Personnalisation de l'interface</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6 class="mb-3">Thème</h6>
                                <div class="row g-3">
                                    <div class="col-auto">
                                        <div class="theme-option" onclick="setTheme('light')" id="theme-light">
                                            <div class="theme-preview light-theme">
                                                <div class="theme-sidebar"></div>
                                                <div class="theme-content">
                                                    <div class="theme-header"></div>
                                                    <div class="theme-body"></div>
                                                </div>
                                            </div>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="theme" id="lightTheme" checked>
                                                <label class="form-check-label" for="lightTheme">
                                                    Clair
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="theme-option" onclick="setTheme('dark')" id="theme-dark">
                                            <div class="theme-preview dark-theme">
                                                <div class="theme-sidebar"></div>
                                                <div class="theme-content">
                                                    <div class="theme-header"></div>
                                                    <div class="theme-body"></div>
                                                </div>
                                            </div>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="theme" id="darkTheme">
                                                <label class="form-check-label" for="darkTheme">
                                                    Sombre
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="theme-option" onclick="setTheme('system')" id="theme-system">
                                            <div class="theme-preview system-theme">
                                                <div class="theme-sidebar"></div>
                                                <div class="theme-content">
                                                    <div class="theme-header"></div>
                                                    <div class="theme-body"></div>
                                                </div>
                                            </div>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="theme" id="systemTheme">
                                                <label class="form-check-label" for="systemTheme">
                                                    Système
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-4">
                                <h6 class="mb-3">Couleur principale</h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <div class="color-option active" style="background-color: #3b82f6;" data-color="#3b82f6" onclick="setPrimaryColor(this)"></div>
                                    <div class="color-option" style="background-color: #8b5cf6;" data-color="#8b5cf6" onclick="setPrimaryColor(this)"></div>
                                    <div class="color-option" style="background-color: #ec4899;" data-color="#ec4899" onclick="setPrimaryColor(this)"></div>
                                    <div class="color-option" style="background-color: #ef4444;" data-color="#ef4444" onclick="setPrimaryColor(this)"></div>
                                    <div class="color-option" style="background-color: #f59e0b;" data-color="#f59e0b" onclick="setPrimaryColor(this)"></div>
                                    <div class="color-option" style="background-color: #10b981;" data-color="#10b981" onclick="setPrimaryColor(this)"></div>
                                    <div class="color-option" style="background-color: #06b6d4;" data-color="#06b6d4" onclick="setPrimaryColor(this)"></div>
                                    <div class="color-option" style="background-color: #6366f1;" data-color="#6366f1" onclick="setPrimaryColor(this)"></div>
                                    <div class="color-option custom-color">
                                        <input type="color" id="customColor" class="custom-color-picker" value="#3b82f6" onchange="setPrimaryColor(this.parentElement)">
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-4">
                                <h6 class="mb-3">Barre latérale</h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <div class="color-option active" style="background-color: #1e293b;" data-color="#1e293b" onclick="setSidebarColor(this)"></div>
                                    <div class="color-option" style="background-color: #0f172a;" data-color="#0f172a" onclick="setSidebarColor(this)"></div>
                                    <div class="color-option" style="background-color: #111827;" data-color="#111827" onclick="setSidebarColor(this)"></div>
                                    <div class="color-option" style="background-color: #374151;" data-color="#374151" onclick="setSidebarColor(this)"></div>
                                    <div class="color-option" style="background-color: #1e40af;" data-color="#1e40af" onclick="setSidebarColor(this)"></div>
                                    <div class="color-option" style="background-color: #6d28d9;" data-color="#6d28d9" onclick="setSidebarColor(this)"></div>
                                    <div class="color-option" style="background-color: #be185d;" data-color="#be185d" onclick="setSidebarColor(this)"></div>
                                    <div class="color-option" style="background-color: #be123c;" data-color="#be123c" onclick="setSidebarColor(this)"></div>
                                    <div class="color-option custom-color">
                                        <input type="color" id="customSidebarColor" class="custom-color-picker" value="#1e293b" onchange="setSidebarColor(this.parentElement)">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="animationsToggle" checked>
                                <label class="form-check-label" for="animationsToggle">Activer les animations</label>
                            </div>
                            
                            <button class="btn btn-primary me-2" id="saveAppearance">Enregistrer les modifications</button>
                            <button class="btn btn-outline-secondary" id="resetAppearance">Réinitialiser</button>
                        </div>
                    </div>
                </div>
                
                <!-- Section Sécurité -->
                <div class="tab-pane fade" id="security">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent py-3">
                            <h5 class="mb-0">Modifier votre mot de passe</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label">Mot de passe actuel</label>
                                    <input type="password" class="form-control" id="currentPassword">
                                </div>
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="newPassword">
                                    <div class="password-strength mt-2" id="passwordStrength">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small class="text-muted mt-1 d-block">Utilisez au moins 8 caractères, dont des lettres, des chiffres et des caractères spéciaux</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirmer le nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="confirmPassword">
                                </div>
                                <button type="submit" class="btn btn-primary">Mettre à jour le mot de passe</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent py-3">
                            <h5 class="mb-0">Sessions actives</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center p-3 border rounded mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-laptop fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="mb-0">Cet appareil</h6>
                                        <span class="badge bg-success">Actif</span>
                                    </div>
                                    <div class="text-muted small">Windows 10 · Chrome · {{ request()->ip() }}</div>
                                    <div class="text-muted small">Dernière activité: {{ now()->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                            
                            <button class="btn btn-outline-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Déconnecter toutes les autres sessions
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Section Notifications -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent py-3">
                            <h5 class="mb-0">Préférences de notification</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('settings.notifications') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">Email</h6>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailTasks" name="email_tasks" checked>
                                        <label class="form-check-label" for="emailTasks">Tâches à échéance proche</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailReminders" name="email_reminders" checked>
                                        <label class="form-check-label" for="emailReminders">Rappels</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailRoutines" name="email_routines">
                                        <label class="form-check-label" for="emailRoutines">Routines</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailSummary" name="email_summary">
                                        <label class="form-check-label" for="emailSummary">Résumé hebdomadaire</label>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">Application</h6>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="appNotifications" name="app_notifications" checked>
                                        <label class="form-check-label" for="appNotifications">Notifications dans l'application</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="soundNotifications" name="sound_notifications" checked>
                                        <label class="form-check-label" for="soundNotifications">Sons de notification</label>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">WhatsApp</h6>
                                    </div>
                                    <div class="mb-3">
                                        <label for="whatsapp_number" class="form-label">Numéro WhatsApp</label>
                                        <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" 
                                               placeholder="+33612345678" value="{{ Auth::user()->whatsapp_number }}">
                                        <div class="form-text">Format international (exemple: +33612345678)</div>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="whatsappNotifications" name="receive_whatsapp" checked>
                                        <label class="form-check-label" for="whatsappNotifications">Recevoir des notifications WhatsApp</label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Enregistrer les préférences</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styles pour les options de thème */
    .theme-option {
        cursor: pointer;
        padding: 5px;
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    .theme-option:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .theme-preview {
        width: 140px;
        height: 90px;
        border-radius: 6px;
        overflow: hidden;
        display: flex;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .light-theme {
        background-color: #f8fafc;
    }
    
    .dark-theme {
        background-color: #1e1e2d;
    }
    
    .system-theme {
        background: linear-gradient(to right, #f8fafc 50%, #1e1e2d 50%);
    }
    
    .theme-sidebar {
        width: 30%;
        height: 100%;
        background-color: #1e293b;
    }
    
    .dark-theme .theme-sidebar {
        background-color: #151521;
    }
    
    .system-theme .theme-sidebar {
        background: linear-gradient(to right, #1e293b 50%, #151521 50%);
    }
    
    .theme-content {
        width: 70%;
        height: 100%;
    }
    
    .theme-header {
        height: 15px;
        background-color: white;
    }
    
    .dark-theme .theme-header {
        background-color: #2a2a3c;
    }
    
    .system-theme .theme-header {
        background: linear-gradient(to right, white 50%, #2a2a3c 50%);
    }
    
    .theme-body {
        height: calc(100% - 15px);
        background-color: white;
        display: flex;
        flex-direction: column;
        padding: 3px;
    }
    
    .dark-theme .theme-body {
        background-color: #2a2a3c;
    }
    
    .system-theme .theme-body {
        background: linear-gradient(to right, white 50%, #2a2a3c 50%);
    }
    
    .theme-body::before, .theme-body::after {
        content: '';
        display: block;
        height: 4px;
        width: 70%;
        background-color: #e9ecef;
        margin-bottom: 3px;
        border-radius: 3px;
    }
    
    .dark-theme .theme-body::before, .dark-theme .theme-body::after {
        background-color: #3f4254;
    }
    
    .system-theme .theme-body::before, .system-theme .theme-body::after {
        background: linear-gradient(to right, #e9ecef 50%, #3f4254 50%);
    }
    
    /* Styles pour les options de couleur */
    .color-option {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
        transition: transform 0.2s;
        position: relative;
    }
    
    .color-option:hover {
        transform: scale(1.1);
    }
    
    .color-option.active::after {
        content: '';
        position: absolute;
        inset: -3px;
        border-radius: 50%;
        border: 2px solid var(--primary, #3b82f6);
    }
    
    .custom-color {
        background: linear-gradient(45deg, #f44336, #ff9800, #ffeb3b, #4caf50, #2196f3, #9c27b0);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    .custom-color-picker {
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    /* Styles pour la liste d'options */
    .list-group-item {
        border: none;
        padding: 0.8rem 1rem;
        color: #495057;
        font-weight: 500;
    }
    
    .list-group-item.active {
        background-color: var(--primary, #3b82f6);
        color: white;
    }
    
    .list-group-item:not(.active):hover {
        background-color: rgba(0, 0, 0, 0.04);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des onglets via URL hash
        const hash = window.location.hash;
        if (hash) {
            const tab = document.querySelector(`.list-group-item[href="${hash}"]`);
            if (tab) tab.click();
        }
        
        // Validation du mot de passe
        const newPassword = document.getElementById('newPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        const progressBar = document.querySelector('#passwordStrength .progress-bar');
        
        if (newPassword) {
            newPassword.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 8) strength += 25;
                if (password.match(/[A-Z]/)) strength += 25;
                if (password.match(/[0-9]/)) strength += 25;
                if (password.match(/[^A-Za-z0-9]/)) strength += 25;
                
                progressBar.style.width = strength + '%';
                
                if (strength < 50) {
                    progressBar.classList.remove('bg-warning', 'bg-success');
                    progressBar.classList.add('bg-danger');
                } else if (strength < 75) {
                    progressBar.classList.remove('bg-danger', 'bg-success');
                    progressBar.classList.add('bg-warning');
                } else {
                    progressBar.classList.remove('bg-danger', 'bg-warning');
                    progressBar.classList.add('bg-success');
                }
            });
        }
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (this.value === newPassword.value) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        }
        
        // Fonctions pour changer les thèmes et couleurs
        window.setTheme = function(theme) {
            document.querySelectorAll('[name="theme"]').forEach(radio => {
                radio.checked = radio.id === theme + 'Theme';
            });
            // Logique pour appliquer le thème
            localStorage.setItem('theme', theme);
            applyTheme(theme);
        };
        
        window.setPrimaryColor = function(element) {
            const color = element.dataset.color || element.querySelector('input').value;
            document.querySelectorAll('.color-option').forEach(option => {
                option.classList.remove('active');
            });
            element.classList.add('active');
            
            // Logique pour appliquer la couleur principale
            document.documentElement.style.setProperty('--primary', color);
            localStorage.setItem('primaryColor', color);
        };
        
        window.setSidebarColor = function(element) {
            const color = element.dataset.color || element.querySelector('input').value;
            document.querySelectorAll('.color-option:not(.custom-color)').forEach(option => {
                option.classList.remove('active');
            });
            element.classList.add('active');
            
            // Logique pour appliquer la couleur de la sidebar
            document.documentElement.style.setProperty('--sidebar-bg', color);
            localStorage.setItem('sidebarColor', color);
        };
        
        function applyTheme(theme) {
            // Logique pour appliquer le thème choisi
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
            } else if (theme === 'light') {
                document.body.classList.remove('dark-mode');
            } else if (theme === 'system') {
                // Détecter les préférences du système
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    document.body.classList.add('dark-mode');
                } else {
                    document.body.classList.remove('dark-mode');
                }
            }
        }
        
        // Charger les préférences sauvegardées
        const savedTheme = localStorage.getItem('theme') || 'light';
        const savedPrimaryColor = localStorage.getItem('primaryColor') || '#3b82f6';
        const savedSidebarColor = localStorage.getItem('sidebarColor') || '#1e293b';
        
        // Appliquer les préférences sauvegardées
        setTheme(savedTheme);
        document.documentElement.style.setProperty('--primary', savedPrimaryColor);
        document.documentElement.style.setProperty('--sidebar-bg', savedSidebarColor);
        
        // Initialiser les sélecteurs de couleur
        document.getElementById('customColor').value = savedPrimaryColor;
        document.getElementById('customSidebarColor').value = savedSidebarColor;
        
        // Enregistrer les modifications
        document.getElementById('saveAppearance').addEventListener('click', function() {
            // Sauvegarder le statut des animations
            const animationsEnabled = document.getElementById('animationsToggle').checked;
            localStorage.setItem('animationsEnabled', animationsEnabled);
            
            // Afficher une notification de succès
            window.showSuccessMessage('Préférences enregistrées avec succès !');
            
            // Appliquer immédiatement le paramètre d'animations
            if (!animationsEnabled) {
                document.body.classList.add('no-animations');
            } else {
                document.body.classList.remove('no-animations');
            }
        });
        
        // Réinitialiser les préférences
        document.getElementById('resetAppearance').addEventListener('click', function() {
            localStorage.removeItem('theme');
            localStorage.removeItem('primaryColor');
            localStorage.removeItem('sidebarColor');
            location.reload();
        });
    });
</script>
@endsection 