<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\Core\Context\ApieContext;
use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Core\Metadata\CompositeMetadata;
use Apie\Core\Metadata\MetadataFactory;
use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\Attributes\OneToOneAttribute;
use Apie\StorageMetadataBuilder\Factories\ClassTypeFactory;
use Apie\StorageMetadataBuilder\Interfaces\RunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;

/**
 * Creates the one to many relations for lists.
 * - create a sub table for the list
 * - the sub table references the entity with 'parent' property
 * - an 'order' property is made for the index of the hashmap or the order of the list.
 */
final class SubObjectCodeGenerator implements RunGeneratedCodeContextInterface
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
        if ($metadata instanceof CompositeMetadata) {
            $tableName = $generatedCodeContext->getPrefix(
                'apie_resource_'
            );
            $table = ClassTypeFactory::createStorageTable($tableName, $class);
            $generatedCodeContext->withCurrentObject($class)->iterateOverTable($table);
            $currentTable->addProperty($propertyName)
                ->setType($tableName)
                ->addAttribute(OneToOneAttribute::class, [$property->name, null, $property->getDeclaringClass()->name]);
        }
    }
}
