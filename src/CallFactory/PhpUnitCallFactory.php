<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

readonly class PhpUnitCallFactory
{
    public static function createFactory(): self
    {
        return new PhpUnitCallFactory();
    }

    public function createCall(
        string $methodName,
        MethodArgumentsInterface $arguments,
        bool $mightThrow,
        TypeCollection $type,
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
            type: TypeCollection::void(),
        );
    }

    /**
     * @param ExpressionInterface[] $methodExpressions
     * @param ExpressionInterface[] $messageExpressions
     */
    public function createAssertionCall(
        string $methodName,
        Property $statementVariable,
        array $methodExpressions,
        array $messageExpressions,
    ): MethodInvocationInterface {
        $messageFactoryArgumentExpressions = [
            $statementVariable,
        ];

        foreach ($messageExpressions as $expression) {
            $messageFactoryArgumentExpressions[] = $expression;
        }

        $messageFactoryCall = new MethodInvocation(
            methodName: 'createAssertionMessage',
            arguments: new MethodArguments($messageFactoryArgumentExpressions),
            mightThrow: false,
            type: TypeCollection::object(),
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

        return $this->createCall($methodName, $arguments, false, TypeCollection::void());
    }

    public function createFailCall(
        Property $statementVariable,
        StatementStage $statementStage,
    ): MethodInvocationInterface {
        $statementStageEnum = Property::asEnum(
            new ClassName(StatementStage::class),
            $statementStage->name,
            TypeCollection::object(),
        );

        $failureMessageFactoryCall = new MethodInvocation(
            methodName: 'createFailureMessage',
            arguments: new MethodArguments([
                $statementVariable,
                Property::asObjectVariable('exception'),
                $statementStageEnum,
            ]),
            mightThrow: false,
            type: TypeCollection::object(),
            parent: Property::asDependency(DependencyName::MESSAGE_FACTORY),
        );

        return $this->createCall(
            methodName: 'fail',
            arguments: new MethodArguments([
                $failureMessageFactoryCall,
            ])->withFormat(MethodArgumentsInterface::FORMAT_STACKED),
            mightThrow: false,
            type: TypeCollection::void(),
        );
    }
}
