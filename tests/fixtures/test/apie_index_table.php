<?php
// @codingStandardsIgnoreStart
class apie_index_table
{
    #[Apie\StorageMetadata\Attributes\ManyToOneAttribute('_indexes')]
    public ?apie_resource__other_animal $ref_apie_resource__other_animal = null;

    #[Apie\StorageMetadata\Attributes\ManyToOneAttribute('_indexes')]
    public ?apie_resource__other_user_with_autoincrement_key $ref_apie_resource__other_user_with_autoincrement_key = null;

    #[Apie\StorageMetadata\Attributes\ManyToOneAttribute('_indexes')]
    public ?apie_resource__default_order $ref_apie_resource__default_order = null;

    #[Apie\StorageMetadata\Attributes\ManyToOneAttribute('_indexes')]
    public ?apie_resource__default_user_with_address $ref_apie_resource__default_user_with_address = null;


    public function __construct(
        public string $text,
        public int $priority,
        public float $idf = 0,
        public float $tf = 0,
    ) {
    }
}
