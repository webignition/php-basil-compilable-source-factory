<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Value;

use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;

class ScalarValueHandlerTest extends \PHPUnit\Framework\TestCase
{
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
        string $expectedRenderedSource,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($value);

        $this->assertTrue(true);

        $this->assertSame($expectedRenderedSource, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

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
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        'ENV',
                    ])
                ]),
            ],
            'browser property, size' => [
                'value' => '$browser.size',
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})()'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'page property, url' => [
                'value' => '$page.url',
                'expectedRenderedSource' => '{{ CLIENT }}->getCurrentURL()',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'page property, title' => [
                'value' => '$page.title',
                'expectedRenderedSource' => '{{ CLIENT }}->getTitle()',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
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
