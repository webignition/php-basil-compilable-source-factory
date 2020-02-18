<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class DomIdentifierExistenceHandler
{
    private $domCrawlerNavigatorCallFactory;
    private $singleQuotedStringEscaper;

    public function __construct(
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createHandler(): DomIdentifierExistenceHandler
    {
        return new DomIdentifierExistenceHandler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            SingleQuotedStringEscaper::create()
        );
    }

    public function createForElement(
        ElementIdentifierInterface $identifier,
        string $assertionFailureMessage
    ): CodeBlockInterface {
        return $this->create(
            $this->domCrawlerNavigatorCallFactory->createHasOneCall($identifier),
            $assertionFailureMessage
        );
    }

    public function createForCollection(
        ElementIdentifierInterface $identifier,
        string $assertionFailureMessage
    ): CodeBlockInterface {
        return $this->create(
            $this->domCrawlerNavigatorCallFactory->createHasCall($identifier),
            $assertionFailureMessage
        );
    }

    private function create(ExpressionInterface $hasCall, string $assertionFailureMessage): CodeBlockInterface
    {
        $hasPlaceholder = VariablePlaceholder::createExport('HAS');
        $hasAssignment = new AssignmentStatement(
            $hasPlaceholder,
            $hasCall
        );

        return new CodeBlock([
            $hasAssignment,
            new Statement(
                new ObjectMethodInvocation(
                    VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                    'assertTrue',
                    [
                        $hasPlaceholder,
                        new LiteralExpression(
                            '\'' . $this->singleQuotedStringEscaper->escape($assertionFailureMessage) . '\''
                        )
                    ]
                )
            )
        ]);
    }
}
