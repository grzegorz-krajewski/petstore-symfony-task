<?php

namespace App\DTO;

use App\Support\PetOptions;
use Symfony\Component\Validator\Constraints as Assert;

final class PetData
{
    #[Assert\NotNull(message: 'ID jest wymagane.')]
    #[Assert\Positive(message: 'ID musi być dodatnią liczbą.')]
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Nazwa jest wymagana.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Nazwa nie może być dłuższa niż 255 znaków.'
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

    public ?string $selectedCategory = null;

    /** @var string[] */
    public array $selectedTags = [];

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
        $this->category = null;

        if ($this->selectedCategory !== null && $this->selectedCategory !== '') {
            $categoryId = (int) $this->selectedCategory;
            $categoryName = PetOptions::getCategoryNameById($categoryId);

            if ($categoryName !== null) {
                $category = new CategoryData();
                $category->id = $categoryId;
                $category->name = $categoryName;
                $this->category = $category;
            }
        }

        $this->tags = [];

        foreach ($this->selectedTags as $selectedTag) {
            $tagId = (int) $selectedTag;
            $tagName = PetOptions::getTagNameById($tagId);

            if ($tagName === null) {
                continue;
            }

            $tag = new TagData();
            $tag->id = $tagId;
            $tag->name = $tagName;
            $this->tags[] = $tag;
        }
    }

    public function syncFormInputsFromStructuredFields(): void
    {
        $this->selectedCategory = $this->category?->id !== null
            ? (string) $this->category->id
            : null;

        $this->selectedTags = array_map(
            static fn (TagData $tag): string => (string) $tag->id,
            array_filter(
                $this->tags,
                static fn (TagData $tag): bool => $tag->id !== null
            )
        );
    }
}