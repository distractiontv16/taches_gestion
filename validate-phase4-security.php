<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\DataEncryptionService;
use App\Services\SecurityAuditService;
use App\Models\User;
use App\Models\Task;
use App\Models\Note;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

echo "🔒 VALIDATION COMPLÈTE DE LA PHASE 4 : SÉCURITÉ AVANCÉE\n";
echo "=====================================================\n\n";

$errors = [];
$warnings = [];
$successes = [];

// 1. Test du service de chiffrement
echo "📋 1. VALIDATION DU SERVICE DE CHIFFREMENT\n";
echo "-------------------------------------------\n";

try {
    $encryptionService = new DataEncryptionService();
    
    // Test de chiffrement/déchiffrement basique
    $testData = "Données sensibles de test avec caractères spéciaux: àéèùç @#$%";
    $encrypted = $encryptionService->encrypt($testData);
    $decrypted = $encryptionService->decrypt($encrypted);
    
    if ($testData === $decrypted) {
        $successes[] = "✅ Chiffrement/déchiffrement basique fonctionne";
    } else {
        $errors[] = "❌ Échec du chiffrement/déchiffrement basique";
    }
    
    // Test de validation des données chiffrées
    if ($encryptionService->validateEncryptedData($encrypted)) {
        $successes[] = "✅ Validation des données chiffrées fonctionne";
    } else {
        $errors[] = "❌ Échec de la validation des données chiffrées";
    }
    
    // Test des champs de modèle
    $taskData = [
        'title' => 'Tâche de test',
        'description' => 'Description sensible',
        'status' => 'pending'
    ];
    
    $encryptedData = $encryptionService->encryptModelFields('Task', $taskData);
    $decryptedData = $encryptionService->decryptModelFields('Task', $encryptedData);
    
    if ($taskData['title'] === $decryptedData['title'] && 
        $taskData['description'] === $decryptedData['description']) {
        $successes[] = "✅ Chiffrement des champs de modèle fonctionne";
    } else {
        $errors[] = "❌ Échec du chiffrement des champs de modèle";
    }
    
} catch (Exception $e) {
    $errors[] = "❌ Erreur dans le service de chiffrement: " . $e->getMessage();
}

// 2. Test du service d'audit de sécurité
echo "\n📋 2. VALIDATION DU SERVICE D'AUDIT DE SÉCURITÉ\n";
echo "-----------------------------------------------\n";

try {
    $auditService = new SecurityAuditService();
    
    // Test de création d'un log d'audit
    $request = Illuminate\Http\Request::create('/test', 'POST');
    $auditService->logLoginAttempt($request, true, 1);
    
    $successes[] = "✅ Service d'audit de sécurité initialisé";
    
    // Test d'analyse des patterns suspects
    $patterns = $auditService->analyzeSuspiciousPatterns();
    if (is_array($patterns)) {
        $successes[] = "✅ Analyse des patterns suspects fonctionne";
    } else {
        $warnings[] = "⚠️  Analyse des patterns suspects retourne un format inattendu";
    }
    
} catch (Exception $e) {
    $errors[] = "❌ Erreur dans le service d'audit: " . $e->getMessage();
}

// 3. Validation de la configuration de sécurité
echo "\n📋 3. VALIDATION DE LA CONFIGURATION DE SÉCURITÉ\n";
echo "------------------------------------------------\n";

// Vérifier la configuration de chiffrement
$encryptionConfig = Config::get('security.encryption');
if ($encryptionConfig && isset($encryptionConfig['algorithm'])) {
    $successes[] = "✅ Configuration de chiffrement présente";
    
    if ($encryptionConfig['algorithm'] === 'AES-256-GCM') {
        $successes[] = "✅ Algorithme de chiffrement AES-256-GCM configuré";
    } else {
        $warnings[] = "⚠️  Algorithme de chiffrement non optimal: " . $encryptionConfig['algorithm'];
    }
} else {
    $errors[] = "❌ Configuration de chiffrement manquante";
}

// Vérifier la configuration CSRF
$csrfConfig = Config::get('security.csrf');
if ($csrfConfig && $csrfConfig['double_submit_enabled']) {
    $successes[] = "✅ Protection CSRF double-submit activée";
} else {
    $warnings[] = "⚠️  Protection CSRF double-submit désactivée";
}

// Vérifier les headers de sécurité
$headersConfig = Config::get('security.headers');
if ($headersConfig && isset($headersConfig['X-Frame-Options'])) {
    $successes[] = "✅ Headers de sécurité configurés";
    
    if ($headersConfig['X-Frame-Options'] === 'DENY') {
        $successes[] = "✅ Protection contre le clickjacking activée";
    }
} else {
    $errors[] = "❌ Headers de sécurité non configurés";
}

