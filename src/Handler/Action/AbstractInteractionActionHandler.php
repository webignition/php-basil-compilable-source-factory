<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;

abstract class AbstractInteractionActionHandler implements HandlerInterface
{
    private $variableAssignmentFactory;
    private $namedDomIdentifierHandler;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        HandlerInterface $namedDomIdentifierHandler
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
    }

    abstract protected function getHandledActionType(): string;
    abstract protected function getElementActionMethod(): string;

    public function handles(object $model): bool
    {
        return $model instanceof InteractionActionInterface && $this->getHandledActionType() === $model->getType();
    }

    /**
     * @param object $model
     *
     * @return StatementListInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createStatementList(object $model): StatementListInterface
    {
        if (!$model instanceof InteractionActionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if ($this->getHandledActionType() !== $model->getType()) {
            throw new NonTranspilableModelException($model);
        }

        $identifier = $model->getIdentifier();

        if (!$identifier instanceof DomIdentifierInterface) {
            throw new NonTranspilableModelException($model);
        }

        $variableExports = new VariablePlaceholderCollection();
        $elementPlaceholder = $variableExports->create('ELEMENT');

        $accessor = $this->namedDomIdentifierHandler->createStatementList(new NamedDomElementIdentifier(
            $identifier,
            $elementPlaceholder
        ));

        return new StatementList(array_merge(
            $accessor->getStatementObjects(),
            [
                new Statement(sprintf(
                    '%s->%s()',
                    (string) $elementPlaceholder,
                    $this->getElementActionMethod()
                ))
            ]
        ));
    }
}
