<?php
/**
 * Apie\Fixtures\Entities\UserWithAddress
 */
class apie_resource__default_user_with_address implements \Apie\StorageMetadata\Interfaces\StorageDtoInterface, Apie\StorageMetadataBuilder\Interfaces\RootObjectInterface, Apie\StorageMetadataBuilder\Interfaces\HasIndexInterface
{
	use \Apie\StorageMetadataBuilder\Concerns\HasIndexes;

	#[Apie\StorageMetadata\Attributes\GetMethodAttribute('getId')]
	public ?string $id;

	#[Apie\StorageMetadata\Attributes\GetSearchIndexAttribute('getId')]
	public array $search_id;

	#[Apie\StorageMetadata\Attributes\GetSearchIndexAttribute('getAddress')]
	public array $search_address;

	#[Apie\StorageMetadata\Attributes\OneToOneAttribute('address', 'Apie\Fixtures\Entities\UserWithAddress')]
	public ?apie_mixed_data $apie_user_with_address_address;

	#[Apie\StorageMetadata\Attributes\PropertyAttribute('password', 'Apie\Fixtures\Entities\UserWithAddress')]
	public ?string $apie_user_with_address_password;

	#[Apie\StorageMetadata\Attributes\PropertyAttribute('id', 'Apie\Fixtures\Entities\UserWithAddress')]
	public ?string $apie_user_with_address_id;

	#[Apie\StorageMetadata\Attributes\OneToManyAttribute(null, 'apie_index_table')]
	public array $_indexes;


	public static function getClassReference(): ReflectionClass
	{
		return new \ReflectionClass(\Apie\Fixtures\Entities\UserWithAddress::class);
	}


	public function getIndexTable(): ReflectionClass
	{
		return new \ReflectionClass(apie_index_table::class);
	}
}
