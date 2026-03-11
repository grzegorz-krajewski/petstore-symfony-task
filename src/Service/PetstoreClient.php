<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PetstoreClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $petstoreApiBaseUrl,
    ) {
    }

    public function getPetById(int $id): ?array
    {
        $response = $this->httpClient->request(
            'GET',
            sprintf('%s/pet/%d', $this->petstoreApiBaseUrl, $id)
        );

        if ($response->getStatusCode() === 404) {
            return null;
        }

        return $response->toArray(false);
    }

    public function createPet(array $data): array
    {
        $response = $this->httpClient->request(
            'POST',
            sprintf('%s/pet', $this->petstoreApiBaseUrl),
            [
                'json' => $data,
            ]
        );

        return $response->toArray(false);
    }
}