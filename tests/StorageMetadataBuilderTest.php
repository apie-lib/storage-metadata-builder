<?php
namespace Apie\Tests\StorageMetadataBuilder;

use Apie\Fixtures\BoundedContextFactory;
use Apie\StorageMetadataBuilder\ChainedBootGeneratedCode;
use Apie\StorageMetadataBuilder\ChainedGeneratedCodeContext;
use Apie\StorageMetadataBuilder\ChainedPostGeneratedCodeContext;
use Apie\StorageMetadataBuilder\CodeGenerators\AddIndexesCodeGenerator;
use Apie\StorageMetadataBuilder\CodeGenerators\ItemListCodeGenerator;
use Apie\StorageMetadataBuilder\CodeGenerators\RootObjectCodeGenerator;
use Apie\StorageMetadataBuilder\CodeGenerators\SimplePropertiesCodeGenerator;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCode;
use Apie\StorageMetadataBuilder\StorageMetadataBuilder;
use PHPUnit\Framework\TestCase;

class StorageMetadataBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_create_storage_objects()
    {
        $simple = new SimplePropertiesCodeGenerator();
        $indexer = new AddIndexesCodeGenerator();
        $testItem = new StorageMetadataBuilder(
            BoundedContextFactory::createHashmapWithMultipleContexts(),
            new ChainedBootGeneratedCode(
                $simple,
                $indexer
            ),
            new ChainedGeneratedCodeContext(
                new ItemListCodeGenerator(),
                $simple,
                new RootObjectCodeGenerator()
            ),
            new ChainedPostGeneratedCodeContext(
                $indexer
            )
        );
        $generatedCode = $testItem->generateCode();
        $this->assertCorrectCode($generatedCode, __DIR__ . '/fixtures/test', true);
    }

    private function assertCorrectCode(GeneratedCode $code, string $fixturePath, bool $overwrite = false)
    {
        foreach ($code->generatedCodeHashmap as $name => $sourceCode) {
            $path = $fixturePath . DIRECTORY_SEPARATOR . $name . '.phpinc';
            if ($overwrite) {
                @mkdir(dirname($path), recursive: true);
                file_put_contents($path, "<?php\n// @codingStandardsIgnoreStart\n" . $sourceCode);
            }
            $this->assertEquals(
                file_get_contents($path),
                "<?php\n// @codingStandardsIgnoreStart\n" . $sourceCode
            );
        }
    }
}
