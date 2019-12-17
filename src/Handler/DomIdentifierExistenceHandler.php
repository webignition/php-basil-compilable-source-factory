<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

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

    public function createExistenceAssertion(DomIdentifier $identifier, bool $asCollection): CodeBlockInterface
    {
        $hasCall = $asCollection
            ? $this->domCrawlerNavigatorCallFactory->createHasCall($identifier)
            : $this->domCrawlerNavigatorCallFactory->createHasOneCall($identifier);

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

    public function createExistenceAssertionForElement(DomIdentifier $identifier): CodeBlockInterface
    {
        $hasCall = $this->domCrawlerNavigatorCallFactory->createHasOneCall($identifier);

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

    public function createExistenceAssertionForCollection(DomIdentifier $identifier): CodeBlockInterface
    {
        $hasCall = $this->domCrawlerNavigatorCallFactory->createHasCall($identifier);

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