// 4. Test des modèles avec chiffrement
echo "\n📋 4. VALIDATION DES MODÈLES AVEC CHIFFREMENT\n";
echo "---------------------------------------------\n";

try {
    // Vérifier que les modèles utilisent le trait EncryptableFields
    $modelsToCheck = [
        'App\Models\User',
        'App\Models\Task', 
        'App\Models\Note',
        'App\Models\Routine',
        'App\Models\Reminder'
    ];
    
    foreach ($modelsToCheck as $modelClass) {
        $reflection = new ReflectionClass($modelClass);
        $traits = $reflection->getTraitNames();
        
        if (in_array('App\Traits\EncryptableFields', $traits)) {
            $successes[] = "✅ " . class_basename($modelClass) . " utilise le trait EncryptableFields";
        } else {
            $errors[] = "❌ " . class_basename($modelClass) . " n'utilise pas le trait EncryptableFields";
        }
    }
    
} catch (Exception $e) {
    $errors[] = "❌ Erreur lors de la vérification des modèles: " . $e->getMessage();
}

// 5. Test des middleware de sécurité
echo "\n📋 5. VALIDATION DES MIDDLEWARE DE SÉCURITÉ\n";
echo "-------------------------------------------\n";

// Vérifier l'existence des middleware
$middlewareFiles = [
    'app/Http/Middleware/VerifyCsrfToken.php',
    'app/Http/Middleware/SecurityHeadersMiddleware.php',
    'app/Http/Middleware/SecurityAuditMiddleware.php',
    'app/Http/Middleware/EnhancedRateLimitMiddleware.php'
];

foreach ($middlewareFiles as $file) {
    if (file_exists($file)) {
        $successes[] = "✅ Middleware " . basename($file, '.php') . " présent";
    } else {
        $errors[] = "❌ Middleware " . basename($file, '.php') . " manquant";
    }
}

// 6. Validation de la configuration de session
echo "\n📋 6. VALIDATION DE LA CONFIGURATION DE SESSION\n";
echo "-----------------------------------------------\n";

$sessionEncrypt = Config::get('session.encrypt');
if ($sessionEncrypt) {
    $successes[] = "✅ Chiffrement des sessions activé";
} else {
    $warnings[] = "⚠️  Chiffrement des sessions désactivé";
}

$sessionSameSite = Config::get('session.same_site');
if ($sessionSameSite === 'strict') {
    $successes[] = "✅ Attribut SameSite strict configuré";
} else {
    $warnings[] = "⚠️  Attribut SameSite non optimal: " . $sessionSameSite;
}

// 7. Test des commandes de sécurité
echo "\n📋 7. VALIDATION DES COMMANDES DE SÉCURITÉ\n";
echo "------------------------------------------\n";

if (file_exists('app/Console/Commands/SecurityAuditCommand.php')) {
    $successes[] = "✅ Commande d'audit de sécurité présente";
} else {
    $errors[] = "❌ Commande d'audit de sécurité manquante";
}

// 8. Résumé final
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RÉSUMÉ DE LA VALIDATION\n";
echo str_repeat("=", 60) . "\n";

echo "\n✅ SUCCÈS (" . count($successes) . "):\n";
foreach ($successes as $success) {
    echo "  " . $success . "\n";
}

if (!empty($warnings)) {
    echo "\n⚠️  AVERTISSEMENTS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "  " . $warning . "\n";
    }
}

if (!empty($errors)) {
    echo "\n❌ ERREURS (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "  " . $error . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";

if (empty($errors)) {
    echo "🎉 PHASE 4 : SÉCURITÉ AVANCÉE VALIDÉE AVEC SUCCÈS!\n";
    echo "Toutes les fonctionnalités de sécurité sont opérationnelles.\n";
} else {
    echo "⚠️  PHASE 4 : VALIDATION INCOMPLÈTE\n";
    echo "Veuillez corriger les erreurs avant de continuer.\n";
}

echo "\n📝 PROCHAINES ÉTAPES RECOMMANDÉES:\n";
echo "- Exécuter les tests unitaires: php artisan test\n";
echo "- Lancer l'audit de sécurité: php artisan security:audit --full\n";
echo "- Vérifier les logs de sécurité: tail -f storage/logs/security.log\n";
echo "- Configurer la rotation des clés de chiffrement\n";
echo "- Mettre en place la surveillance des alertes de sécurité\n";

echo "\n🔒 Phase 4 : Sécurité Avancée - Validation terminée!\n";
