<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\Core\Attributes\StoreOptions;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Enums\ScalarType;
use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Core\Lists\ItemSet;
use Apie\Core\Metadata\ItemHashmapMetadata;
use Apie\Core\Metadata\ItemListMetadata;
use Apie\Core\Metadata\MetadataFactory;
use Apie\Core\Metadata\MetadataInterface;
use Apie\Core\Metadata\UnionTypeMetadata;
use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\Attributes\ManyToManyAttribute;
use Apie\StorageMetadata\Attributes\OneToManyAttribute;
use Apie\StorageMetadata\Attributes\OrderAttribute;
use Apie\StorageMetadata\Attributes\ParentAttribute;
use Apie\StorageMetadata\Interfaces\OwnerLocalEntityStorageInterface;
use Apie\StorageMetadataBuilder\Factories\ClassTypeFactory;
use Apie\StorageMetadataBuilder\Interfaces\RunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;
use ReflectionClass;
use ReflectionType;

/**
 * Creates storage mappings for typed lists and hashmaps.
 * - primitive and value-object items use a one-to-many child table
 * - entity items in lists and hashmaps use owner-local many-to-many entities
 * - an 'order' property stores list positions and hashmap keys.
 */
final class ItemListCodeGenerator implements RunGeneratedCodeContextInterface
{
    private function isValidList(MetadataInterface $metadata): bool
    {
        if ($metadata instanceof ItemListMetadata || $metadata instanceof ItemHashmapMetadata) {
            return true;
        }
        if ($metadata instanceof UnionTypeMetadata) {
            $sub = $metadata->toSkipNull();
            if ($sub instanceof ItemListMetadata || $sub instanceof ItemHashmapMetadata) {
                return true;
            }
        }

        return false;
    }

    private function isNotHashmap(MetadataInterface $metadata): bool
    {
        if ($metadata instanceof ItemListMetadata) {
            return true;
        }
        if ($metadata instanceof ItemHashmapMetadata) {
            return false;
        }
        if ($metadata instanceof UnionTypeMetadata) {
            $sub = $metadata->toSkipNull();
            if ($sub instanceof ItemHashmapMetadata) {
                return false;
            }
        }

        return true;
    }

    private function isSet(MetadataInterface $metadata): bool
    {
        if ($metadata instanceof ItemListMetadata) {
            return $metadata->toClass()->isSubclassOf(ItemSet::class);
        }
        if ($metadata instanceof UnionTypeMetadata) {
            return $this->isSet($metadata->toSkipNull());
        }
        return false;
    }

    private function getEntityClass(ReflectionType $type): ?ReflectionClass
    {
        $class = ConverterUtils::toReflectionClass($type);
        return $class && $class->implementsInterface(EntityInterface::class) ? $class : null;
    }

    public function run(GeneratedCodeContext $generatedCodeContext): void
    {
        $property = $generatedCodeContext->getCurrentProperty();
        $class = $property ? ConverterUtils::toReflectionClass($property) : null;
        $currentTable = $generatedCodeContext->getCurrentTable();
        if (null === $class || null === $currentTable) {
            return;
        }
        $metadata = MetadataFactory::getMetadataStrategyForType($property->getType())
            ->getResultMetadata(new ApieContext());
        $propertyName = 'apie_'
            . str_replace('-', '_', (string) KebabCaseSlug::fromClass($property->getDeclaringClass()))
            . '_'
            . str_replace('-', '_', (string) KebabCaseSlug::fromClass($property));
        if ($currentTable->hasProperty($propertyName)) {
            return;
        }
        if ($this->isValidList($metadata)) {
            $nullableFieldName = null;
            $mutableListFieldName = null;
            foreach ($property->getAttributes(StoreOptions::class) as $attribute) {
                $options = $attribute->newInstance();
                if ($options->mutableListField) {
                    $mutableListFieldName = preg_replace('/^apie_/', 'mute_', $propertyName);
                }
            }
            if ($property->getType() === null || $property->getType()->allowsNull()) {
                $nullableFieldName = preg_replace('/^apie_/', 'null_', $propertyName);
            }
            $tableName = $generatedCodeContext->getPrefix('apie_resource_');
            $arrayType = $class->getMethod('offsetGet')->getReturnType();
            $scalar = MetadataFactory::getScalarForType($arrayType, $arrayType->allowsNull());
            if (!$arrayType || 'mixed' === (string) $arrayType) {
                return;
            }
            $arrayClass = $arrayType ? ConverterUtils::toReflectionClass($arrayType) : null;
            $entityClass = $arrayType ? $this->getEntityClass($arrayType) : null;
            $useManyToMany = $entityClass !== null && !$this->isSet($metadata);
            $table = in_array($scalar, ScalarType::PRIMITIVES)
                ? ClassTypeFactory::createPrimitiveTable($tableName, $scalar->toReflectionType())
                : ClassTypeFactory::createStorageTable($tableName, $arrayClass);
            if ($useManyToMany) {
                $domainId = $table->getProperty('id')->cloneWithName('domainId');
                $table->removeProperty('id');
                $table->addMember($domainId);
                $table->addImplement(OwnerLocalEntityStorageInterface::class);
                $table->addMethod('getKey')
                    ->setReturnType('?int')
                    ->setBody('return $this->id;');
            } else {
                $table->addProperty('parent')
                    ->setType($currentTable->getName())
                    ->addAttribute(ParentAttribute::class);
            }
            $table->addProperty('listOrder')
                ->setType($this->isNotHashmap($metadata) ? 'int' : 'string')
                ->addAttribute(OrderAttribute::class);
            if (in_array($scalar, ScalarType::PRIMITIVES)) {
                $generatedCodeContext->generatedCode->generatedCodeHashmap[$tableName] = $table;
            } else {
                $generatedCodeContext->withCurrentObject($arrayClass)->iterateOverTable($table);
            }
            if ($nullableFieldName) {
                $currentTable->addProperty($nullableFieldName)
                    ->setType('bool')
                    ->setValue(true);
            }
            if ($mutableListFieldName) {
                $currentTable->addProperty($mutableListFieldName)
                    ->setType('bool')
                    ->setValue(true);
            }
            $currentTable->addProperty($propertyName)
                ->addAttribute(
                    $useManyToMany ? ManyToManyAttribute::class : OneToManyAttribute::class,
                    $this->finetune(
                        [
                            $property->name,
                            $tableName,
                            $property->getDeclaringClass()->name,
                            $nullableFieldName,
                            $mutableListFieldName
                        ]
                    )
                );
        }
    }

    /**
     * @param array<int, string> $args
     * @return array<int, string>
     */
    private function finetune(array $args): array
    {
        while (true) {
            if (count($args) === 0) {
                return [];
            }
            if ($args[count($args) - 1] !== null) {
                return $args;
            };
            array_pop($args);
        }
    }
}
