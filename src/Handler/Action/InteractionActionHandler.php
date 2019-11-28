<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnknownIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedActionException;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\IdentifierTypeFinder;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilDataStructure\Action\ActionInterface;
use webignition\BasilDataStructure\Action\InteractionAction;

class InteractionActionHandler
{
    private $variableAssignmentFactory;
    private $namedDomIdentifierHandler;
    private $domIdentifierFactory;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        DomIdentifierFactory $domIdentifierFactory
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
    }

    public static function createHandler(): InteractionActionHandler
    {
        return new InteractionActionHandler(
            VariableAssignmentFactory::createFactory(),
            NamedDomIdentifierHandler::createHandler(),
            DomIdentifierFactory::createFactory()
        );
    }

    /**
     * @param InteractionAction $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnknownIdentifierException
     * @throws UnsupportedActionException
     */
    public function handle(InteractionAction $action): CodeBlockInterface
    {
        $identifier = $action->getIdentifier();

        if (
            !IdentifierTypeFinder::isDomIdentifier($identifier) &&
            !IdentifierTypeFinder::isDescendantDomIdentifier($identifier)
        ) {
            throw new UnsupportedActionException($action);
        }

        $domIdentifier = $this->domIdentifierFactory->create($identifier);

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
