<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\MutableBlockInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ExaminationAssertionInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;

class ExistenceComparisonHandler implements HandlerInterface
{
    private $assertionCallFactory;
    private $scalarValueHandler;
    private $domCrawlerNavigatorCallFactory;
    private $namedDomIdentifierHandler;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        HandlerInterface $scalarValueHandler,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        HandlerInterface $namedDomIdentifierHandler
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
    }

    /**
     * @return ExistenceComparisonHandler
     */
    public static function createHandler(): HandlerInterface
    {
        return new ExistenceComparisonHandler(
            AssertionCallFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            NamedDomIdentifierHandler::createHandler()
        );
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof ExaminationAssertionInterface) {
            return false;
        }

        return in_array($model->getComparison(), [AssertionComparison::EXISTS, AssertionComparison::NOT_EXISTS]);
    }

    /**
     * @param object $model
     *
     * @return BlockInterface
     *
     * @throws UnsupportedModelException
     */
    public function handle(object $model): BlockInterface
    {
        if (!$model instanceof ExaminationAssertionInterface) {
            throw new UnsupportedModelException($model);
        }

        if (!in_array($model->getComparison(), [AssertionComparison::EXISTS, AssertionComparison::NOT_EXISTS])) {
            throw new UnsupportedModelException($model);
        }

        $value = $model->getExaminedValue();
        $valuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        $existence = null;

        if ($this->isScalarValue($value)) {
            $accessor = $this->scalarValueHandler->handle($value);

            if ($accessor instanceof MutableBlockInterface) {
                $accessor->mutateLastStatement(function (string $content) {
                    return $content . ' ?? null';
                });
            }

            $assignment = clone $accessor;

            if ($assignment instanceof MutableBlockInterface) {
                $assignment->mutateLastStatement(function (string $content) use ($valuePlaceholder) {
                    return $valuePlaceholder . ' = ' . $content;
                });

                $assignment->addVariableExportsToLastStatement(new VariablePlaceholderCollection([
                    $valuePlaceholder,
                ]));
            }

            $existence = new Block([
                $assignment,
                new Statement(sprintf('%s = %s !== null', $valuePlaceholder, $valuePlaceholder)),
            ]);

            return $this->createAssertionCall($model->getComparison(), $existence, $valuePlaceholder);
        }

        if ($value instanceof DomIdentifierValueInterface) {
            $identifier = $value->getIdentifier();

            if (null === $identifier->getAttributeName()) {
                $accessor = $this->domCrawlerNavigatorCallFactory->createHasCall($identifier);

                $assignment = new Block([
                    $accessor,
                ]);

                $assignment->mutateLastStatement(function (string $content) use ($valuePlaceholder) {
                    return $valuePlaceholder . ' = ' . $content;
                });
                $assignment->addVariableExportsToLastStatement(new VariablePlaceholderCollection([
                    $valuePlaceholder,
                ]));

                return $this->createAssertionCall(
                    $model->getComparison(),
                    new Block([$assignment]),
                    $valuePlaceholder
                );
            }

            $accessor = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($value, $valuePlaceholder)
            );

            $existence = new Block([
                $accessor,
                new Statement(sprintf('%s = %s !== null', $valuePlaceholder, $valuePlaceholder)),
            ]);

            return $this->createAssertionCall($model->getComparison(), $existence, $valuePlaceholder);
        }

        throw new UnsupportedModelException($model);
    }

    private function createAssertionCall(
        string $comparison,
        SourceInterface $lineList,
        VariablePlaceholder $valuePlaceholder
    ): BlockInterface {
        $assertionTemplate = AssertionComparison::EXISTS === $comparison
            ? AssertionCallFactory::ASSERT_TRUE_TEMPLATE
            : AssertionCallFactory::ASSERT_FALSE_TEMPLATE;

        return $this->assertionCallFactory->createValueExistenceAssertionCall(
            $lineList,
            $valuePlaceholder,
            $assertionTemplate
        );
    }

    private function isScalarValue(ValueInterface $value): bool
    {
        if (!$value instanceof ObjectValueInterface) {
            return false;
        }

        $valueType = $value->getType();

        $types = [
            ObjectValueType::BROWSER_PROPERTY,
            ObjectValueType::ENVIRONMENT_PARAMETER,
            ObjectValueType::PAGE_PROPERTY,
        ];

        foreach ($types as $type) {
            if ($type === $valueType) {
                return true;
            }
        }

        return false;
    }
}
