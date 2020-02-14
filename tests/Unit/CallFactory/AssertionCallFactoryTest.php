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
     * @dataProvider createValueComparisonAssertionCallDataProvider
     */
    public function testCreateValueComparisonAssertionCall(
        string $expectedValue,
        string $actualValue,
        VariablePlaceholder $expectedValuePlaceholder,
        VariablePlaceholder $actualValuePlaceholder,
        string $assertionMethod,
        string $expectedRenderedSource,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->factory->createValueComparisonAssertionCall(
            $expectedValuePlaceholder,
            $actualValuePlaceholder,
            $assertionMethod
        );

        $this->assertSame($expectedRenderedSource, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());

        $testBlock = new CodeBlock([
            new AssignmentStatement($expectedValuePlaceholder, new LiteralExpression($expectedValue)),
            new AssignmentStatement($actualValuePlaceholder, new LiteralExpression($actualValue)),
            new Statement($source)
        ]);

        $code = $this->testCodeGenerator->createPhpUnitTestForBlock($testBlock, [
            VariableNames::EXPECTED_VALUE => '$expectedValue',
            VariableNames::EXAMINED_VALUE => '$examinedValue',
            VariableNames::PHPUNIT_TEST_CASE => '$this',
        ]);

        eval($code);
    }

    public function createValueComparisonAssertionCallDataProvider(): array
    {
        $expectedValuePlaceholder = VariablePlaceholder::createExport(VariableNames::EXPECTED_VALUE);
        $examinedValuePlaceholder = VariablePlaceholder::createExport(VariableNames::EXAMINED_VALUE);

        $expectedMetadata = new Metadata([
            Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                VariableNames::PHPUNIT_TEST_CASE,
            ]),
            Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                VariableNames::EXPECTED_VALUE,
                VariableNames::EXAMINED_VALUE,
            ]),
        ]);

        $stringExpectedValuePlaceholder = VariablePlaceholder::createExport(VariableNames::EXPECTED_VALUE, 'string');
        $stringExaminedValuePlaceholder = VariablePlaceholder::createExport(VariableNames::EXAMINED_VALUE, 'string');

        $stringVariableExportCollection = VariablePlaceholderCollection::createExportCollection();
        $stringVariableExportCollection->add($stringExpectedValuePlaceholder);
        $stringVariableExportCollection->add($stringExaminedValuePlaceholder);

        $stringExpectedMetadata = new Metadata([
            Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                VariableNames::PHPUNIT_TEST_CASE,
            ]),
            Metadata::KEY_VARIABLE_EXPORTS => $stringVariableExportCollection,
        ]);

        return [
            'assert equals' => [
                'expectedValue' => '\'equals\'',
                'actualValue' => '\'equals\'',
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionMethod' => AssertionCallFactory::ASSERT_EQUALS_METHOD,
                'expectedRenderedSource' => '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                'expectedMetadata' => $expectedMetadata,
            ],
            'assert not equals' => [
                'expectedValue' => '\'equals\'',
                'actualValue' => '\'not equals\'',
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionMethod' => AssertionCallFactory::ASSERT_NOT_EQUALS_METHOD,
                'expectedRenderedSource' => '{{ PHPUNIT }}->assertNotEquals({{ EXPECTED }}, {{ EXAMINED }})',
                'expectedMetadata' => $expectedMetadata,
            ],
            'assert string contains string' => [
                'expectedValue' => '\'needle\'',
                'actualValue' => '\'haystack containing needle\'',
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionMethod' => AssertionCallFactory::ASSERT_STRING_CONTAINS_STRING_METHOD,
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->assertStringContainsString((string) {{ EXPECTED }}, (string) {{ EXAMINED }})',
                'expectedMetadata' => $stringExpectedMetadata,
            ],
            'assert string not contains string' => [
                'expectedValue' => '\'needle\'',
                'actualValue' => '\'haystack\'',
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionMethod' => AssertionCallFactory::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->assertStringNotContainsString((string) {{ EXPECTED }}, (string) {{ EXAMINED }})',
                'expectedMetadata' => $stringExpectedMetadata,
            ],
            'assert matches' => [
                'expectedValue' => '\'/pattern/\'',
                'actualValue' => '\'matches pattern\'',
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionMethod' => AssertionCallFactory::ASSERT_MATCHES_METHOD,
                'expectedRenderedSource' => '{{ PHPUNIT }}->assertRegExp({{ EXPECTED }}, {{ EXAMINED }})',
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
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
