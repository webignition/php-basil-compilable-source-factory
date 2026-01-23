<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
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
        private TryCatchBlockFactory $tryCatchBlockFactory,
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
            TryCatchBlockFactory::createFactory(),
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
        $examinedValuePlaceholder = new VariableName(VariableNameEnum::EXAMINED_VALUE->value);
        $expectedValuePlaceholder = new VariableName(VariableNameEnum::EXPECTED_VALUE->value);

        $pregMatchInvocation = new MethodInvocation(
            'preg_match',
            new MethodArguments(
                $this->argumentFactory->create(
                    $examinedValuePlaceholder,
                    null,
                )
            )
        )->setIsErrorSuppressed(true);

        $identityComparison = new ComparisonExpression(
            $pregMatchInvocation,
            new LiteralExpression('false'),
            '==='
        );
        $identityComparison = EncapsulatingCastExpression::forBool($identityComparison);

        $catchBody = Body::createFromExpressions([
            $this->phpUnitCallFactory->createFailCall($assertion, StatementStage::SETUP),
        ]);

        return new StatementHandlerComponents(
            new Statement($this->phpUnitCallFactory->createAssertionCall(
                'assertFalse',
                $assertion,
                [$expectedValuePlaceholder],
                [$expectedValuePlaceholder, $examinedValuePlaceholder],
            ))
        )->withSetup(
            $this->tryCatchBlockFactory->createForThrowable(
                Body::createFromExpressions([
                    new AssignmentExpression($examinedValuePlaceholder, $examinedAccessor),
                    new AssignmentExpression($expectedValuePlaceholder, $identityComparison),
                ]),
                $catchBody,
            )
        );
    }
}
