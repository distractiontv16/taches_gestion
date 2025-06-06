<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_all_responses()
    {
        $response = $this->get('/');

        // Test essential security headers
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy');
    }

    public function test_content_security_policy_header_is_present()
    {
        $response = $this->get('/');

        $response->assertHeader('Content-Security-Policy');
        
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContains("default-src 'self'", $csp);
    }

    public function test_strict_transport_security_in_production()
    {
        // Simulate production environment
        config(['app.env' => 'production']);

        $response = $this->get('/');

        $response->assertHeader('Strict-Transport-Security');
        
        $hsts = $response->headers->get('Strict-Transport-Security');
        $this->assertStringContains('max-age=', $hsts);
        $this->assertStringContains('includeSubDomains', $hsts);
    }

    public function test_permissions_policy_header_is_present()
    {
        $response = $this->get('/');

        $response->assertHeader('Permissions-Policy');
        
        $permissionsPolicy = $response->headers->get('Permissions-Policy');
        $this->assertStringContains('geolocation=()', $permissionsPolicy);
        $this->assertStringContains('microphone=()', $permissionsPolicy);
        $this->assertStringContains('camera=()', $permissionsPolicy);
    }

    public function test_cache_control_headers_for_sensitive_pages()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertHeader('Cache-Control');
        
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContains('no-cache', $cacheControl);
        $this->assertStringContains('no-store', $cacheControl);
        $this->assertStringContains('must-revalidate', $cacheControl);
        $this->assertStringContains('private', $cacheControl);
    }

    public function test_referrer_policy_is_stricter_for_sensitive_pages()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        // Test settings page (should have stricter referrer policy)
        $response = $this->get('/settings');

        $referrerPolicy = $response->headers->get('Referrer-Policy');
        $this->assertEquals('no-referrer', $referrerPolicy);
    }

    public function test_x_frame_options_prevents_clickjacking()
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_content_type_options_prevents_mime_sniffing()
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_xss_protection_header_is_enabled()
    {
        $response = $this->get('/');

        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    public function test_security_headers_on_api_endpoints()
    {
        $response = $this->getJson('/api/test');

        // API endpoints should have more restrictive CSP
        $csp = $response->headers->get('Content-Security-Policy');
        if ($csp) {
            $this->assertStringContains("default-src 'none'", $csp);
            $this->assertStringContains("frame-ancestors 'none'", $csp);
        }
    }

    public function test_security_headers_are_properly_formatted()
    {
        $response = $this->get('/');

        // Check that headers don't contain dangerous characters
        $headers = $response->headers->all();
        
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $this->assertStringNotContainsString("\r", $value, "Header {$name} contains carriage return");
                $this->assertStringNotContainsString("\n", $value, "Header {$name} contains newline");
                $this->assertStringNotContainsString("\t", $value, "Header {$name} contains tab");
            }
        }
    }

    public function test_security_headers_length_limits()
    {
        $response = $this->get('/');

        $headers = $response->headers->all();
        
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $this->assertLessThanOrEqual(8192, strlen($value), 
                    "Header {$name} exceeds maximum length");
            }
        }
    }

    public function test_csp_allows_necessary_resources()
    {
        $response = $this->get('/dashboard');

        $csp = $response->headers->get('Content-Security-Policy');

        // Should allow Bootstrap CDN
        $this->assertStringContains('https://cdn.jsdelivr.net', $csp);

        // Should allow Google Fonts
        $this->assertStringContains('https://fonts.googleapis.com', $csp);
        $this->assertStringContains('https://fonts.gstatic.com', $csp);
    }
}
