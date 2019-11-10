<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementLocatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementInspectorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class NamedDomIdentifierHandler implements HandlerInterface
{
    private $domCrawlerNavigatorCallFactory;
    private $elementLocatorCallFactory;
    private $assertionCallFactory;
    private $webDriverElementInspectorCallFactory;
    private $singleQuotedStringEscaper;

    public function __construct(
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory,
        AssertionCallFactory $assertionCallFactory,
        WebDriverElementInspectorCallFactory $webDriverElementInspectorCallFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->assertionCallFactory = $assertionCallFactory;
        $this->webDriverElementInspectorCallFactory = $webDriverElementInspectorCallFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createHandler(): HandlerInterface
    {
        return new NamedDomIdentifierHandler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory(),
            AssertionCallFactory::createFactory(),
            WebDriverElementInspectorCallFactory::createFactory(),
            SingleQuotedStringEscaper::create()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof NamedDomIdentifierInterface;
    }

    /**
     * @param object $model
     *
     * @return BlockInterface
     *
     * @throws UnsupportedModelException
     */
    public function handle(object $model): BlockInterface
    {
        if (!$model instanceof NamedDomIdentifierInterface) {
            throw new UnsupportedModelException($model);
        }

        $identifier = $model->getIdentifier();
        $hasAttribute = null !== $identifier->getAttributeName();

        if ($model->asCollection()) {
            $hasCall = $this->domCrawlerNavigatorCallFactory->createHasCall($identifier);
            $findCall = $this->domCrawlerNavigatorCallFactory->createFindCall($identifier);
        } else {
            $hasCall = $this->domCrawlerNavigatorCallFactory->createHasOneCall($identifier);
            $findCall = $this->domCrawlerNavigatorCallFactory->createFindOneCall($identifier);
        }

        $hasAssignmentVariableExports = new VariablePlaceholderCollection();
        $hasPlaceholder = $hasAssignmentVariableExports->create('HAS');

        $hasAssignment = new Block([
            $hasCall,
        ]);

        $hasAssignment->mutateLastStatement(function ($content) use ($hasPlaceholder) {
            return $hasPlaceholder . ' = ' . $content;
        });
        $hasAssignment->addVariableExportsToLastStatement($hasAssignmentVariableExports);

        $elementPlaceholder = $model->getPlaceholder();
        $collectionAssignmentVariableExports = new VariablePlaceholderCollection([
            $elementPlaceholder,
        ]);

        $elementOrCollectionAssignment = new Block([
            $findCall,
        ]);
        $elementOrCollectionAssignment->mutateLastStatement(function ($content) use ($elementPlaceholder) {
            return $elementPlaceholder . ' = ' . $content;
        });
        $elementOrCollectionAssignment->addVariableExportsToLastStatement($collectionAssignmentVariableExports);

        $elementExistsAssertion = $this->assertionCallFactory->createValueExistenceAssertionCall(
            $hasAssignment,
            $hasPlaceholder,
            AssertionCallFactory::ASSERT_TRUE_TEMPLATE
        );

        $block = new Block([
            $elementExistsAssertion,
            $elementOrCollectionAssignment,
        ]);

        if ($model->includeValue()) {
            if ($hasAttribute) {
                $valueAssignment = new Block([
                    new Statement(sprintf(
                        '%s = %s->getAttribute(\'%s\')',
                        $elementPlaceholder,
                        $elementPlaceholder,
                        $this->singleQuotedStringEscaper->escape((string) $identifier->getAttributeName())
                    ))
                ]);
            } else {
                $getValueCall = $this->webDriverElementInspectorCallFactory->createGetValueCall($elementPlaceholder);
                $getValueCall = new Block([
                    $getValueCall,
                ]);

                $valueAssignment = clone $getValueCall;
                $valueAssignment->mutateLastStatement(function ($content) use ($elementPlaceholder) {
                    return $elementPlaceholder . ' = ' . $content;
                });
            }

            $block->addLinesFromBlock($valueAssignment);
        }

        return $block;
    }
}
