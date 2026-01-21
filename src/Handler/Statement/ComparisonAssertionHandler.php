<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\AssertionArgument;
use webignition\BasilCompilableSourceFactory\AssertionMessageFactory;
use webignition\BasilCompilableSourceFactory\AssertionStatementFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\StatementInterface;

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
        private AssertionStatementFactory $assertionStatementFactory,
        private ValueAccessorFactory $valueAccessorFactory,
        private AssertionMessageFactory $assertionMessageFactory,
        private TryCatchBlockFactory $tryCatchBlockFactory,
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createHandler(): self
    {
        return new ComparisonAssertionHandler(
            AssertionStatementFactory::createFactory(),
            ValueAccessorFactory::createFactory(),
            AssertionMessageFactory::createFactory(),
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

        $expected = new AssertionArgument($expectedValuePlaceholder, 'string');
        $examined = new AssertionArgument($examinedValuePlaceholder, 'string');

        $catchBody = Body::createFromExpressions([
            $this->phpUnitCallFactory->createFailCall($statement, StatementStage::SETUP),
        ]);

        return new StatementHandlerComponents(
            $this->assertionStatementFactory->create(
                assertionMethod: self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP[$statement->getOperator()],
                assertionMessage: $this->assertionMessageFactory->create($statement, $expected, $examined),
                expected: $expected,
                examined: $examined,
            )
        )->withSetup(
            $this->tryCatchBlockFactory->create(
                Body::createFromExpressions([
                    new AssignmentExpression($expectedValuePlaceholder, $expectedAccessor),
                    new AssignmentExpression($examinedValuePlaceholder, $examinedAccessor),
                ]),
                new ClassNameCollection([new ClassName(\Throwable::class)]),
                $catchBody,
            ),
        );
    }
}
