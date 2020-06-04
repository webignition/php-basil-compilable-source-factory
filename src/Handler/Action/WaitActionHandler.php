<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\CastExpression;
use webignition\BasilCompilableSource\Line\ComparisonExpression;
use webignition\BasilCompilableSource\Line\CompositeExpression;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\ResolvablePlaceholder;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\ActionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class WaitActionHandler
{
    private const DURATION_PLACEHOLDER = 'DURATION';
    private const MICROSECONDS_PER_MILLISECOND = 1000;

    private ScalarValueHandler $scalarValueHandler;
    private DomIdentifierHandler $domIdentifierHandler;
    private AccessorDefaultValueFactory $accessorDefaultValueFactory;
    private DomIdentifierFactory $domIdentifierFactory;
    private IdentifierTypeAnalyser $identifierTypeAnalyser;
    private ElementIdentifierSerializer $elementIdentifierSerializer;

    public function __construct(
        ScalarValueHandler $scalarValueHandler,
        DomIdentifierHandler $domIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        ElementIdentifierSerializer $elementIdentifierSerializer
    ) {
        $this->scalarValueHandler = $scalarValueHandler;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->elementIdentifierSerializer = $elementIdentifierSerializer;
    }

    public static function createHandler(): WaitActionHandler
    {
        return new WaitActionHandler(
            ScalarValueHandler::createHandler(),
            DomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            ElementIdentifierSerializer::createSerializer()
        );
    }

    /**
     * @param ActionInterface $waitAction
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $waitAction): CodeBlockInterface
    {
        $durationPlaceholder = ResolvablePlaceholder::createExport(self::DURATION_PLACEHOLDER);

        $duration = $waitAction->getValue();

        if (ctype_digit($duration)) {
            $duration = '"' . $duration . '"';
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($duration)) {
            $durationIdentifier = $this->domIdentifierFactory->createFromIdentifierString($duration);
            if (null === $durationIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $duration);
            }

            if ($durationIdentifier instanceof AttributeIdentifierInterface) {
                $durationAccessor = $this->domIdentifierHandler->handleAttributeValue(
                    $this->elementIdentifierSerializer->serialize($durationIdentifier),
                    $durationIdentifier->getAttributeName()
                );
            } else {
                $durationAccessor = $this->domIdentifierHandler->handleElementValue(
                    $this->elementIdentifierSerializer->serialize($durationIdentifier)
                );
            }
        } else {
            $durationAccessor = $this->scalarValueHandler->handle($duration);
        }

        $durationAssignment = new AssignmentStatement(
            $durationPlaceholder,
            new CastExpression(
                new ComparisonExpression(
                    $durationAccessor,
                    new LiteralExpression((string) ($this->accessorDefaultValueFactory->createInteger($duration) ?? 0)),
                    '??'
                ),
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
