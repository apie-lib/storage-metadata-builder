<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\StorageMetadataBuilder\Factories\ClassTypeFactory;
use Apie\StorageMetadataBuilder\Interfaces\RootObjectInterface;
use Apie\StorageMetadataBuilder\Interfaces\RunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;

/**
 * Creates the root entities.
 */
final class RootObjectCodeGenerator implements RunGeneratedCodeContextInterface
{
    public function run(GeneratedCodeContext $generatedCodeContext): void
    {
        $currentObject = $generatedCodeContext->getCurrentObject();
        if (null === $currentObject || null !== $generatedCodeContext->getCurrentProperty()) {
            return;
        }
        $tableName = $generatedCodeContext->getPrefix('apie_resource_');
        $table = ClassTypeFactory::createStorageTable($tableName, $currentObject);
        $table->addImplement(RootObjectInterface::class);
        $generatedCodeContext->iterateOverTable($table);
    }
}
