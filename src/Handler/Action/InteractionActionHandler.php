<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;

class InteractionActionHandler
{
    private $variableAssignmentFactory;
    private $namedDomIdentifierHandler;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        NamedDomIdentifierHandler $namedDomIdentifierHandler
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
    }

    public static function createHandler(): InteractionActionHandler
    {
        return new InteractionActionHandler(
            VariableAssignmentFactory::createFactory(),
            NamedDomIdentifierHandler::createHandler()
        );
    }

    /**
     * @param InteractionActionInterface $action
     *
     * @return BlockInterface
     *
     * @throws UnsupportedModelException
     */
    public function handle(InteractionActionInterface $action): BlockInterface
    {
        $identifier = $action->getIdentifier();

        if (!$identifier instanceof DomIdentifierInterface) {
            throw new UnsupportedModelException($action);
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
                $action->getType()
            )),
        ]);
    }
}
