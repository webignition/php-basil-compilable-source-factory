<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\IdentifierTypeFinder;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModels\Action\InputActionInterface;

class SetActionHandler
{
    private $variableAssignmentFactory;
    private $webDriverElementMutatorCallFactory;
    private $scalarValueHandler;
    private $namedDomIdentifierHandler;
    private $accessorDefaultValueFactory;
    private $domIdentifierFactory;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        WebDriverElementMutatorCallFactory $webDriverElementMutatorCallFactory,
        ScalarValueHandler $scalarValueHandler,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        DomIdentifierFactory $domIdentifierFactory
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->webDriverElementMutatorCallFactory = $webDriverElementMutatorCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
    }

    public static function createHandler(): SetActionHandler
    {
        return new SetActionHandler(
            VariableAssignmentFactory::createFactory(),
            WebDriverElementMutatorCallFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            NamedDomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory(),
            DomIdentifierFactory::createFactory()
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

        if (
            !IdentifierTypeFinder::isDomIdentifier($identifier) &&
            !IdentifierTypeFinder::isDescendantDomIdentifier($identifier)
        ) {
            throw new UnsupportedIdentifierException($identifier);
        }

        $value = $action->getValue();

        if (null === $value) {
            throw new UnsupportedValueException($value);
        }

        $domIdentifier = $this->domIdentifierFactory->create($identifier);

        if (null !== $domIdentifier->getAttributeName()) {
            throw new UnsupportedIdentifierException($identifier);
        }

        $variableExports = new VariablePlaceholderCollection();
        $collectionPlaceholder = $variableExports->create('COLLECTION');
        $valuePlaceholder = $variableExports->create('VALUE');

        $collectionAssignment = $this->namedDomIdentifierHandler->handle(
            new NamedDomIdentifier($domIdentifier, $collectionPlaceholder)
        );

        if (
            IdentifierTypeFinder::isDomIdentifier($value) ||
            IdentifierTypeFinder::isDescendantDomIdentifier($value)
        ) {
            $valueDomIdentifier = $this->domIdentifierFactory->create($value);

            $valueAccessor = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($valueDomIdentifier, $valuePlaceholder)
            );

            $valueAccessor->mutateLastStatement(function (string $content) use ($valuePlaceholder) {
                return str_replace((string) $valuePlaceholder . ' = ', '', $content);
            });
        } else {
            $valueAccessor = $this->scalarValueHandler->handle($value);
        }

        $valueAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $valueAccessor,
            $valuePlaceholder,
            $this->accessorDefaultValueFactory->create($value)
        );

        $mutationCall = $this->webDriverElementMutatorCallFactory->createSetValueCall(
            $collectionPlaceholder,
            $valuePlaceholder
        );

        return new CodeBlock([
            $collectionAssignment,
            $valueAssignment,
            $mutationCall
        ]);
    }
}
