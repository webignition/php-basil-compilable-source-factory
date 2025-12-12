<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;

class DomCrawlerNavigatorCallFactory
{
    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory();
    }

    public function createFindCall(ExpressionInterface $elementIdentifierExpression): ExpressionInterface
    {
        return $this->createElementCall('find', $elementIdentifierExpression);
    }

    public function createFindOneCall(ExpressionInterface $elementIdentifierExpression): ExpressionInterface
    {
        return $this->createElementCall('findOne', $elementIdentifierExpression);
    }

    public function createHasCall(ExpressionInterface $elementIdentifierExpression): ExpressionInterface
    {
        return $this->createElementCall('has', $elementIdentifierExpression);
    }

    public function createHasOneCall(ExpressionInterface $elementIdentifierExpression): ExpressionInterface
    {
        return $this->createElementCall('hasOne', $elementIdentifierExpression);
    }

    private function createElementCall(
        string $methodName,
        ExpressionInterface $elementIdentifierExpression
    ): ExpressionInterface {
        return new ObjectMethodInvocation(
            new VariableDependency(VariableName::DOM_CRAWLER_NAVIGATOR),
            $methodName,
            new MethodArguments([
                $elementIdentifierExpression,
            ])
        );
    }
}
