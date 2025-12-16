<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

abstract class AbstractAssertionHandler
{
    public function __construct(
        private AssertionMethodInvocationFactory $assertionMethodInvocationFactory
    ) {}

    /**
     * @return array<string, string>
     */
    abstract protected function getOperationToAssertionTemplateMap(): array;

    protected function createAssertionStatement(
        AssertionInterface $assertion,
        Metadata $metadata,
        MethodArgumentsInterface $arguments,
    ): StatementInterface {
        return new Statement(
            $this->assertionMethodInvocationFactory->create(
                $this->getOperationToAssertionTemplateMap()[$assertion->getOperator()],
                $metadata,
                $arguments
            )
        );
    }
}
