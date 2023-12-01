<?php
namespace Apie\StorageMetadataBuilder\Interfaces;

interface MixedStorageInterface
{
    public function __construct(mixed $input);
    public function toOriginalObject(): mixed;
}
