<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\VariableNames;

class DomCrawlerNavigatorCallFactory
{
    private $elementIdentifierCallFactory;

    public function __construct(ElementIdentifierCallFactory $elementIdentifierCallFactory)
    {
        $this->elementIdentifierCallFactory = $elementIdentifierCallFactory;
    }

    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory(
            ElementIdentifierCallFactory::createFactory()
        );
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
            VariablePlaceholder::createDependency(VariableNames::DOM_CRAWLER_NAVIGATOR),
            $methodName,
            [
                $elementIdentifierExpression,
            ]
        );
    }
}
