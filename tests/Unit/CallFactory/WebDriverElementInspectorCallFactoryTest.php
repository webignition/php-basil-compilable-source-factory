<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementInspectorCallFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class WebDriverElementInspectorCallFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WebDriverElementInspectorCallFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = WebDriverElementInspectorCallFactory::createFactory();
    }

    /**
     * @dataProvider createGetValueCallDataProvider
     */
    public function testCreateGetValueCall(
        VariablePlaceholder $collectionPlaceholder,
        array $expectedStatements,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->factory->createGetValueCall($collectionPlaceholder);

        $this->assertInstanceOf(SourceInterface::class, $source);
        $this->assertEquals($expectedStatements, $source->getStatements());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    public function createGetValueCallDataProvider(): array
    {
        return [
            'default' => [
                'collectionPlaceholder' => new VariablePlaceholder('COLLECTION'),
                'expectedStatements' => [
                    '{{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ COLLECTION }})',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                    ])),
            ],
        ];
    }
}
