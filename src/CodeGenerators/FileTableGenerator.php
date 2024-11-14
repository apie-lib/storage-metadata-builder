<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\Core\FileStorage\FileStorageInterface;
use Apie\Core\FileStorage\StoredFile;
use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\Attributes\GetMethodOrPropertyAttribute;
use Apie\StorageMetadata\Attributes\OneToOneAttribute;
use Apie\StorageMetadata\Attributes\StorageMappingAttribute;
use Apie\StorageMetadataBuilder\Factories\ClassTypeFactory;
use Apie\StorageMetadataBuilder\Interfaces\RunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;

final class FileTableGenerator implements RunGeneratedCodeContextInterface
{
    public function run(GeneratedCodeContext $generatedCodeContext): void
    {
        $property = $generatedCodeContext->getCurrentProperty();
        $currentTable = $generatedCodeContext->getCurrentTable();
        if ($property === null || $currentTable === null) {
            return;
        }
        $class = ConverterUtils::toReflectionClass($property);
        if (!$class) {
            return;
        }
        if ($class->name === UploadedFileInterface::class) {
            $class = new ReflectionClass(StoredFile::class);
        }
        if (in_array(UploadedFileInterface::class, $class->getInterfaceNames())) {
            $tableName = $generatedCodeContext->getPrefix('apie_resource_');
            $table = ClassTypeFactory::createStorageTable($tableName, new ReflectionClass(StoredFile::class));
            $table->addProperty('storage')
                ->setType('?' . FileStorageInterface::class)
                ->addAttribute(StorageMappingAttribute::class);
            $properties = [
                'storagePath' => ['getStoragePath', 'storagePath', $class->name, 'allowLargeStrings' => true],
                'clientMimeType' => ['getClientMediaType', 'clientMimeType', $class->name],
                'clientOriginalFile' => ['getClientFilename', 'clientOriginalFile', $class->name],
                'fileSize' => ['getSize', 'fileSize', $class->name],
                'serverMimeType' => ['getServerMimeType', 'serverMimeType', $class->name],
                'serverPath' => ['getServerPath', 'serverPath', $class->name],
                'indexing' => ['getIndexing', 'indexing', $class->name],
            ];
            $types = [
                'fileSize' => '?int',
                'indexing' => 'array',
                'storagePath' => 'string'
            ];
            foreach ($properties as $propertyName => $attributeArguments) {
                $type = $types[$propertyName] ?? '?string';
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
