<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\StorageMetadataBuilder\Interfaces\BootGeneratedCodeInterface;
use Apie\StorageMetadataBuilder\Interfaces\PostRunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCode;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;
use Nette\PhpGenerator\ClassType;

class AddIndexesCodeGenerator implements BootGeneratedCodeInterface, PostRunGeneratedCodeContextInterface
{
    public function boot(GeneratedCode $generatedCode): void
    {
        $class = new ClassType('apie_index_table');
        $generatedCode->generatedCodeHashmap['apie_index_table'] = $class;
    }
    public function postRun(GeneratedCodeContext $generatedCodeContext): void
    {

    }
}
