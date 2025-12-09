<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;
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
        MethodArgumentsInterface $arguments,
    ): StatementInterface {
        return new Statement(
            $this->assertionMethodInvocationFactory->create(
                $this->getOperationToAssertionTemplateMap()[$assertion->getOperator()],
                $arguments
            )
        );
    }

    protected function createPhpUnitTestCaseObjectMethodInvocation(
        string $methodName,
        ?MethodArgumentsInterface $arguments = null
    ): ExpressionInterface {
        return new ObjectMethodInvocation(
            new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
            $methodName,
            $arguments
        );
    }
}
