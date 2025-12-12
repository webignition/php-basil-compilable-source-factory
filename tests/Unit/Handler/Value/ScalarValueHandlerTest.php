<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;

class ScalarValueHandlerTest extends AbstractResolvableTestCase
{
    private ScalarValueHandler $handler;

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
                'expectedMetadata' => Metadata::create(),
            ],
            'literal string value: integer' => [
                'value' => '"100"',
                'expectedRenderedSource' => '"100"',
                'expectedMetadata' => Metadata::create(),
            ],
            'environment parameter value' => [
                'value' => '$env.KEY',
                'expectedRenderedSource' => '{{ ENV }}[\'KEY\']',
                'expectedMetadata' => Metadata::create(
                    variableNames: [
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
            ],
            'browser property, size' => [
                'value' => '$browser.size',
                'expectedRenderedSource' => '(function () {' . "\n"
                    . '    $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n"
                    . "\n"
                    . '    return (string) ($webDriverDimension->getWidth()) . \'x\' . '
                    . '(string) ($webDriverDimension->getHeight());' . "\n"
                    . '})()',
                'expectedMetadata' => Metadata::create(
                    variableNames: [
                        VariableNames::PANTHER_CLIENT,
                    ],
                ),
            ],
            'page property, url' => [
                'value' => '$page.url',
                'expectedRenderedSource' => '{{ CLIENT }}->getCurrentURL()',
                'expectedMetadata' => Metadata::create(
                    variableNames: [
                        VariableNames::PANTHER_CLIENT,
                    ],
                ),
            ],
            'page property, title' => [
                'value' => '$page.title',
                'expectedRenderedSource' => '{{ CLIENT }}->getTitle()',
                'expectedMetadata' => Metadata::create(
                    variableNames: [
                        VariableNames::PANTHER_CLIENT,
                    ],
                ),
            ],
            'data parameter' => [
                'value' => '$data.key',
                'expectedRenderedSource' => '$key',
                'expectedMetadata' => Metadata::create(),
            ],
        ];
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
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
