<?php

namespace App\DTO;

final class TagData
{
    public ?int $id = null;
    public ?string $name = null;

    public static function fromArray(array $data): self
    {
        $tag = new self();
        $tag->id = isset($data['id']) ? (int) $data['id'] : null;
        $tag->name = $data['name'] ?? null;

        return $tag;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}