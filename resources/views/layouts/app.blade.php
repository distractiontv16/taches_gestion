<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('title') | Gestionnaire de Tâches </title>
    <link rel="shortcut icon" href="{{ asset('assets/img/logo-circle.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/responsive.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: rgb(241 245 249);
            font-family: "Noto Sans", sans-serif !important; 
            overflow-x: hidden;
        }

        @media (min-width: 992px) {
            body {
                flex-direction: row;
                overflow: hidden;
                height: 100vh;
            }
        }

        .btn {
            padding: .25rem .5rem !important;
            font-size: .875rem !important;
        }

        .sidebar {
            width: 100%;
            background-color: #343a40;
            color: white;
            z-index: 1030;
        }

        @media (min-width: 992px) {
            .sidebar {
                width: 250px;
                flex-shrink: 0;
                display: flex;
                flex-direction: column;
                height: 100vh;
                position: sticky;
                top: 0;
            }
        }

        .sidebar .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #495057;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #495057;
            border-radius: 0.25rem;
        }

        .sidebar .nav-link .bi {
            margin-right: 10px;
        }

        .sidebar-toggle {
            display: block;
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1040;
            background-color: #343a40;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (min-width: 992px) {
            .sidebar-toggle {
                display: none;
            }
        }

        .content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .topnav {
            flex-shrink: 0;
            width: 100%;
            background-color: #ffffff;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
        }

        .navbar-brand {
            font-weight: bold;
            color: #343a40;
        }

        .navbar-nav .nav-link {
            color: #343a40;
        }

        .navbar-nav .nav-link:hover {
            color: #007bff;
        }

        .card {
            border: none;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
        }

        footer {
            background-color: #ffffff;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
            flex-shrink: 0;
        }

        main {
            flex-grow: 1;
            padding: 15px;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                height: 100vh;
                width: 250px;
                transition: left 0.3s ease;
                overflow-y: auto;
            }

            .sidebar.show {
                left: 0;
            }

            .sidebar-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1020;
                display: none;
            }

            .sidebar-backdrop.show {
                display: block;
            }
        }

        @media (max-width: 576px) {
            #currentDateTime {
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>
    @if(Auth::check())
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
    @endif
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="sidebar" id="sidebar">
        <h4 class="mb-4 text-center p-3">
            <a href="{{ route('dashboard') }}">
                <img style=" filter: invert(100%) brightness(200%);"
                    src="{{ asset('assets/img/logo-circle-horizontal.png') }}" class="img-fluid" width="100%"
                    alt="gestionnaire de tâches">
            </a>
        </h4>
        @if(Auth::check())
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-house-door"></i> Accueil
                </a>
            </li>
            {{-- <li class="nav-item">
                <a class="nav-link {{ request()->is('mail*') ? 'active' : '' }}" href="{{ route('mail.inbox') }}">
                    <i class="bi bi-inbox"></i> Boîte de réception
                </a>
            </li> --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->is('projects*') ? 'active' : '' }}"
                    href="{{ route('projects.index') }}">
                    <i class="bi bi-folder"></i> Projets
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('tasks*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
                    <i class="bi bi-check2-square"></i> Tâches
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('routines*') ? 'active' : '' }}"
                    href="{{ route('routines.index') }}">
                    <i class="bi bi-calendar-check"></i> Routines
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('notes*') ? 'active' : '' }}" href="{{ route('notes.index') }}">
                    <i class="bi bi-sticky"></i> Notes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('reminders*') ? 'active' : '' }}"
                    href="{{ route('reminders.index') }}">
                    <i class="bi bi-bell"></i> Rappels
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('files*') ? 'active' : '' }}" href="{{ route('files.index') }}">
                    <i class="bi bi-file"></i> Fichiers
                </a>
            </li>
        </ul>
        @else
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('login') }}">
                    <i class="bi bi-box-arrow-in-right"></i> Connexion
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('register') }}">
                    <i class="bi bi-person-plus"></i> Inscription
                </a>
            </li>
        </ul>
        @endif
    </div>
    <div class="content">
        <header class="topnav mb-4">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <a class="navbar-brand" href="{{ route('dashboard') }}">
                        <span class="fw-normal" id="currentDateTime"></span>
                    </a>
                    <div class="navbar-collapse justify-content-end" id="navbarNavDropdown">
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-bell"></i>
                                    <span class="badge rounded-pill bg-danger">
                                        @if(Auth::check())
                                            {{ Auth::user()->tasks()->where('status', '!=', 'completed')->count() }}
                                        @else
                                            0
                                        @endif
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                                    <li class="dropdown-header">Tâches non terminées</li>
                                    @if(Auth::check())
                                        @forelse (Auth::user()->tasks()->where('status', '!=', 'completed')->get() as $task)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('tasks.show', $task->id) }}">
                                                    <span class="fw-bold">{{ Str::limit($task->title, 30) }}</span>
                                                    <br>
                                                    <small class="text-muted">Échéance: {{ $task->due_date ?: 'Non définie' }}</small>
                                                </a>
                                            </li>
                                        @empty
                                            <li><span class="dropdown-item">Aucune tâche en attente</span></li>
                                        @endforelse
                                    @else
                                        <li><span class="dropdown-item">Aucune tâche en attente</span></li>
                                    @endif
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    @if(Auth::check())
                                        {{ Auth::user()->name }}
                                    @else
                                        Compte
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    @if(Auth::check())
                                        <li><a class="dropdown-item" href="{{ route('settings') }}">
                                            <i class="bi bi-gear me-2"></i>Paramètres
                                        </a></li>
                                        <li>
                                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                                </button>
                                            </form>
                                        </li>
                                    @else
                                        <li><a class="dropdown-item" href="{{ route('login') }}">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Connexion
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('register') }}">
                                            <i class="bi bi-person-plus me-2"></i>Inscription
                                        </a></li>
                                    @endif
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <main>
            @yield('content')
        </main>
        <footer class="mt-auto py-3 text-center">
            <div class="container">
                <span class="text-muted">&copy; {{ date('Y') }} Gestionnaire de Tâches</span>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/animations.js') }}"></script>
    <script>
        function updateDateTime() {
            const now = new Date();
            const dayNames = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
            const day = dayNames[now.getDay()];
            const date = now.toLocaleDateString(['fr-FR'], { day: 'numeric', month: 'long', year: 'numeric' });
            const time = now.toLocaleTimeString();

            document.getElementById('currentDateTime').innerText = `${day}, ${date}  ${time}`;
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Code pour appliquer les paramètres d'apparence sur toutes les pages
        document.addEventListener('DOMContentLoaded', function() {
            // Charger les préférences d'apparence sauvegardées dans le localStorage
            const savedTheme = localStorage.getItem('theme') || 'light';
            const savedPrimaryColor = localStorage.getItem('primaryColor') || '#3b82f6';
            const savedSidebarColor = localStorage.getItem('sidebarColor') || '#1e293b';
            const animationsEnabled = localStorage.getItem('animationsEnabled') !== 'false';
            
            // Appliquer le thème
            applyTheme(savedTheme);
            
            // Appliquer les couleurs personnalisées
            document.documentElement.style.setProperty('--primary', savedPrimaryColor);
            document.documentElement.style.setProperty('--sidebar-bg', savedSidebarColor);
            
            // Appliquer les paramètres d'animation
            if (!animationsEnabled) {
                document.body.classList.add('no-animations');
            }
            
            // Fonction pour appliquer le thème
            function applyTheme(theme) {
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

            // Toggle sidebar pour responsive
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const navLinks = document.querySelectorAll('.sidebar .nav-link');

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarBackdrop.classList.toggle('show');
                });
            }

            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarBackdrop.classList.remove('show');
                });
            }

            // Fermer la sidebar quand on clique sur un lien (en mobile)
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        sidebar.classList.remove('show');
                        sidebarBackdrop.classList.remove('show');
                    }
                });
            });

            // Adapter le layout en cas de redimensionnement de fenêtre
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('show');
                    sidebarBackdrop.classList.remove('show');
                }
            });
        });
    </script>
    @yield('scripts')
</body>

</html>
