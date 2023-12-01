<?php
namespace Apie\StorageMetadataBuilder\Interfaces;

use Apie\StorageMetadataBuilder\Mediators\GeneratedCode;

interface BootGeneratedCodeInterface
{
    public function boot(GeneratedCode $generatedCode): void;
}
