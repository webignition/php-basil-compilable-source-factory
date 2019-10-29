<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\LineList;
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
        SourceInterface $expectedValueAssignment,
        SourceInterface $actualValueAssignment,
        VariablePlaceholder $expectedValuePlaceholder,
        VariablePlaceholder $actualValuePlaceholder,
        string $assertionTemplate
    ): SourceInterface {
        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies = $variableDependencies->withAdditionalItems([
            $this->phpUnitTestCasePlaceholder,
        ]);

        $metadata = (new Metadata())->withVariableDependencies($variableDependencies);

        $assertionStatementContent = sprintf(
            $assertionTemplate,
            $this->phpUnitTestCasePlaceholder,
            $expectedValuePlaceholder,
            $actualValuePlaceholder
        );

        return new LineList([
            $expectedValueAssignment,
            $actualValueAssignment,
            new Statement($assertionStatementContent, $metadata)
        ]);
    }

    public function createValueExistenceAssertionCall(
        SourceInterface $assignment,
        VariablePlaceholder $variablePlaceholder,
        string $assertionTemplate
    ): SourceInterface {
        $assertionStatementContent = sprintf(
            $assertionTemplate,
            (string) $this->phpUnitTestCasePlaceholder,
            (string) $variablePlaceholder
        );

        $metadata = (new Metadata())
            ->withVariableDependencies($this->variableDependencies);

        return new LineList([
            $assignment,
            new Statement($assertionStatementContent, $metadata),
        ]);
    }
}
