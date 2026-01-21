<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ObjectConstant;
use webignition\BasilCompilableSourceFactory\Model\Json\AssertionMessage;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilModels\Model\StatementInterface;

readonly class PhpUnitCallFactory
{
    public function __construct(
        private ArgumentFactory $argumentFactory,
    ) {}

    public static function createFactory(): self
    {
        return new PhpUnitCallFactory(
            ArgumentFactory::createFactory(),
        );
    }

    public function createCall(
        string $methodName,
        ?MethodArgumentsInterface $arguments = null
    ): MethodInvocationInterface {
        return new ObjectMethodInvocation(
            new VariableDependency(VariableNameEnum::PHPUNIT_TEST_CASE),
            $methodName,
            $arguments
        );
    }

    public function createAssertionCall(
        string $methodName,
        MethodArgumentsInterface $arguments,
        AssertionMessage $assertionMessage,
    ): MethodInvocationInterface {
        $arguments = $arguments->withArgument($assertionMessage);

        return $this->createCall($methodName, $arguments);
    }

    public function createFailCall(
        StatementInterface $statement,
        StatementStage $statementStage,
    ): MethodInvocationInterface {
        $serializedStatement = (string) json_encode($statement, JSON_PRETTY_PRINT);
        $serializedStatement = addcslashes($serializedStatement, "'");

        $statementStageEnum = new ObjectConstant(
            new ClassName(StatementStage::class),
            $statementStage->name
        );

        $failureMessageFactoryCall = new ObjectMethodInvocation(
            new VariableDependency(VariableNameEnum::FAILURE_MESSAGE_FACTORY),
            'create',
            new MethodArguments([
                $this->argumentFactory->createSingular($serializedStatement),
                $this->argumentFactory->createSingular($statementStageEnum),
                $this->argumentFactory->createSingular(new VariableName('exception')),
            ])->withFormat(MethodArgumentsInterface::FORMAT_STACKED)
        );

        return $this->createCall(
            'fail',
            new MethodArguments([
                $failureMessageFactoryCall,
            ])->withFormat(MethodArgumentsInterface::FORMAT_STACKED)
        );
    }
}
