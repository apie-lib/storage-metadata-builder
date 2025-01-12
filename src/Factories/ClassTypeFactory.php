<?php
namespace Apie\StorageMetadataBuilder\Factories;

use Apie\Core\Entities\EntityInterface;
use Apie\Core\Entities\PolymorphicEntityInterface;
use Apie\Core\Metadata\MetadataFactory;
use Apie\StorageMetadata\Attributes\DiscriminatorMappingAttribute;
use Apie\StorageMetadata\Attributes\GetMethodAttribute;
use Apie\StorageMetadata\Interfaces\StorageClassInstantiatorInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadataBuilder\Concerns\IsPolymorphicStorage;
use Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface;
use Nette\PhpGenerator\ClassType;
use ReflectionClass;
use ReflectionNamedType;

final class ClassTypeFactory
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    public static function createIndexTable(string $tableName)
    {
        $class = new ClassType($tableName);
        $constructor = $class->addMethod('__construct');
        $constructor->addPromotedParameter('text')
            ->setType('string');
        $constructor->addPromotedParameter('priority')
            ->setType('int');
        $constructor->addPromotedParameter('idf', 0)
            ->setType('float');
        $constructor->addPromotedParameter('tf', 0)
            ->setType('float');
        return $class;
    }
    public static function createPrimitiveTable(string $tableName, ReflectionNamedType $primitiveType): ClassType
    {
        $table = new ClassType($tableName);
        $table->addImplement(MixedStorageInterface::class);
        $table->addMethod('__construct')
            ->addPromotedParameter('value')
            ->setPublic()
            ->setType($primitiveType->getName());
        $table->addMethod('toOriginalObject')
            ->setReturnType($primitiveType->getName())
            ->setBody('return $this->value;');
        return $table;
    }

    public static function createStorageTable(string $tableName, ReflectionClass $referencedObject): ClassType
    {
        $table = new ClassType($tableName);
        $table->addComment($referencedObject->name);
        if (in_array(PolymorphicEntityInterface::class, $referencedObject->getInterfaceNames())) {
            $table->addImplement(StorageClassInstantiatorInterface::class);
            $table->addTrait('\\' . IsPolymorphicStorage::class);
            $table->addProperty('discriminatorMapping')
                ->setType('array')
                ->addAttribute(DiscriminatorMappingAttribute::class);
        } else {
            $table->addImplement('\\' . StorageDtoInterface::class);
        }
        if (in_array(EntityInterface::class, $referencedObject->getInterfaceNames())) {
            $returnType = $referencedObject->getMethod('getId')->getReturnType();
            $table->addProperty('id')
                ->addAttribute(GetMethodAttribute::class, ['getId'])
                ->setType('?' . MetadataFactory::getScalarForType($returnType)->value);
        }

        $table->addMethod('getClassReference')
            ->setStatic(true)
            ->setReturnType(ReflectionClass::class)
            ->setBody('return new \\ReflectionClass(\\' . $referencedObject->name . '::class);');

        return $table;
    }
}
