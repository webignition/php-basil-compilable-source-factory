<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilationSource\MutableListLineListInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\WaitActionInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;

class WaitActionHandler implements HandlerInterface
{
    const DURATION_PLACEHOLDER = 'DURATION';
    const MICROSECONDS_PER_MILLISECOND = 1000;

    private $variableAssignmentFactory;
    private $scalarValueHandler;
    private $namedDomIdentifierHandler;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        HandlerInterface $scalarValueHandler,
        HandlerInterface $namedDomIdentifierHandler
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
    }

    public static function createHandler(): HandlerInterface
    {
        return new WaitActionHandler(
            VariableAssignmentFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            NamedDomIdentifierHandler::createHandler()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof WaitActionInterface;
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws UnsupportedModelException
     */
    public function handle(object $model): SourceInterface
    {
        if (!$model instanceof WaitActionInterface) {
            throw new UnsupportedModelException($model);
        }

        $variableExports = new VariablePlaceholderCollection();
        $durationPlaceholder = $variableExports->create(self::DURATION_PLACEHOLDER);

        $duration = $model->getDuration();

        if ($duration instanceof DomIdentifierValueInterface) {
            $durationAccessor = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($duration, $durationPlaceholder)
            );

            if ($durationAccessor instanceof MutableListLineListInterface) {
                $durationAccessor->mutateLastStatement(function (string $content) use ($durationPlaceholder) {
                    return str_replace((string) $durationPlaceholder . ' = ', '', $content);
                });
            }
        } else {
            $durationAccessor = $this->scalarValueHandler->handle($duration);
        }

        $durationAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $durationAccessor,
            $durationPlaceholder,
            'int',
            '0'
        );

        return new LineList([
            $durationAssignment,
            new Statement(sprintf(
                'usleep(%s * %s)',
                (string) $durationPlaceholder,
                self::MICROSECONDS_PER_MILLISECOND
            )),
        ]);
    }
}
