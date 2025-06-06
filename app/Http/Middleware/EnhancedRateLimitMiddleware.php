<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de limitation de taux amélioré
 * Protège contre les attaques par déni de service et force brute
 */
class EnhancedRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'general'): Response
    {
        $key = $this->resolveRequestSignature($request, $type);
        $maxAttempts = $this->getMaxAttempts($type);
        $decayMinutes = $this->getDecayMinutes($type);

        if ($this->tooManyAttempts($key, $maxAttempts)) {
            $this->logRateLimitExceeded($request, $type, $key);
            return $this->buildResponse($key, $maxAttempts, $decayMinutes);
        }

        $this->incrementAttempts($key, $decayMinutes);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts),
            $this->getTimeUntilNextRetry($key)
        );
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request, string $type): string
    {
        $ip = $request->ip();
        $route = $request->route()?->getName() ?? $request->path();
        
        // Different strategies based on type
        switch ($type) {
            case 'login':
                // Combine IP and email for login attempts
                $email = $request->input('email', '');
                return "rate_limit:login:{$ip}:{$email}";
                
            case 'api':
                // API rate limiting by user or IP
                $userId = auth()->id() ?? 'guest';
                return "rate_limit:api:{$userId}:{$ip}";
                
            case 'form':
                // Form submissions by IP and route
                return "rate_limit:form:{$ip}:{$route}";
                
            default:
                // General rate limiting by IP
                return "rate_limit:general:{$ip}";
        }
    }

    /**
     * Get maximum attempts for the given type
     */
    protected function getMaxAttempts(string $type): int
    {
        $limits = Config::get('security.rate_limiting', []);
        
        switch ($type) {
            case 'login':
                return $limits['login_attempts']['max_attempts'] ?? 5;
            case 'api':
                return $limits['api_requests']['max_attempts'] ?? 60;
            case 'form':
                return $limits['form_submissions']['max_attempts'] ?? 10;
            default:
                return 30; // Default general limit
        }
    }

    /**
     * Get decay minutes for the given type
     */
    protected function getDecayMinutes(string $type): int
    {
        $limits = Config::get('security.rate_limiting', []);
        
        switch ($type) {
            case 'login':
                return $limits['login_attempts']['decay_minutes'] ?? 15;
            case 'api':
                return $limits['api_requests']['decay_minutes'] ?? 1;
            case 'form':
                return $limits['form_submissions']['decay_minutes'] ?? 1;
            default:
                return 5; // Default general decay
        }
    }

    /**
     * Determine if the given key has been "accessed" too many times
     */
    protected function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return Cache::get($key, 0) >= $maxAttempts;
    }

    /**
     * Increment the counter for a given key
     */
    protected function incrementAttempts(string $key, int $decayMinutes): int
    {
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinutes($decayMinutes));
        
        return $attempts;
    }

    /**
     * Calculate remaining attempts
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        $attempts = Cache::get($key, 0);
        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get time until next retry
     */
    protected function getTimeUntilNextRetry(string $key): int
    {
        $ttl = Cache::getStore()->getPrefix() . $key;
        
        // Try to get TTL from cache
        if (method_exists(Cache::getStore(), 'ttl')) {
            return Cache::getStore()->ttl($ttl);
        }
        
        return 60; // Default 1 minute
    }

    /**
     * Create a 'too many attempts' response
     */
    protected function buildResponse(string $key, int $maxAttempts, int $decayMinutes): Response
    {
        $retryAfter = $this->getTimeUntilNextRetry($key);
        
        $response = response()->json([
            'message' => 'Too many attempts. Please try again later.',
            'retry_after' => $retryAfter,
            'max_attempts' => $maxAttempts,
        ], 429);

        return $this->addHeaders($response, $maxAttempts, 0, $retryAfter);
    }

    /**
     * Add rate limit headers to response
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts, int $retryAfter): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
            'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
        ]);

        if ($remainingAttempts === 0) {
            $response->headers->set('Retry-After', $retryAfter);
        }

        return $response;
    }

    /**
     * Log rate limit exceeded event
     */
    protected function logRateLimitExceeded(Request $request, string $type, string $key): void
    {
        Log::warning('Rate limit exceeded', [
            'type' => $type,
            'key' => $key,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->url(),
            'method' => $request->method(),
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Clear rate limit for a key (useful for successful actions)
     */
    public static function clearRateLimit(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Get current attempt count for a key
     */
    public static function getAttempts(string $key): int
    {
        return Cache::get($key, 0);
    }

    /**
     * Check if IP is in whitelist
     */
    protected function isWhitelisted(Request $request): bool
    {
        $whitelist = Config::get('security.rate_limiting.whitelist', []);
        $ip = $request->ip();
        
        return in_array($ip, $whitelist);
    }

    /**
     * Apply progressive delays for repeated violations
     */
    protected function applyProgressiveDelay(string $key): void
    {
        $violationKey = $key . ':violations';
        $violations = Cache::get($violationKey, 0) + 1;
        
        // Exponential backoff: 2^violations seconds (max 1 hour)
        $delay = min(pow(2, $violations), 3600);
        
        Cache::put($violationKey, $violations, now()->addHours(24));
        
        if ($delay > 1) {
            sleep($delay);
        }
    }
}
