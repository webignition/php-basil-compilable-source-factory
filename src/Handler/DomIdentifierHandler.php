<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\EmptyLine;
use webignition\BasilCompilableSource\Expression\AssignmentExpression;
use webignition\BasilCompilableSource\Expression\ClosureExpression;
use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Expression\ReturnExpression;
use webignition\BasilCompilableSource\MethodArguments\MethodArguments;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;

class DomIdentifierHandler
{
    public function __construct(
        private DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        private ElementIdentifierCallFactory $elementIdentifierCallFactory,
        private ArgumentFactory $argumentFactory
    ) {
    }

    public static function createHandler(): DomIdentifierHandler
    {
        return new DomIdentifierHandler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            ElementIdentifierCallFactory::createFactory(),
            ArgumentFactory::createFactory()
        );
    }

    public function handleElement(string $serializedElementIdentifier): ExpressionInterface
    {
        return $this->domCrawlerNavigatorCallFactory->createFindOneCall(
            $this->elementIdentifierCallFactory->createConstructorCall($serializedElementIdentifier)
        );
    }

    public function handleElementCollection(string $serializedElementIdentifier): ExpressionInterface
    {
        return $this->domCrawlerNavigatorCallFactory->createFindCall(
            $this->elementIdentifierCallFactory->createConstructorCall($serializedElementIdentifier)
        );
    }

    public function handleAttributeValue(
        string $serializedElementIdentifier,
        string $attributeName
    ): ExpressionInterface {
        $elementIdentifierExpression = $this->elementIdentifierCallFactory->createConstructorCall(
            $serializedElementIdentifier
        );

        $findCall = $this->domCrawlerNavigatorCallFactory->createFindOneCall($elementIdentifierExpression);

        $elementPlaceholder = new VariableName('element');

        $closureExpressionStatements = [
            new Statement(
                new AssignmentExpression($elementPlaceholder, $findCall)
            ),
            new EmptyLine(),
            new Statement(
                new ReturnExpression(
                    new ObjectMethodInvocation(
                        $elementPlaceholder,
                        'getAttribute',
                        new MethodArguments($this->argumentFactory->create($attributeName))
                    )
                )
            ),
        ];

        return new ClosureExpression(new Body($closureExpressionStatements));
    }

    public function handleElementValue(string $serializedElementIdentifier): ExpressionInterface
    {
        $elementIdentifierExpression = $this->elementIdentifierCallFactory->createConstructorCall(
            $serializedElementIdentifier
        );

        $findCall = $this->domCrawlerNavigatorCallFactory->createFindCall($elementIdentifierExpression);

        $elementPlaceholder = new VariableName('element');

        $closureExpressionStatements = [
            new Statement(
                new AssignmentExpression($elementPlaceholder, $findCall)
            ),
            new EmptyLine(),
            new Statement(
                new ReturnExpression(
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableNames::WEBDRIVER_ELEMENT_INSPECTOR),
                        'getValue',
                        new MethodArguments([
                            $elementPlaceholder,
                        ])
                    )
                )
            )
        ];

        return new ClosureExpression(new Body($closureExpressionStatements));
    }
}
