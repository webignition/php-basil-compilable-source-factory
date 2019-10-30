<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\TestHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;

class TestHandlerTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return TestHandler::createHandler();
    }

    public function testHandlesDoesHandle()
    {
        $this->assertTrue($this->handler->handles(new Test(
            'test name',
            new Configuration('chrome', 'http://example.com'),
            []
        )));
    }

    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(
        TestInterface $test,
        SourceInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->createSource($test);

        $this->assertInstanceOf(SourceInterface::class, $source);
        $this->assertSourceContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function createSourceDataProvider(): array
    {
        return [
            'empty test' => [
                'step' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    []
                ),
                'expectedContent' => new LineList([]),
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }
}
