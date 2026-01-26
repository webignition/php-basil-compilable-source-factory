<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\ObjectConstant;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;

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
        MethodArgumentsInterface $arguments,
        bool $mightThrow,
    ): MethodInvocationInterface {
        return new ObjectMethodInvocation(
            object: new VariableDependency(VariableName::PHPUNIT_TEST_CASE->value),
            methodName: $methodName,
            arguments: $arguments,
            mightThrow: $mightThrow,
        );
    }

    public function createRefreshCrawlerAndNavigatorCall(): MethodInvocationInterface
    {
        return $this->createCall(
            methodName: 'refreshCrawlerAndNavigator',
            arguments: new MethodArguments(),
            mightThrow: false,
        );
    }

    /**
     * @param ExpressionInterface[] $methodExpressions
     * @param ExpressionInterface[] $messageExpressions
     */
    public function createAssertionCall(
        string $methodName,
        AssertionInterface $statement,
        array $methodExpressions,
        array $messageExpressions,
    ): MethodInvocationInterface {
        $serializedStatement = (string) json_encode($statement, JSON_PRETTY_PRINT);
        $serializedStatement = addcslashes($serializedStatement, "'");

        $messageFactoryArgumentExpressions = [
            $this->argumentFactory->createSingular($serializedStatement),
        ];

        foreach ($messageExpressions as $expression) {
            $messageFactoryArgumentExpressions[] = $this->argumentFactory->createSingular($expression);
        }

        $messageFactoryCall = new ObjectMethodInvocation(
            object: new VariableDependency(VariableName::MESSAGE_FACTORY->value),
            methodName: 'createAssertionMessage',
            arguments: new MethodArguments($messageFactoryArgumentExpressions)
                ->withFormat(MethodArgumentsInterface::FORMAT_STACKED),
            mightThrow: false,
        );

        $assertionCallArgumentExpressions = [];
        foreach ($methodExpressions as $expression) {
            $assertionCallArgumentExpressions[] = $this->argumentFactory->createSingular($expression);
        }

        $assertionCallArgumentExpressions[] = $this->argumentFactory->createSingular($messageFactoryCall);

        $arguments = new MethodArguments($assertionCallArgumentExpressions)
            ->withFormat(MethodArgumentsInterface::FORMAT_STACKED)
        ;

        return $this->createCall($methodName, $arguments, false);
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
            object: new VariableDependency(VariableName::MESSAGE_FACTORY->value),
            methodName: 'createFailureMessage',
            arguments: new MethodArguments([
                $this->argumentFactory->createSingular($serializedStatement),
                $this->argumentFactory->createSingular(new Property('exception')),
                $this->argumentFactory->createSingular($statementStageEnum),
            ])->withFormat(MethodArgumentsInterface::FORMAT_STACKED),
            mightThrow: false
        );

        return $this->createCall(
            methodName: 'fail',
            arguments: new MethodArguments([
                $failureMessageFactoryCall,
            ])->withFormat(MethodArgumentsInterface::FORMAT_STACKED),
            mightThrow: false,
        );
    }
}
