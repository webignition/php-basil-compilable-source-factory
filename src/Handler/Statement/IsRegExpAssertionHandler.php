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
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
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
    ) {}

    public static function createHandler(): self
    {
        return new IsRegExpAssertionHandler(
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            new ValueTypeIdentifier(),
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
        $examinedAccessorType = $examinedAccessor->getType();

        if (false === $examinedAccessorType->equals(TypeCollection::string())) {
            if ($examinedAccessor->encapsulateWhenCasting()) {
                $examinedAccessor = new EncapsulatedExpression($examinedAccessor);
            }

            $examinedAccessor = new CastExpression($examinedAccessor, Type::STRING);
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            if (null === $this->domIdentifierFactory->createFromIdentifierString($identifier)) {
                throw $unsupportedContentException;
            }
        }

        return $this->createIsRegExpAssertionBody($examinedAccessor, $statement);
    }

    private function createIsRegExpAssertionBody(
        ExpressionInterface $examinedAccessor,
        AssertionInterface $assertion,
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
        if (false === TypeCollection::boolean()->equals($identityComparison->getType())) {
            if ($identityComparison->encapsulateWhenCasting()) {
                $identityComparison = new EncapsulatedExpression($identityComparison);
            }

            $identityComparison = new CastExpression($identityComparison, Type::BOOLEAN);
        }

        return new StatementHandlerCollections(
            BodyContentCollection::createFromExpressions([
                $this->phpUnitCallFactory->createAssertionCall(
                    'assertFalse',
                    $assertion,
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
