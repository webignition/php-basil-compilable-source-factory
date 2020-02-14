<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;

class AssertionCallFactory
{
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';
    public const ASSERT_EQUALS_METHOD = 'assertEquals';
    public const ASSERT_NOT_EQUALS_METHOD = 'assertNotEquals';
    public const ASSERT_STRING_CONTAINS_STRING_METHOD = 'assertStringContainsString';
    public const ASSERT_STRING_NOT_CONTAINS_STRING_METHOD = 'assertStringNotContainsString';
    public const ASSERT_MATCHES_METHOD = 'assertRegExp';

    private $methodsWithStringArguments = [
        self::ASSERT_STRING_CONTAINS_STRING_METHOD,
        self::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
    ];

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
        VariablePlaceholder $expectedValuePlaceholder,
        VariablePlaceholder $actualValuePlaceholder,
        string $assertionMethod
    ): ObjectMethodInvocation {
        if (in_array($assertionMethod, $this->methodsWithStringArguments)) {
            $expectedValuePlaceholder = VariablePlaceholder::createExport(
                $expectedValuePlaceholder->getName(),
                'string'
            );

            $actualValuePlaceholder = VariablePlaceholder::createExport(
                $actualValuePlaceholder->getName(),
                'string'
            );
        }

        return new ObjectMethodInvocation(
            VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
            $assertionMethod,
            [
                $expectedValuePlaceholder,
                $actualValuePlaceholder,
            ]
        );
    }

    public function createValueExistenceAssertionCall(
        VariablePlaceholder $variablePlaceholder,
        string $assertionMethod,
        string $failureMessage
    ): ObjectMethodInvocation {
        return new ObjectMethodInvocation(
            VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
            $assertionMethod,
            [
                $variablePlaceholder,
                new LiteralExpression(
                    '\'' . $this->singleQuotedStringEscaper->escape($failureMessage) . '\''
                )
            ]
        );
    }
}
