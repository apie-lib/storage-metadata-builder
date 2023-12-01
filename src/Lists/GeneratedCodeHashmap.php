<?php
namespace Apie\StorageMetadataBuilder\Lists;

use Apie\Core\Lists\ItemHashmap;
use Nette\PhpGenerator\ClassType;

class GeneratedCodeHashmap extends ItemHashmap
{
    public function offsetGet(mixed $offset): ClassType
    {
        return parent::offsetGet($offset);
    }
}
