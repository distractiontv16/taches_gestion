<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour ajouter les headers de sécurité
 * Implémente les meilleures pratiques de sécurité web
 */
class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add security headers
        $this->addSecurityHeaders($response);

        // Log security headers application
        Log::debug('Security headers applied', [
            'url' => $request->url(),
            'user_id' => auth()->id(),
            'ip' => $request->ip()
        ]);

        return $response;
    }

    /**
     * Add security headers to the response
     */
    protected function addSecurityHeaders(Response $response): void
    {
        $headers = Config::get('security.headers', []);

        foreach ($headers as $name => $value) {
            if (!empty($value)) {
                $response->headers->set($name, $value);
            }
        }

        // Add additional security headers based on environment
        if (app()->environment('production')) {
            $this->addProductionSecurityHeaders($response);
        }

        // Add cache control headers for sensitive pages
        $this->addCacheControlHeaders($response);
    }

    /**
     * Add production-specific security headers
     */
    protected function addProductionSecurityHeaders(Response $response): void
    {
        // Ensure HTTPS in production
        if (!$response->headers->has('Strict-Transport-Security')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Enhanced CSP for production
        if (!$response->headers->has('Content-Security-Policy')) {
            $csp = "default-src 'self'; " .
                   "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
                   "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; " .
                   "font-src 'self' https://fonts.gstatic.com; " .
                   "img-src 'self' data: https:; " .
                   "connect-src 'self'; " .
                   "frame-ancestors 'none'; " .
                   "base-uri 'self'; " .
                   "form-action 'self'";
            
            $response->headers->set('Content-Security-Policy', $csp);
        }
    }

    /**
     * Add cache control headers for sensitive content
     */
    protected function addCacheControlHeaders(Response $response): void
    {
        // Prevent caching of sensitive pages
        $sensitiveRoutes = [
            'dashboard',
            'tasks',
            'notes',
            'routines',
            'settings',
            'profile'
        ];

        $currentRoute = request()->route()?->getName();
        
        if ($currentRoute && $this->isSensitiveRoute($currentRoute, $sensitiveRoutes)) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            
            Log::debug('Cache control headers added for sensitive route', [
                'route' => $currentRoute
            ]);
        }
    }

    /**
     * Check if the current route is sensitive
     */
    protected function isSensitiveRoute(string $currentRoute, array $sensitiveRoutes): bool
    {
        foreach ($sensitiveRoutes as $sensitiveRoute) {
            if (str_contains($currentRoute, $sensitiveRoute)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get Content Security Policy for the current request
     */
    protected function getContentSecurityPolicy(Request $request): string
    {
        $basePolicy = Config::get('security.headers.Content-Security-Policy');
        
        // Customize CSP based on request context
        if ($request->is('dashboard*')) {
            // Allow additional sources for dashboard
            $basePolicy .= "; connect-src 'self' wss: ws:"; // For WebSocket connections
        }

        if ($request->is('api/*')) {
            // More restrictive CSP for API endpoints
            $basePolicy = "default-src 'none'; frame-ancestors 'none'";
        }

        return $basePolicy;
    }

    /**
     * Add feature policy headers
     */
    protected function addFeaturePolicyHeaders(Response $response): void
    {
        $featurePolicy = Config::get('security.headers.Permissions-Policy', '');
        
        if (!empty($featurePolicy)) {
            $response->headers->set('Permissions-Policy', $featurePolicy);
        }
    }

    /**
     * Add referrer policy based on context
     */
    protected function addReferrerPolicy(Response $response, Request $request): void
    {
        $defaultPolicy = Config::get('security.headers.Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Use stricter policy for sensitive pages
        if ($this->isSensitiveRoute($request->route()?->getName() ?? '', ['settings', 'profile'])) {
            $response->headers->set('Referrer-Policy', 'no-referrer');
        } else {
            $response->headers->set('Referrer-Policy', $defaultPolicy);
        }
    }

    /**
     * Add X-Frame-Options with context awareness
     */
    protected function addFrameOptions(Response $response, Request $request): void
    {
        // Always deny framing for security
        $response->headers->set('X-Frame-Options', 'DENY');
        
        // Log if someone tries to frame the application
        if ($request->headers->has('X-Requested-With') && 
            $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            Log::info('AJAX request detected', [
                'url' => $request->url(),
                'referer' => $request->headers->get('referer')
            ]);
        }
    }

    /**
     * Validate and sanitize header values
     */
    protected function sanitizeHeaderValue(string $value): string
    {
        // Remove potentially dangerous characters
        $value = preg_replace('/[\r\n\t]/', '', $value);
        
        // Limit header length
        if (strlen($value) > 8192) {
            $value = substr($value, 0, 8192);
            Log::warning('Header value truncated due to length', [
                'original_length' => strlen($value),
                'truncated_length' => 8192
            ]);
        }

        return $value;
    }
}
