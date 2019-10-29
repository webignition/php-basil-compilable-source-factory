<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\DomElementLocator\ElementLocator;

class StepHandlerTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return StepHandler::createHandler();
    }

    public function testHandlesDoesHandle()
    {
        $this->assertTrue($this->handler->handles(new Step([], [])));
    }

    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(
        StepInterface $step,
        array $expectedSerializedData,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->createSource($step);

        $this->assertInstanceOf(SourceInterface::class, $source);
        $this->assertJsonSerializedData($expectedSerializedData, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function createSourceDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'empty step' => [
                'step' => new Step([], []),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [],
                ],
                'expectedMetadata' => new Metadata(),
            ],
            'one action' => [
                'step' => new Step(
                    [
                        $actionFactory->createFromActionString('click ".selector"'),
                    ],
                    []
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'comment',
                            'content' => 'click ".selector"',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ HAS }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT }}->click()',
                        ],
                        [
                            'type' => 'empty',
                            'content' => '',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT',
                    ])),
            ],
            'two action' => [
                'step' => new Step(
                    [
                        $actionFactory->createFromActionString('click ".selector"'),
                        $actionFactory->createFromActionString('wait 1'),
                    ],
                    []
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'comment',
                            'content' => 'click ".selector"',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ HAS }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT }}->click()',
                        ],
                        [
                            'type' => 'empty',
                            'content' => '',
                        ],
                        [
                            'type' => 'comment',
                            'content' => 'wait 1',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ DURATION }} = "1" ?? 0',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ DURATION }} = (int) {{ DURATION }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => 'usleep({{ DURATION }} * 1000)',
                        ],
                        [
                            'type' => 'empty',
                            'content' => '',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                        'HAS',
                        'ELEMENT',
                    ])),
            ],
            'one assertion' => [
                'step' => new Step(
                    [],
                    [
                        $assertionFactory->createFromAssertionString('$page.title is "value"'),
                    ]
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'comment',
                            'content' => '$page.title is "value"',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = "value" ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = {{ PANTHER_CLIENT }}->getTitle() ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                        [
                            'type' => 'empty',
                            'content' => '',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'EXAMINED_VALUE',
                        'EXPECTED_VALUE',
                    ])),
            ],
            'two assertions' => [
                'step' => new Step(
                    [],
                    [
                        $assertionFactory->createFromAssertionString('$page.title is "value"'),
                        $assertionFactory->createFromAssertionString('$page.url is "http://example.com"'),
                    ]
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'comment',
                            'content' => '$page.title is "value"',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = "value" ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = {{ PANTHER_CLIENT }}->getTitle() ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                        [
                            'type' => 'empty',
                            'content' => '',
                        ],
                        [
                            'type' => 'comment',
                            'content' => '$page.url is "http://example.com"',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = "http://example.com" ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = {{ PANTHER_CLIENT }}->getCurrentURL() ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                        [
                            'type' => 'empty',
                            'content' => '',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'EXAMINED_VALUE',
                        'EXPECTED_VALUE',
                    ])),
            ],
            'one action, one assertion' => [
                'step' => new Step(
                    [
                        $actionFactory->createFromActionString('click ".selector"'),
                    ],
                    [
                        $assertionFactory->createFromAssertionString('$page.title is "value"'),
                    ]
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'comment',
                            'content' => 'click ".selector"',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ HAS }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT }}->click()',
                        ],
                        [
                            'type' => 'empty',
                            'content' => '',
                        ],
                        [
                            'type' => 'comment',
                            'content' => '$page.title is "value"',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = "value" ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = {{ PANTHER_CLIENT }}->getTitle() ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                        [
                            'type' => 'empty',
                            'content' => '',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'ELEMENT',
                        'EXAMINED_VALUE',
                        'EXPECTED_VALUE',
                        'HAS',
                    ])),
            ],
        ];
    }
}
