<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementInspectorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\StatementListInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class WebDriverElementMutatorCallFactoryTest extends \PHPUnit\Framework\TestCase
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
        array $expectedStatements,
        MetadataInterface $expectedMetadata
    ) {
        $statementList = $this->factory->createSetValueCall($collectionPlaceholder, $valuePlaceholder);

        $this->assertInstanceOf(StatementListInterface::class, $statementList);
        $this->assertEquals($expectedStatements, $statementList->getStatements());
        $this->assertEquals($expectedMetadata, $statementList->getMetadata());
    }

    public function createSetValueCallDataProvider(): array
    {
        return [
            'default' => [
                'collectionPlaceholder' => new VariablePlaceholder('COLLECTION'),
                'valuePlaceholder' => new VariablePlaceholder('VALUE'),
                'expectedStatements' => [
                    '{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
        ];
    }
}
