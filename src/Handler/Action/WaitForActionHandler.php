<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\InteractionActionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class WaitForActionHandler
{
    private SingleQuotedStringEscaper $singleQuotedStringEscaper;
    private DomIdentifierFactory $domIdentifierFactory;
    private IdentifierTypeAnalyser $identifierTypeAnalyser;

    public function __construct(
        SingleQuotedStringEscaper $singleQuotedStringEscaper,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser
    ) {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
    }

    public static function createHandler(): WaitForActionHandler
    {
        return new WaitForActionHandler(
            SingleQuotedStringEscaper::create(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create()
        );
    }

    /**
     * @param InteractionActionInterface $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(InteractionActionInterface $action): CodeBlockInterface
    {
        $identifier = $action->getIdentifier();

        if (!$this->identifierTypeAnalyser->isDomIdentifier($identifier)) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        return new CodeBlock([
            new AssignmentStatement(
                VariablePlaceholder::createDependency(VariableNames::PANTHER_CRAWLER),
                new ObjectMethodInvocation(
                    VariablePlaceholder::createDependency(VariableNames::PANTHER_CLIENT),
                    'waitFor',
                    [
                        new LiteralExpression(
                            '\'' . $this->singleQuotedStringEscaper->escape($domIdentifier->getLocator()) . '\''
                        )
                    ]
                )
            )
        ]);
    }
}
