<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class IsRegExpAssertionHandler implements StatementHandlerInterface
{
    public function __construct(
        private ArgumentFactory $argumentFactory,
        private DomIdentifierFactory $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ValueTypeIdentifier $valueTypeIdentifier,
        private ValueAccessorFactory $valueAccessorFactory,
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createHandler(): self
    {
        return new IsRegExpAssertionHandler(
            ArgumentFactory::createFactory(),
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
    public function handle(StatementInterface $statement): ?StatementHandlerComponents
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
        $examinedAccessor = EncapsulatingCastExpression::forString($examinedAccessor);

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
    ): StatementHandlerComponents {
        $examinedValueVariable = Property::asVariable(VariableName::EXAMINED_VALUE->value);
        $expectedValueVariable = Property::asVariable(VariableName::EXPECTED_VALUE->value);

        $pregMatchInvocation = new MethodInvocation(
            methodName: 'preg_match',
            arguments: new MethodArguments(
                $this->argumentFactory->create(
                    $examinedValueVariable,
                    null,
                )
            ),
            mightThrow: false
        )->setIsErrorSuppressed(true);

        $identityComparison = new ComparisonExpression(
            $pregMatchInvocation,
            new LiteralExpression('false'),
            '==='
        );
        $identityComparison = EncapsulatingCastExpression::forBool($identityComparison);

        return new StatementHandlerComponents(
            new Statement($this->phpUnitCallFactory->createAssertionCall(
                'assertFalse',
                $assertion,
                [$expectedValueVariable],
                [$expectedValueVariable, $examinedValueVariable],
            ))
        )->withSetup(
            Body::createFromExpressions([
                new AssignmentExpression($examinedValueVariable, $examinedAccessor),
                new AssignmentExpression($expectedValueVariable, $identityComparison),
            ]),
        );
    }
}
