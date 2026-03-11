<?php

namespace App\Service;

use App\Exception\PetstoreApiException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    public function uploadPetImage(int $id, UploadedFile $image, ?string $additionalMetadata = null): ?string
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                sprintf('%s/pet/%d/uploadImage', $this->petstoreApiBaseUrl, $id),
                [
                    'body' => [
                        'additionalMetadata' => $additionalMetadata ?? '',
                        'file' => fopen($image->getPathname(), 'r'),
                    ],
                ]
            );

            if ($response->getStatusCode() >= 400) {
                throw new PetstoreApiException('Nie udało się przesłać obrazu.');
            }

            $data = $response->toArray(false);

            return $this->extractUploadedFilePath($data['message'] ?? null);
        } catch (ExceptionInterface $exception) {
            throw new PetstoreApiException('Wystąpił błąd podczas przesyłania obrazu.', 0, $exception);
        }
    }

    private function extractUploadedFilePath(?string $message): ?string
    {
        if ($message === null || trim($message) === '') {
            return null;
        }

        if (preg_match('/File uploaded to\s+([^,]+),/i', $message, $matches) !== 1) {
            return null;
        }

        $path = trim($matches[1]);

        return $path !== '' ? $path : null;
    }
}