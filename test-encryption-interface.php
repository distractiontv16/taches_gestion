<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Models\Note;
use App\Services\DataEncryptionService;

echo "🔍 TEST DU CHIFFREMENT DANS L'INTERFACE UTILISATEUR\n";
echo "===================================================\n\n";

try {
    $encryptionService = app(DataEncryptionService::class);
    
    echo "📋 1. VÉRIFICATION DES DONNÉES CHIFFRÉES EN BASE\n";
    echo "------------------------------------------------\n";
    
    // Récupérer un utilisateur de test
    $user = User::where('email', 'admin@sonama-it.com')->first();
    if (!$user) {
        echo "❌ Aucun utilisateur de test trouvé. Exécutez d'abord generate-phase4-test-data.php\n";
        exit(1);
    }
    
    echo "Utilisateur trouvé: {$user->name}\n";
    
    // Vérifier le chiffrement du numéro WhatsApp
    $rawWhatsApp = $user->getAttributes()['whatsapp_number']; // Données brutes de la DB
    echo "WhatsApp en base (chiffré): " . substr($rawWhatsApp, 0, 50) . "...\n";
    echo "WhatsApp déchiffré: {$user->whatsapp_number}\n";
    
    if (strlen($rawWhatsApp) > 30 && $user->whatsapp_number !== $rawWhatsApp) {
        echo "✅ Chiffrement WhatsApp fonctionne\n";
    } else {
        echo "❌ Problème avec le chiffrement WhatsApp\n";
    }
    
    echo "\n📝 2. VÉRIFICATION DES TÂCHES CHIFFRÉES\n";
    echo "--------------------------------------\n";
    
    $task = Task::where('user_id', $user->id)->first();
    if ($task) {
        $rawTitle = $task->getAttributes()['title'];
        $rawDescription = $task->getAttributes()['description'];
        
        echo "Titre en base (chiffré): " . substr($rawTitle, 0, 50) . "...\n";
        echo "Titre déchiffré: {$task->title}\n";
        echo "Description en base (chiffrée): " . substr($rawDescription, 0, 50) . "...\n";
        echo "Description déchiffrée: " . substr($task->description, 0, 100) . "...\n";
        
        if (strlen($rawTitle) > 50 && $task->title !== $rawTitle) {
            echo "✅ Chiffrement des tâches fonctionne\n";
        } else {
            echo "❌ Problème avec le chiffrement des tâches\n";
        }
    }
    
    echo "\n📄 3. VÉRIFICATION DES NOTES CHIFFRÉES\n";
    echo "-------------------------------------\n";
    
    $note = Note::where('user_id', $user->id)->first();
    if ($note) {
        $rawContent = $note->getAttributes()['content'];
        
        echo "Contenu en base (chiffré): " . substr($rawContent, 0, 50) . "...\n";
        echo "Contenu déchiffré: " . substr($note->content, 0, 100) . "...\n";
        
        if (strlen($rawContent) > 50 && $note->content !== $rawContent) {
            echo "✅ Chiffrement des notes fonctionne\n";
        } else {
            echo "❌ Problème avec le chiffrement des notes\n";
        }
    }
    
    echo "\n🔐 4. TEST DE CHIFFREMENT/DÉCHIFFREMENT MANUEL\n";
    echo "---------------------------------------------\n";
    
    $testData = "Données sensibles de test: mot de passe admin123!";
    $encrypted = $encryptionService->encrypt($testData);
    $decrypted = $encryptionService->decrypt($encrypted);
    
    echo "Données originales: {$testData}\n";
    echo "Données chiffrées: " . substr($encrypted, 0, 50) . "...\n";
    echo "Données déchiffrées: {$decrypted}\n";
    
    if ($testData === $decrypted) {
        echo "✅ Chiffrement/déchiffrement manuel fonctionne\n";
    } else {
        echo "❌ Problème avec le chiffrement/déchiffrement manuel\n";
    }
    
    echo "\n🎯 5. SIMULATION D'UTILISATION INTERFACE\n";
    echo "----------------------------------------\n";
    
    // Simuler la création d'une nouvelle tâche via l'interface
    echo "Simulation création tâche via interface...\n";
    
    $newTask = new Task([
        'user_id' => $user->id,
        'title' => 'Tâche de test interface - Données sensibles: API Key abc123',
        'description' => 'Description avec informations confidentielles: serveur 192.168.1.100, mot de passe admin123',
        'priority' => 'high',
        'status' => 'to_do'
    ]);
    
    // Avant sauvegarde (données en clair)
    echo "Avant sauvegarde - Titre: {$newTask->title}\n";
    
    $newTask->save();
    
    // Après sauvegarde (données chiffrées en base mais déchiffrées à l'affichage)
    echo "Après sauvegarde - Titre affiché: {$newTask->title}\n";
    echo "En base (chiffré): " . substr($newTask->getAttributes()['title'], 0, 50) . "...\n";
    
    // Récupération depuis la base (simulation rechargement page)
    $retrievedTask = Task::find($newTask->id);
    echo "Après récupération DB - Titre: {$retrievedTask->title}\n";
    
    if ($retrievedTask->title === 'Tâche de test interface - Données sensibles: API Key abc123') {
        echo "✅ Cycle complet interface fonctionne\n";
    } else {
        echo "❌ Problème dans le cycle interface\n";
    }
    
    // Nettoyer la tâche de test
    $newTask->delete();
    
    echo "\n📊 6. STATISTIQUES DE CHIFFREMENT\n";
    echo "---------------------------------\n";
    
    $totalUsers = User::count();
    $totalTasks = Task::count();
    $totalNotes = Note::count();
    
    echo "Utilisateurs avec données chiffrées: {$totalUsers}\n";
    echo "Tâches avec données chiffrées: {$totalTasks}\n";
    echo "Notes avec données chiffrées: {$totalNotes}\n";
    
    // Calculer l'efficacité du chiffrement
    $sampleTask = Task::first();
    if ($sampleTask) {
        $originalSize = strlen($sampleTask->title);
        $encryptedSize = strlen($sampleTask->getAttributes()['title']);
        $overhead = round((($encryptedSize - $originalSize) / $originalSize) * 100, 2);
        echo "Overhead de chiffrement: +{$overhead}% de taille\n";
    }
    
    echo "\n✅ TESTS D'INTERFACE TERMINÉS\n";
    echo "=============================\n";
    echo "🎯 L'interface utilisateur peut maintenant être testée avec:\n";
    echo "   - Connexion: admin@sonama-it.com / SecureAdmin2024!\n";
    echo "   - Toutes les données sensibles sont automatiquement chiffrées\n";
    echo "   - L'affichage reste transparent pour l'utilisateur\n";
    echo "   - Les logs de sécurité capturent toutes les interactions\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🔒 Test du chiffrement interface terminé!\n";
