<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\Core\FileStorage\FileStorageInterface;
use Apie\Core\FileStorage\StoredFile;
use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Core\Lists\IntegerHashmap;
use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\Attributes\GetMethodOrPropertyAttribute;
use Apie\StorageMetadata\Attributes\OneToOneAttribute;
use Apie\StorageMetadata\Attributes\StorageMappingAttribute;
use Apie\StorageMetadataBuilder\Factories\ClassTypeFactory;
use Apie\StorageMetadataBuilder\Interfaces\RunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;
use Nette\PhpGenerator\ClassType;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use ReflectionProperty;

final class FileTableGenerator implements RunGeneratedCodeContextInterface
{
    private const ALT_TYPES = [
        'fileSize' => '?int',
        'indexing' => '?' . IntegerHashmap::class,
        'storagePath' => 'string'
    ];

    private function createFileProperties(ReflectionClass $class): array
    {
        return [
            'storagePath' => ['getStoragePath', 'storagePath', $class->name, 'allowLargeStrings' => true],
            'clientMimeType' => ['getClientMediaType', 'clientMimeType', $class->name],
            'clientOriginalFile' => ['getClientFilename', 'clientOriginalFile', $class->name],
            'fileSize' => ['getSize', 'fileSize', $class->name],
            'serverMimeType' => ['getServerMimeType', 'serverMimeType', $class->name],
            'serverPath' => ['getServerPath', 'serverPath', $class->name],
            'indexing' => ['getIndexing', 'indexing', $class->name],
        ];
    }

    private function applyProperty(ClassType $table, ReflectionProperty $property, ReflectionClass $class): void
    {
        if (!$table->hasProperty('storage')) {
            $table->addProperty('storage')
                ->setType('?' . FileStorageInterface::class)
                ->addAttribute(StorageMappingAttribute::class);
        }

        $properties = $this->createFileProperties($class);
        $propertyName = $property->name;
        $type = self::ALT_TYPES[$propertyName] ?? '?string';
        if (isset($properties[$propertyName])) {
            $table->addProperty($propertyName)
                ->setType($type)
                ->addAttribute(
                    GetMethodOrPropertyAttribute::class,
                    $properties[$propertyName]
                );
        }
    }

    public function run(GeneratedCodeContext $generatedCodeContext): void
    {
        $property = $generatedCodeContext->getCurrentProperty();
        $currentTable = $generatedCodeContext->getCurrentTable();
        if ($property === null || $currentTable === null) {
            return;
        }
        $class = ConverterUtils::toReflectionClass($property);
        if (!$class) {
            if ($property->getDeclaringClass()->name === StoredFile::class) {
                $this->applyProperty($currentTable, $property, new ReflectionClass(StoredFile::class));
            }
            return;
        }
        if ($class->name === UploadedFileInterface::class) {
            $class = new ReflectionClass(StoredFile::class);
        }
        if (in_array(UploadedFileInterface::class, $class->getInterfaceNames())) {
            // TODO: is this still needed?
            $tableName = $generatedCodeContext->getPrefix('apie_resource_');
            $table = ClassTypeFactory::createStorageTable($tableName, new ReflectionClass(StoredFile::class));
            $table->addProperty('storage')
                ->setType('?' . FileStorageInterface::class)
                ->addAttribute(StorageMappingAttribute::class);
            $properties = $this->createFileProperties($class);
            foreach ($properties as $propertyName => $attributeArguments) {
                $type = self::ALT_TYPES[$propertyName] ?? '?string';
                $table->addProperty($propertyName)
                    ->setType($type)
                    ->addAttribute(
                        GetMethodOrPropertyAttribute::class,
                        $attributeArguments
                    );
            }
            $generatedCodeContext->generatedCode->generatedCodeHashmap[$tableName] = $table;
            //$generatedCodeContext->withCurrentObject($class)->iterateOverTable($table);
            $propertyName = 'apie_'
                . str_replace('-', '_', (string) KebabCaseSlug::fromClass($property->getDeclaringClass()))
                . '_'
                . str_replace('-', '_', (string) KebabCaseSlug::fromClass($property));
            $currentTable->addProperty($propertyName)
                ->setType('?' . $tableName)
                ->addAttribute(OneToOneAttribute::class, [$property->name, null, $property->getDeclaringClass()->name]);
        }
    }
}
