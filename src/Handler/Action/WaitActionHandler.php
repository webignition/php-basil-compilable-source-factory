<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\WaitActionInterface;

class WaitActionHandler
{
    private const DURATION_PLACEHOLDER = 'DURATION';
    private const MICROSECONDS_PER_MILLISECOND = 1000;

    private $variableAssignmentFactory;
    private $scalarValueHandler;
    private $namedDomIdentifierHandler;
    private $accessorDefaultValueFactory;
    private $domIdentifierFactory;
    private $identifierTypeAnalyser;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        ScalarValueHandler $scalarValueHandler,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
    }

    public static function createHandler(): WaitActionHandler
    {
        return new WaitActionHandler(
            VariableAssignmentFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            NamedDomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            new IdentifierTypeAnalyser()
        );
    }

    /**
     * @param WaitActionInterface $waitAction
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedIdentifierException
     * @throws UnsupportedValueException
     */
    public function handle(WaitActionInterface $waitAction): CodeBlockInterface
    {
        $variableExports = new VariablePlaceholderCollection();
        $durationPlaceholder = $variableExports->create(self::DURATION_PLACEHOLDER);

        $duration = $waitAction->getDuration();

        if (ctype_digit($duration)) {
            $duration = '"' . $duration . '"';
        }

        if (
            $this->identifierTypeAnalyser->isDomIdentifier($duration) ||
            $this->identifierTypeAnalyser->isDescendantDomIdentifier($duration)
        ) {
            $durationIdentifier = $this->domIdentifierFactory->create($duration);

            $durationAccessor = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($durationIdentifier, $durationPlaceholder)
            );

            $durationAccessor->mutateLastStatement(function (string $content) use ($durationPlaceholder) {
                return str_replace((string) $durationPlaceholder . ' = ', '', $content);
            });
        } else {
            $durationAccessor = $this->scalarValueHandler->handle($duration);
        }

        $durationAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $durationAccessor,
            $durationPlaceholder,
            $this->accessorDefaultValueFactory->create($duration) ?? 0
        );

        return new CodeBlock([
            $durationAssignment,
            new Statement(sprintf(
                'usleep(%s * %s)',
                (string) $durationPlaceholder,
                self::MICROSECONDS_PER_MILLISECOND
            )),
        ]);
    }
}
