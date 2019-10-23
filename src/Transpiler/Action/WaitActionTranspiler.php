<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Transpiler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Transpiler\Value\ScalarValueTranspiler;
use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\WaitActionInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;

class WaitActionTranspiler implements HandlerInterface
{
    const DURATION_PLACEHOLDER = 'DURATION';
    const MICROSECONDS_PER_MILLISECOND = 1000;

    private $variableAssignmentFactory;
    private $scalarValueTranspiler;
    private $namedDomIdentifierHandler;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        HandlerInterface $scalarValueTranspiler,
        HandlerInterface $namedDomIdentifierHandler
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->scalarValueTranspiler = $scalarValueTranspiler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
    }

    public static function createHandler(): HandlerInterface
    {
        return new WaitActionTranspiler(
            VariableAssignmentFactory::createFactory(),
            ScalarValueTranspiler::createHandler(),
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
     * @throws NonTranspilableModelException
     */
    public function createSource(object $model): SourceInterface
    {
        if (!$model instanceof WaitActionInterface) {
            throw new NonTranspilableModelException($model);
        }

        $variableExports = new VariablePlaceholderCollection();
        $durationPlaceholder = $variableExports->create(self::DURATION_PLACEHOLDER);

        $duration = $model->getDuration();

        if ($duration instanceof DomIdentifierValueInterface) {
            $durationAccessor = $this->namedDomIdentifierHandler->createSource(
                new NamedDomIdentifierValue($duration, $durationPlaceholder)
            );

            $durationAccessor->mutateStatement(3, function ($statement) use ($durationPlaceholder) {
                return str_replace((string) $durationPlaceholder . ' = ', '', $statement);
            });
        } else {
            $durationAccessor = $this->scalarValueTranspiler->createSource($duration);
        }

        $durationAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $durationAccessor,
            $durationPlaceholder,
            'int',
            '0'
        );

        $waitStatement = sprintf(
            'usleep(%s * %s)',
            (string) $durationPlaceholder,
            self::MICROSECONDS_PER_MILLISECOND
        );

        return (new Source())
            ->withPredecessors([$durationAssignment])
            ->withStatements([$waitStatement]);
    }
}
