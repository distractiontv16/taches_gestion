<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\SecurityAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SecurityAuditServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SecurityAuditService $auditService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditService = new SecurityAuditService();
    }

    public function test_can_log_login_attempt()
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->once()
            ->with('Security event', \Mockery::type('array'));

        $request = Request::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $this->auditService->logLoginAttempt($request, true, 1);
        
        $this->assertTrue(true); // Test passes if no exception thrown
    }

    public function test_can_log_password_change()
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->once()
            ->with('Security event', \Mockery::type('array'));

        $request = Request::create('/password/change', 'POST');
        
        $this->auditService->logPasswordChange(1, $request);
        
        $this->assertTrue(true);
    }

    public function test_can_log_data_access()
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->once()
            ->with('Security event', \Mockery::type('array'));

        $this->auditService->logDataAccess('Task', 1, 1, 'read');
        
        $this->assertTrue(true);
    }

    public function test_can_log_data_modification()
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->once()
            ->with('Security event', \Mockery::type('array'));

        $changes = [
            'title' => ['old' => 'Old Title', 'new' => 'New Title'],
            'password' => ['old' => 'old_password', 'new' => 'new_password']
        ];

        $this->auditService->logDataModification('Task', 1, 1, $changes);
        
        $this->assertTrue(true);
    }

    public function test_can_log_failed_authentication()
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->once()
            ->with('Security event', \Mockery::type('array'));

        $request = Request::create('/dashboard', 'GET');
        
        $this->auditService->logFailedAuthentication($request, 'Invalid token');
        
        $this->assertTrue(true);
    }

    public function test_can_log_privilege_escalation()
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->once()
            ->with('Security event', \Mockery::type('array'));

        Log::shouldReceive('critical')
            ->once()
            ->with('Privilege escalation detected', \Mockery::type('array'));

        $request = Request::create('/admin/users', 'GET');
        
        $this->auditService->logPrivilegeEscalation(1, 'Attempted admin access', $request);
        
        $this->assertTrue(true);
    }

    public function test_sanitizes_sensitive_data_in_changes()
    {
        $reflection = new \ReflectionClass($this->auditService);
        $method = $reflection->getMethod('sanitizeChanges');
        $method->setAccessible(true);

        $changes = [
            'title' => ['old' => 'Old Title', 'new' => 'New Title'],
            'password' => ['old' => 'old_password', 'new' => 'new_password'],
            'remember_token' => ['old' => 'old_token', 'new' => 'new_token'],
            'api_token' => ['old' => 'old_api_token', 'new' => 'new_api_token']
        ];

        $sanitized = $method->invoke($this->auditService, $changes);

        $this->assertEquals(['old' => 'Old Title', 'new' => 'New Title'], $sanitized['title']);
        $this->assertEquals('[REDACTED]', $sanitized['password']);
        $this->assertEquals('[REDACTED]', $sanitized['remember_token']);
        $this->assertEquals('[REDACTED]', $sanitized['api_token']);
    }

    public function test_analyze_suspicious_patterns_returns_array()
    {
        $patterns = $this->auditService->analyzeSuspiciousPatterns();
        
        $this->assertIsArray($patterns);
    }

    public function test_cleanup_old_logs_returns_integer()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Audit log cleanup completed', \Mockery::type('array'));

        $result = $this->auditService->cleanupOldLogs();
        
        $this->assertIsInt($result);
    }
}
