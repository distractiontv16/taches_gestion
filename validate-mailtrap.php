<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

echo "📬 VALIDATION DE LA CONFIGURATION MAILTRAP\n";
echo "=========================================\n\n";

// 1. Vérification des variables d'environnement
echo "🔧 1. VARIABLES D'ENVIRONNEMENT\n";
echo "------------------------------\n";

$envVars = [
    'MAIL_MAILER' => env('MAIL_MAILER'),
    'MAIL_HOST' => env('MAIL_HOST'),
    'MAIL_PORT' => env('MAIL_PORT'),
    'MAIL_USERNAME' => env('MAIL_USERNAME'),
    'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? str_repeat('*', strlen(env('MAIL_PASSWORD'))) : null,
    'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
    'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
    'MAIL_FROM_NAME' => env('MAIL_FROM_NAME')
];

foreach ($envVars as $key => $value) {
    $status = !empty($value) ? "✅" : "❌";
    echo "  {$status} {$key}: {$value}\n";
}

// 2. Vérification de la configuration Laravel
echo "\n⚙️ 2. CONFIGURATION LARAVEL\n";
echo "--------------------------\n";

$laravelConfig = [
    'default_mailer' => config('mail.default'),
    'smtp_host' => config('mail.mailers.smtp.host'),
    'smtp_port' => config('mail.mailers.smtp.port'),
    'smtp_username' => config('mail.mailers.smtp.username'),
    'smtp_password' => config('mail.mailers.smtp.password') ? str_repeat('*', strlen(config('mail.mailers.smtp.password'))) : null,
    'smtp_encryption' => config('mail.mailers.smtp.encryption'),
    'from_address' => config('mail.from.address'),
    'from_name' => config('mail.from.name')
];

foreach ($laravelConfig as $key => $value) {
    $status = !empty($value) ? "✅" : "❌";
    echo "  {$status} {$key}: {$value}\n";
}

// 3. Test de connexion SMTP basique
echo "\n🌐 3. TEST DE CONNEXION SMTP\n";
echo "---------------------------\n";

try {
    $host = config('mail.mailers.smtp.host');
    $port = config('mail.mailers.smtp.port');
    
    echo "Tentative de connexion à {$host}:{$port}...\n";
    
    $socket = @fsockopen($host, $port, $errno, $errstr, 10);
    
    if ($socket) {
        echo "✅ Connexion TCP réussie\n";
        fclose($socket);
    } else {
        echo "❌ Impossible de se connecter: {$errstr} ({$errno})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
}

// 4. Test d'envoi d'email simple
echo "\n📧 4. TEST D'ENVOI D'EMAIL SIMPLE\n";
echo "--------------------------------\n";

try {
    $testEmail = 'admin@test.com';
    
    echo "🚀 Envoi d'un email de test vers {$testEmail}...\n";
    
    Mail::raw('Ceci est un test de configuration Mailtrap depuis Laravel.', function ($message) use ($testEmail) {
        $message->to($testEmail)
                ->subject('Test Mailtrap - Configuration Laravel');
    });
    
    echo "✅ Email envoyé avec succès!\n";
    echo "📬 Vérifiez votre boîte Mailtrap: https://mailtrap.io/inboxes\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de l'envoi: " . $e->getMessage() . "\n";
    echo "📁 Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    // Diagnostic détaillé de l'erreur
    echo "\n🔍 DIAGNOSTIC DE L'ERREUR:\n";
    
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "  • Problème de connexion réseau\n";
        echo "  • Vérifiez votre connexion internet\n";
        echo "  • Vérifiez les paramètres de firewall\n";
    }
    
    if (strpos($e->getMessage(), 'Authentication failed') !== false) {
        echo "  • Identifiants Mailtrap incorrects\n";
        echo "  • Vérifiez MAIL_USERNAME et MAIL_PASSWORD dans .env\n";
        echo "  • Vérifiez que votre compte Mailtrap est actif\n";
    }
    
    if (strpos($e->getMessage(), 'stream_socket_enable_crypto') !== false) {
        echo "  • Problème de chiffrement TLS/SSL\n";
        echo "  • Vérifiez MAIL_ENCRYPTION=tls dans .env\n";
        echo "  • Vérifiez que OpenSSL est installé\n";
    }
}

