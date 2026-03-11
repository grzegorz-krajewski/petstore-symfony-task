<?php

namespace App\Service;

use App\Exception\PetstoreApiException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
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
        try {
            $response = $this->httpClient->request(
                'GET',
                sprintf('%s/pet/%d', $this->petstoreApiBaseUrl, $id)
            );

            if ($response->getStatusCode() === 404) {
                return null;
            }

            if ($response->getStatusCode() >= 400) {
                throw new PetstoreApiException('Nie udało się pobrać danych zwierzaka.');
            }

            return $response->toArray(false);
        } catch (ExceptionInterface $exception) {
            throw new PetstoreApiException('Wystąpił błąd podczas komunikacji z API Petstore.', 0, $exception);
        }
    }

    public function createPet(array $data): array
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                sprintf('%s/pet', $this->petstoreApiBaseUrl),
                [
                    'json' => $data,
                ]
            );

            if ($response->getStatusCode() >= 400) {
                throw new PetstoreApiException('Nie udało się dodać zwierzaka.');
            }

            return $response->toArray(false);
        } catch (ExceptionInterface $exception) {
            throw new PetstoreApiException('Wystąpił błąd podczas dodawania zwierzaka.', 0, $exception);
        }
    }

    public function updatePet(array $data): array
    {
        try {
            $response = $this->httpClient->request(
                'PUT',
                sprintf('%s/pet', $this->petstoreApiBaseUrl),
                [
                    'json' => $data,
                ]
            );

            if ($response->getStatusCode() >= 400) {
                throw new PetstoreApiException('Nie udało się zaktualizować zwierzaka.');
            }

            return $response->toArray(false);
        } catch (ExceptionInterface $exception) {
            throw new PetstoreApiException('Wystąpił błąd podczas aktualizacji zwierzaka.', 0, $exception);
        }
    }

    public function deletePet(int $id): void
    {
        try {
            $response = $this->httpClient->request(
                'DELETE',
                sprintf('%s/pet/%d', $this->petstoreApiBaseUrl, $id)
            );

            if ($response->getStatusCode() >= 400) {
                throw new PetstoreApiException('Nie udało się usunąć zwierzaka.');
            }
        } catch (ExceptionInterface $exception) {
            throw new PetstoreApiException('Wystąpił błąd podczas usuwania zwierzaka.', 0, $exception);
        }
    }
}