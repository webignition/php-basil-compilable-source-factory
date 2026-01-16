<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AssertionArgument;
use webignition\BasilCompilableSourceFactory\AssertionMessageFactory;
use webignition\BasilCompilableSourceFactory\AssertionStatementFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\FailureMessageFactory;
use webignition\BasilCompilableSourceFactory\Handler\StatementHandlerComponents;
use webignition\BasilCompilableSourceFactory\Handler\StatementHandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
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
        private FailureMessageFactory $failureMessageFactory,
    ) {}

    public static function createHandler(): self
    {
        return new ComparisonAssertionHandler(
            AssertionStatementFactory::createFactory(),
            ValueAccessorFactory::createFactory(),
            AssertionMessageFactory::createFactory(),
            TryCatchBlockFactory::createFactory(),
            PhpUnitCallFactory::createFactory(),
            FailureMessageFactory::createFactory(),
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
        $expectedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull((string) $statement->getValue());

        $expectedValuePlaceholder = new VariableName(VariableNameEnum::EXPECTED_VALUE->value);
        $examinedValuePlaceholder = new VariableName(VariableNameEnum::EXAMINED_VALUE->value);

        $expected = new AssertionArgument($expectedValuePlaceholder, 'string');
        $examined = new AssertionArgument($examinedValuePlaceholder, 'string');

        $catchBody = Body::createFromExpressions([
            $this->phpUnitCallFactory->createFailCall(
                $this->failureMessageFactory->createForAssertionSetupThrowable($statement)
            ),
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
