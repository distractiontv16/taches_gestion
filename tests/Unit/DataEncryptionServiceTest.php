<?php

namespace Tests\Unit;

use App\Services\DataEncryptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataEncryptionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DataEncryptionService $encryptionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->encryptionService = new DataEncryptionService();
    }

    public function test_can_encrypt_and_decrypt_data()
    {
        $originalData = 'Sensitive information that needs encryption';
        
        $encrypted = $this->encryptionService->encrypt($originalData);
        $this->assertNotEquals($originalData, $encrypted);
        $this->assertNotEmpty($encrypted);
        
        $decrypted = $this->encryptionService->decrypt($encrypted);
        $this->assertEquals($originalData, $decrypted);
    }

    public function test_encrypt_returns_null_for_empty_values()
    {
        $this->assertNull($this->encryptionService->encrypt(null));
        $this->assertNull($this->encryptionService->encrypt(''));
    }

    public function test_decrypt_returns_null_for_empty_values()
    {
        $this->assertNull($this->encryptionService->decrypt(null));
        $this->assertNull($this->encryptionService->decrypt(''));
    }

    public function test_can_encrypt_model_fields()
    {
        $data = [
            'title' => 'Test Task',
            'description' => 'This is a test description',
            'status' => 'pending'
        ];

        $encrypted = $this->encryptionService->encryptModelFields('Task', $data);
        
        $this->assertNotEquals($data['title'], $encrypted['title']);
        $this->assertNotEquals($data['description'], $encrypted['description']);
        $this->assertEquals($data['status'], $encrypted['status']); // Not encrypted
    }

    public function test_can_decrypt_model_fields()
    {
        $originalData = [
            'title' => 'Test Task',
            'description' => 'This is a test description',
            'status' => 'pending'
        ];

        $encrypted = $this->encryptionService->encryptModelFields('Task', $originalData);
        $decrypted = $this->encryptionService->decryptModelFields('Task', $encrypted);
        
        $this->assertEquals($originalData['title'], $decrypted['title']);
        $this->assertEquals($originalData['description'], $decrypted['description']);
        $this->assertEquals($originalData['status'], $decrypted['status']);
    }

    public function test_should_encrypt_field_returns_correct_boolean()
    {
        $this->assertTrue($this->encryptionService->shouldEncryptField('Task', 'title'));
        $this->assertTrue($this->encryptionService->shouldEncryptField('Task', 'description'));
        $this->assertFalse($this->encryptionService->shouldEncryptField('Task', 'status'));
        $this->assertFalse($this->encryptionService->shouldEncryptField('Task', 'created_at'));
    }

    public function test_validate_encrypted_data_works_correctly()
    {
        $originalData = 'Test data for validation';
        $encrypted = $this->encryptionService->encrypt($originalData);
        
        $this->assertTrue($this->encryptionService->validateEncryptedData($encrypted));
        $this->assertFalse($this->encryptionService->validateEncryptedData('invalid_encrypted_data'));
        $this->assertFalse($this->encryptionService->validateEncryptedData(''));
    }

    public function test_encryption_produces_different_results_for_same_input()
    {
        $data = 'Same input data';
        
        $encrypted1 = $this->encryptionService->encrypt($data);
        $encrypted2 = $this->encryptionService->encrypt($data);
        
        // Should be different due to random IV
        $this->assertNotEquals($encrypted1, $encrypted2);
        
        // But both should decrypt to the same value
        $this->assertEquals($data, $this->encryptionService->decrypt($encrypted1));
        $this->assertEquals($data, $this->encryptionService->decrypt($encrypted2));
    }

    public function test_handles_special_characters_and_unicode()
    {
        $specialData = 'Données avec caractères spéciaux: àéèùç @#$%^&*()';
        
        $encrypted = $this->encryptionService->encrypt($specialData);
        $decrypted = $this->encryptionService->decrypt($encrypted);
        
        $this->assertEquals($specialData, $decrypted);
    }

    public function test_handles_large_data()
    {
        $largeData = str_repeat('This is a large string for testing encryption. ', 100);
        
        $encrypted = $this->encryptionService->encrypt($largeData);
        $decrypted = $this->encryptionService->decrypt($encrypted);
        
        $this->assertEquals($largeData, $decrypted);
    }

    public function test_decrypt_fails_gracefully_with_corrupted_data()
    {
        $this->expectException(\Exception::class);
        $this->encryptionService->decrypt('corrupted_base64_data_that_cannot_be_decrypted');
    }

    public function test_can_generate_new_key()
    {
        $key = $this->encryptionService->generateNewKey();
        
        $this->assertNotEmpty($key);
        $this->assertTrue(strlen(base64_decode($key)) === 32); // 256 bits
    }
}
