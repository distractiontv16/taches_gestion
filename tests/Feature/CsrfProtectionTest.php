<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_csrf_protection_blocks_requests_without_token()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        $response = $this->post('/tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'medium'
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    public function test_csrf_protection_allows_requests_with_valid_token()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        $response = $this->post('/tasks', [
            '_token' => csrf_token(),
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'medium',
            'status' => 'to_do'
        ]);

        // Should not be blocked by CSRF (might fail for other reasons like validation)
        $this->assertNotEquals(419, $response->getStatusCode());
    }

    public function test_csrf_token_is_present_in_forms()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        $response = $this->get('/tasks/create');

        $response->assertSee('csrf-token');
        $response->assertSee(csrf_token());
    }

    public function test_csrf_cookie_is_set_for_authenticated_users()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertCookie('XSRF-TOKEN');
    }

    public function test_double_submit_csrf_protection()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        // Get CSRF token
        $token = csrf_token();
        
        // Make request with both session token and header
        $response = $this->withHeaders([
            'X-XSRF-TOKEN' => $token,
        ])->post('/tasks', [
            '_token' => $token,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'medium',
            'status' => 'to_do'
        ]);

        // Should not be blocked by CSRF
        $this->assertNotEquals(419, $response->getStatusCode());
    }

    public function test_csrf_protection_works_with_ajax_requests()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        $token = csrf_token();

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $token,
        ])->postJson('/tasks', [
            'title' => 'AJAX Test Task',
            'description' => 'AJAX Test Description',
            'priority' => 'high',
            'status' => 'to_do'
        ]);

        // Should not be blocked by CSRF
        $this->assertNotEquals(419, $response->getStatusCode());
    }

    public function test_csrf_token_regeneration_on_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        // Get initial token
        $initialResponse = $this->get('/login');
        $initialToken = Session::token();

        // Login
        $loginResponse = $this->post('/login', [
            '_token' => $initialToken,
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        // Token should be regenerated
        $newToken = Session::token();
        $this->assertNotEquals($initialToken, $newToken);
    }

    public function test_csrf_protection_excludes_api_routes()
    {
        // API routes should not require CSRF tokens
        $response = $this->postJson('/api/test-endpoint', [
            'data' => 'test'
        ]);

        // Should not return 419 (CSRF error)
        $this->assertNotEquals(419, $response->getStatusCode());
    }

    public function test_csrf_same_site_cookie_attributes()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        // Check that the CSRF cookie has proper SameSite attribute
        $cookies = $response->headers->getCookies();
        
        $csrfCookie = collect($cookies)->first(function ($cookie) {
            return $cookie->getName() === 'XSRF-TOKEN';
        });

        if ($csrfCookie) {
            $this->assertEquals('strict', $csrfCookie->getSameSite());
        }
    }
}
