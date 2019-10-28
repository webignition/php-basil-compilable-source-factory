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
        ];
    }
}
