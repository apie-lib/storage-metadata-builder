<?php
namespace Apie\StorageMetadataBuilder\Mediators;

use Apie\Core\Actions\BoundedContextEntityTuple;
use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\Attributes\ManyToOneAttribute;
use Apie\StorageMetadata\Attributes\OneToManyAttribute;
use Apie\StorageMetadata\Attributes\ParentAttribute;
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

    public function findInverseProperty(string $tableName): ?string
    {
        $classType = $this->generatedCode->generatedCodeHashmap[$tableName] ?? null;
        if (!$classType) {
            return null;
        }
        // TODO promoted constructor arguments? In general parents are not set in the constructor.
        foreach ($classType->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if ($attribute->getName() === OneToManyAttribute::class) {
                    return $property->getName();
                }
            }
        }

        return null;
    }

    public function findParentProperty(string $tableName): ?string
    {
        $classType = $this->generatedCode->generatedCodeHashmap[$tableName] ?? null;
        if (!$classType) {
            return null;
        }
        // TODO promoted constructor arguments? In general parents are not set in the constructor.
        $foundProperty = null;
        foreach ($classType->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if ($attribute->getName() === ManyToOneAttribute::class) {
                    if ($property->getType() === $this->currentObject->name) {
                        return $property->getName();
                    }
                }
                if ($attribute->getName() === ParentAttribute::class) {
                    $foundProperty = $property;
                }
            }
        }

        return $foundProperty?->getName();
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
