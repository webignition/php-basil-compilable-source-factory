<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AssertionFailureMessageFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierExistenceHandler;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class ExistenceComparisonHandler
{
    private $assertionCallFactory;
    private $scalarValueHandler;
    private $namedDomIdentifierHandler;
    private $identifierTypeAnalyser;
    private $domCrawlerNavigatorCallFactory;
    private $valueTypeIdentifier;
    private $domIdentifierExistenceHandler;
    private $domIdentifierFactory;
    private $assertionFailureMessageFactory;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        ScalarValueHandler $scalarValueHandler,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        ValueTypeIdentifier $valueTypeIdentifier,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        DomIdentifierExistenceHandler $domIdentifierExistenceHandler,
        DomIdentifierFactory $domIdentifierFactory,
        AssertionFailureMessageFactory $assertionFailureMessageFactory
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->valueTypeIdentifier = $valueTypeIdentifier;
        $this->domIdentifierExistenceHandler = $domIdentifierExistenceHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->assertionFailureMessageFactory = $assertionFailureMessageFactory;
    }

    public static function createHandler(): ExistenceComparisonHandler
    {
        return new ExistenceComparisonHandler(
            AssertionCallFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            NamedDomIdentifierHandler::createHandler(),
            new ValueTypeIdentifier(),
            IdentifierTypeAnalyser::create(),
            DomIdentifierExistenceHandler::createHandler(),
            DomIdentifierFactory::createFactory(),
            AssertionFailureMessageFactory::createFactory()
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
        $valuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $identifier = $assertion->getIdentifier();

        if ($this->valueTypeIdentifier->isScalarValue($identifier)) {
            $accessor = $this->scalarValueHandler->handle($identifier);

            $accessor->mutateLastStatement(function (string $content) {
                return $content . ' ?? null';
            });

            $assignment = clone $accessor;

            $assignment->mutateLastStatement(function (string $content) use ($valuePlaceholder) {
                return $valuePlaceholder . ' = ' . $content;
            });

            $assignment->addVariableExportsToLastStatement(new VariablePlaceholderCollection([
                $valuePlaceholder,
            ]));

            $existence = new CodeBlock([
                $assignment,
                new Statement(sprintf('%s = %s !== null', $valuePlaceholder, $valuePlaceholder)),
            ]);

            return $this->createAssertionCall($assertion, $existence, $valuePlaceholder);
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
            if (null === $domIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
            }

            if (!$domIdentifier instanceof AttributeIdentifierInterface) {
                $accessor = $this->domCrawlerNavigatorCallFactory->createHasCall($domIdentifier);

                $assignment = new CodeBlock([
                    $accessor,
                ]);

                $assignment->mutateLastStatement(function (string $content) use ($valuePlaceholder) {
                    return $valuePlaceholder . ' = ' . $content;
                });
                $assignment->addVariableExportsToLastStatement(new VariablePlaceholderCollection([
                    $valuePlaceholder,
                ]));

                return $this->createAssertionCall($assertion, new CodeBlock([$assignment]), $valuePlaceholder);
            }

            $elementExistence = $this->domIdentifierExistenceHandler->createForElement(
                $domIdentifier,
                $this->assertionFailureMessageFactory->createForAssertion($assertion)
            );

            $access = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($domIdentifier, $valuePlaceholder)
            );

            $accessor = new CodeBlock([
                $elementExistence,
                $access,
            ]);

            $existence = new CodeBlock([
                $accessor,
                new Statement(sprintf('%s = %s !== null', $valuePlaceholder, $valuePlaceholder)),
            ]);

            return $this->createAssertionCall($assertion, $existence, $valuePlaceholder);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
    }

    private function createAssertionCall(
        AssertionInterface $assertion,
        CodeBlockInterface $block,
        VariablePlaceholder $valuePlaceholder
    ): CodeBlockInterface {
        $comparison = $assertion->getComparison();

        $assertionTemplate = 'exists' === $comparison
            ? AssertionCallFactory::ASSERT_TRUE_METHOD
            : AssertionCallFactory::ASSERT_FALSE_METHOD;

        return $this->assertionCallFactory->createValueExistenceAssertionCall(
            $block,
            $valuePlaceholder,
            $assertionTemplate,
            $this->assertionFailureMessageFactory->createForAssertion($assertion)
        );
    }
}
