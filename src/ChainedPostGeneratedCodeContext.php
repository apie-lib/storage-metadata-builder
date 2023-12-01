<?php
namespace Apie\StorageMetadataBuilder;

use Apie\StorageMetadataBuilder\Interfaces\PostRunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;

final class ChainedPostGeneratedCodeContext implements PostRunGeneratedCodeContextInterface
{
    /**
     * @var array<int, PostRunGeneratedCodeContextInterface> $codeGenerators
     */
    private array $codeGenerators;
    public function __construct(PostRunGeneratedCodeContextInterface... $codeGenerators)
    {
        $this->codeGenerators = $codeGenerators;
    }

    public static function createFromIterable(iterable $codeGenerators): self
    {
        return new self(...$codeGenerators);
    }

    public function postRun(GeneratedCodeContext $generatedCodeContext): void
    {
        foreach ($this->codeGenerators as $codeGenerator) {
            $codeGenerator->postRun($generatedCodeContext);
        }
    }
}
