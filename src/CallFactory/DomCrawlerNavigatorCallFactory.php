<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

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

    public function createFindCall(ElementIdentifierInterface $identifier): ExpressionInterface
    {
        return $this->createElementCall($identifier, 'find');
    }

    public function createFindOneCall(ElementIdentifierInterface $identifier): ExpressionInterface
    {
        return $this->createElementCall($identifier, 'findOne');
    }

    public function createHasCall(ElementIdentifierInterface $identifier): ExpressionInterface
    {
        return $this->createElementCall($identifier, 'has');
    }

    public function createHasOneCall(ElementIdentifierInterface $identifier): ExpressionInterface
    {
        return $this->createElementCall($identifier, 'hasOne');
    }

    private function createElementCall(ElementIdentifierInterface $identifier, string $methodName): ExpressionInterface
    {
        $elementOnlyIdentifier = ElementIdentifier::fromAttributeIdentifier($identifier);

        return new ObjectMethodInvocation(
            VariablePlaceholder::createDependency(VariableNames::DOM_CRAWLER_NAVIGATOR),
            $methodName,
            [
                $this->elementIdentifierCallFactory->createConstructorCall($elementOnlyIdentifier),
            ]
        );
    }
}
