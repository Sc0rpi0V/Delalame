<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Géocodage d'adresses via Nominatim (OpenStreetMap) — gratuit, sans clé API.
 * Respecte la politique d'usage de Nominatim : un User-Agent identifiable
 * et un appel ponctuel (déclenché uniquement à l'enregistrement de l'adresse
 * en back-office, jamais à chaque affichage de page).
 */
class GeocodingService
{
    private const ENDPOINT = 'https://nominatim.openstreetmap.org/search';

    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    public function geocode(string $address): ?array
    {
        if (trim($address) === '') {
            return null;
        }

        try {
            $response = $this->client->request('GET', self::ENDPOINT, [
                'query' => [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'DelalameSite/1.0 (contact@delalame.fr)',
                ],
                'timeout' => 5,
            ]);

            $results = $response->toArray(false);

            if (empty($results[0]['lat']) || empty($results[0]['lon'])) {
                return null;
            }

            return [
                'lat' => (float) $results[0]['lat'],
                'lng' => (float) $results[0]['lon'],
            ];
        } catch (\Throwable $e) {
            $this->logger->warning('Géocodage impossible pour l\'adresse "{address}" : {error}', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
