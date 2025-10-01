<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Core\ValueObjects\Decimal;
use Apie\StorageMetadata\Attributes\DecimalPropertyAttribute;
use Apie\StorageMetadataBuilder\Interfaces\RunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;
use Apie\TypeConverter\Utils\ReflectionTypeUtil;

class DecimalCodeGenerator implements RunGeneratedCodeContextInterface
{
    public function run(GeneratedCodeContext $generatedCodeContext): void
    {
        $property = $generatedCodeContext->getCurrentProperty();
        $propertyType = $property?->getType();
        
        $table = $generatedCodeContext->getCurrentTable();
        if ($property === null || $table === null || $propertyType === null) {
            return;
        }
        $type = ReflectionTypeUtil::toClass($propertyType);
        if ($type === null || !$type->isSubclassOf(Decimal::class)) {
            return;
        }
        $propertyName = 'apie_'
            . str_replace('-', '_', (string) KebabCaseSlug::fromClass($property->getDeclaringClass()))
            . '_'
            . str_replace('-', '_', (string) KebabCaseSlug::fromClass($property));
    
        $declaredProperty = $table->addProperty($propertyName)->setType('string')->setNullable($property->getType()->allowsNull());
        $declaredProperty->addAttribute(
            DecimalPropertyAttribute::class,
            [
                $property->name,
                $property->getDeclaringClass()->name,
                $type->getMethod('getNumberOfDecimals')->invoke(null)
            ]
        );
    }
}
