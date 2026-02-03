<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\StatementVariableFactory;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class IsRegExpAssertionHandler implements StatementHandlerInterface
{
    public function __construct(
        private DomIdentifierFactory $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ValueTypeIdentifier $valueTypeIdentifier,
        private ValueAccessorFactory $valueAccessorFactory,
        private PhpUnitCallFactory $phpUnitCallFactory,
        private StatementVariableFactory $statementVariableFactory,
    ) {}

    public static function createHandler(): self
    {
        return new IsRegExpAssertionHandler(
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            new ValueTypeIdentifier(),
            ValueAccessorFactory::createFactory(),
            PhpUnitCallFactory::createFactory(),
            StatementVariableFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(StatementInterface $statement, int $sequenceNumber): ?StatementHandlerCollections
    {
        if (!$statement instanceof AssertionInterface) {
            return null;
        }

        if ('is-regexp' !== $statement->getOperator()) {
            return null;
        }

        $identifier = $statement->getIdentifier();

        $unsupportedContentException = new UnsupportedContentException(
            UnsupportedContentException::TYPE_IDENTIFIER,
            $identifier
        );

        if (!is_string($identifier)) {
            throw $unsupportedContentException;
        }

        if (
            !$this->valueTypeIdentifier->isScalarValue($identifier)
            && !$this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)
        ) {
            throw $unsupportedContentException;
        }

        $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull($identifier);
        $examinedAccessor = new CastExpression($examinedAccessor, Type::STRING);

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            if (null === $this->domIdentifierFactory->createFromIdentifierString($identifier)) {
                throw $unsupportedContentException;
            }
        }

        return $this->createIsRegExpAssertionBody($examinedAccessor, $sequenceNumber);
    }

    private function createIsRegExpAssertionBody(
        ExpressionInterface $examinedAccessor,
        int $sequenceNumber,
    ): StatementHandlerCollections {
        $examinedValueVariable = Property::asStringVariable(VariableName::EXAMINED_VALUE);
        $expectedValueVariable = Property::asBooleanVariable(VariableName::EXPECTED_VALUE);

        $pregMatchInvocation = new MethodInvocation(
            methodName: 'preg_match',
            arguments: new MethodArguments([
                $examinedValueVariable,
                LiteralExpression::null(),
            ]),
            mightThrow: false,
            type: TypeCollection::integer(),
        )->setIsErrorSuppressed();

        $identityComparison = new ComparisonExpression(
            $pregMatchInvocation,
            LiteralExpression::boolean(false),
            '==='
        );
        $identityComparison = new CastExpression($identityComparison, Type::BOOLEAN);

        return new StatementHandlerCollections(
            BodyContentCollection::createFromExpressions([
                $this->phpUnitCallFactory->createAssertionCall(
                    'assertFalse',
                    $this->statementVariableFactory->create($sequenceNumber),
                    [$expectedValueVariable],
                    [$expectedValueVariable, $examinedValueVariable],
                )
            ])
        )->withSetup(
            BodyContentCollection::createFromExpressions([
                new AssignmentExpression($examinedValueVariable, $examinedAccessor),
                new AssignmentExpression($expectedValueVariable, $identityComparison),
            ]),
        );
    }
}
