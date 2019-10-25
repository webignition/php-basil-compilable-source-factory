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
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementList;
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

    public function createSource(object $model): SourceInterface
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

        $hasAssignment = clone $hasCall;
        $hasAssignment->mutate(function ($content) use ($hasPlaceholder) {
            return $hasPlaceholder . ' = ' . $content;
        });
        $hasAssignment->addVariableExports($hasAssignmentVariableExports);

        $elementPlaceholder = $model->getPlaceholder();
        $collectionAssignmentVariableExports = new VariablePlaceholderCollection([
            $elementPlaceholder,
        ]);

        $elementOrCollectionAssignment = clone $findCall;
        $elementOrCollectionAssignment->mutate(function ($content) use ($elementPlaceholder) {
            return $elementPlaceholder . ' = ' . $content;
        });
        $elementOrCollectionAssignment->addVariableExports($collectionAssignmentVariableExports);

        $elementExistsAssertion = $this->assertionCallFactory->createValueExistenceAssertionCall(
            new StatementList([$hasAssignment]),
            $hasPlaceholder,
            AssertionCallFactory::ASSERT_TRUE_TEMPLATE
        );

        $statements = array_merge(
            $elementExistsAssertion->getStatementObjects(),
            [$elementOrCollectionAssignment]
        );

        if ($model->includeValue()) {
            if ($hasAttribute) {
                $valueAssignment = new Statement(sprintf(
                    '%s = %s->getAttribute(\'%s\')',
                    $elementPlaceholder,
                    $elementPlaceholder,
                    $this->singleQuotedStringEscaper->escape((string) $identifier->getAttributeName())
                ));
            } else {
                $getValueCall = $this->webDriverElementInspectorCallFactory->createGetValueCall($elementPlaceholder);

                $valueAssignment = clone $getValueCall;
                $valueAssignment->mutate(function ($content) use ($elementPlaceholder) {
                    return $elementPlaceholder . ' = ' . $content;
                });
            }

            $statements = array_merge($statements, [$valueAssignment]);
        }

        return new StatementList($statements);
    }
}
