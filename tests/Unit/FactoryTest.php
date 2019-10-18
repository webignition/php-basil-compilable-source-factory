<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\Factory;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\CreateFromValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\UnhandledValueDataProviderTrait;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    use CreateFromValueDataProviderTrait;
    use UnhandledValueDataProviderTrait;

    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Factory::createFactory();
    }

    /**
     * @dataProvider createFromValueDataProvider
     */
    public function testCreateSourceSuccess(
        object $model,
        array $expectedStatements,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->factory->createSource($model);

        $this->assertInstanceOf(SourceInterface::class, $source);
        $this->assertEquals($expectedStatements, $source->getStatements());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @dataProvider unhandledValueDataProvider
     * @dataProvider unhandledModelDataProvider
     */
    public function testCreateSourceThrowsNonTranspilableModelException(object $model)
    {
        $this->expectException(NonTranspilableModelException::class);

        $this->assertFalse($this->factory->createSource($model));
    }

    public function unhandledModelDataProvider(): array
    {
        return [
            'stdClass' => [
                'model' => new \stdClass(),
            ],
        ];
    }
}
