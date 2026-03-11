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

    public ?CategoryData $category = null;

    /** @var TagData[] */
    public array $tags = [];

    /** @var string[] */
    public array $photoUrls = [];

    public ?int $categoryId = null;
    public ?string $categoryName = null;

    public ?string $tagsInput = null;

    #[Assert\NotBlank(message: 'Adresy zdjęć są wymagane.')]
    public ?string $photoUrlsInput = null;

    public static function fromArray(array $data): self
    {
        $petData = new self();
        $petData->id = isset($data['id']) ? (int) $data['id'] : null;
        $petData->name = $data['name'] ?? null;
        $petData->status = $data['status'] ?? null;

        if (isset($data['category']) && is_array($data['category'])) {
            $petData->category = CategoryData::fromArray($data['category']);
        }

        $petData->tags = [];
        foreach ($data['tags'] ?? [] as $tag) {
            if (is_array($tag)) {
                $petData->tags[] = TagData::fromArray($tag);
            }
        }

        $petData->photoUrls = array_values(
            array_filter(
                array_map(
                    static fn (mixed $value): string => trim((string) $value),
                    $data['photoUrls'] ?? []
                ),
                static fn (string $value): bool => $value !== ''
            )
        );

        $petData->syncFormInputsFromStructuredFields();

        return $petData;
    }

    public function toArray(): array
    {
        $this->syncStructuredFieldsFromFormInputs();

        $payload = [
            'id' => $this->id,
            'name' => $this->name,
            'photoUrls' => $this->photoUrls,
            'status' => $this->status,
        ];

        if ($this->category !== null && !$this->category->isEmpty()) {
            $payload['category'] = $this->category->toArray();
        }

        if ($this->tags !== []) {
            $payload['tags'] = array_map(
                static fn (TagData $tag): array => $tag->toArray(),
                $this->tags
            );
        }

        return $payload;
    }

    public function syncStructuredFieldsFromFormInputs(): void
    {
        $category = new CategoryData();
        $category->id = $this->categoryId;
        $category->name = $this->categoryName !== null ? trim($this->categoryName) : null;

        $this->category = $category->isEmpty() ? null : $category;

        $this->tags = [];
        if ($this->tagsInput !== null && trim($this->tagsInput) !== '') {
            $parts = preg_split('/[\r\n,]+/', $this->tagsInput) ?: [];
            $index = 1;

            foreach ($parts as $part) {
                $name = trim($part);
                if ($name === '') {
                    continue;
                }

                $tag = new TagData();
                $tag->id = $index;
                $tag->name = $name;
                $this->tags[] = $tag;
                $index++;
            }
        }

        $this->photoUrls = [];
        if ($this->photoUrlsInput !== null && trim($this->photoUrlsInput) !== '') {
            $parts = preg_split('/[\r\n,]+/', $this->photoUrlsInput) ?: [];

            foreach ($parts as $part) {
                $url = trim($part);
                if ($url === '') {
                    continue;
                }

                $this->photoUrls[] = $url;
            }
        }
    }

    public function syncFormInputsFromStructuredFields(): void
    {
        $this->categoryId = $this->category?->id;
        $this->categoryName = $this->category?->name;

        $this->tagsInput = $this->tags !== []
            ? implode(', ', array_map(
                static fn (TagData $tag): string => (string) $tag->name,
                $this->tags
            ))
            : null;

        $this->photoUrlsInput = $this->photoUrls !== []
            ? implode(PHP_EOL, $this->photoUrls)
            : null;
    }
}