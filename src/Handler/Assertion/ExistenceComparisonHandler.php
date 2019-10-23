<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;
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
     * @return StatementListInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createStatementList(object $model): StatementListInterface
    {
        if (!$model instanceof ExaminationAssertionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (!in_array($model->getComparison(), [AssertionComparison::EXISTS, AssertionComparison::NOT_EXISTS])) {
            throw new NonTranspilableModelException($model);
        }

        $value = $model->getExaminedValue();
        $valuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        $existence = null;

        if ($this->isScalarValue($value)) {
            $accessor = $this->scalarValueHandler->createStatementList($value);
            $accessor->appendStatement(0, ' ?? null');

            $assignment = clone $accessor;
            $assignment->prependStatement(-1, $valuePlaceholder . ' = ');
            $assignment = $assignment->withMetadata(
                $assignment->getMetadata()->withAdditionalVariableExports(new VariablePlaceholderCollection([
                    $valuePlaceholder,
                ]))
            );

            $existence = (new StatementList())
                ->withPredecessors([$assignment])
                ->withStatements([
                    sprintf('%s = %s !== null', $valuePlaceholder, $valuePlaceholder)
                ]);

            return $this->createAssertionCall($model->getComparison(), $existence, $valuePlaceholder);
        }

        if ($value instanceof DomIdentifierValueInterface) {
            $identifier = $value->getIdentifier();

            if (null === $identifier->getAttributeName()) {
                $accessor = $this->domCrawlerNavigatorCallFactory->createHasCall($identifier);

                $assignment = clone $accessor;
                $assignment->prependStatement(-1, $valuePlaceholder . ' = ');
                $assignment = $assignment->withMetadata(
                    $assignment->getMetadata()->withAdditionalVariableExports(new VariablePlaceholderCollection([
                        $valuePlaceholder,
                    ]))
                );

                return $this->createAssertionCall($model->getComparison(), $assignment, $valuePlaceholder);
            }

            $accessor = $this->namedDomIdentifierHandler->createStatementList(
                new NamedDomIdentifierValue($value, $valuePlaceholder)
            );

            $existence = (new StatementList())
                ->withPredecessors([$accessor])
                ->withStatements([
                    sprintf('%s = %s !== null', $valuePlaceholder, $valuePlaceholder)
                ]);

            return $this->createAssertionCall($model->getComparison(), $existence, $valuePlaceholder);
        }

        throw new NonTranspilableModelException($model);
    }

    private function createAssertionCall(
        string $comparison,
        StatementListInterface $statementList,
        VariablePlaceholder $valuePlaceholder
    ): StatementListInterface {
        $assertionTemplate = AssertionComparison::EXISTS === $comparison
            ? AssertionCallFactory::ASSERT_TRUE_TEMPLATE
            : AssertionCallFactory::ASSERT_FALSE_TEMPLATE;

        return $this->assertionCallFactory->createValueExistenceAssertionCall(
            $statementList,
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
