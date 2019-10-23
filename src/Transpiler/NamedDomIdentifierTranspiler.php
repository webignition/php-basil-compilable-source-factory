<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementLocatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementInspectorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class NamedDomIdentifierTranspiler implements HandlerInterface
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

    public static function createTranspiler(): NamedDomIdentifierTranspiler
    {
        return new NamedDomIdentifierTranspiler(
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

    public function transpile(object $model): SourceInterface
    {
        if (!$model instanceof NamedDomIdentifierInterface) {
            throw new NonTranspilableModelException($model);
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
        $hasAssignment->prependStatement(-1, $hasPlaceholder . ' = ');
        $hasAssignment = $hasAssignment->withMetadata(
            (new Metadata())
                ->merge([
                    $hasCall->getMetadata(),
                ])
                ->withVariableExports($hasAssignmentVariableExports)
        );

        $elementPlaceholder = $model->getPlaceholder();
        $collectionAssignmentVariableExports = new VariablePlaceholderCollection([
            $elementPlaceholder,
        ]);

        $elementOrCollectionAssignment = clone $findCall;
        $elementOrCollectionAssignment->prependStatement(-1, $elementPlaceholder . ' = ');
        $elementOrCollectionAssignment = $elementOrCollectionAssignment->withMetadata(
            (new Metadata())
                ->merge([
                    $findCall->getMetadata(),
                ])
                ->withVariableExports($collectionAssignmentVariableExports)
        );

        $elementExistsAssertion = $this->assertionCallFactory->createValueExistenceAssertionCall(
            $hasAssignment,
            $hasPlaceholder,
            AssertionCallFactory::ASSERT_TRUE_TEMPLATE
        );

        $predecessors = [
            $elementExistsAssertion,
            $elementOrCollectionAssignment,
        ];

        if ($model->includeValue()) {
            if ($hasAttribute) {
                $valueAssignment = (new Source())
                    ->withStatements([
                        sprintf(
                            '%s = %s->getAttribute(\'%s\')',
                            $elementPlaceholder,
                            $elementPlaceholder,
                            $this->singleQuotedStringEscaper->escape((string) $identifier->getAttributeName())
                        )
                    ]);
            } else {
                $getValueCall = $this->webDriverElementInspectorCallFactory->createGetValueCall($elementPlaceholder);

                $valueAssignment = clone $getValueCall;
                $valueAssignment->prependStatement(-1, $elementPlaceholder . ' = ');
            }

            $predecessors[] = $valueAssignment;
        }

        return (new Source())
            ->withPredecessors($predecessors);
    }
}
