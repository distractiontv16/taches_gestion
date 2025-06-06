#!/bin/bash

# Installation Script for Real-Time Notifications - Phase 3
# SoNaMA IT Task Management System

echo "🔔 Installation du Système de Notifications en Temps Réel - Phase 3"
echo "=================================================================="

# Check if Laravel is installed
if [ ! -f "artisan" ]; then
    echo "❌ Erreur: Ce script doit être exécuté dans le répertoire racine de Laravel"
    exit 1
fi

echo "📦 Installation des dépendances..."

# Install Pusher PHP SDK if not already installed
if ! composer show pusher/pusher-php-server &> /dev/null; then
    echo "📥 Installation de Pusher PHP SDK..."
    composer require pusher/pusher-php-server
else
    echo "✅ Pusher PHP SDK déjà installé"
fi

# Check if broadcasting configuration exists
if [ ! -f "config/broadcasting.php" ]; then
    echo "📝 Création du fichier de configuration broadcasting..."
    cp config/broadcasting.php.example config/broadcasting.php 2>/dev/null || echo "⚠️  Fichier de configuration broadcasting déjà créé"
else
    echo "✅ Configuration broadcasting existante"
fi

echo "🔧 Configuration des permissions..."

# Make sure storage and cache directories are writable
chmod -R 775 storage bootstrap/cache

echo "🗄️  Mise à jour de la base de données..."

# Run migrations if needed
php artisan migrate --force

echo "🧹 Nettoyage du cache..."

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "📋 Génération des caches optimisés..."

# Generate optimized caches
php artisan config:cache
php artisan route:cache

echo "🧪 Exécution des tests..."

# Run real-time notification tests
php artisan test --filter=RealTimeNotificationTest

echo ""
echo "✅ Installation terminée avec succès !"
echo ""
echo "📋 Prochaines étapes :"
echo "1. Configurez vos clés Pusher dans le fichier .env :"
echo "   BROADCAST_CONNECTION=pusher"
echo "   PUSHER_APP_ID=your-app-id"
echo "   PUSHER_APP_KEY=your-app-key"
echo "   PUSHER_APP_SECRET=your-app-secret"
echo "   PUSHER_APP_CLUSTER=your-cluster"
echo ""
echo "2. Redémarrez votre serveur web"
echo ""
echo "3. Testez les notifications en temps réel en :"
echo "   - Créant une nouvelle tâche"
echo "   - Modifiant le statut d'une tâche existante"
echo "   - Observant les badges de notification"
echo ""
echo "📖 Documentation complète : REAL_TIME_NOTIFICATIONS.md"
echo ""
echo "🎉 Le système de notifications en temps réel Phase 3 est prêt !"
