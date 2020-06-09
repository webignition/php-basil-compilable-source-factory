<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\Statement\StatementInterface;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilModels\Assertion\AssertionInterface;

abstract class AbstractAssertionHandler
{
    private AssertionMethodInvocationFactory $assertionMethodInvocationFactory;

    public function __construct(
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory
    ) {
        $this->assertionMethodInvocationFactory = $assertionMethodInvocationFactory;
    }

    /**
     * @return array<string, string>
     */
    abstract protected function getOperationToAssertionTemplateMap(): array;

    /**
     * @param AssertionInterface $assertion
     * @param ExpressionInterface[] $arguments
     *
     * @return StatementInterface
     */
    protected function createAssertionStatement(AssertionInterface $assertion, array $arguments): StatementInterface
    {
        return new Statement(
            $this->assertionMethodInvocationFactory->create(
                $this->getOperationToAssertionTemplateMap()[$assertion->getOperator()],
                $arguments
            )
        );
    }
}
