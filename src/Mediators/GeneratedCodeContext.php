<?php
namespace Apie\StorageMetadataBuilder\Mediators;

use Apie\Core\Actions\BoundedContextEntityTuple;
use Apie\Core\FileStorage\StoredFile;
use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\Attributes\ManyToOneAttribute;
use Apie\StorageMetadata\Attributes\OneToManyAttribute;
use Apie\StorageMetadata\Attributes\OrderAttribute;
use Apie\StorageMetadata\Attributes\ParentAttribute;
use Apie\StorageMetadataBuilder\Lists\ReflectionPropertyList;
use Apie\TypeConverter\ReflectionTypeFactory;
use Nette\PhpGenerator\ClassType;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use ReflectionProperty;

final class GeneratedCodeContext
{
    private const INTERFACE_LINKS = [
        UploadedFileInterface::class => StoredFile::class,
    ];

    private array $visited = [];

    private array $visitedTables = [];

    private ?ReflectionClass $currentObject;

    public function __construct(
        public readonly GeneratedCode $generatedCode,
        public readonly ?BoundedContextEntityTuple $boundedContextEntityTuple
    ) {
        $this->currentObject = $boundedContextEntityTuple?->resourceClass;
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

    public function findIndexProperty(string $tableName): ?string
    {
        $classType = $this->generatedCode->generatedCodeHashmap[$tableName] ?? null;
        if (!$classType) {
            return null;
        }
        // TODO promoted constructor arguments? In general indexes are not set in the constructor.
        foreach ($classType->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if ($attribute->getName() === OrderAttribute::class) {
                    return $property->getName();
                }
            }
        }

        return null;
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
            $object = $this->currentObject;
            if (isset(self::INTERFACE_LINKS[$object->name])) {
                $object = new ReflectionClass(self::INTERFACE_LINKS[$object->name]);
            }
            foreach (ReflectionPropertyList::createFromClass($object) as $property) {
                if ($property->isStatic() || $this->isBlacklisted($property)) {
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

    private function isBlacklisted(ReflectionProperty $property): bool
    {
        if ($property->getDeclaringClass()->name === StoredFile::class) {
            return in_array($property->name, ['internalFile', 'removeOnDestruct', 'resource', 'content', 'movedPath']);
        }
        return false;
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
