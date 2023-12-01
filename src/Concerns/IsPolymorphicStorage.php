<?php
namespace Apie\StorageMetadataBuilder\Concerns;

use Apie\Core\Utils\EntityUtils;
use ReflectionClass;

trait IsPolymorphicStorage
{
    public function createDomainObject(ReflectionClass $class): object
    {
        $class = EntityUtils::findClass($this->discriminatorMapping, $class);
        assert(null !== $class);
        return $class->newInstanceWithoutConstructor();
    }
}
