<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AssertionArgument;
use webignition\BasilCompilableSourceFactory\AssertionMessageFactory;
use webignition\BasilCompilableSourceFactory\AssertionStatementFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\FailureMessageFactory;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class ComparisonAssertionHandler
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
     * @return array{'setup': BodyInterface, 'body': BodyInterface}
     *
     * @throws UnsupportedContentException
     * @throws UnsupportedStatementException
     */
    public function handle(AssertionInterface $assertion): array
    {
        if (!$assertion->isComparison()) {
            throw new UnsupportedStatementException($assertion);
        }

        $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull((string) $assertion->getIdentifier());
        $expectedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull((string) $assertion->getValue());

        $expectedValuePlaceholder = new VariableName(VariableNameEnum::EXPECTED_VALUE->value);
        $examinedValuePlaceholder = new VariableName(VariableNameEnum::EXAMINED_VALUE->value);

        $expected = new AssertionArgument($expectedValuePlaceholder, 'string');
        $examined = new AssertionArgument($examinedValuePlaceholder, 'string');

        $catchBody = Body::createFromExpressions([
            $this->phpUnitCallFactory->createFailCall(
                $this->failureMessageFactory->createForAssertionSetupThrowable($assertion)
            ),
        ]);

        $tryCatchBlock = $this->tryCatchBlockFactory->create(
            Body::createFromExpressions([
                new AssignmentExpression($expectedValuePlaceholder, $expectedAccessor),
                new AssignmentExpression($examinedValuePlaceholder, $examinedAccessor),
            ]),
            new ClassNameCollection([new ClassName(\Throwable::class)]),
            $catchBody,
        );

        return [
            'setup' => $tryCatchBlock,
            'body' => $this->assertionStatementFactory->create(
                assertionMethod: self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP[$assertion->getOperator()],
                assertionMessage: $this->assertionMessageFactory->create($assertion, $expected, $examined),
                expected: $expected,
                examined: $examined,
            ),
        ];
    }
}
