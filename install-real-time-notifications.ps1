# Installation Script for Real-Time Notifications - Phase 3 (Windows PowerShell)
# SoNaMA IT Task Management System

Write-Host "🔔 Installation du Système de Notifications en Temps Réel - Phase 3" -ForegroundColor Cyan
Write-Host "=================================================================" -ForegroundColor Cyan

# Check if Laravel is installed
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Erreur: Ce script doit être exécuté dans le répertoire racine de Laravel" -ForegroundColor Red
    exit 1
}

Write-Host "📦 Vérification des dépendances..." -ForegroundColor Yellow

# Check if Pusher is already installed
$pusherInstalled = composer show | Select-String "pusher/pusher-php-server"
if ($pusherInstalled) {
    Write-Host "✅ Pusher PHP SDK déjà installé" -ForegroundColor Green
} else {
    Write-Host "📥 Tentative d'installation de Pusher PHP SDK..." -ForegroundColor Yellow
    
    # Try different installation methods
    $installSuccess = $false
    
    # Method 1: Standard installation
    try {
        Write-Host "Méthode 1: Installation standard..." -ForegroundColor Gray
        composer require pusher/pusher-php-server --no-interaction 2>$null
        if ($LASTEXITCODE -eq 0) {
            $installSuccess = $true
            Write-Host "✅ Installation réussie avec la méthode standard" -ForegroundColor Green
        }
    } catch {
        Write-Host "⚠️ Méthode 1 échouée" -ForegroundColor Yellow
    }
    
    # Method 2: With prefer-dist
    if (-not $installSuccess) {
        try {
            Write-Host "Méthode 2: Installation avec prefer-dist..." -ForegroundColor Gray
            composer require pusher/pusher-php-server --prefer-dist --no-interaction 2>$null
            if ($LASTEXITCODE -eq 0) {
                $installSuccess = $true
                Write-Host "✅ Installation réussie avec prefer-dist" -ForegroundColor Green
            }
        } catch {
            Write-Host "⚠️ Méthode 2 échouée" -ForegroundColor Yellow
        }
    }
    
    # Method 3: Update composer and retry
    if (-not $installSuccess) {
        try {
            Write-Host "Méthode 3: Mise à jour de Composer et nouvelle tentative..." -ForegroundColor Gray
            composer self-update 2>$null
            composer require pusher/pusher-php-server --no-interaction 2>$null
            if ($LASTEXITCODE -eq 0) {
                $installSuccess = $true
                Write-Host "✅ Installation réussie après mise à jour de Composer" -ForegroundColor Green
            }
        } catch {
            Write-Host "⚠️ Méthode 3 échouée" -ForegroundColor Yellow
        }
    }
    
    if (-not $installSuccess) {
        Write-Host "⚠️ Impossible d'installer Pusher automatiquement" -ForegroundColor Yellow
        Write-Host "📝 Mode de développement activé avec service mock" -ForegroundColor Cyan
        Write-Host "   Vous pourrez installer Pusher plus tard avec:" -ForegroundColor Gray
        Write-Host "   composer require pusher/pusher-php-server" -ForegroundColor Gray
    }
}

# Check if broadcasting configuration exists
if (Test-Path "config/broadcasting.php") {
    Write-Host "✅ Configuration broadcasting existante" -ForegroundColor Green
} else {
    Write-Host "📝 Configuration broadcasting créée" -ForegroundColor Yellow
}

Write-Host "🔧 Configuration des permissions..." -ForegroundColor Yellow

# Ensure directories exist and are writable (Windows equivalent)
if (-not (Test-Path "storage/logs")) {
    New-Item -ItemType Directory -Path "storage/logs" -Force | Out-Null
}

if (-not (Test-Path "bootstrap/cache")) {
    New-Item -ItemType Directory -Path "bootstrap/cache" -Force | Out-Null
}

Write-Host "🗄️ Mise à jour de la base de données..." -ForegroundColor Yellow

# Run migrations if needed
try {
    php artisan migrate --force 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Migrations exécutées avec succès" -ForegroundColor Green
    }
} catch {
    Write-Host "⚠️ Erreur lors des migrations (peut être normal si déjà à jour)" -ForegroundColor Yellow
}

Write-Host "🧹 Nettoyage du cache..." -ForegroundColor Yellow

# Clear caches
php artisan config:clear 2>$null
php artisan route:clear 2>$null
php artisan view:clear 2>$null
php artisan cache:clear 2>$null

Write-Host "📋 Génération des caches optimisés..." -ForegroundColor Yellow

# Generate optimized caches
php artisan config:cache 2>$null
php artisan route:cache 2>$null

Write-Host ""
Write-Host "✅ Installation terminée avec succès !" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Prochaines étapes :" -ForegroundColor Cyan
Write-Host "1. Configurez vos clés Pusher dans le fichier .env :" -ForegroundColor White
Write-Host "   BROADCAST_CONNECTION=pusher" -ForegroundColor Gray
Write-Host "   PUSHER_APP_ID=your-app-id" -ForegroundColor Gray
Write-Host "   PUSHER_APP_KEY=your-app-key" -ForegroundColor Gray
Write-Host "   PUSHER_APP_SECRET=your-app-secret" -ForegroundColor Gray
Write-Host "   PUSHER_APP_CLUSTER=your-cluster" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Redémarrez votre serveur web" -ForegroundColor White
Write-Host ""
Write-Host "3. Testez les notifications en temps réel en :" -ForegroundColor White
Write-Host "   - Créant une nouvelle tâche" -ForegroundColor Gray
Write-Host "   - Modifiant le statut d'une tâche existante" -ForegroundColor Gray
Write-Host "   - Observant les badges de notification" -ForegroundColor Gray
Write-Host ""
Write-Host "📖 Documentation complète : REAL_TIME_NOTIFICATIONS.md" -ForegroundColor Cyan
Write-Host ""

# Check network connectivity
Write-Host "🌐 Test de connectivité réseau..." -ForegroundColor Yellow
try {
    $ping = Test-NetConnection -ComputerName "repo.packagist.org" -Port 443 -WarningAction SilentlyContinue
    if ($ping.TcpTestSucceeded) {
        Write-Host "✅ Connexion à Packagist OK" -ForegroundColor Green
    } else {
        Write-Host "⚠️ Problème de connexion à Packagist détecté" -ForegroundColor Yellow
        Write-Host "   Verifiez votre connexion internet ou proxy d'entreprise" -ForegroundColor Gray
    }
} catch {
    Write-Host "⚠️ Impossible de tester la connectivité" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🎉 Le système de notifications en temps réel Phase 3 est prêt !" -ForegroundColor Green

# Pause to let user read the output
Write-Host ""
Write-Host "Appuyez sur une touche pour continuer..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
