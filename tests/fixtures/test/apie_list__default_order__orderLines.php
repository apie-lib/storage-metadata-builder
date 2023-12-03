<?php
class apie_list__default_order__orderLines implements Apie\StorageMetadata\Interfaces\StorageDtoInterface
{
	#[Apie\StorageMetadata\Attributes\ParentAttribute]
	public apie_resource__default_order $parent;

	#[Apie\StorageMetadata\Attributes\OrderAttribute]
	public int $order;

	#[Apie\StorageMetadata\Attributes\PropertyAttribute('id', 'Apie\Fixtures\Entities\OrderLine')]
	public string $apie_order_line_id;


	public static function getClassReference(): ReflectionClass
	{
		return new ReflectionClass(\Apie\Fixtures\Entities\OrderLine::class);
	}
}
