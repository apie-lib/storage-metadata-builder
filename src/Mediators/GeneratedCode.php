<?php
namespace Apie\StorageMetadataBuilder\Mediators;

use Apie\Core\Actions\BoundedContextEntityTuple;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Lists\ReflectionClassList;
use Apie\Core\Lists\ReflectionMethodList;
use Apie\StorageMetadataBuilder\Lists\GeneratedCodeHashmap;
use Apie\StorageMetadataBuilder\Resources\GeneratedCodeTimestamp;
use ReflectionClass;
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
        $this->todo[] = new GeneratedCodeContext(
            $this,
            new BoundedContextEntityTuple(
                new BoundedContext('core', new ReflectionClassList(), new ReflectionMethodList()),
                new ReflectionClass(GeneratedCodeTimestamp::class)
            )
        );
    }

    public function addTodo(GeneratedCodeContext $context)
    {
        $this->todo[] = $context;
    }

    public function hasTodos(): bool
    {
        return count($this->todo) > 0;
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
