<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\VariablePlaceholder;

class AssertionMethodInvocationFactory
{
    private $singleQuotedStringEscaper;

    public function __construct(SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): AssertionMethodInvocationFactory
    {
        return new AssertionMethodInvocationFactory(
            SingleQuotedStringEscaper::create()
        );
    }

    /**
     * @param string $assertionMethod
     * @param array<ExpressionInterface> $arguments
     * @param string $failureMessage
     *
     * @return ObjectMethodInvocation
     */
    public function create(
        string $assertionMethod,
        array $arguments = [],
        string $failureMessage = ''
    ): ObjectMethodInvocation {
        if ('' !== $failureMessage) {
            $arguments[] = new LiteralExpression(
                '\'' . $this->singleQuotedStringEscaper->escape($failureMessage) . '\''
            );
        }

        return new ObjectMethodInvocation(
            VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
            $assertionMethod,
            $arguments,
            MethodInvocation::ARGUMENT_FORMAT_STACKED
        );
    }
}
