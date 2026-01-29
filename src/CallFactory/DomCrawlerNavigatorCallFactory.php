<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

class DomCrawlerNavigatorCallFactory
{
    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory();
    }

    public function createFindCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('find', $expression, TypeCollection::object());
    }

    public function createFindOneCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('findOne', $expression, TypeCollection::object());
    }

    public function createHasCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('has', $expression, TypeCollection::boolean());
    }

    public function createHasOneCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('hasOne', $expression, TypeCollection::boolean());
    }

    private function createElementCall(
        string $methodName,
        ExpressionInterface $expression,
        TypeCollection $type,
    ): ExpressionInterface {
        return new MethodInvocation(
            methodName: $methodName,
            arguments: new MethodArguments([$expression]),
            mightThrow: true,
            type: $type,
            parent: Property::asDependency(DependencyName::DOM_CRAWLER_NAVIGATOR),
        );
    }
}
