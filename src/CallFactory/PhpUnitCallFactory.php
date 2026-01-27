<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\FooMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\Property;
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
        return new FooMethodInvocation(
            methodName: $methodName,
            arguments: $arguments,
            mightThrow: $mightThrow,
            parent: Property::asDependency(DependencyName::PHPUNIT_TEST_CASE),
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

        $messageFactoryCall = new FooMethodInvocation(
            methodName: 'createAssertionMessage',
            arguments: new MethodArguments($messageFactoryArgumentExpressions)
                ->withFormat(MethodArgumentsInterface::FORMAT_STACKED),
            mightThrow: false,
            parent: Property::asDependency(DependencyName::MESSAGE_FACTORY),
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

        $statementStageEnum = Property::asEnum(
            new ClassName(StatementStage::class),
            $statementStage->name
        );

        $failureMessageFactoryCall = new FooMethodInvocation(
            methodName: 'createFailureMessage',
            arguments: new MethodArguments([
                $this->argumentFactory->createSingular($serializedStatement),
                $this->argumentFactory->createSingular(Property::asVariable('exception')),
                $this->argumentFactory->createSingular($statementStageEnum),
            ])->withFormat(MethodArgumentsInterface::FORMAT_STACKED),
            mightThrow: false,
            parent: Property::asDependency(DependencyName::MESSAGE_FACTORY),
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
