<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
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
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createHandler(): self
    {
        return new ComparisonAssertionHandler(
            ValueAccessorFactory::createFactory(),
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

        if (!$statement->isComparison()) {
            return null;
        }

        $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull((string) $statement->getIdentifier());
        $examinedAccessorType = $examinedAccessor->getType();
        if (false === $examinedAccessorType->equals(TypeCollection::string())) {
            $examinedAccessor = EncapsulatingCastExpression::forString($examinedAccessor);
        }

        $expectedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull((string) $statement->getValue());
        $expectedAccessorType = $expectedAccessor->getType();
        if (false === $expectedAccessorType->equals(TypeCollection::string())) {
            $expectedAccessor = EncapsulatingCastExpression::forString($expectedAccessor);
        }

        $expectedValueVariable = Property::asStringVariable(VariableName::EXPECTED_VALUE);
        $examinedValueVariable = Property::asStringVariable(VariableName::EXAMINED_VALUE);

        return new StatementHandlerCollections(
            BodyContentCollection::createFromExpressions([
                $this->phpUnitCallFactory->createAssertionCall(
                    self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP[$statement->getOperator()],
                    $statement,
                    [$expectedValueVariable, $examinedValueVariable],
                    [$expectedValueVariable, $examinedValueVariable],
                )
            ])
        )->withSetup(
            BodyContentCollection::createFromExpressions([
                new AssignmentExpression($expectedValueVariable, $expectedAccessor),
                new AssignmentExpression($examinedValueVariable, $examinedAccessor),
            ]),
        );
    }
}
