<?php
class apie_resource__default_order implements Apie\StorageMetadata\Interfaces\StorageDtoInterface, Apie\StorageMetadataBuilder\Interfaces\RootObjectInterface
{
    #[Apie\StorageMetadata\Attributes\OneToManyAttribute('orderLines', 'apie_list__default_order__orderLines', 'Apie\Fixtures\Entities\Order')]
    public $apie_order_order_lines;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('id', 'Apie\Fixtures\Entities\Order')]
    public string $apie_order_id;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('orderStatus', 'Apie\Fixtures\Entities\Order')]
    public string $apie_order_order_status;


    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(\Apie\Fixtures\Entities\Order::class);
    }
}
