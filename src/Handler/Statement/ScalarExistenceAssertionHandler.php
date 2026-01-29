<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullCoalescerExpression;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;

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
    public function handle(StatementInterface $statement): ?StatementHandlerCollections
    {
        if (!$statement instanceof AssertionInterface) {
            return null;
        }

        $nullComparisonExpression = new NullCoalescerExpression(
            $this->scalarValueHandler->handle((string) $statement->getIdentifier()),
            LiteralExpression::null(),
        );

        $examinedValueVariable = Property::asBooleanVariable('examinedValue');

        $examinedAccessor = new ComparisonExpression(
            new EncapsulatedExpression($nullComparisonExpression),
            LiteralExpression::null(),
            '!=='
        );
        $examinedAccessor = EncapsulatingCastExpression::forBool($examinedAccessor);

        $expected = LiteralExpression::boolean('exists' === $statement->getOperator());

        return new StatementHandlerCollections(
            BodyContentCollection::createFromExpressions([
                $this->phpUnitCallFactory->createAssertionCall(
                    'exists' === $statement->getOperator() ? 'assertTrue' : 'assertFalse',
                    $statement,
                    [$examinedValueVariable],
                    [$expected, $examinedValueVariable],
                )
            ])
        )->withSetup(
            BodyContentCollection::createFromExpressions([
                new AssignmentExpression($examinedValueVariable, $examinedAccessor),
            ])
        );
    }
}
