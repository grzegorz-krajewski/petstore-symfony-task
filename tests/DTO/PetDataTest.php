<?php

namespace App\Tests\DTO;

use App\DTO\PetData;
use PHPUnit\Framework\TestCase;

final class PetDataTest extends TestCase
{
    public function testToArrayBuildsCategoryAndTagsFromSelectedOptions(): void
    {
        $petData = new PetData();
        $petData->id = 101;
        $petData->name = 'Burek';
        $petData->status = 'available';
        $petData->selectedCategory = '1';
        $petData->selectedTags = ['1', '6', '9'];

        $result = $petData->toArray();

        $this->assertSame(101, $result['id']);
        $this->assertSame('Burek', $result['name']);
        $this->assertSame('available', $result['status']);

        $this->assertArrayHasKey('category', $result);
        $this->assertSame([
            'id' => 1,
            'name' => 'Psy',
        ], $result['category']);

        $this->assertArrayHasKey('tags', $result);
        $this->assertCount(3, $result['tags']);

        $this->assertSame(
            ['id' => 1, 'name' => 'szczeniak'],
            $result['tags'][0]
        );

        $this->assertSame(
            ['id' => 6, 'name' => 'przyjazny'],
            $result['tags'][1]
        );

        $this->assertSame(
            ['id' => 9, 'name' => 'do adopcji'],
            $result['tags'][2]
        );
    }

    public function testFromArrayFillsSelectedCategoryAndSelectedTags(): void
    {
        $petData = PetData::fromArray([
            'id' => 202,
            'name' => 'Mruczek',
            'status' => 'pending',
            'category' => [
                'id' => 2,
                'name' => 'Koty',
            ],
            'tags' => [
                ['id' => 2, 'name' => 'dorosły'],
                ['id' => 8, 'name' => 'spokojny'],
            ],
            'photoUrls' => [
                './cat-1.jpg',
                './cat-2.jpg',
            ],
        ]);

        $this->assertSame(202, $petData->id);
        $this->assertSame('Mruczek', $petData->name);
        $this->assertSame('pending', $petData->status);

        $this->assertNotNull($petData->category);
        $this->assertSame(2, $petData->category->id);
        $this->assertSame('Koty', $petData->category->name);

        $this->assertSame('2', $petData->selectedCategory);
        $this->assertSame(['2', '8'], $petData->selectedTags);
        $this->assertSame(['./cat-1.jpg', './cat-2.jpg'], $petData->photoUrls);
    }

    public function testAddPhotoUrlAddsOnlyUniqueNonEmptyValues(): void
    {
        $petData = new PetData();
        $petData->photoUrls = ['./cat-1.jpg'];

        $petData->addPhotoUrl('./cat-2.jpg');
        $petData->addPhotoUrl('./cat-1.jpg');
        $petData->addPhotoUrl('   ');
        $petData->addPhotoUrl('./cat-2.jpg');

        $this->assertSame([
            './cat-1.jpg',
            './cat-2.jpg',
        ], $petData->photoUrls);
    }
}