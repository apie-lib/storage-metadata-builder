<?php
namespace Apie\StorageMetadataBuilder\Lists;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\StringHashmap;
use Nette\PhpGenerator\ClassType;

final class GeneratedCodeHashmap extends ItemHashmap
{
    public function offsetGet(mixed $offset): ClassType
    {
        return parent::offsetGet($offset);
    }

    public function getObjectsWithInterface(string $interface): self
    {
        $internal = [];
        foreach ($this as $file => $code) {
            if (in_array($interface, $code->getImplements())) {
                $internal[$file] = $code;
            }
        }
        return new self($internal);
    }

    public function toStringHashmap(): StringHashmap
    {
        $list = [];
        foreach ($this as $filename => $code) {
            $list[$filename] = (string) $code;
        }
        return new StringHashmap($list);
    }
}
