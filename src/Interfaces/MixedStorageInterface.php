<?php
namespace Apie\StorageMetadataBuilder\Interfaces;

/**
 * Method is not part of interface because of Doctrine proxies,
 * but this should be the constructor.
 *
 * @method __construct(mixed $input)
 */
interface MixedStorageInterface
{
    public function toOriginalObject(): mixed;
}
