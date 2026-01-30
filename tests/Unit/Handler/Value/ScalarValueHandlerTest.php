<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Value;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;

class ScalarValueHandlerTest extends AbstractResolvableTestCase
{
    private ScalarValueHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = ScalarValueHandler::createHandler();
    }

    #[DataProvider('createFromValueDataProvider')]
    public function testHandle(
        string $value,
        string $expectedRenderedSource,
        MetadataInterface $expectedMetadata
    ): void {
        $source = $this->handler->handle($value);

        $this->assertRenderResolvable($expectedRenderedSource, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createFromValueDataProvider(): array
    {
        return [
            'literal string value: string' => [
                'value' => '"value"',
                'expectedRenderedSource' => '"value"',
                'expectedMetadata' => new Metadata(),
            ],
            'literal string value: integer' => [
                'value' => '"100"',
                'expectedRenderedSource' => '"100"',
                'expectedMetadata' => new Metadata(),
            ],
            'environment parameter value' => [
                'value' => '$env.KEY',
                'expectedRenderedSource' => '{{ ENV }}[\'KEY\']',
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
            ],
            'browser property, size' => [
                'value' => '$browser.size',
                'expectedRenderedSource' => <<<'EOD'
            (function (): string {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
            
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })()
            EOD,
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'page property, url' => [
                'value' => '$page.url',
                'expectedRenderedSource' => '{{ CLIENT }}->getCurrentURL()',
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'page property, title' => [
                'value' => '$page.title',
                'expectedRenderedSource' => '{{ CLIENT }}->getTitle()',
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'data parameter' => [
                'value' => '$data.key',
                'expectedRenderedSource' => '$key',
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }

    #[DataProvider('handleThrowsExceptionDataProvider')]
    public function testHandleThrowsException(string $value, \Exception $expectedException): void
    {
        $this->expectExceptionObject($expectedException);

        $this->handler->handle($value);
    }

    /**
     * @return array<mixed>
     */
    public static function handleThrowsExceptionDataProvider(): array
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
