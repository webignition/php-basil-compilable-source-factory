<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullCoalescerExpression;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\StatementInterface;

class ScalarExistenceAssertionHandler implements StatementHandlerInterface
{
    public function __construct(
        private ScalarValueHandler $scalarValueHandler,
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createHandler(): self
    {
        return new ScalarExistenceAssertionHandler(
            ScalarValueHandler::createHandler(),
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

        $nullComparisonExpression = new NullCoalescerExpression(
            $this->scalarValueHandler->handle((string) $statement->getIdentifier()),
            new LiteralExpression('null'),
        );

        $examinedValuePlaceholder = new VariableName(VariableNameEnum::EXAMINED_VALUE->value);

        $examinedAccessor = new ComparisonExpression(
            new EncapsulatedExpression($nullComparisonExpression),
            new LiteralExpression('null'),
            '!=='
        );
        $examinedAccessor = EncapsulatingCastExpression::forBool($examinedAccessor);

        $expected = new LiteralExpression(('exists' === $statement->getOperator()) ? 'true' : 'false');

        return new StatementHandlerComponents(
            new Statement($this->phpUnitCallFactory->createAssertionCall(
                'exists' === $statement->getOperator() ? 'assertTrue' : 'assertFalse',
                $statement,
                [$examinedValuePlaceholder],
                [$expected, $examinedValuePlaceholder],
            ))
        )->withSetup(
            new Statement(
                new AssignmentExpression($examinedValuePlaceholder, $examinedAccessor),
            )
        );
    }
}
