<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Value;

use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTest;
use webignition\BasilCompilableSourceFactory\VariableNames;

class ScalarValueHandlerTest extends AbstractResolvableTest
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

        $this->assertTrue(true);

        $this->assertRenderResolvable($expectedRenderedSource, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public function createFromValueDataProvider(): array
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
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        'ENV',
                    ])
                ]),
            ],
            'browser property, size' => [
                'value' => '$browser.size',
                'expectedRenderedSource' => '(function () {' . "\n" .
                    '    $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) ($webDriverDimension->getWidth()) . \'x\' . ' .
                    '(string) ($webDriverDimension->getHeight());' . "\n" .
                    '})()',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'page property, url' => [
                'value' => '$page.url',
                'expectedRenderedSource' => '{{ CLIENT }}->getCurrentURL()',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'page property, title' => [
                'value' => '$page.title',
                'expectedRenderedSource' => '{{ CLIENT }}->getTitle()',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'data parameter' => [
                'value' => '$data.key',
                'expectedRenderedSource' => '$key',
                'expectedMetadata' => new Metadata(),
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
