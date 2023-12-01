<?php
class apie_resource__other_user_with_autoincrement_key implements Apie\StorageMetadata\Interfaces\StorageDtoInterface, Apie\StorageMetadataBuilder\Interfaces\RootObjectInterface
{
    #[Apie\StorageMetadata\Attributes\OneToOneAttribute('address', 'Apie\Fixtures\Entities\UserWithAutoincrementKey')]
    public apie_mixed_data $apie_user_with_autoincrement_key_address;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('password', 'Apie\Fixtures\Entities\UserWithAutoincrementKey')]
    public string $apie_user_with_autoincrement_key_password;

    #[Apie\StorageMetadata\Attributes\PropertyAttribute('id', 'Apie\Fixtures\Entities\UserWithAutoincrementKey')]
    public int $apie_user_with_autoincrement_key_id;


    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(\Apie\Fixtures\Entities\UserWithAutoincrementKey::class);
    }
}
