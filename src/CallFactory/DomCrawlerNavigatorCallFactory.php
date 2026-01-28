<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;

class DomCrawlerNavigatorCallFactory
{
    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory();
    }

    public function createFindCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('find', $expression, Type::OBJECT);
    }

    public function createFindOneCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('findOne', $expression, Type::OBJECT);
    }

    public function createHasCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('has', $expression, Type::BOOLEAN);
    }

    public function createHasOneCall(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->createElementCall('hasOne', $expression, Type::BOOLEAN);
    }

    private function createElementCall(
        string $methodName,
        ExpressionInterface $expression,
        Type $type,
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
