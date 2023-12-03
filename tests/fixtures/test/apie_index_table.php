<?php
class apie_index_table
{
	#[Apie\StorageMetadata\Attributes\ManyToOneAttribute('_indexes')]
	public ?apie_resource__other_animal $ref_apie_resource__other_animal = null;

	#[Apie\StorageMetadata\Attributes\ManyToOneAttribute('_indexes')]
	public ?apie_resource__other_user_with_autoincrement_key $ref_apie_resource__other_user_with_autoincrement_key = null;

	#[Apie\StorageMetadata\Attributes\ManyToOneAttribute('_indexes')]
	public ?apie_resource__default_order $ref_apie_resource__default_order = null;


	public function __construct(
		public string $search,
		public int $priority,
		public ?float $idf = null,
		public ?float $tdf = null,
	) {
	}
}
