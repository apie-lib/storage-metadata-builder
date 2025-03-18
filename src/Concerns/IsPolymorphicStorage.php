<?php
namespace Apie\StorageMetadataBuilder\Concerns;

use Apie\Core\Entities\PolymorphicEntityInterface;
use Apie\Core\Utils\EntityUtils;
use ReflectionClass;

/**
 * Added for storing polymorphic entities.
 *
 * @see PolymorphicEntityInterface
 */
trait IsPolymorphicStorage
{
    public function createDomainObject(ReflectionClass $class): object
    {
        $class = EntityUtils::findClass($this->discriminatorMapping, $class);
        assert(null !== $class);
        return $class->newInstanceWithoutConstructor();
    }
}
