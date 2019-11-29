<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\IdentifierTypeFinder;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModels\Action\InteractionActionInterface;

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
     * @param InteractionActionInterface $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedIdentifierException
     */
    public function handle(InteractionActionInterface $action): CodeBlockInterface
    {
        $identifier = $action->getIdentifier();

        if (!IdentifierTypeFinder::isDomIdentifier($identifier)) {
            throw new UnsupportedIdentifierException($identifier);
        }

        $domIdentifier = $this->domIdentifierFactory->create($identifier);

        if (null !== $domIdentifier->getAttributeName()) {
            throw new UnsupportedIdentifierException($identifier);
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
