<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierExistenceHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class DomIdentifierExistenceHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DomIdentifierExistenceHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = DomIdentifierExistenceHandler::createHandler();
    }

    /**
     * @dataProvider createForElementDataProvider
     */
    public function testCreateForElement(
        ElementIdentifierInterface $identifier,
        string $assertionFailureMessage,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->createForElement($identifier, $assertionFailureMessage);

        $this->assertEquals($expectedRenderedContent, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    public function createForElementDataProvider(): array
    {
        return [
            'no parent' => [
                'identifier' => new ElementIdentifier('.selector'),
                'assertionFailureMessage' => 'false is not true',
                'expectedRenderedContent' =>
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'false is not true\'' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'HAS',
                    ]),
                ]),
            ],
            'has parent' => [
                'identifier' => (new ElementIdentifier('.selector'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'assertionFailureMessage' => 'false is not true',
                'expectedRenderedContent' =>
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector",' . "\n" .
                    '    "parent": {' . "\n" .
                    '        "locator": ".parent"' . "\n" .
                    '    }' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'false is not true\'' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'HAS',
                    ]),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider createForCollectionDataProvider
     */
    public function testCreateForCollection(
        ElementIdentifierInterface $identifier,
        string $assertionFailureMessage,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->createForCollection($identifier, $assertionFailureMessage);

        $this->assertEquals($expectedRenderedContent, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    public function createForCollectionDataProvider(): array
    {
        return [
            'no parent' => [
                'identifier' => new ElementIdentifier('.selector'),
                'assertionFailureMessage' => 'false is not true',
                'expectedRenderedContent' =>
                    '{{ HAS }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'false is not true\'' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'HAS',
                    ]),
                ]),
            ],
            'has parent' => [
                'identifier' => (new ElementIdentifier('.selector'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'assertionFailureMessage' => 'false is not true',
                'expectedRenderedContent' =>
                    '{{ HAS }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector",' . "\n" .
                    '    "parent": {' . "\n" .
                    '        "locator": ".parent"' . "\n" .
                    '    }' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'false is not true\'' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'HAS',
                    ]),
                ]),
            ],
        ];
    }
}
