<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\Core\Context\ApieContext;
use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Core\Metadata\ItemHashmapMetadata;
use Apie\Core\Metadata\ItemListMetadata;
use Apie\Core\Metadata\MetadataFactory;
use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\Attributes\OneToManyAttribute;
use Apie\StorageMetadata\Attributes\OrderAttribute;
use Apie\StorageMetadata\Attributes\ParentAttribute;
use Apie\StorageMetadataBuilder\Factories\ClassTypeFactory;
use Apie\StorageMetadataBuilder\Interfaces\RunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;

/**
 * Creates the one to many relations for lists.
 * - create a sub table for the list
 * - the sub table references the entity with 'parent' property
 * - an 'order' property is made for the index of the hashmap or the order of the list.
 */
final class ItemListCodeGenerator implements RunGeneratedCodeContextInterface
{
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
        if ($metadata instanceof ItemListMetadata || $metadata instanceof ItemHashmapMetadata) {
            $tableName = $generatedCodeContext->getPrefix(
                $metadata instanceof ItemListMetadata
                ? 'apie_list_'
                : 'apie_map_'
            );
            $arrayType = $class->getMethod('offsetGet')->getReturnType();
            $arrayClass = $arrayType ? ConverterUtils::toReflectionClass($arrayType) : null;
            if (null === $arrayClass) {
                return;
            }
            $table = ClassTypeFactory::createStorageTable($tableName, $arrayClass);
            $table->addProperty('parent')
                ->setType($currentTable->getName())
                ->addAttribute(ParentAttribute::class);
            $table->addProperty('listOrder')
                ->setType($metadata instanceof ItemListMetadata ? 'int' : 'string')
                ->addAttribute(OrderAttribute::class);
            $generatedCodeContext->withCurrentObject($arrayClass)->iterateOverTable($table);
            $currentTable->addProperty($propertyName)
                ->addAttribute(OneToManyAttribute::class, [$property->name, $tableName, $property->getDeclaringClass()->name]);
        }
    }
}
