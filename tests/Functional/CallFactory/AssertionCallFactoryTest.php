<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
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
        string $expectedValue,
        string $actualValue,
        string $assertionTemplate
    ) {
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        $expectedValueCall = (new StatementList())
            ->withStatements([$expectedValuePlaceholder . ' = "' . $expectedValue . '"'])
            ->withMetadata(
                (new Metadata())
                    ->withVariableExports(new VariablePlaceholderCollection([$expectedValuePlaceholder]))
            );

        $actualValueCall = (new StatementList())
            ->withStatements([$examinedValuePlaceholder . ' = "' . $actualValue . '"'])
            ->withMetadata(
                (new Metadata())
                    ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
            );

        $statementList = $this->factory->createValueComparisonAssertionCall(
            $expectedValueCall,
            $actualValueCall,
            $expectedValuePlaceholder,
            $examinedValuePlaceholder,
            $assertionTemplate
        );

        $variableIdentifiers = [
            VariableNames::EXPECTED_VALUE => '$expectedValue',
            VariableNames::EXAMINED_VALUE => '$examinedValue',
            VariableNames::PHPUNIT_TEST_CASE => '$this',
        ];

        $executableCall = $this->executableCallFactory->create($statementList, $variableIdentifiers);

        eval($executableCall);
    }

    public function createValueComparisonAssertionCallDataProvider(): array
    {
        return [
            'assert equals' => [
                'expectedValueCall' => 'value',
                'actualValueCall' => 'value',
                'assertionTemplate' => AssertionCallFactory::ASSERT_EQUALS_TEMPLATE,
            ],
            'assert not equals' => [
                'expectedValueCall' => 'value',
                'actualValueCall' => 'different value',
                'assertionTemplate' => AssertionCallFactory::ASSERT_NOT_EQUALS_TEMPLATE,
            ],
            'assert string contains string' => [
                'expectedValueCall' => 'substring',
                'actualValueCall' => 'string containing substring',
                'assertionTemplate' => AssertionCallFactory::ASSERT_STRING_CONTAINS_STRING_TEMPLATE,
            ],
            'assert string not contains string' => [
                'expectedValueCall' => 'substring',
                'actualValueCall' => 'string',
                'assertionTemplate' => AssertionCallFactory::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE,
            ],
            'assert matches' => [
                'expectedValueCall' => '/^foo/',
                'actualValueCall' => 'foo bar',
                'assertionTemplate' => AssertionCallFactory::ASSERT_MATCHES_TEMPLATE,
            ],
        ];
    }

    /**
     * @dataProvider createValueExistenceAssertionCallDataProvider
     */
    public function testCreateValueExistenceAssertionCall($examinedValue, string $assertionTemplate)
    {
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        $assignmentCall = (new StatementList())
            ->withStatements([$examinedValuePlaceholder . ' = ' . $examinedValue . ' !== null'])
            ->withMetadata(
                (new Metadata())
                    ->withVariableExports(new VariablePlaceholderCollection([$examinedValuePlaceholder]))
            );

        $statementList = $this->factory->createValueExistenceAssertionCall(
            $assignmentCall,
            $examinedValuePlaceholder,
            $assertionTemplate
        );

        $variableIdentifiers = [
            VariableNames::EXAMINED_VALUE => '$examinedValue',
            VariableNames::PHPUNIT_TEST_CASE => '$this',
        ];

        $executableCall = $this->executableCallFactory->create($statementList, $variableIdentifiers);

        eval($executableCall);
    }

    public function createValueExistenceAssertionCallDataProvider(): array
    {
        return [
            'assert true' => [
                'examinedValue' => '"value"',
                'assertionTemplate' => AssertionCallFactory::ASSERT_TRUE_TEMPLATE,
            ],
            'assert false' => [
                'examinedValue' => 'null',
                'assertionTemplate' => AssertionCallFactory::ASSERT_FALSE_TEMPLATE,
            ],
        ];
    }
}
