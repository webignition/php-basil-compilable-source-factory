<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class AssertionCallFactory
{
    public const ASSERT_TRUE_TEMPLATE = '%s->assertTrue(%s, \'%s\')';
    public const ASSERT_FALSE_TEMPLATE = '%s->assertFalse(%s, \'%s\')';
    public const ASSERT_EQUALS_TEMPLATE = '%s->assertEquals(%s, %s)';
    public const ASSERT_NOT_EQUALS_TEMPLATE = '%s->assertNotEquals(%s, %s)';
    public const ASSERT_STRING_CONTAINS_STRING_TEMPLATE = '%s->assertStringContainsString((string) %s, (string) %s)';
    public const ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE =
        '%s->assertStringNotContainsString((string) %s, (string) %s)';
    public const ASSERT_MATCHES_TEMPLATE = '%s->assertRegExp(%s, %s)';

    private $singleQuotedStringEscaper;

    public function __construct(SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): AssertionCallFactory
    {
        return new AssertionCallFactory(
            SingleQuotedStringEscaper::create()
        );
    }

    public function createValueComparisonAssertionCall(
        CodeBlockInterface $expectedValueAssignment,
        CodeBlockInterface $actualValueAssignment,
        VariablePlaceholder $expectedValuePlaceholder,
        VariablePlaceholder $actualValuePlaceholder,
        string $assertionTemplate
    ): CodeBlockInterface {
        $variableDependencies = new VariablePlaceholderCollection();
        $phpUnitTestCasePlaceholder = $variableDependencies->create(VariableNames::PHPUNIT_TEST_CASE);

        $metadata = (new Metadata())->withVariableDependencies($variableDependencies);

        $assertionStatementContent = sprintf(
            $assertionTemplate,
            $phpUnitTestCasePlaceholder,
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
        string $assertionTemplate,
        string $failureMessage
    ): CodeBlockInterface {
        $variableDependencies = new VariablePlaceholderCollection();
        $phpUnitTestCasePlaceholder = $variableDependencies->create(VariableNames::PHPUNIT_TEST_CASE);

        $assertionStatementContent = sprintf(
            $assertionTemplate,
            (string) $phpUnitTestCasePlaceholder,
            (string) $variablePlaceholder,
            $this->singleQuotedStringEscaper->escape($failureMessage)
        );

        $metadata = (new Metadata())
            ->withVariableDependencies($variableDependencies);

        return new CodeBlock([
            $assignment,
            new Statement($assertionStatementContent, $metadata),
        ]);
    }
}
