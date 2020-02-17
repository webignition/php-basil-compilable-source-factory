<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Line\ClosureExpression;
use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\ReturnStatement;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementInspectorCallFactory;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class NamedDomIdentifierHandler
{
    private $domCrawlerNavigatorCallFactory;
    private $webDriverElementInspectorCallFactory;
    private $singleQuotedStringEscaper;

    public function __construct(
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        WebDriverElementInspectorCallFactory $webDriverElementInspectorCallFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->webDriverElementInspectorCallFactory = $webDriverElementInspectorCallFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createHandler(): NamedDomIdentifierHandler
    {
        return new NamedDomIdentifierHandler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            WebDriverElementInspectorCallFactory::createFactory(),
            SingleQuotedStringEscaper::create()
        );
    }

    public function handle(NamedDomIdentifierInterface $namedDomIdentifier): ExpressionInterface
    {
        $identifier = $namedDomIdentifier->getIdentifier();

        $findCall = $namedDomIdentifier->asCollection()
            ? $this->domCrawlerNavigatorCallFactory->createFindCall($identifier)
            : $this->domCrawlerNavigatorCallFactory->createFindOneCall($identifier);

        if (false === $namedDomIdentifier->includeValue()) {
            return new ClosureExpression(new CodeBlock([
                new ReturnStatement(
                    $findCall
                ),
            ]));
        }

        $elementPlaceholder = $namedDomIdentifier->getPlaceholder();

        $closureExpressionStatements = [
            new AssignmentStatement($elementPlaceholder, $findCall),
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
                $this->webDriverElementInspectorCallFactory->createGetValueCall($elementPlaceholder)
            );
        }

        return new ClosureExpression(new CodeBlock($closureExpressionStatements));
    }
}
