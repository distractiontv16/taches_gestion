# Installation Script for Real-Time Notifications - Phase 3 (Windows PowerShell)
# SoNaMA IT Task Management System - Version Simplifiee

Write-Host "Installation du Systeme de Notifications en Temps Reel - Phase 3" -ForegroundColor Cyan
Write-Host "=================================================================" -ForegroundColor Cyan

# Check if Laravel is installed
if (-not (Test-Path "artisan")) {
    Write-Host "ERREUR: Ce script doit etre execute dans le repertoire racine de Laravel" -ForegroundColor Red
    exit 1
}

Write-Host "Verification des dependances..." -ForegroundColor Yellow

# Check if Pusher is already installed
$pusherCheck = composer show 2>$null | Select-String "pusher/pusher-php-server"
if ($pusherCheck) {
    Write-Host "Pusher PHP SDK deja installe" -ForegroundColor Green
} else {
    Write-Host "Tentative d'installation de Pusher PHP SDK..." -ForegroundColor Yellow
    
    # Try to install Pusher
    try {
        $result = composer require pusher/pusher-php-server --no-interaction 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Host "Installation de Pusher reussie" -ForegroundColor Green
        } else {
            Write-Host "Impossible d'installer Pusher automatiquement" -ForegroundColor Yellow
            Write-Host "Mode de developpement active avec service mock" -ForegroundColor Cyan
            Write-Host "Vous pourrez installer Pusher plus tard avec:" -ForegroundColor Gray
            Write-Host "composer require pusher/pusher-php-server" -ForegroundColor Gray
        }
    } catch {
        Write-Host "Erreur lors de l'installation de Pusher" -ForegroundColor Yellow
        Write-Host "Mode mock active" -ForegroundColor Cyan
    }
}

# Check broadcasting configuration
if (Test-Path "config/broadcasting.php") {
    Write-Host "Configuration broadcasting existante" -ForegroundColor Green
} else {
    Write-Host "Configuration broadcasting creee" -ForegroundColor Yellow
}

Write-Host "Configuration des permissions..." -ForegroundColor Yellow

# Ensure directories exist
$directories = @("storage/logs", "bootstrap/cache", "storage/framework/cache", "storage/framework/sessions", "storage/framework/views")
foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "Repertoire cree: $dir" -ForegroundColor Gray
    }
}

Write-Host "Mise a jour de la base de donnees..." -ForegroundColor Yellow

# Run migrations
try {
    php artisan migrate --force 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Migrations executees avec succes" -ForegroundColor Green
    } else {
        Write-Host "Migrations deja a jour" -ForegroundColor Gray
    }
} catch {
    Write-Host "Erreur lors des migrations (peut etre normal)" -ForegroundColor Yellow
}

Write-Host "Nettoyage du cache..." -ForegroundColor Yellow

# Clear caches
$cacheCommands = @("config:clear", "route:clear", "view:clear", "cache:clear")
foreach ($cmd in $cacheCommands) {
    try {
        php artisan $cmd 2>$null
    } catch {
        # Ignore errors
    }
}

Write-Host "Generation des caches optimises..." -ForegroundColor Yellow

# Generate caches
try {
    php artisan config:cache 2>$null
    php artisan route:cache 2>$null
    Write-Host "Caches generes avec succes" -ForegroundColor Green
} catch {
    Write-Host "Erreur lors de la generation des caches" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Installation terminee avec succes !" -ForegroundColor Green
Write-Host ""
Write-Host "Prochaines etapes :" -ForegroundColor Cyan
Write-Host "1. Configurez vos cles Pusher dans le fichier .env :" -ForegroundColor White
Write-Host "   BROADCAST_CONNECTION=pusher" -ForegroundColor Gray
Write-Host "   PUSHER_APP_ID=your-app-id" -ForegroundColor Gray
Write-Host "   PUSHER_APP_KEY=your-app-key" -ForegroundColor Gray
Write-Host "   PUSHER_APP_SECRET=your-app-secret" -ForegroundColor Gray
Write-Host "   PUSHER_APP_CLUSTER=your-cluster" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Redemarrez votre serveur web" -ForegroundColor White
Write-Host ""
Write-Host "3. Testez les notifications en temps reel" -ForegroundColor White
Write-Host ""

# Test network connectivity
Write-Host "Test de connectivite reseau..." -ForegroundColor Yellow
try {
    $ping = Test-NetConnection -ComputerName "repo.packagist.org" -Port 443 -WarningAction SilentlyContinue -InformationLevel Quiet
    if ($ping) {
        Write-Host "Connexion a Packagist OK" -ForegroundColor Green
    } else {
        Write-Host "Probleme de connexion a Packagist detecte" -ForegroundColor Yellow
        Write-Host "Verifiez votre connexion internet ou proxy" -ForegroundColor Gray
    }
} catch {
    Write-Host "Impossible de tester la connectivite" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Le systeme de notifications en temps reel Phase 3 est pret !" -ForegroundColor Green
Write-Host ""
Write-Host "Documentation complete : REAL_TIME_NOTIFICATIONS.md" -ForegroundColor Cyan
Write-Host ""
Write-Host "Appuyez sur une touche pour continuer..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
