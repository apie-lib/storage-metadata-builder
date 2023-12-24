<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\Core\Context\ApieContext;
use Apie\Core\Enums\ScalarType;
use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Core\Metadata\Fields\FieldInterface;
use Apie\Core\Metadata\MetadataFactory;
use Apie\StorageMetadata\Attributes\OneToOneAttribute;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadataBuilder\Interfaces\BootGeneratedCodeInterface;
use Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface;
use Apie\StorageMetadataBuilder\Interfaces\RunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCode;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;
use Apie\TypeConverter\ReflectionTypeFactory;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Parameter;

final class SimplePropertiesCodeGenerator implements RunGeneratedCodeContextInterface, BootGeneratedCodeInterface
{
    public function boot(GeneratedCode $generatedCode): void
    {
        $mixedData = new ClassType('apie_mixed_data');
        $mixedData->addImplement(MixedStorageInterface::class);
        $mixedData->addProperty('serializedString')->setType('string')->setNullable(true);
        $mixedData->addProperty('originalType')->setType('string')->setNullable(true);
        $mixedData->addProperty('unserializedObject')->setType('mixed');
        $mixedData->addMethod('__construct')->setParameters([new Parameter('input')])
            ->setBody(
                '$this->unserializedObject = $input;'
                . PHP_EOL
                . '$this->serializedString = serialize($input);'
                . PHP_EOL
                . '$this->originalType = get_debug_type($input);'
            );
        $mixedData->addMethod('toOriginalObject')
            ->setReturnType('mixed')
            ->setBody(
                'if (!$this->unserializedObject) {
    $this->unserializedObject = unserialize($this->serializedString);
    if (get_debug_type($this->unserializedObject) !== $this->originalType) {
        throw new \LogicException("Could not unserialize object again");
    }
}
return $this->unserializedObject;'
            );

        $generatedCode->generatedCodeHashmap['apie_mixed_data'] = $mixedData;
    }

    public function run(GeneratedCodeContext $generatedCodeContext): void
    {
        $property = $generatedCodeContext->getCurrentProperty();
        $table = $generatedCodeContext->getCurrentTable();
        if ($property === null || $table === null) {
            return;
        }
        $propertyName = 'apie_'
            . str_replace('-', '_', (string) KebabCaseSlug::fromClass($property->getDeclaringClass()))
            . '_'
            . str_replace('-', '_', (string) KebabCaseSlug::fromClass($property));
        $metadata = MetadataFactory::getMetadataStrategyForType(
            $property->getType() ?? ReflectionTypeFactory::createReflectionType('mixed')
        )->getResultMetadata(new ApieContext());
        $scalar = $metadata->toScalarType(true);
        if ($table->hasProperty($propertyName)) {
            return;
        }
        $nullable = (($metadata instanceof FieldInterface ? $metadata->allowsNull() : false) ? '?' : '');
        $nullable = '?';
        $declaredProperty = $table->addProperty($propertyName)
            ->setType($nullable . $scalar->value);
        switch ($scalar) {
            case ScalarType::ARRAY:
            case ScalarType::STDCLASS:
            case ScalarType::MIXED:
                $declaredProperty->setType($nullable . 'apie_mixed_data')->addAttribute(OneToOneAttribute::class, [$property->name, $property->getDeclaringClass()->name]);
                break;
            case ScalarType::NULL:
                $declaredProperty->setType(null)
                    ->setValue(null); // fallthrough
                // no break
            default:
                $declaredProperty->addAttribute(PropertyAttribute::class, [$property->name, $property->getDeclaringClass()->name]);
        }
            
    }
}
