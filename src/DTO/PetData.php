<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class PetData
{
    #[Assert\NotNull(message: 'ID jest wymagane.')]
    #[Assert\Positive(message: 'ID musi być dodatnią liczbą.')]
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Nazwa jest wymagana.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Nazwa przekracza dopuszczalną ilość znaków.'
    )]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'Status jest wymagany.')]
    #[Assert\Choice(
        choices: ['available', 'pending', 'sold'],
        message: 'Wybierz poprawny status.'
    )]
    public ?string $status = null;

    public static function fromArray(array $data): self
    {
        $petData = new self();
        $petData->id = isset($data['id']) ? (int) $data['id'] : null;
        $petData->name = $data['name'] ?? null;
        $petData->status = $data['status'] ?? null;

        return $petData;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
        ];
    }
}