<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\CreateFromValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;

class ScalarValueHandlerTest extends AbstractTestCase
{
    use CreateFromValueDataProviderTrait;

    /**
     * @var ScalarValueHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = ScalarValueHandler::createHandler();
    }

    /**
     * @dataProvider createFromValueDataProvider
     */
    public function testHandle(
        string $value,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($value);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(string $value, \Exception $expectedException)
    {
        $this->expectExceptionObject($expectedException);

        $this->handler->handle($value);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        return [
            'unhandled type' => [
                'value' => 'unquoted literal',
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_VALUE,
                    'unquoted literal'
                ),
            ],
            'unhandled page property' => [
                'value' => '$page.unhandled',
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_VALUE,
                    '$page.unhandled'
                ),
            ],
        ];
    }
}
