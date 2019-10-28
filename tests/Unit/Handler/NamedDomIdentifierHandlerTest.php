<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\ValueInterface;
use webignition\DomElementLocator\ElementLocator;

class NamedDomIdentifierHandlerTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return NamedDomIdentifierHandler::createHandler();
    }

    /**
     * @dataProvider handlesDoesHandleDataProvider
     */
    public function testHandlesDoesHandle(NamedDomIdentifierInterface $model)
    {
        $this->assertTrue($this->handler->handles($model));
    }

    public function handlesDoesHandleDataProvider(): array
    {
        return [
            'element identifier' => [
                'model' => new NamedDomIdentifier(
                    new DomIdentifier('.selector'),
                    new VariablePlaceholder('ELEMENT_PLACEHOLDER')
                ),
            ],
            'attribute identifier' => [
                'model' => new NamedDomIdentifier(
                    (new DomIdentifier('.selector'))->withAttributeName('attribute_name'),
                    new VariablePlaceholder('ATTRIBUTE_PLACEHOLDER')
                ),
            ],
        ];
    }

    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(
        ValueInterface $model,
        array $expectedSerializedData,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->createSource($model);

        $this->assertJsonSerializedData($expectedSerializedData, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function createSourceDataProvider(): array
    {
        return [
            'element value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(new DomIdentifier('.selector')),
                    new VariablePlaceholder('ELEMENT_NO_PARENT')
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ HAS }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT_NO_PARENT }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT_NO_PARENT }} = '
                                . '{{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ ELEMENT_NO_PARENT }})',
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
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT_NO_PARENT',
                    ])),
            ],
            'element value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withParentIdentifier(new DomIdentifier('.parent'))
                    ),
                    new VariablePlaceholder('ELEMENT_HAS_PARENT')
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}'
                                . '->has(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT_HAS_PARENT }} = {{ DOM_CRAWLER_NAVIGATOR }}'
                                . '->find(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT_HAS_PARENT }} = '
                                . '{{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ ELEMENT_HAS_PARENT }})',
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
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT_HAS_PARENT',
                    ])),
            ],
            'attribute value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('attribute_name')
                    ),
                    new VariablePlaceholder('ELEMENT_NO_PARENT')
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}'
                                . '->hasOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT_NO_PARENT }} = {{ DOM_CRAWLER_NAVIGATOR }}'
                                . '->findOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT_NO_PARENT }} = '
                                . '{{ ELEMENT_NO_PARENT }}->getAttribute(\'attribute_name\')',
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
                        'ELEMENT_NO_PARENT',
                    ])),
            ],
            'attribute value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))
                            ->withAttributeName('attribute_name')
                            ->withParentIdentifier(new DomIdentifier('.parent'))
                    ),
                    new VariablePlaceholder('ELEMENT_HAS_PARENT')
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}'
                                .'->hasOne(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT_HAS_PARENT }} = {{ DOM_CRAWLER_NAVIGATOR }}'
                                .'->findOne(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ ELEMENT_HAS_PARENT }} = '
                                . '{{ ELEMENT_HAS_PARENT }}->getAttribute(\'attribute_name\')',
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
                        'ELEMENT_HAS_PARENT',
                    ])),
            ],
        ];
    }
}
