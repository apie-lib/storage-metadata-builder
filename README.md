<img src="https://raw.githubusercontent.com/apie-lib/apie-lib-monorepo/main/docs/apie-logo.svg" width="100px" align="left" />
<h1>storage-metadata-builder</h1>






 [![Latest Stable Version](https://poser.pugx.org/apie/storage-metadata-builder/v)](https://packagist.org/packages/apie/storage-metadata-builder) [![Total Downloads](https://poser.pugx.org/apie/storage-metadata-builder/downloads)](https://packagist.org/packages/apie/storage-metadata-builder) [![Latest Unstable Version](https://poser.pugx.org/apie/storage-metadata-builder/v/unstable)](https://packagist.org/packages/apie/storage-metadata-builder) [![License](https://poser.pugx.org/apie/storage-metadata-builder/license)](https://packagist.org/packages/apie/storage-metadata-builder) [![PHP Composer](https://apie-lib.github.io/projectCoverage/coverage-storage-metadata-builder.svg)](https://apie-lib.github.io/projectCoverage/storage-metadata-builder/index.html)  

[![PHP Composer](https://github.com/apie-lib/storage-metadata-builder/actions/workflows/php.yml/badge.svg?event=push)](https://github.com/apie-lib/storage-metadata-builder/actions/workflows/php.yml)

This package is part of the [Apie](https://github.com/apie-lib) library.
The code is maintained in a monorepo, so PR's need to be sent to the [monorepo](https://github.com/apie-lib/apie-lib-monorepo/pulls)

## Documentation
This package is a ORM agnostic package that helps/creates POPO (plain old php objects) from an Apie domain object that can be used with an ORM. The only library using it now is [apie/doctrine-entity-converter](https://github.com/apie-lib/doctrine-entity-converter) that converts Apie domain objects to Doctrine entities.

### Usage
You need a BoundedContextHashmap instance for all resources in all bounded contexts. Then you can easily create a builder like this:
```php
use Apie\StorageMetadataBuilder\ChainedBootGeneratedCode;
use Apie\StorageMetadataBuilder\ChainedGeneratedCodeContext;
use Apie\StorageMetadataBuilder\ChainedPostGeneratedCodeContext;
use Apie\StorageMetadataBuilder\StorageMetadataBuilder;

$instance = new StorageMetadataBuilder(
    $boundedContextHashmap,
    new ChainedBootGeneratedCode(/* list of class instances that implement BootGeneratedCodeInterface */),
    new ChainedGeneratedCodeContext(/* list of class instances that implement RunGeneratedCodeContextInterface */),
    new ChainedPostGeneratedCodeContext(/* list of class instances that implement PostRunGeneratedCodeContextInterface */)
);
$code = $instance->generateCode();
// this property contains an array with filename => generated php files
$code->generatedCodeHashmap
```
It uses nette\php-generator ClassType to create classes. Creating actual files is not part of this package.

The actual conversion of a domain object to a storage DTO is done with [apie/storage-metadata](https://github.com/apie-lib/storage-metadata)

### Interfaces of code generators
There are 3 interfaces:
- BootGeneratedCodeInterface: do these first to create a GeneratedCodeContext mediator object
- RunGeneratedCodeContextInterface: adds new classes with help of ClassTypeFactory
- PostRunGeneratedCodeContextInterface: do aggregate actions, like link the index table to all generated tables or add ORM specific attributes.

### ClassTypeFactory
This factory class is made to make ClassType instances with common interfaces and properties etc.

- ClassTypeFactory::createPrimitiveTable(string $tableName, ReflectionType $primitiveTypehint): create a storage table to store a primitive data field (string, bool, int, float, array)
- public static function createStorageTable(string $tableName, ReflectionClass $referencedObject): create a storage table to store a class with properties, for example entities, DTO's and composite value objects.


### Interfaces of generated classes
The classes being generated could have these interfaces:

- HasIndexInterface: the storage table is used for search indexes
- MixedStorageInterface: the storage table is used for storing mixed property fields
- RootObjectInterface: the storage table is the root table of a domain object resource and contains references to index tables etc.
