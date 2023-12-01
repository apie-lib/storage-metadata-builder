<?php
namespace Apie\StorageMetadataBuilder\Interfaces;

use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;

interface RunGeneratedCodeContextInterface
{
    public function run(GeneratedCodeContext $generatedCodeContext): void;
}
