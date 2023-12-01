<?php
class apie_resource__other_animal implements Apie\StorageMetadata\Interfaces\StorageClassInstantiatorInterface, Apie\StorageMetadataBuilder\Interfaces\RootObjectInterface
{
    use Apie\StorageMetadataBuilder\Concerns\IsPolymorphicStorage;

    #[Apie\StorageMetadata\Attributes\DiscriminatorMappingAttribute]
    public array $discriminatorMapping;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('id', 'Apie\Fixtures\Entities\Polymorphic\Animal')]
    public string $apie_animal_id;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('poisonous', 'Apie\Fixtures\Entities\Polymorphic\Fish')]
    public bool $apie_fish_poisonous;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('starving', 'Apie\Fixtures\Entities\Polymorphic\Elephant')]
    public bool $apie_elephant_starving;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('hasMilk', 'Apie\Fixtures\Entities\Polymorphic\Cow')]
    public bool $apie_cow_has_milk;


    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(\Apie\Fixtures\Entities\Polymorphic\Animal::class);
    }
}
