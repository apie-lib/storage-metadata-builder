<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\Core\Identifiers\AutoIncrementInteger;
use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\Attributes\OneToOneAttribute;
use Apie\StorageMetadata\Interfaces\AutoIncrementTableInterface;
use Apie\StorageMetadataBuilder\Interfaces\RunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;
use Nette\PhpGenerator\ClassType;

class AddAutoIdGenerator implements RunGeneratedCodeContextInterface
{
    public function run(GeneratedCodeContext $generatedCodeContext): void
    {
        $property = $generatedCodeContext->getCurrentProperty();
        $currentTable = $generatedCodeContext->getCurrentTable();
        if ($property === null || $currentTable === null) {
            return;
        }
        $class = ConverterUtils::toReflectionClass($property);
        if ($class === null) {
            return;
        }
        if ($class->isSubclassOf(AutoIncrementInteger::class) || $class->name === AutoIncrementInteger::class) {
            $propertyName = 'apie_'
                . str_replace('-', '_', (string) KebabCaseSlug::fromClass($property->getDeclaringClass()))
                . '_'
                . str_replace('-', '_', (string) KebabCaseSlug::fromClass($property));
            $type = $generatedCodeContext->getPrefix('apie_increment_');
            $autoIncrementData = new ClassType($type);
            $autoIncrementData->addImplement(AutoIncrementTableInterface::class);
            $autoIncrementData->addMethod('getKey')
                ->setReturnType('?int')
                ->setBody('return $this->id;');

            $autoIncrementData->addProperty('id')->setType('?int');
            $generatedCodeContext->generatedCode->generatedCodeHashmap[$type] = $autoIncrementData;
            $currentTable->addProperty($propertyName)
                ->setType($type)
                ->addAttribute(OneToOneAttribute::class, [$property->name]);

        }
    }
}
