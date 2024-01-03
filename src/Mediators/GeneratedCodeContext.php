<?php
namespace Apie\StorageMetadataBuilder\Mediators;

use Apie\Core\Actions\BoundedContextEntityTuple;
use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadataBuilder\Lists\ReflectionPropertyList;
use Apie\TypeConverter\ReflectionTypeFactory;
use Nette\PhpGenerator\ClassType;
use ReflectionClass;
use ReflectionProperty;

final class GeneratedCodeContext
{
    private array $visited = [];

    private array $visitedTables = [];

    private ?ReflectionClass $currentObject;

    public function __construct(
        public readonly GeneratedCode $generatedCode,
        public readonly BoundedContextEntityTuple $boundedContextEntityTuple
    ) {
        $this->currentObject = $boundedContextEntityTuple->resourceClass;
    }

    public function getCurrentObject(): ?ReflectionClass
    {
        return $this->currentObject;
    }

    public function getCurrentTable(): ?ClassType
    {
        return empty($this->visitedTables) ? null : $this->visitedTables[count($this->visitedTables) - 1];
    }

    public function getCurrentProperty(): ?ReflectionProperty
    {
        return empty($this->visited) ? null : $this->visited[count($this->visited) - 1];
    }

    public function withCurrentObject(?ReflectionClass $currentObject)
    {
        $res = clone $this;
        $res->currentObject = $currentObject;
        return $res;
    }

    public function iterateOverTable(ClassType $table): void
    {
        $tableName = $table->getName();
        // we are dealing with something recursive....
        if (isset($this->generatedCode->generatedCodeHashmap[$tableName])) {
            return;
        }
        $this->generatedCode->generatedCodeHashmap[$tableName] = $table;
        if ($this->currentObject !== null) {
            foreach (ReflectionPropertyList::createFromClass($this->currentObject) as $property) {
                if ($property->isStatic()) {
                    continue;
                }
                $clone = clone $this;
                $clone->visited[] = $property;
                $clone->visitedTables[] = $table;
                $class = ConverterUtils::toReflectionClass($property->getType() ?? ReflectionTypeFactory::createReflectionType('mixed'));
                if ($class !== null) {
                    $clone->currentObject = $class;
                }
                $this->generatedCode->addTodo($clone);
            }
        }
    }

    public function getPrefix(string $prefix = ''): string
    {
        return $prefix
            . '_'
            . $this->boundedContextEntityTuple->boundedContext->getId()
            . '_'
            . str_replace('-', '_', (string) KebabCaseSlug::fromClass($this->boundedContextEntityTuple->resourceClass))
            . (empty($this->visited) ? '' : '__')
            . implode(
                '__',
                array_map(
                    function (ReflectionProperty $property) {
                        return str_replace('-', '_', (string) KebabCaseSlug::fromClass($property));
                    },
                    $this->visited
                )
            );
    }
}
