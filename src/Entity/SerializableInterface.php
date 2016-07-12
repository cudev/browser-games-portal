<?php

namespace Ludos\Entity;

interface SerializableInterface
{
    public function toArray(): array;
    public function toJson(): string;
    public static function createFromArray(array $serialized);
    public static function createFromJson(string $serialized);
}
