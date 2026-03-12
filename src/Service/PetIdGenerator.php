<?php

namespace App\Service;

final class PetIdGenerator
{
    public function __construct(
        private readonly PetstoreClient $petstoreClient,
    ) {
    }

    public function generate(): int
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $id = random_int(100000, 999999999);

            if (!$this->petstoreClient->petExists($id)) {
                return $id;
            }
        }

        throw new \RuntimeException('Nie udało się wygenerować unikalnego ID zwierzaka.');
    }
}