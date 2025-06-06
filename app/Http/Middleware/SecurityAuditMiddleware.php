<?php

namespace App\Http\Middleware;

use App\Services\SecurityAuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour l'audit de sécurité
 * Enregistre les accès et actions sensibles
 */
class SecurityAuditMiddleware
{
    protected SecurityAuditService $auditService;

    public function __construct(SecurityAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enregistrer l'accès avant le traitement
        $this->logAccess($request);

        $response = $next($request);

        // Enregistrer la réponse après le traitement
        $this->logResponse($request, $response);

        return $response;
    }

    /**
     * Enregistre l'accès à la ressource
     */
    protected function logAccess(Request $request): void
    {
        // Enregistrer seulement les routes sensibles
        if ($this->isSensitiveRoute($request)) {
            Log::info('Sensitive route access', [
                'user_id' => Auth::id(),
                'route' => $request->route()?->getName(),
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);
        }

        // Détecter les tentatives d'accès non autorisé
        if (!Auth::check() && $this->requiresAuthentication($request)) {
            $this->auditService->logFailedAuthentication($request, 'Unauthenticated access attempt');
        }
    }

    /**
     * Enregistre la réponse
     */
    protected function logResponse(Request $request, Response $response): void
    {
        // Enregistrer les erreurs d'autorisation
        if ($response->getStatusCode() === 403) {
            $this->auditService->logFailedAuthentication($request, 'Authorization failed');
        }

        // Enregistrer les erreurs de validation CSRF
        if ($response->getStatusCode() === 419) {
            Log::warning('CSRF token mismatch', [
                'user_id' => Auth::id(),
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }

    /**
     * Vérifie si la route est sensible
     */
    protected function isSensitiveRoute(Request $request): bool
    {
        $sensitiveRoutes = [
            'dashboard',
            'tasks.*',
            'notes.*',
            'routines.*',
            'settings.*',
            'profile.*',
            'admin.*',
        ];

        $routeName = $request->route()?->getName();
        
        if (!$routeName) {
            return false;
        }

        foreach ($sensitiveRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si la route nécessite une authentification
     */
    protected function requiresAuthentication(Request $request): bool
    {
        $publicRoutes = [
            'login',
            'register',
            'password.*',
            'verification.*',
            'home',
            '/',
        ];

        $routeName = $request->route()?->getName();
        $path = $request->path();

        // Vérifier les routes nommées
        if ($routeName) {
            foreach ($publicRoutes as $pattern) {
                if (fnmatch($pattern, $routeName)) {
                    return false;
                }
            }
        }

        // Vérifier les chemins
        $publicPaths = [
            '/',
            'login',
            'register',
            'password/*',
            'email/*',
            'assets/*',
            'css/*',
            'js/*',
            'images/*',
        ];

        foreach ($publicPaths as $pattern) {
            if (fnmatch($pattern, $path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Détecte les tentatives d'escalade de privilèges
     */
    protected function detectPrivilegeEscalation(Request $request): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();
        $routeName = $request->route()?->getName();

        // Détecter les tentatives d'accès aux routes admin
        if (str_contains($routeName ?? '', 'admin') && !$this->isAdmin($user)) {
            $this->auditService->logPrivilegeEscalation(
                $user->id,
                "Attempted access to admin route: {$routeName}",
                $request
            );
        }

        // Détecter les tentatives d'accès aux données d'autres utilisateurs
        $this->detectUnauthorizedDataAccess($request, $user);
    }

    /**
     * Détecte les tentatives d'accès non autorisé aux données
     */
    protected function detectUnauthorizedDataAccess(Request $request, $user): void
    {
        // Vérifier les paramètres de route pour les IDs d'autres utilisateurs
        $routeParameters = $request->route()?->parameters() ?? [];
        
        foreach ($routeParameters as $key => $value) {
            if (in_array($key, ['task', 'note', 'routine', 'reminder']) && is_numeric($value)) {
                // Ici, on pourrait vérifier si l'utilisateur a accès à cette ressource
                // Pour l'instant, on log juste l'accès
                Log::debug('Resource access attempt', [
                    'user_id' => $user->id,
                    'resource_type' => $key,
                    'resource_id' => $value,
                    'url' => $request->url(),
                ]);
            }
        }
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     */
    protected function isAdmin($user): bool
    {
        // Implémentation basique - à adapter selon votre système de rôles
        return $user->email === 'admin@example.com' || 
               isset($user->role) && $user->role === 'admin';
    }

    /**
     * Enregistre les modifications de données sensibles
     */
    public function logDataModification(string $modelType, int $modelId, array $changes): void
    {
        if (Auth::check()) {
            $this->auditService->logDataModification(
                $modelType,
                $modelId,
                Auth::id(),
                $changes
            );
        }
    }
}
