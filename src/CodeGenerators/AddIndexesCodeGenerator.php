<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\StorageMetadata\Attributes\ManyToOneAttribute;
use Apie\StorageMetadata\Attributes\OneToManyAttribute;
use Apie\StorageMetadataBuilder\Concerns\HasIndexes;
use Apie\StorageMetadataBuilder\Factories\ClassTypeFactory;
use Apie\StorageMetadataBuilder\Interfaces\BootGeneratedCodeInterface;
use Apie\StorageMetadataBuilder\Interfaces\HasIndexInterface;
use Apie\StorageMetadataBuilder\Interfaces\PostRunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Interfaces\RootObjectInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCode;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;
use Nette\PhpGenerator\ClassType;

/**
 * Adds a single index table for all tables to be able to do text searches.
 * - creates a global apie_index_table table
 * - for every root object add HasIndexes trait and HasIndexInterface
 * - for every root object add a nullable property in apie_index_table to reference this table.
 */
class AddIndexesCodeGenerator implements BootGeneratedCodeInterface, PostRunGeneratedCodeContextInterface
{
    public function __construct(
        private readonly bool $singleIndexTable = true
    ) {
    }

    public function boot(GeneratedCode $generatedCode): void
    {
        if ($this->singleIndexTable) {
            $class = ClassTypeFactory::createIndexTable('apie_index_table');
            $generatedCode->generatedCodeHashmap['apie_index_table'] = $class;
        }
    }
    public function postRun(GeneratedCodeContext $generatedCodeContext): void
    {
        if ($this->singleIndexTable) {
            $indexTable = $generatedCodeContext->generatedCode->generatedCodeHashmap['apie_index_table'] ?? null;
        }
        assert($indexTable instanceof ClassType);
        foreach ($generatedCodeContext->generatedCode->generatedCodeHashmap->getObjectsWithInterface(RootObjectInterface::class) as $code) {
            if (!$this->singleIndexTable) {
                $indexName = str_replace('apie_resource__', 'apie_index__', $code->getName());
                $indexTable = ClassTypeFactory::createIndexTable($indexName);
                $generatedCodeContext->generatedCode->generatedCodeHashmap[$indexName] = $indexTable;
            }
            $code->addTrait('\\' . HasIndexes::class);
            $code->addImplement(HasIndexInterface::class);
            $code->addMethod('getIndexTable')
                ->setReturnType('ReflectionClass')
                ->setBody('return new \\ReflectionClass(' . $indexTable->getName() . '::class);');
            $code->addProperty('_indexes')
                ->setType('array')
                ->addAttribute(OneToManyAttribute::class, [null, $indexTable->getName()]);
            $indexTable->addProperty('ref_' . $code->getName(), null)
                ->setType('?' . $code->getName())
                ->addAttribute(ManyToOneAttribute::class, ['_indexes']);
        }
    }
}
