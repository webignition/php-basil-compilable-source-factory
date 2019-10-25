<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\InputActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;

class SetActionHandler implements HandlerInterface
{
    private $variableAssignmentFactory;
    private $webDriverElementMutatorCallFactory;
    private $scalarValueHandler;
    private $namedDomIdentifierHandler;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        WebDriverElementMutatorCallFactory $webDriverElementMutatorCallFactory,
        HandlerInterface $scalarValueHandler,
        HandlerInterface $namedDomIdentifierHandler
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->webDriverElementMutatorCallFactory = $webDriverElementMutatorCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
    }

    public static function createHandler(): HandlerInterface
    {
        return new SetActionHandler(
            VariableAssignmentFactory::createFactory(),
            WebDriverElementMutatorCallFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            NamedDomIdentifierHandler::createHandler()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof InputActionInterface;
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws UnsupportedModelException
     */
    public function createSource(object $model): SourceInterface
    {
        if (!$model instanceof InputActionInterface) {
            throw new UnsupportedModelException($model);
        }

        $identifier = $model->getIdentifier();

        if (!$identifier instanceof DomIdentifierInterface) {
            throw new UnsupportedModelException($model);
        }

        if (null !== $identifier->getAttributeName()) {
            throw new UnsupportedModelException($model);
        }

        $variableExports = new VariablePlaceholderCollection();
        $collectionPlaceholder = $variableExports->create('COLLECTION');
        $valuePlaceholder = $variableExports->create('VALUE');

        $collectionAssignment = $this->namedDomIdentifierHandler->createSource(new NamedDomIdentifier(
            $identifier,
            $collectionPlaceholder
        ));

        $value = $model->getValue();

        if ($value instanceof DomIdentifierValueInterface) {
            $valueAccessor = $this->namedDomIdentifierHandler->createSource(
                new NamedDomIdentifierValue($value, $valuePlaceholder)
            );

            $valueAccessor->mutateLastStatement(function (string $content) use ($valuePlaceholder) {
                return str_replace((string) $valuePlaceholder . ' = ', '', $content);
            });
        } else {
            $valueAccessor = $this->scalarValueHandler->createSource($value);
        }

        $valueAssignment = $this->variableAssignmentFactory->createForValueAccessor($valueAccessor, $valuePlaceholder);

        $mutationCall = $this->webDriverElementMutatorCallFactory->createSetValueCall(
            $collectionPlaceholder,
            $valuePlaceholder
        );

        $statementList = new StatementList([]);
        $statementList->addStatements($collectionAssignment->getStatementObjects());
        $statementList->addStatements($valueAssignment->getStatementObjects());
        $statementList->addStatement($mutationCall);

        return $statementList;
    }
}
