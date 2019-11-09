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
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
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
     * @dataProvider handleDataProvider
     */
    public function testHandle(
        ValueInterface $model,
        SourceInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($model);

        $this->assertInstanceOf(SourceInterface::class, $source);

        if ($source instanceof SourceInterface) {
            $this->assertSourceContentEquals($expectedContent, $source);
            $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
        }
    }

    public function handleDataProvider(): array
    {
        return [
            'element value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(new DomIdentifier('.selector')),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => Block::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ E }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ E }} = {{ INSPECTOR }}->getValue({{ E }})',
                ]),
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
                        'E',
                    ])),
            ],
            'element value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withParentIdentifier(new DomIdentifier('.parent'))
                    ),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => Block::fromContent([
                    '{{ HAS }} = '
                        . '{{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ E }} = '
                        . '{{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ E }} = {{ INSPECTOR }}->getValue({{ E }})',
                ]),
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
                        'E',
                    ])),
            ],
            'attribute value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('attribute_name')
                    ),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => Block::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ E }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ E }} = {{ E }}->getAttribute(\'attribute_name\')',
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
                        'E',
                    ])),
            ],
            'attribute value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))
                            ->withAttributeName('attribute_name')
                            ->withParentIdentifier(new DomIdentifier('.parent'))
                    ),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => Block::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}'
                    . '->hasOne(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ E }} = {{ NAVIGATOR }}'
                    . '->findOne(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ E }} = {{ E }}->getAttribute(\'attribute_name\')',
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
                        'E',
                    ])),
            ],
        ];
    }
}
