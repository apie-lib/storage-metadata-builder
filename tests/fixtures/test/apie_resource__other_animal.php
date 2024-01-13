<?php
/**
 * Apie\Fixtures\Entities\Polymorphic\Animal
 */
class apie_resource__other_animal implements Apie\StorageMetadata\Interfaces\StorageClassInstantiatorInterface, Apie\StorageMetadataBuilder\Interfaces\RootObjectInterface, Apie\StorageMetadataBuilder\Interfaces\HasIndexInterface
{
    use \Apie\StorageMetadataBuilder\Concerns\IsPolymorphicStorage;
    use \Apie\StorageMetadataBuilder\Concerns\HasIndexes;

    #[Apie\StorageMetadata\Attributes\DiscriminatorMappingAttribute]
    public array $discriminatorMapping;

    #[Apie\StorageMetadata\Attributes\GetMethodAttribute('getId')]
    public ?string $id;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('id', 'Apie\Fixtures\Entities\Polymorphic\Animal')]
    public ?string $apie_animal_id;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('poisonous', 'Apie\Fixtures\Entities\Polymorphic\Fish')]
    public ?bool $apie_fish_poisonous;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('starving', 'Apie\Fixtures\Entities\Polymorphic\Elephant')]
    public ?bool $apie_elephant_starving;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('hasMilk', 'Apie\Fixtures\Entities\Polymorphic\Cow')]
    public ?bool $apie_cow_has_milk;

    #[Apie\StorageMetadata\Attributes\OneToManyAttribute(null, 'apie_index_table')]
    public array $_indexes;


    public static function getClassReference(): ReflectionClass
    {
        return new \ReflectionClass(\Apie\Fixtures\Entities\Polymorphic\Animal::class);
    }


    public function getIndexTable(): ReflectionClass
    {
        return new \ReflectionClass(apie_index_table::class);
    }
}
