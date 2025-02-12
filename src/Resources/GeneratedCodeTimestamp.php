<?php
namespace Apie\StorageMetadataBuilder\Resources;

use Apie\Core\Entities\EntityInterface;
use Apie\Core\Lists\StringHashmap;
use Apie\StorageMetadataBuilder\Identifiers\GeneratedCodeTimestampId;
use Apie\StorageMetadataBuilder\Lists\GeneratedCodeHashmap;

class GeneratedCodeTimestamp implements EntityInterface
{
    private GeneratedCodeTimestampId $id;
    public StringHashmap $codeMap;
    public function __construct(GeneratedCodeHashmap $generatedCodeHashmap)
    {
        $this->codeMap = $generatedCodeHashmap->toStringHashmap();
        $this->id = GeneratedCodeTimestampId::createFromMap($this->codeMap);
    }

    public function getId(): GeneratedCodeTimestampId
    {
        return $this->id;
    }
}
