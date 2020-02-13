<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;

class WebDriverElementMutatorCallFactoryTest extends AbstractTestCase
{
    /**
     * @var WebDriverElementMutatorCallFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = WebDriverElementMutatorCallFactory::createFactory();
    }

    /**
     * @dataProvider createSetValueCallDataProvider
     */
    public function testCreateSetValueCall(
        VariablePlaceholder $collectionPlaceholder,
        VariablePlaceholder $valuePlaceholder,
        string $expectedRenderedSource,
        MetadataInterface $expectedMetadata
    ) {
        $expression = $this->factory->createSetValueCall($collectionPlaceholder, $valuePlaceholder);

        $this->assertSame($expectedRenderedSource, $expression->render());
        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

    public function createSetValueCallDataProvider(): array
    {
        return [
            'default' => [
                'collectionPlaceholder' => VariablePlaceholder::createExport('COLLECTION'),
                'valuePlaceholder' => VariablePlaceholder::createExport('VALUE'),
                'expectedRenderedSource' => '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                       'COLLECTION',
                       'VALUE',
                    ]),
                ])
            ],
        ];
    }
}
