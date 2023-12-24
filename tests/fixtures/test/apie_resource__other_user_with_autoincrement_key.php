<?php
/**
 * Apie\Fixtures\Entities\UserWithAutoincrementKey
 */
class apie_resource__other_user_with_autoincrement_key implements \Apie\StorageMetadata\Interfaces\StorageDtoInterface, Apie\StorageMetadataBuilder\Interfaces\RootObjectInterface, Apie\StorageMetadataBuilder\Interfaces\HasIndexInterface
{
	use \Apie\StorageMetadataBuilder\Concerns\HasIndexes;

	#[Apie\StorageMetadata\Attributes\GetMethodAttribute('getId')]
	public ?int $id;

	#[Apie\StorageMetadata\Attributes\GetSearchIndexAttribute('getId')]
	public array $search_id;

	#[Apie\StorageMetadata\Attributes\GetSearchIndexAttribute('getPassword')]
	public array $search_password;

	#[Apie\StorageMetadata\Attributes\OneToOneAttribute('address', 'Apie\Fixtures\Entities\UserWithAutoincrementKey')]
	public ?apie_mixed_data $apie_user_with_autoincrement_key_address;

	#[Apie\StorageMetadata\Attributes\PropertyAttribute('password', 'Apie\Fixtures\Entities\UserWithAutoincrementKey')]
	public ?string $apie_user_with_autoincrement_key_password;

	#[Apie\StorageMetadata\Attributes\PropertyAttribute('id', 'Apie\Fixtures\Entities\UserWithAutoincrementKey')]
	public ?int $apie_user_with_autoincrement_key_id;

	#[Apie\StorageMetadata\Attributes\OneToManyAttribute(null, 'apie_index_table')]
	public array $_indexes;


	public static function getClassReference(): ReflectionClass
	{
		return new \ReflectionClass(\Apie\Fixtures\Entities\UserWithAutoincrementKey::class);
	}


	public function getIndexTable(): ReflectionClass
	{
		return new \ReflectionClass(apie_index_table::class);
	}
}
