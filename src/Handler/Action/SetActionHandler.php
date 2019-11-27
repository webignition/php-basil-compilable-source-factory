<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\InputActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;

class SetActionHandler
{
    private $variableAssignmentFactory;
    private $webDriverElementMutatorCallFactory;
    private $scalarValueHandler;
    private $namedDomIdentifierHandler;
    private $accessorDefaultValueFactory;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        WebDriverElementMutatorCallFactory $webDriverElementMutatorCallFactory,
        ScalarValueHandler $scalarValueHandler,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->webDriverElementMutatorCallFactory = $webDriverElementMutatorCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
    }

    public static function createHandler(): SetActionHandler
    {
        return new SetActionHandler(
            VariableAssignmentFactory::createFactory(),
            WebDriverElementMutatorCallFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            NamedDomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory()
        );
    }

    /**
     * @param InputActionInterface $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(InputActionInterface $action): CodeBlockInterface
    {
        $identifier = $action->getIdentifier();

        if (!$identifier instanceof DomIdentifierInterface) {
            throw new UnsupportedModelException($action);
        }

        if (null !== $identifier->getAttributeName()) {
            throw new UnsupportedModelException($action);
        }

        $variableExports = new VariablePlaceholderCollection();
        $collectionPlaceholder = $variableExports->create('COLLECTION');
        $valuePlaceholder = $variableExports->create('VALUE');

        $collectionAssignment = $this->namedDomIdentifierHandler->handle(new NamedDomIdentifier(
            $identifier,
            $collectionPlaceholder
        ));

        $value = $action->getValue();

        if ($value instanceof DomIdentifierValueInterface) {
            $valueAccessor = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($value, $valuePlaceholder)
            );

            $valueAccessor->mutateLastStatement(function (string $content) use ($valuePlaceholder) {
                return str_replace((string) $valuePlaceholder . ' = ', '', $content);
            });
        } else {
//            $valueAccessor = $this->scalarValueHandler->handle($value);
            // @todo fix in #211
            $valueAccessor = $this->scalarValueHandler->handle('Fix in #211');
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
