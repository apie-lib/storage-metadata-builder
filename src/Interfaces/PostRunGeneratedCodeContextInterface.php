<?php
namespace Apie\StorageMetadataBuilder\Interfaces;

use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;

interface PostRunGeneratedCodeContextInterface
{
    public function postRun(GeneratedCodeContext $generatedCodeContext): void;
}
