<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementInspectorCallFactory;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
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

    public function handle(NamedDomIdentifierInterface $namedDomIdentifier): CodeBlockInterface
    {
        $identifier = $namedDomIdentifier->getIdentifier();
        $hasAttribute = $identifier instanceof AttributeIdentifierInterface;

        $findCall = $namedDomIdentifier->asCollection()
            ? $this->domCrawlerNavigatorCallFactory->createFindCall($identifier)
            : $this->domCrawlerNavigatorCallFactory->createFindOneCall($identifier);

        $elementPlaceholder = $namedDomIdentifier->getPlaceholder();
        $collectionAssignmentVariableExports = new VariablePlaceholderCollection([
            $elementPlaceholder,
        ]);

        $elementOrCollectionAssignment = new CodeBlock([
            $findCall,
        ]);
        $elementOrCollectionAssignment->mutateLastStatement(function ($content) use ($elementPlaceholder) {
            return $elementPlaceholder . ' = ' . $content;
        });
        $elementOrCollectionAssignment->addVariableExportsToLastStatement($collectionAssignmentVariableExports);

        $block = new CodeBlock([
            $elementOrCollectionAssignment,
        ]);

        if ($namedDomIdentifier->includeValue()) {
            if ($hasAttribute) {
                $valueAssignment = new CodeBlock([
                    new Statement(sprintf(
                        '%s = %s->getAttribute(\'%s\')',
                        $elementPlaceholder,
                        $elementPlaceholder,
                        $this->singleQuotedStringEscaper->escape((string) $identifier->getAttributeName())
                    ))
                ]);
            } else {
                $getValueCall = $this->webDriverElementInspectorCallFactory->createGetValueCall($elementPlaceholder);
                $getValueCall = new CodeBlock([
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
