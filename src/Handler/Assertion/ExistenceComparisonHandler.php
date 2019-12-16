<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierExistenceHandler;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\AssertionInterface;

class ExistenceComparisonHandler
{
    private $assertionCallFactory;
    private $scalarValueHandler;
    private $namedDomIdentifierHandler;
    private $domIdentifierFactory;
    private $identifierTypeAnalyser;
    private $domCrawlerNavigatorCallFactory;
    private $valueTypeIdentifier;
    private $domIdentifierExistenceHandler;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        ScalarValueHandler $scalarValueHandler,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        ValueTypeIdentifier $valueTypeIdentifier,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        DomIdentifierExistenceHandler $domIdentifierExistenceHandler
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->valueTypeIdentifier = $valueTypeIdentifier;
        $this->domIdentifierExistenceHandler = $domIdentifierExistenceHandler;
    }

    public static function createHandler(): ExistenceComparisonHandler
    {
        return new ExistenceComparisonHandler(
            AssertionCallFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            NamedDomIdentifierHandler::createHandler(),
            new ValueTypeIdentifier(),
            DomIdentifierFactory::createFactory(),
            new IdentifierTypeAnalyser(),
            DomIdentifierExistenceHandler::createHandler()
        );
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedIdentifierException
     * @throws UnsupportedValueException
     */
    public function handle(AssertionInterface $assertion): CodeBlockInterface
    {
        $valuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $identifier = $assertion->getIdentifier();
        $comparison = $assertion->getComparison();

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

            return $this->createAssertionCall($comparison, $existence, $valuePlaceholder);
        }

        if (
            $this->identifierTypeAnalyser->isDomIdentifier($identifier) ||
            $this->identifierTypeAnalyser->isDescendantDomIdentifier($identifier)
        ) {
            $domIdentifier = $this->domIdentifierFactory->create($identifier);

            if (null === $domIdentifier->getAttributeName()) {
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

                return $this->createAssertionCall($comparison, new CodeBlock([$assignment]), $valuePlaceholder);
            }

            $elementExistence = $this->domIdentifierExistenceHandler->handle($domIdentifier, false);

            $access = $this->namedDomIdentifierHandler->handleAccess(
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

            return $this->createAssertionCall($comparison, $existence, $valuePlaceholder);
        }

        throw new UnsupportedIdentifierException($identifier);
    }

    private function createAssertionCall(
        string $comparison,
        CodeBlockInterface $block,
        VariablePlaceholder $valuePlaceholder
    ): CodeBlockInterface {
        $assertionTemplate = 'exists' === $comparison
            ? AssertionCallFactory::ASSERT_TRUE_TEMPLATE
            : AssertionCallFactory::ASSERT_FALSE_TEMPLATE;

        return $this->assertionCallFactory->createValueExistenceAssertionCall(
            $block,
            $valuePlaceholder,
            $assertionTemplate
        );
    }
}
