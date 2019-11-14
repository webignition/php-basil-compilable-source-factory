<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class AssertionCallFactory
{
    public const ASSERT_TRUE_TEMPLATE = '%s->assertTrue(%s)';
    public const ASSERT_FALSE_TEMPLATE = '%s->assertFalse(%s)';
    public const ASSERT_EQUALS_TEMPLATE = '%s->assertEquals(%s, %s)';
    public const ASSERT_NOT_EQUALS_TEMPLATE = '%s->assertNotEquals(%s, %s)';
    public const ASSERT_STRING_CONTAINS_STRING_TEMPLATE = '%s->assertStringContainsString((string) %s, (string) %s)';
    public const ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE =
        '%s->assertStringNotContainsString((string) %s, (string) %s)';
    public const ASSERT_MATCHES_TEMPLATE = '%s->assertRegExp(%s, %s)';

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
        CodeBlockInterface $expectedValueAssignment,
        CodeBlockInterface $actualValueAssignment,
        VariablePlaceholder $expectedValuePlaceholder,
        VariablePlaceholder $actualValuePlaceholder,
        string $assertionTemplate
    ): CodeBlockInterface {
        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies->add($this->phpUnitTestCasePlaceholder);

        $metadata = (new Metadata())->withVariableDependencies($variableDependencies);

        $assertionStatementContent = sprintf(
            $assertionTemplate,
            $this->phpUnitTestCasePlaceholder,
            $expectedValuePlaceholder,
            $actualValuePlaceholder
        );

        return new CodeBlock([
            $expectedValueAssignment,
            $actualValueAssignment,
            new Statement($assertionStatementContent, $metadata)
        ]);
    }

    public function createValueExistenceAssertionCall(
        CodeBlockInterface $assignment,
        VariablePlaceholder $variablePlaceholder,
        string $assertionTemplate
    ): CodeBlockInterface {
        $assertionStatementContent = sprintf(
            $assertionTemplate,
            (string) $this->phpUnitTestCasePlaceholder,
            (string) $variablePlaceholder
        );

        $metadata = (new Metadata())
            ->withVariableDependencies($this->variableDependencies);

        return new CodeBlock([
            $assignment,
            new Statement($assertionStatementContent, $metadata),
        ]);
    }
}
