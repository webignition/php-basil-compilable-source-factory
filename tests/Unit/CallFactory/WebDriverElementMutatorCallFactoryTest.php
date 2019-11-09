<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

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
        StatementInterface $expectedStatement
    ) {
        $statement = $this->factory->createSetValueCall($collectionPlaceholder, $valuePlaceholder);

        $this->assertInstanceOf(StatementInterface::class, $statement);
        $this->assertEquals($expectedStatement, $statement);
    }

    public function createSetValueCallDataProvider(): array
    {
        return [
            'default' => [
                'collectionPlaceholder' => new VariablePlaceholder('COLLECTION'),
                'valuePlaceholder' => new VariablePlaceholder('VALUE'),
                'expectedStatement' => new Statement(
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                    (new Metadata())
                        ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                            VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        ]))
                        ->withVariableExports(VariablePlaceholderCollection::createCollection([
                            'COLLECTION',
                            'VALUE',
                        ]))
                ),
            ],
        ];
    }
}
