<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class AssertionCallFactoryTest extends \PHPUnit\Framework\TestCase
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
        StatementListInterface $expectedValueCall,
        StatementListInterface $actualValueCall,
        VariablePlaceholder $expectedValuePlaceholder,
        VariablePlaceholder $actualValuePlaceholder,
        string $assertionTemplate,
        array $expectedStatements,
        MetadataInterface $expectedMetadata
    ) {
        $statementList = $this->factory->createValueComparisonAssertionCall(
            $expectedValueCall,
            $actualValueCall,
            $expectedValuePlaceholder,
            $actualValuePlaceholder,
            $assertionTemplate
        );

        $this->assertInstanceOf(StatementListInterface::class, $statementList);
        $this->assertEquals($expectedStatements, $statementList->getStatements());
        $this->assertEquals($expectedMetadata, $statementList->getMetadata());
    }

    public function createValueComparisonAssertionCallDataProvider(): array
    {
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        $expectedValueCall = (new StatementList())
            ->withStatements([$expectedValuePlaceholder . ' = "expected value"'])
            ->withMetadata(
                (new Metadata())
                    ->withVariableExports(new VariablePlaceholderCollection([$expectedValuePlaceholder]))
            );

        $actualValueCall = (new StatementList())
            ->withStatements([$examinedValuePlaceholder . ' = "actual value"'])
            ->withMetadata(
                (new Metadata())
                    ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
            );

        return [
            'assert equals' => [
                'expectedValueCall' => $expectedValueCall,
                'actualValueCall' => $actualValueCall,
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_EQUALS_TEMPLATE,
                'expectedStatements' => array_merge(
                    $expectedValueCall->getStatements(),
                    $actualValueCall->getStatements(),
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
                'expectedValueCall' => $expectedValueCall,
                'actualValueCall' => $actualValueCall,
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_NOT_EQUALS_TEMPLATE,
                'expectedStatements' => array_merge(
                    $expectedValueCall->getStatements(),
                    $actualValueCall->getStatements(),
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
                'expectedValueCall' => $expectedValueCall,
                'actualValueCall' => $actualValueCall,
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_STRING_CONTAINS_STRING_TEMPLATE,
                'expectedStatements' => array_merge(
                    $expectedValueCall->getStatements(),
                    $actualValueCall->getStatements(),
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
                'expectedValueCall' => $expectedValueCall,
                'actualValueCall' => $actualValueCall,
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE,
                'expectedStatements' => array_merge(
                    $expectedValueCall->getStatements(),
                    $actualValueCall->getStatements(),
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
                'expectedValueCall' => $expectedValueCall,
                'actualValueCall' => $actualValueCall,
                'expectedValuePlaceholder' => $expectedValuePlaceholder,
                'actualValuePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_MATCHES_TEMPLATE,
                'expectedStatements' => array_merge(
                    $expectedValueCall->getStatements(),
                    $actualValueCall->getStatements(),
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
        StatementListInterface $assignmentCall,
        VariablePlaceholder $variablePlaceholder,
        string $assertionTemplate,
        array $expectedStatements,
        MetadataInterface $expectedMetadata
    ) {
        $statementList = $this->factory->createValueExistenceAssertionCall(
            $assignmentCall,
            $variablePlaceholder,
            $assertionTemplate
        );

        $this->assertInstanceOf(StatementListInterface::class, $statementList);
        $this->assertEquals($expectedStatements, $statementList->getStatements());
        $this->assertEquals($expectedMetadata, $statementList->getMetadata());
    }

    public function createValueExistenceAssertionCallDataProvider(): array
    {
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        $assignmentCall = (new StatementList())
            ->withStatements([$examinedValuePlaceholder . ' = "value" !== null'])
            ->withMetadata(
                (new Metadata())
                    ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
            );

        return [
            'assert true' => [
                'assignmentCall' => $assignmentCall,
                'variablePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_TRUE_TEMPLATE,
                'expectedStatements' => array_merge(
                    $assignmentCall->getStatements(),
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
                'assignmentCall' => $assignmentCall,
                'variablePlaceholder' => $examinedValuePlaceholder,
                'assertionTemplate' => AssertionCallFactory::ASSERT_FALSE_TEMPLATE,
                'expectedStatements' => array_merge(
                    $assignmentCall->getStatements(),
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
