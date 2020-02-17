<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifierValue;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\WaitActionInterface;

class WaitActionHandler
{
    private const DURATION_PLACEHOLDER = 'DURATION';
    private const MICROSECONDS_PER_MILLISECOND = 1000;

    private $variableAssignmentFactory;
    private $scalarValueHandler;
    private $domIdentifierHandler;
    private $accessorDefaultValueFactory;
    private $domIdentifierFactory;
    private $identifierTypeAnalyser;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        ScalarValueHandler $scalarValueHandler,
        DomIdentifierHandler $domIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
    }

    public static function createHandler(): WaitActionHandler
    {
        return new WaitActionHandler(
            VariableAssignmentFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            DomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create()
        );
    }

    /**
     * @param WaitActionInterface $waitAction
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(WaitActionInterface $waitAction): CodeBlockInterface
    {
        $variableExports = new VariablePlaceholderCollection();
        $durationPlaceholder = $variableExports->create(self::DURATION_PLACEHOLDER);

        $duration = $waitAction->getDuration();

        if (ctype_digit($duration)) {
            $duration = '"' . $duration . '"';
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($duration)) {
            $durationIdentifier = $this->domIdentifierFactory->createFromIdentifierString($duration);
            if (null === $durationIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $duration);
            }

            $durationAccessor = $this->domIdentifierHandler->handle(
                new DomIdentifierValue($durationIdentifier, $durationPlaceholder)
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
            $this->accessorDefaultValueFactory->createInteger($duration) ?? 0
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
