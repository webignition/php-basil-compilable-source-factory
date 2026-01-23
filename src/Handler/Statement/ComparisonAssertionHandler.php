<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;

class ComparisonAssertionHandler implements StatementHandlerInterface
{
    private const array OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'includes' => 'assertStringContainsString',
        'excludes' => 'assertStringNotContainsString',
        'is' => 'assertEquals',
        'is-not' => 'assertNotEquals',
        'matches' => 'assertMatchesRegularExpression',
    ];

    public function __construct(
        private ValueAccessorFactory $valueAccessorFactory,
        private TryCatchBlockFactory $tryCatchBlockFactory,
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createHandler(): self
    {
        return new ComparisonAssertionHandler(
            ValueAccessorFactory::createFactory(),
            TryCatchBlockFactory::createFactory(),
            PhpUnitCallFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(StatementInterface $statement): ?StatementHandlerComponents
    {
        if (!$statement instanceof AssertionInterface) {
            return null;
        }

        if (!$statement->isComparison()) {
            return null;
        }

        $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull((string) $statement->getIdentifier());
        $examinedAccessor = EncapsulatingCastExpression::forString($examinedAccessor);

        $expectedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull((string) $statement->getValue());
        $expectedAccessor = EncapsulatingCastExpression::forString($expectedAccessor);

        $expectedValuePlaceholder = new VariableName(VariableNameEnum::EXPECTED_VALUE->value);
        $examinedValuePlaceholder = new VariableName(VariableNameEnum::EXAMINED_VALUE->value);

        $catchBody = Body::createFromExpressions([
            $this->phpUnitCallFactory->createFailCall($statement, StatementStage::SETUP),
        ]);

        return new StatementHandlerComponents(
            new Statement(
                $this->phpUnitCallFactory->createAssertionCall(
                    self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP[$statement->getOperator()],
                    $statement,
                    [$expectedValuePlaceholder, $examinedValuePlaceholder],
                    [$expectedValuePlaceholder, $examinedValuePlaceholder],
                )
            ),
        )->withSetup(
            $this->tryCatchBlockFactory->createForThrowable(
                Body::createFromExpressions([
                    new AssignmentExpression($expectedValuePlaceholder, $expectedAccessor),
                    new AssignmentExpression($examinedValuePlaceholder, $examinedAccessor),
                ]),
                $catchBody,
            ),
        );
    }
}
