<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Json\AssertionMessage;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;

readonly class AssertionStatementFactory
{
    public function __construct(
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createFactory(): self
    {
        return new AssertionStatementFactory(
            PhpUnitCallFactory::createFactory(),
        );
    }

    /**
     * @param non-empty-string $assertionMethod
     */
    public function create(
        string $assertionMethod,
        AssertionMessage $assertionMessage,
        ?AssertionArgument $expected,
        ?AssertionArgument $examined,
    ): StatementInterface {
        $argumentExpressions = [];

        if ($expected instanceof AssertionArgument) {
            $argumentExpressions[] = $this->createMethodArgumentsExpression($expected);
        }

        if ($examined instanceof AssertionArgument) {
            $argumentExpressions[] = $this->createMethodArgumentsExpression($examined);
        }

        $arguments = new MethodArguments($argumentExpressions, MethodArgumentsInterface::FORMAT_STACKED);

        return new Statement(
            $this->phpUnitCallFactory->createAssertionCall($assertionMethod, $arguments, $assertionMessage)
        );
    }

    private function createMethodArgumentsExpression(AssertionArgument $argument): ExpressionInterface
    {
        $expression = $argument->expression;
        if ('string' === $argument->type) {
            $expression = new CastExpression($argument->expression, 'string');
        }

        return $expression;
    }
}
