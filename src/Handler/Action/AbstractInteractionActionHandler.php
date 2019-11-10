<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
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
     * @return BlockInterface
     *
     * @throws UnsupportedModelException
     */
    public function handle(object $model): BlockInterface
    {
        if (!$model instanceof InteractionActionInterface) {
            throw new UnsupportedModelException($model);
        }

        if ($this->getHandledActionType() !== $model->getType()) {
            throw new UnsupportedModelException($model);
        }

        $identifier = $model->getIdentifier();

        if (!$identifier instanceof DomIdentifierInterface) {
            throw new UnsupportedModelException($model);
        }

        $variableExports = new VariablePlaceholderCollection();
        $elementPlaceholder = $variableExports->create('ELEMENT');

        $accessor = $this->namedDomIdentifierHandler->handle(new NamedDomElementIdentifier(
            $identifier,
            $elementPlaceholder
        ));

        return new Block([
            $accessor,
            new Statement(sprintf(
                '%s->%s()',
                (string) $elementPlaceholder,
                $this->getElementActionMethod()
            )),
        ]);
    }
}
