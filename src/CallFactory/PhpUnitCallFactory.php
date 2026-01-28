<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
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
        Type $type,
    ): MethodInvocationInterface {
        return new MethodInvocation(
            methodName: $methodName,
            arguments: $arguments,
            mightThrow: $mightThrow,
            type: $type,
            parent: Property::asDependency(DependencyName::PHPUNIT_TEST_CASE),
        );
    }

    public function createRefreshCrawlerAndNavigatorCall(): MethodInvocationInterface
    {
        return $this->createCall(
            methodName: 'refreshCrawlerAndNavigator',
            arguments: new MethodArguments(),
            mightThrow: false,
            type: Type::VOID,
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
            $this->argumentFactory->create($serializedStatement),
        ];

        foreach ($messageExpressions as $expression) {
            $messageFactoryArgumentExpressions[] = $expression;
        }

        $messageFactoryCall = new MethodInvocation(
            methodName: 'createAssertionMessage',
            arguments: new MethodArguments($messageFactoryArgumentExpressions)
                ->withFormat(MethodArgumentsInterface::FORMAT_STACKED),
            mightThrow: false,
            type: Type::OBJECT,
            parent: Property::asDependency(DependencyName::MESSAGE_FACTORY),
        );

        $assertionCallArgumentExpressions = [];
        foreach ($methodExpressions as $expression) {
            $assertionCallArgumentExpressions[] = $expression;
        }

        $assertionCallArgumentExpressions[] = $messageFactoryCall;

        $arguments = new MethodArguments($assertionCallArgumentExpressions)
            ->withFormat(MethodArgumentsInterface::FORMAT_STACKED)
        ;

        return $this->createCall($methodName, $arguments, false, Type::VOID);
    }

    public function createFailCall(
        StatementInterface $statement,
        StatementStage $statementStage,
    ): MethodInvocationInterface {
        $serializedStatement = (string) json_encode($statement, JSON_PRETTY_PRINT);
        $serializedStatement = addcslashes($serializedStatement, "'");

        $statementStageEnum = Property::asEnum(
            new ClassName(StatementStage::class),
            $statementStage->name,
            Type::OBJECT,
        );

        $failureMessageFactoryCall = new MethodInvocation(
            methodName: 'createFailureMessage',
            arguments: new MethodArguments([
                $this->argumentFactory->create($serializedStatement),
                Property::asObjectVariable('exception'),
                $statementStageEnum,
            ])->withFormat(MethodArgumentsInterface::FORMAT_STACKED),
            mightThrow: false,
            type: Type::OBJECT,
            parent: Property::asDependency(DependencyName::MESSAGE_FACTORY),
        );

        return $this->createCall(
            methodName: 'fail',
            arguments: new MethodArguments([
                $failureMessageFactoryCall,
            ])->withFormat(MethodArgumentsInterface::FORMAT_STACKED),
            mightThrow: false,
            type: Type::VOID,
        );
    }
}
