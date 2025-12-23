<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Model\Json\Statement as JsonStatement;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

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
        AssertionInterface $assertion,
        string $assertionMethod,
        MethodArgumentsInterface $arguments,
    ): StatementInterface {
        $arguments = $arguments->withFormat(
            MethodArgumentsInterface::FORMAT_STACKED
        );

        return new Statement(
            $this->phpUnitCallFactory->createAssertionCall(
                $assertionMethod,
                $arguments,
                new JsonStatement($assertion),
            )
        );
    }
}
