<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Factory\ArgumentFactory;
use webignition\BasilCompilableSource\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\StaticObject;
use webignition\DomElementIdentifier\ElementIdentifier;

class ElementIdentifierCallFactory
{
    private ArgumentFactory $argumentFactory;

    public function __construct(ArgumentFactory $argumentFactory)
    {
        $this->argumentFactory = $argumentFactory;
    }

    public static function createFactory(): ElementIdentifierCallFactory
    {
        return new ElementIdentifierCallFactory(
            ArgumentFactory::createFactory()
        );
    }

    public function createConstructorCall(string $serializedSourceIdentifier): ExpressionInterface
    {
        return new StaticObjectMethodInvocation(
            new StaticObject(ElementIdentifier::class),
            'fromJson',
            $this->argumentFactory->create([
                $serializedSourceIdentifier,
            ])
        );
    }
}
