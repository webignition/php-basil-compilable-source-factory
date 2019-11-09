<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
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
     * @dataProvider handleDataProvider
     */
    public function testHandle(
        StepInterface $step,
        SourceInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($step);

        $this->assertInstanceOf(SourceInterface::class, $source);

        if ($source instanceof SourceInterface) {
            $this->assertSourceContentEquals($expectedContent, $source);
            $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
        }
    }

    public function handleDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'empty step' => [
                'step' => new Step([], []),
                'expectedContent' => new Block(),
                'expectedMetadata' => new Metadata(),
            ],
            'one action' => [
                'step' => new Step(
                    [
                        $actionFactory->createFromActionString('click ".selector"'),
                    ],
                    []
                ),
                'expectedContent' => Block::fromContent([
                    '//click ".selector"',
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ ELEMENT }}->click()',
                    '',
                ]),
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
                'expectedContent' => Block::fromContent([
                    '//click ".selector"',
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ ELEMENT }}->click()',
                    '',
                    '//wait 1',
                    '{{ DURATION }} = "1" ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                    '',
                ]),
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
                'expectedContent' => Block::fromContent([
                    '//$page.title is "value"',
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
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
                'expectedContent' => Block::fromContent([
                    '//$page.title is "value"',
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                    '//$page.url is "http://example.com"',
                    '{{ EXPECTED }} = "http://example.com" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ CLIENT }}->getCurrentURL() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
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
                'expectedContent' => Block::fromContent([
                    '//click ".selector"',
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ ELEMENT }}->click()',
                    '',
                    '//$page.title is "value"',
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                ]),
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
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        'HAS',
                    ])),
            ],
        ];
    }
}
