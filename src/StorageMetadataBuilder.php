<?php
namespace Apie\StorageMetadataBuilder;

use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\StorageMetadataBuilder\Interfaces\BootGeneratedCodeInterface;
use Apie\StorageMetadataBuilder\Interfaces\PostRunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Interfaces\RunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Lists\GeneratedCodeHashmap;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCode;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;

final class StorageMetadataBuilder
{
    public function __construct(
        private readonly BoundedContextHashmap $boundedContextHashmap,
        private readonly BootGeneratedCodeInterface $bootGeneratedCode,
        private readonly RunGeneratedCodeContextInterface $runGeneratedCode,
        private readonly PostRunGeneratedCodeContextInterface $postRunGeneratedCode
    ) {
    }

    public function generateCode(): GeneratedCode
    {
        $code = new GeneratedCode(
            $this->boundedContextHashmap,
            new GeneratedCodeHashmap([
            ]),
        );
        $this->bootGeneratedCode->boot($code);
        while ($code->hasTodos()) {
            $context = $code->getNextTodo();
            $this->runGeneratedCode->run($context);
        }
        if (!isset($context)) {
            $context = new GeneratedCodeContext($code, null);
        }
        $this->postRunGeneratedCode->postRun($context);

        return $code;
    }
}
