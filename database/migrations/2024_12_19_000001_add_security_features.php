<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add security audit table
        Schema::create('security_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->json('data')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['event_type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });

        // Add encryption metadata table
        Schema::create('encryption_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key_identifier');
            $table->timestamp('created_at');
            $table->timestamp('rotated_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->index(['key_identifier', 'is_active']);
        });

        // Add session security table
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity');
            $table->timestamp('created_at')->nullable();
            
            $table->index(['user_id', 'last_activity']);
            $table->index(['ip_address', 'last_activity']);
        });

        // Add failed login attempts tracking
        Schema::create('failed_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('attempted_at');
            $table->string('reason')->nullable();
            
            $table->index(['ip_address', 'attempted_at']);
            $table->index(['email', 'attempted_at']);
        });

        // Update users table for enhanced security
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->integer('failed_login_attempts')->default(0)->after('last_login_ip');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->boolean('two_factor_enabled')->default(false)->after('locked_until');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
        });

        // Add security flags to tasks table
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_encrypted')->default(false)->after('target_date');
            $table->timestamp('last_accessed_at')->nullable()->after('is_encrypted');
            $table->string('access_level')->default('private')->after('last_accessed_at');
        });

        // Add security flags to notes table
        Schema::table('notes', function (Blueprint $table) {
            $table->boolean('is_encrypted')->default(false)->after('time');
            $table->timestamp('last_accessed_at')->nullable()->after('is_encrypted');
            $table->string('access_level')->default('private')->after('last_accessed_at');
        });

        // Add security flags to routines table
        Schema::table('routines', function (Blueprint $table) {
            $table->boolean('is_encrypted')->default(false)->after('total_tasks_generated');
            $table->timestamp('last_accessed_at')->nullable()->after('is_encrypted');
            $table->string('access_level')->default('private')->after('last_accessed_at');
        });

        // Add security flags to reminders table
        Schema::table('reminders', function (Blueprint $table) {
            $table->boolean('is_encrypted')->default(false)->after('remindable_type');
            $table->timestamp('last_accessed_at')->nullable()->after('is_encrypted');
            $table->string('access_level')->default('private')->after('last_accessed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove security flags from tables
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropColumn(['is_encrypted', 'last_accessed_at', 'access_level']);
        });

        Schema::table('routines', function (Blueprint $table) {
            $table->dropColumn(['is_encrypted', 'last_accessed_at', 'access_level']);
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['is_encrypted', 'last_accessed_at', 'access_level']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['is_encrypted', 'last_accessed_at', 'access_level']);
        });

        // Remove security columns from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_login_at',
                'last_login_ip',
                'failed_login_attempts',
                'locked_until',
                'two_factor_enabled',
                'two_factor_secret'
            ]);
        });

        // Drop security tables
        Schema::dropIfExists('failed_login_attempts');
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('encryption_keys');
        Schema::dropIfExists('security_audit_logs');
    }
};
