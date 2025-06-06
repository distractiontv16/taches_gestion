<?php

namespace App\Traits;

use App\Services\DataEncryptionService;
use Illuminate\Support\Facades\Log;

/**
 * Trait pour gérer le chiffrement automatique des champs de modèle
 */
trait EncryptableFields
{
    /**
     * Service de chiffrement
     */
    protected ?DataEncryptionService $encryptionService = null;

    /**
     * Boot the trait
     */
    protected static function bootEncryptableFields(): void
    {
        // Chiffrer les données avant la sauvegarde
        static::saving(function ($model) {
            $model->encryptFields();
        });

        // Déchiffrer les données après la récupération
        static::retrieved(function ($model) {
            $model->decryptFields();
        });
    }

    /**
     * Obtient le service de chiffrement
     */
    protected function getEncryptionService(): DataEncryptionService
    {
        if ($this->encryptionService === null) {
            $this->encryptionService = app(DataEncryptionService::class);
        }

        return $this->encryptionService;
    }

    /**
     * Chiffre les champs spécifiés
     */
    protected function encryptFields(): void
    {
        $modelName = class_basename($this);
        $encryptionService = $this->getEncryptionService();

        foreach ($this->getAttributes() as $key => $value) {
            if ($encryptionService->shouldEncryptField($modelName, $key) && !empty($value)) {
                // Vérifier si la valeur n'est pas déjà chiffrée
                if (!$this->isAlreadyEncrypted($value)) {
                    try {
                        $this->attributes[$key] = $encryptionService->encrypt($value);
                        
                        Log::debug('Field encrypted', [
                            'model' => $modelName,
                            'field' => $key,
                            'model_id' => $this->getKey()
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Field encryption failed', [
                            'model' => $modelName,
                            'field' => $key,
                            'model_id' => $this->getKey(),
                            'error' => $e->getMessage()
                        ]);
                        
                        // En cas d'erreur, garder la valeur originale
                        // et signaler le problème
                        throw new \Exception("Encryption failed for field {$key}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Déchiffre les champs spécifiés
     */
    protected function decryptFields(): void
    {
        $modelName = class_basename($this);
        $encryptionService = $this->getEncryptionService();

        foreach ($this->getAttributes() as $key => $value) {
            if ($encryptionService->shouldEncryptField($modelName, $key) && !empty($value)) {
                // Vérifier si la valeur est chiffrée
                if ($this->isAlreadyEncrypted($value)) {
                    try {
                        $this->attributes[$key] = $encryptionService->decrypt($value);
                        
                        Log::debug('Field decrypted', [
                            'model' => $modelName,
                            'field' => $key,
                            'model_id' => $this->getKey()
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Field decryption failed', [
                            'model' => $modelName,
                            'field' => $key,
                            'model_id' => $this->getKey(),
                            'error' => $e->getMessage()
                        ]);
                        
                        // En cas d'erreur de déchiffrement, garder la valeur chiffrée
                        // pour éviter la corruption des données
                    }
                }
            }
        }
    }

    /**
     * Vérifie si une valeur est déjà chiffrée
     */
    protected function isAlreadyEncrypted(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        // Vérifier si c'est du base64 valide (format de nos données chiffrées)
        if (base64_encode(base64_decode($value, true)) !== $value) {
            return false;
        }

        // Vérifier la longueur minimale (IV + tag + données)
        $decoded = base64_decode($value);
        if (strlen($decoded) < 32) {
            return false;
        }

        return true;
    }

    /**
     * Obtient la valeur déchiffrée d'un attribut
     */
    public function getDecryptedAttribute(string $key): ?string
    {
        $value = $this->getAttributeValue($key);
        
        if (empty($value)) {
            return $value;
        }

        $modelName = class_basename($this);
        $encryptionService = $this->getEncryptionService();

        if ($encryptionService->shouldEncryptField($modelName, $key) && $this->isAlreadyEncrypted($value)) {
            try {
                return $encryptionService->decrypt($value);
            } catch (\Exception $e) {
                Log::error('On-demand decryption failed', [
                    'model' => $modelName,
                    'field' => $key,
                    'model_id' => $this->getKey(),
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        }

        return $value;
    }

    /**
     * Définit la valeur chiffrée d'un attribut
     */
    public function setEncryptedAttribute(string $key, ?string $value): void
    {
        if (empty($value)) {
            $this->attributes[$key] = $value;
            return;
        }

        $modelName = class_basename($this);
        $encryptionService = $this->getEncryptionService();

        if ($encryptionService->shouldEncryptField($modelName, $key)) {
            try {
                $this->attributes[$key] = $encryptionService->encrypt($value);
            } catch (\Exception $e) {
                Log::error('On-demand encryption failed', [
                    'model' => $modelName,
                    'field' => $key,
                    'model_id' => $this->getKey(),
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Recherche dans les champs chiffrés (attention: performance limitée)
     */
    public function scopeWhereEncrypted($query, string $field, string $value)
    {
        $modelName = class_basename($this);
        $encryptionService = $this->getEncryptionService();

        if ($encryptionService->shouldEncryptField($modelName, $field)) {
            try {
                $encryptedValue = $encryptionService->encrypt($value);
                return $query->where($field, $encryptedValue);
            } catch (\Exception $e) {
                Log::error('Encrypted search failed', [
                    'model' => $modelName,
                    'field' => $field,
                    'error' => $e->getMessage()
                ]);
                // Retourner une requête qui ne trouve rien
                return $query->whereRaw('1 = 0');
            }
        }

        return $query->where($field, $value);
    }

    /**
     * Valide l'intégrité des données chiffrées
     */
    public function validateEncryptedFields(): array
    {
        $errors = [];
        $modelName = class_basename($this);
        $encryptionService = $this->getEncryptionService();

        foreach ($this->getAttributes() as $key => $value) {
            if ($encryptionService->shouldEncryptField($modelName, $key) && !empty($value)) {
                if ($this->isAlreadyEncrypted($value)) {
                    if (!$encryptionService->validateEncryptedData($value)) {
                        $errors[] = "Field {$key} contains invalid encrypted data";
                    }
                }
            }
        }

        return $errors;
    }
}
