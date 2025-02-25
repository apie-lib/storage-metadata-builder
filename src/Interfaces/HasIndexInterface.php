<?php
namespace Apie\StorageMetadataBuilder\Interfaces;

use ReflectionClass;

interface HasIndexInterface
{
    public static function getIndexTable(): ReflectionClass;

    /**
     * @param array<string, int> $indexes
     */
    public function replaceIndexes(array $indexes): void;
}
