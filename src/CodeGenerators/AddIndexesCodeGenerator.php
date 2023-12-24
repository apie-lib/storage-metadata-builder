<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\StorageMetadata\Attributes\ManyToOneAttribute;
use Apie\StorageMetadata\Attributes\OneToManyAttribute;
use Apie\StorageMetadataBuilder\Concerns\HasIndexes;
use Apie\StorageMetadataBuilder\Interfaces\BootGeneratedCodeInterface;
use Apie\StorageMetadataBuilder\Interfaces\HasIndexInterface;
use Apie\StorageMetadataBuilder\Interfaces\PostRunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Interfaces\RootObjectInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCode;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;
use Nette\PhpGenerator\ClassType;

/**
 * Adds a single index table for all tables to be able to do text searches.
 */
class AddIndexesCodeGenerator implements BootGeneratedCodeInterface, PostRunGeneratedCodeContextInterface
{
    public function boot(GeneratedCode $generatedCode): void
    {
        $class = new ClassType('apie_index_table');
        $constructor = $class->addMethod('__construct');
        $constructor->addPromotedParameter('text')
            ->setType('string');
        $constructor->addPromotedParameter('priority')
            ->setType('int');
        $constructor->addPromotedParameter('idf', null)
            ->setType('?float');
        $constructor->addPromotedParameter('tf', null)
            ->setType('?float');
        $generatedCode->generatedCodeHashmap['apie_index_table'] = $class;
    }
    public function postRun(GeneratedCodeContext $generatedCodeContext): void
    {
        $indexTable = $generatedCodeContext->generatedCode->generatedCodeHashmap['apie_index_table'] ?? null;
        assert($indexTable instanceof ClassType);
        foreach ($generatedCodeContext->generatedCode->generatedCodeHashmap->getObjectsWithInterface(RootObjectInterface::class) as $code) {
            $code->addTrait('\\' . HasIndexes::class);
            $code->addImplement(HasIndexInterface::class);
            $code->addMethod('getIndexTable')
                ->setReturnType('ReflectionClass')
                ->setBody('return new \\ReflectionClass(' . $indexTable->getName() . '::class);');
            $code->addProperty('_indexes')
                ->setType('array')
                ->addAttribute(OneToManyAttribute::class, [null, 'apie_index_table']);
            $indexTable->addProperty('ref_' . $code->getName(), null)
                ->setType('?' . $code->getName())
                ->addAttribute(ManyToOneAttribute::class, ['_indexes']);
        }
    }
}
