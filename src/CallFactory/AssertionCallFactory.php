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
