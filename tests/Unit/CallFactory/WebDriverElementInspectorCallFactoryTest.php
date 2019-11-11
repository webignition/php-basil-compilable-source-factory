<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementInspectorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class WebDriverElementInspectorCallFactoryTest extends AbstractTestCase
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
        CodeBlockInterface $expectedBlock
    ) {
        $statement = $this->factory->createGetValueCall($collectionPlaceholder);

        $this->assertEquals($expectedBlock, $statement);
    }

    public function createGetValueCallDataProvider(): array
    {
        return [
            'default' => [
                'collectionPlaceholder' => new VariablePlaceholder('COLLECTION'),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ INSPECTOR }}->getValue({{ COLLECTION }})',
                        (new Metadata())
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                            ]))
                            ->withVariableExports(VariablePlaceholderCollection::createCollection([
                                'COLLECTION',
                            ]))
                    )
                ]),
            ],
        ];
    }
}
