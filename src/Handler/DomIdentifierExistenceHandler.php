<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class DomIdentifierExistenceHandler
{
    private $domCrawlerNavigatorCallFactory;
    private $assertionCallFactory;

    public function __construct(
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        AssertionCallFactory $assertionCallFactory
    ) {
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->assertionCallFactory = $assertionCallFactory;
    }

    public static function createHandler(): DomIdentifierExistenceHandler
    {
        return new DomIdentifierExistenceHandler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            AssertionCallFactory::createFactory()
        );
    }

    public function createForElementOrCollection(ElementIdentifierInterface $identifier): CodeBlockInterface
    {
        return $identifier instanceof AttributeIdentifierInterface
            ? $this->createForElement($identifier)
            : $this->createForCollection($identifier);
    }

    public function createForElement(ElementIdentifierInterface $identifier): CodeBlockInterface
    {
        return $this->create(
            $this->domCrawlerNavigatorCallFactory->createHasOneCall($identifier)
        );
    }

    public function createForCollection(ElementIdentifierInterface $identifier): CodeBlockInterface
    {
        return $this->create(
            $this->domCrawlerNavigatorCallFactory->createHasCall($identifier)
        );
    }

    private function create(CodeBlockInterface $hasCall): CodeBlockInterface
    {
        $hasAssignmentVariableExports = new VariablePlaceholderCollection();
        $hasPlaceholder = $hasAssignmentVariableExports->create('HAS');

        $hasAssignment = new CodeBlock([
            $hasCall,
        ]);

        $hasAssignment->mutateLastStatement(function ($content) use ($hasPlaceholder) {
            return $hasPlaceholder . ' = ' . $content;
        });
        $hasAssignment->addVariableExportsToLastStatement($hasAssignmentVariableExports);

        return $this->assertionCallFactory->createValueExistenceAssertionCall(
            $hasAssignment,
            $hasPlaceholder,
            AssertionCallFactory::ASSERT_TRUE_TEMPLATE
        );
    }
}
