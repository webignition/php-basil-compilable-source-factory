<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Model\Json\AssertionMessage;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;

readonly class AssertionStatementFactory
{
    public function __construct(
        private PhpUnitCallFactory $phpUnitCallFactory,
        private ArgumentFactory $argumentFactory,
    ) {}

    public static function createFactory(): self
    {
        return new AssertionStatementFactory(
            PhpUnitCallFactory::createFactory(),
            ArgumentFactory::createFactory(),
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
            $argumentExpressions[] = $this->argumentFactory->createSingular($expected->expression);
        }

        if ($examined instanceof AssertionArgument) {
            $argumentExpressions[] = $this->argumentFactory->createSingular($examined->expression);
        }

        $arguments = new MethodArguments($argumentExpressions, MethodArgumentsInterface::FORMAT_STACKED);

        return new Statement(
            $this->phpUnitCallFactory->createAssertionCall($assertionMethod, $arguments, $assertionMessage)
        );
    }
}
