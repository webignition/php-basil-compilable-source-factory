<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\DomElementIdentifier\ElementIdentifier;

class ElementIdentifierCallFactory
{
    public function __construct(
        private ArgumentFactory $argumentFactory
    ) {}

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
            new MethodArguments(
                $this->argumentFactory->create($serializedSourceIdentifier)
            )
        );
    }
}
