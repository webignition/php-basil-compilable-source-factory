<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnknownIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedActionException;
use webignition\BasilCompilableSourceFactory\IdentifierTypeFinder;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilDataStructure\Action\ActionInterface;

class WaitForActionHandler
{
    private $singleQuotedStringEscaper;
    private $domIdentifierFactory;

    public function __construct(
        SingleQuotedStringEscaper $singleQuotedStringEscaper,
        DomIdentifierFactory $domIdentifierFactory
    ) {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
        $this->domIdentifierFactory = $domIdentifierFactory;
    }

    public static function createHandler(): WaitForActionHandler
    {
        return new WaitForActionHandler(
            SingleQuotedStringEscaper::create(),
            DomIdentifierFactory::createFactory()
        );
    }

    /**
     * @param ActionInterface $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnknownIdentifierException
     * @throws UnsupportedActionException
     */
    public function handle(ActionInterface $action): CodeBlockInterface
    {
        $identifier = $action->getIdentifier();

        if (!IdentifierTypeFinder::isDomIdentifier($identifier)) {
            throw new UnsupportedActionException($action);
        }

        $domIdentifier = $this->domIdentifierFactory->create($identifier);

        if (null !== $domIdentifier->getAttributeName()) {
            throw new UnsupportedActionException($action);
        }

        $variableDependencies = new VariablePlaceholderCollection();
        $pantherCrawlerPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CRAWLER);
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $metadata = (new Metadata())->withVariableDependencies($variableDependencies);

        return new CodeBlock([
            new Statement(
                sprintf(
                    '%s = %s->waitFor(\'%s\')',
                    $pantherCrawlerPlaceholder,
                    $pantherClientPlaceholder,
                    $this->singleQuotedStringEscaper->escape($domIdentifier->getLocator())
                ),
                $metadata
            )
        ]);
    }
}
