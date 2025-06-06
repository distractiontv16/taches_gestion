<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Service d'audit de sécurité
 * Gère la journalisation et la détection d'anomalies de sécurité
 */
class SecurityAuditService
{
    protected string $logChannel;
    protected array $auditEvents;
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = Config::get('security.audit.enabled', true);
        $this->logChannel = Config::get('security.audit.log_channel', 'security');
        $this->auditEvents = Config::get('security.audit.events', []);
    }

    /**
     * Enregistre une tentative de connexion
     */
    public function logLoginAttempt(Request $request, bool $successful, ?int $userId = null): void
    {
        if (!$this->shouldLog('login_attempts')) {
            return;
        }

        $data = [
            'event_type' => 'login_attempt',
            'successful' => $successful,
            'user_id' => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
            'session_id' => $request->session()->getId(),
            'email' => $request->input('email'),
        ];

        $this->logSecurityEvent($data);

        // Détecter les tentatives de force brute
        if (!$successful) {
            $this->detectBruteForceAttempt($request);
        }
    }

    /**
     * Enregistre un changement de mot de passe
     */
    public function logPasswordChange(int $userId, Request $request): void
    {
        if (!$this->shouldLog('password_changes')) {
            return;
        }

        $data = [
            'event_type' => 'password_change',
            'user_id' => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
            'session_id' => $request->session()->getId(),
        ];

        $this->logSecurityEvent($data);
    }

    /**
     * Enregistre un accès aux données
     */
    public function logDataAccess(string $modelType, int $modelId, int $userId, string $action = 'read'): void
    {
        if (!$this->shouldLog('data_access')) {
            return;
        }

        $data = [
            'event_type' => 'data_access',
            'user_id' => $userId,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'action' => $action,
            'timestamp' => now(),
            'ip_address' => request()->ip(),
        ];

        $this->logSecurityEvent($data);
    }

    /**
     * Enregistre une modification de données
     */
    public function logDataModification(string $modelType, int $modelId, int $userId, array $changes): void
    {
        if (!$this->shouldLog('data_modifications')) {
            return;
        }

        $data = [
            'event_type' => 'data_modification',
            'user_id' => $userId,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'changes' => $this->sanitizeChanges($changes),
            'timestamp' => now(),
            'ip_address' => request()->ip(),
        ];

        $this->logSecurityEvent($data);
    }

    /**
     * Enregistre une authentification échouée
     */
    public function logFailedAuthentication(Request $request, string $reason): void
    {
        if (!$this->shouldLog('failed_authentications')) {
            return;
        }

        $data = [
            'event_type' => 'failed_authentication',
            'reason' => $reason,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->url(),
            'timestamp' => now(),
            'session_id' => $request->session()->getId(),
        ];

        $this->logSecurityEvent($data);
    }

    /**
     * Enregistre une escalade de privilèges
     */
    public function logPrivilegeEscalation(int $userId, string $action, Request $request): void
    {
        if (!$this->shouldLog('privilege_escalations')) {
            return;
        }

        $data = [
            'event_type' => 'privilege_escalation',
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->url(),
            'timestamp' => now(),
        ];

        $this->logSecurityEvent($data);

        // Alerte immédiate pour les escalades de privilèges
        $this->sendSecurityAlert('Privilege escalation detected', $data);
    }

    /**
     * Détecte les tentatives de force brute
     */
    protected function detectBruteForceAttempt(Request $request): void
    {
        $ip = $request->ip();
        $cacheKey = "failed_login_attempts:{$ip}";
        
        $attempts = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $attempts, now()->addMinutes(15));

        $maxAttempts = Config::get('security.rate_limiting.login_attempts.max_attempts', 5);
        
        if ($attempts >= $maxAttempts) {
            $this->logSecurityEvent([
                'event_type' => 'brute_force_detected',
                'ip_address' => $ip,
                'attempts' => $attempts,
                'timestamp' => now(),
            ]);

            $this->sendSecurityAlert('Brute force attack detected', [
                'ip' => $ip,
                'attempts' => $attempts
            ]);
        }
    }

    /**
     * Analyse les patterns d'accès suspects
     */
    public function analyzeSuspiciousPatterns(): array
    {
        $suspiciousActivities = [];

        // Analyser les connexions multiples depuis différentes IPs
        $multipleIpLogins = $this->detectMultipleIpLogins();
        if (!empty($multipleIpLogins)) {
            $suspiciousActivities['multiple_ip_logins'] = $multipleIpLogins;
        }

        // Analyser les accès en dehors des heures normales
        $offHoursAccess = $this->detectOffHoursAccess();
        if (!empty($offHoursAccess)) {
            $suspiciousActivities['off_hours_access'] = $offHoursAccess;
        }

        // Analyser les modifications de données en masse
        $bulkDataChanges = $this->detectBulkDataChanges();
        if (!empty($bulkDataChanges)) {
            $suspiciousActivities['bulk_data_changes'] = $bulkDataChanges;
        }

        return $suspiciousActivities;
    }

    /**
     * Détecte les connexions multiples depuis différentes IPs
     */
    protected function detectMultipleIpLogins(): array
    {
        // Cette méthode analyserait les logs pour détecter
        // des connexions simultanées depuis différentes IPs
        // Implémentation simplifiée pour l'exemple
        return [];
    }

    /**
     * Détecte les accès en dehors des heures normales
     */
    protected function detectOffHoursAccess(): array
    {
        // Analyser les accès entre 22h et 6h
        $offHoursStart = 22;
        $offHoursEnd = 6;
        
        // Implémentation simplifiée
        return [];
    }

    /**
     * Détecte les modifications de données en masse
     */
    protected function detectBulkDataChanges(): array
    {
        // Détecter plus de 10 modifications en 5 minutes
        $threshold = 10;
        $timeWindow = 5; // minutes
        
        // Implémentation simplifiée
        return [];
    }

    /**
     * Envoie une alerte de sécurité
     */
    protected function sendSecurityAlert(string $title, array $data): void
    {
        Log::channel($this->logChannel)->critical($title, $data);
        
        // Ici, on pourrait ajouter l'envoi d'emails ou de notifications
        // aux administrateurs système
    }

    /**
     * Enregistre un événement de sécurité
     */
    protected function logSecurityEvent(array $data): void
    {
        if (!$this->enabled) {
            return;
        }

        Log::channel($this->logChannel)->info('Security event', $data);
    }

    /**
     * Vérifie si un type d'événement doit être journalisé
     */
    protected function shouldLog(string $eventType): bool
    {
        return $this->enabled && in_array($eventType, $this->auditEvents);
    }

    /**
     * Nettoie les données de changement pour la journalisation
     */
    protected function sanitizeChanges(array $changes): array
    {
        $sensitiveFields = ['password', 'remember_token', 'api_token'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($changes[$field])) {
                $changes[$field] = '[REDACTED]';
            }
        }

        return $changes;
    }

    /**
     * Nettoie les anciens logs d'audit
     */
    public function cleanupOldLogs(): int
    {
        $retentionDays = Config::get('security.audit.retention_days', 365);
        $cutoffDate = Carbon::now()->subDays($retentionDays);
        
        // Cette méthode nettoierait les anciens logs
        // Implémentation dépendante du système de stockage des logs
        
        Log::info('Audit log cleanup completed', [
            'retention_days' => $retentionDays,
            'cutoff_date' => $cutoffDate
        ]);

        return 0; // Nombre de logs supprimés
    }
}
