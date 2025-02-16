<?php
namespace Apie\StorageMetadataBuilder\Resources;

use Apie\Core\Attributes\ProvideIndex;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Lists\StringHashmap;
use Apie\StorageMetadataBuilder\Identifiers\GeneratedCodeTimestampId;
use Apie\StorageMetadataBuilder\Lists\GeneratedCodeHashmap;

#[ProvideIndex('noIndexing')]
class GeneratedCodeTimestamp implements EntityInterface
{
    private GeneratedCodeTimestampId $id;
    private StringHashmap $codeMap;
    public function __construct(GeneratedCodeHashmap $generatedCodeHashmap)
    {
        $this->codeMap = $generatedCodeHashmap->toStringHashmap();
        $this->id = GeneratedCodeTimestampId::createFromMap($this->codeMap);
    }

    public static function noIndexing(): array
    {
        return [];
    }

    public function getId(): GeneratedCodeTimestampId
    {
        return $this->id;
    }
}
