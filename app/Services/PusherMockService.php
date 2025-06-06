<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Service mock pour Pusher en cas de problème d'installation
 * À remplacer par le vrai Pusher une fois installé
 */
class PusherMockService
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'app_id' => config('broadcasting.connections.pusher.app_id'),
            'key' => config('broadcasting.connections.pusher.key'),
            'secret' => config('broadcasting.connections.pusher.secret'),
            'cluster' => config('broadcasting.connections.pusher.options.cluster'),
        ];
    }

    /**
     * Simuler l'envoi d'un événement Pusher
     */
    public function trigger($channel, $event, $data)
    {
        Log::info('Pusher Mock - Event triggered', [
            'channel' => $channel,
            'event' => $event,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ]);

        // En mode développement, on peut logger au lieu d'envoyer
        return [
            'success' => true,
            'message' => 'Event logged (mock mode)',
            'channel' => $channel,
            'event' => $event
        ];
    }

    /**
     * Vérifier la configuration
     */
    public function isConfigured(): bool
    {
        return !empty($this->config['app_id']) && 
               !empty($this->config['key']) && 
               !empty($this->config['secret']);
    }

    /**
     * Obtenir les informations de configuration (sans secrets)
     */
    public function getPublicConfig(): array
    {
        return [
            'key' => $this->config['key'],
            'cluster' => $this->config['cluster'],
            'configured' => $this->isConfigured()
        ];
    }
}
