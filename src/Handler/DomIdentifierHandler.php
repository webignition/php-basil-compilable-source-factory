<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Expression\ClosureExpression;
use webignition\BasilCompilableSource\EmptyLine;
use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Statement\ReturnStatement;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;

class DomIdentifierHandler
{
    private DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory;
    private SingleQuotedStringEscaper $singleQuotedStringEscaper;
    private ElementIdentifierCallFactory $elementIdentifierCallFactory;

    public function __construct(
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper,
        ElementIdentifierCallFactory $elementIdentifierCallFactory
    ) {
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
        $this->elementIdentifierCallFactory = $elementIdentifierCallFactory;
    }

    public static function createHandler(): DomIdentifierHandler
    {
        return new DomIdentifierHandler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            SingleQuotedStringEscaper::create(),
            ElementIdentifierCallFactory::createFactory()
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
            new AssignmentStatement($elementPlaceholder, $findCall),
            new EmptyLine(),
        ];

        $closureExpressionStatements[] = new ReturnStatement(
            new ObjectMethodInvocation(
                $elementPlaceholder,
                'getAttribute',
                [
                    new LiteralExpression(sprintf(
                        '\'%s\'',
                        $this->singleQuotedStringEscaper->escape($attributeName)
                    )),
                ]
            )
        );

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
            new AssignmentStatement($elementPlaceholder, $findCall),
            new EmptyLine(),
        ];

            $closureExpressionStatements[] = new ReturnStatement(
                new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::WEBDRIVER_ELEMENT_INSPECTOR),
                    'getValue',
                    [
                        $elementPlaceholder,
                    ]
                )
            );


        return new ClosureExpression(new Body($closureExpressionStatements));
    }
}
