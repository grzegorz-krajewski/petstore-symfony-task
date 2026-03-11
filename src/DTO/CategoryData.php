<?php

namespace App\DTO;

final class CategoryData
{
    public ?int $id = null;
    public ?string $name = null;

    public static function fromArray(array $data): self
    {
        $category = new self();
        $category->id = isset($data['id']) ? (int) $data['id'] : null;
        $category->name = $data['name'] ?? null;

        return $category;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === null && ($this->name === null || trim($this->name) === '');
    }
}