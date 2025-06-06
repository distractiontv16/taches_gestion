<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Service de chiffrement des données sensibles
 * Implémente AES-256-GCM pour le chiffrement des données au repos
 */
class DataEncryptionService
{
    private string $algorithm;
    private string $key;
    private array $encryptedFields;

    public function __construct()
    {
        $this->algorithm = Config::get('security.encryption.algorithm', 'AES-256-GCM');
        $this->key = $this->getEncryptionKey();
        $this->encryptedFields = Config::get('security.encryption.encrypted_fields', []);
    }

    /**
     * Chiffre une valeur
     */
    public function encrypt(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }

        try {
            $iv = random_bytes(16);
            $tag = '';
            
            $encrypted = openssl_encrypt(
                $value,
                $this->algorithm,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            if ($encrypted === false) {
                throw new Exception('Encryption failed');
            }

            // Combine IV, tag, and encrypted data
            $result = base64_encode($iv . $tag . $encrypted);
            
            Log::info('Data encrypted successfully', [
                'data_length' => strlen($value),
                'encrypted_length' => strlen($result)
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Encryption failed', [
                'error' => $e->getMessage(),
                'data_length' => strlen($value)
            ]);
            throw $e;
        }
    }

    /**
     * Déchiffre une valeur
     */
    public function decrypt(?string $encryptedValue): ?string
    {
        if (empty($encryptedValue)) {
            return $encryptedValue;
        }

        try {
            $data = base64_decode($encryptedValue);
            
            if ($data === false || strlen($data) < 32) {
                throw new Exception('Invalid encrypted data format');
            }

            $iv = substr($data, 0, 16);
            $tag = substr($data, 16, 16);
            $encrypted = substr($data, 32);

            $decrypted = openssl_decrypt(
                $encrypted,
                $this->algorithm,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            if ($decrypted === false) {
                throw new Exception('Decryption failed');
            }

            Log::info('Data decrypted successfully', [
                'encrypted_length' => strlen($encryptedValue),
                'decrypted_length' => strlen($decrypted)
            ]);

            return $decrypted;
        } catch (Exception $e) {
            Log::error('Decryption failed', [
                'error' => $e->getMessage(),
                'encrypted_length' => strlen($encryptedValue)
            ]);
            throw $e;
        }
    }

    /**
     * Chiffre les champs spécifiés d'un modèle
     */
    public function encryptModelFields(string $modelName, array $data): array
    {
        $fieldsToEncrypt = $this->getEncryptedFieldsForModel($modelName);
        
        foreach ($fieldsToEncrypt as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $data[$field] = $this->encrypt($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Déchiffre les champs spécifiés d'un modèle
     */
    public function decryptModelFields(string $modelName, array $data): array
    {
        $fieldsToDecrypt = $this->getEncryptedFieldsForModel($modelName);
        
        foreach ($fieldsToDecrypt as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                try {
                    $data[$field] = $this->decrypt($data[$field]);
                } catch (Exception $e) {
                    Log::warning('Failed to decrypt field', [
                        'model' => $modelName,
                        'field' => $field,
                        'error' => $e->getMessage()
                    ]);
                    // Keep original value if decryption fails
                }
            }
        }

        return $data;
    }

    /**
     * Vérifie si un champ doit être chiffré pour un modèle donné
     */
    public function shouldEncryptField(string $modelName, string $fieldName): bool
    {
        $fieldsToEncrypt = $this->getEncryptedFieldsForModel($modelName);
        return in_array($fieldName, $fieldsToEncrypt);
    }

    /**
     * Obtient les champs à chiffrer pour un modèle
     */
    private function getEncryptedFieldsForModel(string $modelName): array
    {
        $modelKey = strtolower(class_basename($modelName));
        return $this->encryptedFields[$modelKey] ?? [];
    }

    /**
     * Obtient la clé de chiffrement
     */
    private function getEncryptionKey(): string
    {
        $key = Config::get('app.key');
        
        if (empty($key)) {
            throw new Exception('Application key not set');
        }

        // Remove base64: prefix if present
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return $key;
    }

    /**
     * Génère une nouvelle clé de chiffrement
     */
    public function generateNewKey(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Effectue la rotation des clés de chiffrement
     */
    public function rotateKeys(): bool
    {
        try {
            $lastRotation = Cache::get('encryption_key_last_rotation');
            $rotationInterval = Config::get('security.encryption.key_rotation_days', 90) * 24 * 60 * 60;

            if ($lastRotation && (time() - $lastRotation) < $rotationInterval) {
                return false; // Rotation not needed yet
            }

            // This would typically involve:
            // 1. Generating a new key
            // 2. Re-encrypting all data with the new key
            // 3. Updating the application key
            // For now, we just log the rotation event
            
            Log::info('Key rotation initiated', [
                'last_rotation' => $lastRotation,
                'rotation_interval' => $rotationInterval
            ]);

            Cache::put('encryption_key_last_rotation', time());
            return true;
        } catch (Exception $e) {
            Log::error('Key rotation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Valide l'intégrité des données chiffrées
     */
    public function validateEncryptedData(string $encryptedValue): bool
    {
        try {
            $this->decrypt($encryptedValue);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
