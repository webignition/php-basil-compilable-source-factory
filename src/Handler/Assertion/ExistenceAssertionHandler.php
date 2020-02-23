<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\ComparisonExpression;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\Line\Statement\StatementInterface;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\AssertionFailureMessageFactory;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierExistenceHandler;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifierValue;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class ExistenceAssertionHandler
{
    private $scalarValueHandler;
    private $domIdentifierHandler;
    private $identifierTypeAnalyser;
    private $domCrawlerNavigatorCallFactory;
    private $valueTypeIdentifier;
    private $domIdentifierExistenceHandler;
    private $domIdentifierFactory;
    private $assertionFailureMessageFactory;
    private $assertionMethodInvocationFactory;

    public function __construct(
        ScalarValueHandler $scalarValueHandler,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        DomIdentifierHandler $domIdentifierHandler,
        ValueTypeIdentifier $valueTypeIdentifier,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        DomIdentifierExistenceHandler $domIdentifierExistenceHandler,
        DomIdentifierFactory $domIdentifierFactory,
        AssertionFailureMessageFactory $assertionFailureMessageFactory,
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory
    ) {
        $this->scalarValueHandler = $scalarValueHandler;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->valueTypeIdentifier = $valueTypeIdentifier;
        $this->domIdentifierExistenceHandler = $domIdentifierExistenceHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->assertionFailureMessageFactory = $assertionFailureMessageFactory;
        $this->assertionMethodInvocationFactory = $assertionMethodInvocationFactory;
    }

    public static function createHandler(): ExistenceAssertionHandler
    {
        return new ExistenceAssertionHandler(
            ScalarValueHandler::createHandler(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            DomIdentifierHandler::createHandler(),
            new ValueTypeIdentifier(),
            IdentifierTypeAnalyser::create(),
            DomIdentifierExistenceHandler::createHandler(),
            DomIdentifierFactory::createFactory(),
            AssertionFailureMessageFactory::createFactory(),
            AssertionMethodInvocationFactory::createFactory()
        );
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): CodeBlockInterface
    {
        $valuePlaceholder = VariablePlaceholder::createExport(VariableNames::EXAMINED_VALUE);
        $identifier = $assertion->getIdentifier();

        if ($this->valueTypeIdentifier->isScalarValue($identifier)) {
            return new CodeBlock([
                new AssignmentStatement(
                    $valuePlaceholder,
                    new ComparisonExpression(
                        $this->scalarValueHandler->handle($assertion->getIdentifier()),
                        new LiteralExpression('null'),
                        '??'
                    )
                ),
                new AssignmentStatement(
                    $valuePlaceholder,
                    new ComparisonExpression(
                        $valuePlaceholder,
                        new LiteralExpression('null'),
                        '!=='
                    )
                ),
                $this->createAssertionStatement($assertion, $valuePlaceholder),
            ]);
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
            if (null === $domIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
            }

            if (!$domIdentifier instanceof AttributeIdentifierInterface) {
                return new CodeBlock([
                    new AssignmentStatement(
                        $valuePlaceholder,
                        $this->domCrawlerNavigatorCallFactory->createHasCall($domIdentifier)
                    ),
                    $this->createAssertionStatement($assertion, $valuePlaceholder),
                ]);
            }

            return new CodeBlock([
                $this->domIdentifierExistenceHandler->createForElement(
                    $domIdentifier,
                    $this->assertionFailureMessageFactory->createForAssertion($assertion)
                ),
                new AssignmentStatement(
                    $valuePlaceholder,
                    $this->domIdentifierHandler->handle(new DomIdentifierValue($domIdentifier))
                ),
                new AssignmentStatement(
                    $valuePlaceholder,
                    new ComparisonExpression(
                        $valuePlaceholder,
                        new LiteralExpression('null'),
                        '!=='
                    )
                ),
                $this->createAssertionStatement($assertion, $valuePlaceholder),
            ]);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
    }

    private function createAssertionStatement(
        AssertionInterface $assertion,
        VariablePlaceholder $valuePlaceholder
    ): StatementInterface {
        return new Statement(
            $this->assertionMethodInvocationFactory->create(
                'exists' === $assertion->getComparison() ? 'assertTrue' : 'assertFalse',
                [
                    $valuePlaceholder
                ],
                $this->assertionFailureMessageFactory->createForAssertion($assertion)
            )
        );
    }
}
