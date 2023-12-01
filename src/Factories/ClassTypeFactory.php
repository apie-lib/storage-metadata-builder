<?php
namespace Apie\StorageMetadataBuilder\Factories;

use Apie\Core\Entities\PolymorphicEntityInterface;
use Apie\StorageMetadata\Attributes\DiscriminatorMappingAttribute;
use Apie\StorageMetadata\Interfaces\StorageClassInstantiatorInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadataBuilder\Concerns\IsPolymorphicStorage;
use Nette\PhpGenerator\ClassType;
use ReflectionClass;

final class ClassTypeFactory
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    public static function createStorageTable(string $tableName, ReflectionClass $referencedObject): ClassType
    {
        $table = new ClassType($tableName);
        if (in_array(PolymorphicEntityInterface::class, $referencedObject->getInterfaceNames())) {
            $table->addImplement(StorageClassInstantiatorInterface::class);
            $table->addTrait(IsPolymorphicStorage::class);
            $table->addProperty('discriminatorMapping')
                ->setType('array')
                ->addAttribute(DiscriminatorMappingAttribute::class);
        } else {
            $table->addImplement(StorageDtoInterface::class);
        }
        $table->addMethod('getClassReference')
            ->setStatic(true)
            ->setReturnType(ReflectionClass::class)
            ->setBody('return new ReflectionClass(\\' . $referencedObject->name . '::class);');

        return $table;
    }
}
