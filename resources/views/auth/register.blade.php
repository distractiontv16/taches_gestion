<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte - Gestionnaire de Tâches</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/logo-circle.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-light: #f8fafc;
            --sidebar-bg: #1e293b;
            --sidebar-text: #f1f5f9;
            --text-dark: #1e293b;
        }
        
        body {
            min-height: 100vh;
            background-color: var(--bg-light);
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        .register-form-container {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: white;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .register-header {
            margin-bottom: 2rem;
        }

        .form-control {
            border: none;
            border-bottom: 2px solid #e2e8f0;
            border-radius: 0;
            padding: 0.75rem 0;
            font-weight: 300;
            background-color: transparent;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary);
        }

        .form-label {
            font-weight: 400;
            color: var(--text-dark);
            opacity: 0.8;
        }

        .btn-primary {
            background-color: var(--primary);
            border: none;
            border-radius: 30px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }

        .features-container {
            background: linear-gradient(135deg, var(--sidebar-bg), #111827);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .feature-title {
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 2.5rem;
            line-height: 1.2;
        }

        .workflow-container {
            position: relative;
            height: 400px;
            margin-top: 1rem;
            overflow: hidden;
        }

        .workflow-mockup {
            position: relative;
            width: 100%;
            height: 100%;
            transform: perspective(1000px) rotateX(10deg);
            transform-style: preserve-3d;
        }

        .workflow-board {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            gap: 15px;
            height: 100%;
            overflow: hidden;
        }

        .workflow-column {
            flex: 1;
            background-color: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .column-header {
            font-size: 0.9rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeIn 0.5s forwards;
        }

        .column-header .icon {
            margin-right: 6px;
            font-size: 1rem;
        }

        .workflow-card {
            background-color: rgba(255, 255, 255, 0.07);
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 8px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.5s forwards;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .workflow-card:hover {
            background-color: rgba(255, 255, 255, 0.12);
            transform: translateY(-3px);
        }

        .column-1 .column-header { animation-delay: 0.1s; }
        .column-2 .column-header { animation-delay: 0.2s; }
        .column-3 .column-header { animation-delay: 0.3s; }

        .card-title {
            font-size: 0.85rem;
            margin-bottom: 8px;
            color: white;
            font-weight: 500;
        }

        .card-tag {
            display: inline-block;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 3px;
            margin-right: 4px;
            color: white;
        }
        
        .card-tag.project {
            background-color: var(--primary);
        }
        
        .card-tag.due {
            background-color: var(--warning);
        }
        
        .card-tag.notes {
            background-color: var(--success);
        }

        .card-tag.routine {
            background-color: var(--danger);
        }

        .workflow-progress {
            height: 3px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            position: absolute;
            height: 100%;
            background-color: var(--primary);
            border-radius: 2px;
            width: 0;
            transition: width 1.5s cubic-bezier(0.19, 1, 0.22, 1);
        }

        .workflow-card.dragging {
            animation: pulse 1s infinite;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }

        .design-element {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.2), transparent 70%);
            z-index: 0;
        }

        .design-element-1 {
            width: 400px;
            height: 400px;
            bottom: -200px;
            right: -200px;
        }

        .design-element-2 {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
        }

        .workflow-arrow {
            position: absolute;
            width: 60px;
            height: 2px;
            background-color: rgba(255, 255, 255, 0.2);
            top: 50%;
            z-index: 2;
            opacity: 0;
            animation: fadeIn 0.5s forwards 1.5s;
        }

        .workflow-arrow::before,
        .workflow-arrow::after {
            content: '';
            position: absolute;
            right: 0;
            width: 10px;
            height: 2px;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .workflow-arrow::before {
            transform: rotate(45deg);
            top: -3px;
            right: 2px;
        }

        .workflow-arrow::after {
            transform: rotate(-45deg);
            top: 3px;
            right: 2px;
        }

        .arrow-1 {
            left: 33%;
            width: 40px;
        }

        .arrow-2 {
            left: 66%;
            width: 40px;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .register-container {
                flex-direction: column;
            }
            .features-container {
                padding: 2rem;
                order: -1; /* Place features above form on mobile */
            }
            .register-form-container {
                padding: 2rem;
            }
            .feature-title {
                font-size: 1.75rem;
            }
            .workflow-board {
                flex-direction: column;
                height: auto;
            }
            .workflow-column {
                margin-bottom: 15px;
            }
            .workflow-container {
                height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 register-container">
            <div class="col-lg-5 register-form-container">
                <div class="register-header text-center">
                    <img src="{{ asset('assets/img/logo-horizontal.png') }}" class="img-fluid mb-4" alt="gestionnaire de tâches" style="max-width: 250px;">
                    <h2 class="fw-light">Créez votre compte</h2>
                </div>
                
                <form method="POST" action="{{ route('register') }}" class="mb-4">
                        @csrf
                    <div class="mb-4">
                            <label for="name" class="form-label">Nom complet</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    <div class="mb-4">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        </div>
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary">S'inscrire</button>
                        </div>
                </form>
                
                        <div class="text-center">
                    <p>Vous avez déjà un compte ? <a href="{{ route('login') }}" class="text-decoration-none text-primary">Se connecter</a></p>
                    <p class="text-muted mt-5">&copy; {{ date('Y') }} Gestionnaire de Tâches</p>
                </div>
            </div>
            
            <div class="col-lg-7 features-container">
                <div class="design-element design-element-1"></div>
                <div class="design-element design-element-2"></div>
                
                <h1 class="feature-title">Simplifiez votre workflow</h1>
                <p class="mb-4 fw-light">Organisez vos projets, gérez vos tâches et augmentez votre productivité en quelques clics.</p>
                
                <div class="workflow-container">
                    <div class="workflow-mockup">
                        <div class="workflow-board">
                            <div class="workflow-column column-1">
                                <div class="column-header">
                                    <i class="bi bi-list-task icon"></i>
                                    À faire
                                </div>
                                <div class="workflow-card" style="animation-delay: 0.5s;">
                                    <div class="card-title">Préparation du rapport mensuel</div>
                                    <div class="card-tag project">Projet Marketing</div>
                                    <div class="card-tag due">Échéance: 15 Juin</div>
                                </div>
                                <div class="workflow-card" style="animation-delay: 0.7s;">
                                    <div class="card-title">Révision de la documentation</div>
                                    <div class="card-tag project">Site Web</div>
                                </div>
                                <div class="workflow-card" style="animation-delay: 0.9s;">
                                    <div class="card-title">Rendez-vous client</div>
                                    <div class="card-tag routine">Hebdomadaire</div>
                                    <div class="workflow-progress">
                                        <div class="progress-bar" data-progress="0"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="workflow-arrow arrow-1"></div>
                            
                            <div class="workflow-column column-2">
                                <div class="column-header">
                                    <i class="bi bi-clock-history icon"></i>
                                    En cours
                                </div>
                                <div class="workflow-card dragging" style="animation-delay: 0.6s;">
                                    <div class="card-title">Mise à jour du site web</div>
                                    <div class="card-tag project">Site Web</div>
                                    <div class="card-tag notes">3 notes</div>
                                    <div class="workflow-progress">
                                        <div class="progress-bar" data-progress="40"></div>
                                    </div>
                                </div>
                                <div class="workflow-card" style="animation-delay: 0.8s;">
                                    <div class="card-title">Préparation de la présentation</div>
                                    <div class="card-tag project">Conférence</div>
                                    <div class="workflow-progress">
                                        <div class="progress-bar" data-progress="60"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="workflow-arrow arrow-2"></div>
                            
                            <div class="workflow-column column-3">
                                <div class="column-header">
                                    <i class="bi bi-check-circle icon"></i>
                                    Terminé
                                </div>
                                <div class="workflow-card" style="animation-delay: 0.7s;">
                                    <div class="card-title">Révision du design</div>
                                    <div class="card-tag project">Site Web</div>
                                    <div class="workflow-progress">
                                        <div class="progress-bar" data-progress="100"></div>
                                    </div>
                                </div>
                                <div class="workflow-card" style="animation-delay: 0.9s;">
                                    <div class="card-title">Réunion hebdomadaire</div>
                                    <div class="card-tag routine">Hebdomadaire</div>
                                    <div class="workflow-progress">
                                        <div class="progress-bar" data-progress="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-center mt-4 fw-light" style="opacity: 0.8">
                    <i class="bi bi-arrow-left-right me-1"></i> Utilisez le glisser-déposer pour déplacer vos tâches entre les statuts
                </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation des barres de progression
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const progress = bar.getAttribute('data-progress');
                setTimeout(() => {
                    bar.style.width = progress + '%';
                }, 1000);
            });
            
            // Animation de glisser-déposer pour la carte en mouvement
            const draggingCard = document.querySelector('.workflow-card.dragging');
            if (draggingCard) {
                setInterval(() => {
                    const currentColumn = draggingCard.parentElement;
                    const columns = document.querySelectorAll('.workflow-column');
                    const currentIndex = Array.from(columns).findIndex(col => col === currentColumn);
                    
                    // Trouver la prochaine colonne (avec boucle)
                    const nextIndex = (currentIndex + 1) % columns.length;
                    const nextColumn = columns[nextIndex];
                    
                    // Déplacer la carte
                    if (nextColumn !== currentColumn) {
                        draggingCard.style.opacity = '0';
                        draggingCard.style.transform = 'translateY(10px)';
                        
                        setTimeout(() => {
                            nextColumn.appendChild(draggingCard);
                            setTimeout(() => {
                                draggingCard.style.opacity = '1';
                                draggingCard.style.transform = 'translateY(0)';
                            }, 100);
                        }, 300);
                    }
                }, 5000); // Déplacer la carte toutes les 5 secondes
            }
        });
    </script>
</body>
</html> 