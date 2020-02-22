<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class DomIdentifierExistenceHandler
{
    private $domCrawlerNavigatorCallFactory;
    private $assertionMethodInvocationFactory;

    public function __construct(
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory
    ) {
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->assertionMethodInvocationFactory = $assertionMethodInvocationFactory;
    }

    public static function createHandler(): DomIdentifierExistenceHandler
    {
        return new DomIdentifierExistenceHandler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            AssertionMethodInvocationFactory::createFactory()
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
                $this->assertionMethodInvocationFactory->create(
                    'assertTrue',
                    [
                        $hasPlaceholder
                    ],
                    $assertionFailureMessage
                )
            ),
        ]);
    }
}
