<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\ComparisonExpression;
use webignition\BasilCompilableSource\Line\CompositeExpression;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifierValue;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\WaitActionInterface;

class WaitActionHandler
{
    private const DURATION_PLACEHOLDER = 'DURATION';
    private const MICROSECONDS_PER_MILLISECOND = 1000;

    private $scalarValueHandler;
    private $domIdentifierHandler;
    private $accessorDefaultValueFactory;
    private $domIdentifierFactory;
    private $identifierTypeAnalyser;

    public function __construct(
        ScalarValueHandler $scalarValueHandler,
        DomIdentifierHandler $domIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser
    ) {
        $this->scalarValueHandler = $scalarValueHandler;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
    }

    public static function createHandler(): WaitActionHandler
    {
        return new WaitActionHandler(
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
        $durationPlaceholder = VariablePlaceholder::createExport(self::DURATION_PLACEHOLDER);

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
                new DomIdentifierValue($durationIdentifier)
            );
        } else {
            $durationAccessor = $this->scalarValueHandler->handle($duration);
        }

        $durationAssignment = new AssignmentStatement(
            $durationPlaceholder,
            new ComparisonExpression(
                $durationAccessor,
                new LiteralExpression((string) ($this->accessorDefaultValueFactory->createInteger($duration) ?? 0)),
                '??',
                'int'
            )
        );

        $sleepInvocation = new Statement(
            new MethodInvocation(
                'usleep',
                [
                    new CompositeExpression([
                        $durationPlaceholder,
                        new LiteralExpression(' * '),
                        new LiteralExpression((string) self::MICROSECONDS_PER_MILLISECOND)
                    ]),
                ]
            )
        );

        return new CodeBlock([
            $durationAssignment,
            $sleepInvocation,
        ]);
    }
}
