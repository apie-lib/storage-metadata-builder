<?php
namespace Apie\StorageMetadataBuilder\Mediators;

use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\StorageMetadataBuilder\Lists\GeneratedCodeHashmap;
use RuntimeException;

final class GeneratedCode
{
    /**
     * @var GeneratedCodeContext[] $todo
     */
    private array $todo = [];

    public function __construct(
        public readonly BoundedContextHashmap $boundedContextHashmap,
        public readonly GeneratedCodeHashmap $generatedCodeHashmap
    ) {
        foreach ($boundedContextHashmap->getTupleIterator() as $boundedContextTuple) {
            $this->todo[] = new GeneratedCodeContext($this, $boundedContextTuple);
        }
    }

    public function addTodo(GeneratedCodeContext $context)
    {
        $this->todo[] = $context;
    }

    public function hasTodos(): bool
    {
        return count($this->todo) > 1;
    }

    public function getNextTodo(): GeneratedCodeContext
    {
        $current = array_pop($this->todo);
        if ($current instanceof GeneratedCodeContext) {
            return $current;
        }
        throw new RuntimeException('I have no TODOs anymore');
    }
}
