<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierExistenceHandler;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\InputActionInterface;

class SetActionHandler
{
    private $variableAssignmentFactory;
    private $webDriverElementMutatorCallFactory;
    private $scalarValueHandler;
    private $namedDomIdentifierHandler;
    private $accessorDefaultValueFactory;
    private $domIdentifierFactory;
    private $identifierTypeAnalyser;
    private $domIdentifierExistenceHandler;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        WebDriverElementMutatorCallFactory $webDriverElementMutatorCallFactory,
        ScalarValueHandler $scalarValueHandler,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        DomIdentifierExistenceHandler $domIdentifierExistenceHandler
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->webDriverElementMutatorCallFactory = $webDriverElementMutatorCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->domIdentifierExistenceHandler = $domIdentifierExistenceHandler;
    }

    public static function createHandler(): SetActionHandler
    {
        return new SetActionHandler(
            VariableAssignmentFactory::createFactory(),
            WebDriverElementMutatorCallFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            NamedDomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            new IdentifierTypeAnalyser(),
            DomIdentifierExistenceHandler::createHandler()
        );
    }

    /**
     * @param InputActionInterface $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedIdentifierException
     * @throws UnsupportedValueException
     */
    public function handle(InputActionInterface $action): CodeBlockInterface
    {
        $identifier = $action->getIdentifier();

        if (!$this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            throw new UnsupportedIdentifierException($identifier);
        }

        $value = $action->getValue();
        $domIdentifier = $this->domIdentifierFactory->create($identifier);

        if (null !== $domIdentifier->getAttributeName()) {
            throw new UnsupportedIdentifierException($identifier);
        }

        $variableExports = new VariablePlaceholderCollection();
        $collectionPlaceholder = $variableExports->create('COLLECTION');
        $valuePlaceholder = $variableExports->create('VALUE');

        $collectionExistence = $this->domIdentifierExistenceHandler->createForCollection($domIdentifier);
        $collectionAccess = $this->namedDomIdentifierHandler->handle(
            new NamedDomIdentifier($domIdentifier, $collectionPlaceholder)
        );

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
            $valueDomIdentifier = $this->domIdentifierFactory->create($value);

            $valueExistence = $this->domIdentifierExistenceHandler->createForElementOrCollection($valueDomIdentifier);

            $valueAccess = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($valueDomIdentifier, $valuePlaceholder)
            );

            $valueAccessor = new CodeBlock([
                $valueExistence,
                $valueAccess,
            ]);

            $valueAccessor->mutateLastStatement(function (string $content) use ($valuePlaceholder) {
                return str_replace((string) $valuePlaceholder . ' = ', '', $content);
            });
        } else {
            $valueAccessor = $this->scalarValueHandler->handle($value);
        }

        $valueAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $valueAccessor,
            $valuePlaceholder,
            $this->accessorDefaultValueFactory->createString($value)
        );

        $mutationCall = $this->webDriverElementMutatorCallFactory->createSetValueCall(
            $collectionPlaceholder,
            $valuePlaceholder
        );

        return new CodeBlock([
            $collectionExistence,
            $collectionAccess,
            $valueAssignment,
            $mutationCall
        ]);
    }
}
