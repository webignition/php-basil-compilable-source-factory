<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class AssertionCallFactory
{
    const ASSERT_TRUE_TEMPLATE = '%s->assertTrue(%s)';
    const ASSERT_FALSE_TEMPLATE = '%s->assertFalse(%s)';
    const ASSERT_EQUALS_TEMPLATE = '%s->assertEquals(%s, %s)';
    const ASSERT_NOT_EQUALS_TEMPLATE = '%s->assertNotEquals(%s, %s)';
    const ASSERT_STRING_CONTAINS_STRING_TEMPLATE = '%s->assertStringContainsString((string) %s, (string) %s)';
    const ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE = '%s->assertStringNotContainsString((string) %s, (string) %s)';
    const ASSERT_MATCHES_TEMPLATE = '%s->assertRegExp(%s, %s)';

    private $phpUnitTestCasePlaceholder;
    private $variableDependencies;

    public function __construct()
    {
        $this->variableDependencies = new VariablePlaceholderCollection();
        $this->phpUnitTestCasePlaceholder = $this->variableDependencies->create(VariableNames::PHPUNIT_TEST_CASE);
    }

    public static function createFactory(): AssertionCallFactory
    {
        return new AssertionCallFactory();
    }

    public function createValueComparisonAssertionCall(
        StatementListInterface $expectedValueAssignment,
        StatementListInterface $actualValueAssignment,
        VariablePlaceholder $expectedValuePlaceholder,
        VariablePlaceholder $actualValuePlaceholder,
        string $assertionTemplate
    ): StatementListInterface {
        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies = $variableDependencies->withAdditionalItems([
            $this->phpUnitTestCasePlaceholder,
        ]);

        $metadata = (new Metadata())->withVariableDependencies($variableDependencies);

        $assertionStatement = sprintf(
            $assertionTemplate,
            $this->phpUnitTestCasePlaceholder,
            $expectedValuePlaceholder,
            $actualValuePlaceholder
        );

        return (new StatementList())
            ->withPredecessors([$expectedValueAssignment, $actualValueAssignment])
            ->withStatements([$assertionStatement])
            ->withMetadata($metadata);
    }

    public function createValueExistenceAssertionCall(
        StatementListInterface $assignmentCall,
        VariablePlaceholder $variablePlaceholder,
        string $assertionTemplate
    ): StatementListInterface {
        $assertionStatement = sprintf(
            $assertionTemplate,
            (string) $this->phpUnitTestCasePlaceholder,
            (string) $variablePlaceholder
        );

        $metadata = (new Metadata())
            ->withVariableDependencies($this->variableDependencies);

        return (new StatementList())
            ->withMetadata($metadata)
            ->withStatements([$assertionStatement])
            ->withPredecessors([$assignmentCall]);
    }
}
