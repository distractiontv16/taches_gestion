<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur Gestionnaire de Tâches</title>
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

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        .login-form-container {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: white;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .login-header {
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

        .features-slider {
            position: relative;
            height: 300px;
        }

        .feature-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .feature-slide.active {
            opacity: 1;
            transform: translateY(0);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .feature-description {
            font-weight: 300;
            line-height: 1.6;
            opacity: 0.9;
        }

        .slider-dots {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.3);
            margin: 0 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dot.active {
            background-color: white;
            transform: scale(1.2);
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

        @keyframes float {
            0% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
            100% {
                transform: translateY(0px) rotate(0deg);
            }
        }

        .animated-device {
            animation: float 6s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
            }
            .features-container {
                padding: 2rem;
                order: -1; /* Place features above form on mobile */
            }
            .login-form-container {
                padding: 2rem;
            }
            .feature-title {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 login-container">
            <div class="col-lg-5 login-form-container">
                <div class="login-header text-center">
                    <img src="{{ asset('assets/img/logo-horizontal.png') }}" class="img-fluid mb-4" alt="gestionnaire de tâches" style="max-width: 250px;">
                    <h2 class="fw-light">Connectez-vous à votre compte</h2>
                </div>
                
                <form method="POST" action="{{ route('login') }}" class="mb-4">
                        @csrf
                    <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="admin@example.com" required autofocus>
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
                    <div class="mb-4 form-check">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">Se souvenir de moi</label>
                        </div>
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </div>
                </form>
                
                        <div class="text-center">
                    <p>Vous n'avez pas de compte ? <a href="{{ route('register') }}" class="text-decoration-none text-primary">Créer un compte</a></p>
                    <p><a href="{{ route('password.request') }}" class="text-decoration-none text-primary">Mot de passe oublié ?</a></p>
                    <p class="text-muted mt-5">&copy; {{ date('Y') }} Gestionnaire de Tâches</p>
                </div>
            </div>
            
            <div class="col-lg-7 features-container">
                <div class="design-element design-element-1"></div>
                <div class="design-element design-element-2"></div>
                
                <h1 class="feature-title">Simplifiez votre organisation avec notre Gestionnaire de Tâches</h1>
                
                <div class="features-slider">
                    <div class="feature-slide active" data-index="0">
                        <div class="feature-icon">
                            <i class="bi bi-check2-square"></i>
                        </div>
                        <h3>Gestion de tâches intuitive</h3>
                        <p class="feature-description">Créez, organisez et suivez vos tâches avec une interface élégante et facile à utiliser. Définissez des priorités et ne manquez jamais une échéance.</p>
                    </div>
                    
                    <div class="feature-slide" data-index="1">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h3>Routines personnalisées</h3>
                        <p class="feature-description">Établissez des routines quotidiennes, hebdomadaires ou mensuelles pour maintenir votre productivité et atteindre vos objectifs.</p>
                    </div>
                    
                    <div class="feature-slide" data-index="2">
                        <div class="feature-icon">
                            <i class="bi bi-folder"></i>
                        </div>
                        <h3>Projets organisés</h3>
                        <p class="feature-description">Regroupez vos tâches dans des projets pour une meilleure organisation et visualisez votre progression en temps réel.</p>
                </div>

                    <div class="feature-slide" data-index="3">
                        <div class="feature-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                        <h3>Rappels intelligents</h3>
                        <p class="feature-description">Configurez des rappels pour ne jamais manquer une tâche importante ou une échéance cruciale.</p>
                    </div>
                </div>

                <div class="slider-dots">
                    <div class="dot active" data-index="0"></div>
                    <div class="dot" data-index="1"></div>
                    <div class="dot" data-index="2"></div>
                    <div class="dot" data-index="3"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Slider functionality
            const slides = document.querySelectorAll('.feature-slide');
            const dots = document.querySelectorAll('.dot');
            let currentIndex = 0;
            let interval;

            function showSlide(index) {
                // Hide all slides
                slides.forEach(slide => {
                    slide.classList.remove('active');
                });
                dots.forEach(dot => {
                    dot.classList.remove('active');
                });

                // Show selected slide
                slides[index].classList.add('active');
                dots[index].classList.add('active');
                currentIndex = index;
            }

            // Auto advance slides
            function startSlideshow() {
                interval = setInterval(() => {
                    let nextIndex = (currentIndex + 1) % slides.length;
                    showSlide(nextIndex);
                }, 4000);
            }

            // Click on dots
            dots.forEach(dot => {
                dot.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    showSlide(index);
                    clearInterval(interval);
                    startSlideshow();
                });
            });

            // Start the slideshow
            startSlideshow();
        });
    </script>
</body>
</html>
