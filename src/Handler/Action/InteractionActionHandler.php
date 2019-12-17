<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\InteractionActionInterface;

class InteractionActionHandler
{
    private $namedDomIdentifierHandler;
    private $domIdentifierFactory;
    private $identifierTypeAnalyser;

    public function __construct(
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser
    ) {
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
    }

    public static function createHandler(): InteractionActionHandler
    {
        return new InteractionActionHandler(
            NamedDomIdentifierHandler::createHandler(),
            DomIdentifierFactory::createFactory(),
            new IdentifierTypeAnalyser()
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

        if (!$this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            throw new UnsupportedIdentifierException($identifier);
        }

        $domIdentifier = $this->domIdentifierFactory->create($identifier);

        if (null !== $domIdentifier->getAttributeName()) {
            throw new UnsupportedIdentifierException($identifier);
        }

        $variableExports = new VariablePlaceholderCollection();
        $elementPlaceholder = $variableExports->create('ELEMENT');

        $accessor = $this->namedDomIdentifierHandler->handle(
            new NamedDomElementIdentifier($domIdentifier, $elementPlaceholder)
        );

        return new CodeBlock([
            $accessor,
            new Statement(sprintf(
                '%s->%s()',
                (string) $elementPlaceholder,
                $action->getType()
            )),
        ]);
    }
}
