<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AssertionArgument;
use webignition\BasilCompilableSourceFactory\AssertionMessageFactory;
use webignition\BasilCompilableSourceFactory\AssertionStatementFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class ComparisonAssertionHandler
{
    public function __construct(
        private AssertionStatementFactory $assertionStatementFactory,
        private ValueAccessorFactory $valueAccessorFactory,
        private AssertionMessageFactory $assertionMessageFactory,
    ) {}

    public static function createHandler(): self
    {
        return new ComparisonAssertionHandler(
            AssertionStatementFactory::createFactory(),
            ValueAccessorFactory::createFactory(),
            AssertionMessageFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedContentException
     * @throws UnsupportedStatementException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
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

        return new Body([
            new Statement(
                new AssignmentExpression($expectedValuePlaceholder, $expectedAccessor),
            ),
            new Statement(
                new AssignmentExpression($examinedValuePlaceholder, $examinedAccessor),
            ),
            $this->assertionStatementFactory->create(
                assertionMethod: $this->getAssertionMethod($assertion->getOperator()),
                assertionMessage: $this->assertionMessageFactory->create($assertion, $expected, $examined),
                expected: $expected,
                examined: $examined,
            ),
        ]);
    }

    /**
     * @return non-empty-string
     */
    private function getAssertionMethod(string $operator): string
    {
        if ('includes' === $operator) {
            return 'assertStringContainsString';
        }

        if ('excludes' === $operator) {
            return 'assertStringNotContainsString';
        }

        if ('is-not' === $operator) {
            return 'assertNotEquals';
        }

        if ('matches' === $operator) {
            return 'assertMatchesRegularExpression';
        }

        return 'assertEquals';
    }
}
