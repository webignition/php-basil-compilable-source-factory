<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementInspectorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementInterface;
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
        StatementInterface $expectedStatement
    ) {
        $statement = $this->factory->createGetValueCall($collectionPlaceholder);

        $this->assertInstanceOf(StatementInterface::class, $statement);
        $this->assertEquals($expectedStatement, $statement);
    }

    public function createGetValueCallDataProvider(): array
    {
        return [
            'default' => [
                'collectionPlaceholder' => new VariablePlaceholder('COLLECTION'),
                'expectedStatement' => new Statement(
                    '{{ INSPECTOR }}->getValue({{ COLLECTION }})',
                    (new Metadata())
                        ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                            VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        ]))
                        ->withVariableExports(VariablePlaceholderCollection::createCollection([
                            'COLLECTION',
                        ]))
                ),
            ],
        ];
    }
}
