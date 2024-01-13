<?php
/**
 * Apie\Fixtures\Entities\OrderLine
 */
class apie_list__default_order__order_lines implements \Apie\StorageMetadata\Interfaces\StorageDtoInterface
{
    #[Apie\StorageMetadata\Attributes\GetMethodAttribute('getId')]
    public ?string $id;

    #[Apie\StorageMetadata\Attributes\ParentAttribute]
    public apie_resource__default_order $parent;

    #[Apie\StorageMetadata\Attributes\OrderAttribute]
    public int $order;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('id', 'Apie\Fixtures\Entities\OrderLine')]
    public ?string $apie_order_line_id;


    public static function getClassReference(): ReflectionClass
    {
        return new \ReflectionClass(\Apie\Fixtures\Entities\OrderLine::class);
    }
}