// 5. Vérification des dépendances
echo "\n📦 5. VÉRIFICATION DES DÉPENDANCES\n";
echo "---------------------------------\n";

$dependencies = [
    'OpenSSL' => extension_loaded('openssl'),
    'cURL' => extension_loaded('curl'),
    'Sockets' => extension_loaded('sockets'),
    'Symfony Mailer' => class_exists('Symfony\Component\Mailer\Mailer')
];

foreach ($dependencies as $dep => $loaded) {
    $status = $loaded ? "✅" : "❌";
    echo "  {$status} {$dep}\n";
}

// 6. Informations sur Mailtrap
echo "\n📋 6. INFORMATIONS MAILTRAP\n";
echo "--------------------------\n";

echo "🔗 Liens utiles:\n";
echo "  • Dashboard Mailtrap: https://mailtrap.io/inboxes\n";
echo "  • Documentation SMTP: https://help.mailtrap.io/article/12-getting-started-guide\n";
echo "  • Support Mailtrap: https://help.mailtrap.io/\n\n";

echo "📝 Configuration type pour Mailtrap:\n";
echo "  MAIL_MAILER=smtp\n";
echo "  MAIL_HOST=sandbox.smtp.mailtrap.io\n";
echo "  MAIL_PORT=2525\n";
echo "  MAIL_USERNAME=votre_username\n";
echo "  MAIL_PASSWORD=votre_password\n";
echo "  MAIL_ENCRYPTION=tls\n";
echo "  MAIL_FROM_ADDRESS=test@example.com\n";
echo "  MAIL_FROM_NAME=\"Task Manager\"\n\n";

// 7. Recommandations
echo "💡 7. RECOMMANDATIONS\n";
echo "--------------------\n";

$issues = [];

if (empty(config('mail.mailers.smtp.username'))) {
    $issues[] = "Configurez MAIL_USERNAME dans .env";
}

if (empty(config('mail.mailers.smtp.password'))) {
    $issues[] = "Configurez MAIL_PASSWORD dans .env";
}

if (config('mail.mailers.smtp.host') !== 'sandbox.smtp.mailtrap.io') {
    $issues[] = "Vérifiez MAIL_HOST (doit être sandbox.smtp.mailtrap.io)";
}

if (config('mail.mailers.smtp.port') != 2525) {
    $issues[] = "Vérifiez MAIL_PORT (doit être 2525 pour Mailtrap)";
}

if (!extension_loaded('openssl')) {
    $issues[] = "Installez l'extension PHP OpenSSL";
}

if (count($issues) > 0) {
    echo "❌ Problèmes détectés:\n";
    foreach ($issues as $issue) {
        echo "  • {$issue}\n";
    }
} else {
    echo "✅ Configuration semble correcte!\n";
}

echo "\n🎯 PROCHAINES ÉTAPES\n";
echo "===================\n";

echo "1. Si l'email de test a été envoyé avec succès:\n";
echo "   • Vérifiez votre boîte Mailtrap\n";
echo "   • Testez le système complet avec: php test-email-system.php\n\n";

echo "2. Si il y a des erreurs:\n";
echo "   • Corrigez la configuration dans .env\n";
echo "   • Exécutez: php artisan config:clear\n";
echo "   • Relancez ce script\n\n";

echo "3. Pour tester le système de notifications:\n";
echo "   • php create-email-test-tasks.php\n";
echo "   • php artisan app:send-reminder-emails\n\n";

echo "🎉 Validation Mailtrap terminée!\n";
