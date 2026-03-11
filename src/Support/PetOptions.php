<?php

namespace App\Support;

final class PetOptions
{
    public const CATEGORIES = [
        1 => 'Psy',
        2 => 'Koty',
        3 => 'Ptaki',
        4 => 'Ryby',
        5 => 'Gady',
        6 => 'Gryzonie',
    ];

    public const TAGS = [
        1 => 'szczeniak',
        2 => 'dorosły',
        3 => 'mały',
        4 => 'średni',
        5 => 'duży',
        6 => 'przyjazny',
        7 => 'aktywny',
        8 => 'spokojny',
        9 => 'do adopcji',
        10 => 'rasowy',
    ];

    public static function getCategoryChoices(): array
    {
        $choices = [];

        foreach (self::CATEGORIES as $id => $name) {
            $choices[$name] = (string) $id;
        }

        return $choices;
    }

    public static function getTagChoices(): array
    {
        $choices = [];

        foreach (self::TAGS as $id => $name) {
            $choices[$name] = (string) $id;
        }

        return $choices;
    }

    public static function getCategoryNameById(?int $id): ?string
    {
        return $id !== null ? (self::CATEGORIES[$id] ?? null) : null;
    }

    public static function getTagNameById(int $id): ?string
    {
        return self::TAGS[$id] ?? null;
    }
}