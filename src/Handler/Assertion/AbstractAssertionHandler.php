<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\Statement\StatementInterface;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
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

    protected function createAssertionStatement(
        AssertionInterface $assertion,
        ?MethodArgumentsInterface $arguments = null
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
