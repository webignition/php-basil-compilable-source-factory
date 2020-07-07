<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;

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
            new VariableDependency(VariableNames::DOM_CRAWLER_NAVIGATOR),
            $methodName,
            [
                $elementIdentifierExpression,
            ]
        );
    }
}
