<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
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
use webignition\BasilDataStructure\AssertionInterface;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Value\DomIdentifierValueInterface;

class ExistenceComparisonHandler
{
    private $assertionCallFactory;
    private $scalarValueHandler;
    private $domCrawlerNavigatorCallFactory;
    private $namedDomIdentifierHandler;
    private $valueTypeIdentifier;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        ScalarValueHandler $scalarValueHandler,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        ValueTypeIdentifier $valueTypeIdentifier
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->valueTypeIdentifier = $valueTypeIdentifier;
    }

    public static function createHandler(): ExistenceComparisonHandler
    {
        return new ExistenceComparisonHandler(
            AssertionCallFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            NamedDomIdentifierHandler::createHandler(),
            new ValueTypeIdentifier()
        );
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(AssertionInterface $assertion): CodeBlockInterface
    {
//        $value = $assertion->getExaminedValue();
        $valuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        $existence = null;

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

            return $this->createAssertionCall($assertion->getComparison(), $existence, $valuePlaceholder);
        }

        if ($value instanceof DomIdentifierValueInterface) {
            $identifier = $value->getIdentifier();

            if (null === $identifier->getAttributeName()) {
//                $accessor = $this->domCrawlerNavigatorCallFactory->createHasCall($identifier);
                // @todo fix in #209
                $accessor = $this->domCrawlerNavigatorCallFactory->createHasCall(new DomIdentifier(''));

                $assignment = new CodeBlock([
                    $accessor,
                ]);

                $assignment->mutateLastStatement(function (string $content) use ($valuePlaceholder) {
                    return $valuePlaceholder . ' = ' . $content;
                });
                $assignment->addVariableExportsToLastStatement(new VariablePlaceholderCollection([
                    $valuePlaceholder,
                ]));

                return $this->createAssertionCall(
                    $assertion->getComparison(),
                    new CodeBlock([$assignment]),
                    $valuePlaceholder
                );
            }

//            $accessor = $this->namedDomIdentifierHandler->handle(
//                new NamedDomIdentifierValue($value, $valuePlaceholder)
//            );

            // @todo: fix in #209
            $accessor = $this->namedDomIdentifierHandler->handle(new NamedDomIdentifierValue(
                new DomIdentifier(''),
                $valuePlaceholder
            ));

            $existence = new CodeBlock([
                $accessor,
                new Statement(sprintf('%s = %s !== null', $valuePlaceholder, $valuePlaceholder)),
            ]);

            return $this->createAssertionCall($assertion->getComparison(), $existence, $valuePlaceholder);
        }

        throw new UnsupportedModelException($assertion);
    }

    private function createAssertionCall(
        string $comparison,
        CodeBlockInterface $block,
        VariablePlaceholder $valuePlaceholder
    ): CodeBlockInterface {
        $assertionTemplate = AssertionComparison::EXISTS === $comparison
            ? AssertionCallFactory::ASSERT_TRUE_TEMPLATE
            : AssertionCallFactory::ASSERT_FALSE_TEMPLATE;

        return $this->assertionCallFactory->createValueExistenceAssertionCall(
            $block,
            $valuePlaceholder,
            $assertionTemplate
        );
    }
}
