<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestCodeGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;

class AssertionCallFactoryTest extends AbstractTestCase
{
    /**
     * @var AssertionCallFactory
     */
    private $factory;

    /**
     * @var TestCodeGenerator
     */
    private $testCodeGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = AssertionCallFactory::createFactory();
        $this->testCodeGenerator = TestCodeGenerator::create();
    }

    /**
     * @dataProvider createValueExistenceAssertionCallDataProvider
     */
    public function testCreateValueExistenceAssertionCall(
        string $examinedValue,
        VariablePlaceholder $variablePlaceholder,
        string $assertionMethod,
        string $failureMessage,
        string $expectedRenderedSource,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->factory->createValueExistenceAssertionCall(
            $variablePlaceholder,
            $assertionMethod,
            $failureMessage
        );

        $this->assertEquals($expectedRenderedSource, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());

        $testBlock = new CodeBlock([
            new AssignmentStatement($variablePlaceholder, new LiteralExpression($examinedValue)),
            new Statement($source)
        ]);

        $code = $this->testCodeGenerator->createPhpUnitTestForBlock($testBlock, [
            VariableNames::EXAMINED_VALUE => '$examinedValue',
            VariableNames::PHPUNIT_TEST_CASE => '$this',
        ]);

        eval($code);
    }

    public function createValueExistenceAssertionCallDataProvider(): array
    {
        $examinedValuePlaceholder = VariablePlaceholder::createExport(VariableNames::EXAMINED_VALUE);

        $expectedMetadata = new Metadata([
            Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                VariableNames::PHPUNIT_TEST_CASE,
            ]),
            Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                VariableNames::EXAMINED_VALUE,
            ]),
        ]);

        return [
            'assert true' => [
                'examinedValue' => 'true',
                'variablePlaceholder' => $examinedValuePlaceholder,
                'assertionMethod' => AssertionCallFactory::ASSERT_TRUE_METHOD,
                'failureMessage' => 'true is not true',
                'expectedRenderedSource' => '{{ PHPUNIT }}->assertTrue({{ EXAMINED }}, \'true is not true\')',
                'expectedMetadata' => $expectedMetadata,
            ],
            'assert false' => [
                'examinedValue' => 'false',
                'variablePlaceholder' => $examinedValuePlaceholder,
                'assertionMethod' => AssertionCallFactory::ASSERT_FALSE_METHOD,
                'failureMessage' => 'false is not false',
                'expectedRenderedSource' => '{{ PHPUNIT }}->assertFalse({{ EXAMINED }}, \'false is not false\')',
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
