<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AssertionArgument;
use webignition\BasilCompilableSourceFactory\AssertionMessageFactory;
use webignition\BasilCompilableSourceFactory\AssertionStatementFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullCoalescerExpression;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class ScalarExistenceAssertionHandler
{
    public function __construct(
        private AssertionStatementFactory $assertionStatementFactory,
        private ScalarValueHandler $scalarValueHandler,
        private AssertionMessageFactory $assertionMessageFactory,
    ) {}

    public static function createHandler(): self
    {
        return new ScalarExistenceAssertionHandler(
            AssertionStatementFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            AssertionMessageFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
    {
        $nullComparisonExpression = new NullCoalescerExpression(
            $this->scalarValueHandler->handle((string) $assertion->getIdentifier()),
            new LiteralExpression('null'),
        );

        $examinedValuePlaceholder = new VariableName(VariableNameEnum::EXAMINED_VALUE->value);

        $examinedAccessor = new ComparisonExpression(
            new EncapsulatedExpression($nullComparisonExpression),
            new LiteralExpression('null'),
            '!=='
        );

        $examinedAssertionArgument = new AssertionArgument($examinedValuePlaceholder, 'bool');

        return new Body([
            new Statement(
                new AssignmentExpression($examinedValuePlaceholder, $examinedAccessor),
            ),
            $this->assertionStatementFactory->create(
                'exists' === $assertion->getOperator() ? 'assertTrue' : 'assertFalse',
                $this->assertionMessageFactory->create(
                    assertion: $assertion,
                    expected: null,
                    examined: $examinedAssertionArgument,
                ),
                expected: null,
                examined: $examinedAssertionArgument,
            )
        ]);
    }
}
