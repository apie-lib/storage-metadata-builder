<?php
namespace Apie\StorageMetadataBuilder\Identifiers;

use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\Lists\StringHashmap;
use Apie\Core\ValueObjects\Md5Checksum;
use Apie\StorageMetadataBuilder\Resources\GeneratedCodeTimestamp;
use ReflectionClass;

/**
 * @implements IdentifierInterface<GeneratedCodeTimestamp>
 */
class GeneratedCodeTimestampId extends Md5Checksum implements IdentifierInterface
{
    public static function getReferenceFor(): ReflectionClass
    {
        return new ReflectionClass(GeneratedCodeTimestamp::class);
    }

    public static function createFromMap(StringHashmap $generatedCodeHashmap)
    {
        return self::fromNative(md5(json_encode($generatedCodeHashmap)));
    }
}
