<?php
namespace Apie\StorageMetadataBuilder;

use Apie\StorageMetadataBuilder\Interfaces\BootGeneratedCodeInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCode;

final class ChainedBootGeneratedCode implements BootGeneratedCodeInterface
{
    /**
     * @var array<int, BootGeneratedCodeInterface> $bootCodeGenerators
     */
    private array $bootCodeGenerators;
    public function __construct(BootGeneratedCodeInterface... $bootCodeGenerators)
    {
        $this->bootCodeGenerators = $bootCodeGenerators;
    }

    public static function createFromIterable(iterable $bootCodeGenerators): self
    {
        return new self(...$bootCodeGenerators);
    }

    public function boot(GeneratedCode $generatedCode): void
    {
        foreach ($this->bootCodeGenerators as $bootCodeGenerator) {
            $bootCodeGenerator->boot($generatedCode);
        }
    }
}
