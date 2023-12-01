<?php
namespace Apie\StorageMetadataBuilder;

use Apie\StorageMetadataBuilder\Interfaces\RunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;

final class ChainedGeneratedCodeContext implements RunGeneratedCodeContextInterface
{
    /**
     * @var array<int, RunGeneratedCodeContextInterface> $codeGenerators
     */
    private array $codeGenerators;
    public function __construct(RunGeneratedCodeContextInterface... $codeGenerators)
    {
        $this->codeGenerators = $codeGenerators;
    }

    public static function createFromIterable(iterable $codeGenerators): self
    {
        return new self(...$codeGenerators);
    }

    public function run(GeneratedCodeContext $generatedCodeContext): void
    {
        foreach ($this->codeGenerators as $codeGenerator) {
            $codeGenerator->run($generatedCodeContext);
        }
    }
}
