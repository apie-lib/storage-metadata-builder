<?php
/**
 * Apie\Fixtures\Entities\Order
 */
class apie_resource__default_order implements \Apie\StorageMetadata\Interfaces\StorageDtoInterface, Apie\StorageMetadataBuilder\Interfaces\RootObjectInterface, Apie\StorageMetadataBuilder\Interfaces\HasIndexInterface
{
	use \Apie\StorageMetadataBuilder\Concerns\HasIndexes;

	#[Apie\StorageMetadata\Attributes\GetMethodAttribute('getId')]
	public ?string $id;

	#[Apie\StorageMetadata\Attributes\GetSearchIndexAttribute('getId')]
	public array $search_id;

	#[Apie\StorageMetadata\Attributes\GetSearchIndexAttribute('getOrderStatus')]
	public array $search_orderStatus;

	#[Apie\StorageMetadata\Attributes\GetSearchIndexAttribute('getOrderLines')]
	public array $search_orderLines;

	#[Apie\StorageMetadata\Attributes\OneToManyAttribute('orderLines', 'apie_list__default_order__orderLines', 'Apie\Fixtures\Entities\Order')]
	public $apie_order_order_lines;

	#[Apie\StorageMetadata\Attributes\PropertyAttribute('id', 'Apie\Fixtures\Entities\Order')]
	public ?string $apie_order_id;

	#[Apie\StorageMetadata\Attributes\PropertyAttribute('orderStatus', 'Apie\Fixtures\Entities\Order')]
	public ?string $apie_order_order_status;

	#[Apie\StorageMetadata\Attributes\OneToManyAttribute(null, 'apie_index_table')]
	public array $_indexes;


	public static function getClassReference(): ReflectionClass
	{
		return new \ReflectionClass(\Apie\Fixtures\Entities\Order::class);
	}


	public function getIndexTable(): ReflectionClass
	{
		return new \ReflectionClass(apie_index_table::class);
	}
}
