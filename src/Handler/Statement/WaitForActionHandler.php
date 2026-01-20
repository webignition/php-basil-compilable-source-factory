<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use SmartAssert\DomIdentifier\AttributeIdentifierInterface;
use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use SmartAssert\DomIdentifier\FactoryInterface as DomIdentifierFactoryInterface;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\StatementInterface;

class WaitForActionHandler implements StatementHandlerInterface
{
    public function __construct(
        private DomIdentifierFactoryInterface $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ArgumentFactory $argumentFactory
    ) {}

    public static function createHandler(): WaitForActionHandler
    {
        return new WaitForActionHandler(
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            ArgumentFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(StatementInterface $statement): ?StatementHandlerComponents
    {
        if (!$statement instanceof ActionInterface) {
            return null;
        }

        if ('wait-for' !== $statement->getType()) {
            return null;
        }

        $identifier = (string) $statement->getIdentifier();

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

        return new StatementHandlerComponents(
            new Statement(
                new AssignmentExpression(
                    new VariableDependency(VariableName::PANTHER_CRAWLER),
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableName::PANTHER_CLIENT),
                        'waitFor',
                        new MethodArguments(
                            $this->argumentFactory->create($domIdentifier->getLocator())
                        )
                    )
                )
            )
        );
    }
}
