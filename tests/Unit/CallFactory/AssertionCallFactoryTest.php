<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class AssertionCallFactoryTest extends AbstractTestCase
{
    /**
     * @var AssertionCallFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = AssertionCallFactory::createFactory();
    }

    /**
     * @dataProvider createValueComparisonAssertionCallDataProvider
     */
    public function testCreateValueComparisonAssertionCall(
        SourceInterface $expectedValueAssignment,
        SourceInterface $actualValueAssignment,
        VariablePlaceholder $expectedValuePlaceholder,
        VariablePlaceholder $actualValuePlaceholder,
        string $assertionTemplate,
        array $expectedStatements,
        MetadataInterface $expectedMetadata
    ) {
        $statementList = $this->factory->createValueComparisonAssertionCall(
            $expectedValueAssignment,
            $actualValueAssignment,
            $expectedValuePlaceholder,
            $actualValuePlaceholder,
            $assertionTemplate
        );

        $this->assertEquals($expectedStatements, $statementList->getStatements());
        $this->assertMetadataEquals($expectedMetadata, $statementList->getMetadata());
    }

    public function createValueComparisonAssertionCallDataProvider(): array
    {
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        $expectedValueAssignment = new StatementList([
            new Statement(
                $expectedValuePlaceholder . ' = "expected value"',
                (new Metadata())
                    ->withVariableExports(new VariablePlaceholderCollection([$expectedValuePlaceholder]))
            )
        ]);

        $actualValueAssignment = new StatementList([
            new Statement(
                $examinedValuePlaceholder . ' = "actual value"',
                (new Metadata())
                    ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
            )
        ]);

        return [
            'assert equals' => [
                'expectedValueAssignment' => $expectedValueAssignment,
                'actualValueAssignment' => $actualValueAssignment,
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_EQUALS_TEMPLATE,
                'expectedStatements' => array_merge(
                    $expectedValueAssignment->getStatements(),
                    $actualValueAssignment->getStatements(),
                    [
                        '{{ PHPUNIT_TEST_CASE }}->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                    ]
                ),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $expectedValuePlaceholder,
                        $examinedValuePlaceholder,
                    ])),
            ],
            'assert not equals' => [
                'expectedValueAssignment' => $expectedValueAssignment,
                'actualValueAssignment' => $actualValueAssignment,
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_NOT_EQUALS_TEMPLATE,
                'expectedStatements' => array_merge(
                    $expectedValueAssignment->getStatements(),
                    $actualValueAssignment->getStatements(),
                    [
                        '{{ PHPUNIT_TEST_CASE }}->assertNotEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                    ]
                ),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $expectedValuePlaceholder,
                        $examinedValuePlaceholder,
                    ])),
            ],
            'assert string contains string' => [
                'expectedValueAssignment' => $expectedValueAssignment,
                'actualValueAssignment' => $actualValueAssignment,
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_STRING_CONTAINS_STRING_TEMPLATE,
                'expectedStatements' => array_merge(
                    $expectedValueAssignment->getStatements(),
                    $actualValueAssignment->getStatements(),
                    [
                        '{{ PHPUNIT_TEST_CASE }}'
                        .'->assertStringContainsString((string) {{ EXPECTED_VALUE }}, (string) {{ EXAMINED_VALUE }})',
                    ]
                ),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $expectedValuePlaceholder,
                        $examinedValuePlaceholder,
                    ])),
            ],
            'assert string not contains string' => [
                'expectedValueAssignment' => $expectedValueAssignment,
                'actualValueAssignment' => $actualValueAssignment,
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE,
                'expectedStatements' => array_merge(
                    $expectedValueAssignment->getStatements(),
                    $actualValueAssignment->getStatements(),
                    [
                        '{{ PHPUNIT_TEST_CASE }}'.
                        '->assertStringNotContainsString((string) {{ EXPECTED_VALUE }}, (string) {{ EXAMINED_VALUE }})',
                    ]
                ),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $expectedValuePlaceholder,
                        $examinedValuePlaceholder,
                    ])),
            ],
            'assert matches' => [
                'expectedValueAssignment' => $expectedValueAssignment,
                'actualValueAssignment' => $actualValueAssignment,
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_MATCHES_TEMPLATE,
                'expectedStatements' => array_merge(
                    $expectedValueAssignment->getStatements(),
                    $actualValueAssignment->getStatements(),
                    [
                        '{{ PHPUNIT_TEST_CASE }}'
                        .'->assertRegExp({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                    ]
                ),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $expectedValuePlaceholder,
                        $examinedValuePlaceholder,
                    ])),
            ],
        ];
    }

    /**
     * @dataProvider createValueExistenceAssertionCallDataProvider
     */
    public function testCreateValueExistenceAssertionCall(
        SourceInterface $assignment,
        VariablePlaceholder $variablePlaceholder,
        string $assertionTemplate,
        array $expectedStatements,
        MetadataInterface $expectedMetadata
    ) {
        $statementList = $this->factory->createValueExistenceAssertionCall(
            $assignment,
            $variablePlaceholder,
            $assertionTemplate
        );

        $this->assertEquals($expectedStatements, $statementList->getStatements());
        $this->assertMetadataEquals($expectedMetadata, $statementList->getMetadata());
    }

    public function createValueExistenceAssertionCallDataProvider(): array
    {
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        $assignment = new StatementList([
            new Statement(
                $examinedValuePlaceholder . ' = "value" !== null',
                (new Metadata())
                    ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
            )
        ]);

        return [
            'assert true' => [
                'assignment' => $assignment,
                'variablePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_TRUE_TEMPLATE,
                'expectedStatements' => array_merge(
                    $assignment->getStatements(),
                    [
                        '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ EXAMINED_VALUE }})',
                    ]
                ),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $examinedValuePlaceholder,
                    ])),
            ],
            'assert false' => [
                'assignment' => $assignment,
                'variablePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_FALSE_TEMPLATE,
                'expectedStatements' => array_merge(
                    $assignment->getStatements(),
                    [
                        '{{ PHPUNIT_TEST_CASE }}->assertFalse({{ EXAMINED_VALUE }})',
                    ]
                ),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(new VariablePlaceholderCollection([
                        new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                    ]))
                    ->withVariableExports(new VariablePlaceholderCollection([
                        $examinedValuePlaceholder,
                    ])),
            ],
        ];
    }
}
