<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\FooMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;

class DomCrawlerNavigatorCallFactory
{
    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory();
    }

    public function createFindCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('find', $expression);
    }

    public function createFindOneCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('findOne', $expression);
    }

    public function createHasCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('has', $expression);
    }

    public function createHasOneCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('hasOne', $expression);
    }

    private function createElementCall(string $methodName, ExpressionInterface $expression): ExpressionInterface
    {
        return new FooMethodInvocation(
            methodName: $methodName,
            arguments: new MethodArguments([$expression]),
            mightThrow: true,
            parent: Property::asDependency(DependencyName::DOM_CRAWLER_NAVIGATOR),
        );
    }
}
