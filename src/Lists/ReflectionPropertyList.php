<?php
namespace Apie\StorageMetadataBuilder\Lists;

use Apie\Core\Entities\PolymorphicEntityInterface;
use Apie\Core\Lists\ItemList;
use Apie\Core\Utils\EntityUtils;
use ReflectionClass;
use ReflectionProperty;

final class ReflectionPropertyList extends ItemList
{
    public function offsetGet(mixed $offset): ReflectionProperty
    {
        return parent::offsetGet($offset);
    }

    public static function createFromClass(ReflectionClass $class): self
    {
        $singleCase = function (ReflectionClass $class): array {
            $list = [];
            $visibility = null;
            while ($class) {
                $list = [...$list, ...$class->getProperties($visibility)];
                $visibility = ReflectionProperty::IS_PRIVATE;
                $class = $class->getParentClass();
            }
            return $list;
        };
        $list = [];
        $classes = [$class];
        if (in_array(PolymorphicEntityInterface::class, $class->getInterfaceNames())) {
            $classes = EntityUtils::getDiscriminatorClasses($class);
        }
        foreach ($classes as $class) {
            $list = [...$list, ...$singleCase($class)];
        }

        return new self($list);
    }
}
