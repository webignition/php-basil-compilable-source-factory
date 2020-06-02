<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Line\ClosureExpression;
use webignition\BasilCompilableSource\Line\EmptyLine;
use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\ReturnStatement;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

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

    public function handle(DomIdentifierInterface $domIdentifier): ExpressionInterface
    {
        $identifier = $domIdentifier->getIdentifier();
        $elementIdentifierExpression = $this->elementIdentifierCallFactory->createConstructorCall($identifier);

        $findCall = $domIdentifier->asCollection()
            ? $this->domCrawlerNavigatorCallFactory->createFindCall($elementIdentifierExpression)
            : $this->domCrawlerNavigatorCallFactory->createFindOneCall($elementIdentifierExpression);

        if (false === $domIdentifier->includeValue()) {
            return $findCall;
        }

        $elementPlaceholder = VariablePlaceholder::createExport('ELEMENT');

        $closureExpressionStatements = [
            new AssignmentStatement($elementPlaceholder, $findCall),
            new EmptyLine(),
        ];

        if ($identifier instanceof AttributeIdentifierInterface) {
            $closureExpressionStatements[] = new ReturnStatement(
                new ObjectMethodInvocation(
                    $elementPlaceholder,
                    'getAttribute',
                    [
                        new LiteralExpression(sprintf(
                            '\'%s\'',
                            $this->singleQuotedStringEscaper->escape((string) $identifier->getAttributeName())
                        )),
                    ]
                )
            );
        } else {
            $closureExpressionStatements[] = new ReturnStatement(
                new ObjectMethodInvocation(
                    VariablePlaceholder::createDependency(VariableNames::WEBDRIVER_ELEMENT_INSPECTOR),
                    'getValue',
                    [
                        $elementPlaceholder,
                    ]
                )
            );
        }

        return new ClosureExpression(new CodeBlock($closureExpressionStatements));
    }
}
