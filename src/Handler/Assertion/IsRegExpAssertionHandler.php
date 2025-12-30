<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\AssertionStatementFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class IsRegExpAssertionHandler
{
    public function __construct(
        private ArgumentFactory $argumentFactory,
        private AssertionStatementFactory $assertionStatementFactory,
        private DomIdentifierFactory $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ValueTypeIdentifier $valueTypeIdentifier,
        private ValueAccessorFactory $valueAccessorFactory,
    ) {}

    public static function createHandler(): self
    {
        return new IsRegExpAssertionHandler(
            ArgumentFactory::createFactory(),
            AssertionStatementFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            new ValueTypeIdentifier(),
            ValueAccessorFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
    {
        $identifier = $assertion->getIdentifier();

        if (is_string($identifier) && $this->valueTypeIdentifier->isScalarValue($identifier)) {
            $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull($identifier);

            return $this->createIsRegExpAssertionBody($examinedAccessor, $assertion);
        }

        if (is_string($identifier) && $this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
            if (null === $domIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
            }

            $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull($identifier);

            return $this->createIsRegExpAssertionBody($examinedAccessor, $assertion);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
    }

    private function createIsRegExpAssertionBody(
        ExpressionInterface $examinedAccessor,
        AssertionInterface $assertion,
    ): BodyInterface {
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

        return new Body([
            Body::createFromExpressions([
                new AssignmentExpression($examinedValuePlaceholder, $examinedAccessor),
                new AssignmentExpression($expectedValuePlaceholder, $identityComparison),
            ]),
            $this->assertionStatementFactory->create(
                $assertion,
                'assertFalse',
                new MethodArguments([$expectedValuePlaceholder])
            ),
        ]);
    }
}
