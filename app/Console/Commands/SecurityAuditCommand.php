<?php

namespace App\Console\Commands;

use App\Services\DataEncryptionService;
use App\Services\SecurityAuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SecurityAuditCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'security:audit 
                            {--check-encryption : Check encryption integrity}
                            {--analyze-patterns : Analyze suspicious patterns}
                            {--cleanup-logs : Cleanup old audit logs}
                            {--full : Run full security audit}';

    /**
     * The console command description.
     */
    protected $description = 'Run security audit and analysis';

    protected SecurityAuditService $auditService;
    protected DataEncryptionService $encryptionService;

    public function __construct(SecurityAuditService $auditService, DataEncryptionService $encryptionService)
    {
        parent::__construct();
        $this->auditService = $auditService;
        $this->encryptionService = $encryptionService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔒 Starting Security Audit...');

        if ($this->option('full')) {
            $this->runFullAudit();
        } else {
            if ($this->option('check-encryption')) {
                $this->checkEncryptionIntegrity();
            }

            if ($this->option('analyze-patterns')) {
                $this->analyzeSuspiciousPatterns();
            }

            if ($this->option('cleanup-logs')) {
                $this->cleanupAuditLogs();
            }

            if (!$this->option('check-encryption') && 
                !$this->option('analyze-patterns') && 
                !$this->option('cleanup-logs')) {
                $this->runBasicAudit();
            }
        }

        $this->info('✅ Security audit completed.');
        return 0;
    }

    /**
     * Run full security audit
     */
    protected function runFullAudit(): void
    {
        $this->info('Running full security audit...');
        
        $this->checkEncryptionIntegrity();
        $this->analyzeSuspiciousPatterns();
        $this->checkDatabaseSecurity();
        $this->checkFilePermissions();
        $this->checkConfigurationSecurity();
        $this->cleanupAuditLogs();
    }

    /**
     * Run basic security audit
     */
    protected function runBasicAudit(): void
    {
        $this->info('Running basic security audit...');
        
        $this->checkEncryptionIntegrity();
        $this->analyzeSuspiciousPatterns();
    }

    /**
     * Check encryption integrity
     */
    protected function checkEncryptionIntegrity(): void
    {
        $this->info('🔐 Checking encryption integrity...');

        $models = ['User', 'Task', 'Note', 'Routine', 'Reminder'];
        $totalErrors = 0;

        foreach ($models as $model) {
            $this->line("Checking {$model} encryption...");
            
            $modelClass = "App\\Models\\{$model}";
            $records = $modelClass::all();
            $errors = 0;

            foreach ($records as $record) {
                if (method_exists($record, 'validateEncryptedFields')) {
                    $fieldErrors = $record->validateEncryptedFields();
                    if (!empty($fieldErrors)) {
                        $errors += count($fieldErrors);
                        $this->warn("  - {$model} ID {$record->id}: " . implode(', ', $fieldErrors));
                    }
                }
            }

            if ($errors === 0) {
                $this->info("  ✅ {$model}: No encryption errors found");
            } else {
                $this->error("  ❌ {$model}: {$errors} encryption errors found");
                $totalErrors += $errors;
            }
        }

        if ($totalErrors === 0) {
            $this->info('✅ All encrypted data integrity checks passed');
        } else {
            $this->error("❌ Total encryption errors found: {$totalErrors}");
        }
    }

    /**
     * Analyze suspicious patterns
     */
    protected function analyzeSuspiciousPatterns(): void
    {
        $this->info('🕵️ Analyzing suspicious patterns...');

        $patterns = $this->auditService->analyzeSuspiciousPatterns();

        if (empty($patterns)) {
            $this->info('✅ No suspicious patterns detected');
            return;
        }

        foreach ($patterns as $type => $data) {
            $this->warn("⚠️  Suspicious pattern detected: {$type}");
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Check database security
     */
    protected function checkDatabaseSecurity(): void
    {
        $this->info('🗄️ Checking database security...');

        // Check for default passwords
        $defaultPasswords = DB::table('users')
            ->where('password', bcrypt('password'))
            ->orWhere('password', bcrypt('123456'))
            ->count();

        if ($defaultPasswords > 0) {
            $this->warn("⚠️  {$defaultPasswords} users with default passwords found");
        } else {
            $this->info('✅ No default passwords found');
        }

        // Check for unverified emails
        $unverifiedEmails = DB::table('users')
            ->whereNull('email_verified_at')
            ->count();

        if ($unverifiedEmails > 0) {
            $this->warn("⚠️  {$unverifiedEmails} unverified email addresses found");
        } else {
            $this->info('✅ All email addresses are verified');
        }

        // Check for locked accounts
        $lockedAccounts = DB::table('users')
            ->where('locked_until', '>', now())
            ->count();

        if ($lockedAccounts > 0) {
            $this->info("ℹ️  {$lockedAccounts} accounts are currently locked");
        }
    }

    /**
     * Check file permissions
     */
    protected function checkFilePermissions(): void
    {
        $this->info('📁 Checking file permissions...');

        $criticalFiles = [
            '.env',
            'config/app.php',
            'config/database.php',
            'storage/logs',
        ];

        foreach ($criticalFiles as $file) {
            $path = base_path($file);
            
            if (file_exists($path)) {
                $permissions = substr(sprintf('%o', fileperms($path)), -4);
                
                if ($file === '.env' && $permissions !== '0600') {
                    $this->warn("⚠️  .env file permissions: {$permissions} (should be 0600)");
                } elseif (is_dir($path) && !is_writable($path)) {
                    $this->warn("⚠️  Directory {$file} is not writable");
                } else {
                    $this->info("✅ {$file}: permissions OK");
                }
            } else {
                $this->warn("⚠️  File {$file} not found");
            }
        }
    }

    /**
     * Check configuration security
     */
    protected function checkConfigurationSecurity(): void
    {
        $this->info('⚙️ Checking configuration security...');

        // Check debug mode
        if (config('app.debug') && app()->environment('production')) {
            $this->error('❌ Debug mode is enabled in production');
        } else {
            $this->info('✅ Debug mode configuration OK');
        }

        // Check encryption key
        if (empty(config('app.key'))) {
            $this->error('❌ Application key is not set');
        } else {
            $this->info('✅ Application key is configured');
        }

        // Check HTTPS configuration
        if (app()->environment('production') && !config('session.secure')) {
            $this->warn('⚠️  Secure cookies not enabled in production');
        } else {
            $this->info('✅ Cookie security configuration OK');
        }

        // Check CSRF configuration
        if (!config('security.csrf.double_submit_enabled')) {
            $this->warn('⚠️  Double-submit CSRF protection is disabled');
        } else {
            $this->info('✅ CSRF protection configuration OK');
        }
    }

    /**
     * Cleanup audit logs
     */
    protected function cleanupAuditLogs(): void
    {
        $this->info('🧹 Cleaning up old audit logs...');

        $deletedCount = $this->auditService->cleanupOldLogs();
        
        $this->info("✅ Cleaned up {$deletedCount} old audit log entries");
    }
}
