<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Closure;

/**
 * Middleware CSRF renforcé avec double-submit cookie pattern
 * et protection SameSite
 */
class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Add any routes that should be excluded from CSRF protection
    ];

    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next): Response
    {
        // Log CSRF verification attempt
        Log::info('CSRF verification initiated', [
            'url' => $request->url(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id()
        ]);

        // Perform standard CSRF verification
        $response = parent::handle($request, $next);

        // Add double-submit CSRF cookie if enabled
        if (Config::get('security.csrf.double_submit_enabled', true)) {
            $this->addDoubleSubmitCookie($request, $response);
        }

        return $response;
    }

    /**
     * Determine if the HTTP request uses a 'read' verb.
     */
    protected function isReading($request): bool
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     */
    protected function inExceptArray($request): bool
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                Log::info('CSRF verification bypassed for excluded route', [
                    'url' => $request->url(),
                    'excluded_pattern' => $except
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Verify the CSRF token in the request.
     */
    protected function tokensMatch($request): bool
    {
        $token = $this->getTokenFromRequest($request);

        // Standard Laravel CSRF token verification
        $standardMatch = is_string($request->session()->token()) &&
                        is_string($token) &&
                        hash_equals($request->session()->token(), $token);

        // Double-submit cookie verification if enabled
        $doubleSubmitMatch = true;
        if (Config::get('security.csrf.double_submit_enabled', true)) {
            $doubleSubmitMatch = $this->verifyDoubleSubmitToken($request);
        }

        $tokensMatch = $standardMatch && $doubleSubmitMatch;

        Log::info('CSRF token verification result', [
            'standard_match' => $standardMatch,
            'double_submit_match' => $doubleSubmitMatch,
            'final_result' => $tokensMatch,
            'user_id' => auth()->id(),
            'ip' => $request->ip()
        ]);

        if (!$tokensMatch) {
            Log::warning('CSRF token verification failed', [
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
                'has_session_token' => !empty($request->session()->token()),
                'has_request_token' => !empty($token)
            ]);
        }

        return $tokensMatch;
    }

    /**
     * Get the CSRF token from the request.
     */
    protected function getTokenFromRequest($request): ?string
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        if (!$token && $header = $request->header('X-XSRF-TOKEN')) {
            $token = $this->encrypter->decrypt($header, static::serialized());
        }

        return $token;
    }

    /**
     * Add double-submit CSRF cookie to response
     */
    protected function addDoubleSubmitCookie(Request $request, Response $response): void
    {
        if ($this->isReading($request)) {
            $cookieName = Config::get('security.csrf.cookie_name', 'XSRF-TOKEN');
            $token = $request->session()->token();
            
            if ($token) {
                $encryptedToken = $this->encrypter->encrypt($token, static::serialized());
                
                $cookie = Cookie::make(
                    $cookieName,
                    $encryptedToken,
                    Config::get('security.csrf.lifetime', 120),
                    '/',
                    null,
                    Config::get('security.csrf.secure', false),
                    Config::get('security.csrf.http_only', false),
                    false,
                    Config::get('security.csrf.same_site', 'strict')
                );

                $response->headers->setCookie($cookie);

                Log::debug('Double-submit CSRF cookie added', [
                    'cookie_name' => $cookieName,
                    'user_id' => auth()->id()
                ]);
            }
        }
    }

    /**
     * Verify double-submit CSRF token
     */
    protected function verifyDoubleSubmitToken(Request $request): bool
    {
        if ($this->isReading($request)) {
            return true;
        }

        $cookieName = Config::get('security.csrf.cookie_name', 'XSRF-TOKEN');
        $headerName = Config::get('security.csrf.header_name', 'X-XSRF-TOKEN');
        
        $cookieToken = $request->cookie($cookieName);
        $headerToken = $request->header($headerName);

        if (!$cookieToken || !$headerToken) {
            Log::warning('Double-submit CSRF tokens missing', [
                'has_cookie' => !empty($cookieToken),
                'has_header' => !empty($headerToken),
                'url' => $request->url()
            ]);
            return false;
        }

        try {
            $decryptedCookieToken = $this->encrypter->decrypt($cookieToken, static::serialized());
            $decryptedHeaderToken = $this->encrypter->decrypt($headerToken, static::serialized());

            $match = hash_equals($decryptedCookieToken, $decryptedHeaderToken);

            if (!$match) {
                Log::warning('Double-submit CSRF tokens do not match', [
                    'url' => $request->url(),
                    'user_id' => auth()->id()
                ]);
            }

            return $match;
        } catch (\Exception $e) {
            Log::error('Double-submit CSRF token decryption failed', [
                'error' => $e->getMessage(),
                'url' => $request->url()
            ]);
            return false;
        }
    }

    /**
     * Determine if the session and input CSRF tokens match.
     */
    protected static function serialized(): bool
    {
        return false;
    }
}
